<?php
/**
 * Download Remaining Images for All Venues and Activities
 * 
 * Downloads images from Unsplash (royalty-free) for remaining venues and activities
 */

set_time_limit(1800); // 30 minutes

// Image mapping for remaining venues (7-16)
$venue_images = [
    // Golden Tulip Accra Grand Ballroom
    'golden-tulip-1.jpg' => 'https://images.unsplash.com/photo-1511578314322-379afb476865?w=800&h=600&fit=crop', // Ballroom
    'golden-tulip-2.jpg' => 'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?w=800&h=600&fit=crop', // Event space
    'golden-tulip-3.jpg' => 'https://images.unsplash.com/photo-1511578314322-379afb476865?w=800&h=600&fit=crop', // Wedding venue
    
    // Creative Studio Accra
    'studio-1.jpg' => 'https://images.unsplash.com/photo-1492691527719-9d1e07e534b4?w=800&h=600&fit=crop', // Photography studio
    'studio-2.jpg' => 'https://images.unsplash.com/photo-1492691527719-9d1e07e534b4?w=800&h=600&fit=crop', // Studio setup
    'studio-3.jpg' => 'https://images.unsplash.com/photo-1492691527719-9d1e07e534b4?w=800&h=600&fit=crop', // Creative space
    
    // Kokrobite Beach Resort
    'kokrobite-1.jpg' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=800&h=600&fit=crop', // Beach resort
    'kokrobite-2.jpg' => 'https://images.unsplash.com/photo-1559827260-dc66d52bef19?w=800&h=600&fit=crop', // Beach view
    'kokrobite-3.jpg' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=800&h=600&fit=crop', // Resort pool
    
    // Chez Clarisse
    'chez-clarisse-1.jpg' => 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=800&h=600&fit=crop', // French restaurant
    'chez-clarisse-2.jpg' => 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=800&h=600&fit=crop', // Fine dining
    'chez-clarisse-3.jpg' => 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=800&h=600&fit=crop', // Restaurant interior
    
    // Asanka Local
    'asanka-1.jpg' => 'https://images.unsplash.com/photo-1555939594-58d7cb561ad1?w=800&h=600&fit=crop', // Local restaurant
    'asanka-2.jpg' => 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=800&h=600&fit=crop', // Traditional food
    'asanka-3.jpg' => 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=800&h=600&fit=crop', // Street food
    
    // Plot 7 Lounge
    'plot7-1.jpg' => 'https://images.unsplash.com/photo-1514525253161-7a46d19cd819?w=800&h=600&fit=crop', // Lounge
    'plot7-2.jpg' => 'https://images.unsplash.com/photo-1530103862676-de8c9debad1d?w=800&h=600&fit=crop', // Bar atmosphere
    'plot7-3.jpg' => 'https://images.unsplash.com/photo-1514525253161-7a46d19cd819?w=800&h=600&fit=crop', // Cocktail bar
    
    // Accra International Conference Centre
    'aicc-1.jpg' => 'https://images.unsplash.com/photo-1511578314322-379afb476865?w=800&h=600&fit=crop', // Conference hall
    'aicc-2.jpg' => 'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?w=800&h=600&fit=crop', // Large event space
    'aicc-3.jpg' => 'https://images.unsplash.com/photo-1511578314322-379afb476865?w=800&h=600&fit=crop', // Auditorium
    
    // Coco Lounge
    'coco-1.jpg' => 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=800&h=600&fit=crop', // Restaurant lounge
    'coco-2.jpg' => 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=800&h=600&fit=crop', // Outdoor dining
    'coco-3.jpg' => 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=800&h=600&fit=crop', // Garden restaurant
    
    // Bella Roma
    'bella-roma-1.jpg' => 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=800&h=600&fit=crop', // Italian restaurant
    'bella-roma-2.jpg' => 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=800&h=600&fit=crop', // Pasta
    'bella-roma-3.jpg' => 'https://images.unsplash.com/photo-1571091718767-18b5b1457add?w=800&h=600&fit=crop', // Pizza
    
    // Aqua Safari Resort
    'aqua-safari-1.jpg' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=800&h=600&fit=crop', // Beach resort
    'aqua-safari-2.jpg' => 'https://images.unsplash.com/photo-1559827260-dc66d52bef19?w=800&h=600&fit=crop', // Resort pool
    'aqua-safari-3.jpg' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=800&h=600&fit=crop', // Luxury resort
];

