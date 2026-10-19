<?php
require_once '../../core/core.php';

// Redirect to login if the visitor is not authenticated
require_login();

require_once '../layout/header.php';
?>

<div class="page-body">
<main>
    <h2>My Account</h2>
    <p>Welcome, <?php echo htmlspecialchars($_SESSION['customer_name']); ?>!</p>
    <p>Email: <?php echo htmlspecialchars($_SESSION['customer_email']); ?></p>
</main>
</div>

<?php
require_once '../layout/footer.php';
?>
