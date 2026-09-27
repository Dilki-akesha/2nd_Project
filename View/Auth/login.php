<?php
$error = isset($_GET['error']) ? sanitize($_GET['error']) : null;
$success = isset($_GET['success']) ? sanitize($_GET['success']) : null;
?>
<div class="page-content" style="max-width:480px;margin:90px auto;padding:0 20px">
    <div class="card" style="padding:36px">

        <div style="text-align:center;margin-bottom:26px">
            <img src="<?= e(url('assets/harvestly-logo.jpeg')) ?>" alt="Harvestly" style="height:58px;width:auto;margin:0 auto 12px;display:block;object-fit:contain">
            <h1 style="font-size:24px;font-weight:800;margin:0">Welcome to Harvestly</h1>
            <p style="font-size:14px;color:var(--color-on-surface-variant);margin:6px 0 0">
                Sign in to your Buyer, Farmer, Courier Partner or Admin account
            </p>
        </div>

        <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>

        <form action="index.php?action=login" method="POST">
            <?= csrfField() ?>
            <div class="form-group">
                <label class="form-label" for="login-email">Email Address</label>
                <input type="email" id="login-email" name="email" class="form-control"
                       placeholder="name@domain.com" autocomplete="email" required>
            </div>

            <div class="form-group">
                <div style="display:flex;justify-content:space-between;align-items:center;gap:10px">
                    <label class="form-label" for="login-password">Password</label>
                    <a href="index.php?page=forgot_password" style="font-size:12px;color:var(--color-primary);font-weight:600">
                        Forgot Password?
                    </a>
                </div>
                <input type="password" id="login-password" name="password" class="form-control"
                       placeholder="Your password" autocomplete="current-password" required>
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%;margin-top:10px;padding:12px">
                Sign In
            </button>
        </form>

        <div style="text-align:center;margin-top:22px;font-size:14px">
            Don't have an account? <a href="index.php?page=role_select" style="color:var(--color-primary);font-weight:700">Create one</a>
        </div>
    </div>
</div>
