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

require_once __DIR__ . '/layout/header.php';
?>
<div class="page-body">
    <?php require_once __DIR__ . '/layout/sidebar.php'; ?>
    <main>
        <?php if (!$product): ?>
            <p>Product not found. <a href="<?php echo APP_BASE; ?>index.php">Back to Home</a></p>
        <?php else: ?>
            <h2><?php echo htmlspecialchars($product['product_title'], ENT_QUOTES, 'UTF-8'); ?></h2>

            <?php if (!empty($product['product_image'])): ?>
                <img src="<?php echo APP_BASE . 'images/products/' . htmlspecialchars($product['product_image'], ENT_QUOTES, 'UTF-8'); ?>"
                     alt="<?php echo htmlspecialchars($product['product_title'], ENT_QUOTES, 'UTF-8'); ?>"
                     style="max-width:400px;border-radius:6px;border:1px solid #e2e8f0;margin-bottom:20px;display:block;">
            <?php endif; ?>

            <table style="border-collapse:collapse;margin-bottom:20px;">
                <tr>
                    <th style="padding:8px 14px 8px 0;text-align:left;color:#1e3a5f;">Category</th>
                    <td style="padding:8px 14px;"><?php echo htmlspecialchars($product['cat_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                </tr>
                <tr>
                    <th style="padding:8px 14px 8px 0;text-align:left;color:#1e3a5f;">Brand</th>
                    <td style="padding:8px 14px;"><?php echo htmlspecialchars($product['brand_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                </tr>
                <tr>
                    <th style="padding:8px 14px 8px 0;text-align:left;color:#1e3a5f;">Price</th>
                    <td style="padding:8px 14px;font-weight:700;color:#1e3a5f;">$<?php echo number_format((float)$product['product_price'], 2); ?></td>
                </tr>
                <?php if (!empty($product['product_keywords'])): ?>
                <tr>
                    <th style="padding:8px 14px 8px 0;text-align:left;color:#1e3a5f;">Keywords</th>
                    <td style="padding:8px 14px;"><?php echo htmlspecialchars($product['product_keywords'], ENT_QUOTES, 'UTF-8'); ?></td>
                </tr>
                <?php endif; ?>
            </table>

            <?php if (!empty($product['product_desc'])): ?>
                <h3 style="margin-bottom:8px;color:#1e3a5f;">Description</h3>
                <p style="margin-bottom:20px;line-height:1.7;">
                    <?php echo htmlspecialchars($product['product_desc'], ENT_QUOTES, 'UTF-8'); ?>
                </p>
            <?php endif; ?>

            <a href="<?php echo APP_BASE; ?>index.php?add_cart=<?php echo (int)$product['product_id']; ?>"
               class="btn btn-primary">Add to Cart</a>
            <a href="<?php echo APP_BASE; ?>index.php"
               style="margin-left:12px;font-size:0.9rem;">&#8592; Back to Home</a>
        <?php endif; ?>
    </main>
</div>
<?php
require_once __DIR__ . '/layout/footer.php';
?>
