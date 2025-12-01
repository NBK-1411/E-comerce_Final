<?php
/**
 * RSVP Controller - Business Logic Layer
 */

require_once(__DIR__ . '/../classes/rsvp_class.php');

/**
 * Create or update RSVP
 */
function upsert_rsvp_ctr($activity_id, $user_id, $status = 'going', $guest_count = 1) {
    $rsvp = new Rsvp();
    return $rsvp->upsert_rsvp($activity_id, $user_id, $status, $guest_count);
}

/**
 * Get RSVP by user and activity
 */
function get_rsvp_ctr($activity_id, $user_id) {
    $rsvp = new Rsvp();
    return $rsvp->get_rsvp($activity_id, $user_id);
}

/**
 * Delete RSVP
 */
function delete_rsvp_ctr($activity_id, $user_id) {
    $rsvp = new Rsvp();
    return $rsvp->delete_rsvp($activity_id, $user_id);
}

/**
 * Get RSVPs for activity
 */
function get_activity_rsvps_ctr($activity_id, $status = null) {
    $rsvp = new Rsvp();
    return $rsvp->get_activity_rsvps($activity_id, $status);
}

/**
 * Get RSVP counts for activity
 */
function get_rsvp_counts_ctr($activity_id) {
    $rsvp = new Rsvp();
    return $rsvp->get_rsvp_counts($activity_id);
}

/**
 * Get user's RSVPs
 */
function get_user_rsvps_ctr($user_id, $status = null) {
    $rsvp = new Rsvp();
    return $rsvp->get_user_rsvps($user_id, $status);
}

?>

