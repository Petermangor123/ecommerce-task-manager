<?php
require_once '../core/core.php';
require_once 'layout/header.php';
?>
<div class="page-body">
<main>
    <h2>Customer Login</h2>
    <?php if (isset($_SESSION['error'])): ?>
        <?php 
// Display any error passed back from login_action.php, then clear it ?>
        <p style="color:red;"><?php echo htmlspecialchars($_SESSION['error']); ?></p>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    <form id="login-form" action="../actions/login_action.php" method="POST">
        <div>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit">Login</button>
    </form>
    <p>Don't have an account? <a href="register.php">Register here</a></p>
</main>
</div>
<?php // Client-side validation runs on form submit ?>
<script src="../js/validate.js"></script>
<?php
require_once 'layout/footer.php';
?>
