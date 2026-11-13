<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shoppn</title>
    <link rel="stylesheet" href="<?php echo APP_BASE; ?>css/style.css">
</head>

<body>

<?php
require_once __DIR__ . '/../../controllers/CartController.php';
require_once __DIR__ . '/../../controllers/ProductController.php';

if (!isset($cartController) || !($cartController instanceof CartController)) {
    $cartController = new CartController();
}
$header_ip         = get_ip();
$header_cart_count = $cartController->getCartCount($header_ip);
$header_cart_total = $cartController->getCartTotal($header_ip);

if (!isset($controller) || !($controller instanceof ProductController)) {
    $navController = new ProductController();
} else {
    $navController = $controller;
}
$nav_categories = $navController->getAllCategories();
$nav_cat_id     = filter_input(INPUT_GET, 'cat', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$nav_script     = basename($_SERVER['SCRIPT_NAME'] ?? '');
$nav_is_home    = $nav_script === 'index.php'
    && !$nav_cat_id
    && !isset($_GET['brand'], $_GET['pro_id'], $_GET['user_query'], $_GET['cart']);
$search_value   = isset($_GET['user_query']) ? (string)$_GET['user_query'] : '';
?>

<header class="site-header">
    <div class="header-top">
        <a class="logo" href="<?php echo APP_BASE; ?>index.php">
            <span class="logo-mark" aria-hidden="true">S</span>
            <span class="logo-text">Shoppn</span>
        </a>

        <form class="site-search" action="<?php echo APP_BASE; ?>index.php" method="GET">
            <label class="visually-hidden" for="user_query">Search products</label>
            <input type="text" id="user_query" name="user_query" placeholder="Search products"
                   value="<?php echo htmlspecialchars($search_value, ENT_QUOTES, 'UTF-8'); ?>">
            <button type="submit">Search</button>
        </form>

        <nav class="header-nav" aria-label="Account">
            <?php if (is_logged_in()): ?>
                <span class="header-welcome">Hi, <?php echo htmlspecialchars($_SESSION['customer_name'], ENT_QUOTES, 'UTF-8'); ?></span>
                <a href="<?php echo APP_BASE; ?>views/account/my_account.php">Account</a>
                <?php if (is_admin()): ?>
                    <details class="admin-menu">
                        <summary>Admin</summary>
                        <div class="admin-menu__panel">
                            <a href="<?php echo APP_BASE; ?>views/admin/dashboard.php">Dashboard</a>
                            <a href="<?php echo APP_BASE; ?>views/admin/brand.php">Brands</a>
                            <a href="<?php echo APP_BASE; ?>views/admin/category.php">Categories</a>
                            <a href="<?php echo APP_BASE; ?>views/admin/product.php">Products</a>
                        </div>
                    </details>
                <?php endif; ?>
                <a href="<?php echo APP_BASE; ?>logout.php">Logout</a>
            <?php else: ?>
                <a href="<?php echo APP_BASE; ?>views/login.php">Login</a>
                <a href="<?php echo APP_BASE; ?>views/register.php">Register</a>
            <?php endif; ?>

            <a href="<?php echo APP_BASE; ?>index.php?cart=1"
               class="cart-link"
               aria-label="Shopping cart, <?php echo (int)$header_cart_count; ?> items">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                    <circle cx="9" cy="21" r="1"/>
                    <circle cx="20" cy="21" r="1"/>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                </svg>
                <span class="cart-link__count"><?php echo (int)$header_cart_count; ?></span>
                <span class="cart-link__total">GH₵ <?php echo number_format((float)$header_cart_total, 2); ?></span>
            </a>
        </nav>
    </div>

    <nav class="cat-nav" aria-label="Product categories">
        <div class="cat-nav__inner">
            <a href="<?php echo APP_BASE; ?>index.php"<?php echo $nav_is_home ? ' class="is-active"' : ''; ?>>All products</a>
            <?php foreach ($nav_categories as $cat): ?>
                <?php $is_active = $nav_cat_id && (int)$cat['cat_id'] === (int)$nav_cat_id; ?>
                <a href="<?php echo APP_BASE; ?>index.php?cat=<?php echo (int)$cat['cat_id']; ?>"<?php echo $is_active ? ' class="is-active"' : ''; ?>>
                    <?php echo htmlspecialchars($cat['cat_name'], ENT_QUOTES, 'UTF-8'); ?>
                </a>
            <?php endforeach; ?>
        </div>
    </nav>
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
