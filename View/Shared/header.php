<?php
/**
 * Harvestly shared header.
 * Public pages and the Admin console. Absolute asset URLs so the header works
 * from any entry point. No external CDN, font or icon service is referenced.
 */
if (!defined('HARVESTLY_INC')) {
    define('HARVESTLY_INC', true);
}

$currentPage = (string)($_GET['page'] ?? 'landing');
$isAdminPage = strpos($currentPage, 'admin_') === 0;
$sessionRole = strtolower((string)($_SESSION['role'] ?? ''));
$loggedIn = isset($_SESSION['user_id']) && $sessionRole !== '';

/* The landing page for each signed-in role. */
$roleHome = [
    'admin'   => 'index.php?page=admin_overview',
    'buyer'   => 'Controller/Buyer/DashboardController.php',
    'farmer'  => 'View/Farmer/dashboard.php',
    'courier' => 'Controller/Courier/CourierController.php?page=dashboard',
];
$roleLabel = [
    'admin'   => 'Admin',
    'buyer'   => 'Buyer',
    'farmer'  => 'Farmer',
    'courier' => 'Courier Partner',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isAdminPage ? 'Harvestly Admin Console' : 'Harvestly - Sri Lanka Farmer-to-Buyer Marketplace' ?></title>
    <link rel="stylesheet" href="<?= e(url('CSS/Common/style.css')) ?>">
    <link rel="icon" href="<?= e(url('assets/harvestly-logo.jpeg')) ?>">
    <meta name="csrf-token" content="<?= e(csrfToken()) ?>">
    <meta name="app-base" content="<?= e(BASE_URL) ?>">
    <script src="<?= e(url('JS/Common/session.js')) ?>"></script>
</head>
<body>

<?php if (!$isAdminPage): ?>
<header class="public-header no-print">
    <div class="public-header-inner">
        <a href="index.php" class="logo-brand">
            <img src="<?= e(url('assets/harvestly-logo.jpeg')) ?>" alt="Harvestly" style="height:44px;width:auto;border-radius:var(--radius-md);object-fit:contain">
            <span class="logo-text-group">
                <span class="logo-title">Harvestly</span>
                <span class="logo-subtitle">Fresh Local Market</span>
            </span>
        </a>

        <nav class="nav-links">
            <a href="index.php" class="nav-pill <?= $currentPage === 'landing' ? 'active' : '' ?>">Home</a>
            <a href="index.php?page=products" class="nav-pill <?= $currentPage === 'products' ? 'active' : '' ?>">Browse Products</a>
            <a href="index.php#how-it-works" class="nav-pill">How It Works</a>
            <a href="index.php#about-us" class="nav-pill">About Us</a>
        </nav>

        <div class="header-actions">
            <?php if ($loggedIn): ?>
                <a href="<?= e(url($roleHome[$sessionRole] ?? 'index.php')) ?>" class="btn btn-primary btn-sm">
                    <?= e($roleLabel[$sessionRole] ?? 'My Account') ?> Dashboard
                </a>
                <form action="index.php?action=logout" method="POST" style="display:inline">
                    <?= csrfField() ?>
                    <button type="submit" class="btn btn-outline btn-sm">Logout</button>
                </form>
            <?php else: ?>
                <a href="index.php?page=login" class="btn btn-outline">Login</a>
                <a href="index.php?page=role_select" class="btn btn-primary">Sign Up</a>
            <?php endif; ?>
        </div>
    </div>
</header>
<?php else: ?>
<div class="topbar no-print">
    <button class="admin-menu-toggle no-print" id="adminMenuToggle" type="button" aria-label="Toggle Admin navigation" aria-expanded="false" aria-controls="adminSidebar">
        <span aria-hidden="true">&#9776;</span>
    </button>
    <div class="topbar-title">Harvestly Administrative Operations Console</div>
    <div class="topbar-actions">
        <span class="badge badge-success">Administrator Mode</span>
        <form action="index.php?action=logout" method="POST" style="display:inline">
            <?= csrfField() ?>
            <button type="submit" class="btn btn-outline btn-sm">Logout</button>
        </form>
    </div>
</div>
<div class="admin-sidebar-overlay no-print" id="adminSidebarOverlay"></div>
<?php endif; ?>
