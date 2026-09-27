<?php
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/layout.php';

$statusFilter = (string)($_GET['status'] ?? '');
$allOrders = $farmerModel->orders($farmer_id);

$orders = $allOrders;
if ($statusFilter !== '' && in_array($statusFilter, ['ACTION', 'DELIVERY', 'DONE'], true)) {
    $orders = array_values(array_filter($allOrders, static function (array $o) use ($statusFilter): bool {
        $status = (string)$o['order_status'];
        return match ($statusFilter) {
            'ACTION' => in_array($status, ['PAID', 'ACCEPTED', 'PREPARING', 'READY_FOR_DELIVERY'], true),
            'DELIVERY' => in_array($status, ['PENDING_ASSIGNMENT', 'ASSIGNED', 'PICKED_UP', 'IN_TRANSIT', 'OUT_FOR_DELIVERY'], true),
            'DONE' => in_array($status, ['DELIVERED', 'COMPLETED'], true),
            default => true,
        };
    }));
}

/* Explanations for states the Farmer cannot act on. */
$statusHelp = [
    'PENDING_PAYMENT' => 'Waiting for the Buyer to complete payment. Nothing for you to do yet.',
    'PENDING_ASSIGNMENT' => 'Ready for Delivery. Harvestly is looking for an approved, available Courier Partner for this district route. Admin can assist if none is found.',
    'ASSIGNED' => 'A Courier Partner organisation has accepted this delivery.',
    'PICKED_UP' => 'The Courier Partner has collected the order.',
    'IN_TRANSIT' => 'The order is moving between districts.',
    'OUT_FOR_DELIVERY' => 'The Courier Partner is making the delivery attempt.',
    'DELIVERED' => 'Delivered. The Buyer can confirm receipt, or the order completes automatically after the confirmation window.',
    'COMPLETED' => 'Delivered and completed.',
    'UNDELIVERABLE' => 'Two delivery attempts were unsuccessful. Admin has been notified.',
    'REJECTED' => 'You rejected this order. Stock has been returned to your inventory.',
    'CANCELLED' => 'This order was cancelled.',
];

page_top('Orders', 'orders');
?>

<div class="page-title">
    <div>
        <h1>Orders</h1>
        <p>Every order placed for your products, with its current status.</p>
    </div>
</div>

<div class="card mb">
    <div class="row">
        <?php foreach (['' => 'All Orders', 'ACTION' => 'Needs My Action', 'DELIVERY' => 'In Delivery', 'DONE' => 'Delivered / Completed'] as $key => $label): ?>
        <a class="btn small <?= $statusFilter === $key ? '' : 'secondary' ?>" href="orders.php<?= $key !== '' ? '?status=' . e($key) : '' ?>"><?= e($label) ?></a>
        <?php endforeach; ?>
    </div>
</div>

<div class="card">
    <?php if (!$orders): ?>
        <p class="empty">
            <?= $allOrders ? 'No orders match this filter.' : 'You have no orders yet. Orders appear here once a Buyer checks out one of your products.' ?>
        </p>
    <?php else: ?>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Buyer</th>
                    <th>Destination</th>
                    <th>Date</th>
                    <th>Subtotal</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($orders as $r): ?>
                <tr>
                    <td class="nowrap"><a href="order-details.php?id=<?= (int)$r['order_id'] ?>"><?= e(orderPublicId((int)$r['order_id'])) ?></a></td>
                    <td><?= e((string)$r['buyer_name']) ?></td>
                    <td><?= e((string)($r['destination_district'] ?: '—')) ?></td>
                    <td class="nowrap"><?= e(date('d M Y', strtotime((string)$r['created_at']))) ?></td>
                    <td class="nowrap"><?= farmer_money($r['product_subtotal']) ?></td>
                    <td><span class="status-badge <?= e(farmer_status_tone((string)$r['order_status'])) ?>"><?= e(farmer_status_label((string)$r['order_status'])) ?></span></td>
                    <td>
                        <a class="btn secondary small nowrap" href="order-details.php?id=<?= (int)$r['order_id'] ?>">View</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?php page_bottom(); ?>
