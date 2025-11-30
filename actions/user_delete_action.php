<?php
/**
 * Delete User Action - JSON Endpoint (Admin Only)
 */

header('Content-Type: application/json');

require_once(__DIR__ . '/../controllers/customer_controller.php');
require_once(__DIR__ . '/../settings/core.php');

// Check if admin
if (!is_admin()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;

// Validation
if (empty($user_id)) {
    echo json_encode(['success' => false, 'message' => 'User ID is required']);
    exit();
}

// Get user details first to check role
$user = get_customer_by_id_ctr($user_id);

if (!$user) {
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit();
}

// Prevent deletion of admin users
if ($user['user_role'] == 1) {
    echo json_encode(['success' => false, 'message' => 'Cannot delete admin users']);
    exit();
}

// Prevent admin from deleting themselves
if ($user_id == $_SESSION['customer_id']) {
    echo json_encode(['success' => false, 'message' => 'You cannot delete your own account']);
    exit();
}

// Delete user
try {
    $result = delete_customer_ctr($user_id);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'User deleted successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to delete user. This user might be an admin or does not exist.'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

?>

