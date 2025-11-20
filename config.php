<?php
// Configuration file for payment form system

// Dashboard authentication
define('DASHBOARD_USERNAME', 'admin');
define('DASHBOARD_PASSWORD', 'admin123');

// Data storage
define('DATA_DIR', __DIR__ . '/data');
define('ORDERS_FILE', DATA_DIR . '/orders.json');

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
