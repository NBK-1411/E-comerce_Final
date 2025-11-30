<?php
/**
 * Populate Database with More Varied Ghana Activities
 * 
 * Adds diverse activities with different types, dates, and locations
 */

require_once(__DIR__ . '/../settings/db_class.php');
require_once(__DIR__ . '/../classes/customer_class.php');

set_time_limit(300);

$db = new db_connection();
$connection = $db->db_conn();
$customer = new Customer();

// More varied activities with different types
$activities_data = [
    // MORE WORKSHOPS
    [
        'host' => [
            'name' => 'Artisan Collective',
            'email' => 'artisan.collective@activities.gh',
            'password' => 'password123',
            'country' => 'Ghana',
            'city' => 'Kumasi',
            'contact' => '+233 24 111 3333'
        ],
        'activity' => [
            'title' => 'Bead Making Workshop',
            'description' => 'Learn traditional Ghanaian bead making techniques. Create your own colorful beads using traditional methods passed down through generations. Take home your handmade jewelry pieces.',
            'activity_type' => 'workshop',
            'location_text' => 'Kumasi Cultural Centre, Kumasi, Ghana',
            'gps_code' => 'GA-030-3001',
            'start_at' => date('Y-m-d H:i:s', strtotime('+5 days Saturday 10:00 AM')),
            'end_at' => date('Y-m-d H:i:s', strtotime('+5 days Saturday 3:00 PM')),
            'capacity' => 12,
            'price_min' => 100.00,
            'price_max' => 100.00,
            'is_free' => 0,
            'photos' => ['bead-workshop-1.jpg', 'bead-workshop-2.jpg'],
            'venue_id' => null
        ]
    ],
    [
        'host' => [
            'name' => 'Fitness & Wellness Ghana',
            'email' => 'fitness.wellness@activities.gh',
            'password' => 'password123',
            'country' => 'Ghana',
            'city' => 'Accra',
            'contact' => '+233 24 222 4444'
        ],
        'activity' => [
            'title' => 'Yoga & Meditation by the Beach',
            'description' => 'Start your day with yoga and meditation on the beautiful Labadi Beach. Connect with nature, find inner peace, and energize your body and mind. Suitable for all levels. Mats provided.',
            'activity_type' => 'workshop',
            'location_text' => 'Labadi Beach, Accra, Ghana',
            'gps_code' => 'GA-030-3002',
            'start_at' => date('Y-m-d H:i:s', strtotime('+6 days Sunday 6:30 AM')),
            'end_at' => date('Y-m-d H:i:s', strtotime('+6 days Sunday 8:00 AM')),
            'capacity' => 25,
            'price_min' => 40.00,
            'price_max' => 40.00,
            'is_free' => 0,
            'photos' => ['yoga-beach-1.jpg', 'yoga-beach-2.jpg'],
            'venue_id' => null
        ]
    ],
    
    // MORE CLASSES
    [
        'host' => [
            'name' => 'Language Learning Center',
            'email' => 'language.center@activities.gh',
            'password' => 'password123',
            'country' => 'Ghana',
            'city' => 'Accra',
            'contact' => '+233 24 333 5555'
        ],
        'activity' => [
            'title' => 'Twi Language Class for Beginners',
            'description' => 'Learn basic Twi (Akan) language skills in a fun, interactive class. Perfect for expats, tourists, or anyone interested in Ghanaian culture. Learn greetings, common phrases, and cultural context.',
            'activity_type' => 'class',
            'location_text' => 'Osu, Accra, Ghana',
            'gps_code' => 'GA-030-3003',
            'start_at' => date('Y-m-d H:i:s', strtotime('+1 week Tuesday 6:00 PM')),
            'end_at' => date('Y-m-d H:i:s', strtotime('+1 week Tuesday 8:00 PM')),
            'capacity' => 20,
            'price_min' => 50.00,
            'price_max' => 50.00,
            'is_free' => 0,
            'photos' => ['twi-class-1.jpg'],
            'venue_id' => null
        ]
    ],
    [
        'host' => [
            'name' => 'Dance Academy Accra',
            'email' => 'dance.academy@activities.gh',
            'password' => 'password123',
            'country' => 'Ghana',
            'city' => 'Accra',
            'contact' => '+233 24 444 6666'
        ],
        'activity' => [
            'title' => 'Azonto & Afrobeats Dance Class',
            'description' => 'Learn popular Ghanaian dance moves including Azonto, Shoki, and modern Afrobeats choreography. Fun, energetic class with great music. No experience needed - just bring your energy!',
            'activity_type' => 'class',
            'location_text' => 'East Legon, Accra, Ghana',
            'gps_code' => 'GA-030-3004',
            'start_at' => date('Y-m-d H:i:s', strtotime('+1 week Thursday 7:00 PM')),
            'end_at' => date('Y-m-d H:i:s', strtotime('+1 week Thursday 8:30 PM')),
            'capacity' => 30,
            'price_min' => 60.00,
            'price_max' => 60.00,
            'is_free' => 0,
            'photos' => ['dance-class-1.jpg', 'dance-class-2.jpg'],
            'venue_id' => null
        ]
    ],
    
    // MORE TOURS
    [
        'host' => [
            'name' => 'Eco Tours Ghana',
            'email' => 'eco.tours@activities.gh',
            'password' => 'password123',
            'country' => 'Ghana',
            'city' => 'Accra',
            'contact' => '+233 24 555 7777'
        ],
        'activity' => [
            'title' => 'Aburi Botanical Gardens Tour',
            'description' => 'Explore the beautiful Aburi Botanical Gardens in the Akuapem Hills. Guided tour of exotic plants, scenic views, and peaceful walking paths. Perfect for nature lovers and photography enthusiasts.',
            'activity_type' => 'tour',
            'location_text' => 'Aburi, Eastern Region, Ghana',
            'gps_code' => 'GA-030-3005',
            'start_at' => date('Y-m-d H:i:s', strtotime('+2 weeks Saturday 9:00 AM')),
            'end_at' => date('Y-m-d H:i:s', strtotime('+2 weeks Saturday 2:00 PM')),
            'capacity' => 35,
            'price_min' => 80.00,
            'price_max' => 80.00,
            'is_free' => 0,
            'photos' => ['aburi-tour-1.jpg', 'aburi-tour-2.jpg'],
            'venue_id' => null
        ]
    ],
    [
        'host' => [
            'name' => 'Heritage Tours',
            'email' => 'heritage.tours@activities.gh',
            'password' => 'password123',
            'country' => 'Ghana',
            'city' => 'Accra',
            'contact' => '+233 24 666 8888'
        ],
        'activity' => [
            'title' => 'Kwame Nkrumah Mausoleum & Independence Square Tour',
            'description' => 'Visit key historical sites in Accra including the Kwame Nkrumah Mausoleum, Independence Square, and Black Star Square. Learn about Ghana\'s independence struggle and first president.',
            'activity_type' => 'tour',
            'location_text' => 'Independence Square, Accra, Ghana',
            'gps_code' => 'GA-030-3006',
            'start_at' => date('Y-m-d H:i:s', strtotime('+3 weeks Saturday 10:00 AM')),
            'end_at' => date('Y-m-d H:i:s', strtotime('+3 weeks Saturday 1:00 PM')),
            'capacity' => 30,
            'price_min' => 50.00,
            'price_max' => 50.00,
            'is_free' => 0,
            'photos' => ['heritage-tour-1.jpg', 'heritage-tour-2.jpg'],
            'venue_id' => null
        ]
    ],
    
    // MORE POP-UPS
    [
        'host' => [
            'name' => 'Vintage Market Accra',
            'email' => 'vintage.market@activities.gh',
            'password' => 'password123',
            'country' => 'Ghana',
            'city' => 'Accra',
            'contact' => '+233 24 777 9999'
        ],
        'activity' => [
            'title' => 'Vintage & Thrift Market Pop-up',
            'description' => 'Discover unique vintage clothing, accessories, and second-hand treasures. Support sustainable fashion while finding one-of-a-kind pieces. Live DJ, food vendors, and a vibrant atmosphere.',
            'activity_type' => 'popup',
            'location_text' => 'Cantonments, Accra, Ghana',
            'gps_code' => 'GA-030-3007',
            'start_at' => date('Y-m-d H:i:s', strtotime('+2 weeks Sunday 11:00 AM')),
            'end_at' => date('Y-m-d H:i:s', strtotime('+2 weeks Sunday 5:00 PM')),
            'capacity' => 150,
            'price_min' => 0.00,
            'price_max' => 0.00,
            'is_free' => 1,
            'photos' => ['vintage-market-1.jpg', 'vintage-market-2.jpg'],
            'venue_id' => null
        ]
    ],
    [
        'host' => [
            'name' => 'Coffee Pop-up Events',
            'email' => 'coffee.popup@activities.gh',
            'password' => 'password123',
            'country' => 'Ghana',
            'city' => 'Accra',
            'contact' => '+233 24 888 0000'
        ],
        'activity' => [
            'title' => 'Specialty Coffee Tasting Pop-up',
            'description' => 'Experience Ghanaian specialty coffee with expert baristas. Learn about coffee origins, brewing methods, and taste different roasts. Perfect for coffee enthusiasts and beginners alike.',
            'activity_type' => 'popup',
            'location_text' => 'Airport City, Accra, Ghana',
            'gps_code' => 'GA-030-3008',
            'start_at' => date('Y-m-d H:i:s', strtotime('+1 week Saturday 2:00 PM')),
            'end_at' => date('Y-m-d H:i:s', strtotime('+1 week Saturday 5:00 PM')),
            'capacity' => 40,
            'price_min' => 35.00,
            'price_max' => 35.00,
            'is_free' => 0,
            'photos' => ['coffee-popup-1.jpg', 'coffee-popup-2.jpg'],
            'venue_id' => null
        ]
    ],
    
    // MORE SPORTS
    [
        'host' => [
            'name' => 'Running Club Accra',
            'email' => 'running.club@activities.gh',
            'password' => 'password123',
            'country' => 'Ghana',
            'city' => 'Accra',
            'contact' => '+233 24 999 1111'
        ],
        'activity' => [
            'title' => 'Sunrise Beach Run',
            'description' => 'Join fellow runners for a scenic 5K/10K run along Labadi Beach at sunrise. All fitness levels welcome. Enjoy the cool morning breeze and beautiful ocean views. Water and refreshments provided.',
            'activity_type' => 'sports',
            'location_text' => 'Labadi Beach, Accra, Ghana',
            'gps_code' => 'GA-030-3009',
            'start_at' => date('Y-m-d H:i:s', strtotime('+4 days Saturday 6:00 AM')),
            'end_at' => date('Y-m-d H:i:s', strtotime('+4 days Saturday 8:00 AM')),
            'capacity' => 50,
            'price_min' => 20.00,
            'price_max' => 20.00,
            'is_free' => 0,
            'photos' => ['beach-run-1.jpg', 'beach-run-2.jpg'],
            'venue_id' => null
        ]
    ],
    [
        'host' => [
            'name' => 'Football Academy',
            'email' => 'football.academy@activities.gh',
            'password' => 'password123',
            'country' => 'Ghana',
            'city' => 'Accra',
            'contact' => '+233 24 000 2222'
        ],
        'activity' => [
            'title' => '5-a-Side Football Tournament',
            'description' => 'Join a fun 5-a-side football tournament. Teams compete in a friendly but competitive atmosphere. Open to all skill levels. Trophies for winners. Refreshments available.',
            'activity_type' => 'sports',
            'location_text' => 'East Legon, Accra, Ghana',
            'gps_code' => 'GA-030-3010',
            'start_at' => date('Y-m-d H:i:s', strtotime('+2 weeks Sunday 3:00 PM')),
            'end_at' => date('Y-m-d H:i:s', strtotime('+2 weeks Sunday 7:00 PM')),
            'capacity' => 60,
            'price_min' => 40.00,
            'price_max' => 40.00,
            'is_free' => 0,
            'photos' => ['football-tournament-1.jpg', 'football-tournament-2.jpg'],
            'venue_id' => null
        ]
    ],
    
    // MORE MEETUPS
    [
        'host' => [
            'name' => 'Book Club Accra',
            'email' => 'book.club@activities.gh',
            'password' => 'password123',
            'country' => 'Ghana',
            'city' => 'Accra',
            'contact' => '+233 24 111 3334'
        ],
        'activity' => [
            'title' => 'Monthly Book Club Meeting',
            'description' => 'Join fellow book lovers for our monthly discussion. This month we\'re reading "Homegoing" by Yaa Gyasi. Share insights, debate themes, and connect with other readers. Light refreshments provided.',
            'activity_type' => 'meetup',
            'location_text' => 'Cantonments, Accra, Ghana',
            'gps_code' => 'GA-030-3011',
            'start_at' => date('Y-m-d H:i:s', strtotime('+2 weeks Wednesday 6:30 PM')),
            'end_at' => date('Y-m-d H:i:s', strtotime('+2 weeks Wednesday 8:30 PM')),
            'capacity' => 25,
            'price_min' => 0.00,
            'price_max' => 0.00,
            'is_free' => 1,
            'photos' => ['book-club-1.jpg'],
            'venue_id' => null
        ]
    ],
    [
        'host' => [
            'name' => 'Photography Meetup',
            'email' => 'photography.meetup@activities.gh',
            'password' => 'password123',
            'country' => 'Ghana',
            'city' => 'Accra',
            'contact' => '+233 24 222 4445'
        ],
        'activity' => [
            'title' => 'Street Photography Walk',
            'description' => 'Join fellow photographers for a guided walk through Accra\'s vibrant streets. Capture the city\'s energy, people, and culture. Share tips, techniques, and review each other\'s work. All camera types welcome.',
            'activity_type' => 'meetup',
            'location_text' => 'James Town, Accra, Ghana',
            'gps_code' => 'GA-030-3012',
            'start_at' => date('Y-m-d H:i:s', strtotime('+3 weeks Saturday 4:00 PM')),
            'end_at' => date('Y-m-d H:i:s', strtotime('+3 weeks Saturday 7:00 PM')),
            'capacity' => 20,
            'price_min' => 0.00,
            'price_max' => 0.00,
            'is_free' => 1,
            'photos' => ['photography-walk-1.jpg', 'photography-walk-2.jpg'],
            'venue_id' => null
        ]
    ],
    
    // MORE NIGHTLIFE
    [
        'host' => [
            'name' => 'Jazz Nights Accra',
            'email' => 'jazz.nights@activities.gh',
            'password' => 'password123',
            'country' => 'Ghana',
            'city' => 'Accra',
            'contact' => '+233 24 333 5556'
        ],
        'activity' => [
            'title' => 'Live Jazz Night',
            'description' => 'Enjoy smooth jazz performances by talented local musicians. Intimate setting with great acoustics, craft cocktails, and a sophisticated atmosphere. Perfect for a relaxed evening out.',
            'activity_type' => 'nightlife',
            'location_text' => 'Airport Residential, Accra, Ghana',
            'gps_code' => 'GA-030-3013',
            'start_at' => date('Y-m-d H:i:s', strtotime('+1 week Friday 8:00 PM')),
            'end_at' => date('Y-m-d H:i:s', strtotime('+1 week Friday 11:30 PM')),
            'capacity' => 80,
            'price_min' => 60.00,
            'price_max' => 60.00,
            'is_free' => 0,
            'photos' => ['jazz-night-1.jpg', 'jazz-night-2.jpg'],
            'venue_id' => null
        ]
    ],
    
    // MORE CONCERTS
    [
        'host' => [
            'name' => 'Acoustic Sessions',
            'email' => 'acoustic.sessions@activities.gh',
            'password' => 'password123',
            'country' => 'Ghana',
            'city' => 'Accra',
            'contact' => '+233 24 444 6667'
        ],
        'activity' => [
            'title' => 'Acoustic Unplugged Session',
            'description' => 'Intimate acoustic performance featuring local singer-songwriters. Raw, authentic music in a cozy setting. Perfect for music lovers who enjoy stripped-down, emotional performances.',
            'activity_type' => 'concert',
            'location_text' => 'Osu, Accra, Ghana',
            'gps_code' => 'GA-030-3014',
            'start_at' => date('Y-m-d H:i:s', strtotime('+2 weeks Thursday 7:00 PM')),
            'end_at' => date('Y-m-d H:i:s', strtotime('+2 weeks Thursday 10:00 PM')),
            'capacity' => 60,
            'price_min' => 45.00,
            'price_max' => 45.00,
            'is_free' => 0,
            'photos' => ['acoustic-session-1.jpg', 'acoustic-session-2.jpg'],
            'venue_id' => null
        ]
    ],
    
    // MORE FESTIVALS
    [
        'host' => [
            'name' => 'Music Festival Organizers',
            'email' => 'music.festival@activities.gh',
            'password' => 'password123',
            'country' => 'Ghana',
            'city' => 'Accra',
            'contact' => '+233 24 555 7778'
        ],
        'activity' => [
            'title' => 'Ghana Music Week Festival',
            'description' => 'Multi-day celebration of Ghanaian music featuring live performances, panel discussions, workshops, and networking events. Showcasing traditional highlife, modern afrobeats, and everything in between.',
            'activity_type' => 'festival',
            'location_text' => 'National Theatre, Accra, Ghana',
            'gps_code' => 'GA-030-3015',
            'start_at' => date('Y-m-d H:i:s', strtotime('+2 months Friday 10:00 AM')),
            'end_at' => date('Y-m-d H:i:s', strtotime('+2 months Sunday 10:00 PM')),
            'capacity' => 2000,
            'price_min' => 100.00,
            'price_max' => 300.00,
            'is_free' => 0,
            'photos' => ['music-festival-1.jpg', 'music-festival-2.jpg', 'music-festival-3.jpg'],
            'venue_id' => null
        ]
    ],
    
    // GAME NIGHTS VARIATION
    [
        'host' => [
            'name' => 'Trivia Night Accra',
            'email' => 'trivia.night@activities.gh',
            'password' => 'password123',
            'country' => 'Ghana',
            'city' => 'Accra',
            'contact' => '+233 24 666 8889'
        ],
        'activity' => [
            'title' => 'Pub Quiz & Trivia Night',
            'description' => 'Test your knowledge at our fun pub quiz! Teams compete in multiple rounds covering general knowledge, Ghana history, pop culture, and more. Prizes for winners. Great food and drinks available.',
            'activity_type' => 'game_night',
            'location_text' => 'Osu, Accra, Ghana',
            'gps_code' => 'GA-030-3016',
            'start_at' => date('Y-m-d H:i:s', strtotime('+1 week Tuesday 7:00 PM')),
            'end_at' => date('Y-m-d H:i:s', strtotime('+1 week Tuesday 10:00 PM')),
            'capacity' => 50,
            'price_min' => 30.00,
            'price_max' => 30.00,
            'is_free' => 0,
            'photos' => ['trivia-night-1.jpg', 'trivia-night-2.jpg'],
            'venue_id' => null
        ]
    ],
    
    // MORE WORKSHOPS VARIATION
    [
        'host' => [
            'name' => 'Pottery Studio',
            'email' => 'pottery.studio@activities.gh',
            'password' => 'password123',
            'country' => 'Ghana',
            'city' => 'Accra',
            'contact' => '+233 24 777 9990'
        ],
        'activity' => [
            'title' => 'Pottery Making Workshop',
            'description' => 'Learn the art of pottery making using traditional techniques. Create your own ceramic pieces on the wheel, learn glazing, and take home your finished work. All materials included.',
            'activity_type' => 'workshop',
            'location_text' => 'Cantonments, Accra, Ghana',
            'gps_code' => 'GA-030-3017',
            'start_at' => date('Y-m-d H:i:s', strtotime('+3 weeks Sunday 2:00 PM')),
            'end_at' => date('Y-m-d H:i:s', strtotime('+3 weeks Sunday 5:00 PM')),
            'capacity' => 10,
            'price_min' => 120.00,
            'price_max' => 120.00,
            'is_free' => 0,
            'photos' => ['pottery-workshop-1.jpg', 'pottery-workshop-2.jpg'],
            'venue_id' => null
        ]
    ],
    
    // MORE TOURS VARIATION
    [
        'host' => [
            'name' => 'Market Tours Ghana',
            'email' => 'market.tours@activities.gh',
            'password' => 'password123',
            'country' => 'Ghana',
            'city' => 'Accra',
            'contact' => '+233 24 888 0001'
        ],
        'activity' => [
            'title' => 'Makola Market Experience Tour',
            'description' => 'Explore Accra\'s largest market with a local guide. Learn about local products, practice bargaining, sample street food, and experience the vibrant atmosphere of this iconic market.',
            'activity_type' => 'tour',
            'location_text' => 'Makola Market, Accra, Ghana',
            'gps_code' => 'GA-030-3018',
            'start_at' => date('Y-m-d H:i:s', strtotime('+1 week Friday 9:00 AM')),
            'end_at' => date('Y-m-d H:i:s', strtotime('+1 week Friday 12:00 PM')),
            'capacity' => 15,
            'price_min' => 40.00,
            'price_max' => 40.00,
            'is_free' => 0,
            'photos' => ['market-tour-1.jpg', 'market-tour-2.jpg'],
            'venue_id' => null
        ]
    ],
    
    // MORE CLASSES VARIATION
    [
        'host' => [
            'name' => 'Art Studio Accra',
            'email' => 'art.studio@activities.gh',
            'password' => 'password123',
            'country' => 'Ghana',
            'city' => 'Accra',
            'contact' => '+233 24 999 1112'
        ],
        'activity' => [
            'title' => 'Painting & Wine Night',
            'description' => 'Unleash your creativity in a relaxed, social setting. Follow along with step-by-step painting instructions while enjoying wine and snacks. No experience needed - just fun! Take home your masterpiece.',
            'activity_type' => 'class',
            'location_text' => 'East Legon, Accra, Ghana',
            'gps_code' => 'GA-030-3019',
            'start_at' => date('Y-m-d H:i:s', strtotime('+2 weeks Friday 6:00 PM')),
            'end_at' => date('Y-m-d H:i:s', strtotime('+2 weeks Friday 9:00 PM')),
            'capacity' => 20,
            'price_min' => 80.00,
            'price_max' => 80.00,
            'is_free' => 0,
            'photos' => ['paint-wine-1.jpg', 'paint-wine-2.jpg'],
            'venue_id' => null
        ]
    ],
    
    // MORE POP-UPS VARIATION
    [
        'host' => [
            'name' => 'Wellness Pop-ups',
            'email' => 'wellness.popup@activities.gh',
            'password' => 'password123',
            'country' => 'Ghana',
            'city' => 'Accra',
            'contact' => '+233 24 000 2223'
        ],
        'activity' => [
            'title' => 'Wellness & Self-Care Pop-up',
            'description' => 'A day dedicated to wellness featuring yoga sessions, meditation, massage therapy, healthy food vendors, and wellness products. Recharge your mind and body in a peaceful environment.',
            'activity_type' => 'popup',
            'location_text' => 'Cantonments, Accra, Ghana',
            'gps_code' => 'GA-030-3020',
            'start_at' => date('Y-m-d H:i:s', strtotime('+3 weeks Sunday 10:00 AM')),
            'end_at' => date('Y-m-d H:i:s', strtotime('+3 weeks Sunday 4:00 PM')),
            'capacity' => 100,
            'price_min' => 50.00,
            'price_max' => 150.00,
            'is_free' => 0,
            'photos' => ['wellness-popup-1.jpg', 'wellness-popup-2.jpg'],
            'venue_id' => null
        ]
    ]
];

