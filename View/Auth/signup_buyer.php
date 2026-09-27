<?php
$error = isset($_GET['error']) ? sanitize($_GET['error']) : null;
/* All 25 districts come from the database, never a hard-coded list. */
$districts = db_fetch_all(
    "SELECT district_id, district_name FROM districts WHERE is_active = 1 ORDER BY district_name"
);
?>
<div class="page-content" style="max-width:560px;margin:60px auto;padding:0 20px">
    <div class="card" style="padding:36px">
        <div style="margin-bottom:22px">
            <a href="index.php?page=role_select" style="font-size:13px;color:var(--color-outline);font-weight:600">&larr; Change Role</a>
            <h1 style="font-size:24px;font-weight:800;margin:8px 0 4px">Buyer Registration</h1>
            <p style="font-size:14px;color:var(--color-on-surface-variant);margin:0">
                Create a Buyer account to order produce directly from Farmers.
            </p>
        </div>

        <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <form action="index.php?action=signup_buyer" method="POST" id="buyerSignupForm" novalidate>
            <?= csrfField() ?>

            <div class="form-group">
                <label class="form-label" for="b-name">Full Name *</label>
                <input type="text" id="b-name" name="full_name" class="form-control" maxlength="120" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="b-email">Email Address *</label>
                <input type="email" id="b-email" name="email" class="form-control" autocomplete="email" required>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                <div class="form-group">
                    <label class="form-label" for="b-pass">Password *</label>
                    <input type="password" id="b-pass" name="password" class="form-control"
                           minlength="8" autocomplete="new-password" required>
                    <small class="muted">At least 8 characters.</small>
                </div>
                <div class="form-group">
                    <label class="form-label" for="b-confirm-pass">Confirm Password *</label>
                    <input type="password" id="b-confirm-pass" name="confirm_password" class="form-control"
                           minlength="8" autocomplete="new-password" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="b-phone">Phone Number *</label>
                <input type="tel" id="b-phone" name="phone" class="form-control" placeholder="0771234567" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="b-dist">District *</label>
                <select id="b-dist" name="district" class="form-control" required>
                    <option value="">Select a district...</option>
                    <?php foreach ($districts as $d): ?>
                    <option value="<?= e($d['district_name']) ?>"><?= e($d['district_name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <small class="muted">Your default delivery district.</small>
            </div>

            <div class="form-group">
                <label class="form-label" for="b-address">Delivery Address *</label>
                <textarea id="b-address" name="address" class="form-control" rows="2" required></textarea>
            </div>

            <div class="alert alert-success" style="font-size:13px">
                A Buyer account is activated immediately after successful registration.
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%;margin-top:12px;padding:12px">
                Complete Registration
            </button>
        </form>

        <div style="text-align:center;margin-top:22px;font-size:14px">
            Already have an account? <a href="index.php?page=login" style="color:var(--color-primary);font-weight:700">Sign in</a>
        </div>
    </div>
</div>

<script>
(function () {
    var form = document.getElementById('buyerSignupForm');
    if (!form) return;
    form.addEventListener('submit', function (event) {
        var pass = document.getElementById('b-pass').value;
        var confirmPass = document.getElementById('b-confirm-pass').value;
        if (pass.length < 8) {
            event.preventDefault();
            alert('Your password must be at least 8 characters long.');
            return;
        }
        if (pass !== confirmPass) {
            event.preventDefault();
            alert('The two passwords do not match.');
        }
    });
})();
</script>
