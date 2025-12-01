<?php
/**
 * Category Class - Data Access Layer
 */

require_once(__DIR__ . '/../settings/db_class.php');

class Category extends db_connection {
    
    /**
     * Add new category
     */
    public function add_category($name, $description = '', $icon = '') {
        $sql = "INSERT INTO categories (cat_name, cat_description, cat_icon) VALUES (?, ?, ?)";
        return $this->write($sql, [$name, $description, $icon], 'sss');
    }
    
    /**
     * Update category
     */
    public function update_category($cat_id, $name, $description = '', $icon = '') {
        $sql = "UPDATE categories SET cat_name = ?, cat_description = ?, cat_icon = ? WHERE cat_id = ?";
        return $this->write($sql, [$name, $description, $icon, $cat_id], 'sssi');
    }
    
    /**
     * Delete category
     */
    public function delete_category($cat_id) {
        $sql = "DELETE FROM categories WHERE cat_id = ?";
        return $this->write($sql, [$cat_id], 'i');
    }
    
    /**
     * Get all categories
     */
    public function get_all_categories() {
        $sql = "SELECT * FROM categories ORDER BY cat_name ASC";
        return $this->read($sql);
    }
    
    /**
     * Get category by ID
     */
    public function get_category_by_id($cat_id) {
        $sql = "SELECT * FROM categories WHERE cat_id = ?";
        $result = $this->read($sql, [$cat_id], 'i');
        return $result ? $result[0] : false;
    }
    
    /**
     * Get category by name
     */
    public function get_category_by_name($name) {
        $sql = "SELECT * FROM categories WHERE cat_name = ?";
        $result = $this->read($sql, [$name], 's');
        return $result ? $result[0] : false;
    }
    
    /**
     * Get categories with venue count
     */
    public function get_categories_with_venue_count() {
        // Exclude activity-only categories (those we tagged via description prefix)
        $sql = "SELECT c.*, COUNT(v.venue_id) as venue_count 
                FROM categories c 
                LEFT JOIN venue v ON c.cat_id = v.cat_id AND v.status = 'approved'
                WHERE c.cat_description NOT LIKE '__ACTIVITY__ %' OR c.cat_description IS NULL
                GROUP BY c.cat_id 
                ORDER BY c.cat_name ASC";
        return $this->read($sql);
    }
}

?>

