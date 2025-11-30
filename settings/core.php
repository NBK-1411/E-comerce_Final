<?php
/**
 * Core Session and Authorization Functions
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if user is logged in
 * @return bool
 */
function is_logged_in() {
    return isset($_SESSION['customer_id']) && !empty($_SESSION['customer_id']);
}

/**
 * Check if user is admin
 * @return bool
 */
function is_admin() {
    return is_logged_in() && isset($_SESSION['customer_role']) && $_SESSION['customer_role'] == 1;
}

/**
 * Check if user is venue owner
 * @return bool
 */
function is_venue_owner() {
    return is_logged_in() && isset($_SESSION['customer_role']) && $_SESSION['customer_role'] == 3;
}

/**
 * Require user to be logged in
 * Redirects to login page if not logged in
 */
function require_login() {
    if (!is_logged_in()) {
        // Determine the correct path based on current directory
        $current_dir = basename(dirname($_SERVER['SCRIPT_FILENAME']));
        
        if ($current_dir === 'public') {
            // Already in public folder
            header('Location: login.php');
        } else if ($current_dir === 'admin') {
            // In admin folder
            header('Location: ../public/login.php');
        } else {
            // In root or actions
            header('Location: public/login.php');
        }
        exit();
    }
}

/**
 * Require user to be admin
 * Redirects to index if not admin
 */
function require_admin() {
    if (!is_admin()) {
        // Determine the correct path based on current directory
        $current_dir = basename(dirname($_SERVER['SCRIPT_FILENAME']));
        
        if ($current_dir === 'admin' || $current_dir === 'public' || $current_dir === 'actions') {
            header('Location: ../index.php');
        } else {
            header('Location: index.php');
        }
        exit();
    }
}

/**
 * Require user to be venue owner or admin
 */
function require_venue_owner() {
    if (!is_venue_owner() && !is_admin()) {
        // Determine the correct path based on current directory
        $current_dir = basename(dirname($_SERVER['SCRIPT_FILENAME']));
        
        if ($current_dir === 'admin' || $current_dir === 'public' || $current_dir === 'actions') {
            header('Location: ../index.php');
        } else {
            header('Location: index.php');
        }
        exit();
    }
}

/**
 * Get current user ID
 * @return int|null
 */
function get_user_id() {
    return is_logged_in() ? $_SESSION['customer_id'] : null;
}

/**
 * Get current user data
 * @return array|null
 */
function get_user() {
    if (!is_logged_in()) return null;
    
    return [
        'id' => $_SESSION['customer_id'],
        'name' => $_SESSION['customer_name'],
        'email' => $_SESSION['customer_email'],
        'role' => $_SESSION['customer_role']
    ];
}

/**
 * Sanitize input data
 * @param string $data
 * @return string
 */
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Generate CSRF token
 * @return string
 */
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 * @param string $token
 * @return bool
 */
function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Generate random string for QR codes, etc.
 * @param int $length
 * @return string
 */
function generate_random_string($length = 10) {
    return substr(str_shuffle(str_repeat($x='0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
}

?>

