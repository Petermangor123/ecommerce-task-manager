<?php
// search_results.php — displays product search results
// core.php is already loaded by index.php before this file is included
require_once __DIR__ . '/../controllers/ProductController.php';

$controller = new ProductController();

$raw_query  = isset($_GET['user_query']) ? trim($_GET['user_query']) : '';
$products   = [];
$page_title = 'Search Results';

if ($raw_query !== '') {
    $products = $controller->searchProducts($raw_query);
}

require_once __DIR__ . '/layout/header.php';
?>
<div class="page-body">
    <?php require_once __DIR__ . '/layout/sidebar.php'; ?>
    <main>
        <?php if ($raw_query === ''): ?>
            <h2>Search Products</h2>
            <p>Please enter a search term using the search box above.</p>
        <?php else: ?>
            <h2>Search results for: &#8220;<?php echo htmlspecialchars($raw_query, ENT_QUOTES, 'UTF-8'); ?>&#8221;</h2>

            <?php if (empty($products)): ?>
                <p>No products found for &#8220;<?php echo htmlspecialchars($raw_query, ENT_QUOTES, 'UTF-8'); ?>&#8221;.</p>
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
        <?php endif; ?>
    </main>
</div>
<?php
require_once __DIR__ . '/layout/footer.php';
?>
