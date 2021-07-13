<?php

$location = $_SERVER['DOCUMENT_ROOT'];

include ($location . '/wp-load.php');

require_once('stripe-php/init.php'); // Download stripe-php lib

$secret_key = get_field('stripe_live_secret_key', 'options'); // ACF Fields
if (empty($secret_key)){
    $secret_key = get_field('sandbox_secret_key', 'options');
}

\Stripe\Stripe::setApiKey($secret_key);


function calculateOrderAmount($items): int {
    return $items;
}

header('Content-Type: application/json');

try {
    $json_str = file_get_contents('php://input');
    $json_obj = json_decode($json_str);
    $paymentIntent = \Stripe\PaymentIntent::create([
        'amount' => calculateOrderAmount($json_obj->items),
        'currency' => 'usd',
    ]);

    $output = [
        'clientSecret' => $paymentIntent->client_secret,
    ];

    echo json_encode($output);
} catch (Error $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}