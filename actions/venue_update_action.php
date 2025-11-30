<?php
/**
 * Update Venue Action - JSON Endpoint (Venue Owner)
 */

header('Content-Type: application/json');

// Start output buffering to catch any errors
ob_start();

try {
    require_once(__DIR__ . '/../controllers/venue_controller.php');
    require_once(__DIR__ . '/../settings/core.php');
} catch (Exception $e) {
    ob_end_clean();
    echo json_encode([
        'success' => false,
        'message' => 'Failed to load required files: ' . $e->getMessage()
    ]);
    exit();
} catch (Error $e) {
    ob_end_clean();
    echo json_encode([
        'success' => false,
        'message' => 'Fatal error: ' . $e->getMessage() . ' in ' . basename($e->getFile()) . ':' . $e->getLine()
    ]);
    exit();
}

// Check if logged in
if (!is_logged_in()) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Please login to continue']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$user_id = $_SESSION['customer_id'];
$venue_id = isset($_POST['venue_id']) ? intval($_POST['venue_id']) : 0;

// Validate venue ID
if (!$venue_id) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Venue ID is required']);
    exit();
}

// Get venue to check ownership
try {
    $venue = get_venue_by_id_ctr($venue_id);
} catch (Exception $e) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Error loading venue: ' . $e->getMessage()]);
    exit();
}

if (!$venue || !is_array($venue)) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Venue not found']);
    exit();
}

// Check if user owns this venue (or is admin)
if (!isset($venue['created_by']) || ($venue['created_by'] != $user_id && !is_admin())) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'You do not have permission to edit this venue']);
    exit();
}

// Get form data
$title = isset($_POST['title']) ? trim($_POST['title']) : '';
$cat_id = isset($_POST['cat_id']) ? intval($_POST['cat_id']) : 0;
$description = isset($_POST['description']) ? trim($_POST['description']) : '';
$location_text = isset($_POST['location_text']) ? trim($_POST['location_text']) : '';
$lat = isset($_POST['lat']) ? trim($_POST['lat']) : '';
$lng = isset($_POST['lng']) ? trim($_POST['lng']) : '';
// Convert empty strings to null
$lat = ($lat === '' || $lat === null) ? null : $lat;
$lng = ($lng === '' || $lng === null) ? null : $lng;
$price_per_hour = isset($_POST['price_per_hour']) ? floatval($_POST['price_per_hour']) : 0;
$capacity = isset($_POST['capacity']) ? intval($_POST['capacity']) : 0;
$min_booking_hours = isset($_POST['min_booking_hours']) ? intval($_POST['min_booking_hours']) : 1;
$contact_phone = isset($_POST['contact_phone']) ? trim($_POST['contact_phone']) : '';
$contact_email = isset($_POST['contact_email']) && !empty($_POST['contact_email']) ? trim($_POST['contact_email']) : null;

// Amenities (JSON encode) - handle both array and empty cases
$amenities_array = [];
if (isset($_POST['amenities'])) {
    if (is_array($_POST['amenities'])) {
        $amenities_array = $_POST['amenities'];
    }
}
$amenities = json_encode($amenities_array);

// Validation
if (empty($title)) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Venue name is required']);
    exit();
}

if (empty($cat_id)) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Category is required']);
    exit();
}

if (empty($description)) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Description is required']);
    exit();
}

if (empty($location_text)) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Location is required']);
    exit();
}

if ($price_per_hour <= 0) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Valid price is required']);
    exit();
}

if (empty($contact_phone)) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Contact phone is required']);
    exit();
}

// Handle photos - combine existing and new
$photos = [];

// Get remaining existing photos
if (isset($_POST['remaining_photos'])) {
    $remaining = json_decode($_POST['remaining_photos'], true);
    if (is_array($remaining)) {
        $photos = $remaining;
    }
}

// Handle new photo uploads
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

// Format GPS code
$gps_code = '';
if ($lat !== null && $lng !== null && $lat !== '' && $lng !== '') {
    // Validate coordinates are numeric
    if (is_numeric($lat) && is_numeric($lng)) {
        $gps_code = trim($lat) . ',' . trim($lng);
    }
}

