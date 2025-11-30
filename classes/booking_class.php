<?php
/**
 * Booking Class - Data Access Layer
 */

require_once(__DIR__ . '/../settings/db_class.php');

class Booking extends db_connection {
    
    /**
     * Create new booking request
     */
    public function create_booking($data) {
        $sql = "INSERT INTO booking (user_id, venue_id, booking_date, start_time, end_time, guest_count, 
                total_amount, deposit_amount, qr_reference, special_requests, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'requested')";
        
        return $this->write($sql, [
            $data['user_id'],
            $data['venue_id'],
            $data['booking_date'],
            $data['start_time'],
            $data['end_time'],
            $data['guest_count'],
            $data['total_amount'],
            $data['deposit_amount'],
            $data['qr_reference'],
            $data['special_requests']
        ], 'iisssiddss');
    }
    
    /**
     * Update booking status
     */
    public function update_booking_status($booking_id, $status) {
        $sql = "UPDATE booking SET status = ? WHERE booking_id = ?";
        return $this->write($sql, [$status, $booking_id], 'si');
    }
    
    /**
     * Cancel booking
     */
    public function cancel_booking($booking_id, $reason) {
        $sql = "UPDATE booking SET status = 'cancelled', cancellation_reason = ? WHERE booking_id = ?";
        return $this->write($sql, [$reason, $booking_id], 'si');
    }
    
    /**
     * Get booking by ID
     */
    public function get_booking_by_id($booking_id) {
        $sql = "SELECT b.*, v.title as venue_title, v.location_text, v.gps_code, 
                c.customer_name, c.customer_email, c.customer_contact,
                v.created_by as venue_owner_id
                FROM booking b 
                LEFT JOIN venue v ON b.venue_id = v.venue_id 
                LEFT JOIN customer c ON b.user_id = c.customer_id 
                WHERE b.booking_id = ?";
        $result = $this->read($sql, [$booking_id], 'i');
        return $result ? $result[0] : false;
    }
    
    /**
     * Get booking by QR reference
     */
    public function get_booking_by_qr($qr_reference) {
        $sql = "SELECT b.*, v.title as venue_title, v.location_text, 
                c.customer_name, c.customer_email 
                FROM booking b 
                LEFT JOIN venue v ON b.venue_id = v.venue_id 
                LEFT JOIN customer c ON b.user_id = c.customer_id 
                WHERE b.qr_reference = ?";
        $result = $this->read($sql, [$qr_reference], 's');
        return $result ? $result[0] : false;
    }
    
    /**
     * Get user bookings
     */
    public function get_user_bookings($user_id) {
        $sql = "SELECT b.*, v.title as venue_title, v.location_text, v.photos_json 
                FROM booking b 
                LEFT JOIN venue v ON b.venue_id = v.venue_id 
                WHERE b.user_id = ? 
                ORDER BY b.booking_date DESC, b.created_at DESC";
        return $this->read($sql, [$user_id], 'i');
    }
    
    /**
     * Get venue bookings (for venue owner)
     */
    public function get_venue_bookings($venue_id) {
        $sql = "SELECT b.*, c.customer_name, c.customer_email, c.customer_contact 
                FROM booking b 
                LEFT JOIN customer c ON b.user_id = c.customer_id 
                WHERE b.venue_id = ? 
                ORDER BY b.booking_date DESC, b.created_at DESC";
        return $this->read($sql, [$venue_id], 'i');
    }
    
    /**
     * Get all bookings by venue owner
     */
    public function get_bookings_by_owner($owner_id) {
        $sql = "SELECT b.*, v.title as venue_title, c.customer_name, c.customer_email 
                FROM booking b 
                LEFT JOIN venue v ON b.venue_id = v.venue_id 
                LEFT JOIN customer c ON b.user_id = c.customer_id 
                WHERE v.created_by = ? 
                ORDER BY b.booking_date DESC, b.created_at DESC";
        return $this->read($sql, [$owner_id], 'i');
    }
    
    /**
     * Get upcoming bookings for user
     */
    public function get_upcoming_bookings($user_id) {
        $sql = "SELECT b.*, v.title as venue_title, v.location_text, v.gps_code 
                FROM booking b 
                LEFT JOIN venue v ON b.venue_id = v.venue_id 
                WHERE b.user_id = ? AND b.booking_date >= CURDATE() 
                AND b.status IN ('requested', 'confirmed')
                ORDER BY b.booking_date ASC, b.start_time ASC";
        return $this->read($sql, [$user_id], 'i');
    }
    
    /**
     * Get past bookings for user
     */
    public function get_past_bookings($user_id) {
        $sql = "SELECT b.*, v.title as venue_title, v.venue_id 
                FROM booking b 
                LEFT JOIN venue v ON b.venue_id = v.venue_id 
                WHERE b.user_id = ? AND (b.booking_date < CURDATE() OR b.status = 'completed')
                ORDER BY b.booking_date DESC";
        return $this->read($sql, [$user_id], 'i');
    }
    
    /**
     * Check venue availability
     */
    public function check_venue_availability($venue_id, $date, $start_time, $end_time) {
        $sql = "SELECT COUNT(*) as count FROM booking 
                WHERE venue_id = ? AND booking_date = ? 
                AND status IN ('requested', 'confirmed')
                AND (
                    (start_time <= ? AND end_time > ?) OR
                    (start_time < ? AND end_time >= ?) OR
                    (start_time >= ? AND end_time <= ?)
                )";
        $result = $this->read($sql, [$venue_id, $date, $start_time, $start_time, $end_time, $end_time, $start_time, $end_time], 'isssssss');
        return $result && $result[0]['count'] == 0;
    }
    
    /**
     * Get all bookings (admin)
     */
    public function get_all_bookings() {
        $sql = "SELECT b.*, v.title as venue_title, c.customer_name 
                FROM booking b 
                LEFT JOIN venue v ON b.venue_id = v.venue_id 
                LEFT JOIN customer c ON b.user_id = c.customer_id 
                ORDER BY b.created_at DESC";
        return $this->read($sql);
    }
}

?>

