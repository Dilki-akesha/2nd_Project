<?php
require_once __DIR__ . '/../../config/app.php';
if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) redirect('Controller/Buyer/ProfileController.php');
require __DIR__ . '/includes/layout.php';
buyer_page_top('My Profile', 'ProfileController.php');
?>

<section class="buyer-title">
    <div>
        <h2>My Profile</h2>
        <p>Keep your contact and default delivery information up to date.</p>
    </div>
</section>

<?php buyerFlash(); ?>

<div class="buyer-metrics">
    <?php foreach ([
        ['total', 'Total orders', 'receipt_long'],
        ['active', 'Active orders', 'local_shipping'],
        ['completed', 'Completed orders', 'done_all'],
        ['closed', 'Cancelled / undeliverable', 'cancel'],
    ] as [$key, $label, $icon]): ?>
    <a class="buyer-metric" href="<?= e(buyerRoute('OrdersController.php')) ?>">
        <span class="buyer-metric-icon material-symbols-outlined" aria-hidden="true"><?= e($icon) ?></span>
        <span>
            <strong><?= (int)($orderStats[$key] ?? 0) ?></strong>
            <span><?= e($label) ?></span>
        </span>
    </a>
    <?php endforeach; ?>
</div>

<form class="buyer-panel" method="post" action="<?= e(buyerRoute('ProfileController.php')) ?>">
    <?= csrfField() ?>

    <h3>Personal information</h3>
    <div class="buyer-form-grid">
        <div class="buyer-field">
            <span>Full name</span>
            <input type="text" name="name" value="<?= e((string)($buyer['name'] ?? '')) ?>" required>
        </div>
        <div class="buyer-field">
            <span>Email</span>
            <input type="email" name="email" value="<?= e((string)($buyer['email'] ?? '')) ?>" required>
        </div>
        <div class="buyer-field">
            <span>Phone</span>
            <input type="tel" name="phone" value="<?= e((string)($buyer['phone'] ?? '')) ?>" required>
        </div>
        <div class="buyer-field">
            <span>Default district</span>
            <select name="district" required>
                <?php foreach ($districts as $d): ?>
                <option value="<?= e($d['district_name']) ?>" <?= ($buyer['district'] ?? '') === $d['district_name'] ? 'selected' : '' ?>>
                    <?= e($d['district_name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
            <small>Used as the default destination district at checkout.</small>
        </div>
    </div>

    <hr class="buyer-hr">
    <h3>Default delivery address</h3>
    <div class="buyer-form-grid">
        <div class="buyer-field buyer-field--wide">
            <span>Street address</span>
            <input type="text" name="address" value="<?= e((string)($buyer['address'] ?? '')) ?>" required>
        </div>
        <div class="buyer-field buyer-field--wide">
            <span>Apartment / landmark (optional)</span>
            <input type="text" name="address2" value="<?= e((string)($buyer['address2'] ?? '')) ?>">
        </div>
        <div class="buyer-field">
            <span>City / town (optional)</span>
            <input type="text" name="city" value="<?= e((string)($buyer['city'] ?? '')) ?>">
        </div>
        <div class="buyer-field">
            <span>Postal code (optional)</span>
            <input type="text" name="postal" value="<?= e((string)($buyer['postal'] ?? '')) ?>">
        </div>
    </div>

    <div class="buyer-actions" style="margin-top:22px">
        <button class="buyer-button" type="submit">
            <span class="material-symbols-outlined" aria-hidden="true">save</span>
            <span>Save Profile</span>
        </button>
        <a class="buyer-button buyer-ghost" href="<?= e(buyerRoute('DashboardController.php')) ?>">Cancel</a>
    </div>
</form>

<section class="buyer-panel">
    <h3>Account</h3>
    <dl class="buyer-kv">
        <dt>Account status</dt>
        <dd><?= e(harvestlyStatusLabel((string)($buyer['account_status'] ?? ''))) ?></dd>
        <dt>Member since</dt>
        <dd><?= e((string)($buyer['joined'] ?? '—')) ?></dd>
    </dl>
    <p class="buyer-note" style="margin-top:14px">
        Harvestly has no public Admin registration. If you need a Farmer or Courier Partner account,
        sign up from the role selection page and an Admin will review your application.
    </p>
</section>

<?php buyer_page_bottom(); ?>