// Prepare venue data array with safe defaults
$venue_data = [
    'title' => $title,
    'description' => $description,
    'cat_id' => $cat_id,
    'gps_code' => $gps_code,
    'location_text' => $location_text,
    'capacity' => $capacity ? $capacity : 0, // Default to 0 if not provided
    'price_min' => $price_per_hour,
    'price_max' => $price_per_hour * 8, // Default max price (8 hours)
    'deposit_percentage' => isset($venue['deposit_percentage']) && $venue['deposit_percentage'] !== null ? $venue['deposit_percentage'] : 30,
    'rules_json' => isset($venue['rules_json']) && $venue['rules_json'] !== null ? $venue['rules_json'] : json_encode([]),
    'safety_notes' => isset($venue['safety_notes']) ? ($venue['safety_notes'] !== null ? $venue['safety_notes'] : '') : '',
    'parking_transport' => isset($venue['parking_transport']) ? ($venue['parking_transport'] !== null ? $venue['parking_transport'] : '') : '',
    'accessibility_info' => isset($venue['accessibility_info']) ? ($venue['accessibility_info'] !== null ? $venue['accessibility_info'] : '') : '',
    'cancellation_policy' => isset($venue['cancellation_policy']) && $venue['cancellation_policy'] !== null ? $venue['cancellation_policy'] : 'flexible',
    'contact_phone' => $contact_phone,
    'contact_email' => $contact_email
];

// Update venue
try {
    // Validate required fields are present
    if (empty($venue_data['title']) || empty($venue_data['description']) || empty($venue_data['location_text'])) {
        ob_end_clean();
        echo json_encode([
            'success' => false,
            'message' => 'Please fill in all required fields (Title, Description, Location).'
        ]);
        exit();
    }
    
    // Ensure all required fields exist in venue_data
    if (!isset($venue_data['safety_notes'])) $venue_data['safety_notes'] = '';
    if (!isset($venue_data['parking_transport'])) $venue_data['parking_transport'] = '';
    if (!isset($venue_data['accessibility_info'])) $venue_data['accessibility_info'] = '';
    if (!isset($venue_data['cancellation_policy'])) $venue_data['cancellation_policy'] = 'flexible';
    if (!isset($venue_data['deposit_percentage'])) $venue_data['deposit_percentage'] = 30;
    if (!isset($venue_data['rules_json'])) $venue_data['rules_json'] = json_encode([]);
    
    $result = update_venue_ctr($venue_id, $venue_data);
    
    if ($result) {
        // Update photos
        try {
            if (!empty($photos_json)) {
                update_venue_photos_ctr($venue_id, $photos_json);
            }
        } catch (Exception $e) {
            // Log photo update error but don't fail the whole update
            error_log("Photo update error: " . $e->getMessage());
        }
        
        // If venue was approved, set it back to pending for re-approval
        $status_message = '';
        if (isset($venue['status']) && !empty($venue['status']) && $venue['status'] == 'approved') {
            try {
                update_venue_status_ctr($venue_id, 'pending');
                $status_message = ' Your venue has been set to "pending" and will be reviewed by our team.';
            } catch (Exception $e) {
                error_log("Status update error: " . $e->getMessage());
                // Don't fail the update if status change fails
            }
        }
        
        ob_end_clean();
        echo json_encode([
            'success' => true,
            'message' => 'Venue updated successfully!' . $status_message
        ]);
    } else {
        ob_end_clean();
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update venue. Please check all required fields and try again. If the problem persists, contact support.'
        ]);
    }
} catch (Exception $e) {
    ob_end_clean();
    // More detailed error message for debugging
    $error_message = $e->getMessage();
    error_log("Venue update error: " . $error_message);
    error_log("Venue ID: " . $venue_id);
    error_log("Venue data: " . print_r($venue_data, true));
    error_log("Stack trace: " . $e->getTraceAsString());
    
    // Provide user-friendly error message
    $user_message = 'Error updating venue. ';
    if (strpos($error_message, 'Unknown column') !== false) {
        $user_message .= 'Database structure issue detected. Please contact support.';
    } elseif (strpos($error_message, 'Duplicate entry') !== false) {
        $user_message .= 'This venue already exists.';
    } elseif (strpos($error_message, 'Parameter binding') !== false) {
        $user_message .= 'Data validation error. Please check all fields.';
    } else {
        $user_message .= 'Please try again or contact support if the problem persists.';
        // In development, show more details
        if (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'localhost') !== false) {
            $user_message .= ' Details: ' . $error_message;
        }
    }
    
    echo json_encode([
        'success' => false,
        'message' => $user_message
    ]);
} catch (Error $e) {
    ob_end_clean();
    error_log("Fatal error in venue update: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
    echo json_encode([
        'success' => false,
        'message' => 'Fatal error occurred. Please check server logs or contact support.'
    ]);
}

?>

