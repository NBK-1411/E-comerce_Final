<?php
/**
 * Fetch Categories Action - JSON Endpoint
 */

header('Content-Type: application/json');

require_once(__DIR__ . '/../controllers/category_controller.php');

$categories = get_all_categories_ctr();

if ($categories !== false) {
    echo json_encode(['success' => true, 'data' => $categories]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to fetch categories']);
}

?>

