<?php
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/layout.php';

$earnings = $farmerModel->earnings($farmer_id);
$totals = $earnings['totals'];
$rows = $earnings['rows'];

page_top('Earnings', 'earnings');
?>

<div class="page-title">
    <div>
        <h1>Earnings &amp; Payout History</h1>
        <p>Earnings generated from valid paid orders, and their recorded payout status.</p>
    </div>
    <a class="btn secondary" href="sales.php">View Sales</a>
</div>

<div class="stats-grid">
    <div class="stat-card"><div class="stat-icon">₨</div><div><p>Total Recorded Earnings</p><h3><?= farmer_money($totals['pending_payout'] + $totals['paid'] + $totals['held']) ?></h3></div></div>
    <div class="stat-card"><div class="stat-icon">◷</div><div><p>Pending Payout</p><h3><?= farmer_money($totals['pending_payout']) ?></h3></div></div>
    <div class="stat-card"><div class="stat-icon">✓</div><div><p>Paid</p><h3><?= farmer_money($totals['paid']) ?></h3></div></div>
    <div class="stat-card"><div class="stat-icon">▣</div><div><p>Held (not yet eligible)</p><h3><?= farmer_money($totals['held']) ?></h3></div></div>
</div>

<div class="card">
    <div class="section-head"><h2>Earnings History</h2></div>
    <?php if (!$rows): ?>
        <p class="empty">
            No earnings recorded yet. An earning is created when you accept an order that has a
            successful payment.
        </p>
    <?php else: ?>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr><th>Order</th><th>Your Amount</th><th>Status</th><th>Recorded</th><th>Settlement Date</th></tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td class="nowrap"><a href="order-details.php?id=<?= (int)$r['order_id'] ?>"><?= e(orderPublicId((int)$r['order_id'])) ?></a></td>
                    <td class="nowrap"><?= farmer_money($r['amount']) ?></td>
                    <td><span class="status-badge <?= e(farmer_status_tone((string)$r['earning_status'])) ?>"><?= e(farmer_status_label((string)$r['earning_status'])) ?></span></td>
                    <td class="nowrap"><?= e(date('d M Y', strtotime((string)$r['created_at']))) ?></td>
                    <td class="nowrap"><?= e((string)($r['settled_at'] ?: '—')) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <p class="notice mt">
        <strong>Simulated accounting only.</strong> Harvestly records payout status inside its own
        database. There is no real bank transfer API and no bank details are collected. A weekly
        settlement moves eligible <em>Pending Payout</em> earnings to <em>Paid</em> and stores the
        settlement date.
    </p>
    <?php endif; ?>
</div>

<?php page_bottom(); ?>
