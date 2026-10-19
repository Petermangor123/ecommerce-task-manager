<?php
// Bootstrap core (session, helpers, DB)
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/ProductController.php';

// Only administrators may update products
require_admin();

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(APP_BASE . 'views/admin/product.php');
}

// ── Validate product ID ───────────────────────────────────────────────────
$product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]);
if ($product_id === false || $product_id === null) {
    $_SESSION['error'] = 'Invalid product ID.';
    redirect(APP_BASE . 'views/admin/product.php');
}

$controller       = new ProductController();
$existing_product = $controller->getProductById($product_id);

if (!$existing_product) {
    $_SESSION['error'] = 'Product not found.';
    redirect(APP_BASE . 'views/admin/product.php');
}

// ── Validate category and brand IDs ──────────────────────────────────────
$cat_id = filter_input(INPUT_POST, 'product_cat', FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]);
if ($cat_id === false || $cat_id === null) {
    $_SESSION['error'] = 'Please select a valid category.';
    redirect(APP_BASE . 'views/admin/product.php?edit_id=' . $product_id);
}

$brand_id = filter_input(INPUT_POST, 'product_brand', FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]);
if ($brand_id === false || $brand_id === null) {
    $_SESSION['error'] = 'Please select a valid brand.';
    redirect(APP_BASE . 'views/admin/product.php?edit_id=' . $product_id);
}

// ── Validate text fields ──────────────────────────────────────────────────
$title    = trim(strip_tags($_POST['product_title']    ?? ''));
$desc     = trim(strip_tags($_POST['product_desc']     ?? ''));
$keywords = trim(strip_tags($_POST['product_keywords'] ?? ''));

if ($title === '') {
    $_SESSION['error'] = 'Product title is required.';
    redirect(APP_BASE . 'views/admin/product.php?edit_id=' . $product_id);
}
if (mb_strlen($title) > 200) {
    $_SESSION['error'] = 'Product title must not exceed 200 characters.';
    redirect(APP_BASE . 'views/admin/product.php?edit_id=' . $product_id);
}
if ($desc === '') {
    $_SESSION['error'] = 'Product description is required.';
    redirect(APP_BASE . 'views/admin/product.php?edit_id=' . $product_id);
}
if ($keywords === '') {
    $_SESSION['error'] = 'Product keywords are required.';
    redirect(APP_BASE . 'views/admin/product.php?edit_id=' . $product_id);
}

// ── Validate price ────────────────────────────────────────────────────────
$price_raw = $_POST['product_price'] ?? '';
if (!is_numeric($price_raw) || (float)$price_raw < 0) {
    $_SESSION['error'] = 'Price must be a valid non-negative number.';
    redirect(APP_BASE . 'views/admin/product.php?edit_id=' . $product_id);
}
$price = (float)$price_raw;

// ── Confirm category and brand exist ─────────────────────────────────────
if (!$controller->getCategoryById($cat_id)) {
    $_SESSION['error'] = 'Selected category does not exist.';
    redirect(APP_BASE . 'views/admin/product.php?edit_id=' . $product_id);
}
if (!$controller->getBrandById($brand_id)) {
    $_SESSION['error'] = 'Selected brand does not exist.';
    redirect(APP_BASE . 'views/admin/product.php?edit_id=' . $product_id);
}

// ── Image handling ────────────────────────────────────────────────────────
$upload_dir   = __DIR__ . '/../images/products/';
$new_filename = null; // will be set only if a new file is uploaded

if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] !== UPLOAD_ERR_NO_FILE) {

    if ($_FILES['product_image']['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['error'] = 'Image upload failed (error code: ' . (int)$_FILES['product_image']['error'] . '). Please try again.';
        redirect(APP_BASE . 'views/admin/product.php?edit_id=' . $product_id);
    }

    if ($_FILES['product_image']['size'] > 5 * 1024 * 1024) {
        $_SESSION['error'] = 'Image file must not exceed 5 MB.';
        redirect(APP_BASE . 'views/admin/product.php?edit_id=' . $product_id);
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
        redirect(APP_BASE . 'views/admin/product.php?edit_id=' . $product_id);
    }

    $ext          = $allowed[$mimeType];
    $new_filename = uniqid('prod_', true) . $ext;

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $destination = $upload_dir . $new_filename;

    if (!move_uploaded_file($_FILES['product_image']['tmp_name'], $destination)) {
        $_SESSION['error'] = 'Failed to save the uploaded image. Please try again.';
        redirect(APP_BASE . 'views/admin/product.php?edit_id=' . $product_id);
    }
}

// Determine which filename to store.
// IMPORTANT: get existing filename from DB — never trust a hidden form field.
$old_filename   = $existing_product['product_image'];
$final_filename = ($new_filename !== null) ? $new_filename : $old_filename;

// ── Update product in database ────────────────────────────────────────────
$result = $controller->updateProduct(
    $product_id, $cat_id, $brand_id, $title, $price, $desc, $final_filename, $keywords
);

if ($result) {
    // Delete old image file only if a new one replaced it
    if ($new_filename !== null && !empty($old_filename) && $old_filename !== $new_filename) {
        $upload_dir_real = realpath($upload_dir);
        $old_path        = realpath($upload_dir . $old_filename);
        // Path traversal guard: only delete if the resolved path is inside our upload directory
        if ($old_path !== false && $upload_dir_real !== false &&
            strpos($old_path, $upload_dir_real . DIRECTORY_SEPARATOR) === 0 &&
            file_exists($old_path)) {
            unlink($old_path);
        }
    }
    $_SESSION['success'] = 'Product updated successfully.';
} else {
    // DB update failed — remove newly uploaded file (if any) but keep the old image
    if ($new_filename !== null) {
        $new_path = $upload_dir . $new_filename;
        if (file_exists($new_path)) {
            unlink($new_path);
        }
    }
    $_SESSION['error'] = 'Failed to update product. Please try again.';
}
redirect(APP_BASE . 'views/admin/product.php');
