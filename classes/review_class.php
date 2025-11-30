<?php
/**
 * Review Class - Data Access Layer
 */

require_once(__DIR__ . '/../settings/db_class.php');

class Review extends db_connection {
    
    /**
     * Add new review
     */
    public function add_review($venue_id, $user_id, $booking_id, $rating, $review_text) {
        $is_verified = !empty($booking_id) ? 1 : 0;
        
        $sql = "INSERT INTO review (venue_id, user_id, booking_id, rating, review_text, is_verified) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        return $this->write($sql, [$venue_id, $user_id, $booking_id, $rating, $review_text, $is_verified], 'iiiisi');
    }
    
    /**
     * Update review moderation status
     */
    public function update_review_status($review_id, $status) {
        $sql = "UPDATE review SET moderation_status = ? WHERE review_id = ?";
        return $this->write($sql, [$status, $review_id], 'si');
    }
    
    /**
     * Increment report count
     */
    public function increment_report_count($review_id) {
        $sql = "UPDATE review SET report_count = report_count + 1 WHERE review_id = ?";
        return $this->write($sql, [$review_id], 'i');
    }
    
    /**
     * Get review by ID
     */
    public function get_review_by_id($review_id) {
        $sql = "SELECT r.*, c.customer_name, c.customer_image, v.title as venue_title 
                FROM review r 
                LEFT JOIN customer c ON r.user_id = c.customer_id 
                LEFT JOIN venue v ON r.venue_id = v.venue_id 
                WHERE r.review_id = ?";
        $result = $this->read($sql, [$review_id], 'i');
        return $result ? $result[0] : false;
    }
    
    /**
     * Get reviews by venue
     */
    public function get_reviews_by_venue($venue_id, $approved_only = true) {
        $sql = "SELECT r.*, c.customer_name, c.customer_image 
                FROM review r 
                LEFT JOIN customer c ON r.user_id = c.customer_id 
                WHERE r.venue_id = ?";
        
        if ($approved_only) {
            $sql .= " AND r.moderation_status = 'approved'";
        }
        
        $sql .= " ORDER BY r.is_verified DESC, r.created_at DESC";
        
        return $this->read($sql, [$venue_id], 'i');
    }
    
    /**
     * Get reviews by user
     */
    public function get_reviews_by_user($user_id) {
        $sql = "SELECT r.*, v.title as venue_title, v.venue_id 
                FROM review r 
                LEFT JOIN venue v ON r.venue_id = v.venue_id 
                WHERE r.user_id = ? 
                ORDER BY r.created_at DESC";
        return $this->read($sql, [$user_id], 'i');
    }
    
    /**
     * Check if user has already reviewed a booking
     */
    public function has_user_reviewed_booking($booking_id) {
        $sql = "SELECT COUNT(*) as count FROM review WHERE booking_id = ?";
        $result = $this->read($sql, [$booking_id], 'i');
        return $result && $result[0]['count'] > 0;
    }
    
    /**
     * Get pending reviews for moderation
     */
    public function get_pending_reviews() {
        $sql = "SELECT r.*, c.customer_name, v.title as venue_title 
                FROM review r 
                LEFT JOIN customer c ON r.user_id = c.customer_id 
                LEFT JOIN venue v ON r.venue_id = v.venue_id 
                WHERE r.moderation_status = 'pending' 
                ORDER BY r.created_at ASC";
        return $this->read($sql);
    }
    
    /**
     * Get flagged reviews
     */
    public function get_flagged_reviews() {
        $sql = "SELECT r.*, c.customer_name, v.title as venue_title 
                FROM review r 
                LEFT JOIN customer c ON r.user_id = c.customer_id 
                LEFT JOIN venue v ON r.venue_id = v.venue_id 
                WHERE r.moderation_status = 'flagged' OR r.report_count > 0
                ORDER BY r.report_count DESC, r.created_at DESC";
        return $this->read($sql);
    }
    
    /**
     * Get all reviews (admin)
     */
    public function get_all_reviews() {
        $sql = "SELECT r.*, c.customer_name, v.title as venue_title 
                FROM review r 
                LEFT JOIN customer c ON r.user_id = c.customer_id 
                LEFT JOIN venue v ON r.venue_id = v.venue_id 
                ORDER BY r.created_at DESC";
        return $this->read($sql);
    }
    
    /**
     * Delete review
     */
    public function delete_review($review_id) {
        $sql = "DELETE FROM review WHERE review_id = ?";
        return $this->write($sql, [$review_id], 'i');
    }
    
    /**
     * Report review
     */
    public function report_review($review_id, $reported_by, $reason) {
        $sql = "INSERT INTO review_report (review_id, reported_by, reason) VALUES (?, ?, ?)";
        $result = $this->write($sql, [$review_id, $reported_by, $reason], 'iis');
        
        if ($result) {
            $this->increment_report_count($review_id);
        }
        
        return $result;
    }
    
    /**
     * Get venue rating statistics
     */
    public function get_venue_rating_stats($venue_id) {
        $sql = "SELECT 
                COUNT(*) as total_reviews,
                AVG(rating) as avg_rating,
                SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
                SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
                SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star,
                SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star,
                SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star
                FROM review 
                WHERE venue_id = ? AND moderation_status = 'approved'";
        $result = $this->read($sql, [$venue_id], 'i');
        return $result ? $result[0] : false;
    }
}

?>

