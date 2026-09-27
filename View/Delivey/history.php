<?php $history = $data['history'] ?? []; ?>

<div class="courier-card">
    <h3>Delivery History</h3>
    <p class="courier-note">Completed deliveries and deliveries that could not be completed.</p>
</div>

<?php if (!$history): ?>
<div class="courier-card courier-empty">You have no delivery history yet.</div>
<?php else: ?>
<div class="courier-table-wrap courier-card">
    <table class="courier-table">
        <thead>
            <tr>
                <th>Order</th>
                <th>Destination District</th>
                <th>Status</th>
                <th>Delivery Fee</th>
                <th>Completed</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($history as $r): ?>
        <tr>
            <td class="courier-nowrap"><?= e(orderPublicId((int)$r['order_id'])) ?></td>
            <td><?= e((string)($r['destination_district'] ?? '—')) ?></td>
            <td>
                <span class="courier-badge <?= e(harvestlyStatusTone((string)$r['delivery_status'])) ?>">
                    <?= e(harvestlyStatusLabel((string)$r['delivery_status'])) ?>
                </span>
            </td>
            <td class="courier-nowrap"><?= harvestlyMoney($r['delivery_fee']) ?></td>
            <td class="courier-nowrap"><?= e((string)($r['completed_at'] ?: '—')) ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
