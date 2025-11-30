<?php
/**
 * Database Credentials - EXAMPLE FILE
 * Copy this file to db_cred.php and update with your actual credentials
 */

// Database configuration constants
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'EventWave');

// Database connection character set
define('DB_CHARSET', 'utf8mb4');

// Google Maps API Key for Geocoding
// Get your API key from: https://console.cloud.google.com/google/maps-apis
// Enable "Geocoding API" in your Google Cloud Console
define('GOOGLE_MAPS_API_KEY', 'YOUR_GOOGLE_MAPS_API_KEY_HERE');

// Paystack API Configuration
// Get your keys from: https://dashboard.paystack.com/#/settings/developer
// Use test keys for development, live keys for production
define('PAYSTACK_SECRET_KEY', 'YOUR_PAYSTACK_SECRET_KEY_HERE');
define('PAYSTACK_PUBLIC_KEY', 'YOUR_PAYSTACK_PUBLIC_KEY_HERE');
define('PAYSTACK_TEST_MODE', true); // Set to false for production

// Paystack API URLs
define('PAYSTACK_INITIALIZE_URL', 'https://api.paystack.co/transaction/initialize');
define('PAYSTACK_VERIFY_URL', 'https://api.paystack.co/transaction/verify/');

// OpenAI API Key for Chatbot
// Get your API key from: https://platform.openai.com/api-keys
define('OPENAI_API_KEY', 'YOUR_OPENAI_API_KEY_HERE');

?>

