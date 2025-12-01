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

// Support both admin/category.php (name/description/icon) and admin/dashboard.php (cat_name/cat_description/cat_icon)
$name = isset($_POST['name']) ? trim($_POST['name']) : (isset($_POST['cat_name']) ? trim($_POST['cat_name']) : '');
$description = isset($_POST['description']) ? trim($_POST['description']) : (isset($_POST['cat_description']) ? trim($_POST['cat_description']) : '');
$icon = isset($_POST['icon']) ? trim($_POST['icon']) : (isset($_POST['cat_icon']) ? trim($_POST['cat_icon']) : '');

// Optional scope: 'venue' or 'activity'. For now we only persist one categories table,
// but we use this to conceptually separate activity categories in the admin UI.
$scope = isset($_POST['scope']) ? trim($_POST['scope']) : 'venue';
if ($scope === 'activity') {
    // Prefix description so we can distinguish activity categories in the dashboard
    $description = '__ACTIVITY__ ' . $description;
}

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

