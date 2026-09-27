<?php
$courierBase = 'Controller/Courier/CourierController.php?page=';
$maxAttempts = maxDeliveryAttempts();
$confirmHours = buyerConfirmationHours();
$d = $data['delivery'] ?? null;

/* The single source of truth for the Courier Partner delivery flow. */
$transitions = [
    'ASSIGNED'         => 'PICKED_UP',
    'PICKED_UP'        => 'IN_TRANSIT',
    'IN_TRANSIT'       => 'OUT_FOR_DELIVERY',
    'OUT_FOR_DELIVERY' => 'DELIVERED',
];
$flow = [
    'ASSIGNED' => 'Assigned',
    'PICKED_UP' => 'Picked Up',
    'IN_TRANSIT' => 'In Transit',
    'OUT_FOR_DELIVERY' => 'Out for Delivery',
    'DELIVERED' => 'Delivered',
    'COMPLETED' => 'Completed',
];
?>

<?php if (!$d): ?>
<div class="courier-card courier-empty">
    Select an active delivery from <a href="<?= e(url($courierBase . 'assigned')) ?>">Active Deliveries</a>.
</div>
<?php else: ?>

<?php $current = (string)$d['delivery_status']; $flowKeys = array_keys($flow); $currentIndex = (int)array_search($current, $flowKeys, true); ?>

<div class="courier-card">
    <div class="courier-row between mb">
        <h3 style="margin:0"><?= e(orderPublicId((int)$d['order_id'])) ?></h3>
        <span class="courier-badge <?= e(harvestlyStatusTone($current)) ?>"><?= e(harvestlyStatusLabel($current)) ?></span>
    </div>

    <div class="courier-grid-2">
        <div>
            <h4 style="margin:0 0 8px">Pickup</h4>
            <p class="courier-note">
                <strong><?= e((string)$d['farmer_name']) ?></strong><br>
                <?= e(trim(((string)($d['pickup_address_line1'] ?? '')) . ' ' . ((string)($d['pickup_address_line2'] ?? '')))) ?><br>
                <?= e((string)($d['pickup_city_town'] ?? '')) ?>
                <?= e((string)($d['origin_district'] ?? '')) ?>
            </p>
        </div>
        <div>
            <h4 style="margin:0 0 8px">Delivery</h4>
            <p class="courier-note">
                <strong><?= e((string)$d['recipient_name']) ?></strong><br>
                <?= e(trim(((string)($d['delivery_address_line1'] ?? '')) . ' ' . ((string)($d['delivery_address_line2'] ?? '')))) ?><br>
                <?= e((string)($d['delivery_city_town'] ?? '')) ?>
                <?= e((string)($d['destination_district'] ?? '')) ?><br>
                <?= e((string)$d['recipient_phone']) ?>
            </p>
        </div>
    </div>

    <hr style="border:0;border-top:1px solid var(--hv-border);margin:18px 0">

    <dl class="courier-kv">
        <dt>District route</dt>
        <dd>
            <span class="courier-route">
                <?= e((string)($d['origin_district'] ?? '—')) ?>
                <span class="material-symbols-outlined" aria-hidden="true">arrow_forward</span>
                <?= e((string)($d['destination_district'] ?? '—')) ?>
            </span>
        </dd>
        <dt>Delivery fee</dt>
        <dd><?= harvestlyMoney($d['delivery_fee']) ?></dd>
        <dt>Recorded attempts</dt>
        <dd><?= (int)$d['active_attempt_count'] ?> / <?= $maxAttempts ?></dd>
    </dl>
</div>

<div class="courier-card">
    <h3>Delivery progress</h3>
    <ul class="courier-steps">
        <?php foreach ($flow as $key => $label):
            $stepIndex = (int)array_search($key, $flowKeys, true);
            $isDone = $currentIndex > 0 && $stepIndex < $currentIndex;
            $isCurrent = $key === $current;
        ?>
        <li class="<?= $isDone ? 'done' : ($isCurrent ? 'current' : 'pending') ?>">
            <strong><?= e($label) ?></strong>
            <small><?= $isCurrent ? 'Current status' : ($isDone ? 'Completed' : 'Pending') ?></small>
        </li>
        <?php endforeach; ?>
    </ul>
</div>

<div class="courier-card">
    <h3>Update delivery status</h3>
    <?php if (isset($transitions[$current])): ?>
    <p class="courier-note">Advance this delivery to the next stage in the Harvestly order flow.</p>
    <form method="post" action="<?= e(url('Controller/Courier/CourierController.php')) ?>" class="mt">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="delivery_status">
        <input type="hidden" name="return_page" value="tracking">
        <input type="hidden" name="delivery_id" value="<?= (int)$d['delivery_id'] ?>">
        <input type="hidden" name="next_status" value="<?= e($transitions[$current]) ?>">
        <button class="courier-btn" type="submit">
            <span class="material-symbols-outlined" aria-hidden="true">arrow_forward</span>
            <span>Mark <?= e(harvestlyStatusLabel($transitions[$current])) ?></span>
        </button>
    </form>
    <?php else: ?>
    <p class="courier-note">
        <?php if ($current === 'DELIVERED'): ?>
        Waiting for the Buyer to confirm receipt. If the Buyer does not confirm within
        <?= $confirmHours ?> hours, Harvestly marks the order Completed automatically. Harvestly
        uses no one-time delivery code &mdash; the Buyer simply confirms receipt.
        <?php else: ?>
        This delivery has reached the end of the Courier Partner flow.
        <?php endif; ?>
    </p>
    <?php endif; ?>

    <div class="courier-row mt">
        <a class="courier-btn secondary small" href="<?= e(url($courierBase . 'assigned')) ?>">
            <span class="material-symbols-outlined" aria-hidden="true">arrow_back</span>
            <span>Back to Active Deliveries</span>
        </a>
    </div>
</div>

<?php if ($current === 'OUT_FOR_DELIVERY'): ?>
<div class="courier-card">
    <h3>Record an unsuccessful attempt</h3>
    <p class="courier-note">
        If the Buyer is unavailable when you arrive, record the attempt here. Harvestly allows a maximum
        of <?= $maxAttempts ?> attempts. After the second unsuccessful attempt the delivery is set to
        <strong>Undeliverable</strong> and Admin is notified. A single failed attempt returns the
        delivery to In Transit so you can try again.
    </p>
    <?php if ((int)$d['active_attempt_count'] >= $maxAttempts): ?>
    <p class="courier-notice warn mt">The maximum number of delivery attempts has already been reached.</p>
    <?php else: ?>
    <form method="post" action="<?= e(url('Controller/Courier/CourierController.php')) ?>" class="mt">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="buyer_unavailable">
        <input type="hidden" name="return_page" value="tracking">
        <input type="hidden" name="delivery_id" value="<?= (int)$d['delivery_id'] ?>">
        <div class="courier-field">
            <span>Notes (optional)</span>
            <textarea name="notes" maxlength="500" placeholder="For example: no answer at the address."></textarea>
        </div>
        <button class="courier-btn danger" type="submit"
                onclick="return confirm('Record an unsuccessful delivery attempt?');">
            <span class="material-symbols-outlined" aria-hidden="true">report_problem</span>
            <span>Record Unsuccessful Attempt</span>
        </button>
    </form>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php endif; ?>
