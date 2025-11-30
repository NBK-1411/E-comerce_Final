<?php
/**
 * Payment Controller - Business Logic Layer
 */

require_once(__DIR__ . '/../classes/payment_class.php');

/**
 * Create payment record
 */
function create_payment_ctr($data) {
    $payment = new Payment();
    return $payment->create_payment($data);
}

/**
 * Update payment status
 */
function update_payment_status_ctr($payment_id, $status) {
    $payment = new Payment();
    return $payment->update_payment_status($payment_id, $status);
}

/**
 * Set escrow release date
 */
function set_escrow_release_date_ctr($payment_id, $release_date) {
    $payment = new Payment();
    return $payment->set_escrow_release_date($payment_id, $release_date);
}

/**
 * Get payment by ID
 */
function get_payment_by_id_ctr($payment_id) {
    $payment = new Payment();
    return $payment->get_payment_by_id($payment_id);
}

/**
 * Get payment by transaction reference
 */
function get_payment_by_transaction_ref_ctr($transaction_ref) {
    $payment = new Payment();
    return $payment->get_payment_by_transaction_ref($transaction_ref);
}

/**
 * Get payments by booking
 */
function get_payments_by_booking_ctr($booking_id) {
    $payment = new Payment();
    return $payment->get_payments_by_booking($booking_id);
}

/**
 * Get payments by user
 */
function get_payments_by_user_ctr($user_id) {
    $payment = new Payment();
    return $payment->get_payments_by_user($user_id);
}

/**
 * Get payments by venue owner
 */
function get_payments_by_venue_owner_ctr($owner_id) {
    $payment = new Payment();
    return $payment->get_payments_by_venue_owner($owner_id);
}

/**
 * Get payments ready for release
 */
function get_payments_ready_for_release_ctr() {
    $payment = new Payment();
    return $payment->get_payments_ready_for_release();
}

/**
 * Get all payments (admin)
 */
function get_all_payments_ctr() {
    $payment = new Payment();
    return $payment->get_all_payments();
}

/**
 * Get payment by Paystack reference
 */
function get_payment_by_paystack_ref_ctr($paystack_reference) {
    $payment = new Payment();
    return $payment->get_payment_by_paystack_ref($paystack_reference);
}

/**
 * Update payment with Paystack data
 */
function update_payment_paystack_ctr($payment_id, $paystack_reference, $channel, $status, $meta_json = null) {
    $payment = new Payment();
    return $payment->update_payment_paystack($payment_id, $paystack_reference, $channel, $status, $meta_json);
}

?>

