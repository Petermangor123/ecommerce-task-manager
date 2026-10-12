<?php
// Bootstrap core (session, helpers, DB) — one level up from views/
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/ProductController.php';

// Read and sanitise the search term from the GET parameter
$raw_query  = $_GET['user_query'] ?? '';
$search_term = trim(strip_tags($raw_query));

// Run the search only when the term is non-empty
$products   = [];
$searched   = false;

if ($search_term !== '') {
    $searched   = true;
    $controller = new ProductController();
    $products   = $controller->searchProducts($search_term);
}

require_once __DIR__ . '/layout/header.php';
?>
<div class="page-body">
    <?php require_once __DIR__ . '/layout/sidebar.php'; ?>
    <main>
        <h2>Search Results</h2>

        <?php // ── Search form (pre-filled with the current query) ──────────── ?>
        <form action="<?php echo APP_BASE; ?>views/search_results.php"
              method="GET"
              style="display:flex;gap:8px;margin-bottom:24px;">
            <input type="text"
                   name="user_query"
                   value="<?php echo htmlspecialchars($search_term); ?>"
                   placeholder="Search products&hellip;"
                   style="flex:1;max-width:400px;">
            <button type="submit">Search</button>
        </form>

        <?php if (!$searched): ?>
            <?php // Empty search term submitted — prompt the user ?>
            <p>Enter a search term above to find products.</p>

        <?php elseif (empty($products)): ?>
            <?php // Term was searched but nothing matched ?>
            <p>No products found for
               &ldquo;<strong><?php echo htmlspecialchars($search_term); ?></strong>&rdquo;.
               Try a different keyword.
            </p>

        <?php else: ?>
            <p style="margin-bottom:16px;color:#6b7280;font-size:0.9rem;">
                <?php echo count($products); ?>
                result<?php echo count($products) !== 1 ? 's' : ''; ?> for
                &ldquo;<strong><?php echo htmlspecialchars($search_term); ?></strong>&rdquo;
            </p>

            <?php // ── Results table ──────────────────────────────────────────── ?>
            <table style="width:100%;border-collapse:collapse;font-size:0.9rem;">
                <thead>
                    <tr>
                        <th style="padding:10px 14px;border:1px solid #e5e7eb;text-align:left;background:#f8fafc;font-size:0.78rem;text-transform:uppercase;letter-spacing:.5px;color:#6b7280;">Product</th>
                        <th style="padding:10px 14px;border:1px solid #e5e7eb;text-align:left;background:#f8fafc;font-size:0.78rem;text-transform:uppercase;letter-spacing:.5px;color:#6b7280;">Brand</th>
                        <th style="padding:10px 14px;border:1px solid #e5e7eb;text-align:left;background:#f8fafc;font-size:0.78rem;text-transform:uppercase;letter-spacing:.5px;color:#6b7280;">Category</th>
                        <th style="padding:10px 14px;border:1px solid #e5e7eb;text-align:right;background:#f8fafc;font-size:0.78rem;text-transform:uppercase;letter-spacing:.5px;color:#6b7280;">Price</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                    <tr>
                        <td style="padding:11px 14px;border:1px solid #e5e7eb;vertical-align:top;">
                            <strong><?php echo htmlspecialchars($product['product_title']); ?></strong>
                            <?php if (!empty($product['product_desc'])): ?>
                                <br>
                                <span style="color:#6b7280;font-size:0.85rem;">
                                    <?php echo htmlspecialchars(mb_strimwidth($product['product_desc'], 0, 120, '…')); ?>
                                </span>
                            <?php endif; ?>
                        </td>
                        <td style="padding:11px 14px;border:1px solid #e5e7eb;white-space:nowrap;">
                            <?php echo htmlspecialchars($product['brand_name'] ?? '—'); ?>
                        </td>
                        <td style="padding:11px 14px;border:1px solid #e5e7eb;white-space:nowrap;">
                            <?php echo htmlspecialchars($product['cat_name'] ?? '—'); ?>
                        </td>
                        <td style="padding:11px 14px;border:1px solid #e5e7eb;text-align:right;white-space:nowrap;">
                            $<?php echo number_format((float)$product['product_price'], 2); ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </main>
</div>
<?php
require_once __DIR__ . '/layout/footer.php';
?>
