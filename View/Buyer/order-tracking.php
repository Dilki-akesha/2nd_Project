<?php
require_once __DIR__ . '/../../config/app.php';
if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    redirect('Controller/Buyer/OrderTrackingController.php?' . ($_SERVER['QUERY_STRING'] ?? ''));
}
require __DIR__ . '/includes/layout.php';
buyer_page_top('Order Details', 'OrdersController.php');

if (!$order) {
    ?>
    <section class="buyer-panel">
        <h3>Order not found</h3>
        <p class="buyer-note">This order is unavailable or does not belong to your account.</p>
        <div class="buyer-actions" style="margin-top:16px">
            <a class="buyer-button" href="<?= e(buyerRoute('OrdersController.php')) ?>">Back to My Orders</a>
        </div>
    </section>
    <?php
    buyer_page_bottom();
    return;
}

/* The canonical flow every Harvestly role shares. */
$flow = [
    'PENDING_PAYMENT' => 'Pending Payment',
    'PAID' => 'Paid',
    'ACCEPTED' => 'Accepted',
    'PREPARING' => 'Preparing',
    'READY_FOR_DELIVERY' => 'Ready for Delivery',
    'PENDING_ASSIGNMENT' => 'Pending Assignment',
    'ASSIGNED' => 'Assigned',
    'PICKED_UP' => 'Picked Up',
    'IN_TRANSIT' => 'In Transit',
    'OUT_FOR_DELIVERY' => 'Out for Delivery',
    'DELIVERED' => 'Delivered',
    'COMPLETED' => 'Completed',
];
$flowKeys = array_keys($flow);
$currentKey = (string)$order['status_code'];
$terminal = in_array($currentKey, ['UNDELIVERABLE', 'REJECTED', 'CANCELLED'], true);
$currentIndex = $terminal ? -1 : (int)array_search($currentKey, $flowKeys, true);

/* Index each recorded history row so the timeline can mark it done. */
$historyByStatus = [];
foreach ($history as $row) {
    $historyByStatus[(string)$row['status']] = $row;
}
$confirmHours = (int)db_setting('buyer_confirmation_hours', 48);
?>

<div class="buyer-actions no-print" style="margin-bottom:18px">
    <a class="buyer-button buyer-ghost" href="<?= e(buyerRoute('OrdersController.php')) ?>">
        <span class="material-symbols-outlined" aria-hidden="true">arrow_back</span>
        <span>Back to My Orders</span>
    </a>
    <a class="buyer-button buyer-ghost" href="<?= e(buyerRoute('FeedbackController.php', 'order_id=' . (int)$order['db_id'])) ?>">
        <span class="material-symbols-outlined" aria-hidden="true">rate_review</span>
        <span>Reviews / Report Issue</span>
    </a>
</div>

<?php buyerFlash(); ?>

<section class="buyer-title">
    <div>
        <h2><?= e($order['id']) ?></h2>
        <p>Placed <?= e($order['date']) ?> &middot; <?= e($order['destination_district'] ?: 'District not set') ?></p>
    </div>
    <?= buyerStatusBadge((string)$order['status_code']) ?>
</section>

<?php if ($currentKey === 'PENDING_PAYMENT'): ?>
<div class="buyer-alert warn">
    <strong>Payment pending</strong>
    No payment has been collected for this order and no card details are stored. It stays in
    <em>Pending Payment</em> until payment integration is enabled.
</div>
<?php endif; ?>

<?php if ($terminal): ?>
<div class="buyer-alert error">
    <strong><?= e(harvestlyStatusLabel($currentKey)) ?></strong>
    <?php if ($currentKey === 'UNDELIVERABLE'): ?>
    Two delivery attempts were unsuccessful. An Admin has been notified and will review the order.
    You can <a href="<?= e(buyerRoute('FeedbackController.php', 'order_id=' . (int)$order['db_id'])) ?>">report an issue</a> for this order.
    <?php elseif ($currentKey === 'CANCELLED'): ?>
    This order was cancelled<?= $order['cancellation_reason'] ? ' - ' . e((string)$order['cancellation_reason']) : '' ?>.
    <?php else: ?>
    The Farmer could not accept this order<?= $order['cancellation_reason'] ? ' - ' . e((string)$order['cancellation_reason']) : '' ?>.
    <?php endif; ?>
</div>
<?php endif; ?>

<?php if ($currentKey === 'DELIVERED'): ?>
<section class="buyer-panel">
    <h3>Confirm your delivery</h3>
    <p class="buyer-note">
        Your Courier Partner has marked this order Delivered. Please confirm receipt to complete
        the order. If you do not respond within <?= $confirmHours ?> hours the order completes
        automatically. Reporting an issue does not stop this automatic completion.
    </p>
    <form method="post" action="<?= e(buyerRoute('OrderTrackingController.php')) ?>" data-once="1" style="margin-top:16px">
        <?= csrfField() ?>
        <input type="hidden" name="order_id" value="<?= (int)$order['db_id'] ?>">
        <button class="buyer-button" type="submit">
            <span class="material-symbols-outlined" aria-hidden="true">check_circle</span>
            <span>Confirm Received</span>
        </button>
    </form>
