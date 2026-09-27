<?php
/**
 * Harvestly shared footer. Public pages only - the Admin console has its own
 * sidebar and does not render this.
 */
$currentPage = (string)($_GET['page'] ?? 'landing');
$isAdminPage = strpos($currentPage, 'admin_') === 0;
?>

<?php if (!$isAdminPage): ?>
<footer class="public-footer no-print" style="background-color:var(--color-surface-container-high);padding:64px 24px 32px;border-top:1px solid var(--color-outline-variant);margin-top:auto">
    <div style="max-width:1280px;margin:0 auto;display:flex;flex-direction:column;gap:40px">
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:32px">
            <div style="display:flex;flex-direction:column;gap:14px">
                <a href="index.php" class="logo-brand" style="text-decoration:none">
                    <img src="<?= e(url('assets/harvestly-logo.jpeg')) ?>" alt="Harvestly" style="height:40px;width:auto;border-radius:var(--radius-md);object-fit:contain">
                    <span class="logo-title">Harvestly</span>
                </a>
                <p style="font-size:14px;color:var(--color-on-surface-variant);line-height:1.6;margin:0">
                    A farmer-to-buyer marketplace connecting Sri Lankan growers, Buyers and approved
                    Courier Partner delivery organisations.
                </p>
            </div>

            <div style="display:flex;flex-direction:column;gap:12px">
                <strong style="font-size:15px;color:var(--color-on-surface)">Quick Navigation</strong>
                <a href="index.php" style="font-size:14px;color:var(--color-on-surface-variant);text-decoration:none">Home</a>
                <a href="index.php?page=products" style="font-size:14px;color:var(--color-on-surface-variant);text-decoration:none">Browse Products</a>
                <a href="index.php#how-it-works" style="font-size:14px;color:var(--color-on-surface-variant);text-decoration:none">How It Works</a>
                <a href="index.php#about-us" style="font-size:14px;color:var(--color-on-surface-variant);text-decoration:none">About Us</a>
            </div>

            <div style="display:flex;flex-direction:column;gap:12px">
                <strong style="font-size:15px;color:var(--color-on-surface)">Register</strong>
                <a href="index.php?page=role_select" style="font-size:14px;color:var(--color-on-surface-variant);text-decoration:none">Choose a Role</a>
                <a href="index.php?page=signup_buyer" style="font-size:14px;color:var(--color-on-surface-variant);text-decoration:none">Buyer Registration</a>
                <a href="index.php?page=signup_farmer" style="font-size:14px;color:var(--color-on-surface-variant);text-decoration:none">Farmer Registration</a>
                <a href="index.php?page=signup_courier" style="font-size:14px;color:var(--color-on-surface-variant);text-decoration:none">Courier Partner Registration</a>
            </div>

            <div style="display:flex;flex-direction:column;gap:12px">
                <strong style="font-size:15px;color:var(--color-on-surface)">Account</strong>
                <a href="index.php?page=login" style="font-size:14px;color:var(--color-on-surface-variant);text-decoration:none">Sign In</a>
                <a href="index.php?page=forgot_password" style="font-size:14px;color:var(--color-on-surface-variant);text-decoration:none">Forgot Password</a>
                <a href="index.php?page=reset_password" style="font-size:14px;color:var(--color-on-surface-variant);text-decoration:none">Reset Password</a>
            </div>
        </div>

        <div style="padding-top:24px;border-top:1px solid var(--color-outline-variant);display:flex;flex-wrap:wrap;justify-content:space-between;gap:16px;font-size:13px;color:var(--color-outline)">
            <p style="margin:0">&copy; <?= date('Y') ?> Harvestly. Farmer-to-Buyer Marketplace.</p>
            <span>Sri Lanka</span>
        </div>
    </div>
</footer>
<?php endif; ?>

<script src="<?= e(url('JS/Common/main.js')) ?>"></script>
</body>
</html>
