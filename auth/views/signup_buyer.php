<?php
$error = isset($_GET['error']) ? sanitize($_GET['error']) : null;
?>
<div class="page-content" style="max-width: 560px; margin: 60px auto; padding: 0 20px;">
    <div style="background-color: var(--color-surface); border: 1px solid var(--color-outline-variant); border-radius: var(--radius-xl); padding: 36px; box-shadow: var(--shadow-md);">
        <div style="margin-bottom: 24px;">
            <a href="index.php?page=role_select" style="font-size: 13px; color: var(--color-outline); font-weight: 600;">&larr; Change Role</a>
            <h1 style="font-size: 24px; font-weight: 800; color: var(--color-on-surface); margin-top: 8px;">Buyer Registration</h1>
            <p style="font-size: 14px; color: var(--color-on-surface-variant);">Create a buyer account for direct doorstep produce orders</p>
        </div>

        <?php if ($error): ?>
            <div style="background-color: var(--color-error-container); color: var(--color-error); padding: 12px 16px; border-radius: var(--radius-md); font-size: 13px; font-weight: 600; margin-bottom: 20px;">
                ⚠️ <?= $error; ?>
            </div>
        <?php endif; ?>

        <form action="index.php?action=signup_buyer" method="POST">
            <div class="form-group">
                <label class="form-label" for="b-name">Full Name</label>
                <input type="text" id="b-name" name="full_name" class="form-control" placeholder="e.g. Kasun Jayasinghe" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="b-email">Email Address</label>
                <input type="email" id="b-email" name="email" class="form-control" placeholder="kasun@gmail.com" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="b-pass">Password</label>
                <input type="password" id="b-pass" name="password" class="form-control" placeholder="••••••••" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="b-phone">Phone Number</label>
                <input type="text" id="b-phone" name="phone" class="form-control" placeholder="+94 77 123 4567" required>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label" for="b-prov">Province</label>
                    <select id="b-prov" name="province" class="form-control" required>
                        <option value="Western">Western Province</option>
                        <option value="Central">Central Province</option>
                        <option value="Southern">Southern Province</option>
                        <option value="Northern">Northern Province</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="b-dist">District</label>
                    <select id="b-dist" name="district" class="form-control" required>
                        <option value="Colombo">Colombo</option>
                        <option value="Gampaha">Gampaha</option>
                        <option value="Kandy">Kandy</option>
                        <option value="Nuwara Eliya">Nuwara Eliya</option>
                        <option value="Jaffna">Jaffna</option>
                        <option value="Galle">Galle</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="b-address">Doorstep Delivery Address</label>
                <textarea id="b-address" name="address" class="form-control" rows="2" placeholder="Full street address..." required></textarea>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 12px; padding: 12px;">
                Complete Registration & Activate
            </button>
        </form>
    </div>
</div>
