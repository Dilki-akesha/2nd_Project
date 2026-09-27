<?php
/**
 * Admin - Delivery Monitoring.
 * Read-only monitoring of every delivery record. There are no drivers,
 * vehicles, hubs or live location fields in Harvestly, so none are shown.
 */
$statusTone = [
    'COMPLETED' => 'badge-success',
    'DELIVERED' => 'badge-success',
    'PENDING_ASSIGNMENT' => 'badge-warning',
    'ASSIGNED' => 'badge-warning',
    'ACCEPTED' => 'badge-warning',
    'PICKED_UP' => 'badge-info',
    'IN_TRANSIT' => 'badge-info',
    'OUT_FOR_DELIVERY' => 'badge-info',
    'UNDELIVERABLE' => 'badge-danger',
    'CANCELLED' => 'badge-danger',
];
?>
<div class="view-container">
    <div class="page-header mb-4">
        <h1>Delivery Monitoring</h1>
        <p class="text-sm text-muted">
            Every delivery record, its district route, the assigned Courier Partner organisation and
            the delivery fee.
        </p>
    </div>

    <div class="card" style="padding:0;overflow:hidden">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Delivery</th>
                        <th>Order</th>
                        <th>Route</th>
                        <th>Farmer</th>
                        <th>Buyer</th>
                        <th>Courier Partner</th>
                        <th>Delivery Fee</th>
                        <th>Status</th>
                        <th>Delivered</th>
                        <th>Completed</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!$deliveries): ?>
                    <tr>
                        <td colspan="10" style="text-align:center;padding:32px;color:var(--color-outline)">
                            No delivery records yet. A delivery record is created for every order.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($deliveries as $del):
                        $st = strtoupper((string)$del['delivery_status']);
                    ?>
                    <tr>
                        <td><strong>#DEL-<?= sprintf('%03d', (int)$del['id']) ?></strong></td>
                        <td><strong><?= sanitize($del['order_number']) ?></strong></td>
                        <td class="text-xs nowrap">
                            <?= sanitize($del['origin_district'] ?: 'Not set') ?>
                            &rarr;
                            <?= sanitize($del['destination_district'] ?: 'Not set') ?>
                        </td>
                        <td class="text-xs"><?= sanitize($del['farmer_name'] ?: '—') ?></td>
                        <td class="text-xs"><?= sanitize($del['buyer_name'] ?: '—') ?></td>
                        <td>
                            <?php if (!empty($del['courier_name'])): ?>
                                <span class="badge badge-info"><?= sanitize($del['courier_name']) ?></span>
                            <?php else: ?>
                                <span class="badge badge-warning">Unassigned</span>
                            <?php endif; ?>
                        </td>
                        <td class="nowrap"><?= harvestlyMoney($del['delivery_fee']) ?></td>
                        <td>
                            <span class="badge <?= $statusTone[$st] ?? 'badge-pending' ?>">
                                <?= sanitize(harvestlyStatusLabel($st)) ?>
                            </span>
                        </td>
                        <td class="nowrap text-xs">
                            <?= !empty($del['delivered_at']) ? date('d M Y H:i', strtotime((string)$del['delivered_at'])) : '—' ?>
                        </td>
                        <td class="nowrap text-xs">
                            <?= !empty($del['completed_at']) ? date('d M Y H:i', strtotime((string)$del['completed_at'])) : '—' ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card mt-4">
        <p class="text-sm text-muted" style="margin:0">
            <strong>Delivery fee.</strong> The fee stored on each order is the base delivery fee plus
            the stored district reference distance multiplied by the per-kilometre rate. The
            Courier Partner organisation receives that fee, and it becomes eligible for payout after
            the Buyer confirms receipt or after the confirmation window completes the order.
        </p>
    </div>
</div>
