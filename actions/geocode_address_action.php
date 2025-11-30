<?php
/**
 * Geocode Address Action - Using Google Maps Geocoding API
 * This provides better coverage, especially for businesses and places in Ghana
 */

// Enable error logging for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$query = isset($_GET['q']) ? trim($_GET['q']) : '';

if (empty($query)) {
    echo json_encode(['success' => false, 'message' => 'Query parameter is required']);
    exit();
}

// Load Google Maps API key
require_once(__DIR__ . '/../settings/db_cred.php');

$api_key = defined('GOOGLE_MAPS_API_KEY') ? GOOGLE_MAPS_API_KEY : '';

if (empty($api_key) || $api_key === 'YOUR_GOOGLE_MAPS_API_KEY_HERE') {
    echo json_encode([
        'success' => false,
        'message' => 'Google Maps API key not configured. Please contact the administrator.'
    ]);
    exit();
}

// Log the search query
error_log("Google Maps geocoding search for: " . $query);

// Try multiple search strategies for better results
$search_queries = [
    $query, // Try exact query first (best for business names)
    $query . ', Accra, Ghana',
    $query . ', Ghana',
    $query . ', Greater Accra, Ghana'
];

$all_results = [];
$best_result = null;

// Try each search query
foreach ($search_queries as $search_query) {
    // Build Google Maps Geocoding API URL
    $geocoding_url = 'https://maps.googleapis.com/maps/api/geocode/json?' . http_build_query([
        'address' => $search_query,
        'key' => $api_key,
        'region' => 'gh', // Bias results to Ghana
        'components' => 'country:gh' // Restrict to Ghana
    ]);
    
    // Initialize cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $geocoding_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json'
    ]);
    
    // Execute request
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);
    
    // Log the request
    error_log("Google Maps request: " . $geocoding_url);
    error_log("HTTP Code: " . $http_code);
    if ($curl_error) {
        error_log("cURL Error: " . $curl_error);
    }
    
    if ($curl_error) {
        error_log("cURL error for query '$search_query': " . $curl_error);
        continue; // Try next query
    }
    
    if ($http_code !== 200) {
        error_log("HTTP error for query '$search_query': Code $http_code");
        continue; // Try next query
    }
    
    if (empty($response)) {
        error_log("Empty response for query '$search_query'");
        continue; // Try next query
    }
    
    // Parse JSON response
    $data = json_decode($response, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("JSON decode error for query '$search_query': " . json_last_error_msg());
        continue; // Try next URL
    }
    
    // Check for API errors
    if (isset($data['status']) && $data['status'] !== 'OK' && $data['status'] !== 'ZERO_RESULTS') {
        error_log("Google Maps API error for query '$search_query': " . $data['status']);
        if (isset($data['error_message'])) {
            error_log("Error message: " . $data['error_message']);
        }
        
        // If it's a quota or key error, return immediately
        if ($data['status'] === 'REQUEST_DENIED' || $data['status'] === 'OVER_QUERY_LIMIT') {
            echo json_encode([
                'success' => false,
                'message' => 'Geocoding service error: ' . (isset($data['error_message']) ? $data['error_message'] : $data['status'])
            ]);
            exit();
        }
        continue; // Try next query
    }
    
    if (!isset($data['results']) || empty($data['results'])) {
        error_log("No results for query '$search_query'");
        continue; // Try next query
    }
    
    error_log("Found " . count($data['results']) . " results for query '$search_query'");
    
    // Process Google Maps results
    foreach ($data['results'] as $result) {
        if (!isset($result['geometry']['location'])) {
            continue; // Skip invalid results
        }
        
        $location = $result['geometry']['location'];
        $lat = floatval($location['lat']);
        $lng = floatval($location['lng']);
        
        // Check if result is in Ghana
        $is_ghana = false;
        $ghana_confidence = 0;
        
        // Check address components
        if (isset($result['address_components'])) {
            foreach ($result['address_components'] as $component) {
                if (isset($component['types'])) {
                    if (in_array('country', $component['types'])) {
                        if (isset($component['short_name']) && strtolower($component['short_name']) === 'gh') {
                            $is_ghana = true;
                            $ghana_confidence = 10; // Highest confidence
                            break;
                        }
                    }
                }
            }
        }
        
        // Also check formatted address for Ghana indicators
        if (isset($result['formatted_address'])) {
            $formatted_address = $result['formatted_address'];
            $ghana_keywords = ['Ghana', 'Accra', 'Kumasi', 'Tamale', 'Cape Coast', 'Takoradi', 'Tema'];
            foreach ($ghana_keywords as $keyword) {
                if (stripos($formatted_address, $keyword) !== false) {
                    $is_ghana = true;
                    $ghana_confidence = max($ghana_confidence, 8);
                    break;
                }
            }
        }
        
        // Check bounding box - Ghana is roughly between 4.7°N to 11.2°N and 3.3°W to 1.3°E
        if ($lat >= 4.5 && $lat <= 11.5 && $lng >= -3.5 && $lng <= 1.5) {
            if (!$is_ghana) {
                $is_ghana = true;
                $ghana_confidence = 5; // Lower confidence but likely Ghana
            }
        }
        
        // Convert Google Maps format to our format
        $formatted_result = [
            'lat' => (string)$lat,
            'lon' => (string)$lng,
            'display_name' => isset($result['formatted_address']) ? $result['formatted_address'] : '',
            'address' => $result['address_components'] ?? [],
            'importance' => isset($result['geometry']['location_type']) ? 
                ($result['geometry']['location_type'] === 'ROOFTOP' ? 0.9 : 0.7) : 0.5,
            '_ghana_confidence' => $ghana_confidence
        ];
        
        // Add to results - prioritize Ghana locations but include others if no Ghana results
        $should_add = false;
        if ($is_ghana) {
            $should_add = true;
        } elseif (empty($all_results)) {
            // If no results yet, add any result (but with lower priority)
            $should_add = true;
            $formatted_result['_ghana_confidence'] = 1; // Very low confidence for non-Ghana results
        }
        
        if ($should_add) {
            // Check if we already have this result (avoid duplicates)
            $is_duplicate = false;
            foreach ($all_results as $existing) {
                if (abs(floatval($existing['lat']) - $lat) < 0.0001 &&
                    abs(floatval($existing['lon']) - $lng) < 0.0001) {
                    $is_duplicate = true;
                    break;
                }
            }
            
            if (!$is_duplicate) {
                $all_results[] = $formatted_result;
                // Use first high-confidence Ghana result as best match
                if ($is_ghana && $ghana_confidence >= 8 && $best_result === null) {
                    $best_result = $formatted_result;
                }
            }
        }
    }
    
    // If we found good Ghana results, break early
    if (!empty($all_results) && $best_result !== null) {
        break;
    }
    
    // If we have some results (even if not Ghana), use them
    if (!empty($all_results)) {
        break;
    }
}

// If no results found, return empty with helpful message
if (empty($all_results)) {
    error_log("No results found for any query variation of: " . $query);
    echo json_encode([
        'success' => false,
        'message' => 'No results found for "' . htmlspecialchars($query) . '". Try:\n- A more specific address\n- Include area name (e.g., "Treehouse Restaurant, Osu, Accra")\n- Or click directly on the map',
        'results' => []
    ]);
    exit();
}

// Sort results by Ghana confidence (highest first)
usort($all_results, function($a, $b) {
    $conf_a = isset($a['_ghana_confidence']) ? $a['_ghana_confidence'] : 0;
    $conf_b = isset($b['_ghana_confidence']) ? $b['_ghana_confidence'] : 0;
    return $conf_b - $conf_a; // Descending order
});

// Remove confidence score before returning
foreach ($all_results as &$result) {
    unset($result['_ghana_confidence']);
}
unset($result);

// Limit to top 5 results
$all_results = array_slice($all_results, 0, 5);

error_log("Returning " . count($all_results) . " results for query: " . $query);

// Return results
echo json_encode([
    'success' => true,
    'results' => $all_results
]);

?>