// Function to create host user
function create_host_user($host_data, $customer) {
    $existing = $customer->get_customer_by_email($host_data['email']);
    if ($existing) {
        return $existing['customer_id'];
    }
    
    $result = $customer->register_customer(
        $host_data['name'],
        $host_data['email'],
        $host_data['password'],
        $host_data['country'],
        $host_data['city'],
        $host_data['contact'],
        3
    );
    
    if ($result) {
        $user = $customer->get_customer_by_email($host_data['email']);
        $customer->verify_customer($user['customer_id']);
        return $user['customer_id'];
    }
    
    return false;
}

// Function to create activity using direct SQL
function create_activity($activity_data, $host_id, $connection) {
    $photos = [];
    foreach ($activity_data['photos'] as $photo) {
        $photos[] = '../uploads/activities/' . $photo;
    }
    $photos_json = json_encode($photos);
    
    $venue_id = 'NULL';
    
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
    
    $sql = "INSERT INTO activities (title, description, activity_type, recurrence_type, host_id, venue_id, location_text, gps_code, 
            start_at, end_at, capacity, price_min, price_max, is_free, photos_json, status) 
            VALUES ('$title', '$description', '$activity_type', 'none', $host_id, $venue_id, '$location_text', '$gps_code', 
            '$start_at', $end_at, $capacity, {$activity_data['price_min']}, {$activity_data['price_max']}, $is_free, 
            '$photos_json_escaped', 'approved')";
    
    if ($connection->query($sql)) {
        return $connection->insert_id;
    } else {
        echo "  SQL Error: " . $connection->error . "\n";
        return false;
    }
}

// Main execution
echo "Starting additional activity population...\n";
$created = 0;
$errors = 0;

foreach ($activities_data as $index => $data) {
    echo "\nProcessing activity " . ($index + 1) . ": " . $data['activity']['title'] . "\n";
    
    $host_id = create_host_user($data['host'], $customer);
    if (!$host_id) {
        echo "  ERROR: Failed to create host user\n";
        $errors++;
        continue;
    }
    echo "  ✓ Host user created/retrieved (ID: $host_id)\n";
    
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

