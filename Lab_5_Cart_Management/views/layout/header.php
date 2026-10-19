<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shoppn</title>
    <link rel="stylesheet" href="<?php echo APP_BASE; ?>css/style.css">
</head>

<body>

<header>
    <!-- ── Top row: logo · nav · search ──────────────────────── -->
    <div class="header-top">
        <h1><a href="<?php echo APP_BASE; ?>index.php">Shoppn</a></h1>

        <nav>
            <a href="<?php echo APP_BASE; ?>index.php">Home</a>

            <?php if (is_logged_in()): ?>
                <!-- Logged-in navigation -->
                <span>Welcome, <?php echo htmlspecialchars($_SESSION['customer_name'], ENT_QUOTES, 'UTF-8'); ?></span>
                <a href="<?php echo APP_BASE; ?>views/account/my_account.php">My Account</a>
                <?php if (is_admin()): ?>
                    <!-- Admin links only shown to user_role = 1 -->
                    <a href="<?php echo APP_BASE; ?>views/admin/dashboard.php">Admin</a>
                    <a href="<?php echo APP_BASE; ?>views/admin/brand.php">Brand</a>
                    <a href="<?php echo APP_BASE; ?>views/admin/category.php">Category</a>
                    <a href="<?php echo APP_BASE; ?>views/admin/product.php">Products</a>
                <?php endif; ?>
                <a href="<?php echo APP_BASE; ?>logout.php">Logout</a>
            <?php else: ?>
                <!-- Logged-out navigation -->
                <a href="<?php echo APP_BASE; ?>views/register.php">Register</a>
                <a href="<?php echo APP_BASE; ?>views/login.php">Login</a>
            <?php endif; ?>
        </nav>

        <form action="<?php echo APP_BASE; ?>index.php" method="GET">
            <input type="text" name="user_query" placeholder="Search products"
                   value="<?php echo htmlspecialchars(isset($_GET['user_query']) ? (string)$_GET['user_query'] : '', ENT_QUOTES, 'UTF-8'); ?>">
            <button type="submit">Search</button>
        </form>
    </div><!-- /.header-top -->

    <!-- ── Bottom row: cart summary ──────────────────────────── -->
    <?php
    require_once __DIR__ . '/../../controllers/CartController.php';
    if (!isset($cartController) || !($cartController instanceof CartController)) {
        $cartController = new CartController();
    }
    $header_ip         = get_ip();
    $header_cart_count = $cartController->getCartCount($header_ip);
    $header_cart_total = $cartController->getCartTotal($header_ip);
    ?>
    <div class="cart-summary">
        <div class="cart-summary__inner">
            <!-- Cart icon (inline SVG — no external dependency) -->
            <svg class="cart-summary__icon" xmlns="http://www.w3.org/2000/svg"
                 viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                 aria-hidden="true" focusable="false">
                <circle cx="9"  cy="21" r="1"/>
                <circle cx="20" cy="21" r="1"/>
                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
            </svg>

            <span class="cart-summary__label">Your Cart</span>

            <span class="cart-summary__stat">
                <span class="cart-summary__stat-value"><?php echo (int)$header_cart_count; ?></span>
                <span class="cart-summary__stat-label"><?php echo $header_cart_count === 1 ? 'item' : 'items'; ?></span>
            </span>

            <span class="cart-summary__divider" aria-hidden="true"></span>

            <span class="cart-summary__stat">
                <span class="cart-summary__stat-label">Total</span>
                <span class="cart-summary__stat-value cart-summary__total">
                    $<?php echo number_format((float)$header_cart_total, 2); ?>
                </span>
            </span>

            <a href="<?php echo APP_BASE; ?>index.php?cart=1"
               class="cart-summary__cta"
               aria-label="Go to your shopping cart">
                Go to Cart
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2.5"
                     stroke-linecap="round" stroke-linejoin="round"
                     aria-hidden="true" focusable="false">
                    <polyline points="9 18 15 12 9 6"/>
                </svg>
            </a>
        </div>
    </div>
</header>

<?php if (isset($_SESSION['success'])): ?>
    <p class="success-msg flash-banner">
        <?php echo htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success']); ?>
    </p>
<?php endif; ?>
<?php if (isset($_SESSION['error'])): ?>
    <p class="error-msg flash-banner">
        <?php echo htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error']); ?>
    </p>
<?php endif; ?>
