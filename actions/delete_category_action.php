<?php
/**
 * Delete Category Action - JSON Endpoint
 */

header('Content-Type: application/json');

require_once(__DIR__ . '/../controllers/category_controller.php');
require_once(__DIR__ . '/../settings/core.php');

// Check if admin
if (!is_admin()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$cat_id = isset($_POST['cat_id']) ? intval($_POST['cat_id']) : 0;

if (empty($cat_id)) {
    echo json_encode(['success' => false, 'message' => 'Category ID is required']);
    exit();
}

$result = delete_category_ctr($cat_id);

if ($result) {
    echo json_encode(['success' => true, 'message' => 'Category deleted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete category']);
}

?>

