<?php
// Bootstrap core (session, helpers, DB)
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/ProductController.php';

// Only administrators may add products
require_admin();

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(APP_BASE . 'views/admin/product.php');
}

// ── Validate category and brand IDs ──────────────────────────────────────
$cat_id = filter_input(INPUT_POST, 'product_cat', FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]);
if ($cat_id === false || $cat_id === null) {
    $_SESSION['error'] = 'Please select a valid category.';
    redirect(APP_BASE . 'views/admin/product.php');
}

$brand_id = filter_input(INPUT_POST, 'product_brand', FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]);
if ($brand_id === false || $brand_id === null) {
    $_SESSION['error'] = 'Please select a valid brand.';
    redirect(APP_BASE . 'views/admin/product.php');
}

// ── Validate text fields ──────────────────────────────────────────────────
$title    = trim(strip_tags($_POST['product_title']    ?? ''));
$desc     = trim(strip_tags($_POST['product_desc']     ?? ''));
$keywords = trim(strip_tags($_POST['product_keywords'] ?? ''));

if ($title === '') {
    $_SESSION['error'] = 'Product title is required.';
    redirect(APP_BASE . 'views/admin/product.php');
}
if (mb_strlen($title) > 200) {
    $_SESSION['error'] = 'Product title must not exceed 200 characters.';
    redirect(APP_BASE . 'views/admin/product.php');
}
if ($desc === '') {
    $_SESSION['error'] = 'Product description is required.';
    redirect(APP_BASE . 'views/admin/product.php');
}
if ($keywords === '') {
    $_SESSION['error'] = 'Product keywords are required.';
    redirect(APP_BASE . 'views/admin/product.php');
}

// ── Validate price ────────────────────────────────────────────────────────
$price_raw = $_POST['product_price'] ?? '';
if (!is_numeric($price_raw) || (float)$price_raw < 0) {
    $_SESSION['error'] = 'Price must be a valid non-negative number.';
    redirect(APP_BASE . 'views/admin/product.php');
}
$price = (float)$price_raw;

// ── Confirm category and brand exist ─────────────────────────────────────
$controller = new ProductController();

if (!$controller->getCategoryById($cat_id)) {
    $_SESSION['error'] = 'Selected category does not exist.';
    redirect(APP_BASE . 'views/admin/product.php');
}
if (!$controller->getBrandById($brand_id)) {
    $_SESSION['error'] = 'Selected brand does not exist.';
    redirect(APP_BASE . 'views/admin/product.php');
}

// ── Validate image upload ─────────────────────────────────────────────────
if (!isset($_FILES['product_image']) || $_FILES['product_image']['error'] === UPLOAD_ERR_NO_FILE) {
    $_SESSION['error'] = 'A product image is required.';
    redirect(APP_BASE . 'views/admin/product.php');
}

if ($_FILES['product_image']['error'] !== UPLOAD_ERR_OK) {
    $_SESSION['error'] = 'Image upload failed (error code: ' . (int)$_FILES['product_image']['error'] . '). Please try again.';
    redirect(APP_BASE . 'views/admin/product.php');
}

define('MAX_IMAGE_SIZE', 5 * 1024 * 1024); // 5 MB
if ($_FILES['product_image']['size'] > MAX_IMAGE_SIZE) {
    $_SESSION['error'] = 'Image file must not exceed 5 MB.';
    redirect(APP_BASE . 'views/admin/product.php');
}

// Check actual MIME type — do NOT trust $_FILES[...]['type']
$finfo    = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $_FILES['product_image']['tmp_name']);
finfo_close($finfo);

$allowed = [
    'image/jpeg' => '.jpg',
    'image/png'  => '.png',
    'image/gif'  => '.gif',
    'image/webp' => '.webp',
];

if (!array_key_exists($mimeType, $allowed)) {
    $_SESSION['error'] = 'Only JPEG, PNG, GIF, and WebP images are allowed.';
    redirect(APP_BASE . 'views/admin/product.php');
}

$ext           = $allowed[$mimeType];
$safe_filename = uniqid('prod_', true) . $ext;
$upload_dir    = __DIR__ . '/../images/products/';

// Create directory if it does not exist
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

$destination = $upload_dir . $safe_filename;

if (!move_uploaded_file($_FILES['product_image']['tmp_name'], $destination)) {
    $_SESSION['error'] = 'Failed to save the uploaded image. Please try again.';
    redirect(APP_BASE . 'views/admin/product.php');
}

// ── Insert product into database ─────────────────────────────────────────
$result = $controller->addProduct($cat_id, $brand_id, $title, $price, $desc, $safe_filename, $keywords);

if ($result) {
    $_SESSION['success'] = 'Product added successfully.';
} else {
    // Remove the uploaded file if the DB insert failed
    if (file_exists($destination)) {
        unlink($destination);
    }
    $_SESSION['error'] = 'Failed to add product. Please try again.';
}
redirect(APP_BASE . 'views/admin/product.php');
