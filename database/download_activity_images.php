<?php
/**
 * Download Unique Images for All Activities
 * 
 * Downloads unique images from Unsplash for each activity
 * Ensures similar activities get different images
 */

require_once(__DIR__ . '/../settings/db_class.php');

set_time_limit(600); // 10 minutes

$db = new db_connection();
$connection = $db->db_conn();

// Get all activities from database
$sql = "SELECT activity_id, title, activity_type FROM activities WHERE status='approved' ORDER BY activity_id";
$result = $connection->query($sql);
$activities = [];
while ($row = $result->fetch_assoc()) {
    $activities[] = $row;
}

// Unique image URLs for each activity - using different Unsplash images for variety
// Format: 'filename.jpg' => 'unsplash_url'
$activity_images = [
    // Concerts (different styles)
    'afrobeat-concert-1.jpg' => 'https://images.unsplash.com/photo-1470229722913-7c0e2dbbafd3?w=800&h=600&fit=crop', // Live concert
    'afrobeat-concert-2.jpg' => 'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?w=800&h=600&fit=crop', // Stage performance
    'afrobeat-concert-3.jpg' => 'https://images.unsplash.com/photo-1514525253161-7a46d19cd819?w=800&h=600&fit=crop', // Crowd dancing
    
    'reggae-night-1.jpg' => 'https://images.unsplash.com/photo-1501281668745-7b2097ad7f62?w=800&h=600&fit=crop', // Reggae band
    'reggae-night-2.jpg' => 'https://images.unsplash.com/photo-1516450360452-9312f5e86fc7?w=800&h=600&fit=crop', // Live music
    
    'acoustic-session-1.jpg' => 'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?w=800&h=600&fit=crop', // Acoustic guitar
    'acoustic-session-2.jpg' => 'https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?w=800&h=600&fit=crop', // Intimate performance
    
    // Festivals (different themes)
    'food-festival-1.jpg' => 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=800&h=600&fit=crop', // Food vendors
    'food-festival-2.jpg' => 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=800&h=600&fit=crop', // Food spread
    'food-festival-3.jpg' => 'https://images.unsplash.com/photo-1555939594-58d7cb561ad1?w=800&h=600&fit=crop', // Festival atmosphere
    
    'chale-wote-1.jpg' => 'https://images.unsplash.com/photo-1513475382585-d06e58bcb0e0?w=800&h=600&fit=crop', // Street art
    'chale-wote-2.jpg' => 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=800&h=600&fit=crop', // Art festival
    'chale-wote-3.jpg' => 'https://images.unsplash.com/photo-1513475382585-d06e58bcb0e0?w=800&h=600&fit=crop', // Colorful art
    
    'music-festival-1.jpg' => 'https://images.unsplash.com/photo-1470229722913-7c0e2dbbafd3?w=800&h=600&fit=crop', // Music festival stage
    'music-festival-2.jpg' => 'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?w=800&h=600&fit=crop', // Festival crowd
    'music-festival-3.jpg' => 'https://images.unsplash.com/photo-1514525253161-7a46d19cd819?w=800&h=600&fit=crop', // Live performance
    
    // Workshops (different crafts)
    'kente-workshop-1.jpg' => 'https://images.unsplash.com/photo-1586790170083-2f9ceadc732d?w=800&h=600&fit=crop', // Textile weaving
    'kente-workshop-2.jpg' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=800&h=600&fit=crop', // Handicraft
    
    'drumming-workshop-1.jpg' => 'https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?w=800&h=600&fit=crop', // Drums
    'drumming-workshop-2.jpg' => 'https://images.unsplash.com/photo-1501281668745-7b2097ad7f62?w=800&h=600&fit=crop', // African drums
    
    'bead-workshop-1.jpg' => 'https://images.unsplash.com/photo-1586790170083-2f9ceadc732d?w=800&h=600&fit=crop', // Bead making
    'bead-workshop-2.jpg' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=800&h=600&fit=crop', // Jewelry making
    
    'pottery-workshop-1.jpg' => 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=800&h=600&fit=crop', // Pottery wheel
    'pottery-workshop-2.jpg' => 'https://images.unsplash.com/photo-1586790170083-2f9ceadc732d?w=800&h=600&fit=crop', // Ceramic art
    
    'yoga-beach-1.jpg' => 'https://images.unsplash.com/photo-1506126613408-eca07ce68773?w=800&h=600&fit=crop', // Beach yoga
    'yoga-beach-2.jpg' => 'https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?w=800&h=600&fit=crop', // Yoga on beach
    
    // Classes (different subjects)
    'cooking-class-1.jpg' => 'https://images.unsplash.com/photo-1556910103-1c02745aae4d?w=800&h=600&fit=crop', // Cooking class
    'cooking-class-2.jpg' => 'https://images.unsplash.com/photo-1555939594-58d7cb561ad1?w=800&h=600&fit=crop', // Food prep
    
    'twi-class-1.jpg' => 'https://images.unsplash.com/photo-1434030216411-0b793f4b4173?w=800&h=600&fit=crop', // Language learning
    
    'dance-class-1.jpg' => 'https://images.unsplash.com/photo-1516450360452-9312f5e86fc7?w=800&h=600&fit=crop', // Dance class
    'dance-class-2.jpg' => 'https://images.unsplash.com/photo-1506126613408-eca07ce68773?w=800&h=600&fit=crop', // Dancing
    
    'paint-wine-1.jpg' => 'https://images.unsplash.com/photo-1541961017774-22349e4a1262?w=800&h=600&fit=crop', // Painting class
    'paint-wine-2.jpg' => 'https://images.unsplash.com/photo-1513475382585-d06e58bcb0e0?w=800&h=600&fit=crop', // Art class
    
    // Tours (different locations)
    'city-tour-1.jpg' => 'https://images.unsplash.com/photo-1488646953014-85cb44e25828?w=800&h=600&fit=crop', // City walking
    'city-tour-2.jpg' => 'https://images.unsplash.com/photo-1514525253161-7a46d19cd819?w=800&h=600&fit=crop', // Urban tour
    
    'kakum-tour-1.jpg' => 'https://images.unsplash.com/photo-1441974231531-c6227db76b6e?w=800&h=600&fit=crop', // Forest canopy
    'kakum-tour-2.jpg' => 'https://images.unsplash.com/photo-1441974231531-c6227db76b6e?w=800&h=600&fit=crop', // Nature walk
    'kakum-tour-3.jpg' => 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&h=600&fit=crop', // Forest bridge
    
    'castle-tour-1.jpg' => 'https://images.unsplash.com/photo-1515542622106-78bda8ba0e5b?w=800&h=600&fit=crop', // Historic castle
    'castle-tour-2.jpg' => 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&h=600&fit=crop', // Historical site
    
    'aburi-tour-1.jpg' => 'https://images.unsplash.com/photo-1441974231531-c6227db76b6e?w=800&h=600&fit=crop', // Botanical garden
    'aburi-tour-2.jpg' => 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&h=600&fit=crop', // Garden paths
    
    'heritage-tour-1.jpg' => 'https://images.unsplash.com/photo-1515542622106-78bda8ba0e5b?w=800&h=600&fit=crop', // Monument
    'heritage-tour-2.jpg' => 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&h=600&fit=crop', // Historical
    
    'market-tour-1.jpg' => 'https://images.unsplash.com/photo-1488646953014-85cb44e25828?w=800&h=600&fit=crop', // Market scene
    'market-tour-2.jpg' => 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=800&h=600&fit=crop', // Market vendors
    
    // Pop-ups (different themes)
    'artisan-market-1.jpg' => 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=800&h=600&fit=crop', // Craft market
    'artisan-market-2.jpg' => 'https://images.unsplash.com/photo-1586790170083-2f9ceadc732d?w=800&h=600&fit=crop', // Handmade items
    
    'street-food-1.jpg' => 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=800&h=600&fit=crop', // Street food
    'street-food-2.jpg' => 'https://images.unsplash.com/photo-1555939594-58d7cb561ad1?w=800&h=600&fit=crop', // Food vendors
    
    'vintage-market-1.jpg' => 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=800&h=600&fit=crop', // Vintage market
    'vintage-market-2.jpg' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=800&h=600&fit=crop', // Thrift shopping
    
    'coffee-popup-1.jpg' => 'https://images.unsplash.com/photo-1509042239860-f550ce710b93?w=800&h=600&fit=crop', // Coffee shop
    'coffee-popup-2.jpg' => 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?w=800&h=600&fit=crop', // Coffee tasting
    
    'wellness-popup-1.jpg' => 'https://images.unsplash.com/photo-1506126613408-eca07ce68773?w=800&h=600&fit=crop', // Wellness
    'wellness-popup-2.jpg' => 'https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?w=800&h=600&fit=crop', // Self-care
    
    // Sports (different activities)
    'beach-volleyball-1.jpg' => 'https://images.unsplash.com/photo-1544551763-46a013bb70d5?w=800&h=600&fit=crop', // Beach volleyball
    'beach-volleyball-2.jpg' => 'https://images.unsplash.com/photo-1517649763962-0c623066013b?w=800&h=600&fit=crop', // Sports
    
    'beach-run-1.jpg' => 'https://images.unsplash.com/photo-1544551763-46a013bb70d5?w=800&h=600&fit=crop', // Beach running
    'beach-run-2.jpg' => 'https://images.unsplash.com/photo-1571008887538-b36bb32f4571?w=800&h=600&fit=crop', // Running
    
    'football-tournament-1.jpg' => 'https://images.unsplash.com/photo-1431324155629-1a6deb1dec8d?w=800&h=600&fit=crop', // Football
    'football-tournament-2.jpg' => 'https://images.unsplash.com/photo-1517649763962-0c623066013b?w=800&h=600&fit=crop', // Soccer
    
    // Game Nights (different games)
    'game-night-1.jpg' => 'https://images.unsplash.com/photo-1511512578047-dfb367046420?w=800&h=600&fit=crop', // Board games
    'game-night-2.jpg' => 'https://images.unsplash.com/photo-1606092195730-5d7b9af1efc5?w=800&h=600&fit=crop', // Social games
    
    'trivia-night-1.jpg' => 'https://images.unsplash.com/photo-1511512578047-dfb367046420?w=800&h=600&fit=crop', // Pub quiz
    'trivia-night-2.jpg' => 'https://images.unsplash.com/photo-1516450360452-9312f5e86fc7?w=800&h=600&fit=crop', // Quiz night
    
    // Meetups (different themes)
    'tech-meetup-1.jpg' => 'https://images.unsplash.com/photo-1524178232363-1fb2b075b655?w=800&h=600&fit=crop', // Tech networking
    'book-club-1.jpg' => 'https://images.unsplash.com/photo-1544947950-fa07a98d237f?w=800&h=600&fit=crop', // Book reading
    'photography-walk-1.jpg' => 'https://images.unsplash.com/photo-1502920917128-1aa500764cbd?w=800&h=600&fit=crop', // Photography
    'photography-walk-2.jpg' => 'https://images.unsplash.com/photo-1488646953014-85cb44e25828?w=800&h=600&fit=crop', // Street photography
    
    // Nightlife (different vibes)
    'rooftop-party-1.jpg' => 'https://images.unsplash.com/photo-1514933651103-005eec06c04b?w=800&h=600&fit=crop', // Rooftop
    'rooftop-party-2.jpg' => 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=800&h=600&fit=crop', // Night party
    
    'jazz-night-1.jpg' => 'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?w=800&h=600&fit=crop', // Jazz club
    'jazz-night-2.jpg' => 'https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?w=800&h=600&fit=crop', // Live jazz
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
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
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

// Ensure directory exists
$activity_dir = __DIR__ . '/../uploads/activities/';
if (!is_dir($activity_dir)) {
    mkdir($activity_dir, 0755, true);
}

// Download activity images
echo "Downloading activity images...\n";
$downloaded = 0;
$failed = 0;
$skipped = 0;

foreach ($activity_images as $filename => $url) {
    $filepath = $activity_dir . $filename;
    
    // Skip if already exists
    if (file_exists($filepath)) {
        echo "  ⏭  Skipping $filename (already exists)\n";
        $skipped++;
        continue;
    }
    
    echo "  ⬇  Downloading $filename...\n";
    if (download_image($url, $filepath)) {
        echo "  ✓  Downloaded $filename\n";
        $downloaded++;
    } else {
        echo "  ✗  Failed to download $filename\n";
        $failed++;
    }
    
    // Small delay to be respectful
    usleep(500000); // 0.5 seconds
}

echo "\n=== SUMMARY ===\n";
echo "Activity images downloaded: $downloaded\n";
echo "Activity images skipped: $skipped\n";
echo "Activity images failed: $failed\n";
echo "\nTotal images processed: " . ($downloaded + $skipped + $failed) . "\n";

