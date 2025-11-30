<?php
/**
 * Download Subset of Images for Venues and Activities
 * 
 * Downloads images from Unsplash (royalty-free) for a subset of venues and activities
 * Focuses on the most visible items (first 6 venues, first 6 activities)
 */

require_once(__DIR__ . '/../settings/db_class.php');

set_time_limit(600); // 10 minutes

$db = new db_connection();
$connection = $db->db_conn();

// Unsplash API - using their Source API (no key required for basic usage)
// We'll use direct Unsplash image URLs with appropriate search terms

// Image mapping for venues (first 6)
$venue_images = [
    'santoku-1.jpg' => 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=800&h=600&fit=crop', // Restaurant interior
    'santoku-2.jpg' => 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=800&h=600&fit=crop', // Japanese food
    'santoku-3.jpg' => 'https://images.unsplash.com/photo-1571091718767-18b5b1457add?w=800&h=600&fit=crop', // Sushi
    
    'buka-1.jpg' => 'https://images.unsplash.com/photo-1555939594-58d7cb561ad1?w=800&h=600&fit=crop', // African food
    'buka-2.jpg' => 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=800&h=600&fit=crop', // Traditional restaurant
    'buka-3.jpg' => 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=800&h=600&fit=crop', // Food spread
    
    'skybar-1.jpg' => 'https://images.unsplash.com/photo-1514933651103-005eec06c04b?w=800&h=600&fit=crop', // Rooftop bar
    'skybar-2.jpg' => 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=800&h=600&fit=crop', // City view
    'skybar-3.jpg' => 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=800&h=600&fit=crop', // Rooftop dining
    
    'republic-1.jpg' => 'https://images.unsplash.com/photo-1530103862676-de8c9debad1d?w=800&h=600&fit=crop', // Nightclub
    'republic-2.jpg' => 'https://images.unsplash.com/photo-1514525253161-7a46d19cd819?w=800&h=600&fit=crop', // Bar atmosphere
    'republic-3.jpg' => 'https://images.unsplash.com/photo-1557682250-33bd709cbe85?w=800&h=600&fit=crop', // Party scene
    
    'carbon-1.jpg' => 'https://images.unsplash.com/photo-1514525253161-7a46d19cd819?w=800&h=600&fit=crop', // Nightclub
    'carbon-2.jpg' => 'https://images.unsplash.com/photo-1530103862676-de8c9debad1d?w=800&h=600&fit=crop', // DJ setup
    'carbon-3.jpg' => 'https://images.unsplash.com/photo-1514525253161-7a46d19cd819?w=800&h=600&fit=crop', // Dance floor
    
    'labadi-1.jpg' => 'https://images.unsplash.com/photo-1511578314322-379afb476865?w=800&h=600&fit=crop', // Conference room
    'labadi-2.jpg' => 'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?w=800&h=600&fit=crop', // Event space
    'labadi-3.jpg' => 'https://images.unsplash.com/photo-1511578314322-379afb476865?w=800&h=600&fit=crop', // Beach view
];

// Image mapping for activities (first 6)
$activity_images = [
    'afrobeat-concert-1.jpg' => 'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?w=800&h=600&fit=crop', // Live music
    'afrobeat-concert-2.jpg' => 'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?w=800&h=600&fit=crop', // Concert crowd
    'afrobeat-concert-3.jpg' => 'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?w=800&h=600&fit=crop', // Stage performance
    
    'reggae-night-1.jpg' => 'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?w=800&h=600&fit=crop', // Live band
    'reggae-night-2.jpg' => 'https://images.unsplash.com/photo-1514525253161-7a46d19cd819?w=800&h=600&fit=crop', // Beach music
    
    'food-festival-1.jpg' => 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=800&h=600&fit=crop', // Food festival
    'food-festival-2.jpg' => 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=800&h=600&fit=crop', // Food vendors
    'food-festival-3.jpg' => 'https://images.unsplash.com/photo-1555939594-58d7cb561ad1?w=800&h=600&fit=crop', // Festival atmosphere
    
    'chale-wote-1.jpg' => 'https://images.unsplash.com/photo-1513475382585-d06e58bcb0e0?w=800&h=600&fit=crop', // Street art
    'chale-wote-2.jpg' => 'https://images.unsplash.com/photo-1514525253161-7a46d19cd819?w=800&h=600&fit=crop', // Art festival
    'chale-wote-3.jpg' => 'https://images.unsplash.com/photo-1513475382585-d06e58bcb0e0?w=800&h=600&fit=crop', // Cultural event
    
    'kente-workshop-1.jpg' => 'https://images.unsplash.com/photo-1586281380349-632531db7ed4?w=800&h=600&fit=crop', // Textile weaving
    'kente-workshop-2.jpg' => 'https://images.unsplash.com/photo-1586281380349-632531db7ed4?w=800&h=600&fit=crop', // Craft workshop
    
    'cooking-class-1.jpg' => 'https://images.unsplash.com/photo-1556910103-1c02745aae4d?w=800&h=600&fit=crop', // Cooking class
    'cooking-class-2.jpg' => 'https://images.unsplash.com/photo-1555939594-58d7cb561ad1?w=800&h=600&fit=crop', // Food preparation
];

// Function to download image
function download_image($url, $filepath) {
    $ch = curl_init($url);
    $fp = fopen($filepath, 'wb');
    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $success = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    fclose($fp);
    
    if ($success && $http_code == 200) {
        return true;
    } else {
        // Delete failed download
        if (file_exists($filepath)) {
            unlink($filepath);
        }
        return false;
    }
}

// Download venue images
echo "Downloading venue images...\n";
$venue_dir = __DIR__ . '/../uploads/venues/';
$venue_downloaded = 0;
$venue_failed = 0;

foreach ($venue_images as $filename => $url) {
    $filepath = $venue_dir . $filename;
    
    // Skip if already exists
    if (file_exists($filepath)) {
        echo "  ⏭  Skipping $filename (already exists)\n";
        continue;
    }
    
    echo "  ⬇  Downloading $filename...\n";
    if (download_image($url, $filepath)) {
        echo "  ✓  Downloaded $filename\n";
        $venue_downloaded++;
    } else {
        echo "  ✗  Failed to download $filename\n";
        $venue_failed++;
    }
    
    // Small delay to be respectful
    usleep(500000); // 0.5 seconds
}

// Download activity images
echo "\nDownloading activity images...\n";
$activity_dir = __DIR__ . '/../uploads/activities/';
$activity_downloaded = 0;
$activity_failed = 0;

foreach ($activity_images as $filename => $url) {
    $filepath = $activity_dir . $filename;
    
    // Skip if already exists
    if (file_exists($filepath)) {
        echo "  ⏭  Skipping $filename (already exists)\n";
        continue;
    }
    
    echo "  ⬇  Downloading $filename...\n";
    if (download_image($url, $filepath)) {
        echo "  ✓  Downloaded $filename\n";
        $activity_downloaded++;
    } else {
        echo "  ✗  Failed to download $filename\n";
        $activity_failed++;
    }
    
    // Small delay to be respectful
    usleep(500000); // 0.5 seconds
}

echo "\n=== SUMMARY ===\n";
echo "Venue images downloaded: $venue_downloaded\n";
echo "Venue images failed: $venue_failed\n";
echo "Activity images downloaded: $activity_downloaded\n";
echo "Activity images failed: $activity_failed\n";
echo "\nTotal images downloaded: " . ($venue_downloaded + $activity_downloaded) . "\n";

