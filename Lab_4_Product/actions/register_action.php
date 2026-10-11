<?php
// Bootstrap core (session, helpers, DB)
require_once '../core/core.php';
require_once '../controllers/CustomerController.php';
// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../views/register.php');
}
// Sanitize all text inputs
$name    = trim(strip_tags($_POST['name']             ?? ''));
$email   = trim(strip_tags($_POST['email']            ?? ''));
$pass    =                  $_POST['password']         ?? '';
$confirm_pass =             $_POST['confirm_password'] ?? '';
$country = trim(strip_tags($_POST['country']          ?? ''));
$city    = trim(strip_tags($_POST['city']             ?? ''));
$contact = trim(strip_tags($_POST['contact']          ?? ''));
// Make sure no required field is empty
if ($name === '' || $email === '' || $pass === '' ||
    $country === '' || $city === '' || $contact === '') {
    $_SESSION['error'] = 'All fields are required.';
    redirect('../views/register.php');
}
// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = 'Invalid email address.';
    redirect('../views/register.php');
}
// Enforce the database column length for email (varchar(50) in schema)
if (strlen($email) > 50) {
    $_SESSION['error'] = 'Email address is too long.';
    redirect('../views/register.php');
}
// Enforce password strength: 8+ chars, uppercase, lowercase, digit, special character
if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()\-_=+\[\]{};\':"\\|,.<>\/?]).{8,}$/', $pass)) {
    $_SESSION['error'] = 'Password must be at least 8 characters and include an uppercase letter, a lowercase letter, a number, and a special character.';
    redirect('../views/register.php');
}
// Confirm password must match
if ($pass !== $confirm_pass) {
    $_SESSION['error'] = 'Passwords do not match.';
    redirect('../views/register.php');
}
$data = [
    'name'    => $name,
    'email'   => $email,
    'pass'    => $pass,
    'country' => $country,
    'city'    => $city,
    'contact' => $contact,
];
$controller = new CustomerController();
$result     = $controller->register($data);
if ($result['success']) {
    // Store the new customer's details in the session
    $_SESSION['customer_id']    = $result['customer_id'];
    $_SESSION['customer_name']  = $result['customer_name'];
    $_SESSION['customer_email'] = $result['customer_email'];
    $_SESSION['user_role']      = $result['user_role'];
    // Redirect to the account page after successful registration
    redirect('../views/account/my_account.php');
} else {
    $_SESSION['error'] = $result['error'];
    redirect('../views/register.php');
}
