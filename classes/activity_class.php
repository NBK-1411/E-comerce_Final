<?php
/**
 * Activity Class - Data Access Layer
 * Handles activities (concerts, pop-ups, festivals, classes, etc.)
 */

require_once(__DIR__ . '/../settings/db_class.php');

class Activity extends db_connection {
    
    /**
     * Get all approved activities
     */
    public function get_all_approved_activities() {
        $sql = "SELECT a.*, c.customer_name as host_name
                FROM activities a
                LEFT JOIN customer c ON a.host_id = c.customer_id
                WHERE a.status = 'approved'
                ORDER BY a.start_at ASC";
        
        return $this->read($sql);
    }
    
    /**
     * Get upcoming activities (approved, future dates)
     */
    public function get_upcoming_activities($limit = 50) {
        $sql = "SELECT a.*, c.customer_name as host_name
                FROM activities a
                LEFT JOIN customer c ON a.host_id = c.customer_id
                WHERE a.status = 'approved' AND a.start_at >= NOW()
                ORDER BY a.start_at ASC
                LIMIT ?";
        
        return $this->read($sql, [$limit], 'i');
    }
    
    /**
     * Search activities with filters
     */
    public function search_activities($filters = []) {
        $sql = "SELECT a.*, c.customer_name as host_name
                FROM activities a
                LEFT JOIN customer c ON a.host_id = c.customer_id
                WHERE a.status = 'approved'";
        
        $params = [];
        $types = '';
        
        if (!empty($filters['activity_type'])) {
            $sql .= " AND a.activity_type = ?";
            $params[] = $filters['activity_type'];
            $types .= 's';
        }
        
        if (!empty($filters['location'])) {
            $sql .= " AND (a.location_text LIKE ? OR a.gps_code LIKE ?)";
            $search_term = "%{$filters['location']}%";
            $params[] = $search_term;
            $params[] = $search_term;
            $types .= 'ss';
        }
        
        if (!empty($filters['start_date'])) {
            $sql .= " AND DATE(a.start_at) >= ?";
            $params[] = $filters['start_date'];
            $types .= 's';
        }
        
        if (!empty($filters['is_free'])) {
            $sql .= " AND a.is_free = 1";
        }
        
        if (!empty($filters['max_price'])) {
            $sql .= " AND a.price_min <= ?";
            $params[] = $filters['max_price'];
            $types .= 'd';
        }
        
        $sql .= " ORDER BY a.start_at ASC";
        
        return empty($params) ? $this->read($sql) : $this->read($sql, $params, $types);
    }
    
    /**
     * Get activity by ID
     */
    public function get_activity_by_id($activity_id) {
        $sql = "SELECT a.*, c.customer_name as host_name, c.customer_email as host_email,
                v.title as venue_title, v.location_text as venue_location
                FROM activities a
                LEFT JOIN customer c ON a.host_id = c.customer_id
                LEFT JOIN venue v ON a.venue_id = v.venue_id
                WHERE a.activity_id = ?";
        
        $result = $this->read($sql, [$activity_id], 'i');
        return $result ? $result[0] : false;
    }
}

?>

