<?php
require_once(__DIR__ . '/../settings/core.php');
require_once(__DIR__ . '/../controllers/category_controller.php');
require_once(__DIR__ . '/../controllers/venue_controller.php');
require_once(__DIR__ . '/../includes/site_nav.php');

// Check if logged in
require_login();

// Get venue ID
$venue_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$venue_id) {
    header('Location: owner_dashboard.php');
    exit();
}

// Get venue details
$venue = get_venue_by_id_ctr($venue_id);

if (!$venue) {
    header('Location: owner_dashboard.php');
    exit();
}

// Check if user owns this venue (or is admin)
$customer_id = $_SESSION['customer_id'];
if ($venue['created_by'] != $customer_id && !is_admin()) {
    header('Location: owner_dashboard.php');
    exit();
}

// Get all categories for the form
$categories = get_all_categories_ctr();
if ($categories === false) $categories = [];

// Parse existing data
$amenities = json_decode($venue['amenities_json'] ?? '[]', true);
$photos = json_decode($venue['photos_json'] ?? '[]', true);
$rules = json_decode($venue['rules_json'] ?? '{}', true);

// Parse GPS code
$lat = '';
$lng = '';
if (!empty($venue['gps_code']) && strpos($venue['gps_code'], ',') !== false) {
    list($lat, $lng) = explode(',', $venue['gps_code']);
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Edit Venue - Go Outside</title>
<link rel="stylesheet" href="../css/bootstrap.min.css">
<link rel="stylesheet" href="../css/main.css"> 
<link rel="stylesheet" href="../css/responsive.css">
<link rel="stylesheet" href="../css/animate.min.css">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
.page-header {
    background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('../images/banner.jpg') center/cover;
    padding: 80px 0 60px;
    text-align: center;
}
.form-section {
    background: #1e1e1e;
    border: 1px solid #3a3a3a;
    border-radius: 10px;
    padding: 30px;
    margin-bottom: 30px;
}
.section-title {
    color: #ff5518;
    font-size: 22px;
    font-weight: 700;
    margin-bottom: 25px;
    padding-bottom: 10px;
    border-bottom: 2px solid #3a3a3a;
}
.form-label {
    color: #fff;
    font-weight: 600;
    margin-bottom: 8px;
    display: block;
}
.form-control {
    background: #010101;
    border: 1px solid #3a3a3a;
    color: #fff;
    padding: 12px 15px;
    border-radius: 5px;
    width: 100%;
    margin-bottom: 20px;
}
.form-control:focus {
    border-color: #ff5518;
    outline: none;
}
textarea.form-control {
    min-height: 120px;
    resize: vertical;
}
/* Select dropdown specific styling */
select.form-control {
    padding: 15px 40px 15px 15px !important;
    min-height: 50px !important;
    line-height: 1.8 !important;
    font-size: 15px;
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    background-image: url('data:image/svg+xml;utf8,<svg fill="%23ff5518" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg"><path d="M7 10l5 5 5-5z"/></svg>');
    background-repeat: no-repeat;
    background-position: right 10px center;
    background-size: 20px;
    cursor: pointer;
}
select.form-control option {
    background: #1e1e1e;
    color: #fff;
    padding: 12px 15px;
    line-height: 1.8;
}
select.form-control:focus {
    background-image: url('data:image/svg+xml;utf8,<svg fill="%23ff5518" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg"><path d="M7 10l5 5 5-5z"/></svg>');
}
.upload-area {
    background: #010101;
    border: 2px dashed #3a3a3a;
    border-radius: 10px;
    padding: 40px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s;
}
.upload-area:hover {
    border-color: #ff5518;
    background: #1e1e1e;
}
.upload-area i {
    font-size: 48px;
    color: #ff5518;
    margin-bottom: 15px;
}
.checkbox-group {
    background: #010101;
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 20px;
}
.checkbox-group label {
    color: #bcbcbc;
    display: block;
    margin-bottom: 10px;
    cursor: pointer;
}
.checkbox-group input[type="checkbox"] {
    margin-right: 10px;
}
.help-text {
    color: #777;
    font-size: 13px;
    margin-top: -15px;
    margin-bottom: 20px;
}
.alert {
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    display: none;
}
.alert-success {
    background: #2d5016;
    color: #90ee90;
    border: 1px solid #4caf50;
}
.alert-danger {
    background: #5d1616;
    color: #ffcccb;
    border: 1px solid #f44336;
}
.alert-warning {
    background: #5d4a1f;
    color: #ffd700;
    border: 1px solid #ffa500;
}
.existing-photo {
    position: relative;
    background-size: cover;
    background-position: center;
    height: 150px;
    border-radius: 8px;
    border: 2px solid #3a3a3a;
}
.existing-photo .remove-btn {
    position: absolute;
    top: 5px;
    right: 5px;
    background: #5d1616;
    color: #fff;
    border: none;
    border-radius: 50%;
    width: 30px;
    height: 30px;
    cursor: pointer;
    font-size: 14px;
}
.location-map-picker {
    width: 100%;
    height: 400px;
    border-radius: 8px;
    overflow: hidden;
    border: 1px solid #3a3a3a;
    margin-top: 15px;
    margin-bottom: 15px;
}
.location-map-picker .leaflet-container {
    background: #1e1e1e;
}
.address-search-box {
    position: relative;
    margin-bottom: 15px;
}
.address-search-box input {
    padding-right: 45px;
}
.address-search-btn {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    background: #ff5518;
    color: #fff;
    border: none;
    padding: 8px 15px;
    border-radius: 5px;
    cursor: pointer;
    font-size: 12px;
}
.address-search-btn:hover {
    background: #e63d00;
}
.map-instructions {
    background: #010101;
    padding: 15px;
    border-radius: 8px;
    border: 1px solid #3a3a3a;
    margin-bottom: 15px;
    color: #bcbcbc;
    font-size: 13px;
}
.map-instructions i {
    color: #ff5518;
    margin-right: 8px;
}
.coordinates-display {
    background: #010101;
    padding: 12px 15px;
    border-radius: 5px;
    border: 1px solid #3a3a3a;
    color: #bcbcbc;
    font-size: 13px;
    margin-top: 10px;
}
.coordinates-display strong {
    color: #fff;
}
</style>
</head>

<body>
<?php render_site_nav(['base_path' => '../']); ?>

<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <h1 class="wow fadeInDown animated" style="color: #fff; margin-bottom: 10px;">
            <i class="fa fa-edit"></i> Edit Venue
        </h1>
        <p class="wow fadeInUp animated" style="color: #bcbcbc; font-size: 18px;">
            Update your venue information
        </p>
    </div>
</div>

<!-- Main Content -->
<section class="section" style="background: #010101; padding: 60px 0;">
    <div class="container">
        <!-- Re-approval Warning -->
        <div class="alert alert-warning" style="display: block;">
            <i class="fa fa-info-circle"></i> <strong>Important:</strong> Any changes to your venue will require admin approval before going live. Your venue will be set to "pending" status until reviewed.
        </div>

        <!-- Alert Message -->
        <div id="alertMessage"></div>

        <form id="editVenueForm">
            <input type="hidden" name="venue_id" value="<?php echo $venue_id; ?>">
            
            <!-- Basic Information -->
            <div class="form-section">
                <h3 class="section-title">
                    <i class="fa fa-info-circle"></i> Basic Information
                </h3>
                
                <div class="row">
                    <div class="col-md-12">
                        <label class="form-label">Venue Name *</label>
                        <input type="text" name="title" class="form-control" 
                               value="<?php echo htmlspecialchars($venue['title']); ?>" 
                               placeholder="e.g., The Garden Hall" required>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">Category *</label>
                        <select name="cat_id" class="form-control" required>
                            <option value="">Select a category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['cat_id']; ?>" 
                                        <?php echo $venue['cat_id'] == $category['cat_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['cat_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Booking Type *</label>
                        <select name="booking_type" class="form-control" required>
                            <option value="rent" <?php echo ($venue['booking_type'] ?? 'rent') == 'rent' ? 'selected' : ''; ?>>Rent (Exclusive Use)</option>
                            <option value="reservation" <?php echo ($venue['booking_type'] ?? 'rent') == 'reservation' ? 'selected' : ''; ?>>Reservation (Non-exclusive)</option>
                            <option value="ticket" <?php echo ($venue['booking_type'] ?? 'rent') == 'ticket' ? 'selected' : ''; ?>>Ticketed Event</option>
                        </select>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">Capacity</label>
                        <input type="number" name="capacity" class="form-control" 
                               value="<?php echo $venue['capacity']; ?>"
                               placeholder="Maximum number of guests">
                    </div>
                    
                    <div class="col-md-12">
                        <label class="form-label">Description *</label>
                        <textarea name="description" class="form-control" 
                                  placeholder="Describe your venue..." required><?php echo htmlspecialchars($venue['description']); ?></textarea>
                        <p class="help-text">Provide a detailed description of your venue</p>
                    </div>
                </div>
            </div>

            <!-- Location -->
            <div class="form-section">
                <h3 class="section-title">
                    <i class="fa fa-map-marker"></i> Location
                </h3>
                
                <div class="row">
                    <div class="col-md-12">
                        <label class="form-label">Address *</label>
                        <div class="address-search-box">
                            <input type="text" id="addressSearch" class="form-control" 
                                   placeholder="Search for an address or location (e.g., Accra, Ghana)">
                            <button type="button" class="address-search-btn" onclick="searchAddress()">
                                <i class="fa fa-search"></i> Search
                            </button>
                        </div>
                        <input type="text" name="location_text" id="location_text" class="form-control" 
                               value="<?php echo htmlspecialchars($venue['location_text']); ?>"
                               placeholder="e.g., 123 Main Street, Accra" required style="margin-top: 10px;">
                        <p class="help-text">Enter the full address or search and select from map</p>
                    </div>
                </div>
                
                <div class="map-instructions">
                    <i class="fa fa-info-circle"></i>
                    <strong>How to set location:</strong> Search for an address above, or click directly on the map below to set your venue's exact location.
                </div>
                
                <!-- Interactive Map Picker -->
                <div id="locationMapPicker" class="location-map-picker"></div>
                
                <!-- Coordinates Display (Hidden inputs for form submission) -->
                <input type="hidden" name="lat" id="selectedLat" value="<?php echo htmlspecialchars($lat); ?>">
                <input type="hidden" name="lng" id="selectedLng" value="<?php echo htmlspecialchars($lng); ?>">
                
                <div class="coordinates-display" id="coordinatesDisplay" <?php echo (!empty($lat) && !empty($lng)) ? '' : 'style="display: none;"'; ?>>
                    <i class="fa fa-check-circle" style="color: #90ee90;"></i>
                    Location set: <strong id="displayCoords"><?php echo (!empty($lat) && !empty($lng)) ? htmlspecialchars($lat . ', ' . $lng) : ''; ?></strong>
                </div>
            </div>

            <!-- Pricing & Availability -->
            <div class="form-section">
                <h3 class="section-title">
                    <i class="fa fa-money"></i> Pricing & Availability
                </h3>
                
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">Price per Hour (GH₵) *</label>
                        <input type="number" name="price_per_hour" class="form-control" 
                               value="<?php echo $venue['price_min']; ?>"
                               step="0.01" placeholder="0.00" required>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">Minimum Booking Hours</label>
                        <input type="number" name="min_booking_hours" class="form-control" 
                               value="1" min="1">
                    </div>
                </div>
            </div>

            <!-- Amenities -->
            <div class="form-section">
                <h3 class="section-title">
                    <i class="fa fa-check-square-o"></i> Amenities
                </h3>
                
                <div class="checkbox-group">
                    <label>
                        <input type="checkbox" name="amenities[]" value="WiFi" 
                               <?php echo in_array('WiFi', $amenities) ? 'checked' : ''; ?>>
                        <i class="fa fa-wifi"></i> WiFi
                    </label>
                    <label>
                        <input type="checkbox" name="amenities[]" value="Parking" 
                               <?php echo in_array('Parking', $amenities) ? 'checked' : ''; ?>>
                        <i class="fa fa-car"></i> Parking
                    </label>
                    <label>
                        <input type="checkbox" name="amenities[]" value="Air Conditioning" 
                               <?php echo in_array('Air Conditioning', $amenities) ? 'checked' : ''; ?>>
                        <i class="fa fa-snowflake-o"></i> Air Conditioning
                    </label>
                    <label>
                        <input type="checkbox" name="amenities[]" value="Sound System" 
                               <?php echo in_array('Sound System', $amenities) ? 'checked' : ''; ?>>
                        <i class="fa fa-volume-up"></i> Sound System
                    </label>
                    <label>
                        <input type="checkbox" name="amenities[]" value="Catering" 
                               <?php echo in_array('Catering', $amenities) ? 'checked' : ''; ?>>
                        <i class="fa fa-cutlery"></i> Catering Available
                    </label>
                    <label>
                        <input type="checkbox" name="amenities[]" value="Projector" 
                               <?php echo in_array('Projector', $amenities) ? 'checked' : ''; ?>>
                        <i class="fa fa-desktop"></i> Projector/Screen
                    </label>
                </div>
            </div>

            <!-- Photos -->
            <div class="form-section">
                <h3 class="section-title">
                    <i class="fa fa-camera"></i> Photos
                </h3>
                
                <!-- Existing Photos -->
                <?php if (!empty($photos)): ?>
                <div style="margin-bottom: 25px;">
                    <label class="form-label">Current Photos</label>
                    <div id="existingPhotos" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 15px;">
                        <?php foreach ($photos as $index => $photo): ?>
                        <div class="existing-photo" style="background-image: url('<?php echo htmlspecialchars($photo); ?>');" data-photo-index="<?php echo $index; ?>">
                            <button type="button" class="remove-btn" onclick="removePhoto(<?php echo $index; ?>)">
                                <i class="fa fa-times"></i>
                            </button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" id="remainingPhotos" name="remaining_photos" value='<?php echo htmlspecialchars(json_encode($photos)); ?>'>
                </div>
                <?php endif; ?>
                
                <div class="upload-area" onclick="document.getElementById('photoUpload').click()">
                    <i class="fa fa-cloud-upload"></i>
                    <h4 style="color: #fff; margin-bottom: 10px;">Upload New Photos</h4>
                    <p style="color: #bcbcbc;">Click to browse or drag and drop</p>
                    <p class="help-text">Add more photos to your venue</p>
                </div>
                <input type="file" id="photoUpload" name="photos[]" multiple accept="image/*" 
                       style="display: none;">
                <div id="photoPreview" style="margin-top: 20px; display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 15px;"></div>
            </div>

            <!-- Contact Information -->
            <div class="form-section">
                <h3 class="section-title">
                    <i class="fa fa-phone"></i> Contact Information
                </h3>
                
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">Phone Number *</label>
                        <input type="tel" name="contact_phone" class="form-control" 
                               value="<?php echo htmlspecialchars($venue['contact_phone'] ?? ''); ?>"
                               placeholder="+233 XX XXX XXXX" required>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="contact_email" class="form-control" 
                               value="<?php echo htmlspecialchars($venue['contact_email'] ?? ''); ?>"
                               placeholder="venue@example.com">
                    </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div style="text-align: center;">
                <a href="owner_dashboard.php" class="btn btn-large" style="background: #3a3a3a; margin-right: 15px;">
                    <i class="fa fa-times"></i> Cancel
                </a>
                <button type="submit" class="btn btn-large" id="submitBtn">
                    <i class="fa fa-check"></i> Update Venue
                </button>
                <p class="help-text" style="margin-top: 15px;">
                    Changes will be reviewed by our team before going live
                </p>
            </div>
        </form>
    </div>
</section>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<script src="../js/bootstrap.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
// Track which existing photos to keep
let remainingPhotos = JSON.parse($('#remainingPhotos').val() || '[]');

// Remove existing photo
function removePhoto(index) {
    if (confirm('Remove this photo?')) {
        remainingPhotos.splice(index, 1);
        $('#remainingPhotos').val(JSON.stringify(remainingPhotos));
        $('[data-photo-index="' + index + '"]').fadeOut(300, function() {
            $(this).remove();
        });
    }
}

// Photo preview for new uploads
$('#photoUpload').on('change', function() {
    const files = this.files;
    const preview = $('#photoPreview');
    preview.empty();
    
    for (let i = 0; i < files.length; i++) {
        const file = files[i];
        const reader = new FileReader();
        
        reader.onload = function(e) {
            const img = $('<div>').css({
                'background-image': 'url(' + e.target.result + ')',
                'background-size': 'cover',
                'background-position': 'center',
                'height': '150px',
                'border-radius': '8px',
                'border': '2px solid #3a3a3a'
            });
            preview.append(img);
        };
        
        reader.readAsDataURL(file);
    }
});

// Show alert message
function showAlert(message, type) {
    const alertDiv = $('#alertMessage');
    alertDiv.removeClass('alert-success alert-danger');
    alertDiv.addClass('alert alert-' + type);
    alertDiv.html('<i class="fa fa-' + (type === 'success' ? 'check-circle' : 'exclamation-circle') + '"></i> ' + message);
    alertDiv.show();
    
    $('html, body').animate({ scrollTop: 0 }, 'slow');
    
    setTimeout(function() {
        alertDiv.fadeOut();
    }, 5000);
}

// Form submission
$('#editVenueForm').on('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = $('#submitBtn');
    
    // Disable button
    submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Updating...');
    
    $.ajax({
        url: '../actions/venue_update_action.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(result) {
            if (result.success) {
                showAlert(result.message, 'success');
                // Redirect after 2 seconds
                setTimeout(function() {
                    window.location.href = 'owner_dashboard.php';
                }, 2000);
            } else {
                showAlert(result.message || 'Failed to update venue. Please check all fields and try again.', 'danger');
                submitBtn.prop('disabled', false).html('<i class="fa fa-check"></i> Update Venue');
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', status, error);
            console.error('Response:', xhr.responseText);
            
            let errorMessage = 'An error occurred. Please try again.';
            
            // Try to parse error response
            try {
                const response = JSON.parse(xhr.responseText);
                if (response.message) {
                    errorMessage = response.message;
                }
            } catch (e) {
                // If not JSON, show the raw response
                if (xhr.responseText) {
                    errorMessage = 'Error: ' + xhr.responseText.substring(0, 200);
                }
            }
            
            showAlert(errorMessage, 'danger');
            submitBtn.prop('disabled', false).html('<i class="fa fa-check"></i> Update Venue');
        },
        complete: function() {
            // Only re-enable if not already done in error handler
            if (!submitBtn.prop('disabled')) {
                return;
            }
        }
    });
});

// Map Picker Functionality
let locationMap;
let locationMarker;
let selectedLat = <?php echo !empty($lat) ? $lat : 'null'; ?>;
let selectedLng = <?php echo !empty($lng) ? $lng : 'null'; ?>;

// Initialize map
$(document).ready(function() {
    // Default to Accra if no existing location, otherwise use existing location
    const defaultLat = selectedLat || 5.6037;
    const defaultLng = selectedLng || -0.1870;
    const defaultZoom = selectedLat ? 16 : 13;
    
    locationMap = L.map('locationMapPicker', {
        zoomControl: true,
        scrollWheelZoom: true
    }).setView([defaultLat, defaultLng], defaultZoom);
    
    // Add OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(locationMap);
    
    // Custom orange marker icon
    var orangeIcon = L.icon({
        iconUrl: 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="32" height="48" viewBox="0 0 32 48"><path fill="%23ff5518" d="M16 0C7.163 0 0 7.163 0 16c0 11.5 16 32 16 32s16-20.5 16-32C32 7.163 24.837 0 16 0zm0 22c-3.314 0-6-2.686-6-6s2.686-6 6-6 6 2.686 6 6-2.686 6-6 6z"/></svg>',
        iconSize: [32, 48],
        iconAnchor: [16, 48],
        popupAnchor: [0, -48]
    });
    
    // If existing location, add marker
    if (selectedLat && selectedLng) {
        locationMarker = L.marker([selectedLat, selectedLng], {icon: orangeIcon}).addTo(locationMap);
        locationMarker.bindPopup('Current Venue Location').openPopup();
    }
    
    // Add click handler to map
    locationMap.on('click', function(e) {
        setLocation(e.latlng.lat, e.latlng.lng);
    });
    
    // Allow Enter key to search
    $('#addressSearch').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            searchAddress();
        }
    });
});

