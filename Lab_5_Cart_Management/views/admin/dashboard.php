<?php
// Bootstrap core (session, helpers, DB) — two levels up from views/admin/
require_once __DIR__ . '/../../core/core.php';
require_once __DIR__ . '/../../controllers/ProductController.php';

// Only administrators may view this page
require_admin();

// ── Fetch summary counts through the MVC layer ────────────────────────────
$controller    = new ProductController();
$brandCount    = count($controller->getAllBrands());
$categoryCount = count($controller->getAllCategories());
$customerCount = $controller->getCustomerCount();

// Include the shared header (starts HTML output)
require_once '../layout/header.php';
?>
<div class="page-body">
<main>
    <h2>Admin Dashboard</h2>

    <?php if (isset($_SESSION['customer_name'])): ?>
        <p style="margin-bottom:24px;color:#4b5563;">
            Welcome back, <strong><?php echo htmlspecialchars($_SESSION['customer_name']); ?></strong>!
        </p>
    <?php endif; ?>

    <?php // ── Summary cards ──────────────────────────────────────────────── ?>
    <div style="display:flex;gap:20px;flex-wrap:wrap;margin-bottom:32px;">

        <div style="flex:1;min-width:160px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:20px 24px;text-align:center;">
            <p style="font-size:2rem;font-weight:700;color:#1d4ed8;margin-bottom:4px;">
                <?php echo $customerCount; ?>
            </p>
            <p style="font-size:0.9rem;color:#1e40af;font-weight:600;">Registered Customers</p>
        </div>

        <div style="flex:1;min-width:160px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:20px 24px;text-align:center;">
            <p style="font-size:2rem;font-weight:700;color:#15803d;margin-bottom:4px;">
                <?php echo $brandCount; ?>
            </p>
            <p style="font-size:0.9rem;color:#166534;font-weight:600;">Brands</p>
        </div>

        <div style="flex:1;min-width:160px;background:#fefce8;border:1px solid #fef08a;border-radius:8px;padding:20px 24px;text-align:center;">
            <p style="font-size:2rem;font-weight:700;color:#a16207;margin-bottom:4px;">
                <?php echo $categoryCount; ?>
            </p>
            <p style="font-size:0.9rem;color:#854d0e;font-weight:600;">Categories</p>
        </div>

    </div>

    <?php // ── Quick navigation ───────────────────────────────────────────── ?>
    <h3 style="margin-bottom:16px;color:#1e3a5f;">Quick Links</h3>
    <div style="display:flex;gap:14px;flex-wrap:wrap;">
        <a href="<?php echo APP_BASE; ?>views/admin/brand.php"
           style="background:#2563eb;color:#fff;padding:10px 22px;border-radius:5px;font-size:0.9rem;font-weight:600;text-decoration:none;">
            Manage Brands
        </a>
        <a href="<?php echo APP_BASE; ?>views/admin/category.php"
           style="background:#2563eb;color:#fff;padding:10px 22px;border-radius:5px;font-size:0.9rem;font-weight:600;text-decoration:none;">
            Manage Categories
        </a>
    </div>

</main>
</div>
<?php
require_once '../layout/footer.php';
?>
