<?php
/**
 * Get Notifications Action
 * Returns user notifications as JSON
 */

header('Content-Type: application/json');
require_once(__DIR__ . '/../settings/core.php');
require_once(__DIR__ . '/../controllers/notification_controller.php');

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Please login to continue']);
    exit();
}

$user_id = get_user_id();
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 20;
$unread_only = isset($_GET['unread_only']) && $_GET['unread_only'] === 'true';

$notifications = get_user_notifications_ctr($user_id, $limit, $unread_only);
$unread_count = get_unread_notification_count_ctr($user_id);

// Format notifications for frontend
$formatted_notifications = [];
foreach ($notifications as $notif) {
    $formatted_notifications[] = [
        'id' => $notif['notification_id'],
        'type' => $notif['notification_type'],
        'title' => $notif['title'],
        'message' => $notif['message'],
        'is_read' => (bool)$notif['is_read'],
        'created_at' => $notif['created_at'],
        'time_ago' => time_ago($notif['created_at']),
        'related_booking_id' => $notif['related_booking_id'],
        'related_venue_id' => $notif['related_venue_id'],
        'related_review_id' => $notif['related_review_id'],
        'venue_title' => $notif['venue_title'] ?? null
    ];
}

echo json_encode([
    'success' => true,
    'notifications' => $formatted_notifications,
    'unread_count' => $unread_count
]);

/**
 * Helper function to calculate time ago
 */
function time_ago($datetime) {
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;
    
    if ($diff < 60) {
        return 'just now';
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . ' minute' . ($mins > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } else {
        return date('M d, Y', $timestamp);
    }
}

