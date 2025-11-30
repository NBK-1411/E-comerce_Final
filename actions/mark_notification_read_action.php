<?php
/**
 * Mark Notification as Read Action
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
    $notification_id = isset($_POST['notification_id']) ? intval($_POST['notification_id']) : 0;
    
    if ($notification_id > 0) {
        $result = mark_notification_as_read_ctr($notification_id, $user_id);
        if ($result) {
            $unread_count = get_unread_notification_count_ctr($user_id);
            echo json_encode([
                'success' => true,
                'message' => 'Notification marked as read',
                'unread_count' => $unread_count
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to mark notification as read']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid notification ID']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

