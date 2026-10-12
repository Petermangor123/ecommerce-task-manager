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
    <h1><a href="<?php echo APP_BASE; ?>index.php">Shoppn</a></h1>

    <nav>
        <a href="<?php echo APP_BASE; ?>index.php">Home</a>

        <?php if (is_logged_in()): ?>
            <!-- Logged-in navigation -->
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['customer_name']); ?></span>
            <a href="<?php echo APP_BASE; ?>views/account/my_account.php">My Account</a>
            <?php if (is_admin()): ?>
                <!-- Admin links are only shown to users with user_role = 1 -->
                <a href="<?php echo APP_BASE; ?>views/admin/dashboard.php">Admin</a>
                <a href="<?php echo APP_BASE; ?>views/admin/brand.php">Brand</a>
                <a href="<?php echo APP_BASE; ?>views/admin/category.php">Category</a>
            <?php endif; ?>
            <a href="<?php echo APP_BASE; ?>logout.php">Logout</a>
        <?php else: ?>
            <!-- Logged-out navigation -->
            <a href="<?php echo APP_BASE; ?>views/register.php">Register</a>
            <a href="<?php echo APP_BASE; ?>views/login.php">Login</a>
        <?php endif; ?>
    </nav>

    <form action="<?php echo APP_BASE; ?>views/search_results.php" method="GET">
        <input type="text" name="user_query" placeholder="Search products">
        <button type="submit">Search</button>
    </form>
</header>
