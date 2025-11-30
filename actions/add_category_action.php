<?php
/**
 * Add Category Action - JSON Endpoint
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

$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$description = isset($_POST['description']) ? trim($_POST['description']) : '';
$icon = isset($_POST['icon']) ? trim($_POST['icon']) : '';

if (empty($name)) {
    echo json_encode(['success' => false, 'message' => 'Category name is required']);
    exit();
}

$result = add_category_ctr($name, $description, $icon);

if ($result) {
    echo json_encode(['success' => true, 'message' => 'Category added successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add category. Name may already exist.']);
}

?>

