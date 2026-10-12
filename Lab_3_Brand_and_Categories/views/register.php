<?php
require_once '../core/core.php';
require_once 'layout/header.php';
?>
<div class="page-body">
<main>
    <h2>Customer Registration</h2>
    <?php if (isset($_SESSION['error'])): ?>
        <?php // Display any server-side validation error, then clear it ?>
        <p class="error-msg"><?php echo htmlspecialchars($_SESSION['error']); ?></p>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php // Inline error spans (name-error, email-error, etc.) are populated by validate.js ?>
    <form id="register-form" action="../actions/register_action.php" method="POST">
        <div>
            <label for="name">Full Name:</label>
            <input type="text" id="name" name="name" required>
            <span id="name-error"></span>
        </div>
        <div>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
            <span id="email-error"></span>
        </div>
        <div>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
            <span id="password-error"></span>
        </div>
        <div>
            <label for="confirm_password">Confirm Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
            <span id="confirm-password-error"></span>
        </div>
        <div>
            <label for="country">Country:</label>
            <select id="country" name="country" required>
                <option value="">Select Country</option>
                <option value="South Sudan">South Sudan</option>
                <option value="Ghana">Ghana</option>
                <option value="Uganda">Uganda</option>
                <option value="Kenya">Kenya</option>
            </select>
        </div>
        <div>
            <label for="city">City:</label>
            <input type="text" id="city" name="city" required>
        </div>
        <div>
            <label for="contact">Contact Number:</label>
            <input type="text" id="contact" name="contact" required>
            <span id="contact-error"></span>
        </div>
        <div>
            <label for="address">Address:</label>
            <input type="text" id="address" name="address">
        </div>
        <div>
            <label for="customer_image">Profile Image (Optional):</label>
            <input type="file" id="customer_image" name="customer_image">
        </div>
        <button type="submit">Register</button>
    </form>
</main>
</div>
<?php // Client-side validation runs on form submit ?>
<script src="../js/validate.js"></script>
<?php
require_once 'layout/footer.php';
?>
