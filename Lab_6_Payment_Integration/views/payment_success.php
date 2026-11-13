<?php
// payment_success.php — invoice page shown after successful payment
if (!defined('APP_BASE')) {
    require_once __DIR__ . '/../core/core.php';
}
require_once __DIR__ . '/../controllers/CartController.php';

// Must be logged in
require_login();

// Validate order_id from URL
$order_id = filter_input(INPUT_GET, 'order_id', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1],
]);
if (!$order_id) {
    $_SESSION['error'] = 'Invalid order reference.';
    redirect(APP_BASE . 'index.php');
}

// Fetch the full order
$cartController = new CartController();
$data           = $cartController->getOrderById($order_id);

if (!$data) {
    $_SESSION['error'] = 'Order not found.';
    redirect(APP_BASE . 'index.php');
}

$order = $data['order'];
$items = $data['items'];

// Access control — this invoice belongs only to the logged-in customer
if ((int)$order['customer_id'] !== (int)$_SESSION['customer_id']) {
    $_SESSION['error'] = 'Access denied.';
    redirect(APP_BASE . 'index.php');
}

// Calculate grand total from line items (authoritative source)
$grand_total = 0.0;
foreach ($items as $item) {
    $grand_total += (float)$item['line_total'];
}

// send the receipt once per order, if mail is turned on
$email_sent = false;
if (!isset($_SESSION['order_email_sent_' . $order_id])) {
    if (file_exists(__DIR__ . '/../core/mailer.php')) {
        require_once __DIR__ . '/../core/mailer.php';
        $email_sent = send_order_confirmation($order, $items, $grand_total);
    }
    $_SESSION['order_email_sent_' . $order_id] = true;
}

require_once __DIR__ . '/layout/header.php';
?>

