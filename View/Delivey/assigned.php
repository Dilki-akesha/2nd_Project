<?php
$courierBase = 'Controller/Courier/CourierController.php?page=';
$deliveries = $data['deliveries'] ?? [];
$maxAttempts = maxDeliveryAttempts();
?>

<div class="courier-card">
    <h3>Active Deliveries</h3>
    <p class="courier-note">
        Deliveries assigned to your organisation that are not yet completed. Open a delivery to advance
        its status. Harvestly tracks no maps, GPS location, drivers or vehicles.
    </p>
</div>

<?php if (!$deliveries): ?>
<div class="courier-card courier-empty">
    You have no active deliveries. Accept an assignment offer to start a delivery.
</div>
<?php else: ?>
<div class="courier-table-wrap courier-card">
    <table class="courier-table">
        <thead>
            <tr>
                <th>Order</th>
                <th>Status</th>
                <th>Pickup</th>
                <th>Deliver To</th>
                <th>Delivery Fee</th>
                <th>Attempts</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($deliveries as $d): ?>
        <tr>
            <td class="courier-nowrap"><?= e(orderPublicId((int)$d['order_id'])) ?></td>
            <td>
                <span class="courier-badge <?= e(harvestlyStatusTone((string)$d['delivery_status'])) ?>">
                    <?= e(harvestlyStatusLabel((string)$d['delivery_status'])) ?>
                </span>
            </td>
            <td>
                <?= e((string)$d['farmer_name']) ?>
                <div class="courier-muted" style="font-size:12px"><?= e((string)($d['origin_district'] ?? '')) ?></div>
            </td>
            <td>
                <?= e((string)$d['recipient_name']) ?>
                <div class="courier-muted" style="font-size:12px"><?= e((string)($d['destination_district'] ?? '')) ?></div>
            </td>
            <td class="courier-nowrap"><?= harvestlyMoney($d['delivery_fee']) ?></td>
            <td class="courier-nowrap"><?= (int)$d['active_attempt_count'] ?> / <?= $maxAttempts ?></td>
            <td>
                <a class="courier-btn secondary small" href="<?= e(url($courierBase . 'tracking&id=' . (int)$d['delivery_id'])) ?>">
                    View / Update
                </a>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
