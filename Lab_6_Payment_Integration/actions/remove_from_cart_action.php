<?php
// remove_from_cart_action.php — handles item removal from the cart (POST only)
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/CartController.php';

$cartUrl = APP_BASE . 'index.php?cart=1';

// Reject non-POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect($cartUrl);
}

// Validate CSRF token
if (!csrf_validate()) {
    $_SESSION['error'] = 'Your session expired. Please try again.';
    redirect($cartUrl);
}

// Read the submitted remove[] array
$submitted = $_POST['remove'] ?? [];
if (!is_array($submitted) || $submitted === []) {
    $_SESSION['error'] = 'Please select at least one item to remove.';
    redirect($cartUrl);
}

$ip   = get_ip();
$cart = new CartController();

$removed = 0;
foreach ($submitted as $raw_id) {
    // Validate each product ID as a positive integer — ignore anything else
    $product_id = filter_var($raw_id, FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1],
    ]);
    if (!$product_id) {
        continue;
    }
    // removeFromCart is IP-scoped in the model — cannot touch another visitor's row
    if ($cart->removeFromCart($product_id, $ip)) {
        $removed++;
    }
}

if ($removed > 0) {
    $_SESSION['success'] = $removed === 1
        ? 'Item removed from your cart.'
        : $removed . ' items removed from your cart.';
} else {
    $_SESSION['error'] = 'No matching cart items were removed.';
}

redirect($cartUrl);
