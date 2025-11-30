<?php
/**
 * Activity Controller - Business Logic Layer
 */

require_once(__DIR__ . '/../classes/activity_class.php');

/**
 * Get all approved activities
 */
function get_all_approved_activities_ctr() {
    $activity = new Activity();
    return $activity->get_all_approved_activities();
}

/**
 * Get upcoming activities
 */
function get_upcoming_activities_ctr($limit = 50) {
    $activity = new Activity();
    return $activity->get_upcoming_activities($limit);
}

/**
 * Search activities
 */
function search_activities_ctr($filters = []) {
    $activity = new Activity();
    return $activity->search_activities($filters);
}

/**
 * Get activity by ID
 */
function get_activity_by_id_ctr($activity_id) {
    $activity = new Activity();
    return $activity->get_activity_by_id($activity_id);
}

?>

