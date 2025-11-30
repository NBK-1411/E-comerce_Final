<?php
/**
 * Update Booking Status Action - JSON Endpoint (Admin Only)
 */

header('Content-Type: application/json');

require_once(__DIR__ . '/../controllers/booking_controller.php');
require_once(__DIR__ . '/../controllers/notification_controller.php');
require_once(__DIR__ . '/../controllers/venue_controller.php');
require_once(__DIR__ . '/../settings/core.php');

// Check if admin
if (!is_admin()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$booking_id = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0;
$status = isset($_POST['status']) ? trim($_POST['status']) : '';

// Validation
if (empty($booking_id)) {
    echo json_encode(['success' => false, 'message' => 'Booking ID is required']);
    exit();
}

if (!in_array($status, ['requested', 'confirmed', 'cancelled', 'completed', 'disputed'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit();
}

$result = update_booking_status_ctr($booking_id, $status);

if ($result) {
    // Get booking details for notification
    $booking = get_booking_by_id_ctr($booking_id);
    if ($booking) {
        $venue = get_venue_by_id_ctr($booking['venue_id']);
        
        if ($status === 'confirmed') {
            notify_booking_confirmed_ctr($booking_id, $booking['user_id'], $venue['title'] ?? 'your venue');
        } elseif ($status === 'cancelled' || $status === 'declined') {
            notify_booking_declined_ctr($booking_id, $booking['user_id'], $venue['title'] ?? 'your venue');
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Booking status updated to ' . $status
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update booking status'
    ]);
}

?>