</section>
<?php endif; ?>

<div class="buyer-detail-layout">
    <div>
        <section class="buyer-panel">
            <h3>Order items</h3>
            <div class="buyer-table-wrap">
                <table class="buyer-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th class="buyer-num">Quantity</th>
                            <th class="buyer-num">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($order['items'] as $item): ?>
                        <tr>
                            <td class="buyer-product-cell">
                                <?php if (!empty($item['image'])): ?><img src="<?= e($item['image']) ?>" alt=""><?php endif; ?>
                                <?= e($item['name']) ?>
                            </td>
                            <td class="buyer-num"><?= e(rtrim(rtrim(number_format((float)$item['quantity'], 3), '0'), '.')) ?> <?= e($item['unit']) ?></td>
                            <td class="buyer-num"><?= e(harvestlyMoney((float)$item['price'] * (float)$item['quantity'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <hr class="buyer-hr">
            <dl class="buyer-kv">
                <dt>Product subtotal</dt>
                <dd><?= e(harvestlyMoney($order['subtotal'])) ?></dd>
                <dt>Buyer service fee</dt>
                <dd><?= e(harvestlyMoney($order['service_fee'])) ?></dd>
                <dt>Delivery fee</dt>
                <dd><?= e(harvestlyMoney($order['delivery_fee'])) ?></dd>
                <dt class="buyer-kv-total">Total</dt>
                <dd class="buyer-kv-total"><?= e(harvestlyMoney($order['total'])) ?></dd>
            </dl>
            <p class="buyer-note" style="margin-top:14px">
                The delivery fee is the base delivery fee plus the stored district reference distance
                for your destination district at the per-kilometre rate. It is an approximate
                district-level figure, not a live or GPS distance.
            </p>
        </section>

        <section class="buyer-panel">
            <h3>Order progress</h3>
            <?php if (!$flowKeys): ?>
                <p class="buyer-note">No progress recorded.</p>
            <?php else: ?>
            <ul class="buyer-steps">
                <?php foreach ($flow as $key => $label): ?>
                    <?php
                    $stepIndex = (int)array_search($key, $flowKeys, true);
                    $recorded = $historyByStatus[$key] ?? null;
                    $isDone = $recorded !== null && ($currentIndex < 0 || $stepIndex < $currentIndex || $currentKey === 'COMPLETED');
                    $isCurrent = $key === $currentKey;
                    $isPending = !$isDone && !$isCurrent;
                    ?>
                    <li class="<?= $isDone ? 'is-done' : ($isCurrent ? 'is-current' : 'is-pending') ?>">
                        <strong><?= e($label) ?></strong>
                        <?php if ($recorded): ?>
                        <small><?= e(date('d M Y, H:i', strtotime((string)$recorded['changed_at']))) ?><?= $recorded['note'] !== null && $recorded['note'] !== '' ? ' · ' . e((string)$recorded['note']) : '' ?></small>
                        <?php else: ?>
                        <small><?= $isCurrent ? 'Current status' : 'Pending' ?></small>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </section>
    </div>

    <div>
        <section class="buyer-panel">
            <h3>Delivery information</h3>
            <dl class="buyer-kv">
                <dt>Recipient</dt>
                <dd><?= e($order['full_name']) ?></dd>
                <dt>Phone</dt>
                <dd><?= e($order['phone']) ?></dd>
                <dt>Address</dt>
                <dd><?= e($order['address']) ?></dd>
                <?php if ($order['city'] !== ''): ?>
                <dt>City / town</dt>
                <dd><?= e($order['city']) ?></dd>
                <?php endif; ?>
                <?php if ($order['postal'] !== ''): ?>
                <dt>Postal code</dt>
                <dd><?= e($order['postal']) ?></dd>
                <?php endif; ?>
                <dt>Destination district</dt>
                <dd><?= e($order['destination_district'] ?: '—') ?></dd>
            </dl>
        </section>

        <section class="buyer-panel">
            <h3>Courier Partner</h3>
            <?php if (!empty($delivery['organisation_name'])): ?>
                <dl class="buyer-kv">
                    <dt>Organisation</dt>
                    <dd><?= e((string)$delivery['organisation_name']) ?></dd>
                    <?php if (!empty($delivery['phone'])): ?>
                    <dt>Contact</dt>
                    <dd><?= e((string)$delivery['phone']) ?></dd>
                    <?php endif; ?>
                    <dt>Delivery status</dt>
                    <dd><?= e(harvestlyStatusLabel((string)$delivery['delivery_status'])) ?></dd>
                </dl>
                <p class="buyer-note" style="margin-top:14px">
                    Your order is handled by this Courier Partner organisation from the Farmer's pickup
                    district to your destination district. Harvestly does not use live maps, GPS
                    tracking, individual drivers or vehicles.
                </p>
            <?php else: ?>
                <p class="buyer-note">
                    A Courier Partner is assigned automatically after the Farmer marks this order
                    Ready for Delivery, provided an approved and available partner covers your
                    district route. You will be notified when one accepts.
                </p>
            <?php endif; ?>
        </section>
    </div>
</div>

<?php buyer_page_bottom(); ?>
