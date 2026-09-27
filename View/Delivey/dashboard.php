<?php
$courierBase = 'Controller/Courier/CourierController.php?page=';
$offers = $data['offers'] ?? [];
$pendingOffers = array_values(array_filter($offers, static fn(array $o): bool => (string)$o['offer_status'] === 'PENDING'));
$isAvailable = (string)($profile['availability_status'] ?? 'UNAVAILABLE') === 'AVAILABLE';
?>

<div class="courier-grid-auto mb">
    <div class="courier-card" style="margin:0">
        <p class="courier-muted">Pending Offers</p>
        <h2 style="margin:4px 0"><?= (int)$stats['offers'] ?></h2>
    </div>
    <div class="courier-card" style="margin:0">
        <p class="courier-muted">Active Deliveries</p>
        <h2 style="margin:4px 0"><?= (int)$stats['active'] ?></h2>
    </div>
    <div class="courier-card" style="margin:0">
        <p class="courier-muted">Completed Deliveries</p>
        <h2 style="margin:4px 0"><?= (int)$stats['completed'] ?></h2>
    </div>
    <div class="courier-card" style="margin:0">
        <p class="courier-muted">Pending Earnings</p>
        <h2 style="margin:4px 0"><?= harvestlyMoney($stats['pending_earnings']) ?></h2>
    </div>
</div>

<div class="courier-card">
    <h3>Availability</h3>
    <div class="courier-row">
        <span class="courier-badge <?= $isAvailable ? 'ok' : 'muted' ?>"><?= e(harvestlyStatusLabel($isAvailable ? 'AVAILABLE' : 'UNAVAILABLE')) ?></span>
        <form method="post" action="<?= e(url('Controller/Courier/CourierController.php')) ?>">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="availability">
            <input type="hidden" name="return_page" value="dashboard">
            <input type="hidden" name="status" value="<?= $isAvailable ? 'UNAVAILABLE' : 'AVAILABLE' ?>">
            <button class="courier-btn <?= $isAvailable ? 'danger' : '' ?>" type="submit">
                <span class="material-symbols-outlined" aria-hidden="true"><?= $isAvailable ? 'cancel' : 'check_circle' ?></span>
                <span>Set <?= $isAvailable ? 'Unavailable' : 'Available' ?></span>
            </button>
        </form>
        <a class="courier-btn secondary" href="<?= e(url($courierBase . 'coverage')) ?>">
            <span class="material-symbols-outlined" aria-hidden="true">route</span>
            <span>Coverage Routes</span>
        </a>
    </div>
</div>

<div class="courier-card">
    <div class="courier-row between mb">
        <h3 style="margin:0">Recent Assignment Offers</h3>
        <a class="courier-btn secondary small" href="<?= e(url($courierBase . 'requests')) ?>">View All Offers</a>
    </div>

    <?php if (!$pendingOffers): ?>
    <div class="courier-empty">
        No pending assignment offers. An offer is created automatically when a Farmer marks an order
        Ready for Delivery and the district route matches one of your active coverage routes.
    </div>
    <?php else: ?>
    <div class="courier-table-wrap">
        <table class="courier-table">
            <thead>
                <tr><th>Order</th><th>Farmer</th><th>Route</th><th>Delivery Fee</th><th>Expires</th><th></th></tr>
            </thead>
            <tbody>
            <?php foreach ($pendingOffers as $o): ?>
            <tr>
                <td class="courier-nowrap"><?= e(orderPublicId((int)$o['order_id'])) ?></td>
                <td><?= e((string)$o['farmer_name']) ?></td>
                <td class="courier-nowrap">
                    <span class="courier-route">
                        <?= e((string)($o['origin_district'] ?? '—')) ?>
                        <span class="material-symbols-outlined" aria-hidden="true">arrow_forward</span>
                        <?= e((string)($o['destination_district'] ?? '—')) ?>
                    </span>
                </td>
                <td class="courier-nowrap"><?= harvestlyMoney($o['delivery_fee']) ?></td>
                <td class="courier-nowrap"><?= e((string)($o['expires_at'] ?? '—')) ?></td>
                <td><a class="courier-btn secondary small" href="<?= e(url($courierBase . 'requests')) ?>">Respond</a></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
