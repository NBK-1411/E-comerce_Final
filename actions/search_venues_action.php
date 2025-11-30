<?php
/**
 * Search Venues Action - JSON Endpoint
 * Enhanced with additional filters matching Next.js app functionality
 */

header('Content-Type: application/json');

require_once(__DIR__ . '/../controllers/venue_controller.php');

$filters = [];

// Basic filters
if (isset($_GET['category']) && !empty($_GET['category'])) {
    $filters['category'] = intval($_GET['category']);
}

if (isset($_GET['location']) && !empty($_GET['location'])) {
    $filters['location'] = trim($_GET['location']);
}

if (isset($_GET['q']) && !empty($_GET['q'])) {
    $filters['q'] = trim($_GET['q']);
}

// Price range
if (isset($_GET['min_price']) && !empty($_GET['min_price'])) {
    $filters['min_price'] = floatval($_GET['min_price']);
}

if (isset($_GET['max_price']) && !empty($_GET['max_price'])) {
    $filters['max_price'] = floatval($_GET['max_price']);
}

// Capacity range
if (isset($_GET['min_capacity']) && !empty($_GET['min_capacity'])) {
    $filters['min_capacity'] = intval($_GET['min_capacity']);
}

if (isset($_GET['max_capacity']) && !empty($_GET['max_capacity'])) {
    $filters['max_capacity'] = intval($_GET['max_capacity']);
}

// Boolean filters
if (isset($_GET['verified']) && $_GET['verified'] === 'true') {
    $filters['verified'] = true;
}

if (isset($_GET['parking']) && $_GET['parking'] === 'true') {
    $filters['parking'] = true;
}

if (isset($_GET['accessibility']) && $_GET['accessibility'] === 'true') {
    $filters['accessibility'] = true;
}

// Cancellation policy
if (isset($_GET['cancellation_policy']) && !empty($_GET['cancellation_policy'])) {
    $policy = trim($_GET['cancellation_policy']);
    if (in_array($policy, ['flexible', 'standard', 'strict'])) {
        $filters['cancellation_policy'] = $policy;
    }
}

// Tags/vibes (comma-separated or array)
if (isset($_GET['tags']) && !empty($_GET['tags'])) {
    if (is_array($_GET['tags'])) {
        $filters['tags'] = $_GET['tags'];
    } else {
        $filters['tags'] = array_map('trim', explode(',', $_GET['tags']));
    }
}

$venues = search_venues_ctr($filters);

if ($venues !== false) {
    echo json_encode([
        'success' => true, 
        'data' => $venues,
        'count' => count($venues),
        'filters_applied' => $filters
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to search venues']);
}

?>

