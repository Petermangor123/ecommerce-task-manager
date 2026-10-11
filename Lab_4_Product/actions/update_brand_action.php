<?php
// Bootstrap core (session, helpers, DB)
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/ProductController.php';

// Only administrators may update brands
require_admin();

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(APP_BASE . 'views/admin/brand.php');
}

// Validate brand_id — must be a positive integer
$brand_id = filter_input(INPUT_POST, 'brand_id', FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]);
if ($brand_id === false || $brand_id === null) {
    $_SESSION['error'] = 'Invalid brand ID.';
    redirect(APP_BASE . 'views/admin/brand.php');
}

// Sanitize and validate brand_name
$brand_name = trim(strip_tags($_POST['brand_name'] ?? ''));
if ($brand_name === '') {
    $_SESSION['error'] = 'Brand name is required.';
    redirect(APP_BASE . 'views/admin/brand.php?edit_id=' . $brand_id);
}
if (mb_strlen($brand_name) > 100) {
    $_SESSION['error'] = 'Brand name must not exceed 100 characters.';
    redirect(APP_BASE . 'views/admin/brand.php?edit_id=' . $brand_id);
}

$controller = new ProductController();

// Confirm the brand actually exists before attempting an update
$existing = $controller->getBrandById($brand_id);
if (!$existing) {
    $_SESSION['error'] = 'Brand not found.';
    redirect(APP_BASE . 'views/admin/brand.php');
}

$result = $controller->updateBrand($brand_id, $brand_name);

if ($result) {
    $_SESSION['success'] = 'Brand updated successfully.';
} else {
    $_SESSION['error'] = 'Failed to update brand. Please try again.';
}
redirect(APP_BASE . 'views/admin/brand.php');
