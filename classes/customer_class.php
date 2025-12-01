<?php
/**
 * Customer Class - Data Access Layer
 */

require_once(__DIR__ . '/../settings/db_class.php');

class Customer extends db_connection {
    
    /**
     * Register new customer
     */
    public function register_customer($name, $email, $password, $country, $city, $contact, $role = 2) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO customer (customer_name, customer_email, customer_pass, customer_country, customer_city, customer_contact, user_role) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        return $this->write($sql, [$name, $email, $hashed_password, $country, $city, $contact, $role], 'ssssssi');
    }
    
    /**
     * Get customer by email
     */
    public function get_customer_by_email($email) {
        $sql = "SELECT * FROM customer WHERE customer_email = ?";
        $result = $this->read($sql, [$email], 's');
        
        return $result ? $result[0] : false;
    }
    
    /**
     * Get customer by ID
     */
    public function get_customer_by_id($customer_id) {
        $sql = "SELECT * FROM customer WHERE customer_id = ?";
        $result = $this->read($sql, [$customer_id], 'i');
        
        return $result ? $result[0] : false;
    }
    
    /**
     * Update customer profile
     */
    public function update_customer($customer_id, $name, $country, $city, $contact) {
        $sql = "UPDATE customer SET customer_name = ?, customer_country = ?, customer_city = ?, customer_contact = ? 
                WHERE customer_id = ?";
        
        return $this->write($sql, [$name, $country, $city, $contact, $customer_id], 'ssssi');
    }
    
    /**
     * Update customer image
     */
    public function update_customer_image($customer_id, $image_path) {
        $sql = "UPDATE customer SET customer_image = ? WHERE customer_id = ?";
        return $this->write($sql, [$image_path, $customer_id], 'si');
    }
    
    /**
     * Verify customer email
     */
    public function verify_customer($customer_id) {
        $sql = "UPDATE customer SET verified = 1 WHERE customer_id = ?";
        return $this->write($sql, [$customer_id], 'i');
    }
    
    /**
     * Update Ghana Card for KYC
     */
    public function update_ghana_card($customer_id, $ghana_card) {
        $sql = "UPDATE customer SET ghana_card = ? WHERE customer_id = ?";
        return $this->write($sql, [$ghana_card, $customer_id], 'si');
    }
    
    /**
     * Get all venue owners
     */
    public function get_venue_owners() {
        $sql = "SELECT customer_id, customer_name, customer_email, customer_city, customer_contact, verified, created_at 
                FROM customer WHERE user_role = 3 ORDER BY created_at DESC";
        return $this->read($sql);
    }
    
    /**
     * Get all customers
     */
    public function get_all_customers() {
        $sql = "SELECT customer_id, customer_name, customer_email, customer_country, customer_city, customer_contact, customer_image, user_role, verified, created_at 
                FROM customer ORDER BY created_at DESC";
        return $this->read($sql);
    }
    
    /**
     * Delete customer (only non-admin users)
     */
    public function delete_customer($customer_id) {
        // First check if user is admin (prevent deletion of admins)
        $check_sql = "SELECT user_role FROM customer WHERE customer_id = ?";
        $result = $this->read($check_sql, [$customer_id], 'i');
        
        if ($result && $result[0]['user_role'] == 1) {
            return false; // Cannot delete admin
        }
        
        $sql = "DELETE FROM customer WHERE customer_id = ? AND user_role != 1";
        return $this->write($sql, [$customer_id], 'i');
    }
    /**
     * Get or create default collection for a customer
     */
    private function get_or_create_default_collection($customer_id) {
        // Check if user has a default "Saved" collection
        $sql = "SELECT collection_id FROM collections WHERE owner_id = ? AND title = 'Saved' LIMIT 1";
        $result = $this->read($sql, [$customer_id], 'i');
        
        if ($result && !empty($result)) {
            return $result[0]['collection_id'];
        }
        
        // Create default collection
        $sql = "INSERT INTO collections (owner_id, title, description, visibility) VALUES (?, 'Saved', 'My saved venues and activities', 'private')";
        $collection_id = $this->write($sql, [$customer_id], 'i');
        
        return $collection_id;
    }

    /**
     * Save a venue to user's default collection
     */
    public function save_venue($customer_id, $venue_id) {
        // Get or create default collection
        $collection_id = $this->get_or_create_default_collection($customer_id);
        
        if (!$collection_id) {
            return false;
        }
        
        // Check if already in collection
        $check_sql = "SELECT item_id FROM collection_items 
                     WHERE collection_id = ? AND item_type = 'venue' AND item_reference_id = ?";
        $exists = $this->read($check_sql, [$collection_id, $venue_id], 'ii');
        
        if ($exists && !empty($exists)) {
            return true; // Already saved
        }
        
        // Add to collection
        $sql = "INSERT INTO collection_items (collection_id, item_type, item_reference_id) VALUES (?, 'venue', ?)";
        return $this->write($sql, [$collection_id, $venue_id], 'ii');
    }

    /**
     * Unsave a venue (remove from all user's collections)
     */
    public function unsave_venue($customer_id, $venue_id) {
        $sql = "DELETE ci FROM collection_items ci
                INNER JOIN collections c ON ci.collection_id = c.collection_id
                WHERE c.owner_id = ? AND ci.item_type = 'venue' AND ci.item_reference_id = ?";
        return $this->write($sql, [$customer_id, $venue_id], 'ii');
    }

    /**
     * Check if venue is saved in any of user's collections
     */
    public function is_venue_saved($customer_id, $venue_id) {
        $sql = "SELECT ci.item_id FROM collection_items ci
                INNER JOIN collections c ON ci.collection_id = c.collection_id
                WHERE c.owner_id = ? AND ci.item_type = 'venue' AND ci.item_reference_id = ?
                LIMIT 1";
        $result = $this->read($sql, [$customer_id, $venue_id], 'ii');
        return $result && !empty($result) ? true : false;
    }

    /**
     * Get all saved venues for a customer (from all their collections)
     */
    public function get_saved_venues($customer_id) {
        $sql = "SELECT DISTINCT v.*, c.cat_name, ci.added_at as saved_at 
                FROM collection_items ci
                INNER JOIN collections col ON ci.collection_id = col.collection_id
                INNER JOIN venue v ON ci.item_reference_id = v.venue_id
                LEFT JOIN categories c ON v.cat_id = c.cat_id
                WHERE col.owner_id = ? AND ci.item_type = 'venue'
                ORDER BY ci.added_at DESC";
        return $this->read($sql, [$customer_id], 'i');
    }

    /**
     * Save an activity to user's default collection
     */
    public function save_activity($customer_id, $activity_id) {
        // Get or create default collection
        $collection_id = $this->get_or_create_default_collection($customer_id);
        
        if (!$collection_id) {
            return false;
        }
        
        // Check if already in collection
        $check_sql = "SELECT item_id FROM collection_items 
                     WHERE collection_id = ? AND item_type = 'activity' AND item_reference_id = ?";
        $exists = $this->read($check_sql, [$collection_id, $activity_id], 'ii');
        
        if ($exists && !empty($exists)) {
            return true; // Already saved
        }
        
        // Add to collection
        $sql = "INSERT INTO collection_items (collection_id, item_type, item_reference_id) VALUES (?, 'activity', ?)";
        return $this->write($sql, [$collection_id, $activity_id], 'ii');
    }

    /**
     * Unsave an activity (remove from all user's collections)
     */
    public function unsave_activity($customer_id, $activity_id) {
        $sql = "DELETE ci FROM collection_items ci
                INNER JOIN collections c ON ci.collection_id = c.collection_id
                WHERE c.owner_id = ? AND ci.item_type = 'activity' AND ci.item_reference_id = ?";
        return $this->write($sql, [$customer_id, $activity_id], 'ii');
    }

    /**
     * Check if activity is saved in any of user's collections
     */
    public function is_activity_saved($customer_id, $activity_id) {
        $sql = "SELECT ci.item_id FROM collection_items ci
                INNER JOIN collections c ON ci.collection_id = c.collection_id
                WHERE c.owner_id = ? AND ci.item_type = 'activity' AND ci.item_reference_id = ?
                LIMIT 1";
        $result = $this->read($sql, [$customer_id, $activity_id], 'ii');
        return $result && !empty($result) ? true : false;
    }

    /**
     * Get all saved activities for a customer (from all their collections)
     */
    public function get_saved_activities($customer_id) {
        $sql = "SELECT DISTINCT a.*, ci.added_at as saved_at 
                FROM collection_items ci
                INNER JOIN collections col ON ci.collection_id = col.collection_id
                INNER JOIN activities a ON ci.item_reference_id = a.activity_id
                WHERE col.owner_id = ? AND ci.item_type = 'activity'
                ORDER BY ci.added_at DESC";
        return $this->read($sql, [$customer_id], 'i');
    }
}

?>

