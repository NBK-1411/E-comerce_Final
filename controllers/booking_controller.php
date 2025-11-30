<?php
/**
 * Booking Controller - Business Logic Layer
 */

require_once(__DIR__ . '/../classes/booking_class.php');

/**
 * Create new booking
 */
function create_booking_ctr($data) {
    $booking = new Booking();
    
    // Check availability first
    $is_available = $booking->check_venue_availability(
        $data['venue_id'],
        $data['booking_date'],
        $data['start_time'],
        $data['end_time']
    );
    
    if (!$is_available) {
        return false;
    }
    
    $result = $booking->create_booking($data);
    
    // Ensure we return the booking ID (not true/false)
    // If result is true, it means insert_id was 0, which shouldn't happen for auto-increment
    if ($result === true) {
        error_log("Warning: create_booking returned true instead of booking_id. Data: " . json_encode($data));
        return false; // Return false to indicate failure
    }
    
    return $result;
}

/**
 * Update booking status
 */
function update_booking_status_ctr($booking_id, $status) {
    $booking = new Booking();
    return $booking->update_booking_status($booking_id, $status);
}

/**
 * Cancel booking
 */
function cancel_booking_ctr($booking_id, $reason) {
    $booking = new Booking();
    return $booking->cancel_booking($booking_id, $reason);
}

/**
 * Get booking by ID
 */
function get_booking_by_id_ctr($booking_id) {
    $booking = new Booking();
    return $booking->get_booking_by_id($booking_id);
}

/**
 * Get booking by QR
 */
function get_booking_by_qr_ctr($qr_reference) {
    $booking = new Booking();
    return $booking->get_booking_by_qr($qr_reference);
}

/**
 * Get user bookings
 */
function get_user_bookings_ctr($user_id) {
    $booking = new Booking();
    return $booking->get_user_bookings($user_id);
}

/**
 * Get venue bookings
 */
function get_venue_bookings_ctr($venue_id) {
    $booking = new Booking();
    return $booking->get_venue_bookings($venue_id);
}

/**
 * Get bookings by owner
 */
function get_bookings_by_owner_ctr($owner_id) {
    $booking = new Booking();
    return $booking->get_bookings_by_owner($owner_id);
}

/**
 * Get upcoming bookings
 */
function get_upcoming_bookings_ctr($user_id) {
    $booking = new Booking();
    return $booking->get_upcoming_bookings($user_id);
}

/**
 * Get past bookings
 */
function get_past_bookings_ctr($user_id) {
    $booking = new Booking();
    return $booking->get_past_bookings($user_id);
}

/**
 * Check venue availability
 */
function check_venue_availability_ctr($venue_id, $date, $start_time, $end_time) {
    $booking = new Booking();
    return $booking->check_venue_availability($venue_id, $date, $start_time, $end_time);
}

/**
 * Get all bookings (admin)
 */
function get_all_bookings_ctr() {
    $booking = new Booking();
    return $booking->get_all_bookings();
}

?>

