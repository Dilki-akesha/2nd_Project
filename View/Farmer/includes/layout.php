<?php
require_once __DIR__ . '/auth.php';

if (!function_exists('page_top')) {
    function page_top(string $title, string $active = ''): void
    {
        /*
         * page_top() runs in function scope, so the authenticated Farmer values
         * created by includes/auth.php are pulled in explicitly for sidebar.php.
         */
        global $farmer_id, $farmer_user, $farmerModel;

        $GLOBALS['activePage'] = $active;
        $displayName = e((string)($farmer_user['full_name'] ?? ($_SESSION['user_name'] ?? 'Farmer')));
        $flashData = $_SESSION['_farmer_flash'] ?? null;
        unset($_SESSION['_farmer_flash']);
        ?>
        <!doctype html>
        <html lang="en">
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title><?= e($title) ?> | Harvestly</title>
            <link rel="stylesheet" href="../../CSS/Farmer/style.css">
            <link rel="stylesheet" href="../../CSS/Farmer/shell-additions.css">
            <link rel="icon" href="<?= e(url('assets/harvestly-logo.jpeg')) ?>">
            <meta name="csrf-token" content="<?= e(csrfToken()) ?>">
            <meta name="app-base" content="<?= e(BASE_URL) ?>">
            <script src="<?= e(url('JS/Common/session.js')) ?>"></script>
        <script defer src="<?= e(url('JS/Common/validation.js')) ?>"></script>
        </head>
        <body>
        <div class="app">
            <?php require __DIR__ . '/../sidebar.php'; ?>
            <main class="main">
                <header class="topbar">
                    <div class="toplinks">
                        <a href="dashboard.php">Dashboard</a>
                        <a href="products.php">Products</a>
                        <a href="orders.php">Orders</a>
                        <a href="earnings.php">Earnings</a>
                    </div>
                    <div class="user">
                        <div class="avatar"><?= e(strtoupper(substr($displayName, 0, 1))) ?></div>
                        <div>
                            <strong><?= $displayName ?></strong>
                            <div class="user-role">Farmer</div>
                        </div>
                    </div>
                </header>
                <section class="content">
                    <?php if ($flashData): ?>
                        <div class="flash flash--<?= ($flashData['type'] ?? '') === 'error' ? 'error' : 'success' ?>">
                            <?= e((string)($flashData['message'] ?? '')) ?>
                        </div>
                    <?php endif; ?>
        <?php
    }
}

if (!function_exists('page_bottom')) {
    function page_bottom(): void
    {
        ?>
                </section>
            </main>
        </div>
        </body>
        </html>
        <?php
    }
}
