<?php
/**
 * Venue Controller - Business Logic Layer
 */

require_once(__DIR__ . '/../classes/venue_class.php');

/**
 * Create new venue
 */
function create_venue_ctr($data)
{
    $venue = new Venue();
    return $venue->create_venue($data);
}

/**
 * Update venue
 */
function update_venue_ctr($venue_id, $data)
{
    $venue = new Venue();
    return $venue->update_venue($venue_id, $data);
}

/**
 * Update venue photos
 */
function update_venue_photos_ctr($venue_id, $photos_json)
{
    $venue = new Venue();
    return $venue->update_venue_photos($venue_id, $photos_json);
}

/**
 * Update venue status
 */
function update_venue_status_ctr($venue_id, $status)
{
    $venue = new Venue();
    return $venue->update_venue_status($venue_id, $status);
}

/**
 * Get venue by ID
 */
function get_venue_by_id_ctr($venue_id)
{
    $venue = new Venue();
    return $venue->get_venue_by_id($venue_id);
}

/**
 * Get venue details (alias for get_venue_by_id)
 */
function get_venue_details_ctr($venue_id)
{
    return get_venue_by_id_ctr($venue_id);
}

/**
 * Get all approved venues
 */
function get_all_approved_venues_ctr()
{
    $venue = new Venue();
    return $venue->get_all_approved_venues();
}

/**
 * Get venues by owner
 */
function get_venues_by_owner_ctr($owner_id)
{
    $venue = new Venue();
    return $venue->get_venues_by_owner($owner_id);
}

/**
 * Search venues
 */
function search_venues_ctr($filters = [])
{
    $venue = new Venue();
    return $venue->search_venues($filters);
}

/**
 * Get all venues (admin)
 */
function get_all_venues_admin_ctr()
{
    $venue = new Venue();
    return $venue->get_all_venues_admin();
}

/**
 * Delete venue
 */
function delete_venue_ctr($venue_id)
{
    $venue = new Venue();
    return $venue->delete_venue($venue_id);
}

/**
 * Get venue statistics
 */
function get_venue_stats_ctr($venue_id)
{
    $venue = new Venue();
    return $venue->get_venue_stats($venue_id);
}

/**
 * Get upcoming activities - DEPRECATED: Use activity_controller.php instead
 * This function is kept for backward compatibility but should not be used
 */
function get_upcoming_activities_ctr_old($limit = 4)
{
    $venue = new Venue();
    return $venue->get_upcoming_activities($limit);
}

?>