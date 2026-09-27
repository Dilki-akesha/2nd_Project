<?php
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/../../Model/Farmer/FarmerOrderModel.php';

$id = (int)($_GET['id'] ?? 0);
$order = $farmerModel->order($id, $farmer_id);
if (!$order) {
    http_response_code(404);
    flash('error', 'Order not found.');
    farmer_redirect('orders.php');
}

/* The single source of truth for the Farmer order flow. */
$allowed = ['PAID' => 'ACCEPTED', 'ACCEPTED' => 'PREPARING', 'PREPARING' => 'READY_FOR_DELIVERY'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ok = (new FarmerOrderModel())->advance($id, $farmer_id, ($_POST['action'] ?? '') === 'reject');
    flash(
        $ok ? 'success' : 'error',
        $ok
            ? (($_POST['action'] ?? '') === 'reject' ? 'Order rejected and stock returned.' : 'Order updated.')
            : 'Unable to update this order.'
    );
    farmer_redirect('order-details.php?id=' . $id);
}

$items = $farmerModel->orderItems($id);
$history = $farmerModel->orderHistory($id);
$delivery = $farmerModel->deliveryForOrder($id);
$status = (string)$order['order_status'];

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
$terminal = in_array($status, ['UNDELIVERABLE', 'REJECTED', 'CANCELLED'], true);
$currentIndex = $terminal ? -1 : (int)array_search($status, $flowKeys, true);
$historyByStatus = [];
foreach ($history as $row) {
    $historyByStatus[(string)$row['status']] = $row;
}

$statusHelp = [
    'PENDING_PAYMENT' => 'Waiting for the Buyer to complete payment. Nothing for you to do yet.',
    'PENDING_ASSIGNMENT' => 'Ready for Delivery. Harvestly is looking for an approved, available Courier Partner that supports this district route. Admin can assist if no partner is found.',
    'ASSIGNED' => 'A Courier Partner organisation has accepted this delivery.',
    'PICKED_UP' => 'The Courier Partner has collected the order.',
    'IN_TRANSIT' => 'The order is travelling between districts.',
    'OUT_FOR_DELIVERY' => 'The Courier Partner is making the delivery attempt.',
    'DELIVERED' => 'Delivered. The Buyer can confirm receipt, or the order completes automatically after the confirmation window.',
    'COMPLETED' => 'Delivered and completed.',
    'UNDELIVERABLE' => 'Two delivery attempts were unsuccessful. Admin has been notified.',
    'REJECTED' => 'You rejected this order and the stock has been returned to your inventory.',
    'CANCELLED' => 'This order was cancelled.',
];

page_top('Order Details', 'orders');
?>

<div class="page-title">
    <div>
        <h1>Order Details &mdash; <?= e(orderPublicId($id)) ?></h1>
        <p><?= e((string)$order['buyer_name']) ?> &middot; <?= e((string)($order['destination_district'] ?: 'District not set')) ?></p>
    </div>
    <a class="btn secondary" href="orders.php">Back to Orders</a>
</div>

<div class="card">
    <div class="row between mb">
        <span class="status-badge <?= e(farmer_status_tone($status)) ?>"><?= e(farmer_status_label($status)) ?></span>
        <span class="muted">Placed <?= e(date('d M Y', strtotime((string)$order['created_at']))) ?></span>
    </div>

    <?php if (isset($statusHelp[$status])): ?>
    <p class="notice <?= in_array($status, ['REJECTED', 'CANCELLED', 'UNDELIVERABLE'], true) ? 'error' : '' ?>"><?= e($statusHelp[$status]) ?></p>
    <?php endif; ?>

    <?php if (isset($allowed[$status])): ?>
    <div class="row mt">
        <form method="post">
            <?= csrfField() ?>
            <button class="btn" type="submit">Move to <?= e(farmer_status_label($allowed[$status])) ?></button>
        </form>
        <?php if ($status === 'PAID'): ?>
        <form method="post" onsubmit="return confirm('Reject this order? Stock will be returned to your inventory.');">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="reject">
            <button class="btn danger" type="submit">Reject Order</button>
        </form>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php if ($status === 'PAID'): ?>
    <p class="muted mt">Accept the order to start preparing it, or reject it if you cannot fulfil it.</p>
    <?php endif; ?>
    <?php if ($status === 'ACCEPTED'): ?>
    <p class="muted mt">Mark the order <strong>Preparing</strong> once you start packing it.</p>
    <?php endif; ?>
    <?php if ($status === 'PREPARING'): ?>
    <p class="muted mt">When the produce is packed and ready, mark it <strong>Ready for Delivery</strong>. Harvestly then offers it to an eligible Courier Partner.</p>
    <?php endif; ?>
