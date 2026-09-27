<?php
require_once __DIR__ . '/../../config/app.php';
if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) redirect('Controller/Buyer/OrdersController.php');
require __DIR__ . '/includes/layout.php';
buyer_page_top('My Orders', 'OrdersController.php');
?>

<section class="buyer-title">
    <div>
        <h2>My Orders</h2>
        <p>View your order history, delivery progress and receipt confirmation.</p>
    </div>
    <div class="buyer-actions">
        <a class="buyer-button buyer-secondary" href="<?= e(buyerRoute('ProductController.php')) ?>">Browse Products</a>
    </div>
</section>

<?php buyerFlash(); ?>

<?php if (!$orders): ?>
<section class="buyer-panel buyer-empty">
    You have no orders yet. <a href="<?= e(buyerRoute('ProductController.php')) ?>">Browse products</a> to place your first order.
</section>
<?php else: ?>

<section class="buyer-panel">
    <div class="buyer-table-wrap">
        <table class="buyer-table">
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Date</th>
                    <th>Destination</th>
                    <th>Items</th>
                    <th>Status</th>
                    <th class="buyer-num">Total</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($orders as $order): ?>
                <tr>
                    <td class="buyer-nowrap"><?= e($order['id']) ?></td>
                    <td class="buyer-nowrap"><?= e($order['date']) ?></td>
                    <td><?= e($order['destination_district'] ?: '—') ?></td>
                    <td>
                        <?php foreach ($order['items'] as $item): ?>
                        <div><?= e($item['name']) ?> &times; <?= e(rtrim(rtrim(number_format((float)$item['quantity'], 3), '0'), '.')) ?> <?= e($item['unit']) ?></div>
                        <?php endforeach; ?>
                    </td>
                    <td><?= buyerStatusBadge((string)$order['status_code']) ?></td>
                    <td class="buyer-num"><?= e(harvestlyMoney($order['total'])) ?></td>
                    <td>
                        <div class="buyer-actions-cell">
                            <a class="buyer-button buyer-button--ghost buyer-button--small"
                               href="<?= e(buyerRoute('OrderTrackingController.php', 'id=' . (int)$order['db_id'])) ?>">
                                <?= $order['status_code'] === 'DELIVERED' ? 'Confirm Received' : 'View Order' ?>
                            </a>
                            <a class="buyer-button buyer-secondary buyer-button--small"
                               href="<?= e(buyerRoute('FeedbackController.php', 'order_id=' . (int)$order['db_id'])) ?>">
                                Reviews / Issue
                            </a>
                            <?php if (in_array($order['status_code'], ['PENDING_PAYMENT', 'PAID'], true)): ?>
                            <form method="post" action="<?= e(buyerRoute('OrdersController.php')) ?>"
                                  data-confirm="Cancel order <?= e($order['id']) ?>?">
                                <?= csrfField() ?>
                                <input type="hidden" name="order_id" value="<?= (int)$order['db_id'] ?>">
                                <button class="buyer-button buyer-danger buyer-button--small" type="submit">Cancel</button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<?php endif; ?>

<?php buyer_page_bottom(); ?>
