<?php
/**
 * Update Venue Status Action - JSON Endpoint (Admin Only)
 */

header('Content-Type: application/json');

require_once(__DIR__ . '/../controllers/venue_controller.php');
require_once(__DIR__ . '/../controllers/notification_controller.php');
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

$venue_id = isset($_POST['venue_id']) ? intval($_POST['venue_id']) : 0;
$status = isset($_POST['status']) ? trim($_POST['status']) : '';

// Validation
if (empty($venue_id)) {
    echo json_encode(['success' => false, 'message' => 'Venue ID is required']);
    exit();
}

if (!in_array($status, ['pending', 'approved', 'rejected'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit();
}

$result = update_venue_status_ctr($venue_id, $status);

if ($result) {
    // Get venue details for notification
    $venue = get_venue_by_id_ctr($venue_id);
    if ($venue && isset($venue['created_by'])) {
        if ($status === 'approved') {
            notify_venue_approved_ctr($venue_id, $venue['created_by'], $venue['title'] ?? 'your venue');
        } elseif ($status === 'rejected') {
            notify_venue_rejected_ctr($venue_id, $venue['created_by'], $venue['title'] ?? 'your venue');
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Venue status updated to ' . $status
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update venue status']);
}

?>

