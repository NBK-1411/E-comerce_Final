<?php
/**
 * Create Venue Action - JSON Endpoint (Venue Owner)
 */

header('Content-Type: application/json');

require_once(__DIR__ . '/../controllers/venue_controller.php');
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

$owner_id = $_SESSION['customer_id'];

// Get form data
$title = isset($_POST['title']) ? trim($_POST['title']) : '';
$cat_id = isset($_POST['cat_id']) ? intval($_POST['cat_id']) : 0;
$description = isset($_POST['description']) ? trim($_POST['description']) : '';
$location_text = isset($_POST['location_text']) ? trim($_POST['location_text']) : '';
$lat = isset($_POST['lat']) ? trim($_POST['lat']) : null;
$lng = isset($_POST['lng']) ? trim($_POST['lng']) : null;
$price_per_hour = isset($_POST['price_per_hour']) ? floatval($_POST['price_per_hour']) : 0;
$capacity = isset($_POST['capacity']) ? intval($_POST['capacity']) : null;
$min_booking_hours = isset($_POST['min_booking_hours']) ? intval($_POST['min_booking_hours']) : 1;
$contact_phone = isset($_POST['contact_phone']) ? trim($_POST['contact_phone']) : '';
$contact_email = isset($_POST['contact_email']) ? trim($_POST['contact_email']) : null;

// Amenities (JSON encode)
$amenities = isset($_POST['amenities']) ? json_encode($_POST['amenities']) : json_encode([]);

// Validation
if (empty($title)) {
    echo json_encode(['success' => false, 'message' => 'Venue name is required']);
    exit();
}

if (empty($cat_id)) {
    echo json_encode(['success' => false, 'message' => 'Category is required']);
    exit();
}

if (empty($description)) {
    echo json_encode(['success' => false, 'message' => 'Description is required']);
    exit();
}

if (empty($location_text)) {
    echo json_encode(['success' => false, 'message' => 'Location is required']);
    exit();
}

if ($price_per_hour <= 0) {
    echo json_encode(['success' => false, 'message' => 'Valid price is required']);
    exit();
}

if (empty($contact_phone)) {
    echo json_encode(['success' => false, 'message' => 'Contact phone is required']);
    exit();
}

// Handle photo uploads (simplified for now - in production, use proper file storage)
$photos = [];
if (isset($_FILES['photos']) && !empty($_FILES['photos']['name'][0])) {
    $upload_dir = __DIR__ . '/../uploads/venues/';
    
    // Create directory if it doesn't exist
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    for ($i = 0; $i < count($_FILES['photos']['name']); $i++) {
        if ($_FILES['photos']['error'][$i] === UPLOAD_ERR_OK) {
            $file_name = uniqid() . '_' . basename($_FILES['photos']['name'][$i]);
            $target_path = $upload_dir . $file_name;
            
            if (move_uploaded_file($_FILES['photos']['tmp_name'][$i], $target_path)) {
                $photos[] = '../uploads/venues/' . $file_name; // Add ../ for correct relative path from public/
            }
        }
    }
}

$photos_json = json_encode($photos);

// Prepare venue data array
$venue_data = [
    'title' => $title,
    'description' => $description,
    'cat_id' => $cat_id,
    'gps_code' => ($lat && $lng) ? "$lat,$lng" : '', // Empty string instead of null
    'location_text' => $location_text,
    'capacity' => $capacity ? $capacity : 0, // Default to 0 if not provided
    'price_min' => $price_per_hour,
    'price_max' => $price_per_hour * 8, // Default max price (8 hours)
    'deposit_percentage' => 30, // Default 30% deposit
    'rules_json' => json_encode([]), // Empty rules for now
    'safety_notes' => null,
    'parking_transport' => null,
    'accessibility_info' => null,
    'photos_json' => $photos_json,
    'cancellation_policy' => 'flexible', // Default policy
    'created_by' => $owner_id,
    'contact_phone' => $contact_phone,
    'contact_email' => $contact_email
];

// Create venue
try {
    $result = create_venue_ctr($venue_data);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Venue submitted successfully! It will be reviewed by our team.'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to create venue. Please check all required fields and try again.'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

?>

