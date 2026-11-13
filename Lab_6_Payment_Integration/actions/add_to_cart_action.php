<?php
// Add a product to the visitor's IP-based cart (GET ?add_cart=N)
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/CartController.php';
require_once __DIR__ . '/../controllers/ProductController.php';

$product_id = filter_input(INPUT_GET, 'add_cart', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1],
]);

if (!$product_id) {
    $_SESSION['error'] = 'Invalid product.';
    redirect(APP_BASE . 'index.php');
}

$productController = new ProductController();
$product           = $productController->getProductById($product_id);
if (!$product) {
    $_SESSION['error'] = 'Product not found.';
    redirect_back();
}

$ip             = get_ip();
$cartController = new CartController();

if ($cartController->isInCart($product_id, $ip)) {
    $_SESSION['error'] = 'This item is already in your cart. Go to cart to update quantity.';
    redirect_back();
}

// qty comes from the product page. the card links don't send it, so those add 1.
$qty = filter_input(INPUT_GET, 'qty', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1, 'max_range' => 99],
]);
if (!$qty) {
    $qty = 1;
}

$customer_id = is_logged_in() ? (int)$_SESSION['customer_id'] : null;
$added       = $cartController->addToCart($product_id, $qty, $ip, $customer_id);

if ($added) {
    $_SESSION['success'] = 'Product added to cart.';
} else {
    $_SESSION['error'] = 'Could not add this product to your cart. Please try again.';
}

redirect_back();
