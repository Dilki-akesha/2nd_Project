<?php
require_once __DIR__ . '/includes/auth.php';

/*
 * sidebar.php can be included from page_top(), which has its own local scope,
 * so the shared authenticated Farmer values are resolved explicitly.
 */
$farmer_id = (int)($farmer_id ?? ($GLOBALS['farmer_id'] ?? currentFarmerId()));
$farmerModel = $farmerModel ?? new FarmerModel();
$notification_count = $farmerModel->unreadNotifications($farmer_id);
$activePage = $GLOBALS['activePage'] ?? '';

$farmerNav = [
    'dashboard'     => ['dashboard.php',     'Dashboard'],
    'products'      => ['products.php',      'My Products'],
    'inventory'     => ['inventory.php',     'Inventory & Stock'],
    'orders'        => ['orders.php',        'Orders'],
    'harvest-soon'  => ['harvest-soon.php',  'Pre-Listings / Harvest Soon'],
    'sales'         => ['sales.php',         'Sales'],
    'earnings'      => ['earnings.php',      'Earnings & Payouts'],
    'reviews'       => ['reviews.php',       'Reviews'],
    'report-issue'  => ['report-issue.php',  'Report Issue'],
    'notifications' => ['notifications.php', 'Notifications'],
    'profile'       => ['profile.php',       'Profile'],
];
?>

<aside class="sidebar">
  <div class="brand harvestly-brand">
    <img src="../../assets/harvestly-logo.jpeg" alt="Harvestly" class="site-logo">
  </div>
  <p class="role-caption">Farmer Workspace</p>

  <nav class="nav" id="farmerNav" aria-label="Farmer navigation">
    <?php foreach ($farmerNav as $key => [$file, $label]): ?>
    <a href="<?= e($file) ?>" data-page="<?= e($key) ?>"
       class="<?= $activePage === $key ? 'active' : '' ?>"
       <?= $activePage === $key ? 'aria-current="page"' : '' ?>><?= e($label) ?><?php
        if ($key === 'notifications' && $notification_count > 0): ?>
      <span class="nav-count"><?= $notification_count ?></span>
    <?php endif; ?></a>
    <?php endforeach; ?>
  </nav>

  <div class="bottom-nav">
    <form method="post" action="<?= e(url('index.php?action=logout')) ?>">
      <?= csrfField() ?>
      <button type="submit" class="nav-logout">Logout</button>
    </form>
  </div>
</aside>
