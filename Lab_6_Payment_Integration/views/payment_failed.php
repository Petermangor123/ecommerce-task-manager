<?php
// payment_failed.php — shown when Paystack verification fails or the user cancels
if (!defined('APP_BASE')) {
    require_once __DIR__ . '/../core/core.php';
}

require_once __DIR__ . '/layout/header.php';
?>
<div class="page-body">
    <main>
        <div class="status-panel">

            <!-- Failure icon -->
            <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24"
                 fill="none" stroke="#dc2626" stroke-width="1.8"
                 stroke-linecap="round" stroke-linejoin="round"
                 class="status-panel__icon" aria-hidden="true">
                <circle cx="12" cy="12" r="10"/>
                <line x1="15" y1="9" x2="9" y2="15"/>
                <line x1="9" y1="9" x2="15" y2="15"/>
            </svg>

            <h2 class="status-panel__title status-panel__title--fail">Payment Failed</h2>

            <?php if (!empty($_SESSION['error'])): ?>
                <p class="status-panel__text">
                    <?php echo htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8');
                          unset($_SESSION['error']); ?>
                </p>
            <?php else: ?>
                <p class="status-panel__text">
                    Your payment could not be completed. No charge has been made and your cart
                    is still intact.
                </p>
            <?php endif; ?>

            <p class="status-panel__note">
                If you were charged but see this page, please contact support and quote your
                Paystack transaction reference.
            </p>

            <div class="cart-actions status-panel__actions">
                <a href="<?php echo APP_BASE; ?>views/payment.php" class="btn btn-primary">
                    Try Again
                </a>
                <a href="<?php echo APP_BASE; ?>index.php?cart=1" class="btn btn-secondary">
                    Return to Cart
                </a>
                <a href="<?php echo APP_BASE; ?>index.php" class="btn btn-secondary">
                    Continue Shopping
                </a>
            </div>

        </div>
    </main>
</div>
<?php require_once __DIR__ . '/layout/footer.php'; ?>
