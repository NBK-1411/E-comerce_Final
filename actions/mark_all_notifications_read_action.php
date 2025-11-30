<?php
/**
 * Mark All Notifications as Read Action
 */

header('Content-Type: application/json');
require_once(__DIR__ . '/../settings/core.php');
require_once(__DIR__ . '/../controllers/notification_controller.php');

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Please login to continue']);
    exit();
}

$user_id = get_user_id();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = mark_all_notifications_as_read_ctr($user_id);
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'All notifications marked as read',
            'unread_count' => 0
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to mark notifications as read']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

