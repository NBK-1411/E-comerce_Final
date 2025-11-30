<?php
/**
 * Get Booking Details Action - JSON Endpoint (Admin Only)
 */

header('Content-Type: application/json');

require_once(__DIR__ . '/../controllers/booking_controller.php');
require_once(__DIR__ . '/../settings/core.php');

// Check if admin
if (!is_admin()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;

// Validation
if (empty($booking_id)) {
    echo json_encode(['success' => false, 'message' => 'Booking ID is required']);
    exit();
}

$booking = get_booking_by_id_ctr($booking_id);

if ($booking) {
    echo json_encode([
        'success' => true,
        'data' => $booking
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Booking not found'
    ]);
}

?>

