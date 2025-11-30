<?php
/**
 * Paystack Webhook Handler
 * This endpoint receives webhook notifications from Paystack
 */

require_once(__DIR__ . '/../settings/core.php');
require_once(__DIR__ . '/../settings/db_cred.php');
require_once(__DIR__ . '/../controllers/payment_controller.php');
require_once(__DIR__ . '/../controllers/booking_controller.php');

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit();
}

// Get the raw POST data
$input = @file_get_contents('php://input');
$event = json_decode($input, true);

if (!$event || !isset($event['event'])) {
    http_response_code(400);
    exit();
}

// Verify webhook signature (optional but recommended)
// You can implement signature verification using Paystack's webhook secret

// Handle different event types
switch ($event['event']) {
    case 'charge.success':
        // Payment was successful
        $data = $event['data'];
        $reference = $data['reference'];
        
        // Get payment by reference
        $payment = get_payment_by_paystack_ref_ctr($reference);
        
        if ($payment && $payment['status'] !== 'completed') {
            // Update payment status
            update_payment_paystack_ctr(
                $payment['payment_id'],
                $reference,
                $data['channel'] ?? null,
                'completed',
                $data
            );
            
            // Update booking status
            update_booking_status_ctr($payment['booking_id'], 'confirmed');
        }
        break;
        
    case 'charge.failed':
        // Payment failed
        $data = $event['data'];
        $reference = $data['reference'];
        
        $payment = get_payment_by_paystack_ref_ctr($reference);
        
        if ($payment && $payment['status'] !== 'failed') {
            update_payment_paystack_ctr(
                $payment['payment_id'],
                $reference,
                $data['channel'] ?? null,
                'failed',
                $data
            );
        }
        break;
        
    case 'transfer.success':
        // Transfer to venue owner successful (for escrow release)
        // Handle escrow release logic here if needed
        break;
        
    default:
        // Log unhandled events
        error_log('Unhandled Paystack webhook event: ' . $event['event']);
}

// Always return 200 to acknowledge receipt
http_response_code(200);
echo json_encode(['success' => true]);

?>

