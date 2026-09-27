<?php
/**
 * Harvestly forgot password.
 * Harvestly sends no email. Recovery is completed locally: an Admin verifies
 * the account holder's identity and issues a one-time reset link.
 */
$error = isset($_GET['error']) ? sanitize($_GET['error']) : null;
$success = isset($_GET['success']) ? sanitize($_GET['success']) : null;
?>
<div class="page-content" style="max-width:480px;margin:90px auto;padding:0 20px">
    <div class="card" style="padding:36px">
        <div style="text-align:center;margin-bottom:24px">
            <img src="<?= e(url('assets/harvestly-logo.jpeg')) ?>" alt="Harvestly" style="height:56px;width:auto;margin:0 auto 12px;display:block;object-fit:contain">
            <h1 style="font-size:24px;font-weight:800;margin:0">Forgot Password?</h1>
            <p style="font-size:14px;color:var(--color-on-surface-variant);margin:6px 0 0">
                Enter your registered email address to start local account recovery.
            </p>
        </div>

        <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>

        <form action="index.php?action=request_password_reset" method="POST">
            <?= csrfField() ?>
            <div class="form-group">
                <label class="form-label" for="reset-email">Email Address</label>
                <input type="email" id="reset-email" name="email" class="form-control" autocomplete="email" required>
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%;margin-top:10px;padding:12px">
                Request Recovery Instructions
            </button>
        </form>

        <div class="notice" style="margin-top:22px;font-size:13px">
            <strong>How Harvestly password recovery works</strong><br>
            After you submit your email address, contact the Harvestly administrator to verify your
            identity. They issue a one-time reset link that is valid for one hour, which opens the
            <a href="index.php?page=reset_password" style="color:var(--color-primary);font-weight:600">Reset Password</a>
            page.
        </div>

        <div style="text-align:center;margin-top:20px;font-size:14px">
            Remember your password? <a href="index.php?page=login" style="color:var(--color-primary);font-weight:700">Back to Sign In</a>
        </div>
    </div>
</div>
