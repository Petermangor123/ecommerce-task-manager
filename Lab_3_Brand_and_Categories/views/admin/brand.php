<?php
// Bootstrap core (session, helpers, DB) — two levels up from views/admin/
require_once __DIR__ . '/../../core/core.php';
require_once __DIR__ . '/../../controllers/ProductController.php';
// Only administrators may view this page
require_admin();

$controller = new ProductController();

// ── Edit-mode detection ────────────────────────────────────────────────────
// If ?edit_id=N is present, validate it and fetch the brand to pre-fill the form.
$edit_mode  = false;
$edit_brand = null;
$edit_id    = filter_input(INPUT_GET, 'edit_id', FILTER_VALIDATE_INT,
                  ['options' => ['min_range' => 1]]);

if ($edit_id !== null && $edit_id !== false) {
    $edit_brand = $controller->getBrandById($edit_id);
    if ($edit_brand) {
        $edit_mode = true;
    }
    // If the ID was valid but no brand was found, $edit_mode stays false
    // and an error notice is shown below.
}

// Include the shared header (starts HTML output)
require_once '../layout/header.php';
?>
<div class="page-body">
<main>
    <h2>Brand Management</h2>

    <?php // ── Flash messages ───────────────────────────────────────────── ?>
    <?php if (isset($_SESSION['success'])): ?>
        <p style="background:#f0fdf4;border:1px solid #bbf7d0;color:#16a34a;padding:10px 14px;border-radius:5px;margin-bottom:16px;font-size:0.9rem;">
            <?php echo htmlspecialchars($_SESSION['success']); ?>
        </p>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <p class="error-msg">
            <?php echo htmlspecialchars($_SESSION['error']); ?>
        </p>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php // ── Invalid edit_id supplied but brand not found ──────────────── ?>
    <?php if ($edit_id !== null && $edit_id !== false && !$edit_brand): ?>
        <p class="error-msg">Brand not found. <a href="<?php echo APP_BASE; ?>views/admin/brand.php">Back to Brand list</a></p>
    <?php endif; ?>

    <?php // ── Add / Edit form ───────────────────────────────────────────── ?>
    <h3><?php echo $edit_mode ? 'Edit Brand' : 'Add New Brand'; ?></h3>

    <?php
    $form_action  = $edit_mode
        ? APP_BASE . 'actions/update_brand_action.php'
        : APP_BASE . 'actions/add_brand_action.php';
    $prefill_name = $edit_mode
        ? htmlspecialchars($edit_brand['brand_name'], ENT_QUOTES)
        : '';
    $submit_label = $edit_mode ? 'Update Brand' : 'Add Brand';
    ?>

    <form action="<?php echo $form_action; ?>" method="POST" id="brand-form">
        <?php if ($edit_mode): ?>
            <input type="hidden" name="brand_id" value="<?php echo (int)$edit_brand['brand_id']; ?>">
        <?php endif; ?>
        <div style="margin-bottom:16px;">
            <label for="brand_name">Brand Name</label>
            <input type="text" id="brand_name" name="brand_name"
                   placeholder="Enter brand name"
                   value="<?php echo $prefill_name; ?>"
                   required maxlength="100">
        </div>
        <button type="submit"><?php echo $submit_label; ?></button>
        <?php if ($edit_mode): ?>
            <a href="<?php echo APP_BASE; ?>views/admin/brand.php"
               style="margin-left:12px;font-size:0.9rem;">Cancel</a>
        <?php endif; ?>
    </form>

    <?php // ── Brands table ──────────────────────────────────────────────── ?>
    <h3 style="margin-top:28px;">All Brands</h3>
    <?php
    $brands = $controller->getAllBrands();
    if (empty($brands)):
    ?>
        <p>No brands found.</p>
    <?php else: ?>
        <table style="width:100%;border-collapse:collapse;margin-top:16px;">
            <thead>
                <tr>
                    <th style="padding:10px 12px;border:1px solid #e2e8f0;text-align:left;background:#f8fafc;">#</th>
                    <th style="padding:10px 12px;border:1px solid #e2e8f0;text-align:left;background:#f8fafc;">Brand Name</th>
                    <th style="padding:10px 12px;border:1px solid #e2e8f0;text-align:left;background:#f8fafc;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1; foreach ($brands as $brand): ?>
                <tr>
                    <td style="padding:10px 12px;border:1px solid #e2e8f0;"><?php echo $i++; ?></td>
                    <td style="padding:10px 12px;border:1px solid #e2e8f0;"><?php echo htmlspecialchars($brand['brand_name']); ?></td>
                    <td style="padding:10px 12px;border:1px solid #e2e8f0;">
                        <a href="<?php echo APP_BASE; ?>views/admin/brand.php?edit_id=<?php echo (int)$brand['brand_id']; ?>"
                           style="background:#2563eb;color:#fff;padding:5px 12px;border-radius:4px;font-size:0.85rem;text-decoration:none;">Edit</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</main>
</div>
<?php
require_once '../layout/footer.php';
?>
