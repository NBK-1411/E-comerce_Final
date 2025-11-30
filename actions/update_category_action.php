<?php
/**
 * Update Category Action - JSON Endpoint
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
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$description = isset($_POST['description']) ? trim($_POST['description']) : '';
$icon = isset($_POST['icon']) ? trim($_POST['icon']) : '';

if (empty($cat_id) || empty($name)) {
    echo json_encode(['success' => false, 'message' => 'Category ID and name are required']);
    exit();
}

$result = update_category_ctr($cat_id, $name, $description, $icon);

if ($result) {
    echo json_encode(['success' => true, 'message' => 'Category updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update category']);
}

?>

