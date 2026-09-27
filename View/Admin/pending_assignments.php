<?php
/**
 * Admin - Pending Courier Partner Assignments.
 * Shown when automatic assignment failed and the order fell back to
 * Pending Assignment, so an Admin can assign a Courier Partner manually.
 */
?>
<div class="view-container">
    <div class="page-header mb-4">
        <h1>Pending Courier Partner Assignments</h1>
        <p class="text-sm text-muted">
            Orders where automatic assignment found no approved, available Courier Partner covering
            the required district route. Assign one manually to move the order forward.
        </p>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success mb-4"><?= sanitize($_GET['success']); ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger mb-4"><?= sanitize($_GET['error']); ?></div>
    <?php endif; ?>

    <div class="card" style="padding:0;overflow:hidden">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Buyer</th>
                        <th>Farmer</th>
                        <th>Pickup District</th>
                        <th>Destination District</th>
                        <th>Status</th>
                        <th style="text-align:right">Admin Assignment</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!$pendingOrders): ?>
                    <tr>
                        <td colspan="7" style="text-align:center;padding:32px;color:var(--color-outline)">
                            All orders currently have a Courier Partner assigned.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($pendingOrders as $order): ?>
                    <tr>
                        <td><strong><?= sanitize($order['order_number']) ?></strong></td>
                        <td><?= sanitize($order['buyer_name']) ?></td>
                        <td><?= sanitize($order['farmer_name'] ?: '—') ?></td>
                        <td><?= sanitize($order['origin_district'] ?: 'Not set') ?></td>
                        <td><?= sanitize($order['destination_district'] ?: 'Not set') ?></td>
                        <td>
                            <span class="badge badge-warning">
                                <?= sanitize(harvestlyStatusLabel((string)$order['reason'])) ?>
                            </span>
                        </td>
                        <td style="text-align:right">
                            <?php if (!$couriers): ?>
                                <span class="text-xs" style="color:var(--color-outline)">
                                    No approved Courier Partner available.
                                    <?= sanitize($order['origin_district'] ?: '—') ?> &rarr;
                                    <?= sanitize($order['destination_district'] ?: '—') ?>
                                </span>
                            <?php else: ?>
                            <form method="POST" action="index.php?admin_action=override_delivery" class="flex justify-end items-center gap-2">
                                <?= csrfField() ?>
                                <input type="hidden" name="order_id" value="<?= (int)$order['id']; ?>">
                                <select name="courier_id" class="input-field" required aria-label="Courier Partner for <?= sanitize($order['order_number']) ?>">
                                    <option value="">Select a Courier Partner</option>
                                    <?php foreach ($couriers as $c): ?>
                                    <option value="<?= (int)$c['id']; ?>">
                                        <?= sanitize($c['organisation_name']) ?><?= !empty($c['office_district']) ? ' (' . sanitize($c['office_district']) . ')' : '' ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="btn btn-sm btn-primary">Assign</button>
                            </form>
                            <?php endif; ?>
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
            <strong>How automatic assignment works.</strong> When a Farmer marks an order
            <em>Ready for Delivery</em>, Harvestly looks for a Courier Partner organisation that is
            approved, active, available, and supports the Farmer pickup district to Buyer destination
            district route. The offer expires after the configured response window, then the next
            eligible organisation is attempted. Only when no eligible organisation exists does an
            order appear on this page. There is no workload ranking, AI selection or GPS proximity in
            Harvestly.
        </p>
    </div>
</div>
