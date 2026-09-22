<?php
require_once __DIR__ . '/includes/auth.php';
$notification_count = 0;
$stmt = $conn->prepare(
  'SELECT COUNT(*) AS total 
  FROM notifications 
  WHERE user_id=? AND is_read=0
');

$stmt->bind_param('i', $farmer_id);
$stmt->execute();
$notification_count = (int)($stmt->get_result()->fetch_assoc()['total'] ?? 0);
?>


<aside class="sidebar">
  <div class="brand harvestly-brand">
    <img src="assets/harvestly-logo.jpg" alt="Harvestly" class="site-logo">
  </div>

  <nav class="nav" id="farmerNav">
    <a href="dashboard.php" data-page="dashboard">Dashboard</a>
    <a href="products.php" data-page="products">Products</a>
    <a href="inventory.php" data-page="inventory">Inventory</a>
    <a href="orders.php" data-page="orders">Orders</a>
    <a href="harvest-soon.php" data-page="harvest-soon">Pre-Listings / Harvest Soon</a>
    <a href="sales.php" data-page="sales">Sales / Order History</a>
    <a href="earnings.php" data-page="earnings">Earnings</a>
    <a href="reviews.php" data-page="reviews">Reviews</a>
    <a href="report-issue.php" data-page="report-issue">Report Issue</a>
    <a href="notifications.php" data-page="notifications">Notifications<?php if($notification_count > 0): ?><span class="nav-count"><?= $notification_count ?></span><?php endif; ?></a>
    <a href="profile.php" data-page="profile">Profile</a>
  </nav>
  <div class="bottom-nav nav"><a href="logout.php">Logout</a></div>
</aside>
