<?php
/**
 * Get Venue Action - JSON Endpoint
 */

header('Content-Type: application/json');

require_once(__DIR__ . '/../controllers/venue_controller.php');
require_once(__DIR__ . '/../controllers/review_controller.php');

$venue_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (empty($venue_id)) {
    echo json_encode(['success' => false, 'message' => 'Venue ID is required']);
    exit();
}

$venue = get_venue_by_id_ctr($venue_id);

if ($venue) {
    // Get reviews
    $reviews = get_reviews_by_venue_ctr($venue_id, true);
    
    // Get rating stats
    $rating_stats = get_venue_rating_stats_ctr($venue_id);
    
    $venue['reviews'] = $reviews;
    $venue['rating_stats'] = $rating_stats;
    
    echo json_encode(['success' => true, 'data' => $venue]);
} else {
    echo json_encode(['success' => false, 'message' => 'Venue not found']);
}

?>

