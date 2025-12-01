<?php
/**
 * Create Booking Action - JSON Endpoint
 */

// Suppress error output to prevent breaking JSON response
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Start output buffering to catch any unexpected output
ob_start();

header('Content-Type: application/json');

require_once(__DIR__ . '/../controllers/booking_controller.php');
require_once(__DIR__ . '/../controllers/payment_controller.php');
require_once(__DIR__ . '/../controllers/notification_controller.php');
require_once(__DIR__ . '/../controllers/venue_controller.php');
require_once(__DIR__ . '/../controllers/customer_controller.php');
require_once(__DIR__ . '/../settings/core.php');

// Helper function to send clean JSON response
function send_json_response($data) {
    ob_clean();
    echo json_encode($data);
    ob_end_flush();
    exit();
}

// Check if logged in
if (!is_logged_in()) {
    send_json_response(['success' => false, 'message' => 'Please login to continue']);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json_response(['success' => false, 'message' => 'Invalid request method']);
}

$customer_id = $_SESSION['customer_id'];

// Get form data
$venue_id = isset($_POST['venue_id']) ? intval($_POST['venue_id']) : 0;
$booking_date = isset($_POST['booking_date']) ? trim($_POST['booking_date']) : '';
$start_time = isset($_POST['start_time']) ? trim($_POST['start_time']) : '';
$end_time = isset($_POST['end_time']) ? trim($_POST['end_time']) : '';
$number_of_guests = isset($_POST['number_of_guests']) ? intval($_POST['number_of_guests']) : 1;
if ($number_of_guests < 1) {
    $number_of_guests = 1;
}
$special_requirements = isset($_POST['special_requirements']) ? trim($_POST['special_requirements']) : null;
$total_amount = isset($_POST['total_amount']) ? floatval($_POST['total_amount']) : 0;
$payment_method = isset($_POST['payment_method']) ? trim($_POST['payment_method']) : 'momo';

// Validation
if (empty($venue_id)) {
    send_json_response(['success' => false, 'message' => 'Venue ID is required']);
}

if (empty($booking_date)) {
    send_json_response(['success' => false, 'message' => 'Booking date is required']);
}

if (empty($start_time) || empty($end_time)) {
    send_json_response(['success' => false, 'message' => 'Start and end time are required']);
}

// Allow 0 amount only for reservations
if ($total_amount <= 0 && $payment_method !== 'reservation') {
    send_json_response(['success' => false, 'message' => 'Invalid booking amount']);
}

// Validate date is not in the past
$today = date('Y-m-d');
if ($booking_date < $today) {
    send_json_response(['success' => false, 'message' => 'Cannot book dates in the past']);
}

// Create booking
$booking_data = [
    'user_id' => $customer_id,
    'venue_id' => $venue_id,
    'booking_date' => $booking_date,
    'start_time' => $start_time,
    'end_time' => $end_time,
    'guest_count' => $number_of_guests,
    'total_amount' => $total_amount,
    'deposit_amount' => 0, // Will be updated if payment is made
    'qr_reference' => 'BK-' . strtoupper(uniqid()),
    'special_requests' => $special_requirements
];

$booking_id = create_booking_ctr($booking_data);

if (!$booking_id) {
    send_json_response(['success' => false, 'message' => 'Failed to create booking. Please try again.']);
}

// Get venue and customer info for notification
$venue = get_venue_by_id_ctr($venue_id);
$customer = get_customer_by_id_ctr($customer_id);

if ($venue && $customer && isset($venue['created_by'])) {
    // Notify venue owner about new booking request (non-blocking)
    try {
        notify_booking_request_ctr(
            $booking_id,
            $venue['created_by'],
            $customer['customer_name'] ?? 'A customer',
            $venue['title'] ?? 'your venue'
        );
    } catch (Exception $e) {
        // Log error but don't fail the booking
        error_log("Notification error: " . $e->getMessage());
    }
}

// Payment method specific processing
if ($payment_method === 'reservation') {
    // For reservations, no payment is required
    send_json_response([
        'success' => true,
        'message' => 'Reservation request sent successfully.',
        'booking_id' => $booking_id,
        'payment_method' => 'reservation',
        'payment_required' => false
    ]);

} else if ($payment_method === 'paystack') {
    // For Paystack, booking is created first, then payment is initialized separately
    // Return booking_id so frontend can call payment_init_paystack_action.php
    send_json_response([
        'success' => true,
        'message' => 'Booking created. Please proceed to payment.',
        'booking_id' => $booking_id,
        'payment_method' => 'paystack',
        'payment_required' => true,
        'requires_payment_init' => true
    ]);

} else if ($payment_method === 'momo') {
    $momo_number = isset($_POST['momo_number']) ? trim($_POST['momo_number']) : '';
    
    if (empty($momo_number)) {
        send_json_response(['success' => false, 'message' => 'Mobile Money number is required']);
    }
    
    // Legacy MoMo processing (can be kept for backward compatibility)
    $payment_status = 'pending';
    $transaction_ref = 'MOMO_' . strtoupper(uniqid());
    
    // Create payment record
    $payment_data = [
        'booking_id' => $booking_id,
        'user_id' => $customer_id,
        'amount' => $total_amount,
        'currency' => 'GHS',
        'payment_method' => 'momo',
        'payment_type' => 'deposit',
        'transaction_ref' => $transaction_ref,
        'momo_number' => $momo_number,
        'status' => $payment_status
    ];

    $payment_id = create_payment_ctr($payment_data);

if (!$payment_id) {
    send_json_response(['success' => false, 'message' => 'Booking created but payment processing failed']);
}

    send_json_response([
        'success' => true,
        'message' => 'Booking created. Awaiting payment confirmation.',
        'booking_id' => $booking_id,
        'payment_required' => true
    ]);

} else {
    send_json_response(['success' => false, 'message' => 'Invalid payment method. Please use Paystack.']);
}

?>