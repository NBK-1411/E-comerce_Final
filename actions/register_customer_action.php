<?php
/**
 * Register Customer Action - JSON Endpoint
 */

header('Content-Type: application/json');

require_once(__DIR__ . '/../controllers/customer_controller.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Get POST data
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';
$country = isset($_POST['country']) ? trim($_POST['country']) : '';
$city = isset($_POST['city']) ? trim($_POST['city']) : '';
$contact = isset($_POST['contact']) ? trim($_POST['contact']) : '';
$role = isset($_POST['role']) ? intval($_POST['role']) : 2;

// Validation
$errors = [];

if (empty($name)) {
    $errors[] = 'Name is required';
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Valid email is required';
}

if (empty($password) || strlen($password) < 8) {
    $errors[] = 'Password must be at least 8 characters';
}

if (empty($country)) {
    $errors[] = 'Country is required';
}

if (empty($city)) {
    $errors[] = 'City is required';
}

if (empty($contact)) {
    $errors[] = 'Contact number is required';
}

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
    exit();
}

// Register customer
$result = register_customer_ctr($name, $email, $password, $country, $city, $contact, $role);

if ($result) {
    echo json_encode([
        'success' => true,
        'message' => 'Registration successful! Please login.'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Registration failed. Email may already be registered.'
    ]);
}

?>

