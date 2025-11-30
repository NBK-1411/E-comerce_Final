<?php
/**
 * Populate Database with Real Ghana Venues
 * 
 * This script creates host users and populates the database with real venues
 * and activities from Ghana.
 * 
 * Usage: Run from command line: php populate_ghana_venues.php
 * Or access via browser: http://localhost/Event-Management-Website/database/populate_ghana_venues.php
 */

require_once(__DIR__ . '/../settings/db_class.php');
require_once(__DIR__ . '/../classes/customer_class.php');
require_once(__DIR__ . '/../classes/venue_class.php');
require_once(__DIR__ . '/../controllers/venue_controller.php');

// Set execution time limit
set_time_limit(300); // 5 minutes

$db = new db_connection();

// Real Ghana Venues Data
$venues_data = [
    // RESTAURANTS
    [
        'host' => [
            'name' => 'Kwame Asante',
            'email' => 'kwame.asante@venues.gh',
            'password' => 'password123',
            'country' => 'Ghana',
            'city' => 'Accra',
            'contact' => '+233 24 456 7890'
        ],
        'venue' => [
            'title' => 'Santoku Restaurant',
            'description' => 'A premium Japanese restaurant in Accra offering authentic sushi, teppanyaki, and Japanese cuisine. Located in the heart of Osu, Santoku provides an elegant dining experience with fresh ingredients and expert chefs.',
            'cat_id' => 3, // Restaurants
            'location_text' => 'Osu, Accra, Ghana',
            'gps_code' => 'GA-030-1234',
            'capacity' => 80,
            'price_min' => 150.00,
            'price_max' => 500.00,
            'photos' => ['santoku-1.jpg', 'santoku-2.jpg', 'santoku-3.jpg'],
            'amenities' => ['WiFi', 'Air Conditioning', 'Parking', 'Bar', 'Outdoor Seating'],
            'rules' => ['Smart casual dress code', 'Reservations recommended', 'No smoking indoors'],
            'safety' => 'All staff are trained in food safety. Contactless payment available.',
            'parking' => 'Valet parking available. Street parking also available nearby.',
            'accessibility' => 'Wheelchair accessible. Ground floor dining available.'
        ]
    ],
    [
        'host' => [
            'name' => 'Ama Mensah',
            'email' => 'ama.mensah@venues.gh',
            'password' => 'password123',
            'country' => 'Ghana',
            'city' => 'Accra',
            'contact' => '+233 24 567 8901'
        ],
        'venue' => [
            'title' => 'Buka Restaurant',
            'description' => 'Experience authentic Ghanaian cuisine at Buka Restaurant. Known for traditional dishes like jollof rice, banku with tilapia, and fufu. A vibrant atmosphere perfect for experiencing local culture and flavors.',
            'cat_id' => 3, // Restaurants
            'location_text' => 'East Legon, Accra, Ghana',
            'gps_code' => 'GA-030-1235',
            'capacity' => 120,
            'price_min' => 80.00,
            'price_max' => 250.00,
            'photos' => ['buka-1.jpg', 'buka-2.jpg', 'buka-3.jpg'],
            'amenities' => ['WiFi', 'Parking', 'Live Music', 'Outdoor Seating'],
            'rules' => ['Casual dress code', 'Groups welcome', 'Traditional music on weekends'],
            'safety' => 'Food prepared fresh daily. All ingredients locally sourced.',
            'parking' => 'Ample parking space available on-site.',
            'accessibility' => 'Ground floor accessible. Family-friendly environment.'
        ]
    ],
    [
        'host' => [
            'name' => 'Kofi Adjei',
            'email' => 'kofi.adjei@venues.gh',
            'password' => 'password123',
            'country' => 'Ghana',
            'city' => 'Accra',
            'contact' => '+233 24 678 9012'
        ],
        'venue' => [
            'title' => 'Skybar 25',
            'description' => 'Rooftop bar and restaurant with stunning views of Accra. Perfect for sunset cocktails, fine dining, and special events. Features an infinity pool, modern decor, and international cuisine.',
            'cat_id' => 3, // Restaurants
            'location_text' => 'Airport City, Accra, Ghana',
            'gps_code' => 'GA-030-1236',
            'capacity' => 150,
            'price_min' => 200.00,
            'price_max' => 800.00,
            'photos' => ['skybar-1.jpg', 'skybar-2.jpg', 'skybar-3.jpg'],
            'amenities' => ['WiFi', 'Air Conditioning', 'Pool', 'Bar', 'Rooftop', 'Valet Parking'],
            'rules' => ['Smart casual required', 'Reservations essential for dinner', 'Age 18+ for bar area'],
            'safety' => '24/7 security. Fire safety compliant. First aid available.',
            'parking' => 'Valet parking service available.',
            'accessibility' => 'Elevator access to rooftop. Wheelchair accessible areas.'
        ]
    ],
    
    // CLUBS & LOUNGES
    [
        'host' => [
            'name' => 'Yaw Boateng',
            'email' => 'yaw.boateng@venues.gh',
            'password' => 'password123',
            'country' => 'Ghana',
            'city' => 'Accra',
            'contact' => '+233 24 789 0123'
        ],
        'venue' => [
            'title' => 'Republic Bar & Grill',
            'description' => 'One of Accra\'s most popular nightlife destinations. Features live DJ sets, international and local music, craft cocktails, and a vibrant atmosphere. Perfect for parties, events, and weekend nights out.',
            'cat_id' => 2, // Clubs & Lounges
            'location_text' => 'Osu, Accra, Ghana',
            'gps_code' => 'GA-030-1237',
            'capacity' => 300,
            'price_min' => 100.00,
            'price_max' => 500.00,
            'photos' => ['republic-1.jpg', 'republic-2.jpg', 'republic-3.jpg'],
            'amenities' => ['WiFi', 'Sound System', 'Dance Floor', 'VIP Section', 'Bar', 'Outdoor Area'],
            'rules' => ['Age 18+', 'Smart casual dress code', 'No outside drinks', 'ID required'],
            'safety' => 'Security personnel on-site. CCTV surveillance. First aid available.',
            'parking' => 'Limited parking. Street parking available nearby.',
            'accessibility' => 'Ground floor accessible. Some areas may have steps.'
        ]
    ],
    [
        'host' => [
            'name' => 'Efua Owusu',
            'email' => 'efua.owusu@venues.gh',
            'password' => 'password123',
            'country' => 'Ghana',
            'city' => 'Accra',
            'contact' => '+233 24 890 1234'
        ],
        'venue' => [
            'title' => 'Carbon Nightclub',
            'description' => 'Premium nightclub in Accra featuring state-of-the-art sound and lighting systems. Hosts top DJs, themed nights, and exclusive events. The ultimate destination for nightlife in Accra.',
            'cat_id' => 2, // Clubs & Lounges
            'location_text' => 'Airport Residential, Accra, Ghana',
            'gps_code' => 'GA-030-1238',
            'capacity' => 500,
            'price_min' => 150.00,
            'price_max' => 1000.00,
            'photos' => ['carbon-1.jpg', 'carbon-2.jpg', 'carbon-3.jpg'],
            'amenities' => ['Premium Sound System', 'LED Lighting', 'VIP Lounge', 'Bottle Service', 'Dance Floor', 'Bar'],
            'rules' => ['Age 21+', 'Strict dress code enforced', 'No cameras without permission', 'VIP reservations recommended'],
            'safety' => 'Professional security team. Bag checks at entrance. Medical staff on standby.',
            'parking' => 'Valet parking available. Additional parking nearby.',
            'accessibility' => 'Ground floor accessible. VIP areas may require stairs.'
        ]
    ],
    
    // EVENT SPACES
    [
        'host' => [
            'name' => 'Nana Yeboah',
            'email' => 'nana.yeboah@venues.gh',
            'password' => 'password123',
            'country' => 'Ghana',
            'city' => 'Accra',
            'contact' => '+233 24 901 2345'
        ],
        'venue' => [
            'title' => 'Labadi Beach Hotel Conference Centre',
            'description' => 'Elegant conference and event space overlooking the Atlantic Ocean. Perfect for corporate events, weddings, conferences, and social gatherings. Features modern AV equipment, catering services, and beachfront access.',
            'cat_id' => 1, // Event Spaces
            'location_text' => 'Labadi, Accra, Ghana',
            'gps_code' => 'GA-030-1239',
            'capacity' => 500,
            'price_min' => 500.00,
            'price_max' => 5000.00,
            'photos' => ['labadi-1.jpg', 'labadi-2.jpg', 'labadi-3.jpg'],
            'amenities' => ['WiFi', 'Projector & Screen', 'Sound System', 'Catering', 'Beach Access', 'Parking', 'Air Conditioning'],
            'rules' => ['Advance booking required', 'Catering packages available', 'Beach access included', 'No smoking indoors'],
            'safety' => 'Fire safety compliant. Security available. First aid on-site.',
            'parking' => 'Large parking lot available. Valet service optional.',
            'accessibility' => 'Fully wheelchair accessible. Elevator access. Accessible restrooms.'
        ]
    ],
    [
        'host' => [
            'name' => 'Akosua Darko',
            'email' => 'akosua.darko@venues.gh',
            'password' => 'password123',
            'country' => 'Ghana',
            'city' => 'Accra',
            'contact' => '+233 24 012 3456'
        ],
        'venue' => [
            'title' => 'Golden Tulip Accra Grand Ballroom',
            'description' => 'Luxurious grand ballroom perfect for weddings, corporate galas, and large celebrations. Features elegant chandeliers, high ceilings, and professional event planning services. Located in the heart of Accra.',
            'cat_id' => 1, // Event Spaces
            'location_text' => 'Airport City, Accra, Ghana',
            'gps_code' => 'GA-030-1240',
            'capacity' => 800,
            'price_min' => 1000.00,
            'price_max' => 10000.00,
            'photos' => ['golden-tulip-1.jpg', 'golden-tulip-2.jpg', 'golden-tulip-3.jpg'],
            'amenities' => ['WiFi', 'Stage', 'Sound System', 'Lighting', 'Catering', 'Bridal Suite', 'Parking', 'Air Conditioning'],
            'rules' => ['Minimum booking 4 hours', 'Catering must be arranged through venue', 'Decorations allowed with approval'],
            'safety' => 'Full fire safety systems. Security personnel. Medical assistance available.',
            'parking' => 'Large secure parking area. Valet service available.',
            'accessibility' => 'Fully accessible. Elevators. Wheelchair ramps. Accessible restrooms.'
        ]
    ],
    
    // STUDIOS
    [
        'host' => [
            'name' => 'Kojo Mensah',
            'email' => 'kojo.mensah@venues.gh',
            'password' => 'password123',
            'country' => 'Ghana',
            'city' => 'Accra',
            'contact' => '+233 24 123 4567'
        ],
        'venue' => [
            'title' => 'Creative Studio Accra',
            'description' => 'Modern photography and video production studio with professional lighting, green screen, and equipment. Perfect for photoshoots, video production, content creation, and creative workshops.',
            'cat_id' => 4, // Studios
            'location_text' => 'Cantonments, Accra, Ghana',
            'gps_code' => 'GA-030-1241',
            'capacity' => 30,
            'price_min' => 200.00,
            'price_max' => 1500.00,
            'photos' => ['studio-1.jpg', 'studio-2.jpg', 'studio-3.jpg'],
            'amenities' => ['Professional Lighting', 'Green Screen', 'WiFi', 'Changing Room', 'Equipment Rental', 'Air Conditioning'],
            'rules' => ['Booking required in advance', 'Equipment handling training provided', 'No food in studio area'],
            'safety' => 'Fire safety compliant. Equipment regularly maintained. First aid available.',
            'parking' => 'Limited parking available. Street parking nearby.',
            'accessibility' => 'Ground floor accessible. Wide doorways for equipment.'
        ]
    ],
    
    // SHORT STAYS
    [
        'host' => [
            'name' => 'Abena Kumi',
            'email' => 'abena.kumi@venues.gh',
            'password' => 'password123',
            'country' => 'Ghana',
            'city' => 'Accra',
            'contact' => '+233 24 234 5678'
        ],
        'venue' => [
            'title' => 'Kokrobite Beach Resort',
            'description' => 'Beachfront resort perfect for retreats, team building, and short stays. Features beach access, pool, restaurant, and comfortable accommodations. Ideal for groups looking for a relaxing getaway near Accra.',
            'cat_id' => 5, // Short Stays
            'location_text' => 'Kokrobite, Greater Accra, Ghana',
            'gps_code' => 'GA-030-1242',
            'capacity' => 100,
            'price_min' => 300.00,
            'price_max' => 2000.00,
            'photos' => ['kokrobite-1.jpg', 'kokrobite-2.jpg', 'kokrobite-3.jpg'],
            'amenities' => ['Beach Access', 'Pool', 'Restaurant', 'WiFi', 'Parking', 'Air Conditioning', 'Outdoor Space'],
            'rules' => ['Check-in 2 PM, Check-out 11 AM', 'Beach activities available', 'Group bookings welcome'],
            'safety' => '24/7 security. Lifeguards at beach. First aid available.',
            'parking' => 'On-site parking available for all guests.',
            'accessibility' => 'Some accessible rooms available. Ground floor options.'
        ]
    ],
    
    // MORE RESTAURANTS
    [
        'host' => [
            'name' => 'Maame Adjei',
            'email' => 'maame.adjei@venues.gh',
            'password' => 'password123',
            'country' => 'Ghana',
            'city' => 'Accra',
            'contact' => '+233 24 345 6789'
        ],
        'venue' => [
            'title' => 'Chez Clarisse',
            'description' => 'French-inspired restaurant offering European and African fusion cuisine. Elegant atmosphere with outdoor garden seating. Perfect for romantic dinners, business lunches, and special occasions.',
            'cat_id' => 3, // Restaurants
            'location_text' => 'East Legon, Accra, Ghana',
            'gps_code' => 'GA-030-1243',
            'capacity' => 60,
            'price_min' => 120.00,
            'price_max' => 400.00,
            'photos' => ['chez-clarisse-1.jpg', 'chez-clarisse-2.jpg', 'chez-clarisse-3.jpg'],
            'amenities' => ['WiFi', 'Outdoor Seating', 'Parking', 'Bar', 'Air Conditioning'],
            'rules' => ['Reservations recommended', 'Smart casual dress code', 'No smoking indoors'],
            'safety' => 'Food safety certified. Contactless payment available.',
            'parking' => 'On-site parking available.',
            'accessibility' => 'Ground floor accessible. Garden area accessible.'
        ]
    ],
    [
        'host' => [
            'name' => 'Kwabena Osei',
            'email' => 'kwabena.osei@venues.gh',
            'password' => 'password123',
            'country' => 'Ghana',
            'city' => 'Accra',
            'contact' => '+233 24 456 7890'
        ],
        'venue' => [
            'title' => 'Asanka Local',
            'description' => 'Popular local restaurant serving authentic Ghanaian street food and traditional dishes. Vibrant atmosphere with live music on weekends. Experience the real taste of Ghana.',
            'cat_id' => 3, // Restaurants
            'location_text' => 'Osu, Accra, Ghana',
            'gps_code' => 'GA-030-1244',
            'capacity' => 90,
            'price_min' => 50.00,
            'price_max' => 150.00,
            'photos' => ['asanka-1.jpg', 'asanka-2.jpg', 'asanka-3.jpg'],
            'amenities' => ['WiFi', 'Outdoor Seating', 'Live Music', 'Parking'],
            'rules' => ['Casual dress code', 'Cash and mobile money accepted', 'Groups welcome'],
            'safety' => 'Food prepared fresh. Clean and hygienic environment.',
            'parking' => 'Street parking available nearby.',
            'accessibility' => 'Ground floor accessible. Outdoor seating available.'
        ]
    ],
    
    // MORE CLUBS
    [
        'host' => [
            'name' => 'Esi Appiah',
            'email' => 'esi.appiah@venues.gh',
            'password' => 'password123',
            'country' => 'Ghana',
            'city' => 'Accra',
            'contact' => '+233 24 567 8901'
        ],
        'venue' => [
            'title' => 'Plot 7 Lounge',
            'description' => 'Upscale lounge and bar in Accra featuring craft cocktails, premium spirits, and a sophisticated atmosphere. Perfect for networking events, private parties, and evening drinks.',
            'cat_id' => 2, // Clubs & Lounges
            'location_text' => 'Airport Residential, Accra, Ghana',
            'gps_code' => 'GA-030-1245',
            'capacity' => 200,
            'price_min' => 150.00,
            'price_max' => 800.00,
            'photos' => ['plot7-1.jpg', 'plot7-2.jpg', 'plot7-3.jpg'],
            'amenities' => ['Premium Bar', 'WiFi', 'VIP Section', 'Outdoor Terrace', 'Sound System', 'Parking'],
            'rules' => ['Age 21+', 'Smart casual dress code', 'Reservations recommended for groups'],
            'safety' => 'Security on-site. CCTV surveillance.',
            'parking' => 'Valet parking available.',
            'accessibility' => 'Ground floor accessible. Elevator to terrace.'
        ]
    ],
    
    // MORE EVENT SPACES
    [
        'host' => [
            'name' => 'Kofi Ampofo',
            'email' => 'kofi.ampofo@venues.gh',
            'password' => 'password123',
            'country' => 'Ghana',
            'city' => 'Accra',
            'contact' => '+233 24 678 9012'
        ],
        'venue' => [
            'title' => 'Accra International Conference Centre',
            'description' => 'State-of-the-art conference facility with multiple meeting rooms, auditorium, and exhibition space. Equipped with modern technology, simultaneous interpretation, and professional event services.',
            'cat_id' => 1, // Event Spaces
            'location_text' => 'Ridge, Accra, Ghana',
            'gps_code' => 'GA-030-1246',
            'capacity' => 1000,
            'price_min' => 2000.00,
            'price_max' => 20000.00,
            'photos' => ['aicc-1.jpg', 'aicc-2.jpg', 'aicc-3.jpg'],
            'amenities' => ['WiFi', 'AV Equipment', 'Simultaneous Interpretation', 'Catering', 'Exhibition Space', 'Parking', 'Air Conditioning'],
            'rules' => ['Advance booking required', 'Catering packages available', 'Technical support included'],
            'safety' => 'Full security. Fire safety systems. Medical assistance available.',
            'parking' => 'Large secure parking area.',
            'accessibility' => 'Fully accessible. Elevators. Wheelchair ramps throughout.'
        ]
    ],
    
    // MORE RESTAURANTS
    [
        'host' => [
            'name' => 'Ama Serwaa',
            'email' => 'ama.serwaa@venues.gh',
            'password' => 'password123',
            'country' => 'Ghana',
            'city' => 'Accra',
            'contact' => '+233 24 789 0123'
        ],
        'venue' => [
            'title' => 'Coco Lounge',
            'description' => 'Trendy restaurant and bar with a relaxed atmosphere. Features international cuisine, creative cocktails, and a beautiful outdoor garden. Popular for brunch, lunch, and evening dining.',
            'cat_id' => 3, // Restaurants
            'location_text' => 'East Legon, Accra, Ghana',
            'gps_code' => 'GA-030-1247',
            'capacity' => 100,
            'price_min' => 100.00,
            'price_max' => 350.00,
            'photos' => ['coco-1.jpg', 'coco-2.jpg', 'coco-3.jpg'],
            'amenities' => ['WiFi', 'Outdoor Garden', 'Bar', 'Parking', 'Air Conditioning'],
            'rules' => ['Reservations recommended for dinner', 'Casual dress code', 'Pet-friendly outdoor area'],
            'safety' => 'Food safety certified. Contactless payment available.',
            'parking' => 'On-site parking available.',
            'accessibility' => 'Ground floor accessible. Garden area accessible.'
        ]
    ],
    [
        'host' => [
            'name' => 'Yaw Mensah',
            'email' => 'yaw.mensah@venues.gh',
            'password' => 'password123',
            'country' => 'Ghana',
            'city' => 'Accra',
            'contact' => '+233 24 890 1234'
        ],
        'venue' => [
            'title' => 'Bella Roma',
            'description' => 'Authentic Italian restaurant serving traditional pasta, pizza, and Italian dishes. Cozy atmosphere with Italian decor. Perfect for family dinners and romantic evenings.',
            'cat_id' => 3, // Restaurants
            'location_text' => 'Cantonments, Accra, Ghana',
            'gps_code' => 'GA-030-1248',
            'capacity' => 70,
            'price_min' => 90.00,
            'price_max' => 300.00,
            'photos' => ['bella-roma-1.jpg', 'bella-roma-2.jpg', 'bella-roma-3.jpg'],
            'amenities' => ['WiFi', 'Parking', 'Air Conditioning', 'Bar'],
            'rules' => ['Reservations recommended', 'Casual to smart casual', 'Family-friendly'],
            'safety' => 'Food prepared fresh. Hygienic kitchen practices.',
            'parking' => 'Street parking available nearby.',
            'accessibility' => 'Ground floor accessible.'
        ]
    ],
    
    // MORE SHORT STAYS
    [
        'host' => [
            'name' => 'Kofi Bonsu',
            'email' => 'kofi.bonsu@venues.gh',
            'password' => 'password123',
            'country' => 'Ghana',
            'city' => 'Accra',
            'contact' => '+233 24 901 2345'
        ],
        'venue' => [
            'title' => 'Aqua Safari Resort',
            'description' => 'Luxury beachfront resort perfect for retreats, corporate events, and short stays. Features multiple pools, spa, restaurants, and direct beach access. Ideal for team building and relaxation.',
            'cat_id' => 5, // Short Stays
            'location_text' => 'Ada, Greater Accra, Ghana',
            'gps_code' => 'GA-030-1249',
            'capacity' => 200,
            'price_min' => 500.00,
            'price_max' => 3000.00,
            'photos' => ['aqua-safari-1.jpg', 'aqua-safari-2.jpg', 'aqua-safari-3.jpg'],
            'amenities' => ['Beach Access', 'Multiple Pools', 'Spa', 'Restaurants', 'WiFi', 'Parking', 'Air Conditioning', 'Gym'],
            'rules' => ['Check-in 3 PM, Check-out 11 AM', 'Resort activities available', 'Group bookings welcome'],
            'safety' => '24/7 security. Lifeguards. Medical assistance available.',
            'parking' => 'Large secure parking area.',
            'accessibility' => 'Accessible rooms available. Ground floor options. Wheelchair accessible facilities.'
        ]
    ]
];

