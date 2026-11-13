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
            // same csrf token for qty updates and remove.
            // qty inputs point at #update-form. remove is its own form so they don't nest.
            $token = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');
            $remove_action = APP_BASE . 'actions/remove_from_cart_action.php';
            $update_action = APP_BASE . 'actions/update_qty_action.php';
            ?>

            <form action="<?php echo $update_action; ?>" method="POST" id="update-form">
                <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">
            </form>

            <div class="cart-wrap">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Unit Price</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item):
                            $pid      = (int)$item['p_id'];
                            $qty      = (int)$item['qty'];
                            $price    = (float)$item['product_price'];
                            $subtotal = $qty * $price;
                            $title    = htmlspecialchars($item['product_title'], ENT_QUOTES, 'UTF-8');
                        ?>
                        <tr>
                            <td class="cart-product">
                                <?php if (!empty($item['product_image'])): ?>
                                    <img src="<?php echo APP_BASE . 'images/products/' . htmlspecialchars($item['product_image'], ENT_QUOTES, 'UTF-8'); ?>"
                                         alt="<?php echo $title; ?>"
                                         class="cart-table__img">
                                <?php else: ?>
                                    <div class="cart-table__no-img">No Image</div>
                                <?php endif; ?>
                                <a href="<?php echo APP_BASE; ?>index.php?pro_id=<?php echo $pid; ?>">
                                    <?php echo $title; ?>
                                </a>
                            </td>
                            <td>GH₵ <?php echo number_format($price, 2); ?></td>
                            <td>
                                <input type="hidden" name="update[]" value="<?php echo $pid; ?>" form="update-form">
                                <input type="number"
                                       name="qty[]"
                                       form="update-form"
                                       value="<?php echo $qty; ?>"
                                       min="1"
                                       step="1"
                                       required
                                       class="cart-qty"
                                       aria-label="Quantity for <?php echo $title; ?>">
                            </td>
                            <td>GH₵ <?php echo number_format($subtotal, 2); ?></td>
                            <td>
                                <form action="<?php echo $remove_action; ?>" method="POST">
                                    <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">
                                    <input type="hidden" name="remove[]" value="<?php echo $pid; ?>">
                                    <button type="submit" class="btn btn-secondary">Remove</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3" class="cart-table__total-label">Cart total</th>
                            <th colspan="2">GH₵ <?php echo number_format($grand_total, 2); ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="cart-actions">
                <button type="submit" class="btn btn-primary" form="update-form">Update quantities</button>
                <a href="<?php echo APP_BASE; ?>index.php" class="btn btn-secondary">Continue Shopping</a>
                <a href="<?php echo APP_BASE; ?>views/payment.php" class="btn btn-primary">Proceed to Checkout</a>
            </div>

        <?php endif; ?>
    </main>
</div>
<?php require_once __DIR__ . '/layout/footer.php'; ?>
