<?php
/**
 * Admin - Platform Settings.
 * Only the configurable Harvestly financial and operational settings appear
 * here. Geographic coverage rules beyond district routes, GPS settings,
 * driver/vehicle settings, one-time-code settings, arbitration windows,
 * product grading settings and hard-coded fee splits are all absent.
 */
$locked = AdminModel::lockedSettings();
$editable = [
    'farmer_marketplace_fee_percent' => ['Farmer Marketplace Fee (%)', 'Percentage of the product subtotal retained by Harvestly on each Farmer sale. Configurable, never hard-coded.'],
    'buyer_service_fee_percent' => ['Buyer Service Fee (%)', 'Percentage of the product subtotal charged to the Buyer.'],
    'delivery_base_fee' => ['Base Delivery Fee (Rs.)', 'Flat component of every district-based delivery fee.'],
    'delivery_per_km_rate' => ['Per-Kilometre Rate (Rs.)', 'Multiplied by the stored district reference distance.'],
    'courier_assignment_response_minutes' => ['Courier Partner Response Time (minutes)', 'How long a Courier Partner has to accept or reject an assignment offer.'],
];
$lockedLabels = [
    'buyer_confirmation_hours' => 'Buyer Confirmation Window (hours)',
    'review_window_days' => 'Review Window (days)',
    'max_delivery_attempts' => 'Maximum Delivery Attempts',
];
?>
<div class="page-content" style="max-width:880px;margin:0 auto">

    <div class="section-header">
        <div class="section-title-group">
            <h1>Platform Settings</h1>
            <p>Configure the configurable fees and delivery rules used across Harvestly.</p>
        </div>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success"><?= sanitize($_GET['success']); ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger"><?= sanitize($_GET['error']); ?></div>
    <?php endif; ?>

    <form action="index.php?admin_action=update_settings" method="POST" class="card">
        <?= csrfField() ?>

        <h2 style="margin-top:0">Marketplace Fees</h2>
        <p class="muted" style="margin-top:-6px">
            Harvestly uses configurable percentages. A Courier Partner always receives the
            applicable delivery fee.
        </p>
        <div class="form-grid">
            <?php foreach (['farmer_marketplace_fee_percent', 'buyer_service_fee_percent'] as $key): ?>
            <div class="form-group">
                <label class="form-label" for="<?= $key ?>"><?= $editable[$key][0] ?></label>
                <input class="form-control" id="<?= $key ?>" type="number" step="0.01" min="0" max="100"
                       name="<?= $key ?>" required
                       value="<?= sanitize($settings[$key] ?? '0.00') ?>">
                <small class="muted"><?= $editable[$key][1] ?></small>
            </div>
            <?php endforeach; ?>
        </div>

        <h2>District-Based Delivery Fee</h2>
        <div class="form-group">
            <p class="notice" style="margin:0 0 14px">
                <strong>Delivery Fee = Base Delivery Fee + (District Reference Distance &times; Per-Kilometre Rate)</strong>
            </p>
        </div>
        <div class="form-grid">
            <?php foreach (['delivery_base_fee', 'delivery_per_km_rate'] as $key): ?>
            <div class="form-group">
                <label class="form-label" for="<?= $key ?>"><?= $editable[$key][0] ?></label>
                <input class="form-control" id="<?= $key ?>" type="number" step="0.01" min="0"
                       name="<?= $key ?>" required
                       value="<?= sanitize($settings[$key] ?? '0.00') ?>">
                <small class="muted"><?= $editable[$key][1] ?></small>
            </div>
            <?php endforeach; ?>
        </div>

        <h2>Courier Partner Assignment</h2>
        <div class="form-grid">
            <div class="form-group">
                <label class="form-label" for="courier_assignment_response_minutes"><?= $editable['courier_assignment_response_minutes'][0] ?></label>
                <input class="form-control" id="courier_assignment_response_minutes" type="number" min="5" step="1"
                       name="courier_assignment_response_minutes" required
                       value="<?= sanitize($settings['courier_assignment_response_minutes'] ?? '30') ?>">
                <small class="muted"><?= $editable['courier_assignment_response_minutes'][1] ?></small>
            </div>
        </div>

        <h2>Fixed Scope Rules</h2>
        <p class="muted" style="margin-top:-6px">
            These values are fixed by the agreed Harvestly scope and are shown for reference only.
        </p>
        <div class="form-grid">
            <?php foreach ($lockedLabels as $key => $label): ?>
            <div class="form-group">
                <label class="form-label" for="<?= $key ?>"><?= $label ?></label>
                <input class="form-control" id="<?= $key ?>" type="number"
                       value="<?= sanitize($settings[$key] ?? $locked[$key]) ?>" readonly>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="form-actions">
            <button class="btn btn-primary" type="submit">Save Settings</button>
            <a class="btn btn-secondary" href="index.php?page=admin_overview">Cancel</a>
        </div>
    </form>
</div>
