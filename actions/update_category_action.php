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
// Support both admin/category.php (name/description/icon) and admin/dashboard.php (cat_name/cat_description/cat_icon)
$name = isset($_POST['name']) ? trim($_POST['name']) : (isset($_POST['cat_name']) ? trim($_POST['cat_name']) : '');
$description = isset($_POST['description']) ? trim($_POST['description']) : (isset($_POST['cat_description']) ? trim($_POST['cat_description']) : '');
$icon = isset($_POST['icon']) ? trim($_POST['icon']) : (isset($_POST['cat_icon']) ? trim($_POST['cat_icon']) : '');

// Optional scope: 'venue' or 'activity' to keep activity categories separate in the UI
$scope = isset($_POST['scope']) ? trim($_POST['scope']) : 'venue';
if ($scope === 'activity') {
    // Ensure activity categories keep the marker prefix
    if (strpos($description, '__ACTIVITY__ ') !== 0) {
        $description = '__ACTIVITY__ ' . $description;
    }
} else {
    // Strip marker if an activity category is being converted back to a venue category
    if (strpos($description, '__ACTIVITY__ ') === 0) {
        $description = trim(substr($description, strlen('__ACTIVITY__ ')));
    }
}

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

