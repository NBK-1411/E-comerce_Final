<?php
/**
 * Populate Database with Real Ghana Activities
 * 
 * This script creates host users (if needed) and populates the database with real activities
 * from Ghana including concerts, festivals, workshops, tours, and more.
 * 
 * Usage: Run from command line: php populate_ghana_activities.php
 */

require_once(__DIR__ . '/../settings/db_class.php');
require_once(__DIR__ . '/../classes/customer_class.php');

// Set execution time limit
set_time_limit(300); // 5 minutes

$db = new db_connection();
$connection = $db->db_conn();

// Get some existing host users or create new ones
$customer = new Customer();

// Real Ghana Activities Data
$activities_data = [
    // CONCERTS
    [
        'host' => [
            'name' => 'DJ Black',
            'email' => 'dj.black@activities.gh',
            'password' => 'password123',
            'country' => 'Ghana',
            'city' => 'Accra',
            'contact' => '+233 24 111 2222'
        ],
        'activity' => [
            'title' => 'Afrobeat Night Live Concert',
            'description' => 'Experience the best of Ghanaian and African music at this electrifying live concert. Featuring top local artists, DJ sets, and an amazing atmosphere. Dance the night away to afrobeat, highlife, and contemporary African sounds.',
            'activity_type' => 'concert',
            'location_text' => 'Republic Bar & Grill, Osu, Accra',
            'gps_code' => 'GA-030-2001',
            'start_at' => date('Y-m-d H:i:s', strtotime('+2 weeks Saturday 8:00 PM')),
            'end_at' => date('Y-m-d H:i:s', strtotime('+2 weeks Saturday 2:00 AM')),
            'capacity' => 300,
            'price_min' => 50.00,
            'price_max' => 150.00,
            'is_free' => 0,
            'photos' => ['afrobeat-concert-1.jpg', 'afrobeat-concert-2.jpg', 'afrobeat-concert-3.jpg'],
            'venue_id' => null // Will link to Republic Bar if exists
        ]
    ],
    [
        'host' => [
            'name' => 'Music Promoter Accra',
            'email' => 'music.promoter@activities.gh',
            'password' => 'password123',
            'country' => 'Ghana',
            'city' => 'Accra',
            'contact' => '+233 24 222 3333'
        ],
        'activity' => [
            'title' => 'Reggae Night with Live Band',
            'description' => 'Join us for an unforgettable reggae night featuring live bands, authentic Jamaican vibes, and the best reggae music. Enjoy great food, drinks, and the warm community atmosphere.',
            'activity_type' => 'concert',
            'location_text' => 'Labadi Beach, Accra, Ghana',
            'gps_code' => 'GA-030-2002',
            'start_at' => date('Y-m-d H:i:s', strtotime('+3 weeks Friday 7:00 PM')),
            'end_at' => date('Y-m-d H:i:s', strtotime('+3 weeks Friday 11:00 PM')),
            'capacity' => 200,
            'price_min' => 40.00,
            'price_max' => 100.00,
            'is_free' => 0,
            'photos' => ['reggae-night-1.jpg', 'reggae-night-2.jpg'],
            'venue_id' => null
        ]
    ],
    
    // FESTIVALS
    [
        'host' => [
            'name' => 'Festival Organizer Ghana',
            'email' => 'festival.organizer@activities.gh',
            'password' => 'password123',
            'country' => 'Ghana',
            'city' => 'Accra',
            'contact' => '+233 24 333 4444'
        ],
        'activity' => [
            'title' => 'Accra Food & Culture Festival',
            'description' => 'A celebration of Ghanaian cuisine, culture, and music. Sample traditional dishes, watch cooking demonstrations, enjoy live performances, and shop from local artisans. A family-friendly event showcasing the best of Ghana.',
            'activity_type' => 'festival',
            'location_text' => 'Independence Square, Accra, Ghana',
            'gps_code' => 'GA-030-2003',
            'start_at' => date('Y-m-d H:i:s', strtotime('+1 month Saturday 10:00 AM')),
            'end_at' => date('Y-m-d H:i:s', strtotime('+1 month Saturday 8:00 PM')),
            'capacity' => 1000,
            'price_min' => 30.00,
            'price_max' => 80.00,
            'is_free' => 0,
            'photos' => ['food-festival-1.jpg', 'food-festival-2.jpg', 'food-festival-3.jpg'],
            'venue_id' => null
        ]
    ],
    [
        'host' => [
            'name' => 'Arts & Culture Hub',
            'email' => 'arts.culture@activities.gh',
            'password' => 'password123',
            'country' => 'Ghana',
            'city' => 'Accra',
            'contact' => '+233 24 444 5555'
        ],
        'activity' => [
            'title' => 'Chale Wote Street Art Festival',
            'description' => 'Join the vibrant Chale Wote Street Art Festival featuring live art installations, music performances, fashion shows, and cultural displays. Experience the creative energy of Accra\'s art scene.',
            'activity_type' => 'festival',
            'location_text' => 'James Town, Accra, Ghana',
            'gps_code' => 'GA-030-2004',
            'start_at' => date('Y-m-d H:i:s', strtotime('+6 weeks Saturday 9:00 AM')),
            'end_at' => date('Y-m-d H:i:s', strtotime('+6 weeks Saturday 6:00 PM')),
            'capacity' => 2000,
            'price_min' => 0.00,
            'price_max' => 0.00,
            'is_free' => 1,
            'photos' => ['chale-wote-1.jpg', 'chale-wote-2.jpg', 'chale-wote-3.jpg'],
            'venue_id' => null
        ]
    ],
    
    // WORKSHOPS & CLASSES
    [
        'host' => [
            'name' => 'Creative Workshops Ghana',
            'email' => 'creative.workshops@activities.gh',
            'password' => 'password123',
            'country' => 'Ghana',
            'city' => 'Accra',
            'contact' => '+233 24 555 6666'
        ],
        'activity' => [
            'title' => 'Traditional Kente Weaving Workshop',
            'description' => 'Learn the ancient art of Kente weaving from master craftsmen. This hands-on workshop teaches you the techniques, patterns, and cultural significance of Ghana\'s iconic textile. Take home your own woven piece!',
            'activity_type' => 'workshop',
            'location_text' => 'Bonwire, Ashanti Region, Ghana',
            'gps_code' => 'GA-030-2005',
            'start_at' => date('Y-m-d H:i:s', strtotime('+1 week Saturday 10:00 AM')),
            'end_at' => date('Y-m-d H:i:s', strtotime('+1 week Saturday 4:00 PM')),
            'capacity' => 15,
            'price_min' => 150.00,
            'price_max' => 150.00,
            'is_free' => 0,
            'photos' => ['kente-workshop-1.jpg', 'kente-workshop-2.jpg'],
            'venue_id' => null
        ]
    ],
    [
        'host' => [
            'name' => 'Cooking School Accra',
            'email' => 'cooking.school@activities.gh',
            'password' => 'password123',
            'country' => 'Ghana',
            'city' => 'Accra',
            'contact' => '+233 24 666 7777'
        ],
        'activity' => [
            'title' => 'Ghanaian Cooking Class: Jollof & More',
            'description' => 'Master the art of cooking authentic Ghanaian dishes including jollof rice, banku, and kelewele. Learn from experienced local chefs, enjoy the food you prepare, and take home recipes. Perfect for food lovers!',
            'activity_type' => 'class',
            'location_text' => 'East Legon, Accra, Ghana',
            'gps_code' => 'GA-030-2006',
            'start_at' => date('Y-m-d H:i:s', strtotime('+2 weeks Sunday 2:00 PM')),
            'end_at' => date('Y-m-d H:i:s', strtotime('+2 weeks Sunday 6:00 PM')),
            'capacity' => 12,
            'price_min' => 120.00,
            'price_max' => 120.00,
            'is_free' => 0,
            'photos' => ['cooking-class-1.jpg', 'cooking-class-2.jpg'],
            'venue_id' => null
        ]
    ],
    [
        'host' => [
            'name' => 'Drumming Academy',
            'email' => 'drumming.academy@activities.gh',
            'password' => 'password123',
            'country' => 'Ghana',
            'city' => 'Accra',
            'contact' => '+233 24 777 8888'
        ],
        'activity' => [
            'title' => 'Traditional Drumming & Dance Workshop',
            'description' => 'Experience the rhythm of Ghana! Learn traditional drumming techniques and dance moves from expert instructors. Connect with Ghanaian culture through music and movement. All skill levels welcome.',
            'activity_type' => 'workshop',
            'location_text' => 'Cantonments, Accra, Ghana',
            'gps_code' => 'GA-030-2007',
            'start_at' => date('Y-m-d H:i:s', strtotime('+3 weeks Saturday 3:00 PM')),
            'end_at' => date('Y-m-d H:i:s', strtotime('+3 weeks Saturday 6:00 PM')),
            'capacity' => 20,
            'price_min' => 80.00,
            'price_max' => 80.00,
            'is_free' => 0,
            'photos' => ['drumming-workshop-1.jpg', 'drumming-workshop-2.jpg'],
            'venue_id' => null
        ]
    ],
    
    // TOURS
    [
        'host' => [
            'name' => 'Ghana Tours & Experiences',
            'email' => 'ghana.tours@activities.gh',
            'password' => 'password123',
            'country' => 'Ghana',
            'city' => 'Accra',
            'contact' => '+233 24 888 9999'
        ],
        'activity' => [
            'title' => 'Accra City Walking Tour',
            'description' => 'Discover the history and culture of Accra on this guided walking tour. Visit historic sites, markets, and cultural landmarks. Learn about Ghana\'s independence, local traditions, and modern city life from knowledgeable guides.',
            'activity_type' => 'tour',
            'location_text' => 'Independence Square, Accra, Ghana',
            'gps_code' => 'GA-030-2008',
            'start_at' => date('Y-m-d H:i:s', strtotime('+4 days Saturday 9:00 AM')),
            'end_at' => date('Y-m-d H:i:s', strtotime('+4 days Saturday 1:00 PM')),
            'capacity' => 25,
            'price_min' => 60.00,
            'price_max' => 60.00,
            'is_free' => 0,
            'photos' => ['city-tour-1.jpg', 'city-tour-2.jpg'],
            'venue_id' => null
        ]
    ],
    [
        'host' => [
            'name' => 'Kakum Canopy Walk Tours',
            'email' => 'kakum.tours@activities.gh',
            'password' => 'password123',
            'country' => 'Ghana',
            'city' => 'Cape Coast',
            'contact' => '+233 24 999 0000'
        ],
        'activity' => [
            'title' => 'Kakum National Park Canopy Walk',
            'description' => 'Experience the breathtaking canopy walkway through the rainforest. Walk 40 meters above the forest floor, spot wildlife, and learn about the ecosystem from expert guides. Includes transportation from Accra.',
            'activity_type' => 'tour',
            'location_text' => 'Kakum National Park, Central Region, Ghana',
            'gps_code' => 'GA-030-2009',
            'start_at' => date('Y-m-d H:i:s', strtotime('+2 weeks Sunday 7:00 AM')),
            'end_at' => date('Y-m-d H:i:s', strtotime('+2 weeks Sunday 5:00 PM')),
            'capacity' => 30,
            'price_min' => 200.00,
            'price_max' => 200.00,
            'is_free' => 0,
            'photos' => ['kakum-tour-1.jpg', 'kakum-tour-2.jpg', 'kakum-tour-3.jpg'],
            'venue_id' => null
        ]
    ],
    [
        'host' => [
            'name' => 'Cape Coast Castle Tours',
            'email' => 'cape.coast.tours@activities.gh',
            'password' => 'password123',
            'country' => 'Ghana',
            'city' => 'Cape Coast',
            'contact' => '+233 24 000 1111'
        ],
        'activity' => [
            'title' => 'Cape Coast Castle & Elmina Castle Tour',
            'description' => 'Visit two UNESCO World Heritage sites and learn about Ghana\'s history. Guided tour of Cape Coast Castle and Elmina Castle, including the dungeons and museums. A powerful and educational experience.',
            'activity_type' => 'tour',
            'location_text' => 'Cape Coast, Central Region, Ghana',
            'gps_code' => 'GA-030-2010',
            'start_at' => date('Y-m-d H:i:s', strtotime('+3 weeks Saturday 8:00 AM')),
            'end_at' => date('Y-m-d H:i:s', strtotime('+3 weeks Saturday 4:00 PM')),
            'capacity' => 40,
            'price_min' => 150.00,
            'price_max' => 150.00,
            'is_free' => 0,
            'photos' => ['castle-tour-1.jpg', 'castle-tour-2.jpg'],
            'venue_id' => null
        ]
    ],
    
    // POP-UPS
    [
        'host' => [
            'name' => 'Pop-up Events Accra',
            'email' => 'popup.events@activities.gh',
            'password' => 'password123',
            'country' => 'Ghana',
            'city' => 'Accra',
            'contact' => '+233 24 111 2223'
        ],
        'activity' => [
            'title' => 'Artisan Market Pop-up',
            'description' => 'Discover unique handmade crafts, jewelry, clothing, and art from local artisans. Support local creators while finding one-of-a-kind pieces. Food vendors, live music, and a vibrant community atmosphere.',
            'activity_type' => 'popup',
            'location_text' => 'Osu, Accra, Ghana',
            'gps_code' => 'GA-030-2011',
            'start_at' => date('Y-m-d H:i:s', strtotime('+1 week Sunday 10:00 AM')),
            'end_at' => date('Y-m-d H:i:s', strtotime('+1 week Sunday 4:00 PM')),
            'capacity' => 200,
            'price_min' => 0.00,
            'price_max' => 0.00,
            'is_free' => 1,
            'photos' => ['artisan-market-1.jpg', 'artisan-market-2.jpg'],
            'venue_id' => null
        ]
    ],
    [
        'host' => [
            'name' => 'Food Pop-ups Ghana',
            'email' => 'food.popups@activities.gh',
            'password' => 'password123',
            'country' => 'Ghana',
            'city' => 'Accra',
            'contact' => '+233 24 222 3334'
        ],
        'activity' => [
            'title' => 'Street Food Festival Pop-up',
            'description' => 'Taste the best street food Accra has to offer! Sample waakye, kelewele, bofrot, and more from the city\'s top street vendors. Live cooking demonstrations and a celebration of Ghanaian street food culture.',
            'activity_type' => 'popup',
            'location_text' => 'Oxford Street, Osu, Accra, Ghana',
            'gps_code' => 'GA-030-2012',
            'start_at' => date('Y-m-d H:i:s', strtotime('+2 weeks Friday 5:00 PM')),
            'end_at' => date('Y-m-d H:i:s', strtotime('+2 weeks Friday 10:00 PM')),
            'capacity' => 300,
            'price_min' => 20.00,
            'price_max' => 50.00,
            'is_free' => 0,
            'photos' => ['street-food-1.jpg', 'street-food-2.jpg'],
            'venue_id' => null
        ]
    ],
    
    // SPORTS
    [
        'host' => [
            'name' => 'Sports Events Ghana',
            'email' => 'sports.events@activities.gh',
            'password' => 'password123',
            'country' => 'Ghana',
            'city' => 'Accra',
            'contact' => '+233 24 333 4445'
        ],
        'activity' => [
            'title' => 'Beach Volleyball Tournament',
            'description' => 'Join or watch an exciting beach volleyball tournament at Labadi Beach. Teams compete for prizes, enjoy beach vibes, music, and refreshments. Open to all skill levels. Register your team or come to cheer!',
            'activity_type' => 'sports',
            'location_text' => 'Labadi Beach, Accra, Ghana',
            'gps_code' => 'GA-030-2013',
            'start_at' => date('Y-m-d H:i:s', strtotime('+3 weeks Saturday 8:00 AM')),
            'end_at' => date('Y-m-d H:i:s', strtotime('+3 weeks Saturday 6:00 PM')),
            'capacity' => 100,
            'price_min' => 30.00,
            'price_max' => 30.00,
            'is_free' => 0,
            'photos' => ['beach-volleyball-1.jpg', 'beach-volleyball-2.jpg'],
            'venue_id' => null
        ]
    ],
    
    // GAME NIGHTS
    [
        'host' => [
            'name' => 'Game Night Organizers',
            'email' => 'game.night@activities.gh',
            'password' => 'password123',
            'country' => 'Ghana',
            'city' => 'Accra',
            'contact' => '+233 24 444 5556'
        ],
        'activity' => [
            'title' => 'Board Game Night & Social',
            'description' => 'Unwind with friends over board games, card games, and puzzles. A relaxed social evening with snacks, drinks, and great company. Perfect for meeting new people and having fun. Games provided!',
            'activity_type' => 'game_night',
            'location_text' => 'Cantonments, Accra, Ghana',
            'gps_code' => 'GA-030-2014',
            'start_at' => date('Y-m-d H:i:s', strtotime('+1 week Friday 6:00 PM')),
            'end_at' => date('Y-m-d H:i:s', strtotime('+1 week Friday 10:00 PM')),
            'capacity' => 40,
            'price_min' => 25.00,
            'price_max' => 25.00,
            'is_free' => 0,
            'photos' => ['game-night-1.jpg', 'game-night-2.jpg'],
            'venue_id' => null
        ]
    ],
    
    // MEETUPS
    [
        'host' => [
            'name' => 'Tech Meetup Accra',
            'email' => 'tech.meetup@activities.gh',
            'password' => 'password123',
            'country' => 'Ghana',
            'city' => 'Accra',
            'contact' => '+233 24 555 6667'
        ],
        'activity' => [
            'title' => 'Tech Entrepreneurs Networking Meetup',
            'description' => 'Connect with fellow tech entrepreneurs, developers, and innovators. Share ideas, network, and learn from guest speakers. Light refreshments provided. Open to all in the tech community.',
            'activity_type' => 'meetup',
            'location_text' => 'Airport City, Accra, Ghana',
            'gps_code' => 'GA-030-2015',
            'start_at' => date('Y-m-d H:i:s', strtotime('+2 weeks Wednesday 6:00 PM')),
            'end_at' => date('Y-m-d H:i:s', strtotime('+2 weeks Wednesday 9:00 PM')),
            'capacity' => 50,
            'price_min' => 0.00,
            'price_max' => 0.00,
            'is_free' => 1,
            'photos' => ['tech-meetup-1.jpg'],
            'venue_id' => null
        ]
    ],
    
    // NIGHTLIFE
    [
        'host' => [
            'name' => 'Nightlife Events Accra',
            'email' => 'nightlife.events@activities.gh',
            'password' => 'password123',
            'country' => 'Ghana',
            'city' => 'Accra',
            'contact' => '+233 24 666 7778'
        ],
        'activity' => [
            'title' => 'Rooftop Party: Sunset to Sunrise',
            'description' => 'Experience Accra from above at this exclusive rooftop party. Enjoy cocktails, DJ sets, stunning city views, and dancing under the stars. Dress to impress!',
            'activity_type' => 'nightlife',
            'location_text' => 'Airport City, Accra, Ghana',
            'gps_code' => 'GA-030-2016',
            'start_at' => date('Y-m-d H:i:s', strtotime('+4 weeks Saturday 6:00 PM')),
            'end_at' => date('Y-m-d H:i:s', strtotime('+4 weeks Sunday 2:00 AM')),
            'capacity' => 250,
            'price_min' => 80.00,
            'price_max' => 150.00,
            'is_free' => 0,
            'photos' => ['rooftop-party-1.jpg', 'rooftop-party-2.jpg'],
            'venue_id' => null
        ]
    ]
];

