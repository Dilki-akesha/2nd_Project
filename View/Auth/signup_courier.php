<?php
$error = isset($_GET['error']) ? sanitize($_GET['error']) : null;
$districts = db_fetch_all(
    "SELECT district_id, district_name FROM districts WHERE is_active = 1 ORDER BY district_name"
);
?>
<div class="page-content" style="max-width:580px;margin:50px auto;padding:0 20px">
    <div class="card" style="padding:36px">
        <div style="margin-bottom:22px">
            <a href="index.php?page=role_select" style="font-size:13px;color:var(--color-outline);font-weight:600">&larr; Change Role</a>
            <h1 style="font-size:24px;font-weight:800;margin:8px 0 4px">Courier Partner Registration</h1>
            <p style="font-size:14px;color:var(--color-on-surface-variant);margin:0">
                Register your organisation to provide district-to-district delivery on Harvestly.
            </p>
        </div>

        <div class="alert alert-success" style="font-size:13px">
            <strong>Courier Partner = organisation or company.</strong>
            Harvestly has no individual driver accounts, vehicles, fleets or live GPS tracking. You
            register your company, manage the pickup-district to destination-district routes you serve,
            and accept or reject delivery assignments.
        </div>

        <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <form action="index.php?action=signup_courier" method="POST" enctype="multipart/form-data" id="courierSignupForm" novalidate>
            <?= csrfField() ?>

            <div class="form-group">
                <label class="form-label" for="c-comp">Organisation / Company Name *</label>
                <input type="text" id="c-comp" name="company_name" class="form-control" maxlength="160" required>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                <div class="form-group">
                    <label class="form-label" for="c-contact">Contact Person Name *</label>
                    <input type="text" id="c-contact" name="contact_person" class="form-control" maxlength="120" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="c-phone">Contact Number *</label>
                    <input type="tel" id="c-phone" name="phone" class="form-control" placeholder="0112345678" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="c-email">Organisation Email Address *</label>
                <input type="email" id="c-email" name="email" class="form-control" autocomplete="email" required>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                <div class="form-group">
                    <label class="form-label" for="c-pass">Password *</label>
                    <input type="password" id="c-pass" name="password" class="form-control"
                           minlength="8" autocomplete="new-password" required>
                    <small class="muted">At least 8 characters.</small>
                </div>
                <div class="form-group">
                    <label class="form-label" for="c-confirm-pass">Confirm Password *</label>
                    <input type="password" id="c-confirm-pass" name="confirm_password" class="form-control"
                           minlength="8" autocomplete="new-password" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="c-district">Primary Operations District *</label>
                <select id="c-district" name="district" class="form-control" required>
                    <option value="">Select a district...</option>
                    <?php foreach ($districts as $d): ?>
                    <option value="<?= e($d['district_name']) ?>"><?= e($d['district_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="c-address">Office Address *</label>
                <textarea id="c-address" name="business_address" class="form-control" rows="2" required></textarea>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                <div class="form-group">
                    <label class="form-label" for="c-city">Office City / Town (optional)</label>
                    <input type="text" id="c-city" name="office_city" class="form-control" maxlength="100">
                </div>
                <div class="form-group">
                    <label class="form-label" for="c-postal">Office Postal Code (optional)</label>
                    <input type="text" id="c-postal" name="office_postal" class="form-control" maxlength="20">
                </div>
            </div>

            <div class="form-group" style="background:var(--color-surface-container-low);padding:16px;border-radius:var(--radius-md);border:1px dashed var(--color-outline-variant)">
                <label class="form-label" for="c-cert">Business Verification Document (optional)</label>
                <input type="file" id="c-cert" name="verification_document" accept=".jpg,.jpeg,.png,.pdf">
                <small class="muted">
                    JPG, PNG or PDF; 5 MB maximum. Uploaded documents are only accessible to Harvestly
                    Admins and are never served as public files.
                </small>
            </div>

            <div class="alert alert-success" style="font-size:13px">
                Your account is created as <strong>Pending</strong>. A Harvestly Admin must approve it
                before the Courier Partner dashboard can be opened.
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%;margin-top:12px;padding:12px">
                Submit Courier Partner Application
            </button>
        </form>

        <div style="text-align:center;margin-top:22px;font-size:14px">
            Already registered? <a href="index.php?page=login" style="color:var(--color-primary);font-weight:700">Sign in</a>
        </div>
    </div>
</div>

<script>
(function () {
    var form = document.getElementById('courierSignupForm');
    if (!form) return;
    form.addEventListener('submit', function (event) {
        var pass = document.getElementById('c-pass').value;
        var confirmPass = document.getElementById('c-confirm-pass').value;
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
