<?php
// Bootstrap core (session, helpers, DB)
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/ProductController.php';

// Only administrators may add categories
require_admin();

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(APP_BASE . 'views/admin/category.php');
}

// Sanitize and validate the category name
$cat_name = trim(strip_tags($_POST['cat_name'] ?? ''));

if ($cat_name === '') {
    $_SESSION['error'] = 'Category name is required.';
    redirect(APP_BASE . 'views/admin/category.php');
}

if (mb_strlen($cat_name) > 100) {
    $_SESSION['error'] = 'Category name must not exceed 100 characters.';
    redirect(APP_BASE . 'views/admin/category.php');
}

$controller = new ProductController();
$result     = $controller->addCategory($cat_name);

if ($result) {
    $_SESSION['success'] = 'Category added successfully.';
} else {
    $_SESSION['error'] = 'Failed to add category. Please try again.';
}
redirect(APP_BASE . 'views/admin/category.php');
