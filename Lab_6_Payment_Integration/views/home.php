<?php
// home.php — customer-facing product listing
// core.php is already loaded by index.php before this file is included
require_once __DIR__ . '/../controllers/ProductController.php';

$controller = new ProductController();

// category filter wins over brand. otherwise show featured products.
// Category takes precedence over brand when both are supplied.
$cat_id   = filter_input(INPUT_GET, 'cat',   FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$brand_id = filter_input(INPUT_GET, 'brand', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

if ($cat_id) {
    $products   = $controller->getProductsByCategory($cat_id);
    $page_title = 'Products by Category';
} elseif ($brand_id) {
    $products   = $controller->getProductsByBrand($brand_id);
    $page_title = 'Products by Brand';
} else {
    $products   = $controller->getFeaturedProducts(12);
    $page_title = 'Featured Products';
}

require_once __DIR__ . '/layout/header.php';
?>
<div class="page-body">
    <?php require_once __DIR__ . '/layout/sidebar.php'; ?>
    <main>
        <h2><?php echo htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8'); ?></h2>

        <?php if (isset($_SESSION['error'])): ?>
            <p class="error-msg">
                <?php echo htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error']); ?>
            </p>
        <?php endif; ?>

        <?php if (!$cat_id && !$brand_id): ?>
            <p class="page-lead">Items currently in the store. Open a product for details, or add it straight to your cart.</p>
        <?php endif; ?>

        <?php if (empty($products)): ?>
            <p class="empty-note">No products found.</p>
        <?php else: ?>
            <div class="product-grid">
                <?php foreach ($products as $p): ?>
                    <?php require __DIR__ . '/layout/product_card.php'; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
</div>
<?php
require_once __DIR__ . '/layout/footer.php';
?>
