<?php
$message = isset($_GET['message'])
    ? sanitize($_GET['message'])
    : 'Your registration application has been received and is currently under review.';
?>
<div class="page-content" style="max-width:620px;margin:80px auto;padding:0 20px">
    <div class="card" style="padding:44px 36px;text-align:center">
        <div style="width:72px;height:72px;border-radius:50%;background:#fff6e3;color:#8a5f11;display:flex;align-items:center;justify-content:center;margin:0 auto 20px">
            <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
        </div>

        <h1 style="font-size:26px;font-weight:800;margin:0 0 12px">Application Under Review</h1>

        <p style="font-size:15px;color:var(--color-on-surface-variant);line-height:1.6;margin:0 0 24px">
            <?= $message ?>
        </p>

        <div class="notice" style="text-align:left">
            You can sign in, but the dashboard stays locked until an Admin reviews your application.
        </div>

        <a href="index.php?page=login" class="btn btn-primary" style="padding:12px 32px;margin-top:26px">
            Return to Sign In
        </a>
    </div>
</div>
