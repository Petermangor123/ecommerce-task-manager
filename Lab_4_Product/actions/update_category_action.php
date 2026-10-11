<?php
// Bootstrap core (session, helpers, DB)
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/ProductController.php';

// Only administrators may update categories
require_admin();

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(APP_BASE . 'views/admin/category.php');
}

// Validate cat_id — must be a positive integer
$cat_id = filter_input(INPUT_POST, 'cat_id', FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]);
if ($cat_id === false || $cat_id === null) {
    $_SESSION['error'] = 'Invalid category ID.';
    redirect(APP_BASE . 'views/admin/category.php');
}

// Sanitize and validate cat_name
$cat_name = trim(strip_tags($_POST['cat_name'] ?? ''));
if ($cat_name === '') {
    $_SESSION['error'] = 'Category name is required.';
    redirect(APP_BASE . 'views/admin/category.php?edit_id=' . $cat_id);
}
if (mb_strlen($cat_name) > 100) {
    $_SESSION['error'] = 'Category name must not exceed 100 characters.';
    redirect(APP_BASE . 'views/admin/category.php?edit_id=' . $cat_id);
}

$controller = new ProductController();

// Confirm the category actually exists before attempting an update
$existing = $controller->getCategoryById($cat_id);
if (!$existing) {
    $_SESSION['error'] = 'Category not found.';
    redirect(APP_BASE . 'views/admin/category.php');
}

$result = $controller->updateCategory($cat_id, $cat_name);

if ($result) {
    $_SESSION['success'] = 'Category updated successfully.';
} else {
    $_SESSION['error'] = 'Failed to update category. Please try again.';
}
redirect(APP_BASE . 'views/admin/category.php');
