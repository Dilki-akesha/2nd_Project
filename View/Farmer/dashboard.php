<?php
 require 'includes/auth.php';
 require 'includes/layout.php';

 //product stats
 $st=$conn->prepare("
    SELECT COUNT(*) products,
        SUM(listing_status='ACTIVE') active,
        SUM(available_quantity<=10) low_stock 
    FROM products 
    WHERE farmer_id=?
");
 $st->bind_param('i',$farmer_id);
 $st->execute();
 $ps=$st->get_result()->fetch_assoc();

 //order stats
 $st=$conn->prepare("
    SELECT COUNT(*) orders,
        SUM(order_status IN ('PAID','ACCEPTED','PREPARING','READY_FOR_DELIVERY')) action_orders,
        COALESCE(SUM(
            CASE 
                WHEN order_status IN ('DELIVERED','COMPLETED') 
                THEN product_subtotal-farmer_marketplace_fee 
                ELSE 0 
            END
        ),0) sales 
    FROM orders 
    WHERE farmer_id=?
");
 $st->bind_param('i',$farmer_id);
 $st->execute();
 $os=$st->get_result()->fetch_assoc();

 //recent orders
 $st=$conn->prepare('
    SELECT order_id,order_status,product_subtotal,created_at 
    FROM orders 
    WHERE farmer_id=? 
    ORDER BY created_at DESC 
    LIMIT 5
');
 $st->bind_param('i',$farmer_id);
 $st->execute();
 $orders=$st->get_result();
 
 page_top('Farmer Dashboard','dashboard');
 ?>
 
 <div class="page-title">
    <div>
        <h1>Farmer Dashboard</h1>
        <p>Live summary from the Harvestly database.</p>
    </div>
</div>


<div class="dashboard-stats">
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">▦</div>
        <div>
            <p>Products</p>
            <h3><?=e($ps['products'])?></h3>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">✓</div>
        <div>
            <p>Active Listings</p>
            <h3><?=e($ps['active'])?></h3>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">▣</div>
        <div>
            <p>Orders Needing Action</p>
            <h3><?=e($os['action_orders'])?></h3>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">₨</div>
        <div>
            <p>Delivered/Completed Sales</p>
            <h3><?=money($os['sales'])?></h3>
        </div>
    </div>
</div>
</div>
<div class="card">
    <div class="section-head">
        <h2>Recent Orders</h2>
        <a href="orders.php">View all</a>
    </div>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Date</th>
                    <th>Subtotal</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while($r=$orders->fetch_assoc()):?>
                    <tr>
                        <td>
                            <a href="order-details.php?id=<?=$r['order_id']?>">#HV<?=$r['order_id']?></a>
                        </td>
                        <td><?=e($r['created_at'])?></td>
                        <td><?=money($r['product_subtotal'])?></td>
                        <td>
                            <span class="badge"><?=e($r['order_status'])?></span>
                        </td>
                    </tr>
                <?php endwhile;?>
            </tbody>
        </table>
    </div>
</div>
<?php page_bottom();?>