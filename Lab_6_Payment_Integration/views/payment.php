<?php
// payment.php — checkout page with Paystack Inline JS
// core.php is loaded by callers that use APP_BASE, but this view may also
// be opened directly from the cart so we ensure core is loaded.
if (!defined('APP_BASE')) {
    require_once __DIR__ . '/../core/core.php';
}
require_once __DIR__ . '/../core/paystack_config.php';
require_once __DIR__ . '/../controllers/CartController.php';

// Must be logged in to check out
require_login();

$ip             = get_ip();
$cartController = new CartController();
$items          = $cartController->getCartItems($ip);

// Redirect back to cart if it is empty
if (empty($items)) {
    $_SESSION['error'] = 'Your cart is empty. Please add items before checking out.';
    redirect(APP_BASE . 'index.php?cart=1');
}

// Calculate grand total in GHS
$grand_total = 0.0;
foreach ($items as $item) {
    $grand_total += (int)$item['qty'] * (float)$item['product_price'];
}

// Store expected amount in session so process_payment_action.php can verify it
$_SESSION['expected_amount'] = $grand_total;

// Amount in pesewas for Paystack (multiply GHS by 100, cast to int)
$amount_pesewas = (int)round($grand_total * 100);

$customer_email = htmlspecialchars($_SESSION['customer_email'] ?? '', ENT_QUOTES, 'UTF-8');
$customer_name  = htmlspecialchars($_SESSION['customer_name']  ?? '', ENT_QUOTES, 'UTF-8');

require_once __DIR__ . '/layout/header.php';
?>
<div class="page-body">
    <main>
        <h2>Checkout</h2>
        <p class="page-lead">Review the order, then pay with Paystack. The amount is checked again on the server after payment.</p>

        <div class="checkout-layout">
            <section>
                <h3>Order summary</h3>
                <div class="cart-wrap">
                    <table class="cart-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Qty</th>
                                <th>Unit Price</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item):
                                $qty      = (int)$item['qty'];
                                $price    = (float)$item['product_price'];
                                $subtotal = $qty * $price;
                            ?>
                            <tr>
                                <td class="cart-product">
                                    <?php if (!empty($item['product_image'])): ?>
                                        <img src="<?php echo APP_BASE . 'images/products/' . htmlspecialchars($item['product_image'], ENT_QUOTES, 'UTF-8'); ?>"
                                             alt="<?php echo htmlspecialchars($item['product_title'], ENT_QUOTES, 'UTF-8'); ?>"
                                             class="cart-table__img">
                                    <?php endif; ?>
                                    <?php echo htmlspecialchars($item['product_title'], ENT_QUOTES, 'UTF-8'); ?>
                                </td>
                                <td><?php echo $qty; ?></td>
                                <td>GH₵ <?php echo number_format($price, 2); ?></td>
                                <td>GH₵ <?php echo number_format($subtotal, 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="cart-table__total-label">Grand Total</th>
                                <th>GH₵ <?php echo number_format($grand_total, 2); ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </section>

            <section class="pay-box">
                <h3>Pay with Paystack</h3>
                <p>Paying as <strong><?php echo $customer_name; ?></strong></p>
                <p class="pay-box__email"><?php echo $customer_email; ?></p>
                <p class="pay-box__amount">GH₵ <?php echo number_format($grand_total, 2); ?></p>
                <button id="paystack-btn" class="btn btn-primary" type="button">
                    Pay GH₵ <?php echo number_format($grand_total, 2); ?> with Paystack
                </button>
                <a href="<?php echo APP_BASE; ?>index.php?cart=1" class="btn btn-secondary">Back to Cart</a>
            </section>
        </div>
    </main>
</div>

<!-- Paystack Inline JS — loaded from Paystack CDN -->
<script src="https://js.paystack.co/v1/inline.js"></script>
<script>
(function () {
    var btn = document.getElementById('paystack-btn');
    if (!btn) { return; }

    btn.addEventListener('click', function () {
        btn.disabled = true;
        btn.textContent = 'Opening payment…';

        var handler = PaystackPop.setup({
            key:       '<?php echo htmlspecialchars(PAYSTACK_PUBLIC_KEY, ENT_QUOTES, 'UTF-8'); ?>',
            email:     '<?php echo $customer_email; ?>',
            amount:    <?php echo $amount_pesewas; ?>,  // in pesewas
            currency:  '<?php echo PAYSTACK_CURRENCY; ?>',
            ref:       'shoppn_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9),

            onClose: function () {
                btn.disabled = false;
                btn.textContent = 'Pay GH₵ <?php echo number_format($grand_total, 2); ?> with Paystack';
            },

            callback: function (response) {
                // Redirect to server-side verification with the reference
                window.location.href =
                    '<?php echo APP_BASE; ?>actions/process_payment_action.php?ref='
                    + encodeURIComponent(response.reference);
            }
        });
        handler.openIframe();
    });
}());
</script>

<?php require_once __DIR__ . '/layout/footer.php'; ?>
