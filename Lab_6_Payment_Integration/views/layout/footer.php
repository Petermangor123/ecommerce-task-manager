<footer class="site-footer">
    <div class="footer-grid">
        <div>
            <p class="footer-brand">Shoppn</p>
            <p>A student-built online store. Browse the catalogue, keep a cart, and pay in Ghana cedis.</p>
        </div>
        <div>
            <p class="footer-heading">Shop</p>
            <a href="<?php echo APP_BASE; ?>index.php">Home</a>
            <a href="<?php echo APP_BASE; ?>index.php?cart=1">Shopping cart</a>
        </div>
        <div>
            <p class="footer-heading">Account</p>
            <?php if (is_logged_in()): ?>
                <a href="<?php echo APP_BASE; ?>views/account/my_account.php">My account</a>
                <a href="<?php echo APP_BASE; ?>logout.php">Logout</a>
            <?php else: ?>
                <a href="<?php echo APP_BASE; ?>views/login.php">Login</a>
                <a href="<?php echo APP_BASE; ?>views/register.php">Create an account</a>
            <?php endif; ?>
        </div>
        <div>
            <p class="footer-heading">Checkout</p>
            <p>Prices are shown in GH₵. Card and mobile-money payments are processed by Paystack.</p>
        </div>
    </div>
    <p class="footer-copy">&copy; 2026 Shoppn</p>
</footer>

</body>
</html>
