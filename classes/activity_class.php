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
        
        // Filter by recurrence type
        if (!empty($filters['recurrence_type'])) {
            $sql .= " AND a.recurrence_type = ?";
            $params[] = $filters['recurrence_type'];
            $types .= 's';
        }
        
        // For recurring activities, show them even if start date has passed
        // For one-time activities, only show if start date is in the future
        if (empty($filters['recurrence_type']) || $filters['recurrence_type'] === 'none') {
            // Only show upcoming one-time activities
            $sql .= " AND (a.recurrence_type = 'recurring' OR a.start_at >= NOW())";
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
    
    /**
     * Create new activity
     */
    public function create_activity($data) {
        $sql = "INSERT INTO activities (title, description, activity_type, recurrence_type, host_id, venue_id, location_text, gps_code, 
                start_at, end_at, capacity, price_min, price_max, is_free, photos_json, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $data['title'],
            $data['description'],
            $data['activity_type'],
            $data['recurrence_type'] ?? 'none',
            $data['host_id'],
            $data['venue_id'] ?? null,
            $data['location_text'],
            $data['gps_code'] ?? '',
            $data['start_at'],
            $data['end_at'] ?? null,
            $data['capacity'] ?? null,
            $data['price_min'] ?? 0.00,
            $data['price_max'] ?? 0.00,
            $data['is_free'] ?? 0,
            $data['photos_json'] ?? json_encode([]),
            $data['status'] ?? 'pending'
        ];
        
        return $this->write($sql, $params, 'ssssississddiisi');
    }
    
    /**
     * Get activities by host
     */
    public function get_activities_by_host($host_id) {
        $sql = "SELECT a.*, v.title as venue_title
                FROM activities a
                LEFT JOIN venue v ON a.venue_id = v.venue_id
                WHERE a.host_id = ?
                ORDER BY a.created_at DESC";
        
        return $this->read($sql, [$host_id], 'i');
    }
}

?>