// Image mapping for remaining activities (7-16)
$activity_images = [
    // Traditional Drumming & Dance Workshop
    'drumming-workshop-1.jpg' => 'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?w=800&h=600&fit=crop', // Drumming
    'drumming-workshop-2.jpg' => 'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?w=800&h=600&fit=crop', // Dance workshop
    
    // Accra City Walking Tour
    'city-tour-1.jpg' => 'https://images.unsplash.com/photo-1513475382585-d06e58bcb0e0?w=800&h=600&fit=crop', // City tour
    'city-tour-2.jpg' => 'https://images.unsplash.com/photo-1513475382585-d06e58bcb0e0?w=800&h=600&fit=crop', // Walking tour
    
    // Kakum National Park Canopy Walk
    'kakum-tour-1.jpg' => 'https://images.unsplash.com/photo-1441974231531-c6227db76b6e?w=800&h=600&fit=crop', // Forest canopy
    'kakum-tour-2.jpg' => 'https://images.unsplash.com/photo-1441974231531-c6227db76b6e?w=800&h=600&fit=crop', // Canopy walk
    'kakum-tour-3.jpg' => 'https://images.unsplash.com/photo-1441974231531-c6227db76b6e?w=800&h=600&fit=crop', // Rainforest
    
    // Cape Coast Castle & Elmina Castle Tour
    'castle-tour-1.jpg' => 'https://images.unsplash.com/photo-1515542622106-78bda8ba0e5b?w=800&h=600&fit=crop', // Historic castle
    'castle-tour-2.jpg' => 'https://images.unsplash.com/photo-1515542622106-78bda8ba0e5b?w=800&h=600&fit=crop', // Castle exterior
    
    // Artisan Market Pop-up
    'artisan-market-1.jpg' => 'https://images.unsplash.com/photo-1513475382585-d06e58bcb0e0?w=800&h=600&fit=crop', // Market
    'artisan-market-2.jpg' => 'https://images.unsplash.com/photo-1513475382585-d06e58bcb0e0?w=800&h=600&fit=crop', // Handicrafts
    
    // Street Food Festival Pop-up
    'street-food-1.jpg' => 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=800&h=600&fit=crop', // Street food
    'street-food-2.jpg' => 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=800&h=600&fit=crop', // Food vendors
    
    // Beach Volleyball Tournament
    'beach-volleyball-1.jpg' => 'https://images.unsplash.com/photo-1534158914592-062992fbe900?w=800&h=600&fit=crop', // Beach volleyball
    'beach-volleyball-2.jpg' => 'https://images.unsplash.com/photo-1534158914592-062992fbe900?w=800&h=600&fit=crop', // Sports tournament
    
    // Board Game Night & Social
    'game-night-1.jpg' => 'https://images.unsplash.com/photo-1606092195730-5d7b9af1efc5?w=800&h=600&fit=crop', // Board games
    'game-night-2.jpg' => 'https://images.unsplash.com/photo-1606092195730-5d7b9af1efc5?w=800&h=600&fit=crop', // Social gaming
    
    // Tech Entrepreneurs Networking Meetup
    'tech-meetup-1.jpg' => 'https://images.unsplash.com/photo-1511578314322-379afb476865?w=800&h=600&fit=crop', // Networking event
    
    // Rooftop Party: Sunset to Sunrise
    'rooftop-party-1.jpg' => 'https://images.unsplash.com/photo-1514933651103-005eec06c04b?w=800&h=600&fit=crop', // Rooftop party
    'rooftop-party-2.jpg' => 'https://images.unsplash.com/photo-1514933651103-005eec06c04b?w=800&h=600&fit=crop', // Night party
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
echo "Downloading remaining venue images...\n";
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
echo "\nDownloading remaining activity images...\n";
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