// Search for address using Nominatim (OpenStreetMap geocoding)
function searchAddress() {
    const query = $('#addressSearch').val().trim();
    
    if (!query) {
        alert('Please enter an address to search');
        return;
    }
    
    console.log('Searching for:', query);
    
    // Show loading
    $('.address-search-btn').html('<i class="fa fa-spinner fa-spin"></i> Searching...').prop('disabled', true);
    
    // Use our PHP proxy - it handles all query variations
    $.ajax({
        url: '../actions/geocode_address_action.php',
        method: 'GET',
        data: {
            q: query
        },
        dataType: 'json',
        timeout: 20000, // 20 second timeout (PHP tries multiple queries)
        success: function(response) {
            console.log('Geocoding response:', response);
            
            if (response.success && response.results && response.results.length > 0) {
                // Use the first result (best match, already sorted by PHP)
                const result = response.results[0];
                const lat = parseFloat(result.lat);
                const lng = parseFloat(result.lon);
                
                console.log('Selected result:', result);
                console.log('Coordinates:', lat, lng);
                
                // Validate coordinates
                if (isNaN(lat) || isNaN(lng)) {
                    $('.address-search-btn').html('<i class="fa fa-search"></i> Search').prop('disabled', false);
                    alert('Invalid coordinates returned. Please try a different search or click on the map.');
                    return;
                }
                
                // Set location on map
                setLocation(lat, lng);
                
                // Update address field with full address
                const address = result.display_name || (result.address ? 
                    (result.address.road ? result.address.road + ', ' : '') +
                    (result.address.suburb || result.address.neighbourhood || '') +
                    (result.address.city || result.address.town || result.address.village || '') +
                    (result.address.state || '') : query);
                
                $('#location_text').val(result.display_name || address);
                
                // Center map on location with appropriate zoom
                const zoom = result.importance && result.importance > 0.7 ? 17 : 16;
                locationMap.setView([lat, lng], zoom);
                
                $('.address-search-btn').html('<i class="fa fa-search"></i> Search').prop('disabled', false);
                
                // Show success message if multiple results were found
                if (response.results.length > 1) {
                    console.log('Found ' + response.results.length + ' results, using the best match');
                }
            } else {
                // No results found
                $('.address-search-btn').html('<i class="fa fa-search"></i> Search').prop('disabled', false);
                let errorMessage = 'Location not found. ';
                if (response.message) {
                    errorMessage = response.message.replace(/\\n/g, '\n');
                }
                alert(errorMessage + '\n\nYou can click directly on the map to set your venue location.');
            }
        },
        error: function(xhr, status, error) {
            console.error('Geocoding error:', status, error);
            console.error('Response:', xhr.responseText);
            console.error('Status code:', xhr.status);
            
            // Try to get error message from response
            let errorMessage = 'Search failed. ';
            try {
                if (xhr.responseText) {
                    const response = JSON.parse(xhr.responseText);
                    if (response.message) {
                        errorMessage = response.message.replace(/\\n/g, '\n');
                    }
                }
            } catch (e) {
                console.error('Error parsing response:', e);
                if (xhr.status === 0) {
                    errorMessage = 'Network error. Please check your internet connection.';
                } else if (xhr.status >= 500) {
                    errorMessage = 'Server error. Please try again later.';
                } else {
                    errorMessage = 'Search service error. Please try again.';
                }
            }
            
            $('.address-search-btn').html('<i class="fa fa-search"></i> Search').prop('disabled', false);
            alert(errorMessage + '\n\nYou can click directly on the map to set your venue location.');
        }
    });
}

