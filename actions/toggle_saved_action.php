<?php
/**
 * Toggle Saved Venue Action - JSON Endpoint
 */

header('Content-Type: application/json');

// Require core.php first for session management
require_once(__DIR__ . '/../settings/core.php');
require_once(__DIR__ . '/../controllers/customer_controller.php');

// Check if user is logged in
if (!is_logged_in()) {
    echo json_encode(['status' => 'error', 'message' => 'Please login to save venues']);
    exit;
}

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);
$venue_id = isset($input['venue_id']) ? intval($input['venue_id']) : 0;
$action = isset($input['action']) ? trim($input['action']) : '';

if ($venue_id <= 0 || !in_array($action, ['save', 'unsave'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit;
}

$customer_id = get_user_id();
$result = false;

try {
if ($action === 'save') {
    // Check if already saved to prevent duplicates (though DB handles unique constraint)
    if (!is_venue_saved_ctr($customer_id, $venue_id)) {
        $result = save_venue_ctr($customer_id, $venue_id);
    } else {
        $result = true; // Already saved
    }
} else {
    $result = unsave_venue_ctr($customer_id, $venue_id);
}

if ($result) {
    echo json_encode(['status' => 'success', 'action' => $action]);
} else {
        echo json_encode(['status' => 'error', 'message' => 'Database error. Please try again.']);
    }
} catch (Exception $e) {
    error_log("Toggle saved venue error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'An error occurred while saving the venue.']);
}
?>