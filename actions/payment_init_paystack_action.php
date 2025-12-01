<?php
/**
 * Initialize Paystack Payment - JSON Endpoint
 */

header('Content-Type: application/json');

require_once(__DIR__ . '/../settings/core.php');
require_once(__DIR__ . '/../settings/db_cred.php');
require_once(__DIR__ . '/../controllers/booking_controller.php');
require_once(__DIR__ . '/../controllers/payment_controller.php');

// Check if logged in
if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Please login to continue']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$user_id = $_SESSION['customer_id'];
// Get user email - check multiple possible session keys
$user_email = $_SESSION['customer_email'] ?? $_SESSION['email'] ?? '';
$user_name = $_SESSION['customer_name'] ?? $_SESSION['name'] ?? '';

// If email not in session, get from database
if (empty($user_email)) {
    require_once(__DIR__ . '/../controllers/customer_controller.php');
    $user_data = get_customer_by_id_ctr($user_id);
    if ($user_data) {
        $user_email = $user_data['email'] ?? '';
        $user_name = $user_data['name'] ?? $user_name;
    }
}

if (empty($user_email)) {
    echo json_encode(['success' => false, 'message' => 'User email is required for payment']);
    exit();
}

// Get form data
$booking_id = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0;
$amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
$callback_url = isset($_POST['callback_url']) ? trim($_POST['callback_url']) : '';

// Validation
if (empty($booking_id)) {
    echo json_encode(['success' => false, 'message' => 'Booking ID is required']);
    exit();
}

if ($amount <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid amount']);
    exit();
}

// Get booking details
$booking = get_booking_by_id_ctr($booking_id);
if (!$booking || $booking['user_id'] != $user_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid booking']);
    exit();
}

// Create payment record first
$payment_data = [
    'booking_id' => $booking_id,
    'user_id' => $user_id,
    'amount' => $amount,
    'currency' => 'GHS',
    'payment_method' => 'paystack',
    'payment_type' => 'deposit',
    'status' => 'pending'
];

$payment_id = create_payment_ctr($payment_data);

if (!$payment_id) {
    echo json_encode(['success' => false, 'message' => 'Failed to create payment record']);
    exit();
}

// Build callback URL - use a more reliable method that works on live servers
function build_base_url() {
    // Detect protocol - check multiple ways for better compatibility
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
    
    // Get the script directory relative to document root
    $script_path = str_replace('\\', '/', dirname(__FILE__));
    $doc_root = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
    
    // Normalize paths (remove trailing slashes for comparison)
    $script_path = rtrim($script_path, '/');
    $doc_root = rtrim($doc_root, '/');
    
    // Calculate the relative path from document root
    $relative_path = str_replace($doc_root, '', $script_path);
    
    // Remove /actions since we're in actions folder
    $relative_path = str_replace('/actions', '', $relative_path);
    $relative_path = rtrim($relative_path, '/');
    
    // Build base URL
    $base_url = $protocol . '://' . $host;
    if (!empty($relative_path)) {
        $base_url .= '/' . ltrim($relative_path, '/');
    }
    
    return $base_url;
}

$callback_url_final = !empty($callback_url) ? $callback_url : build_base_url() . '/actions/payment_verify_paystack_action.php';

// Ensure callback URL is properly formatted (no double slashes, proper encoding)
$callback_url_final = preg_replace('#([^:])//+#', '$1/', $callback_url_final);

// Initialize Paystack transaction
$paystack_data = [
    'email' => $user_email,
    'amount' => round($amount * 100), // Convert to kobo (smallest currency unit)
    'reference' => 'EVT_' . $payment_id . '_' . time(),
    'callback_url' => $callback_url_final,
    'metadata' => [
        'payment_id' => $payment_id,
        'booking_id' => $booking_id,
        'user_id' => $user_id,
        'user_name' => $user_name
    ]
];

// Make request to Paystack
$ch = curl_init(PAYSTACK_INITIALIZE_URL);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($paystack_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . PAYSTACK_SECRET_KEY,
    'Content-Type: application/json'
]);

$response = curl_exec($ch);
$error = curl_error($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($error) {
    echo json_encode([
        'success' => false, 
        'message' => 'Payment gateway connection error: ' . $error,
        'debug' => [
            'callback_url' => $callback_url_final,
            'http_code' => $http_code
        ]
    ]);
    exit();
}

$result = json_decode($response, true);

if (!$result || !isset($result['status']) || !$result['status']) {
    $message = isset($result['message']) ? $result['message'] : 'Failed to initialize payment';
    $debug_info = [
        'callback_url' => $callback_url_final,
        'http_code' => $http_code,
        'paystack_response' => $result
    ];
    
    // Log the error for debugging (remove in production if needed)
    error_log('Paystack Init Error: ' . json_encode($debug_info));
    
    echo json_encode([
        'success' => false, 
        'message' => $message,
        'debug' => $debug_info
    ]);
    exit();
}

// Update payment with Paystack reference
$paystack_reference = $result['data']['reference'];
update_payment_paystack_ctr($payment_id, $paystack_reference, null, 'pending', $result['data']);

// Return authorization URL for redirect
echo json_encode([
    'success' => true,
    'authorization_url' => $result['data']['authorization_url'],
    'access_code' => $result['data']['access_code'],
    'reference' => $paystack_reference,
    'payment_id' => $payment_id
]);

?>

