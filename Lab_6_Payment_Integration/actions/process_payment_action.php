<?php
// process_payment_action.php
// Receives a Paystack transaction reference from the client-side callback,
// verifies it server-side via cURL, then creates the order if everything checks out.
// Never trust the client-side success callback alone.

require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../core/paystack_config.php';
require_once __DIR__ . '/../controllers/CartController.php';

$cartUrl    = APP_BASE . 'index.php?cart=1';
$paymentUrl = APP_BASE . 'views/payment.php';
$failUrl    = APP_BASE . 'views/payment_failed.php';

// 1. Only GET requests arrive here (Paystack callback redirects with ?ref=...)
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    redirect($cartUrl);
}

// 2. Customer must be logged in
require_login();

// 3. Validate the reference from the query string
$reference = trim($_GET['ref'] ?? '');
if ($reference === '' || !preg_match('/^[A-Za-z0-9_\-]+$/', $reference)) {
    $_SESSION['error'] = 'Invalid or missing payment reference.';
    redirect($failUrl);
}

// 4. Retrieve the expected amount from the session (set in views/payment.php)
$expected_amount = isset($_SESSION['expected_amount'])
    ? (float)$_SESSION['expected_amount']
    : 0.0;

if ($expected_amount <= 0) {
    $_SESSION['error'] = 'Session expired or no pending payment found. Please try again.';
    redirect($paymentUrl);
}

// 5. Verify the transaction with Paystack's API (server-side)
$verify_url = 'https://api.paystack.co/transaction/verify/' . rawurlencode($reference);

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL            => $verify_url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => [
        'Authorization: Bearer ' . PAYSTACK_SECRET_KEY,
        'Cache-Control: no-cache',
    ],
    CURLOPT_TIMEOUT        => 30,
    CURLOPT_SSL_VERIFYPEER => true,
]);

$response_body = curl_exec($ch);
$curl_error    = curl_error($ch);
$http_code     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($curl_error || $response_body === false) {
    error_log('[process_payment] cURL error: ' . $curl_error);
    $_SESSION['error'] = 'Could not reach the payment gateway. Please try again.';
    redirect($failUrl);
}

$response = json_decode($response_body, true);

if (!is_array($response) || empty($response['status'])) {
    error_log('[process_payment] Bad JSON from Paystack: ' . $response_body);
    $_SESSION['error'] = 'Unexpected response from payment gateway.';
    redirect($failUrl);
}

// 6. Validate the response fields
if ($response['status'] !== true || empty($response['data'])) {
    $_SESSION['error'] = 'Payment verification failed: ' . ($response['message'] ?? 'Unknown error');
    redirect($failUrl);
}

$data = $response['data'];

// Status must be 'success'
if (($data['status'] ?? '') !== 'success') {
    $_SESSION['error'] = 'Payment was not successful. Status: ' . htmlspecialchars($data['status'] ?? 'unknown');
    redirect($failUrl);
}

// Reference must match what we sent
if (($data['reference'] ?? '') !== $reference) {
    error_log('[process_payment] Reference mismatch. Expected: ' . $reference . ' Got: ' . ($data['reference'] ?? ''));
    $_SESSION['error'] = 'Payment reference mismatch. Please contact support.';
    redirect($failUrl);
}

// Currency must be GHS
if (($data['currency'] ?? '') !== PAYSTACK_CURRENCY) {
    $_SESSION['error'] = 'Invalid payment currency.';
    redirect($failUrl);
}

// Amount must match (Paystack returns amount in pesewas — convert to GHS)
$paid_amount_ghs = (float)($data['amount'] ?? 0) / 100.0;
if (abs($paid_amount_ghs - $expected_amount) > 0.01) {
    error_log('[process_payment] Amount mismatch. Expected: ' . $expected_amount . ' Paid: ' . $paid_amount_ghs);
    $_SESSION['error'] = 'Payment amount does not match order total.';
    redirect($failUrl);
}

// 7. All checks passed — create the order
$customer_id = (int)$_SESSION['customer_id'];
$ip          = get_ip();
$cart        = new CartController();

$order_id = $cart->processPayment($customer_id, $ip, $reference, $paid_amount_ghs);

if (!$order_id) {
    // Could be a duplicate reference or empty cart — log and show error
    error_log('[process_payment] processPayment() returned false for ref=' . $reference . ' customer=' . $customer_id);
    $_SESSION['error'] = 'Your order could not be recorded. If payment was taken, please contact support with reference: ' . htmlspecialchars($reference);
    redirect($failUrl);
}

// 8. Clean up session payment state
unset($_SESSION['expected_amount']);

// 9. Redirect to invoice
$_SESSION['success'] = 'Payment successful! Your order has been placed.';
redirect(APP_BASE . 'views/payment_success.php?order_id=' . $order_id);
