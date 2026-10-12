<?php
// sidebar.php lives in views/layout/ — go two levels up to reach the project root
require_once __DIR__ . '/../../controllers/ProductController.php';
// ProductController requires core.php (for the DB connection) which is already
// loaded by the time any view includes this sidebar, so no double-include needed.
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
        <li><?php echo htmlspecialchars($cat['cat_name'], ENT_QUOTES, 'UTF-8'); ?></li>
        <?php endforeach; ?>
    <?php endif; ?>
    </ul>

    <h3>Brands</h3>
    <ul>
    <?php if (empty($brands)): ?>
        <li>No brands available.</li>
    <?php else: ?>
        <?php foreach ($brands as $brand): ?>
        <li><?php echo htmlspecialchars($brand['brand_name']); ?></li>
        <?php endforeach; ?>
    <?php endif; ?>
    </ul>

</aside>