</div>

<div class="card">
    <div class="section-head"><h2>Items</h2></div>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr><th>Product</th><th>Unit price</th><th>Quantity</th><th>Line total</th></tr>
            </thead>
            <tbody>
            <?php foreach ($items as $i): ?>
                <tr>
                    <td><?= e((string)$i['product_name_snapshot']) ?></td>
                    <td class="nowrap"><?= farmer_money($i['unit_price_snapshot']) ?></td>
                    <td class="nowrap"><?= e(rtrim(rtrim(number_format((float)$i['quantity'], 3), '0'), '.')) ?> <?= e((string)($i['unit_label'] ?? '')) ?></td>
                    <td class="nowrap"><?= farmer_money($i['line_total']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <hr style="border:0;border-top:1px solid var(--line);margin:18px 0">
    <dl class="kv">
        <dt>Product subtotal</dt>
        <dd><?= farmer_money($order['product_subtotal']) ?></dd>
        <dt>Farmer marketplace fee</dt>
        <dd>- <?= farmer_money($order['farmer_marketplace_fee']) ?></dd>
        <dt>Your net earnings</dt>
        <dd><strong><?= farmer_money((float)$order['product_subtotal'] - (float)$order['farmer_marketplace_fee']) ?></strong></dd>
        <dt>Buyer service fee</dt>
        <dd><?= farmer_money($order['buyer_service_fee']) ?></dd>
        <dt>Delivery fee (Courier Partner)</dt>
        <dd><?= farmer_money($order['delivery_fee']) ?></dd>
    </dl>
    <p class="muted mt">
        The delivery fee is paid to the Courier Partner, not to you. The marketplace fee percentage is
        configurable in Admin settings; Harvestly has no fixed revenue split.
    </p>
</div>

<div class="card">
    <div class="section-head"><h2>Delivery</h2></div>
    <dl class="kv">
        <dt>Recipient</dt>
        <dd><?= e((string)$order['recipient_name']) ?></dd>
        <dt>Phone</dt>
        <dd><?= e((string)$order['recipient_phone']) ?></dd>
        <dt>Address</dt>
        <dd><?= e(trim((string)$order['delivery_address_line1'] . ' ' . (string)$order['delivery_address_line2'])) ?></dd>
        <dt>City / town</dt>
        <dd><?= e((string)($order['delivery_city_town'] ?: '—')) ?></dd>
        <dt>Destination district</dt>
        <dd><?= e((string)($order['destination_district'] ?: '—')) ?></dd>
    </dl>

    <?php if ($delivery && !empty($delivery['organisation_name'])): ?>
    <hr style="border:0;border-top:1px solid var(--line);margin:18px 0">
    <dl class="kv">
        <dt>Courier Partner</dt>
        <dd><?= e((string)$delivery['organisation_name']) ?></dd>
        <dt>Delivery status</dt>
        <dd><?= e(farmer_status_label((string)$delivery['delivery_status'])) ?></dd>
    </dl>
    <p class="muted mt">
        Harvestly assigns Courier Partner organisations by district route. There are no individual
        drivers, vehicles, fleets, maps or live GPS tracking in Harvestly.
    </p>
    <?php else: ?>
    <p class="muted mt">A Courier Partner organisation is assigned automatically after you mark the order Ready for Delivery.</p>
    <?php endif; ?>
</div>

<div class="card">
    <div class="section-head"><h2>Order Progress</h2></div>
    <?php if ($terminal): ?>
    <p class="notice error">This order ended as <strong><?= e(farmer_status_label($status)) ?></strong>.</p>
    <?php endif; ?>
    <ul class="timeline">
        <?php foreach ($flow as $key => $label):
            $stepIndex = (int)array_search($key, $flowKeys, true);
            $recorded = $historyByStatus[$key] ?? null;
            $isDone = $recorded !== null && ($currentIndex < 0 || $stepIndex < $currentIndex || $status === 'COMPLETED');
            $isCurrent = $key === $status;
        ?>
        <li class="<?= $isDone ? 'done' : ($isCurrent ? 'current' : 'pending') ?>">
            <strong><?= e($label) ?></strong>
            <?php if ($recorded): ?>
            <small><?= e(date('d M Y, H:i', strtotime((string)$recorded['created_at']))) ?><?= !empty($recorded['note']) ? ' · ' . e((string)$recorded['note']) : '' ?></small>
            <?php else: ?>
            <small><?= $isCurrent ? 'Current status' : 'Pending' ?></small>
            <?php endif; ?>
        </li>
        <?php endforeach; ?>
    </ul>
</div>

<?php page_bottom(); ?>
