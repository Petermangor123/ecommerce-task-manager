<?php
// update_qty_action.php — handles cart quantity updates (POST only)
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

// Both arrays must be present and be arrays
$qty_inputs    = $_POST['qty']    ?? [];
$update_inputs = $_POST['update'] ?? [];

if (!is_array($qty_inputs) || !is_array($update_inputs)) {
    $_SESSION['error'] = 'Invalid form submission.';
    redirect($cartUrl);
}

// update[] drives the loop — qty[] is indexed by position, update[] holds product IDs
// The view submits them as parallel indexed arrays (update[0]=pid, qty[0]=qty, ...)
$ip   = get_ip();
$cart = new CartController();

$updated = 0;
$errors  = 0;

// Re-index both arrays to plain integer keys so we can pair them safely
$update_ids  = array_values($update_inputs);
$qty_values  = array_values($qty_inputs);
$count       = min(count($update_ids), count($qty_values));

for ($i = 0; $i < $count; $i++) {
    $product_id = filter_var($update_ids[$i], FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1],
    ]);
    $new_qty = filter_var($qty_values[$i], FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1],   // rejects 0 and negatives
    ]);

    if (!$product_id || !$new_qty) {
        // Bad product ID or non-positive quantity — skip and count as an error
        $errors++;
        continue;
    }

    // updateQty is IP-scoped in the model — cannot touch another visitor's row
    if ($cart->updateQty($product_id, $new_qty, $ip)) {
        $updated++;
    } else {
        $errors++;
    }
}

if ($errors > 0 && $updated === 0) {
    $_SESSION['error'] = 'Quantities must be positive whole numbers (1 or more).';
} elseif ($errors > 0) {
    $_SESSION['error']   = 'Some quantities were invalid and were not updated.';
    $_SESSION['success'] = $updated . ' item(s) updated successfully.';
} else {
    $_SESSION['success'] = 'Cart updated successfully.';
}

redirect($cartUrl);
