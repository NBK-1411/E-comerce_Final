<?php
/**
 * Create Activity Action - JSON Endpoint (Host)
 */

header('Content-Type: application/json');

require_once(__DIR__ . '/../controllers/activity_controller.php');
require_once(__DIR__ . '/../settings/core.php');

// Check if logged in
if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Please login to continue']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$host_id = $_SESSION['customer_id'];

// Get form data
$title = isset($_POST['title']) ? trim($_POST['title']) : '';
$description = isset($_POST['description']) ? trim($_POST['description']) : '';
$activity_type = isset($_POST['activity_type']) ? trim($_POST['activity_type']) : '';
$recurrence_type = isset($_POST['recurrence_type']) ? trim($_POST['recurrence_type']) : 'none';
$location_text = isset($_POST['location_text']) ? trim($_POST['location_text']) : '';
$lat = isset($_POST['lat']) ? trim($_POST['lat']) : (isset($_POST['selectedLat']) ? trim($_POST['selectedLat']) : null);
$lng = isset($_POST['lng']) ? trim($_POST['lng']) : (isset($_POST['selectedLng']) ? trim($_POST['selectedLng']) : null);
$venue_id = isset($_POST['venue_id']) && !empty($_POST['venue_id']) ? intval($_POST['venue_id']) : null;
$start_date = isset($_POST['start_date']) ? trim($_POST['start_date']) : '';
$start_time = isset($_POST['start_time']) ? trim($_POST['start_time']) : '';
$end_date = isset($_POST['end_date']) ? trim($_POST['end_date']) : '';
$end_time = isset($_POST['end_time']) ? trim($_POST['end_time']) : '';
$capacity = isset($_POST['capacity']) && !empty($_POST['capacity']) ? intval($_POST['capacity']) : null;
$is_free = isset($_POST['is_free']) && $_POST['is_free'] === '1' ? 1 : 0;
$price_min = isset($_POST['price_min']) ? floatval($_POST['price_min']) : 0.00;
$price_max = isset($_POST['price_max']) ? floatval($_POST['price_max']) : 0.00;

// Validation
if (empty($title)) {
    echo json_encode(['success' => false, 'message' => 'Activity title is required']);
    exit();
}

if (empty($description)) {
    echo json_encode(['success' => false, 'message' => 'Description is required']);
    exit();
}

if (empty($activity_type)) {
    echo json_encode(['success' => false, 'message' => 'Activity type is required']);
    exit();
}

if (empty($location_text)) {
    echo json_encode(['success' => false, 'message' => 'Location is required']);
    exit();
}

if (empty($start_date) || empty($start_time)) {
    echo json_encode(['success' => false, 'message' => 'Start date and time are required']);
    exit();
}

if (!$is_free && $price_min <= 0) {
    echo json_encode(['success' => false, 'message' => 'Price is required for paid activities']);
    exit();
}

// Combine date and time
$start_at = $start_date . ' ' . $start_time . ':00';
$end_at = null;
if (!empty($end_date) && !empty($end_time)) {
    $end_at = $end_date . ' ' . $end_time . ':00';
}

// GPS code
$gps_code = '';
if ($lat && $lng && is_numeric($lat) && is_numeric($lng)) {
    $gps_code = "$lat,$lng";
}

// Handle photo uploads
$photos = [];
if (isset($_FILES['photos']) && !empty($_FILES['photos']['name'][0])) {
    $upload_dir = __DIR__ . '/../uploads/activities/';
    
    // Create directory if it doesn't exist
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    for ($i = 0; $i < count($_FILES['photos']['name']); $i++) {
        if ($_FILES['photos']['error'][$i] === UPLOAD_ERR_OK) {
            $file_name = uniqid() . '_' . basename($_FILES['photos']['name'][$i]);
            $target_path = $upload_dir . $file_name;
            
            if (move_uploaded_file($_FILES['photos']['tmp_name'][$i], $target_path)) {
                $photos[] = '../uploads/activities/' . $file_name;
            }
        }
    }
}

$photos_json = json_encode($photos);

// Validate recurrence_type
if (!in_array($recurrence_type, ['none', 'recurring'])) {
    $recurrence_type = 'none';
}

// Prepare activity data array
$activity_data = [
    'title' => $title,
    'description' => $description,
    'activity_type' => $activity_type,
    'recurrence_type' => $recurrence_type,
    'host_id' => $host_id,
    'venue_id' => $venue_id,
    'location_text' => $location_text,
    'gps_code' => $gps_code,
    'start_at' => $start_at,
    'end_at' => $end_at,
    'capacity' => $capacity,
    'price_min' => $price_min,
    'price_max' => $price_max > 0 ? $price_max : $price_min,
    'is_free' => $is_free,
    'photos_json' => $photos_json,
    'status' => 'pending' // Requires admin approval
];

// Create activity
try {
    $result = create_activity_ctr($activity_data);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Activity created successfully! It will be reviewed by admin before being published.',
            'activity_id' => $result
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to create activity. Please try again.']);
    }
} catch (Exception $e) {
    error_log("Activity creation error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']);
}

?>

