<?php
// sidebar.php lives in views/layout/ — go two levels up to reach the project root
require_once __DIR__ . '/../../controllers/ProductController.php';
// core.php is already loaded by the time any view includes this sidebar.
// The require_once guard prevents a fatal error if it hasn't been loaded yet.
require_once __DIR__ . '/../../core/core.php';

$sidebarController = new ProductController();
$brands            = $sidebarController->getAllBrands();
$categories        = $sidebarController->getAllCategories();
?>
<aside>
    <h3>Categories</h3>
    <ul>
    <?php if (empty($categories)): ?>
        <li>No categories available.</li>
    <?php else: ?>
        <?php foreach ($categories as $cat): ?>
        <li>
            <a href="<?php echo APP_BASE; ?>index.php?cat=<?php echo (int)$cat['cat_id']; ?>">
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
            <a href="<?php echo APP_BASE; ?>index.php?brand=<?php echo (int)$brand['brand_id']; ?>">
                <?php echo htmlspecialchars($brand['brand_name'], ENT_QUOTES, 'UTF-8'); ?>
            </a>
        </li>
        <?php endforeach; ?>
    <?php endif; ?>
    </ul>
</aside>
