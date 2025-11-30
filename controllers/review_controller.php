<?php
/**
 * Review Controller - Business Logic Layer
 */

require_once(__DIR__ . '/../classes/review_class.php');

/**
 * Add new review
 */
function add_review_ctr($venue_id, $user_id, $booking_id, $rating, $review_text) {
    $review = new Review();
    
    // If booking_id provided, check if already reviewed
    if (!empty($booking_id)) {
        if ($review->has_user_reviewed_booking($booking_id)) {
            return false;
        }
    }
    
    return $review->add_review($venue_id, $user_id, $booking_id, $rating, $review_text);
}

/**
 * Update review status
 */
function update_review_status_ctr($review_id, $status) {
    $review = new Review();
    return $review->update_review_status($review_id, $status);
}

/**
 * Get review by ID
 */
function get_review_by_id_ctr($review_id) {
    $review = new Review();
    return $review->get_review_by_id($review_id);
}

/**
 * Get reviews by venue
 */
function get_reviews_by_venue_ctr($venue_id, $approved_only = true) {
    $review = new Review();
    return $review->get_reviews_by_venue($venue_id, $approved_only);
}

/**
 * Get reviews by user
 */
function get_reviews_by_user_ctr($user_id) {
    $review = new Review();
    return $review->get_reviews_by_user($user_id);
}

/**
 * Get pending reviews
 */
function get_pending_reviews_ctr() {
    $review = new Review();
    return $review->get_pending_reviews();
}

/**
 * Get flagged reviews
 */
function get_flagged_reviews_ctr() {
    $review = new Review();
    return $review->get_flagged_reviews();
}

/**
 * Get all reviews (admin)
 */
function get_all_reviews_ctr() {
    $review = new Review();
    return $review->get_all_reviews();
}

/**
 * Delete review
 */
function delete_review_ctr($review_id) {
    $review = new Review();
    return $review->delete_review($review_id);
}

/**
 * Report review
 */
function report_review_ctr($review_id, $reported_by, $reason) {
    $review = new Review();
    return $review->report_review($review_id, $reported_by, $reason);
}

/**
 * Get venue rating stats
 */
function get_venue_rating_stats_ctr($venue_id) {
    $review = new Review();
    return $review->get_venue_rating_stats($venue_id);
}

?>

