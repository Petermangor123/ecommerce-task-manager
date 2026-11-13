<?php
// Reuse the controller created by the parent view; instantiate only if missing.
require_once __DIR__ . '/../../controllers/ProductController.php';
require_once __DIR__ . '/../../core/core.php';

if (!isset($controller) || !($controller instanceof ProductController)) {
    $controller = new ProductController();
}

$categories = $controller->getAllCategories();
$brands     = $controller->getAllBrands();
$side_cat   = filter_input(INPUT_GET, 'cat', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$side_brand = filter_input(INPUT_GET, 'brand', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
?>
<aside class="shop-filters">
    <h3>Categories</h3>
    <ul>
        <li>
            <a href="<?php echo APP_BASE; ?>index.php">All products</a>
        </li>
    <?php if (empty($categories)): ?>
        <li>No categories available.</li>
    <?php else: ?>
        <?php foreach ($categories as $cat): ?>
        <li>
            <a href="<?php echo APP_BASE; ?>index.php?cat=<?php echo (int)$cat['cat_id']; ?>"<?php echo ($side_cat && (int)$cat['cat_id'] === (int)$side_cat) ? ' class="is-active"' : ''; ?>>
                <?php echo htmlspecialchars($cat['cat_name'], ENT_QUOTES, 'UTF-8'); ?>
            </a>
        </li>
        <?php endforeach; ?>
    <?php endif; ?>
    </ul>

    <h3>Brands</h3>
    <ul>
    <?php if (empty($brands)): ?>
        <li>No brands available.</li>
    <?php else: ?>
        <?php foreach ($brands as $brand): ?>
        <li>
            <a href="<?php echo APP_BASE; ?>index.php?brand=<?php echo (int)$brand['brand_id']; ?>"<?php echo ($side_brand && (int)$brand['brand_id'] === (int)$side_brand) ? ' class="is-active"' : ''; ?>>
                <?php echo htmlspecialchars($brand['brand_name'], ENT_QUOTES, 'UTF-8'); ?>
            </a>
        </li>
        <?php endforeach; ?>
    <?php endif; ?>
    </ul>
</aside>
