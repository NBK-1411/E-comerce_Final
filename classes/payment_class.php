<?php
/**
 * Payment Class - Data Access Layer
 */

require_once(__DIR__ . '/../settings/db_class.php');

class Payment extends db_connection {
    
    /**
     * Create payment record
     */
    public function create_payment($data) {
        // Use only columns that exist in the base payment table
        // Paystack-specific columns (currency, paystack_reference, channel, meta_json) 
        // will be added via ALTER TABLE and can be updated later
        $sql = "INSERT INTO payment (booking_id, user_id, amount, payment_method, payment_type, 
                transaction_ref, momo_number, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        // Store paystack_reference in transaction_ref if provided (until ALTER TABLE is run)
        $transaction_ref = $data['paystack_reference'] ?? $data['transaction_ref'] ?? null;
        
        $params = [
            $data['booking_id'],
            $data['user_id'],
            $data['amount'],
            $data['payment_method'] ?? 'paystack',
            $data['payment_type'] ?? 'deposit',
            $transaction_ref,
            $data['momo_number'] ?? null,
            $data['status'] ?? 'pending'
        ];
        
        return $this->write($sql, $params, 'iidsssss');
    }
    
    /**
     * Update payment status
     */
    public function update_payment_status($payment_id, $status) {
        $sql = "UPDATE payment SET status = ? WHERE payment_id = ?";
        return $this->write($sql, [$status, $payment_id], 'si');
    }
    
    /**
     * Set escrow release date
     */
    public function set_escrow_release_date($payment_id, $release_date) {
        $sql = "UPDATE payment SET escrow_release_date = ?, status = 'held_in_escrow' WHERE payment_id = ?";
        return $this->write($sql, [$release_date, $payment_id], 'si');
    }
    
    /**
     * Get payment by ID
     */
    public function get_payment_by_id($payment_id) {
        $sql = "SELECT p.*, b.venue_id, b.booking_date, v.title as venue_title 
                FROM payment p 
                LEFT JOIN booking b ON p.booking_id = b.booking_id 
                LEFT JOIN venue v ON b.venue_id = v.venue_id 
                WHERE p.payment_id = ?";
        $result = $this->read($sql, [$payment_id], 'i');
        return $result ? $result[0] : false;
    }
    
    /**
     * Get payment by transaction reference
     */
    public function get_payment_by_transaction_ref($transaction_ref) {
        $sql = "SELECT * FROM payment WHERE transaction_ref = ?";
        $result = $this->read($sql, [$transaction_ref], 's');
        return $result ? $result[0] : false;
    }
    
    /**
     * Get payment by Paystack reference
     */
    public function get_payment_by_paystack_ref($paystack_reference) {
        // Use transaction_ref until ALTER TABLE adds paystack_reference column
        $sql = "SELECT * FROM payment WHERE transaction_ref = ?";
        $result = $this->read($sql, [$paystack_reference], 's');
        return $result ? $result[0] : false;
    }
    
    /**
     * Update payment with Paystack data
     */
    public function update_payment_paystack($payment_id, $paystack_reference, $channel, $status, $meta_json = null) {
        // Update only columns that exist in the base table
        // Store paystack_reference in transaction_ref until ALTER TABLE adds paystack_reference column
        $sql = "UPDATE payment SET transaction_ref = ?, status = ?, updated_at = CURRENT_TIMESTAMP WHERE payment_id = ?";
        return $this->write($sql, [$paystack_reference, $status, $payment_id], 'ssi');
    }
    
    /**
     * Get payments by booking
     */
    public function get_payments_by_booking($booking_id) {
        $sql = "SELECT * FROM payment WHERE booking_id = ? ORDER BY payment_date DESC";
        return $this->read($sql, [$booking_id], 'i');
    }
    
    /**
     * Get payments by user
     */
    public function get_payments_by_user($user_id) {
        $sql = "SELECT p.*, b.booking_date, v.title as venue_title 
                FROM payment p 
                LEFT JOIN booking b ON p.booking_id = b.booking_id 
                LEFT JOIN venue v ON b.venue_id = v.venue_id 
                WHERE p.user_id = ? 
                ORDER BY p.payment_date DESC";
        return $this->read($sql, [$user_id], 'i');
    }
    
    /**
     * Get payments for venue owner
     */
    public function get_payments_by_venue_owner($owner_id) {
        $sql = "SELECT p.*, b.booking_date, v.title as venue_title, c.customer_name 
                FROM payment p 
                LEFT JOIN booking b ON p.booking_id = b.booking_id 
                LEFT JOIN venue v ON b.venue_id = v.venue_id 
                LEFT JOIN customer c ON p.user_id = c.customer_id 
                WHERE v.created_by = ? 
                ORDER BY p.payment_date DESC";
        return $this->read($sql, [$owner_id], 'i');
    }
    
    /**
     * Get payments ready for escrow release
     */
    public function get_payments_ready_for_release() {
        $sql = "SELECT p.*, b.booking_id, v.title as venue_title, v.created_by as venue_owner_id 
                FROM payment p 
                LEFT JOIN booking b ON p.booking_id = b.booking_id 
                LEFT JOIN venue v ON b.venue_id = v.venue_id 
                WHERE p.status = 'held_in_escrow' 
                AND p.escrow_release_date <= CURDATE()
                AND b.status = 'completed'";
        return $this->read($sql);
    }
    
    /**
     * Get all payments (admin)
     */
    public function get_all_payments() {
        $sql = "SELECT p.*, c.customer_name, v.title as venue_title 
                FROM payment p 
                LEFT JOIN customer c ON p.user_id = c.customer_id 
                LEFT JOIN booking b ON p.booking_id = b.booking_id 
                LEFT JOIN venue v ON b.venue_id = v.venue_id 
                ORDER BY p.payment_date DESC";
        return $this->read($sql);
    }
}

?>

