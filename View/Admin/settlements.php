<?php
/**
 * Admin - Earnings & Payout Monitoring.
 * All figures come from the earnings / settlement_items tables. This is a
 * simulated local accounting workflow and never a real bank transfer.
 */
$summary = $summary ?? [
    'pending_payout' => 0.0,
    'held' => 0.0,
    'paid' => 0.0,
    'by_type' => [],
    'platform_fees' => 0.0,
    'platform_share_percent' => 0.0,
];
$typeLabel = [
    'farmer' => 'Farmer',
    'courier_partner' => 'Courier Partner',
    'platform' => 'Platform',
];
?>
<div class="page-content">
    <div class="section-header">
        <div class="section-title-group">
            <h1>Earnings &amp; Payout Monitoring</h1>
            <p>Farmer earnings, Courier Partner delivery fees, recorded platform fees and settlement history.</p>
        </div>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success"><?= sanitize($_GET['success']); ?></div>
    <?php endif; ?>

    <div class="kpi-grid" style="margin-bottom:24px">
        <div class="kpi-card">
            <div class="kpi-icon-box">&#128176;</div>
            <div class="kpi-data">
                <span class="kpi-value"><?= harvestlyMoney($summary['pending_payout']) ?></span>
                <span class="kpi-label">Pending Payout</span>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon-box">&#128206;</div>
            <div class="kpi-data">
                <span class="kpi-value"><?= harvestlyMoney($summary['held']) ?></span>
                <span class="kpi-label">Held (not yet eligible)</span>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon-box">&#10003;</div>
            <div class="kpi-data">
                <span class="kpi-value"><?= harvestlyMoney($summary['paid']) ?></span>
                <span class="kpi-label">Recorded as Paid</span>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon-box">&#37;</div>
            <div class="kpi-data">
                <span class="kpi-value"><?= e(number_format((float)$summary['platform_share_percent'], 2)) ?>%</span>
                <span class="kpi-label">Platform Fees Retained (<?= harvestlyMoney($summary['platform_fees']) ?>)</span>
            </div>
        </div>
    </div>

    <div class="card" style="margin-bottom:22px">
        <h2 style="font-size:17px;margin:0 0 14px">Earnings by Beneficiary</h2>
        <?php if (!$summary['by_type']): ?>
            <p class="text-sm text-muted" style="margin:0">No earnings recorded yet.</p>
        <?php else: ?>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr><th>Beneficiary</th><th>Entries</th><th>Total</th></tr>
                </thead>
                <tbody>
                <?php foreach ($summary['by_type'] as $row): ?>
                    <tr>
                        <td><?= e($typeLabel[strtolower((string)$row['beneficiary_type'])] ?? (string)$row['beneficiary_type']) ?></td>
                        <td><?= (int)$row['entries'] ?></td>
                        <td><strong><?= harvestlyMoney($row['total']) ?></strong></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
        <p class="text-sm text-muted" style="margin:14px 0 0">
            Fees are configurable from
            <a href="index.php?page=admin_settings">Platform Settings</a> and are never hard-coded.
        </p>
    </div>

    <div class="card-table-wrapper">
        <div class="table-toolbar">
            <div class="search-box">
                <input type="text" placeholder="Search settlement reference" data-table-search="settlements-table">
            </div>
        </div>

        <table class="data-table" id="settlements-table">
            <thead>
                <tr>
                    <th>Reference</th>
                    <th>Beneficiary</th>
                    <th>User</th>
                    <th>Amount</th>
                    <th>Settlement Status</th>
                    <th>Settlement Date</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$settlements): ?>
                <tr>
                    <td colspan="6" style="text-align:center;padding:32px;color:var(--color-outline)">
                        No settlement records yet. Run <code>scripts/weekly_settlements.php</code> to
                        settle eligible Pending Payout earnings.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($settlements as $s):
                    $type = strtolower((string)$s['user_type']);
                ?>
                <tr>
                    <td><code><?= sanitize($s['reference_no']) ?></code></td>
                    <td>
                        <span class="badge badge-info">
                            <?= e($typeLabel[$type] ?? (string)$s['user_type']) ?>
                        </span>
                    </td>
                    <td>#USR-<?= sprintf('%04d', (int)$s['user_id']) ?></td>
                    <td><strong><?= harvestlyMoney($s['amount']) ?></strong></td>
                    <td>
                        <span class="badge <?= strtolower((string)$s['status']) === 'completed' ? 'badge-success' : 'badge-pending' ?>">
                            <?= sanitize(harvestlyStatusLabel((string)$s['status'])) ?>
                        </span>
                    </td>
                    <td><?= date('d M Y', strtotime((string)$s['settlement_date'])) ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="card" style="margin-top:20px">
        <p class="text-sm text-muted" style="margin:0">
            <strong>Simulated accounting only.</strong> Harvestly records payout status inside its own
            database. There is no real bank transfer API, no bank account collection and no payment
            gateway integration. A weekly settlement moves eligible <em>Pending Payout</em> earnings to
            <em>Paid</em> and stores the settlement date.
        </p>
    </div>
</div>
