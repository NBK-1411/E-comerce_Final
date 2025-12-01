<?php
/**
 * Verify Paystack Payment - Callback/Webhook Handler
 * This can be used as both a callback (redirect) and webhook endpoint
 */

require_once(__DIR__ . '/../settings/core.php');
require_once(__DIR__ . '/../settings/db_cred.php');
require_once(__DIR__ . '/../controllers/payment_controller.php');
require_once(__DIR__ . '/../controllers/booking_controller.php');

// Helper function to build redirect URL - use a more reliable method that works on live servers
function build_base_url() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
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

function build_redirect_url($path) {
    return build_base_url() . $path;
}

// Get reference from query string (callback) or POST (webhook)
$reference = isset($_GET['reference']) ? trim($_GET['reference']) : (isset($_POST['reference']) ? trim($_POST['reference']) : '');

if (empty($reference)) {
    if (isset($_GET['reference'])) {
        header('Location: ' . build_redirect_url('/public/booking.php?error=payment_verification_failed'));
        exit();
    }
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Reference is required']);
    exit();
}

// Verify transaction with Paystack
$ch = curl_init(PAYSTACK_VERIFY_URL . $reference);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . PAYSTACK_SECRET_KEY
]);

$response = curl_exec($ch);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    if (isset($_GET['reference'])) {
        header('Location: ' . build_redirect_url('/public/booking.php?error=payment_verification_failed'));
        exit();
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Verification error']);
    exit();
}

$result = json_decode($response, true);

if (!$result || !isset($result['status']) || !$result['status']) {
    if (isset($_GET['reference'])) {
        header('Location: ' . build_redirect_url('/public/booking.php?error=payment_failed'));
        exit();
    }
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Payment verification failed']);
    exit();
}

$transaction = $result['data'];

// Get payment by Paystack reference
$payment = get_payment_by_paystack_ref_ctr($reference);

if (!$payment) {
    if (isset($_GET['reference'])) {
        header('Location: ' . build_redirect_url('/public/booking.php?error=payment_not_found'));
        exit();
    }
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Payment not found']);
    exit();
}

// Update payment status based on transaction status
$status = 'pending';
if ($transaction['status'] === 'success') {
    $status = 'completed';
} elseif ($transaction['status'] === 'failed') {
    $status = 'failed';
}

// Update payment record
update_payment_paystack_ctr(
    $payment['payment_id'],
    $reference,
    $transaction['channel'] ?? null,
    $status,
    $transaction
);

// If payment successful, update booking status
if ($status === 'completed') {
    update_booking_status_ctr($payment['booking_id'], 'confirmed');
    
    // Generate QR reference for booking
    $qr_ref = 'QR_' . strtoupper(uniqid());
    // Note: You may need to add a function to update booking QR reference
    // update_booking_qr_ref_ctr($payment['booking_id'], $qr_ref);
}

// Handle callback (redirect) vs webhook (JSON response)
if (isset($_GET['reference'])) {
    // This is a callback redirect
    if ($status === 'completed') {
        header('Location: ' . build_redirect_url('/public/booking_confirmation.php?booking_id=' . $payment['booking_id']));
    } else {
        header('Location: ' . build_redirect_url('/public/booking.php?error=payment_' . $status));
    }
    exit();
} else {
    // This is a webhook call
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Payment verified',
        'status' => $status,
        'booking_id' => $payment['booking_id']
    ]);
}

?>

