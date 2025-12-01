<?php
/**
 * RSVP Action - Handle RSVP requests
 */

header('Content-Type: application/json');

require_once(__DIR__ . '/../settings/core.php');
require_once(__DIR__ . '/../controllers/rsvp_controller.php');
require_once(__DIR__ . '/../controllers/activity_controller.php');

// Check if logged in
if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Please login to RSVP']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$user_id = $_SESSION['customer_id'];
$activity_id = isset($_POST['activity_id']) ? intval($_POST['activity_id']) : 0;
$action = isset($_POST['action']) ? trim($_POST['action']) : 'rsvp'; // 'rsvp' or 'cancel'
$guest_count = isset($_POST['guest_count']) ? intval($_POST['guest_count']) : 1;
$status = isset($_POST['status']) ? trim($_POST['status']) : 'going'; // 'going' or 'interested'

if ($activity_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid activity ID']);
    exit();
}

// Validate guest count
if ($guest_count < 1) {
    $guest_count = 1;
}

// Get activity to check capacity
$activity = get_activity_by_id_ctr($activity_id);
if (!$activity) {
    echo json_encode(['success' => false, 'message' => 'Activity not found']);
    exit();
}

// Check capacity if activity has one
if (isset($activity['capacity']) && $activity['capacity'] > 0) {
    $rsvp_counts = get_rsvp_counts_ctr($activity_id);
    $current_going = $rsvp_counts['going_count'] ?? 0;
    
    // Get current user's RSVP if exists
    $current_rsvp = get_rsvp_ctr($activity_id, $user_id);
    if ($current_rsvp && $current_rsvp['status'] === 'going') {
        $current_going -= $current_rsvp['guest_count'];
    }
    
    if ($action === 'rsvp' && $status === 'going') {
        if (($current_going + $guest_count) > $activity['capacity']) {
            echo json_encode(['success' => false, 'message' => 'Not enough spots available. Only ' . ($activity['capacity'] - $current_going) . ' spots left.']);
            exit();
        }
    }
}

// Perform action
if ($action === 'cancel') {
    $result = delete_rsvp_ctr($activity_id, $user_id);
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'RSVP cancelled', 'action' => 'cancelled']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to cancel RSVP']);
    }
} else {
    $result = upsert_rsvp_ctr($activity_id, $user_id, $status, $guest_count);
    if ($result) {
        $rsvp_counts = get_rsvp_counts_ctr($activity_id);
        echo json_encode([
            'success' => true, 
            'message' => 'RSVP confirmed!',
            'action' => 'rsvp',
            'status' => $status,
            'counts' => $rsvp_counts
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to RSVP']);
    }
}

?>

