<?php
// home.php — customer-facing product listing
// core.php is already loaded by index.php before this file is included
require_once __DIR__ . '/../controllers/ProductController.php';

$controller = new ProductController();

// ── Determine which products to show based on GET parameters ──────────────
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
    $products   = $controller->getFeaturedProducts();
    $page_title = 'Featured Products';
}

require_once 'layout/header.php';
?>
<div class="page-body">
    <?php require_once 'layout/sidebar.php'; ?>
    <main>
        <h2><?php echo htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8'); ?></h2>

        <?php if (isset($_SESSION['error'])): ?>
            <p class="error-msg">
                <?php echo htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error']); ?>
            </p>
        <?php endif; ?>

        <?php if (empty($products)): ?>
            <p>No products found.</p>
        <?php else: ?>
            <div class="product-grid">
                <?php foreach ($products as $p): ?>
                <div class="product-card">
                    <?php if (!empty($p['product_image'])): ?>
                        <img src="<?php echo APP_BASE . 'images/products/' . htmlspecialchars($p['product_image'], ENT_QUOTES, 'UTF-8'); ?>"
                             alt="<?php echo htmlspecialchars($p['product_title'], ENT_QUOTES, 'UTF-8'); ?>"
                             class="product-card__img">
                    <?php else: ?>
                        <div class="product-card__no-img">No Image</div>
                    <?php endif; ?>
                    <div class="product-card__body">
                        <h3 class="product-card__title">
                            <?php echo htmlspecialchars($p['product_title'], ENT_QUOTES, 'UTF-8'); ?>
                        </h3>
                        <p class="product-card__price">
                            $<?php echo number_format((float)$p['product_price'], 2); ?>
                        </p>
                        <div class="product-card__actions">
                            <a href="<?php echo APP_BASE; ?>index.php?pro_id=<?php echo (int)$p['product_id']; ?>"
                               class="btn btn-primary">Details</a>
                            <a href="<?php echo APP_BASE; ?>index.php?add_cart=<?php echo (int)$p['product_id']; ?>"
                               class="btn btn-secondary">Add to Cart</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
</div>
<?php
require_once 'layout/footer.php';
?>
