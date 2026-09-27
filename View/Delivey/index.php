<?php
/**
 * Courier Partner module layout.
 *
 * The folder is still named View/Delivey to avoid breaking existing include
 * paths, but the actor is always referred to as a Courier Partner organisation.
 * There are no individual drivers, vehicles, fleets, hubs or GPS features.
 */
$organisation = (string)($profile['organisation_name'] ?? $profile['full_name'] ?? 'Courier Partner');
$initials = '';
foreach (array_slice(preg_split('/\s+/', trim($organisation)) ?: [], 0, 2) as $word) {
    if ($word !== '') $initials .= strtoupper(substr($word, 0, 1));
}
if ($initials === '') $initials = 'CP';

$courierBase = 'Controller/Courier/CourierController.php?page=';

$nav = [
    'dashboard'     => ['Dashboard', 'dashboard'],
    'requests'      => ['Assignment Offers', 'assignment'],
    'assigned'      => ['Active Deliveries', 'local_shipping'],
    'coverage'      => ['Coverage Routes', 'route'],
    'history'       => ['Delivery History', 'history'],
    'earnings'      => ['Earnings & Payouts', 'payments'],
    'complaints'    => ['Complaints / Issues', 'report_problem'],
    'notifications' => ['Notifications', 'notifications'],
    'profile'       => ['Organisation Profile', 'storefront'],
];

$pageTitle = $page === 'tracking' ? 'Delivery Details' : ($nav[$page][0] ?? 'Courier Partner');
$unread = (int)($stats['unread'] ?? 0);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> | Harvestly</title>
    <link rel="stylesheet" href="<?= e(url('CSS/Delivery/style.css')) ?>">
    <link rel="stylesheet" href="<?= e(url('CSS/Delivery/shell-additions.css')) ?>">
    <link rel="icon" href="<?= e(url('assets/harvestly-logo.jpeg')) ?>">
    <meta name="csrf-token" content="<?= e(csrfToken()) ?>">
    <meta name="app-base" content="<?= e(BASE_URL) ?>">
    <script src="<?= e(url('JS/Common/session.js')) ?>"></script>
    <script defer src="<?= e(url('JS/Common/local-icons.js')) ?>"></script>
</head>
<body>
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="logo-wrapper">
            <img src="<?= e(url('assets/harvestly-logo.jpeg')) ?>" class="logo-img" alt="Harvestly">
        </div>
        <h1>Harvest<span>ly</span></h1>
    </div>

    <p class="nav-label">Courier Partner</p>

    <nav class="sidebar-nav" aria-label="Courier Partner navigation">
        <?php foreach ($nav as $key => [$label, $icon]): ?>
        <a href="<?= e(url($courierBase . $key)) ?>"
           class="<?= $page === $key ? 'active' : '' ?>"
           <?= $page === $key ? 'aria-current="page"' : '' ?>>
            <span><?= e($label) ?></span>
            <?php if ($key === 'notifications' && $unread > 0): ?>
            <span class="badge"><?= $unread ?></span>
            <?php endif; ?>
        </a>
        <?php endforeach; ?>
    </nav>

    <div class="sidebar-footer">
        <div class="avatar" aria-hidden="true"><?= e($initials) ?></div>
        <div class="user-info">
            <div class="name"><?= e($organisation) ?></div>
            <div class="role">Courier Partner</div>
        </div>
        <form method="post" action="<?= e(url('index.php?action=logout')) ?>">
            <?= csrfField() ?>
            <button class="logout-btn" type="submit" title="Logout" aria-label="Logout">
                <span class="material-symbols-outlined" aria-hidden="true">logout</span>
            </button>
        </form>
    </div>
</aside>

<div class="main">
    <header class="topbar">
        <div class="topbar-left">
            <button class="menu-toggle" id="menuToggle" type="button" aria-label="Toggle navigation">
                <span class="material-symbols-outlined" aria-hidden="true">menu</span>
            </button>
            <h2><?= e($pageTitle) ?></h2>
        </div>
        <div class="topbar-right">
            <?php if ($page !== 'dashboard'): ?>
            <a class="topbar-link" href="<?= e(url($courierBase . 'dashboard')) ?>">
                <span class="material-symbols-outlined" aria-hidden="true">dashboard</span>
                <span>Dashboard</span>
            </a>
            <?php endif; ?>
            <span class="partner-badge <?= (string)($profile['verification_status'] ?? 'PENDING') === 'APPROVED' ? 'approved' : 'pending' ?>">
                <?= e(farmerCourierLabel((string)($profile['verification_status'] ?? 'PENDING'))) ?>
            </span>
        </div>
    </header>

    <div class="page-content">
        <?php if ($flash): ?>
        <div class="courier-flash courier-flash--<?= $flash['ok'] ? 'ok' : 'error' ?>"><?= e($flash['message']) ?></div>
        <?php endif; ?>

        <?php require __DIR__ . '/' . $page . '.php'; ?>
    </div>
</div>

<script src="<?= e(url('JS/Delivery/script.js')) ?>"></script>
</body>
</html>
