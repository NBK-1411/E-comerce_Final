<?php
/**
 * Booking Request Action - JSON Endpoint
 */

header('Content-Type: application/json');

require_once(__DIR__ . '/../controllers/booking_controller.php');
require_once(__DIR__ . '/../settings/core.php');

// Check if logged in
if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Please login to make a booking']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Get POST data
$venue_id = isset($_POST['venue_id']) ? intval($_POST['venue_id']) : 0;
$booking_date = isset($_POST['booking_date']) ? trim($_POST['booking_date']) : '';
$start_time = isset($_POST['start_time']) ? trim($_POST['start_time']) : '';
$end_time = isset($_POST['end_time']) ? trim($_POST['end_time']) : '';
$guest_count = isset($_POST['guest_count']) ? intval($_POST['guest_count']) : 0;
$total_amount = isset($_POST['total_amount']) ? floatval($_POST['total_amount']) : 0;
$deposit_amount = isset($_POST['deposit_amount']) ? floatval($_POST['deposit_amount']) : 0;
$special_requests = isset($_POST['special_requests']) ? trim($_POST['special_requests']) : '';

// Validation
if (empty($venue_id) || empty($booking_date) || empty($start_time) || empty($end_time)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit();
}

// Generate QR reference
$qr_reference = 'QR-' . generate_random_string(10);

$booking_data = [
    'user_id' => get_user_id(),
    'venue_id' => $venue_id,
    'booking_date' => $booking_date,
    'start_time' => $start_time,
    'end_time' => $end_time,
    'guest_count' => $guest_count,
    'total_amount' => $total_amount,
    'deposit_amount' => $deposit_amount,
    'qr_reference' => $qr_reference,
    'special_requests' => $special_requests
];

$result = create_booking_ctr($booking_data);

if ($result) {
    echo json_encode([
        'success' => true,
        'message' => 'Booking request submitted successfully!',
        'qr_reference' => $qr_reference
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Booking failed. Venue may not be available for selected time.'
    ]);
}

?>

