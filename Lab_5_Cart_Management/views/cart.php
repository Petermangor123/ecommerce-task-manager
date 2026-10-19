<?php
// cart.php — shopping cart page (IP-based, Task 12)
// core.php is already loaded by index.php before this file is included.
require_once __DIR__ . '/../controllers/CartController.php';

$cartController = new CartController();
$ip             = get_ip();
$items          = $cartController->getCartItems($ip);

// Pre-calculate grand total so both forms can display the same value
$grand_total = 0.0;
foreach ($items as $item) {
    $grand_total += (int)$item['qty'] * (float)$item['product_price'];
}

require_once __DIR__ . '/layout/header.php';
?>
<div class="page-body">
    <main>
        <h2>Shopping Cart</h2>

        <?php if (empty($items)): ?>
            <p>Your shopping cart is empty.</p>
            <p style="margin-top:16px;">
                <a href="<?php echo APP_BASE; ?>index.php" class="btn btn-primary">Continue Shopping</a>
            </p>

        <?php else: ?>

            <?php
            // Shared CSRF token — both forms use the same session token.
            $token = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');
            $remove_action = APP_BASE . 'actions/remove_from_cart_action.php';
            $update_action = APP_BASE . 'actions/update_qty_action.php';
            ?>

            <!-- ══════════════════════════════════════════════════════
                 FORM 1 — Remove selected items
                 Submits: csrf_token, remove[] (checked product IDs)
            ══════════════════════════════════════════════════════ -->
            <form action="<?php echo $remove_action; ?>" method="POST" id="remove-form">
                <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">

                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Remove</th>
                            <th>Image</th>
                            <th>Product</th>
                            <th>Unit Price</th>
                            <th>Qty</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item):
                            $pid      = (int)$item['p_id'];
                            $qty      = (int)$item['qty'];
                            $price    = (float)$item['product_price'];
                            $subtotal = $qty * $price;
                        ?>
                        <tr>
                            <td style="text-align:center;">
                                <input type="checkbox"
                                       name="remove[]"
                                       value="<?php echo $pid; ?>"
                                       aria-label="Remove <?php echo htmlspecialchars($item['product_title'], ENT_QUOTES, 'UTF-8'); ?>">
                            </td>
                            <td>
                                <?php if (!empty($item['product_image'])): ?>
                                    <img src="<?php echo APP_BASE . 'images/products/' . htmlspecialchars($item['product_image'], ENT_QUOTES, 'UTF-8'); ?>"
                                         alt="<?php echo htmlspecialchars($item['product_title'], ENT_QUOTES, 'UTF-8'); ?>"
                                         class="cart-table__img">
                                <?php else: ?>
                                    <div class="cart-table__no-img">No Image</div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?php echo APP_BASE; ?>index.php?pro_id=<?php echo $pid; ?>">
                                    <?php echo htmlspecialchars($item['product_title'], ENT_QUOTES, 'UTF-8'); ?>
                                </a>
                            </td>
                            <td>$<?php echo number_format($price, 2); ?></td>
                            <td><?php echo $qty; ?></td>
                            <td>$<?php echo number_format($subtotal, 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="5" class="cart-table__total-label">Grand Total</th>
                            <th>$<?php echo number_format($grand_total, 2); ?></th>
                        </tr>
                    </tfoot>
                </table>

                <div class="cart-actions">
                    <button type="submit" class="btn btn-secondary">Remove Selected</button>
                </div>
            </form>

            <hr style="border:none;border-top:1px solid #e2e8f0;margin:28px 0;">

            <!-- ══════════════════════════════════════════════════════
                 FORM 2 — Update quantities
                 Submits: csrf_token, update[] (product IDs), qty[] (new quantities)
                 update[i] and qty[i] share the same index so the action can pair them.
            ══════════════════════════════════════════════════════ -->
            <form action="<?php echo $update_action; ?>" method="POST" id="update-form">
                <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">

                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Product</th>
                            <th>Unit Price</th>
                            <th>New Qty</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item):
                            $pid      = (int)$item['p_id'];
                            $qty      = (int)$item['qty'];
                            $price    = (float)$item['product_price'];
                            $subtotal = $qty * $price;
                        ?>
                        <tr>
                            <td>
                                <?php if (!empty($item['product_image'])): ?>
                                    <img src="<?php echo APP_BASE . 'images/products/' . htmlspecialchars($item['product_image'], ENT_QUOTES, 'UTF-8'); ?>"
                                         alt="<?php echo htmlspecialchars($item['product_title'], ENT_QUOTES, 'UTF-8'); ?>"
                                         class="cart-table__img">
                                <?php else: ?>
                                    <div class="cart-table__no-img">No Image</div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?php echo APP_BASE; ?>index.php?pro_id=<?php echo $pid; ?>">
                                    <?php echo htmlspecialchars($item['product_title'], ENT_QUOTES, 'UTF-8'); ?>
                                </a>
                            </td>
                            <td>$<?php echo number_format($price, 2); ?></td>
                            <td>
                                <!-- Hidden product ID paired with the quantity input below -->
                                <input type="hidden" name="update[]" value="<?php echo $pid; ?>">
                                <input type="number"
                                       name="qty[]"
                                       value="<?php echo $qty; ?>"
                                       min="1"
                                       step="1"
                                       required
                                       class="cart-qty"
                                       aria-label="Quantity for <?php echo htmlspecialchars($item['product_title'], ENT_QUOTES, 'UTF-8'); ?>">
                            </td>
                            <td>$<?php echo number_format($subtotal, 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="4" class="cart-table__total-label">Grand Total</th>
                            <th>$<?php echo number_format($grand_total, 2); ?></th>
                        </tr>
                    </tfoot>
                </table>

                <div class="cart-actions">
                    <button type="submit" class="btn btn-primary">Update Quantity</button>
                </div>
            </form>

            <!-- ══════════════════════════════════════════════════════
                 Navigation buttons (not inside a form)
            ══════════════════════════════════════════════════════ -->
            <div class="cart-actions" style="margin-top:24px;">
                <a href="<?php echo APP_BASE; ?>index.php" class="btn btn-secondary">Continue Shopping</a>
                <!--
                    Checkout is not yet implemented (views/checkout.php does not exist).
                    The button is disabled until Task 13 implements the checkout flow.
                -->
                <button type="button" class="btn btn-primary" disabled
                        title="Checkout is not yet available.">
                    Checkout (Coming Soon)
                </button>
            </div>

        <?php endif; ?>
    </main>
</div>
<?php require_once __DIR__ . '/layout/footer.php'; ?>
