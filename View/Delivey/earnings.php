<?php
$earnings = $data['earnings'] ?? [];
$totals = $data['totals'] ?? ['held' => 0, 'pending_payout' => 0, 'paid' => 0];
?>

<div class="courier-card">
    <h3>Earnings &amp; Payouts</h3>
</div>

<div class="courier-grid-auto mb">
    <div class="courier-card" style="margin:0">
        <p class="courier-muted">Held</p>
        <h2 style="margin:4px 0"><?= harvestlyMoney($totals['held']) ?></h2>
    </div>
    <div class="courier-card" style="margin:0">
        <p class="courier-muted">Pending Payout</p>
        <h2 style="margin:4px 0"><?= harvestlyMoney($totals['pending_payout']) ?></h2>
    </div>
    <div class="courier-card" style="margin:0">
        <p class="courier-muted">Paid</p>
        <h2 style="margin:4px 0"><?= harvestlyMoney($totals['paid']) ?></h2>
    </div>
</div>

<div class="courier-card">
    <h3>Payout History</h3>
    <?php if (!$earnings): ?>
    <div class="courier-empty">No delivery fee earnings recorded yet. Accept an assignment to start earning.</div>
    <?php else: ?>
    <div class="courier-table-wrap">
        <table class="courier-table">
            <thead>
                <tr><th>Order</th><th>Amount</th><th>Status</th><th>Recorded</th><th>Settled</th></tr>
            </thead>
            <tbody>
            <?php foreach ($earnings as $r): ?>
            <tr>
                <td class="courier-nowrap"><?= e(orderPublicId((int)$r['order_id'])) ?></td>
                <td class="courier-nowrap"><?= harvestlyMoney($r['amount']) ?></td>
                <td>
                    <span class="courier-badge <?= e(harvestlyStatusTone((string)$r['earning_status'])) ?>">
                        <?= e(harvestlyStatusLabel((string)$r['earning_status'])) ?>
                    </span>
                </td>
                <td class="courier-nowrap"><?= e(date('d M Y', strtotime((string)$r['created_at']))) ?></td>
                <td class="courier-nowrap"><?= e((string)($r['settled_at'] ?: '—')) ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<div class="courier-card">
    <div class="courier-notice warn">
        <strong>Payments via PayHere Sandbox.</strong> Payouts are released through the PayHere
        Sandbox gateway. Sandbox approval is still pending, so settlements are recorded in Harvestly
        and no live bank transfer is made until approval is granted.
    </div>
</div>
