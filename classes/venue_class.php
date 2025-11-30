<?php
/**
 * Venue Class - Data Access Layer
 */

require_once(__DIR__ . '/../settings/db_class.php');

class Venue extends db_connection
{

    /**
     * Create new venue
     */
    public function create_venue($data)
    {
        $sql = "INSERT INTO venue (title, description, cat_id, gps_code, location_text, capacity, 
                price_min, price_max, deposit_percentage, rules_json, safety_notes, parking_transport, 
                accessibility_info, photos_json, cancellation_policy, created_by, contact_phone, contact_email, booking_type) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        return $this->write($sql, [
            $data['title'],
            $data['description'],
            $data['cat_id'],
            $data['gps_code'],
            $data['location_text'],
            $data['capacity'],
            $data['price_min'],
            $data['price_max'],
            $data['deposit_percentage'],
            $data['rules_json'],
            $data['safety_notes'],
            $data['parking_transport'],
            $data['accessibility_info'],
            $data['photos_json'],
            $data['cancellation_policy'],
            $data['created_by'],
            $data['contact_phone'] ?? null,
            $data['contact_email'] ?? null,
            $data['booking_type'] ?? 'rent'
        ], 'ssissiddissssssiss');
    }

    /**
     * Update venue
     */
    public function update_venue($venue_id, $data)
    {
        // Check if contact fields exist in database
        $has_contact_fields = false;
        try {
            $connection = $this->db_conn();
            if ($connection) {
                $result = $connection->query("SHOW COLUMNS FROM venue LIKE 'contact_phone'");
                if ($result && $result->num_rows > 0) {
                    $has_contact_fields = true;
                }
                if ($result) {
                    $result->free();
                }
            }
        } catch (Exception $e) {
            // If check fails, assume columns don't exist
            $has_contact_fields = false;
        } catch (Error $e) {
            // Catch fatal errors too
            $has_contact_fields = false;
        }

        if ($has_contact_fields) {
            // Update with contact fields
            $sql = "UPDATE venue SET title = ?, description = ?, cat_id = ?, gps_code = ?, location_text = ?, 
                    capacity = ?, price_min = ?, price_max = ?, deposit_percentage = ?, rules_json = ?, 
                    safety_notes = ?, parking_transport = ?, accessibility_info = ?, cancellation_policy = ?, 
                    contact_phone = ?, contact_email = ?, booking_type = ? 
                    WHERE venue_id = ?";

            $contact_phone = isset($data['contact_phone']) && $data['contact_phone'] !== '' ? $data['contact_phone'] : '';
            $contact_email = isset($data['contact_email']) && $data['contact_email'] !== '' ? $data['contact_email'] : '';

            // Prepare parameters array
            $params = [
                $data['title'],
                $data['description'],
                $data['cat_id'],
                $data['gps_code'],
                $data['location_text'],
                $data['capacity'],
                $data['price_min'],
                $data['price_max'],
                $data['deposit_percentage'],
                $data['rules_json'],
                $data['safety_notes'] ?? '',
                $data['parking_transport'] ?? '',
                $data['accessibility_info'] ?? '',
                $data['cancellation_policy'],
                $contact_phone,
                $contact_email,
                $data['booking_type'] ?? 'rent',
                $venue_id
            ];

            return $this->write($sql, $params, 'ssissiddissssssssi');
        } else {
            // Update without contact fields (for databases that haven't been updated yet)
            $sql = "UPDATE venue SET title = ?, description = ?, cat_id = ?, gps_code = ?, location_text = ?, 
                    capacity = ?, price_min = ?, price_max = ?, deposit_percentage = ?, rules_json = ?, 
                    safety_notes = ?, parking_transport = ?, accessibility_info = ?, cancellation_policy = ?, 
                    booking_type = ? 
                    WHERE venue_id = ?";

            // Prepare parameters array with null-safe values
            $params = [
                $data['title'],
                $data['description'],
                $data['cat_id'],
                $data['gps_code'],
                $data['location_text'],
                $data['capacity'],
                $data['price_min'],
                $data['price_max'],
                $data['deposit_percentage'],
                $data['rules_json'],
                $data['safety_notes'] ?? '',
                $data['parking_transport'] ?? '',
                $data['accessibility_info'] ?? '',
                $data['cancellation_policy'],
                $data['booking_type'] ?? 'rent',
                $venue_id
            ];

            return $this->write($sql, $params, 'ssissiddissssssi');
        }
    }

    /**
     * Update venue photos
     */
    public function update_venue_photos($venue_id, $photos_json)
    {
        $sql = "UPDATE venue SET photos_json = ? WHERE venue_id = ?";
        return $this->write($sql, [$photos_json, $venue_id], 'si');
    }

    /**
     * Update venue status
     */
    public function update_venue_status($venue_id, $status)
    {
        $sql = "UPDATE venue SET status = ? WHERE venue_id = ?";
        return $this->write($sql, [$status, $venue_id], 'si');
    }

    /**
     * Get venue by ID
     */
    public function get_venue_by_id($venue_id)
    {
        $sql = "SELECT v.*, c.cat_name, cu.customer_name as owner_name, cu.customer_contact as owner_contact 
                FROM venue v 
                LEFT JOIN categories c ON v.cat_id = c.cat_id 
                LEFT JOIN customer cu ON v.created_by = cu.customer_id 
                WHERE v.venue_id = ?";
        $result = $this->read($sql, [$venue_id], 'i');
        return $result ? $result[0] : false;
    }

    /**
     * Get all approved venues
     */
    public function get_all_approved_venues()
    {
        $sql = "SELECT v.*, c.cat_name FROM venue v 
                LEFT JOIN categories c ON v.cat_id = c.cat_id 
                WHERE v.status = 'approved' 
                ORDER BY v.created_at DESC";
        return $this->read($sql);
    }

    /**
     * Get venues by owner
     */
    public function get_venues_by_owner($owner_id)
    {
        $sql = "SELECT v.*, c.cat_name FROM venue v 
                LEFT JOIN categories c ON v.cat_id = c.cat_id 
                WHERE v.created_by = ? 
                ORDER BY v.created_at DESC";
        return $this->read($sql, [$owner_id], 'i');
    }

    /**
     * Search venues with filters
     */
    public function search_venues($filters = [])
    {
        $sql = "SELECT v.*, c.cat_name, cu.verified as owner_verified FROM venue v 
                LEFT JOIN categories c ON v.cat_id = c.cat_id 
                LEFT JOIN customer cu ON v.created_by = cu.customer_id
                WHERE v.status = 'approved'";

        $params = [];
        $types = '';

        if (!empty($filters['category'])) {
            $sql .= " AND v.cat_id = ?";
            $params[] = $filters['category'];
            $types .= 'i';
        }

        if (!empty($filters['location'])) {
            $sql .= " AND (v.location_text LIKE ? OR v.gps_code LIKE ?)";
            $search_term = "%{$filters['location']}%";
            $params[] = $search_term;
            $params[] = $search_term;
            $types .= 'ss';
        }

        if (!empty($filters['min_price'])) {
            $sql .= " AND v.price_min >= ?";
            $params[] = $filters['min_price'];
            $types .= 'd';
        }

        if (!empty($filters['max_price'])) {
            $sql .= " AND v.price_max <= ?";
            $params[] = $filters['max_price'];
            $types .= 'd';
        }

        if (!empty($filters['min_capacity'])) {
            $sql .= " AND v.capacity >= ?";
            $params[] = $filters['min_capacity'];
            $types .= 'i';
        }

        if (!empty($filters['max_capacity'])) {
            $sql .= " AND v.capacity <= ?";
            $params[] = $filters['max_capacity'];
            $types .= 'i';
        }

        if (isset($filters['verified']) && $filters['verified'] == true) {
            $sql .= " AND cu.verified = 1";
        }

        if (isset($filters['parking']) && $filters['parking'] == true) {
            $sql .= " AND v.parking_transport LIKE '%parking%'";
        }

        if (isset($filters['accessibility']) && $filters['accessibility'] == true) {
            $sql .= " AND v.accessibility_info IS NOT NULL AND v.accessibility_info != ''";
        }

        if (!empty($filters['cancellation_policy'])) {
            $sql .= " AND v.cancellation_policy = ?";
            $params[] = $filters['cancellation_policy'];
            $types .= 's';
        }

        // Filter by Type (Venue vs Activity)
        if (!empty($filters['type'])) {
            if ($filters['type'] === 'activity') {
                // Activities are ticketed events or specific activity categories
                $sql .= " AND (v.booking_type = 'ticket' OR c.cat_name LIKE '%Activity%' OR c.cat_name LIKE '%Event%' OR c.cat_name LIKE '%Workshop%' OR c.cat_name LIKE '%Tour%')";
            } elseif ($filters['type'] === 'venue') {
                // Venues are for rent or reservation (restaurants, lounges, etc.)
                $sql .= " AND (v.booking_type IN ('rent', 'reservation') AND c.cat_name NOT LIKE '%Activity%' AND c.cat_name NOT LIKE '%Event%' AND c.cat_name NOT LIKE '%Workshop%' AND c.cat_name NOT LIKE '%Tour%')";
            }
        }

        // Search in description, title, or location
        if (!empty($filters['q'])) {
            $sql .= " AND (v.title LIKE ? OR v.description LIKE ? OR v.location_text LIKE ?)";
            $search_term = "%{$filters['q']}%";
            $params[] = $search_term;
            $params[] = $search_term;
            $params[] = $search_term;
            $types .= 'sss';
        }

        // Tag/vibe filtering (if venue_tags table exists)
        if (!empty($filters['tags']) && is_array($filters['tags'])) {
            $tag_placeholders = implode(',', array_fill(0, count($filters['tags']), '?'));
            $sql .= " AND v.venue_id IN (
                SELECT DISTINCT vtr.venue_id FROM venue_tag_relation vtr
                INNER JOIN venue_tags vt ON vtr.tag_id = vt.tag_id
                WHERE vt.tag_name IN ($tag_placeholders)
            )";
            foreach ($filters['tags'] as $tag) {
                $params[] = $tag;
                $types .= 's';
            }
        }

        // Order by featured first, then by creation date
        $sql .= " ORDER BY v.created_at DESC";

        return empty($params) ? $this->read($sql) : $this->read($sql, $params, $types);
    }

    /**
     * Get all venues for admin (all statuses)
     */
    public function get_all_venues_admin()
    {
        $sql = "SELECT v.*, c.cat_name, cu.customer_name as owner_name 
                FROM venue v 
                LEFT JOIN categories c ON v.cat_id = c.cat_id 
                LEFT JOIN customer cu ON v.created_by = cu.customer_id 
                ORDER BY v.created_at DESC";
        return $this->read($sql);
    }

    /**
     * Delete venue
     */
    public function delete_venue($venue_id)
    {
        $sql = "DELETE FROM venue WHERE venue_id = ?";
        return $this->write($sql, [$venue_id], 'i');
    }

    /**
     * Get venue statistics
     */
    public function get_venue_stats($venue_id)
    {
        $sql = "SELECT 
                (SELECT COUNT(*) FROM booking WHERE venue_id = ? AND status = 'completed') as total_bookings,
                (SELECT AVG(rating) FROM review WHERE venue_id = ?) as avg_rating,
                (SELECT COUNT(*) FROM review WHERE venue_id = ?) as total_reviews
                ";
        $result = $this->read($sql, [$venue_id, $venue_id, $venue_id], 'iii');
        return $result ? $result[0] : false;
    }
    /**
     * Get upcoming activities (venues with category 'Activity' or 'Event')
     */
    public function get_upcoming_activities($limit = 4)
    {
        $sql = "SELECT v.*, c.cat_name FROM venue v 
                LEFT JOIN categories c ON v.cat_id = c.cat_id 
                WHERE v.status = 'approved' 
                AND (c.cat_name LIKE '%Activity%' OR c.cat_name LIKE '%Event%' OR c.cat_name LIKE '%Workshop%' OR c.cat_name LIKE '%Tour%')
                ORDER BY v.created_at DESC LIMIT ?";
        return $this->read($sql, [$limit], 'i');
    }
}

?>