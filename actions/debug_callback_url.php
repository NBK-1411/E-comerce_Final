<?php
/**
 * Debug endpoint to check callback URL generation
 * Access this file directly to see what callback URL is being generated
 */

require_once(__DIR__ . '/../settings/core.php');
require_once(__DIR__ . '/../settings/db_cred.php');

// Build callback URL - same function as in payment_init_paystack_action.php
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

$callback_url = build_base_url() . '/actions/payment_verify_paystack_action.php';
$callback_url = preg_replace('#([^:])//+#', '$1/', $callback_url);

// Output debug information
header('Content-Type: application/json');
echo json_encode([
    'callback_url' => $callback_url,
    'debug_info' => [
        'protocol' => isset($_SERVER['HTTPS']) ? ($_SERVER['HTTPS'] === 'on' ? 'https' : 'http') : 'http',
        'http_host' => $_SERVER['HTTP_HOST'],
        'server_port' => $_SERVER['SERVER_PORT'] ?? 'not set',
        'script_path' => str_replace('\\', '/', dirname(__FILE__)),
        'document_root' => $_SERVER['DOCUMENT_ROOT'],
        'script_name' => $_SERVER['SCRIPT_NAME'],
        'request_uri' => $_SERVER['REQUEST_URI'] ?? 'not set',
        'http_x_forwarded_proto' => $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? 'not set',
        'base_url' => build_base_url()
    ],
    'test_callback' => [
        'url' => $callback_url,
        'accessible' => 'Check if this URL is publicly accessible',
        'note' => 'Paystack requires this URL to be publicly accessible via HTTP/HTTPS'
    ]
], JSON_PRETTY_PRINT);

