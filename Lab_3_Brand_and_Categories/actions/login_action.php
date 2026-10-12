<?php
// Bootstrap core (session, helpers, DB)
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/CustomerController.php';
// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(APP_BASE . 'views/login.php');
}
// Sanitize inputs
$email = trim(strip_tags($_POST['email']    ?? ''));
$pass  =                  $_POST['password'] ?? '';
// Make sure both fields are filled
if ($email === '' || $pass === '') {
    $_SESSION['error'] = 'All fields are required.';
    redirect(APP_BASE . 'views/login.php');
}
// Validate email format before hitting the database
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = 'Invalid email address.';
    redirect(APP_BASE . 'views/login.php');
}
$controller = new CustomerController();
$result     = $controller->login($email, $pass);
if ($result['success']) {
    $customer = $result['customer'];
    // Populate the session with the logged-in customer's details
    $_SESSION['customer_id']    = $customer['customer_id'];
    $_SESSION['customer_name']  = $customer['customer_name'];
    $_SESSION['customer_email'] = $customer['customer_email'];
    $_SESSION['user_role']      = $customer['user_role'];
    // Send the customer to the home page after login
    redirect(APP_BASE . 'index.php');
} else {
    // Store the error and return to the login page
    $_SESSION['error'] = $result['error'];
    redirect(APP_BASE . 'views/login.php');
}