// Function to create host user
function create_host_user($host_data, $db) {
    $customer = new Customer();
    
    // Check if user already exists
    $existing = $customer->get_customer_by_email($host_data['email']);
    if ($existing) {
        return $existing['customer_id'];
    }
    
    // Create new host user (role 3 = venue owner)
    $result = $customer->register_customer(
        $host_data['name'],
        $host_data['email'],
        $host_data['password'],
        $host_data['country'],
        $host_data['city'],
        $host_data['contact'],
        3 // role_id = 3 for venue owner
    );
    
    if ($result) {
        $user = $customer->get_customer_by_email($host_data['email']);
        // Auto-verify host users for seeding
        $customer->verify_customer($user['customer_id']);
        return $user['customer_id'];
    }
    
    return false;
}

// Function to download and save image
function download_image($url, $filename, $upload_dir) {
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $filepath = $upload_dir . $filename;
    
    // If file already exists, skip
    if (file_exists($filepath)) {
        return '../uploads/venues/' . $filename;
    }
    
    // Try to download (for now, we'll use placeholder approach)
    // In production, you'd download real images
    // For now, create a placeholder or use existing images
    
    // Return placeholder path - images will need to be added manually
    return '../uploads/venues/' . $filename;
}

// Function to create venue using direct SQL
function create_venue($venue_data, $host_id, $db) {
    $connection = $db->db_conn();
    
    // Prepare photos JSON
    $photos = [];
    foreach ($venue_data['photos'] as $photo) {
        $photos[] = '../uploads/venues/' . $photo;
    }
    $photos_json = json_encode($photos);
    
    // Prepare rules JSON
    $rules_json = json_encode($venue_data['rules'] ?? []);
    
    // Escape strings for SQL
    $title = $connection->real_escape_string($venue_data['title']);
    $description = $connection->real_escape_string($venue_data['description']);
    $gps_code = $connection->real_escape_string($venue_data['gps_code']);
    $location_text = $connection->real_escape_string($venue_data['location_text']);
    $rules_json_escaped = $connection->real_escape_string($rules_json);
    $safety_notes = $connection->real_escape_string($venue_data['safety'] ?? '');
    $parking_transport = $connection->real_escape_string($venue_data['parking'] ?? '');
    $accessibility_info = $connection->real_escape_string($venue_data['accessibility'] ?? '');
    $photos_json_escaped = $connection->real_escape_string($photos_json);
    $contact_phone = $connection->real_escape_string('+233 24 123 4567');
    
    // Insert directly using SQL
    $sql = "INSERT INTO venue (title, description, cat_id, gps_code, location_text, capacity, 
            price_min, price_max, deposit_percentage, rules_json, safety_notes, parking_transport, 
            accessibility_info, photos_json, cancellation_policy, created_by, contact_phone, contact_email, booking_type, status) 
            VALUES ('$title', '$description', {$venue_data['cat_id']}, '$gps_code', '$location_text', 
            {$venue_data['capacity']}, {$venue_data['price_min']}, {$venue_data['price_max']}, 30, 
            '$rules_json_escaped', '$safety_notes', '$parking_transport', '$accessibility_info', 
            '$photos_json_escaped', 'flexible', $host_id, '$contact_phone', '', 'rent', 'approved')";
    
    if ($connection->query($sql)) {
        $venue_id = $connection->insert_id;
        return $venue_id;
    } else {
        echo "  SQL Error: " . $connection->error . "\n";
        return false;
    }
}

// Main execution
echo "Starting venue population...\n";
$created = 0;
$errors = 0;

foreach ($venues_data as $index => $data) {
    echo "\nProcessing venue " . ($index + 1) . ": " . $data['venue']['title'] . "\n";
    
    // Create host user
    $host_id = create_host_user($data['host'], $db);
    if (!$host_id) {
        echo "  ERROR: Failed to create host user\n";
        $errors++;
        continue;
    }
    echo "  ✓ Host user created/retrieved (ID: $host_id)\n";
    
    // Create venue
    $venue_id = create_venue($data['venue'], $host_id, $db);
    if (!$venue_id) {
        echo "  ERROR: Failed to create venue\n";
        $errors++;
        continue;
    }
    echo "  ✓ Venue created (ID: $venue_id)\n";
    
    $created++;
}

echo "\n\n=== SUMMARY ===\n";
echo "Venues created: $created\n";
echo "Errors: $errors\n";
echo "\nNote: Images need to be added manually to uploads/venues/ directory.\n";
echo "The script has created placeholder image references.\n";

