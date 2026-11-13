<?php
// single_product.php — displays a single product's full details
// core.php is already loaded by index.php before this file is included
require_once __DIR__ . '/../controllers/ProductController.php';

$controller = new ProductController();

// Validate pro_id; redirect home if missing or invalid
$pro_id = filter_input(INPUT_GET, 'pro_id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if (!$pro_id) {
    redirect(APP_BASE . 'index.php');
}

$product = $controller->getProductById($pro_id);
if (!$product) {
    redirect(APP_BASE . 'index.php');
}

$cat_name   = $product['cat_name'] ?? '';
$brand_name = $product['brand_name'] ?? '';
if ($cat_name === '' && !empty($product['product_cat'])) {
    $category = $controller->getCategoryById((int)$product['product_cat']);
    $cat_name = $category ? $category['cat_name'] : '';
}
if ($brand_name === '' && !empty($product['product_brand'])) {
    $brand = $controller->getBrandById((int)$product['product_brand']);
    $brand_name = $brand ? $brand['brand_name'] : '';
}

require_once __DIR__ . '/layout/header.php';
?>
<div class="page-body">
    <?php require_once __DIR__ . '/layout/sidebar.php'; ?>
    <main>
        <article class="product-detail">
            <div class="product-detail__media">
                <?php if (!empty($product['product_image'])): ?>
                    <img src="<?php echo APP_BASE . 'images/products/' . htmlspecialchars($product['product_image'], ENT_QUOTES, 'UTF-8'); ?>"
                         alt="<?php echo htmlspecialchars($product['product_title'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                         class="product-detail__img">
                <?php else: ?>
                    <div class="product-card__no-img product-detail__img">No Image</div>
                <?php endif; ?>
            </div>

            <div class="product-detail__info">
                <p class="product-card__meta">
                    <?php echo htmlspecialchars(trim($brand_name . ($brand_name !== '' && $cat_name !== '' ? ' · ' : '') . $cat_name), ENT_QUOTES, 'UTF-8'); ?>
                </p>
                <h2><?php echo htmlspecialchars($product['product_title'] ?? '', ENT_QUOTES, 'UTF-8'); ?></h2>
                <p class="product-detail__price">GH₵ <?php echo number_format((float)$product['product_price'], 2); ?></p>

                <dl class="product-facts">
                    <div>
                        <dt>Category</dt>
                        <dd><?php echo htmlspecialchars($cat_name !== '' ? $cat_name : '—', ENT_QUOTES, 'UTF-8'); ?></dd>
                    </div>
                    <div>
                        <dt>Brand</dt>
                        <dd><?php echo htmlspecialchars($brand_name !== '' ? $brand_name : '—', ENT_QUOTES, 'UTF-8'); ?></dd>
                    </div>
                    <?php if (!empty($product['product_keywords'])): ?>
                    <div>
                        <dt>Keywords</dt>
                        <dd><?php echo htmlspecialchars($product['product_keywords'], ENT_QUOTES, 'UTF-8'); ?></dd>
                    </div>
                    <?php endif; ?>
                </dl>

                <?php if (!empty($product['product_desc'])): ?>
                    <h3>Description</h3>
                    <p class="product-detail__desc">
                        <?php echo htmlspecialchars($product['product_desc'], ENT_QUOTES, 'UTF-8'); ?>
                    </p>
                <?php endif; ?>

                <form class="buy-box" action="<?php echo APP_BASE; ?>index.php" method="GET">
                    <input type="hidden" name="add_cart" value="<?php echo (int)$product['product_id']; ?>">
                    <label for="qty">Quantity</label>
                    <input type="number" id="qty" name="qty" class="qty-input" value="1" min="1" max="99" step="1" required>
                    <button type="submit" class="btn btn-primary">Add to Cart</button>
                </form>
                <p class="buy-note">If this item is already in your cart, change the quantity on the cart page.</p>
                <p><a href="<?php echo APP_BASE; ?>index.php">Continue Shopping</a></p>
            </div>
        </article>
    </main>
</div>
<?php
require_once __DIR__ . '/layout/footer.php';
?>
