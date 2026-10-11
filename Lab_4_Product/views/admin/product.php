<?php
// Bootstrap core (session, helpers, DB) — two levels up from views/admin/
require_once __DIR__ . '/../../core/core.php';
require_once __DIR__ . '/../../controllers/ProductController.php';

// Only administrators may view this page
require_admin();

$controller = new ProductController();

// ── Edit-mode detection ────────────────────────────────────────────────────
$edit_mode    = false;
$edit_product = null;
$edit_id      = filter_input(INPUT_GET, 'edit_id', FILTER_VALIDATE_INT,
                    ['options' => ['min_range' => 1]]);

if ($edit_id !== null && $edit_id !== false) {
    $edit_product = $controller->getProductById($edit_id);
    if ($edit_product) {
        $edit_mode = true;
    }
}

// Retrieve data for dropdowns and the product list
$categories = $controller->getAllCategories();
$brands     = $controller->getAllBrands();
$products   = $controller->getAllProducts();

// Include the shared header
require_once '../layout/header.php';
?>
<div class="page-body">
<main>
    <h2>Product Management</h2>

    <?php // ── Flash messages ─────────────────────────────────────────────── ?>
    <?php if (isset($_SESSION['success'])): ?>
        <p style="background:#f0fdf4;border:1px solid #bbf7d0;color:#16a34a;padding:10px 14px;border-radius:5px;margin-bottom:16px;font-size:0.9rem;">
            <?php echo htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8'); ?>
        </p>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <p class="error-msg">
            <?php echo htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8'); ?>
        </p>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php // ── Invalid edit_id supplied but product not found ──────────────── ?>
    <?php if ($edit_id !== null && $edit_id !== false && !$edit_product): ?>
        <p class="error-msg">Product not found. <a href="<?php echo APP_BASE; ?>views/admin/product.php">Back to Product list</a></p>
    <?php endif; ?>

    <?php // ── Add / Edit form ──────────────────────────────────────────────── ?>
    <h3><?php echo $edit_mode ? 'Edit Product' : 'Add New Product'; ?></h3>

    <?php
    $form_action  = $edit_mode
        ? APP_BASE . 'actions/update_product_action.php'
        : APP_BASE . 'actions/add_product_action.php';
    $submit_label = $edit_mode ? 'Update Product' : 'Add Product';
    ?>

    <form action="<?php echo $form_action; ?>" method="POST"
          enctype="multipart/form-data" id="product-form">

        <?php if ($edit_mode): ?>
            <input type="hidden" name="product_id"
                   value="<?php echo (int)$edit_product['product_id']; ?>">
        <?php endif; ?>

        <?php // Product Title ?>
        <div style="margin-bottom:16px;">
            <label for="product_title">Product Title <span style="color:#dc2626;">*</span></label>
            <input type="text" id="product_title" name="product_title"
                   placeholder="Enter product title"
                   value="<?php echo $edit_mode ? htmlspecialchars($edit_product['product_title'], ENT_QUOTES, 'UTF-8') : ''; ?>"
                   required maxlength="200">
        </div>

        <?php // Price ?>
        <div style="margin-bottom:16px;">
            <label for="product_price">Price <span style="color:#dc2626;">*</span></label>
            <input type="number" id="product_price" name="product_price"
                   placeholder="0.00" step="0.01" min="0"
                   value="<?php echo $edit_mode ? htmlspecialchars($edit_product['product_price'], ENT_QUOTES, 'UTF-8') : ''; ?>"
                   required>
        </div>

        <?php // Description ?>
        <div style="margin-bottom:16px;">
            <label for="product_desc">Description <span style="color:#dc2626;">*</span></label>
            <textarea id="product_desc" name="product_desc"
                      placeholder="Enter product description"
                      rows="4" required
                      style="width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:5px;font-size:0.9rem;resize:vertical;"><?php echo $edit_mode ? htmlspecialchars($edit_product['product_desc'], ENT_QUOTES, 'UTF-8') : ''; ?></textarea>
        </div>

        <?php // Keywords ?>
        <div style="margin-bottom:16px;">
            <label for="product_keywords">Keywords <span style="color:#dc2626;">*</span></label>
            <input type="text" id="product_keywords" name="product_keywords"
                   placeholder="e.g. electronics, phone, mobile"
                   value="<?php echo $edit_mode ? htmlspecialchars($edit_product['product_keywords'], ENT_QUOTES, 'UTF-8') : ''; ?>"
                   required maxlength="100">
        </div>

        <?php // Category dropdown ?>
        <div style="margin-bottom:16px;">
            <label for="product_cat">Category <span style="color:#dc2626;">*</span></label>
            <select id="product_cat" name="product_cat" required>
                <option value="">-- Select Category --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo (int)$cat['cat_id']; ?>"
                        <?php echo ($edit_mode && (int)$edit_product['product_cat'] === (int)$cat['cat_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['cat_name'], ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <?php // Brand dropdown ?>
        <div style="margin-bottom:16px;">
            <label for="product_brand">Brand <span style="color:#dc2626;">*</span></label>
            <select id="product_brand" name="product_brand" required>
                <option value="">-- Select Brand --</option>
                <?php foreach ($brands as $brand): ?>
                    <option value="<?php echo (int)$brand['brand_id']; ?>"
                        <?php echo ($edit_mode && (int)$edit_product['product_brand'] === (int)$brand['brand_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($brand['brand_name'], ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <?php // Product image ?>
        <div style="margin-bottom:16px;">
            <label for="product_image">Product Image<?php echo $edit_mode ? '' : ' <span style="color:#dc2626;">*</span>'; ?></label>

            <?php if ($edit_mode && !empty($edit_product['product_image'])): ?>
                <div style="margin-bottom:8px;">
                    <img src="<?php echo APP_BASE . 'images/products/' . htmlspecialchars($edit_product['product_image'], ENT_QUOTES, 'UTF-8'); ?>"
                         alt="Current product image"
                         style="max-width:120px;max-height:120px;border:1px solid #e2e8f0;border-radius:4px;object-fit:contain;">
                    <p style="font-size:0.85rem;color:#6b7280;margin-top:4px;">
                        Leave the file input empty to keep the current image.
                    </p>
                </div>
            <?php endif; ?>

            <input type="file" id="product_image" name="product_image"
                   accept="image/jpeg,image/png,image/gif,image/webp"
                   <?php echo $edit_mode ? '' : 'required'; ?>>
            <span id="image-error"
                  style="display:block;font-size:0.8rem;color:#dc2626;margin-top:3px;min-height:1em;"></span>
        </div>

        <button type="submit"><?php echo $submit_label; ?></button>
        <a href="<?php echo APP_BASE; ?>views/admin/product.php"
           style="margin-left:12px;font-size:0.9rem;">Cancel</a>

    </form>

    <?php // ── Products table ─────────────────────────────────────────────── ?>
    <h3 style="margin-top:28px;">All Products</h3>
    <?php if (empty($products)): ?>
        <p>No products found.</p>
    <?php else: ?>
        <table style="width:100%;border-collapse:collapse;margin-top:16px;">
            <thead>
                <tr>
                    <th style="padding:10px 12px;border:1px solid #e2e8f0;text-align:left;background:#f8fafc;">#</th>
                    <th style="padding:10px 12px;border:1px solid #e2e8f0;text-align:left;background:#f8fafc;">Image</th>
                    <th style="padding:10px 12px;border:1px solid #e2e8f0;text-align:left;background:#f8fafc;">Title</th>
                    <th style="padding:10px 12px;border:1px solid #e2e8f0;text-align:left;background:#f8fafc;">Category</th>
                    <th style="padding:10px 12px;border:1px solid #e2e8f0;text-align:left;background:#f8fafc;">Brand</th>
                    <th style="padding:10px 12px;border:1px solid #e2e8f0;text-align:left;background:#f8fafc;">Price</th>
                    <th style="padding:10px 12px;border:1px solid #e2e8f0;text-align:left;background:#f8fafc;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1; foreach ($products as $product): ?>
                <tr>
                    <td style="padding:10px 12px;border:1px solid #e2e8f0;"><?php echo $i++; ?></td>
                    <td style="padding:10px 12px;border:1px solid #e2e8f0;">
                        <?php if (!empty($product['product_image'])): ?>
                            <img src="<?php echo APP_BASE . 'images/products/' . htmlspecialchars($product['product_image'], ENT_QUOTES, 'UTF-8'); ?>"
                                 alt="<?php echo htmlspecialchars($product['product_title'], ENT_QUOTES, 'UTF-8'); ?>"
                                 style="width:50px;height:50px;object-fit:cover;border-radius:4px;border:1px solid #e2e8f0;">
                        <?php else: ?>
                            <span style="color:#9ca3af;font-size:0.8rem;">No image</span>
                        <?php endif; ?>
                    </td>
                    <td style="padding:10px 12px;border:1px solid #e2e8f0;">
                        <?php echo htmlspecialchars($product['product_title'], ENT_QUOTES, 'UTF-8'); ?>
                    </td>
                    <td style="padding:10px 12px;border:1px solid #e2e8f0;">
                        <?php echo htmlspecialchars($product['cat_name'], ENT_QUOTES, 'UTF-8'); ?>
                    </td>
                    <td style="padding:10px 12px;border:1px solid #e2e8f0;">
                        <?php echo htmlspecialchars($product['brand_name'], ENT_QUOTES, 'UTF-8'); ?>
                    </td>
                    <td style="padding:10px 12px;border:1px solid #e2e8f0;">
                        $<?php echo number_format((float)$product['product_price'], 2); ?>
                    </td>
                    <td style="padding:10px 12px;border:1px solid #e2e8f0;">
                        <a href="<?php echo APP_BASE; ?>views/admin/product.php?edit_id=<?php echo (int)$product['product_id']; ?>"
                           style="background:#2563eb;color:#fff;padding:5px 12px;border-radius:4px;font-size:0.85rem;text-decoration:none;">Edit</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

</main>
</div>

<?php // ── Client-side image validation ────────────────────────────────────── ?>
<script>
(function () {
    var input     = document.getElementById('product_image');
    var errorSpan = document.getElementById('image-error');

    if (!input) { return; }

    var ALLOWED_EXT = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    var MAX_BYTES   = 5 * 1024 * 1024; // 5 MB

    input.addEventListener('change', function () {
        errorSpan.textContent = '';

        if (!input.files || input.files.length === 0) {
            return;
        }

        var file = input.files[0];
        var ext  = file.name.split('.').pop().toLowerCase();

        if (ALLOWED_EXT.indexOf(ext) === -1) {
            errorSpan.textContent = 'Invalid file type. Only JPEG, PNG, GIF, and WebP are allowed.';
            input.value = '';
            return;
        }

        if (file.size > MAX_BYTES) {
            errorSpan.textContent = 'File is too large. Maximum allowed size is 5 MB.';
            input.value = '';
            return;
        }
    });
}());
</script>

<?php
require_once '../layout/footer.php';
?>