// Function to create host user
function create_host_user($host_data, $customer) {
    // Check if user already exists
    $existing = $customer->get_customer_by_email($host_data['email']);
    if ($existing) {
        return $existing['customer_id'];
    }
    
    // Create new host user (role 3 = venue owner, can also host activities)
    $result = $customer->register_customer(
        $host_data['name'],
        $host_data['email'],
        $host_data['password'],
        $host_data['country'],
        $host_data['city'],
        $host_data['contact'],
        3 // role_id = 3 for venue owner/activity host
    );
    
    if ($result) {
        $user = $customer->get_customer_by_email($host_data['email']);
        // Auto-verify host users for seeding
        $customer->verify_customer($user['customer_id']);
        return $user['customer_id'];
    }
    
    return false;
}

// Function to find venue by name (for linking)
function find_venue_by_name($venue_name, $connection) {
    $venue_name_escaped = $connection->real_escape_string($venue_name);
    $result = $connection->query("SELECT venue_id FROM venue WHERE title LIKE '%$venue_name_escaped%' LIMIT 1");
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['venue_id'];
    }
    return null;
}

// Function to create activity using direct SQL
function create_activity($activity_data, $host_id, $connection) {
    // Prepare photos JSON
    $photos = [];
    foreach ($activity_data['photos'] as $photo) {
        $photos[] = '../uploads/activities/' . $photo;
    }
    $photos_json = json_encode($photos);
    
    // Find venue if specified
    $venue_id = 'NULL';
    if (!empty($activity_data['venue_id'])) {
        $venue_id = intval($activity_data['venue_id']);
    } elseif (!empty($activity_data['venue_name'])) {
        $found_venue_id = find_venue_by_name($activity_data['venue_name'], $connection);
        if ($found_venue_id) {
            $venue_id = $found_venue_id;
        }
    }
    
    // Escape strings for SQL
    $title = $connection->real_escape_string($activity_data['title']);
    $description = $connection->real_escape_string($activity_data['description']);
    $activity_type = $connection->real_escape_string($activity_data['activity_type']);
    $location_text = $connection->real_escape_string($activity_data['location_text']);
    $gps_code = $connection->real_escape_string($activity_data['gps_code'] ?? '');
    $photos_json_escaped = $connection->real_escape_string($photos_json);
    
    $start_at = $connection->real_escape_string($activity_data['start_at']);
    $end_at = !empty($activity_data['end_at']) ? "'" . $connection->real_escape_string($activity_data['end_at']) . "'" : 'NULL';
    $capacity = !empty($activity_data['capacity']) ? intval($activity_data['capacity']) : 'NULL';
    $is_free = isset($activity_data['is_free']) && $activity_data['is_free'] ? 1 : 0;
    
    // Insert directly using SQL
    $sql = "INSERT INTO activities (title, description, activity_type, recurrence_type, host_id, venue_id, location_text, gps_code, 
            start_at, end_at, capacity, price_min, price_max, is_free, photos_json, status) 
            VALUES ('$title', '$description', '$activity_type', 'none', $host_id, $venue_id, '$location_text', '$gps_code', 
            '$start_at', $end_at, $capacity, {$activity_data['price_min']}, {$activity_data['price_max']}, $is_free, 
            '$photos_json_escaped', 'approved')";
    
    if ($connection->query($sql)) {
        $activity_id = $connection->insert_id;
        return $activity_id;
    } else {
        echo "  SQL Error: " . $connection->error . "\n";
        return false;
    }
}

// Main execution
echo "Starting activity population...\n";
$created = 0;
$errors = 0;

foreach ($activities_data as $index => $data) {
    echo "\nProcessing activity " . ($index + 1) . ": " . $data['activity']['title'] . "\n";
    
    // Create host user
    $host_id = create_host_user($data['host'], $customer);
    if (!$host_id) {
        echo "  ERROR: Failed to create host user\n";
        $errors++;
        continue;
    }
    echo "  ✓ Host user created/retrieved (ID: $host_id)\n";
    
    // Create activity
    $activity_id = create_activity($data['activity'], $host_id, $connection);
    if (!$activity_id) {
        echo "  ERROR: Failed to create activity\n";
        $errors++;
        continue;
    }
    echo "  ✓ Activity created (ID: $activity_id)\n";
    
    $created++;
}

echo "\n\n=== SUMMARY ===\n";
echo "Activities created: $created\n";
echo "Errors: $errors\n";
echo "\nNote: Images need to be added manually to uploads/activities/ directory.\n";
echo "The script has created placeholder image references.\n";

