
<?php
// Start the session and set the timezone
session_start();
date_default_timezone_set('Africa/Accra');

// Base URL for local XAMPP
define('APP_BASE', '/e-commerce-labs/Lab_5_Cart_Management/');
// Load the database connection class
require_once __DIR__ . '/db_class.php';

// Return the visitor's IP address
function get_ip() {
    return $_SERVER['REMOTE_ADDR'] ?? '';
}
// Send a redirect header and stop execution
function redirect($url) {
    header("Location: $url");
    exit();
}

// Redirect to a same-host referrer under APP_BASE, otherwise home
function redirect_back($fallback = null) {
    $fallback = $fallback ?: (APP_BASE . 'index.php');
    $referer  = $_SERVER['HTTP_REFERER'] ?? '';
    if ($referer === '') {
        redirect($fallback);
    }

    $parts = parse_url($referer);
    if ($parts === false || empty($parts['host'])) {
        redirect($fallback);
    }

    $currentHost = $_SERVER['HTTP_HOST'] ?? '';
    if (strcasecmp($parts['host'], $currentHost) !== 0) {
        redirect($fallback);
    }

    $path     = $parts['path'] ?? '/';
    $basePath = parse_url(APP_BASE, PHP_URL_PATH);
    if (!is_string($basePath) || $basePath === '') {
        $basePath = '/';
    }
    if (strpos($path, $basePath) !== 0) {
        redirect($fallback);
    }

    $query = [];
    if (!empty($parts['query'])) {
        parse_str($parts['query'], $query);
        unset($query['add_cart']);
    }

    $scheme = $parts['scheme'] ?? ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http');
    $safe   = $scheme . '://' . $parts['host'];
    if (isset($parts['port'])) {
        $safe .= ':' . $parts['port'];
    }
    $safe .= $path;
    if ($query) {
        $safe .= '?' . http_build_query($query);
    }
    redirect($safe);
}

// Session CSRF token for cart modification forms
function csrf_token() {
    if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_validate() {
    $token = $_POST['csrf_token'] ?? '';
    return is_string($token)
        && isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

// Check whether a customer is logged in
function is_logged_in() {
    return isset($_SESSION['customer_id']);
}
// Check whether the logged-in user is an admin
function is_admin() {
    return isset($_SESSION['user_role'])
        && (int) $_SESSION['user_role'] === 1;
}
// Redirect unauthenticated users to login
function require_login() {
    if (!is_logged_in()) {
        redirect(APP_BASE . 'views/login.php');
    }
}

// Redirect non-admin users to the home page
function require_admin() {
    if (!is_admin()) {
        $_SESSION['error'] = 'Access denied. Admins only.';
        redirect(APP_BASE . 'index.php');
    }
}
