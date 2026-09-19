<?php
/**
 * Shared Header Component
 */
if (!defined('HARVESTLY_INC')) {
    define('HARVESTLY_INC', true);
}
$currentPage = isset($_GET['page']) ? sanitize($_GET['page']) : 'landing';
$isAdminPage = in_array($currentPage, ['admin_overview', 'admin_users', 'admin_verifications', 'admin_listings', 'admin_orders', 'admin_disputes', 'admin_reports', 'admin_regions_hubs', 'admin_settlements', 'admin_notifications', 'admin_settings']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Harvestly - Direct Farm-to-Doorstep Marketplace</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php if (!$isAdminPage): ?>
    <!-- Public Landing Page Header -->
    <header class="public-header no-print">
        <div class="public-header-inner">
            <a href="index.php" class="logo-brand">
                <img src="assets/images/harvestly_logo.jpg" alt="Harvestly Logo" style="height: 44px; width: auto; border-radius: var(--radius-md); object-fit: contain;">
                <div class="logo-text-group">
                    <span class="logo-title">Harvestly</span>
                    <span class="logo-subtitle">Fresh Local Market</span>
                </div>
            </a>

            <nav class="nav-links">
                <a href="#featured-produce" class="nav-pill active">🌱 Browse Produce</a>
                <a href="#how-it-works" class="nav-pill">How It Works</a>
                <a href="#trust-pillars" class="nav-pill">Why Harvestly</a>
                <a href="#partners" class="nav-pill">Join Fleet & Farmers</a>
            </nav>

            <div class="header-actions">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <span class="badge badge-info">Logged in as <?= sanitize($_SESSION['role']); ?></span>
                    <a href="index.php?action=logout" class="btn btn-outline btn-sm">Logout</a>
                <?php else: ?>
                    <a href="index.php?page=login" class="btn btn-outline">Login</a>
                    <a href="index.php?page=role_select" class="btn btn-primary">Sign Up</a>
                <?php endif; ?>
            </div>
        </div>
    </header>
<?php else: ?>
    <!-- Admin Top Bar -->
    <div class="topbar no-print">
        <div class="topbar-title">Operations Console</div>
        <div class="topbar-actions">
            <span class="badge badge-success">Administrator Mode</span>
            <a href="index.php?action=logout" class="btn btn-outline btn-sm">Logout</a>
        </div>
    </div>
<?php endif; ?>
