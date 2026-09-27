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
            <h1 style="font-size:24px;font-weight:800;margin:8px 0 4px">Farmer Registration</h1>
            <p style="font-size:14px;color:var(--color-on-surface-variant);margin:0">
                Apply to sell your produce on Harvestly. Your account is reviewed by an Admin before
                you can access the Farmer dashboard.
            </p>
        </div>

        <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <form action="index.php?action=signup_farmer" method="POST" enctype="multipart/form-data" id="farmerSignupForm" novalidate>
            <?= csrfField() ?>

            <div class="form-group">
                <label class="form-label" for="f-name">Full Name *</label>
                <input type="text" id="f-name" name="full_name" class="form-control" maxlength="120" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="f-email">Email Address *</label>
                <input type="email" id="f-email" name="email" class="form-control" autocomplete="email" required>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                <div class="form-group">
                    <label class="form-label" for="f-pass">Password *</label>
                    <input type="password" id="f-pass" name="password" class="form-control"
                           minlength="8" autocomplete="new-password" required>
                    <small class="muted">At least 8 characters.</small>
                </div>
                <div class="form-group">
                    <label class="form-label" for="f-confirm-pass">Confirm Password *</label>
                    <input type="password" id="f-confirm-pass" name="confirm_password" class="form-control"
                           minlength="8" autocomplete="new-password" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="f-phone">Phone Number *</label>
                <input type="tel" id="f-phone" name="phone" class="form-control" placeholder="0771234567" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="f-farmname">Farm Name (optional)</label>
                <input type="text" id="f-farmname" name="farm_name" class="form-control" maxlength="150">
            </div>

            <div class="form-group">
                <label class="form-label" for="f-district">Pickup District *</label>
                <select id="f-district" name="district" class="form-control" required>
                    <option value="">Select a district...</option>
                    <?php foreach ($districts as $d): ?>
                    <option value="<?= e($d['district_name']) ?>"><?= e($d['district_name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <small class="muted">
                    Your pickup district determines which Buyer destination districts Harvestly can
                    deliver from your farm, and the delivery fee charged to the Buyer.
                </small>
            </div>

            <div class="form-group">
                <label class="form-label" for="f-address">Farm Pickup Address *</label>
                <textarea id="f-address" name="farm_address" class="form-control" rows="2" required></textarea>
            </div>

            <div class="form-group" style="background:var(--color-surface-container-low);padding:16px;border-radius:var(--radius-md);border:1px dashed var(--color-outline-variant)">
                <label class="form-label" for="f-doc">Supporting Verification Document (optional)</label>
                <input type="file" id="f-doc" name="verification_document" accept=".jpg,.jpeg,.png,.pdf">
                <small class="muted">
                    JPG, PNG or PDF; 5 MB maximum. Uploaded documents are only accessible to Harvestly
                    Admins and are never served as public files.
                </small>
            </div>

            <div class="alert alert-success" style="font-size:13px">
                Your account is created as <strong>Pending</strong>. A Harvestly Admin must approve it
                before the Farmer dashboard can be opened.
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%;margin-top:12px;padding:12px">
                Submit Application for Admin Review
            </button>
        </form>

        <div style="text-align:center;margin-top:22px;font-size:14px">
            Already registered? <a href="index.php?page=login" style="color:var(--color-primary);font-weight:700">Sign in</a>
        </div>
    </div>
</div>

<script>
(function () {
    var form = document.getElementById('farmerSignupForm');
    if (!form) return;
    form.addEventListener('submit', function (event) {
        var pass = document.getElementById('f-pass').value;
        var confirmPass = document.getElementById('f-confirm-pass').value;
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
