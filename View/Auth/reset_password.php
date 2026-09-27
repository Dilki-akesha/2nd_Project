<?php
/**
 * Harvestly reset password.
 * Reached only with a one-time, one-hour token issued by an Admin through
 * scripts/issue_password_reset.php after identity verification.
 */
$error = isset($_GET['error']) ? sanitize($_GET['error']) : null;
$success = isset($_GET['success']) ? sanitize($_GET['success']) : null;
$token = trim((string)($_GET['token'] ?? ''));
?>
<div class="page-content" style="max-width:480px;margin:90px auto;padding:0 20px">
    <div class="card" style="padding:36px">
        <div style="text-align:center;margin-bottom:24px">
            <img src="<?= e(url('assets/harvestly-logo.jpeg')) ?>" alt="Harvestly" style="height:56px;width:auto;margin:0 auto 12px;display:block;object-fit:contain">
            <h1 style="font-size:24px;font-weight:800;margin:0">Reset Your Password</h1>
            <p style="font-size:14px;color:var(--color-on-surface-variant);margin:6px 0 0">
                Enter a new password for your Harvestly account.
            </p>
        </div>

        <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>

        <?php if ($token === ''): ?>
        <div class="alert alert-danger">
            This page needs a one-time reset link. Please request recovery instructions from the
            <a href="index.php?page=forgot_password" style="color:var(--color-primary);font-weight:700">Forgot Password</a> page.
        </div>
        <div style="text-align:center;margin-top:8px">
            <a href="index.php?page=login" style="color:var(--color-primary);font-weight:700">Back to Sign In</a>
        </div>
        <?php else: ?>
        <form action="index.php?action=reset_password" method="POST" id="resetPasswordForm" novalidate>
            <?= csrfField() ?>
            <input type="hidden" name="token" value="<?= sanitize($token) ?>">

            <div class="form-group">
                <label class="form-label" for="new-password">New Password</label>
                <input type="password" id="new-password" name="new_password" class="form-control"
                       minlength="8" autocomplete="new-password" required>
                <small class="muted">At least 8 characters.</small>
            </div>

            <div class="form-group">
                <label class="form-label" for="confirm-password">Confirm New Password</label>
                <input type="password" id="confirm-password" name="confirm_password" class="form-control"
                       minlength="8" autocomplete="new-password" required>
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%;margin-top:10px;padding:12px">
                Reset Password
            </button>
        </form>

        <div style="text-align:center;margin-top:20px;font-size:14px">
            <a href="index.php?page=login" style="color:var(--color-primary);font-weight:700">Back to Sign In</a>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($token !== ''): ?>
<script>
(function () {
    var form = document.getElementById('resetPasswordForm');
    if (!form) return;
    form.addEventListener('submit', function (event) {
        var pass = document.getElementById('new-password').value;
        var confirmPass = document.getElementById('confirm-password').value;
        if (pass.length < 8) {
            event.preventDefault();
            alert('Your new password must be at least 8 characters long.');
            return;
        }
        if (pass !== confirmPass) {
            event.preventDefault();
            alert('The two passwords do not match.');
        }
    });
})();
</script>
<?php endif; ?>
