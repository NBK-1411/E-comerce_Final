<?php
/**
 * RSVP Class - Data Access Layer
 * Handles activity RSVPs (going, interested, not_going)
 */

require_once(__DIR__ . '/../settings/db_class.php');

class Rsvp extends db_connection {
    
    /**
     * Create or update RSVP
     */
    public function upsert_rsvp($activity_id, $user_id, $status = 'going', $guest_count = 1) {
        $sql = "INSERT INTO rsvps (activity_id, user_id, status, guest_count) 
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE status = VALUES(status), guest_count = VALUES(guest_count), updated_at = CURRENT_TIMESTAMP";
        
        return $this->write($sql, [$activity_id, $user_id, $status, $guest_count], 'iisi');
    }
    
    /**
     * Get RSVP by user and activity
     */
    public function get_rsvp($activity_id, $user_id) {
        $sql = "SELECT * FROM rsvps WHERE activity_id = ? AND user_id = ?";
        $result = $this->read($sql, [$activity_id, $user_id], 'ii');
        return $result ? $result[0] : false;
    }
    
    /**
     * Delete RSVP
     */
    public function delete_rsvp($activity_id, $user_id) {
        $sql = "DELETE FROM rsvps WHERE activity_id = ? AND user_id = ?";
        return $this->write($sql, [$activity_id, $user_id], 'ii');
    }
    
    /**
     * Get RSVPs for activity
     */
    public function get_activity_rsvps($activity_id, $status = null) {
        if ($status) {
            $sql = "SELECT r.*, c.customer_name, c.customer_email
                    FROM rsvps r
                    LEFT JOIN customer c ON r.user_id = c.customer_id
                    WHERE r.activity_id = ? AND r.status = ?
                    ORDER BY r.created_at DESC";
            return $this->read($sql, [$activity_id, $status], 'is');
        } else {
            $sql = "SELECT r.*, c.customer_name, c.customer_email
                    FROM rsvps r
                    LEFT JOIN customer c ON r.user_id = c.customer_id
                    WHERE r.activity_id = ?
                    ORDER BY r.created_at DESC";
            return $this->read($sql, [$activity_id], 'i');
        }
    }
    
    /**
     * Get RSVP counts for activity
     */
    public function get_rsvp_counts($activity_id) {
        $sql = "SELECT 
                    SUM(CASE WHEN status = 'going' THEN guest_count ELSE 0 END) as going_count,
                    SUM(CASE WHEN status = 'interested' THEN 1 ELSE 0 END) as interested_count,
                    COUNT(*) as total_count
                FROM rsvps 
                WHERE activity_id = ?";
        
        $result = $this->read($sql, [$activity_id], 'i');
        return $result ? $result[0] : ['going_count' => 0, 'interested_count' => 0, 'total_count' => 0];
    }
    
    /**
     * Get user's RSVPs (for profile)
     */
    public function get_user_rsvps($user_id, $status = null) {
        if ($status) {
            $sql = "SELECT r.*, a.*, a.title as activity_title, a.start_at, a.location_text, a.price_min, a.is_free, a.photos_json
                    FROM rsvps r
                    INNER JOIN activities a ON r.activity_id = a.activity_id
                    WHERE r.user_id = ? AND r.status = ?
                    ORDER BY a.start_at ASC";
            return $this->read($sql, [$user_id, $status], 'is');
        } else {
            $sql = "SELECT r.*, a.*, a.title as activity_title, a.start_at, a.location_text, a.price_min, a.is_free, a.photos_json
                    FROM rsvps r
                    INNER JOIN activities a ON r.activity_id = a.activity_id
                    WHERE r.user_id = ?
                    ORDER BY a.start_at ASC";
            return $this->read($sql, [$user_id], 'i');
        }
    }
}

?>

