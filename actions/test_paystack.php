<?php
/**
 * Test Paystack Connection - Debug Endpoint
 * This will test if Paystack API is accessible and keys are valid
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once(__DIR__ . '/../settings/core.php');
require_once(__DIR__ . '/../settings/db_cred.php');

header('Content-Type: application/json');

// Check if logged in
if (!is_logged_in()) {
    echo json_encode(['error' => 'Please login first']);
    exit();
}

$results = [
    'timestamp' => date('Y-m-d H:i:s'),
    'tests' => []
];

// Test 1: Check if API keys are defined
$results['tests']['api_keys'] = [
    'secret_key_defined' => defined('PAYSTACK_SECRET_KEY') && !empty(PAYSTACK_SECRET_KEY),
    'public_key_defined' => defined('PAYSTACK_PUBLIC_KEY') && !empty(PAYSTACK_PUBLIC_KEY),
    'secret_key_preview' => defined('PAYSTACK_SECRET_KEY') ? substr(PAYSTACK_SECRET_KEY, 0, 10) . '...' : 'NOT DEFINED',
    'public_key_preview' => defined('PAYSTACK_PUBLIC_KEY') ? substr(PAYSTACK_PUBLIC_KEY, 0, 10) . '...' : 'NOT DEFINED',
    'test_mode' => defined('PAYSTACK_TEST_MODE') ? PAYSTACK_TEST_MODE : 'NOT DEFINED'
];

// Test 2: Check API URLs
$results['tests']['api_urls'] = [
    'initialize_url' => defined('PAYSTACK_INITIALIZE_URL') ? PAYSTACK_INITIALIZE_URL : 'NOT DEFINED',
    'verify_url' => defined('PAYSTACK_VERIFY_URL') ? PAYSTACK_VERIFY_URL : 'NOT DEFINED'
];

// Test 3: Test Paystack API connection
if (defined('PAYSTACK_SECRET_KEY') && !empty(PAYSTACK_SECRET_KEY)) {
    $test_data = [
        'email' => $_SESSION['customer_email'] ?? 'test@example.com',
        'amount' => 100, // 1 GHS in kobo
        'reference' => 'TEST_' . time()
    ];
    
    $ch = curl_init(PAYSTACK_INITIALIZE_URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($test_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . PAYSTACK_SECRET_KEY,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $error = curl_error($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $results['tests']['api_connection'] = [
        'curl_error' => $error ?: 'None',
        'http_code' => $http_code,
        'response_received' => !empty($response),
        'response_preview' => $response ? substr($response, 0, 200) : 'No response'
    ];
    
    if ($response) {
        $decoded = json_decode($response, true);
        $results['tests']['api_connection']['response_status'] = $decoded['status'] ?? 'unknown';
        $results['tests']['api_connection']['response_message'] = $decoded['message'] ?? 'No message';
        $results['tests']['api_connection']['full_response'] = $decoded;
    }
} else {
    $results['tests']['api_connection'] = [
        'error' => 'Cannot test - API keys not defined'
    ];
}

// Test 4: Check callback URL generation
function build_base_url() {
    $protocol = 'http';
    if (isset($_SERVER['HTTPS'])) {
        if ($_SERVER['HTTPS'] === 'on' || $_SERVER['HTTPS'] === '1') {
            $protocol = 'https';
        }
    } elseif (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) {
        $protocol = 'https';
    } elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
        $protocol = 'https';
    }
    
    $host = $_SERVER['HTTP_HOST'];
    $script_path = str_replace('\\', '/', dirname(__FILE__));
    $doc_root = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
    $script_path = rtrim($script_path, '/');
    $doc_root = rtrim($doc_root, '/');
    $relative_path = str_replace($doc_root, '', $script_path);
    $relative_path = str_replace('/actions', '', $relative_path);
    $relative_path = rtrim($relative_path, '/');
    
    $base_url = $protocol . '://' . $host;
    if (!empty($relative_path)) {
        $base_url .= '/' . ltrim($relative_path, '/');
    }
    
    return $base_url;
}

$callback_url = build_base_url() . '/actions/payment_verify_paystack_action.php';
$callback_url = preg_replace('#([^:])//+#', '$1/', $callback_url);

$results['tests']['callback_url'] = [
    'generated_url' => $callback_url,
    'base_url' => build_base_url(),
    'server_info' => [
        'http_host' => $_SERVER['HTTP_HOST'],
        'script_path' => dirname(__FILE__),
        'document_root' => $_SERVER['DOCUMENT_ROOT'],
        'https' => $_SERVER['HTTPS'] ?? 'not set',
        'server_port' => $_SERVER['SERVER_PORT'] ?? 'not set'
    ]
];

// Test 5: Check if callback URL is accessible (basic check)
$results['tests']['callback_accessibility'] = [
    'note' => 'This checks if the URL format is valid, not if Paystack can reach it',
    'url_format_valid' => filter_var($callback_url, FILTER_VALIDATE_URL) !== false
];

echo json_encode($results, JSON_PRETTY_PRINT);

