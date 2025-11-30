<?php
/**
 * Category Controller - Business Logic Layer
 */

require_once(__DIR__ . '/../classes/category_class.php');

/**
 * Add new category
 */
function add_category_ctr($name, $description = '', $icon = '') {
    $category = new Category();
    
    // Check if category name already exists
    $existing = $category->get_category_by_name($name);
    if ($existing) {
        return false;
    }
    
    return $category->add_category($name, $description, $icon);
}

/**
 * Update category
 */
function update_category_ctr($cat_id, $name, $description = '', $icon = '') {
    $category = new Category();
    
    // Check if another category has the same name
    $existing = $category->get_category_by_name($name);
    if ($existing && $existing['cat_id'] != $cat_id) {
        return false;
    }
    
    return $category->update_category($cat_id, $name, $description, $icon);
}

/**
 * Delete category
 */
function delete_category_ctr($cat_id) {
    $category = new Category();
    return $category->delete_category($cat_id);
}

/**
 * Get all categories
 */
function get_all_categories_ctr() {
    $category = new Category();
    return $category->get_all_categories();
}

/**
 * Get category by ID
 */
function get_category_by_id_ctr($cat_id) {
    $category = new Category();
    return $category->get_category_by_id($cat_id);
}

/**
 * Get categories with venue count
 */
function get_categories_with_venue_count_ctr() {
    $category = new Category();
    return $category->get_categories_with_venue_count();
}

?>

