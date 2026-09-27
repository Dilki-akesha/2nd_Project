<?php
/**
 * Admin - Orders Monitor.
 * orders.order_status is an ENUM; the badge always shows the canonical
 * Harvestly status label so every role reads the same order state.
 */
$statusTone = [
    'COMPLETED' => 'badge-success',
    'PAID' => 'badge-success',
    'ACCEPTED' => 'badge-success',
    'DELIVERED' => 'badge-success',
    'PENDING_PAYMENT' => 'badge-pending',
    'PENDING_ASSIGNMENT' => 'badge-danger',
    'READY_FOR_DELIVERY' => 'badge-pending',
    'PREPARING' => 'badge-pending',
    'ASSIGNED' => 'badge-pending',
    'PICKED_UP' => 'badge-info',
    'IN_TRANSIT' => 'badge-info',
    'OUT_FOR_DELIVERY' => 'badge-info',
    'REJECTED' => 'badge-danger',
    'CANCELLED' => 'badge-danger',
    'UNDELIVERABLE' => 'badge-danger',
];
?>
<div class="page-content">
    <div class="section-header">
        <div class="section-title-group">
            <h1>Orders Monitor</h1>
            <p>Track the order fulfilment timeline and manually assign a Courier Partner when automatic assignment falls back to Pending Assignment.</p>
        </div>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success"><?= sanitize($_GET['success']); ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger"><?= sanitize($_GET['error']); ?></div>
    <?php endif; ?>

    <div class="card-table-wrapper">
        <div class="table-toolbar">
            <div class="search-box">
                <input type="text" placeholder="Search order number, Buyer or Farmer" data-table-search="orders-table">
            </div>
        </div>

        <table class="data-table" id="orders-table">
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Buyer</th>
                    <th>Farmer</th>
                    <th>Route</th>
                    <th>Courier Partner</th>
                    <th>Total</th>
                    <th>Delivery Fee</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th>Placed</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$orders): ?>
                <tr>
                    <td colspan="11" style="text-align:center;padding:32px;color:var(--color-outline)">No orders found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($orders as $o):
                    $status = strtoupper((string)$o['status']);
                ?>
                <tr>
                    <td><strong><?= sanitize($o['order_number']) ?></strong></td>
                    <td><?= sanitize($o['buyer_name']) ?></td>
                    <td><?= sanitize($o['farmer_name']) ?></td>
                    <td class="text-xs">
                        <?= sanitize($o['origin_district'] ?: '—') ?>
                        &rarr;
                        <?= sanitize($o['destination_district'] ?: '—') ?>
                    </td>
                    <td>
                        <?php if (!empty($o['courier_name'])): ?>
                            <?= sanitize($o['courier_name']) ?>
                        <?php else: ?>
                            <span class="badge badge-danger">Unassigned</span>
                        <?php endif; ?>
                    </td>
                    <td class="nowrap"><?= harvestlyMoney($o['total_amount']) ?></td>
                    <td class="nowrap"><?= harvestlyMoney($o['delivery_fee']) ?></td>
                    <td>
                        <span class="badge badge-pending"><?= sanitize(harvestlyStatusLabel((string)($o['payment_status'] ?? 'PENDING'))) ?></span>
                    </td>
                    <td>
                        <span class="badge <?= $statusTone[$status] ?? 'badge-pending' ?>">
                            <?= sanitize(harvestlyStatusLabel($status)) ?>
                        </span>
                    </td>
                    <td class="nowrap text-xs"><?= date('d M Y', strtotime((string)$o['created_at'])) ?></td>
                    <td>
                        <button type="button" class="btn btn-outline btn-sm" data-modal-target="modal-override-<?= (int)$o['id']; ?>">
                            <?= $status === 'PENDING_ASSIGNMENT' ? 'Assign Courier' : 'Re-assign Courier' ?>
                        </button>
                    </td>
                </tr>

                <div class="modal-backdrop" id="modal-override-<?= (int)$o['id']; ?>">
                    <div class="modal-card">
                        <div class="modal-header">
                            <div class="modal-title">Manual Delivery Assignment &mdash; <?= sanitize($o['order_number']) ?></div>
                            <button type="button" class="modal-close-btn" data-modal-close>&times;</button>
                        </div>
                        <form action="index.php?admin_action=override_delivery" method="POST">
                            <?= csrfField() ?>
                            <div class="modal-body">
                                <input type="hidden" name="order_id" value="<?= (int)$o['id']; ?>">
                                <dl class="kv" style="display:grid;grid-template-columns:auto 1fr;gap:8px 16px;font-size:13px">
                                    <dt style="color:var(--color-outline)">Buyer address</dt>
                                    <dd style="text-align:right"><?= sanitize($o['delivery_address']) ?></dd>
                                    <dt style="color:var(--color-outline)">Route</dt>
                                    <dd style="text-align:right">
                                        <?= sanitize($o['origin_district'] ?: '—') ?> &rarr; <?= sanitize($o['destination_district'] ?: '—') ?>
                                    </dd>
                                    <dt style="color:var(--color-outline)">Delivery fee</dt>
                                    <dd style="text-align:right"><?= harvestlyMoney($o['delivery_fee']) ?></dd>
                                </dl>
                                <div class="form-group" style="margin-top:14px">
                                    <label class="form-label" for="c-select-<?= (int)$o['id']; ?>">
                                        Courier Partner organisation
                                    </label>
                                    <?php if (!$couriers): ?>
                                        <p class="alert alert-danger" style="margin:0">
                                            No approved and active Courier Partner organisation is available.
                                            Approve one on the Courier Approvals page first.
                                        </p>
                                    <?php else: ?>
                                    <select id="c-select-<?= (int)$o['id']; ?>" name="courier_id" class="form-control" required>
                                        <?php foreach ($couriers as $c): ?>
                                        <option value="<?= (int)$c['id']; ?>" <?= (int)($o['courier_id'] ?? 0) === (int)$c['id'] ? 'selected' : '' ?>>
                                            <?= sanitize($c['organisation_name']) ?><?= !empty($c['office_district']) ? ' (' . sanitize($c['office_district']) . ')' : '' ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php endif; ?>
                                </div>
                                <p class="text-xs" style="color:var(--color-outline);margin:0">
                                    The override is written to the order status history, the delivery
                                    assignment trail and the Courier Partner notifications.
                                </p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline btn-sm" data-modal-close>Cancel</button>
                                <?php if ($couriers): ?>
                                <button type="submit" class="btn btn-primary btn-sm">Confirm Assignment</button>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