// Set location on map
function setLocation(lat, lng) {
    selectedLat = lat;
    selectedLng = lng;
    
    // Update hidden inputs
    $('#selectedLat').val(lat);
    $('#selectedLng').val(lng);
    
    // Remove existing marker
    if (locationMarker) {
        locationMap.removeLayer(locationMarker);
    }
    
    // Add new marker
    var orangeIcon = L.icon({
        iconUrl: 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="32" height="48" viewBox="0 0 32 48"><path fill="%23ff5518" d="M16 0C7.163 0 0 7.163 0 16c0 11.5 16 32 16 32s16-20.5 16-32C32 7.163 24.837 0 16 0zm0 22c-3.314 0-6-2.686-6-6s2.686-6 6-6 6 2.686 6 6-2.686 6-6 6z"/></svg>',
        iconSize: [32, 48],
        iconAnchor: [16, 48],
        popupAnchor: [0, -48]
    });
    
    locationMarker = L.marker([lat, lng], {icon: orangeIcon}).addTo(locationMap);
    locationMarker.bindPopup('Venue Location').openPopup();
    
    // Show coordinates
    $('#displayCoords').text(lat.toFixed(6) + ', ' + lng.toFixed(6));
    $('#coordinatesDisplay').show();
}
</script>
</body>
</html>

