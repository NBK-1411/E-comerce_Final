<?php
/**
 * Notification Controller - Business Logic Layer
 */

require_once(__DIR__ . '/../classes/notification_class.php');

/**
 * Create a notification
 */
function create_notification_ctr($data) {
    $notification = new Notification();
    return $notification->create_notification($data);
}

/**
 * Get user notifications
 */
function get_user_notifications_ctr($user_id, $limit = 20, $unread_only = false) {
    $notification = new Notification();
    return $notification->get_user_notifications($user_id, $limit, $unread_only);
}

/**
 * Get unread notification count
 */
function get_unread_notification_count_ctr($user_id) {
    $notification = new Notification();
    return $notification->get_unread_count($user_id);
}

/**
 * Mark notification as read
 */
function mark_notification_as_read_ctr($notification_id, $user_id) {
    $notification = new Notification();
    return $notification->mark_as_read($notification_id, $user_id);
}

/**
 * Mark all notifications as read
 */
function mark_all_notifications_as_read_ctr($user_id) {
    $notification = new Notification();
    return $notification->mark_all_as_read($user_id);
}

/**
 * Delete notification
 */
function delete_notification_ctr($notification_id, $user_id) {
    $notification = new Notification();
    return $notification->delete_notification($notification_id, $user_id);
}

/**
 * Helper function to create booking-related notifications
 */
function notify_booking_request_ctr($booking_id, $venue_owner_id, $customer_name, $venue_title) {
    return create_notification_ctr([
        'recipient_id' => $venue_owner_id,
        'notification_type' => 'booking_request',
        'title' => 'New Booking Request',
        'message' => $customer_name . ' has requested to book ' . $venue_title,
        'related_booking_id' => $booking_id
    ]);
}

function notify_booking_confirmed_ctr($booking_id, $customer_id, $venue_title) {
    return create_notification_ctr([
        'recipient_id' => $customer_id,
        'notification_type' => 'booking_confirmed',
        'title' => 'Booking Confirmed',
        'message' => 'Your booking at ' . $venue_title . ' has been confirmed!',
        'related_booking_id' => $booking_id
    ]);
}

function notify_booking_declined_ctr($booking_id, $customer_id, $venue_title) {
    return create_notification_ctr([
        'recipient_id' => $customer_id,
        'notification_type' => 'booking_declined',
        'title' => 'Booking Declined',
        'message' => 'Your booking request for ' . $venue_title . ' has been declined.',
        'related_booking_id' => $booking_id
    ]);
}

function notify_payment_received_ctr($booking_id, $venue_owner_id, $amount, $venue_title) {
    return create_notification_ctr([
        'recipient_id' => $venue_owner_id,
        'notification_type' => 'payment_received',
        'title' => 'Payment Received',
        'message' => 'GH₵' . number_format($amount, 2) . ' payment received for ' . $venue_title,
        'related_booking_id' => $booking_id
    ]);
}

function notify_review_posted_ctr($review_id, $venue_owner_id, $venue_title, $rating) {
    return create_notification_ctr([
        'recipient_id' => $venue_owner_id,
        'notification_type' => 'review_posted',
        'title' => 'New Review',
        'message' => 'You received a ' . $rating . '-star review for ' . $venue_title,
        'related_review_id' => $review_id
    ]);
}

function notify_venue_approved_ctr($venue_id, $venue_owner_id, $venue_title) {
    return create_notification_ctr([
        'recipient_id' => $venue_owner_id,
        'notification_type' => 'venue_approved',
        'title' => 'Venue Approved',
        'message' => 'Your venue "' . $venue_title . '" has been approved and is now live!',
        'related_venue_id' => $venue_id
    ]);
}

function notify_venue_rejected_ctr($venue_id, $venue_owner_id, $venue_title) {
    return create_notification_ctr([
        'recipient_id' => $venue_owner_id,
        'notification_type' => 'venue_rejected',
        'title' => 'Venue Rejected',
        'message' => 'Your venue "' . $venue_title . '" has been rejected. Please check the details and resubmit.',
        'related_venue_id' => $venue_id
    ]);
}

