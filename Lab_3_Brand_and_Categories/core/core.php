<?php
// Start the session and set the timezone for the whole application
session_start();
date_default_timezone_set('Africa/Accra');

// Base URL for the application — auto-detected from the HTTP host.
// Live server (Ashesi): uses /~peter.mayen/ tilde path.
// Local XAMPP:          uses /e-commerce-labs/ direct path.
if (isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] !== 'localhost') {
    define('APP_BASE', '/~peter.mayen/e-commerce-labs/Lab_3_Brand_and_Categories/');
} else {
    define('APP_BASE', '/e-commerce-labs/Lab_3_Brand_and_Categories/');
}

// Load the database connection class
require_once __DIR__ . '/db_class.php';
// Return the visitor's IP address
function get_ip() {
    return $_SERVER['REMOTE_ADDR'];
}
// Send a redirect header and stop execution
function redirect($url) {
    header("Location: $url");
    exit();
}
// Returns true when a customer is logged in
function is_logged_in() {
    return isset($_SESSION['customer_id']);
}
// Returns true only when the logged-in user has the admin role (user_role = 1)
function is_admin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 1;
}
// Redirect unauthenticated users to the login page
function require_login() {
    if (!is_logged_in()) {
        redirect(APP_BASE . 'views/login.php');
    }
}
// Redirect non-admin users to the home page with an error message
function require_admin() {
    if (!is_admin()) {
        $_SESSION['error'] = 'Access denied. Admins only.';
        redirect(APP_BASE . 'index.php');
    }
}
