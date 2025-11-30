<?php
/**
 * Add Review Action - JSON Endpoint
 */

header('Content-Type: application/json');

require_once(__DIR__ . '/../controllers/review_controller.php');
require_once(__DIR__ . '/../settings/core.php');

// Check if logged in
if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Please login to submit a review']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$venue_id = isset($_POST['venue_id']) ? intval($_POST['venue_id']) : 0;
$booking_id = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : null;
$rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
$review_text = isset($_POST['review_text']) ? trim($_POST['review_text']) : '';

// Validation
if (empty($venue_id) || empty($rating) || $rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'message' => 'Valid venue ID and rating (1-5) are required']);
    exit();
}

$result = add_review_ctr($venue_id, get_user_id(), $booking_id, $rating, $review_text);

if ($result) {
    echo json_encode([
        'success' => true,
        'message' => 'Review submitted successfully! It will be visible after moderation.'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to submit review. You may have already reviewed this booking.'
    ]);
}

?>

