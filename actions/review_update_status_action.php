<?php
/**
 * Update Review Status Action - JSON Endpoint (Admin Only)
 */

header('Content-Type: application/json');

// Require core.php first for session and authentication
require_once(__DIR__ . '/../settings/core.php');
require_once(__DIR__ . '/../controllers/review_controller.php');

// Check if admin
if (!is_admin()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$review_id = isset($_POST['review_id']) ? intval($_POST['review_id']) : 0;
$status = isset($_POST['status']) ? trim($_POST['status']) : '';

// Validation
if (empty($review_id) || $review_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Review ID is required']);
    exit();
}

if (empty($status) || !in_array($status, ['pending', 'approved', 'rejected', 'flagged'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid status. Must be: pending, approved, rejected, or flagged']);
    exit();
}

try {
$result = update_review_status_ctr($review_id, $status);

if ($result) {
    echo json_encode([
        'success' => true,
            'message' => 'Review status updated to ' . $status . ' successfully'
    ]);
} else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update review status. Please try again.'
        ]);
    }
} catch (Exception $e) {
    error_log("Review update error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while updating the review status.'
    ]);
}

?>

