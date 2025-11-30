<?php
/**
 * Notification Class - Data Access Layer
 */

require_once(__DIR__ . '/../settings/db_class.php');

class Notification extends db_connection {
    
    /**
     * Create a new notification
     */
    public function create_notification($data) {
        $sql = "INSERT INTO notifications (recipient_id, notification_type, title, message, 
                related_booking_id, related_venue_id, related_review_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        return $this->write($sql, [
            $data['recipient_id'],
            $data['notification_type'],
            $data['title'],
            $data['message'],
            $data['related_booking_id'] ?? null,
            $data['related_venue_id'] ?? null,
            $data['related_review_id'] ?? null
        ], 'isssiii');
    }
    
    /**
     * Get notifications for a user
     */
    public function get_user_notifications($user_id, $limit = 20, $unread_only = false) {
        $sql = "SELECT n.*, 
                b.booking_id, b.booking_date, b.status as booking_status,
                v.venue_id, v.title as venue_title,
                r.review_id, r.rating as review_rating
                FROM notifications n
                LEFT JOIN booking b ON n.related_booking_id = b.booking_id
                LEFT JOIN venue v ON n.related_venue_id = v.venue_id
                LEFT JOIN review r ON n.related_review_id = r.review_id
                WHERE n.recipient_id = ?";
        
        $params = [$user_id];
        $types = 'i';
        
        if ($unread_only) {
            $sql .= " AND n.is_read = 0";
        }
        
        $sql .= " ORDER BY n.created_at DESC LIMIT ?";
        $params[] = $limit;
        $types .= 'i';
        
        return $this->read($sql, $params, $types);
    }
    
    /**
     * Get unread notification count for a user
     */
    public function get_unread_count($user_id) {
        $sql = "SELECT COUNT(*) as count FROM notifications 
                WHERE recipient_id = ? AND is_read = 0";
        
        $result = $this->read($sql, [$user_id], 'i');
        return $result ? (int)$result[0]['count'] : 0;
    }
    
    /**
     * Mark notification as read
     */
    public function mark_as_read($notification_id, $user_id) {
        $sql = "UPDATE notifications 
                SET is_read = 1, read_at = CURRENT_TIMESTAMP 
                WHERE notification_id = ? AND recipient_id = ?";
        
        return $this->write($sql, [$notification_id, $user_id], 'ii');
    }
    
    /**
     * Mark all notifications as read for a user
     */
    public function mark_all_as_read($user_id) {
        $sql = "UPDATE notifications 
                SET is_read = 1, read_at = CURRENT_TIMESTAMP 
                WHERE recipient_id = ? AND is_read = 0";
        
        return $this->write($sql, [$user_id], 'i');
    }
    
    /**
     * Delete a notification
     */
    public function delete_notification($notification_id, $user_id) {
        $sql = "DELETE FROM notifications 
                WHERE notification_id = ? AND recipient_id = ?";
        
        return $this->write($sql, [$notification_id, $user_id], 'ii');
    }
}

