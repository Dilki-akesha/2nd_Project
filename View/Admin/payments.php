<?php
/**
 * Admin - Payment Monitoring.
 *
 * PayHere Sandbox is the planned payment provider but approval has NOT been
 * received, so nothing here represents a completed gateway transaction.
 * Harvestly never collects or stores a card number or CVV. When PayHere is
 * enabled, a payment may only be marked successful after server-side
 * verification of the gateway notification.
 */
$statusTone = [
    'SUCCESS'   => 'badge-success',
    'PENDING'   => 'badge-warning',
    'FAILED'    => 'badge-danger',
    'CANCELLED' => 'badge-danger',
];
?>
<div class="view-container">
    <div class="page-header mb-4">
        <h1>Payment Monitoring</h1>
        <p class="text-sm text-muted">Order payment records held in the Harvestly database.</p>
    </div>

    <div class="alert alert-warn mb-4">
        <strong>Payments via PayHere Sandbox.</strong> Sandbox approval is still pending, so orders
        stay in <em>Pending Payment</em> until approval is granted. Harvestly never requests, collects
        or stores a card number or CVV.
    </div>

    <div class="card" style="padding:0;overflow:hidden">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Payment</th>
                        <th>Order</th>
                        <th>Buyer</th>
                        <th>Provider</th>
                        <th>Gateway Reference</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Recorded</th>
                        <th>Paid At</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!$payments): ?>
                    <tr>
                        <td colspan="9" style="text-align:center;padding:32px;color:var(--color-outline)">
                            No payment records found.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($payments as $pay):
                        $st = strtoupper((string)$pay['payment_status']);
                    ?>
                    <tr>
                        <td><strong>#PAY-<?= sprintf('%04d', (int)$pay['id']) ?></strong></td>
                        <td><strong><?= sanitize($pay['order_number']) ?></strong></td>
                        <td><?= sanitize($pay['buyer_name']) ?></td>
                        <td class="text-xs"><?= sanitize((string)($pay['provider'] ?? 'LOCAL_DEMO')) ?></td>
                        <td class="text-xs">
                            <?php if (!empty($pay['provider_reference'])): ?>
                                <code><?= sanitize($pay['provider_reference']) ?></code>
                            <?php else: ?>
                                <span class="text-muted">Not available</span>
                            <?php endif; ?>
                        </td>
                        <td><strong><?= harvestlyMoney($pay['amount']) ?></strong></td>
                        <td>
                            <span class="badge <?= $statusTone[$st] ?? 'badge-pending' ?>">
                                <?= sanitize(harvestlyStatusLabel($st)) ?>
                            </span>
                        </td>
                        <td class="nowrap text-xs"><?= date('d M Y H:i', strtotime((string)$pay['created_at'])) ?></td>
                        <td class="nowrap text-xs">
                            <?= !empty($pay['paid_at']) ? date('d M Y H:i', strtotime((string)$pay['paid_at'])) : '—' ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
