<?php
/**
 * Customer Controller - Business Logic Layer
 */

require_once(__DIR__ . '/../classes/customer_class.php');

/**
 * Register a new customer
 */
function register_customer_ctr($name, $email, $password, $country, $city, $contact, $role = 2)
{
    $customer = new Customer();

    // Check if email already exists
    $existing = $customer->get_customer_by_email($email);
    if ($existing) {
        return false;
    }

    return $customer->register_customer($name, $email, $password, $country, $city, $contact, $role);
}

/**
 * Login customer
 */
function login_customer_ctr($email, $password)
{
    $customer = new Customer();
    $user = $customer->get_customer_by_email($email);

    if (!$user) {
        return false;
    }

    if (password_verify($password, $user['customer_pass'])) {
        return $user;
    }

    return false;
}

/**
 * Get customer by ID
 */
function get_customer_by_id_ctr($customer_id)
{
    $customer = new Customer();
    return $customer->get_customer_by_id($customer_id);
}

/**
 * Update customer profile
 */
function update_customer_ctr($customer_id, $name, $country, $city, $contact)
{
    $customer = new Customer();
    return $customer->update_customer($customer_id, $name, $country, $city, $contact);
}

/**
 * Update customer image
 */
function update_customer_image_ctr($customer_id, $image_path)
{
    $customer = new Customer();
    return $customer->update_customer_image($customer_id, $image_path);
}

/**
 * Verify customer
 */
function verify_customer_ctr($customer_id)
{
    $customer = new Customer();
    return $customer->verify_customer($customer_id);
}

/**
 * Update Ghana Card
 */
function update_ghana_card_ctr($customer_id, $ghana_card)
{
    $customer = new Customer();
    return $customer->update_ghana_card($customer_id, $ghana_card);
}

/**
 * Get all venue owners
 */
function get_venue_owners_ctr()
{
    $customer = new Customer();
    return $customer->get_venue_owners();
}

/**
 * Get all customers
 */
function get_all_customers_ctr()
{
    $customer = new Customer();
    return $customer->get_all_customers();
}

/**
 * Delete customer (only non-admin users)
 */
function delete_customer_ctr($customer_id)
{
    $customer = new Customer();
    return $customer->delete_customer($customer_id);
}

function save_venue_ctr($customer_id, $venue_id)
{
    $customer = new Customer();
    return $customer->save_venue($customer_id, $venue_id);
}

function unsave_venue_ctr($customer_id, $venue_id)
{
    $customer = new Customer();
    return $customer->unsave_venue($customer_id, $venue_id);
}

function is_venue_saved_ctr($customer_id, $venue_id)
{
    $customer = new Customer();
    return $customer->is_venue_saved($customer_id, $venue_id);
}

function get_saved_venues_ctr($customer_id)
{
    $customer = new Customer();
    return $customer->get_saved_venues($customer_id);
}

?>