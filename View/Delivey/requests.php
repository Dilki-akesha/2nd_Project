<?php
$courierBase = 'Controller/Courier/CourierController.php?page=';
$offers = array_values(array_filter(
    $data['offers'] ?? [],
    static fn(array $o): bool => (string)$o['offer_status'] === 'PENDING'
));
$responseMinutes = (int)db_setting('courier_assignment_response_minutes', 30);
?>

<div class="courier-card">
    <h3>Assignment Offers</h3>
</div>

<?php if (!$offers): ?>
<div class="courier-card courier-empty">
    No pending assignment offers. Offers arrive automatically when a Ready for Delivery order matches
    one of your active district routes.
</div>
<?php else: ?>
<?php foreach ($offers as $o): ?>
<div class="courier-card">
    <div class="courier-row between mb">
        <h3 style="margin:0"><?= e(orderPublicId((int)$o['order_id'])) ?></h3>
        <span class="courier-badge warn">Pending Response</span>
    </div>

    <dl class="courier-kv">
        <dt>Farmer (pickup)</dt>
        <dd><?= e((string)$o['farmer_name']) ?></dd>
        <dt>Buyer</dt>
        <dd><?= e((string)$o['buyer_name']) ?></dd>
        <dt>Pickup district</dt>
        <dd><?= e((string)($o['origin_district'] ?? '—')) ?></dd>
        <dt>Destination district</dt>
        <dd><?= e((string)($o['destination_district'] ?? '—')) ?></dd>
        <dt>Delivery fee</dt>
        <dd><?= harvestlyMoney($o['delivery_fee']) ?></dd>
        <dt>Respond by</dt>
        <dd><?= e((string)($o['expires_at'] ?? '—')) ?></dd>
    </dl>

    <div class="courier-row mt">
        <form method="post" action="<?= e(url('Controller/Courier/CourierController.php')) ?>">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="respond_offer">
            <input type="hidden" name="return_page" value="requests">
            <input type="hidden" name="offer_id" value="<?= (int)$o['offer_id'] ?>">
            <button class="courier-btn" type="submit" name="response" value="accept">
                <span class="material-symbols-outlined" aria-hidden="true">check_circle</span>
                <span>Accept Assignment</span>
            </button>
            <button class="courier-btn danger" type="submit" name="response" value="reject"
                    onclick="return confirm('Reject this assignment? Harvestly will try the next eligible Courier Partner.');">
                <span class="material-symbols-outlined" aria-hidden="true">cancel</span>
                <span>Reject</span>
            </button>
        </form>
    </div>
</div>
<?php endforeach; ?>
<?php endif; ?>
