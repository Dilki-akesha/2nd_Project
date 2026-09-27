<?php
if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) redirect('Controller/Buyer/DashboardController.php');
require __DIR__ . '/includes/layout.php';
buyer_page_top('Dashboard', 'DashboardController.php');
?>

<section class="buyer-title">
    <div>
        <h2>Welcome back, <?= e($buyerName) ?></h2>
        <p>Your fresh produce, orders and deliveries in one place.</p>
    </div>
    <div class="buyer-actions">
        <a class="buyer-button" href="<?= e(buyerRoute('ProductController.php')) ?>">
            <span class="material-symbols-outlined" aria-hidden="true">shopping_bag</span>
            <span>Browse Products</span>
        </a>
    </div>
</section>

<?php buyerFlash(); ?>

<?php if ((int)$buyerData['awaitingConfirmation'] > 0): ?>
<div class="buyer-alert warn">
    <strong>Action needed</strong>
    <?= (int)$buyerData['awaitingConfirmation'] ?> of your delivered <?= (int)$buyerData['awaitingConfirmation'] === 1 ? 'order is' : 'orders are' ?>
    waiting for you to confirm receipt. Orders complete automatically after
    <?= (int)db_setting('buyer_confirmation_hours', 48) ?> hours.
    <a href="<?= e(buyerRoute('OrdersController.php')) ?>">Review <?= (int)$buyerData['awaitingConfirmation'] === 1 ? 'it' : 'them' ?> now</a>
</div>
<?php endif; ?>

<div class="buyer-metrics">
    <a class="buyer-metric" href="<?= e(buyerRoute('OrdersController.php')) ?>">
        <span class="buyer-metric-icon material-symbols-outlined" aria-hidden="true">local_shipping</span>
        <span>
            <strong><?= (int)$buyerData['activeOrders'] ?></strong>
            <span>Active orders</span>
        </span>
    </a>
    <a class="buyer-metric" href="<?= e(buyerRoute('OrdersController.php')) ?>">
        <span class="buyer-metric-icon material-symbols-outlined" aria-hidden="true">done_all</span>
        <span>
            <strong><?= (int)$buyerData['completedOrders'] ?></strong>
            <span>Completed orders</span>
        </span>
    </a>
    <a class="buyer-metric" href="<?= e(buyerRoute('CartController.php')) ?>">
        <span class="buyer-metric-icon material-symbols-outlined" aria-hidden="true">shopping_cart</span>
        <span>
            <strong><?= (int)$buyerData['cartCount'] ?></strong>
            <span>Items in your cart</span>
        </span>
    </a>
    <a class="buyer-metric" href="<?= e(buyerRoute('NotificationsController.php')) ?>">
        <span class="buyer-metric-icon material-symbols-outlined" aria-hidden="true">notifications</span>
        <span>
            <strong><?= (int)$buyerData['notificationCount'] ?></strong>
            <span>Unread notifications</span>
        </span>
    </a>
</div>

<section class="buyer-panel">
    <h3>Find fresh produce</h3>
    <form class="buyer-search" method="get" action="<?= e(buyerRoute('ProductController.php')) ?>">
        <input type="search" name="search" aria-label="Search products or farmers" placeholder="Search products or farmers">
        <button class="buyer-button" type="submit">
            <span class="material-symbols-outlined" aria-hidden="true">search</span>
            <span>Search</span>
        </button>
    </form>
</section>

<div class="buyer-columns">
    <section class="buyer-panel">
        <h3>Recent orders</h3>
        <?php if (!$buyerData['recentOrders']): ?>
            <div class="buyer-empty">You have no orders yet. Browse products to start your first order.</div>
        <?php else: ?>
            <div class="buyer-table-wrap">
                <table class="buyer-table">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Farmer</th>
                            <th>Destination</th>
                            <th>Status</th>
                            <th class="buyer-num">Total</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($buyerData['recentOrders'] as $order): ?>
                        <tr>
                            <td class="buyer-nowrap"><?= e(orderPublicId((int)$order['order_id'])) ?></td>
                            <td><?= e($order['farmer_name']) ?></td>
                            <td><?= e($order['destination_district'] ?: '—') ?></td>
                            <td><?= buyerStatusBadge((string)$order['order_status']) ?></td>
                            <td class="buyer-num"><?= e(harvestlyMoney($order['grand_total'])) ?></td>
                            <td>
                                <a class="buyer-button buyer-button--ghost buyer-button--small"
                                   href="<?= e(buyerRoute('OrderTrackingController.php', 'id=' . (int)$order['order_id'])) ?>">View</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="buyer-actions" style="margin-top:16px">
                <a class="buyer-button buyer-secondary buyer-small" href="<?= e(buyerRoute('OrdersController.php')) ?>">View all orders</a>
            </div>
        <?php endif; ?>
    </section>

    <section class="buyer-panel">
        <h3>Your account</h3>
        <div class="buyer-quicklinks">
            <a href="<?= e(buyerRoute('OrdersController.php')) ?>">
                <span>Order history &amp; delivery progress</span>
                <span class="material-symbols-outlined" aria-hidden="true">chevron_right</span>
            </a>
            <a href="<?= e(buyerRoute('FeedbackController.php')) ?>">
                <span>Reviews &amp; report an issue</span>
                <span class="material-symbols-outlined" aria-hidden="true">chevron_right</span>
            </a>
            <a href="<?= e(buyerRoute('ProfileController.php')) ?>">
                <span>Manage your profile</span>
                <span class="material-symbols-outlined" aria-hidden="true">chevron_right</span>
            </a>
        </div>
    </section>
</div>

<?php if ($buyerData['recentProducts']): ?>
<section class="buyer-panel">
    <h3>Newly listed produce</h3>
    <div class="buyer-product-grid">
        <?php foreach ($buyerData['recentProducts'] as $product): ?>
        <article class="buyer-product">
            <?php if (!empty($product['image_path'])): ?>
                <img src="<?= e(url($product['image_path'])) ?>" alt="<?= e($product['product_name']) ?>">
            <?php endif; ?>
            <div class="buyer-product-content">
                <h3><a href="<?= e(buyerRoute('ProductDetailsController.php', 'id=' . (int)$product['product_id'])) ?>"><?= e($product['product_name']) ?></a></h3>
                <p class="buyer-price"><?= e(harvestlyMoney($product['unit_price'])) ?> / <?= e($product['unit_label']) ?></p>
                <div class="buyer-product-foot">
                    <a class="buyer-button buyer-secondary buyer-small" href="<?= e(buyerRoute('ProductDetailsController.php', 'id=' . (int)$product['product_id'])) ?>">View</a>
                </div>
            </div>
        </article>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>


<?php buyer_page_bottom(); ?>
