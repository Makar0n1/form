<?php
// Configuration file for payment form system

// Dashboard authentication
define('DASHBOARD_USERNAME', 'admin');
define('DASHBOARD_PASSWORD', 'admin123');

// Data storage
define('DATA_DIR', __DIR__ . '/data');
define('ORDERS_FILE', DATA_DIR . '/orders.json');

// WooCommerce API settings for multiple stores
$WC_STORES = [
    'store1' => [
        'url' => 'https://store1.com',
        'consumer_key' => 'ck_store1_key',
        'consumer_secret' => 'cs_store1_secret',
    ],
    'store2' => [
        'url' => 'https://store2.com',
        'consumer_key' => 'ck_store2_key',
        'consumer_secret' => 'cs_store2_secret',
    ],
    'store3' => [
        'url' => 'https://store3.com',
        'consumer_key' => 'ck_store3_key',
        'consumer_secret' => 'cs_store3_secret',
    ],
    'store4' => [
        'url' => 'https://store4.com',
        'consumer_key' => 'ck_store4_key',
        'consumer_secret' => 'cs_store4_secret',
    ],
    'store5' => [
        'url' => 'https://store5.com',
        'consumer_key' => 'ck_store5_key',
        'consumer_secret' => 'cs_store5_secret',
    ],
    'store6' => [
        'url' => 'https://store6.com',
        'consumer_key' => 'ck_store6_key',
        'consumer_secret' => 'cs_store6_secret',
    ],
];

// Session settings
define('SESSION_NAME', 'payment_dashboard');
define('SESSION_TIMEOUT', 3600); // 1 hour

// Timezone
date_default_timezone_set('UTC');

// Initialize data directory if it doesn't exist
if (!file_exists(DATA_DIR)) {
    mkdir(DATA_DIR, 0755, true);
}

// Initialize orders file if it doesn't exist
if (!file_exists(ORDERS_FILE)) {
    file_put_contents(ORDERS_FILE, json_encode([], JSON_PRETTY_PRINT));
    chmod(ORDERS_FILE, 0600); // Only owner can read/write
}

// Helper function to read orders
function getOrders() {
    if (!file_exists(ORDERS_FILE)) {
        return [];
    }
    $json = file_get_contents(ORDERS_FILE);
    return json_decode($json, true) ?: [];
}

// Helper function to save orders
function saveOrders($orders) {
    return file_put_contents(ORDERS_FILE, json_encode($orders, JSON_PRETTY_PRINT));
}

// Helper function to add new order
function addOrder($orderData) {
    $orders = getOrders();
    $orders[] = $orderData;
    return saveOrders($orders);
}

// Helper function to check if user is logged in
function isLoggedIn() {
    session_name(SESSION_NAME);
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        return false;
    }

    // Check session timeout
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
        session_unset();
        session_destroy();
        return false;
    }

    $_SESSION['last_activity'] = time();
    return true;
}

// Helper function to require login
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /dashboard/login.php');
        exit;
    }
}

// Helper function to get store config
function getStoreConfig($storeId) {
    global $WC_STORES;
    return $WC_STORES[$storeId] ?? null;
}

// Helper function to update WooCommerce order status
function updateWooCommerceOrderStatus($orderId, $storeId, $status = 'processing') {
    $storeConfig = getStoreConfig($storeId);

    if (!$storeConfig) {
        error_log("Invalid store_id: $storeId");
        return [
            'success' => false,
            'http_code' => 0,
            'response' => ['error' => 'Invalid store configuration']
        ];
    }

    $url = $storeConfig['url'] . '/wp-json/wc/v3/orders/' . $orderId;

    $data = json_encode([
        'status' => $status
    ]);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($data)
    ]);
    curl_setopt($ch, CURLOPT_USERPWD, $storeConfig['consumer_key'] . ':' . $storeConfig['consumer_secret']);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [
        'success' => $httpCode >= 200 && $httpCode < 300,
        'http_code' => $httpCode,
        'response' => json_decode($result, true)
    ];
}
