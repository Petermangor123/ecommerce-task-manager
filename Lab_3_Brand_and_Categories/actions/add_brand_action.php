<?php
// Bootstrap core (session, helpers, DB)
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/ProductController.php';
// Only administrators may add brands
require_admin();
// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(APP_BASE . 'views/admin/brand.php');
}
// Sanitize and validate the brand name
$brand_name = trim(strip_tags($_POST['brand_name'] ?? ''));
if ($brand_name === '') {
    $_SESSION['error'] = 'Brand name is required.';
    redirect(APP_BASE . 'views/admin/brand.php');
}
$controller = new ProductController();
$result     = $controller->addBrand($brand_name);
if ($result) {
    $_SESSION['success'] = 'Brand added successfully.';
} else {
    $_SESSION['error'] = 'Failed to add brand. Please try again.';
}
redirect(APP_BASE . 'views/admin/brand.php');
