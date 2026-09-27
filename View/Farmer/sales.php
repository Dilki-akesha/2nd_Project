<?php
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/layout.php';

$sales = $farmerModel->sales($farmer_id);
$feePercent = (float)db_setting('farmer_marketplace_fee_percent', 0);

$totalSubtotal = 0.0;
$totalFee = 0.0;
$totalNet = 0.0;
foreach ($sales as $row) {
    $totalSubtotal += (float)$row['product_subtotal'];
    $totalFee += (float)$row['farmer_marketplace_fee'];
    $totalNet += (float)$row['product_subtotal'] - (float)$row['farmer_marketplace_fee'];
}

page_top('Sales', 'sales');
?>

<div class="page-title">
    <div>
        <h1>Sales / Order History</h1>
        <p>Delivered and completed sales for your farm.</p>
    </div>
    <a class="btn secondary" href="earnings.php">View Earnings &amp; Payouts</a>
</div>

<div class="stats-grid">
    <div class="stat-card"><div class="stat-icon">▦</div><div><p>Sales Orders</p><h3><?= count($sales) ?></h3></div></div>
    <div class="stat-card"><div class="stat-icon">₨</div><div><p>Product Subtotal</p><h3><?= farmer_money($totalSubtotal) ?></h3></div></div>
    <div class="stat-card"><div class="stat-icon">%</div><div><p>Marketplace Fee (<?= e(number_format($feePercent, 2)) ?>%)</p><h3><?= farmer_money($totalFee) ?></h3></div></div>
    <div class="stat-card"><div class="stat-icon">✓</div><div><p>Your Net Earnings</p><h3><?= farmer_money($totalNet) ?></h3></div></div>
</div>

<div class="card">
    <div class="section-head"><h2>Sales Ledger</h2></div>
    <?php if (!$sales): ?>
        <p class="empty">No delivered or completed sales yet. Sales appear here once an order reaches Delivered.</p>
    <?php else: ?>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Buyer</th>
                    <th>Date</th>
                    <th>Product Subtotal</th>
                    <th>Marketplace Fee</th>
                    <th>Your Amount</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($sales as $r): ?>
                <tr>
                    <td class="nowrap"><a href="order-details.php?id=<?= (int)$r['order_id'] ?>"><?= e(orderPublicId((int)$r['order_id'])) ?></a></td>
                    <td><?= e((string)$r['buyer_name']) ?></td>
                    <td class="nowrap"><?= e(date('d M Y', strtotime((string)$r['created_at']))) ?></td>
                    <td class="nowrap"><?= farmer_money($r['product_subtotal']) ?></td>
                    <td class="nowrap"><?= farmer_money($r['farmer_marketplace_fee']) ?></td>
                    <td class="nowrap"><strong><?= farmer_money((float)$r['product_subtotal'] - (float)$r['farmer_marketplace_fee']) ?></strong></td>
                    <td><span class="status-badge ok"><?= e(farmer_status_label((string)$r['order_status'])) ?></span></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <p class="muted mt">
        Your amount is the product subtotal minus the configurable Farmer marketplace fee
        (<?= e(number_format($feePercent, 2)) ?>%). The delivery fee is paid to the Courier Partner,
        and the Buyer service fee is collected from the Buyer. The split is configurable, never hard-coded.
    </p>
    <?php endif; ?>
</div>

<?php page_bottom(); ?>
