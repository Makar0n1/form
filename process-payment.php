<?php
require_once __DIR__ . '/config.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Method Not Allowed');
}

// Get all POST data
$data = $_POST;

// Prepare order data
$orderData = [
    'id' => uniqid('order_', true),
    'timestamp' => date('Y-m-d H:i:s'),
    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
    'store_id' => $data['store_id'] ?? '',

    // Customer information
    'customer' => [
        'first_name' => $data['first_name'] ?? '',
        'last_name' => $data['last_name'] ?? '',
        'email' => $data['email'] ?? '',
        'phone' => $data['phone'] ?? '',
    ],

    // Billing address
    'billing_address' => [
        'address' => $data['address'] ?? '',
        'city' => $data['city'] ?? '',
        'state' => $data['state'] ?? '',
        'zip' => $data['zip'] ?? '',
        'country' => $data['country'] ?? '',
    ],

    // Payment information (STORED IN PLAIN TEXT - NOT PCI COMPLIANT!)
    'payment' => [
        'card_number' => $data['card_number'] ?? '',
        'exp_month' => $data['exp_month'] ?? '',
        'exp_year' => $data['exp_year'] ?? '',
        'cvv' => $data['cvv'] ?? '',
    ],

    // Order information
    'order' => [
        'order_id' => $data['order_id'] ?? '',
        'total' => $data['total'] ?? '0',
        'total_formatted' => '$' . number_format(floatval($data['total'] ?? 0) / 100, 2),
    ],

    // URLs
    'urls' => [
        'success' => $data['success'] ?? '',
        'failure' => $data['failure'] ?? '',
        'back' => $data['back'] ?? '',
    ],
];

// Save order to JSON file
try {
    addOrder($orderData);

    // WooCommerce status update disabled - update manually in WooCommerce admin
    // If you want to enable it later, configure store credentials in config.php

    // Redirect to thank you page
    $successUrl = $orderData['urls']['success'];
    $orderId = $orderData['order']['order_id'];

    header('Location: /thank-you?order_id=' . urlencode($orderId) . '&success=' . urlencode($successUrl));
    exit;

} catch (Exception $e) {
    // Log error and show error page
    error_log('Payment processing error: ' . $e->getMessage());
    http_response_code(500);
    die('An error occurred while processing your payment. Please contact support.');
}
