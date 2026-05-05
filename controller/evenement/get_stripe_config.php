<?php
// Get Stripe configuration (publishable key only)
header('Content-Type: application/json');

// Load environment variables from .env
$envFile = __DIR__ . '/../../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

$publishableKey = $_ENV['STRIPE_PUBLISHABLE_KEY'] ?? '';

if (!$publishableKey) {
    http_response_code(500);
    echo json_encode(['error' => 'Stripe configuration missing']);
    exit;
}

echo json_encode(['publishableKey' => $publishableKey]);
?>
