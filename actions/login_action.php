<?php
/**
 * Login Action - JSON Endpoint
 */

header('Content-Type: application/json');

require_once(__DIR__ . '/../controllers/customer_controller.php');
require_once(__DIR__ . '/../settings/core.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Get POST data
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

// Validation
if (empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Email and password are required']);
    exit();
}

// Login
$user = login_customer_ctr($email, $password);

if ($user) {
    // Set session with flat keys
    $_SESSION['customer_id'] = $user['customer_id'];
    $_SESSION['customer_name'] = $user['customer_name'];
    $_SESSION['customer_email'] = $user['customer_email'];
    $_SESSION['customer_role'] = $user['user_role'];
    $_SESSION['customer_image'] = $user['customer_image'] ?? null;
    
    echo json_encode([
        'success' => true,
        'message' => 'Login successful!',
        'user' => [
            'name' => $user['customer_name'],
            'role' => $user['user_role']
        ]
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid email or password'
    ]);
}

?>