<style>
/* Invoice-specific styles — print-friendly */
.invoice-wrapper {
    max-width: 740px;
    margin: 0 auto;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 40px 44px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.07);
}
.invoice-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 32px;
    border-bottom: 2px solid #1e3a5f;
    padding-bottom: 20px;
}
.invoice-brand { font-size: 1.5rem; font-weight: 700; color: #1e3a5f; }
.invoice-brand span { color: #2563eb; }
.invoice-meta { text-align: right; font-size: 0.88rem; color: #4b5563; }
.invoice-meta strong { display: block; font-size: 1.05rem; color: #1e3a5f; margin-bottom: 4px; }
.invoice-parties { display: flex; gap: 40px; margin-bottom: 28px; }
.invoice-party h4 { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.06em; color: #2563eb; margin-bottom: 6px; }
.invoice-party p { font-size: 0.88rem; color: #374151; line-height: 1.6; margin: 0; }
.invoice-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 0.9rem; }
.invoice-table th { background: #1e3a5f; color: #fff; padding: 10px 12px; text-align: left; }
.invoice-table td { padding: 10px 12px; border-bottom: 1px solid #e2e8f0; vertical-align: middle; }
.invoice-table tfoot td { font-weight: 700; border-top: 2px solid #1e3a5f; border-bottom: none; }
.invoice-table tfoot .total-label { text-align: right; }
.invoice-status { display: inline-block; background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; border-radius: 20px; padding: 4px 14px; font-size: 0.82rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; }
.invoice-actions { margin-top: 28px; display: flex; gap: 12px; flex-wrap: wrap; }
@media print {
    header, .invoice-actions, .flash-banner { display: none !important; }
    .invoice-wrapper { box-shadow: none; border: none; padding: 0; }
    .page-body { padding: 0; }
}
</style>

<div class="page-body">
    <main>
        <div class="invoice-wrapper">

            <!-- invoice header -->
            <div class="invoice-header">
                <div>
                    <div class="invoice-brand">Shopp<span>n</span></div>
                    <p style="font-size:0.82rem;color:#6b7280;margin-top:4px;">
                        Your trusted e-commerce store
                    </p>
                    <div class="invoice-status" style="margin-top:10px;">
                        <?php echo htmlspecialchars($order['order_status'], ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                </div>
                <div class="invoice-meta">
                    <strong>INVOICE</strong>
                    Invoice #: <?php echo (int)$order['invoice_no']; ?><br>
                    Order ID: #<?php echo (int)$order['order_id']; ?><br>
                    Date: <?php echo htmlspecialchars(date('d M Y', strtotime($order['order_date'])), ENT_QUOTES, 'UTF-8'); ?><br>
                    <?php if (!empty($order['paystack_ref'])): ?>
                        Ref: <?php echo htmlspecialchars($order['paystack_ref'], ENT_QUOTES, 'UTF-8'); ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- customer and payment -->
            <div class="invoice-parties">
                <div class="invoice-party">
                    <h4>Billed To</h4>
                    <p>
                        <strong><?php echo htmlspecialchars($order['customer_name'], ENT_QUOTES, 'UTF-8'); ?></strong><br>
                        <?php echo htmlspecialchars($order['customer_email'], ENT_QUOTES, 'UTF-8'); ?><br>
                        <?php echo htmlspecialchars($order['customer_city'] ?? '', ENT_QUOTES, 'UTF-8'); ?>,
                        <?php echo htmlspecialchars($order['customer_country'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                    </p>
                </div>
                <div class="invoice-party">
                    <h4>Payment Info</h4>
                    <p>
                        Currency: <?php echo htmlspecialchars($order['currency'] ?? 'GHS', ENT_QUOTES, 'UTF-8'); ?><br>
                        Method: Paystack<br>
                        <?php if (!empty($order['payment_date'])): ?>
                            Paid on: <?php echo htmlspecialchars(date('d M Y', strtotime($order['payment_date'])), ENT_QUOTES, 'UTF-8'); ?>
                        <?php endif; ?>
                    </p>
                </div>
            </div>

            <!-- items -->
            <table class="invoice-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th style="text-align:center;">Qty</th>
                        <th style="text-align:right;">Unit Price</th>
                        <th style="text-align:right;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td>
                            <?php if (!empty($item['product_image'])): ?>
                                <img src="<?php echo APP_BASE . 'images/products/' . htmlspecialchars($item['product_image'], ENT_QUOTES, 'UTF-8'); ?>"
                                     alt=""
                                     style="width:36px;height:36px;object-fit:cover;border-radius:4px;vertical-align:middle;margin-right:8px;border:1px solid #e2e8f0;">
                            <?php endif; ?>
                            <?php echo htmlspecialchars($item['product_title'], ENT_QUOTES, 'UTF-8'); ?>
                        </td>
                        <td style="text-align:center;"><?php echo (int)$item['qty']; ?></td>
                        <td style="text-align:right;">GH₵ <?php echo number_format((float)$item['unit_price'], 2); ?></td>
                        <td style="text-align:right;">GH₵ <?php echo number_format((float)$item['line_total'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="total-label" style="text-align:right;padding-right:12px;">Grand Total</td>
                        <td style="text-align:right;">GH₵ <?php echo number_format($grand_total, 2); ?></td>
                    </tr>
                </tfoot>
            </table>

            <!-- thanks -->
            <p style="font-size:0.88rem;color:#4b5563;margin-top:12px;">
                Thank you for shopping with Shoppn.
                <?php if ($email_sent): ?>
                    A confirmation email has been sent to <strong><?php echo htmlspecialchars($order['customer_email'], ENT_QUOTES, 'UTF-8'); ?></strong>.
                <?php endif; ?>
            </p>

            <!-- buttons -->
            <div class="invoice-actions">
                <button onclick="window.print();" class="btn btn-primary">
                    🖨 Print Invoice
                </button>
                <a href="<?php echo APP_BASE; ?>index.php" class="btn btn-secondary">
                    Continue Shopping
                </a>
            </div>

        </div><!-- /.invoice-wrapper -->
    </main>
</div>

<?php require_once __DIR__ . '/layout/footer.php'; ?>
