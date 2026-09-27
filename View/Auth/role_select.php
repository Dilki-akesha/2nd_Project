<?php
/**
 * Harvestly registration role selection.
 * Exactly three public registration roles. There is no public Admin registration.
 */
$tiles = [
    [
        'page' => 'signup_buyer',
        'title' => 'Buyer',
        'badge' => 'Active immediately',
        'badgeClass' => 'badge-success',
        'text' => 'Order fresh produce directly from Farmers and track your delivery from farm to destination.',
        'cta' => 'Register as Buyer',
        'icon' => '<path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/>',
    ],
    [
        'page' => 'signup_farmer',
        'title' => 'Farmer',
        'badge' => 'Admin approval required',
        'badgeClass' => 'badge-pending',
        'text' => 'List your produce, manage your inventory and stock, and receive orders from Buyers anywhere in Sri Lanka.',
        'cta' => 'Register as Farmer',
        'icon' => '<path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>',
    ],
    [
        'page' => 'signup_courier',
        'title' => 'Courier Partner',
        'badge' => 'Admin approval required',
        'badgeClass' => 'badge-pending',
        'text' => 'Register your delivery organisation, define the pickup-district to destination-district routes you serve, and accept delivery assignments.',
        'cta' => 'Register Organisation',
        'icon' => '<rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/>',
    ],
];
?>
<div class="page-content" style="max-width:940px;margin:70px auto;padding:0 20px">
    <div style="text-align:center;margin-bottom:36px">
        <h1 style="font-size:32px;font-weight:800;margin:0">Join the Harvestly Marketplace</h1>
        <p style="font-size:16px;color:var(--color-on-surface-variant);margin:8px 0 0">
            Choose the role you want to register for
        </p>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(270px,1fr));gap:24px">
        <?php foreach ($tiles as $tile): ?>
        <a href="index.php?page=<?= $tile['page'] ?>"
           class="role-tile"
           style="background:#fff;border:2px solid var(--color-outline-variant);border-radius:var(--radius-xl);padding:32px 24px;display:flex;flex-direction:column;align-items:center;text-align:center;gap:14px;text-decoration:none;transition:all .2s ease">
            <div style="width:64px;height:64px;border-radius:50%;background:rgba(49,95,59,.12);color:var(--color-primary);display:flex;align-items:center;justify-content:center">
                <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><?= $tile['icon'] ?></svg>
            </div>
            <div>
                <h3 style="font-size:20px;font-weight:700;margin:0;color:var(--color-on-surface)"><?= $tile['title'] ?></h3>
                <span class="badge <?= $tile['badgeClass'] ?>" style="margin-top:6px;display:inline-block"><?= $tile['badge'] ?></span>
            </div>
            <p style="font-size:14px;color:var(--color-on-surface-variant);line-height:1.55;margin:0"><?= $tile['text'] ?></p>
            <span class="btn btn-outline btn-sm" style="margin-top:auto"><?= $tile['cta'] ?> &rarr;</span>
        </a>
        <?php endforeach; ?>
    </div>

    <div class="notice" style="margin-top:28px;font-size:13px">
        <strong>No public Admin registration.</strong> Harvestly has exactly four actors &mdash; Buyer,
        Farmer, Courier Partner and Admin. Admin accounts are created by Harvestly only. Farmer and
        Courier Partner accounts are reviewed by an Admin before their dashboards can be opened.
    </div>

    <div style="text-align:center;margin-top:26px">
        Already registered? <a href="index.php?page=login" style="color:var(--color-primary);font-weight:700">Sign in to your account</a>
    </div>
</div>
