<?php
/**
 * Update Profile Action - JSON Endpoint (Logged in users)
 */

header('Content-Type: application/json');

require_once(__DIR__ . '/../controllers/customer_controller.php');
require_once(__DIR__ . '/../settings/core.php');

// Check if logged in
if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Please login to continue']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$user_id = $_SESSION['customer_id'];

// Get form data
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$country = isset($_POST['country']) ? trim($_POST['country']) : '';
$city = isset($_POST['city']) ? trim($_POST['city']) : '';
$contact = isset($_POST['contact']) ? trim($_POST['contact']) : '';

// Validation
if (empty($name)) {
    echo json_encode(['success' => false, 'message' => 'Name is required']);
    exit();
}

if (empty($country)) {
    echo json_encode(['success' => false, 'message' => 'Country is required']);
    exit();
}

if (empty($city)) {
    echo json_encode(['success' => false, 'message' => 'City is required']);
    exit();
}

if (empty($contact)) {
    echo json_encode(['success' => false, 'message' => 'Phone number is required']);
    exit();
}

// Update profile
try {
    $result = update_customer_ctr($user_id, $name, $country, $city, $contact);
    
    if ($result) {
        // Update session data
        $_SESSION['customer_name'] = $name;
        
        echo json_encode([
            'success' => true,
            'message' => 'Profile updated successfully!'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update profile. Please try again.'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

?>

