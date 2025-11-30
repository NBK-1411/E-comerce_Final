<?php
require_once(__DIR__ . '/../settings/db_class.php');

class Seeder extends db_connection
{

    public function run()
    {
        echo "Starting database seed...\n";

        // 1. Create Hosts
        $hosts = [
            [
                'name' => 'Kofi Mensah',
                'email' => 'kofi.mensah@example.com',
                'password' => password_hash('password123', PASSWORD_DEFAULT),
                'contact' => '+233201234567',
                'role' => 2 // Venue Owner
            ],
            [
                'name' => 'Ama Osei',
                'email' => 'ama.osei@example.com',
                'password' => password_hash('password123', PASSWORD_DEFAULT),
                'contact' => '+233249876543',
                'role' => 2 // Venue Owner
            ]
        ];

        $host_ids = [];

        foreach ($hosts as $host) {
            // Check if exists
            $check = $this->read("SELECT customer_id FROM customer WHERE customer_email = ?", [$host['email']], 's');
            if ($check) {
                $host_ids[] = $check[0]['customer_id'];
                echo "Host {$host['name']} already exists (ID: {$check[0]['customer_id']})\n";
            } else {
                $sql = "INSERT INTO customer (customer_name, customer_email, customer_pass, customer_contact, user_role, verified) VALUES (?, ?, ?, ?, ?, 1)";
                $this->write($sql, [$host['name'], $host['email'], $host['password'], $host['contact'], $host['role']], 'ssssi');
                $new_id = $this->db->insert_id;
                $host_ids[] = $new_id;
                echo "Created host {$host['name']} (ID: $new_id)\n";
            }
        }

        // 2. Create Venues
        $venues = [
            [
                'title' => 'Santoku Restaurant',
                'description' => 'Experience world-class Japanese dining in the heart of Accra. Santoku offers a sophisticated atmosphere with meticulously crafted sushi, sashimi, and contemporary Japanese dishes. Perfect for business lunches, romantic dinners, and special occasions.',
                'cat_id' => 3, // Restaurant
                'gps_code' => '5.6037,-0.1870',
                'location_text' => 'Villaggio Vista, North Airport Road, Accra',
                'capacity' => 80,
                'price_min' => 0, // Reservation only
                'price_max' => 0,
                'booking_type' => 'reservation',
                'photos' => ['uploads/venues/santoku_1.jpg', 'uploads/venues/santoku_2.jpg'],
                'amenities' => ['Air Conditioning', 'WiFi', 'Parking', 'Bar'],
                'host_index' => 0
            ],
            [
                'title' => 'The Buka Restaurant',
                'description' => 'Authentic African cuisine in an open-air setting. Enjoy the best of Ghanaian and Nigerian dishes, from Jollof Rice to Grilled Tilapia. A vibrant atmosphere that captures the spirit of Accra.',
                'cat_id' => 3, // Restaurant
                'gps_code' => '5.5560,-0.1969',
                'location_text' => '10th Lane, Osu, Accra',
                'capacity' => 120,
                'price_min' => 0, // Reservation only
                'price_max' => 0,
                'booking_type' => 'reservation',
                'photos' => ['uploads/venues/buka_1.jpg', 'uploads/venues/buka_2.jpg'],
                'amenities' => ['Outdoor Seating', 'WiFi', 'Parking'],
                'host_index' => 0
            ],
            [
                'title' => 'The Savannah Outdoor Garden',
                'description' => 'A lush, manicured garden perfect for weddings, garden parties, and corporate events. Located in the quiet neighborhood of Cantonments, it offers a serene escape from the city noise.',
                'cat_id' => 1, // Event Space
                'gps_code' => '5.5833,-0.1667',
                'location_text' => 'Cantonments, Accra',
                'capacity' => 300,
                'price_min' => 5000.00,
                'price_max' => 15000.00,
                'booking_type' => 'rent',
                'photos' => ['uploads/venues/savannah_1.jpg', 'uploads/venues/savannah_2.jpg'],
                'amenities' => ['Garden', 'Parking', 'Restrooms', 'Security'],
                'host_index' => 1
            ],
            [
                'title' => 'Labadi Beach Hotel Event Center',
                'description' => 'Host your event at Ghana\'s premier 5-star hotel. Our beachfront event spaces and grand ballrooms provide the perfect setting for conferences, galas, and luxury weddings.',
                'cat_id' => 1, // Event Space
                'gps_code' => '5.5700,-0.1400',
                'location_text' => 'La Road, Accra',
                'capacity' => 500,
                'price_min' => 10000.00,
                'price_max' => 50000.00,
                'booking_type' => 'rent',
                'photos' => ['uploads/venues/labadi_1.jpg', 'uploads/venues/labadi_2.jpg'],
                'amenities' => ['Air Conditioning', 'WiFi', 'Parking', 'Catering', 'AV Equipment'],
                'host_index' => 1
            ],
            [
                'title' => 'Legon Botanical Gardens',
                'description' => 'Reconnect with nature at the Legon Botanical Gardens. Ideal for picnics, canopy walks, and outdoor team building activities. A beautiful green space for relaxation and adventure.',
                'cat_id' => 4, // Activity (assuming 4 is Activity/Tour based on previous context, or I should check categories)
                'gps_code' => '5.6667,-0.1833',
                'location_text' => 'Haatso-Atomic Rd, Accra',
                'capacity' => 1000,
                'price_min' => 20.00, // Ticket price
                'price_max' => 50.00,
                'booking_type' => 'ticket',
                'photos' => ['uploads/venues/legon_1.jpg', 'uploads/venues/legon_2.jpg'],
                'amenities' => ['Parking', 'Restrooms', 'Playground', 'Canopy Walk'],
                'host_index' => 0
            ]
        ];

        foreach ($venues as $venue) {
            // Check if exists
            $check = $this->read("SELECT venue_id FROM venue WHERE title = ?", [$venue['title']], 's');
            if ($check) {
                echo "Venue {$venue['title']} already exists (ID: {$check[0]['venue_id']})\n";

                // Update photos just in case
                $photos_json = json_encode($venue['photos']);
                $this->write("UPDATE venue SET photos_json = ? WHERE venue_id = ?", [$photos_json, $check[0]['venue_id']], 'si');

            } else {
                $host_id = $host_ids[$venue['host_index']];
                $photos_json = json_encode($venue['photos']);
                $amenities_json = json_encode($venue['amenities']);

                $sql = "INSERT INTO venue (title, description, cat_id, gps_code, location_text, capacity, price_min, price_max, booking_type, photos_json, amenities_json, created_by, status, allow_event_booking) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'approved', 1)";

                $this->write($sql, [
                    $venue['title'],
                    $venue['description'],
                    $venue['cat_id'],
                    $venue['gps_code'],
                    $venue['location_text'],
                    $venue['capacity'],
                    $venue['price_min'],
                    $venue['price_max'],
                    $venue['booking_type'],
                    $photos_json,
                    $amenities_json,
                    $host_id
                ], 'ssissiddsssi');

                echo "Created venue {$venue['title']}\n";
            }
        }

        echo "Seeding completed successfully!\n";
    }
}

$seeder = new Seeder();
$seeder->run();
?>