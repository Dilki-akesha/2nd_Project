<?php
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/layout.php';

$productStats = $farmerModel->productStats($farmer_id);
$orderStats = $farmerModel->orderStats($farmer_id);
$recentOrders = $farmerModel->recentOrders($farmer_id, 6);

page_top('Farmer Dashboard', 'dashboard');
?>

<div class="page-title">
    <div>
        <h1>Farmer Dashboard</h1>
        <p>Live summary from the Harvestly database.</p>
    </div>
    <div class="row">
        <a class="btn secondary" href="orders.php">View Orders</a>
        <a class="btn" href="add-product.php">+ Add Product</a>
    </div>
</div>

<div class="stats-grid">
    <a class="stat-card" href="products.php">
        <div class="stat-icon">▦</div>
        <div>
            <p>Products</p>
            <h3><?= (int)$productStats['total'] ?></h3>
        </div>
    </a>
    <a class="stat-card" href="products.php">
        <div class="stat-icon">✓</div>
        <div>
            <p>Active Listings</p>
            <h3><?= (int)$productStats['active'] ?></h3>
        </div>
    </a>
    <a class="stat-card" href="orders.php">
        <div class="stat-icon">▣</div>
        <div>
            <p>Orders Needing Action</p>
            <h3><?= (int)$orderStats['action_required'] ?></h3>
        </div>
    </a>
    <a class="stat-card" href="sales.php">
        <div class="stat-icon">₨</div>
        <div>
            <p>Delivered / Completed Sales</p>
            <h3><?= farmer_money($orderStats['sales']) ?></h3>
        </div>
    </a>
</div>

<div class="card">
    <div class="section-head">
        <h2>Recent Orders</h2>
        <a href="orders.php">View all</a>
    </div>
    <?php if (!$recentOrders): ?>
        <p class="empty">You have no orders yet. Your orders will appear here once a Buyer checks out.</p>
    <?php else: ?>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Buyer</th>
                    <th>Date</th>
                    <th>Subtotal</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($recentOrders as $r): ?>
                <tr>
                    <td><a href="order-details.php?id=<?= (int)$r['order_id'] ?>"><?= e(orderPublicId((int)$r['order_id'])) ?></a></td>
                    <td><?= e((string)$r['buyer_name']) ?></td>
                    <td><?= e(date('d M Y', strtotime((string)$r['created_at']))) ?></td>
                    <td><?= farmer_money($r['product_subtotal']) ?></td>                    <td><span class="status-badge <?= e(farmer_status_tone((string)$r['order_status'])) ?>"><?= e(farmer_status_label((string)$r['order_status'])) ?></span></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?php page_bottom(); ?>
