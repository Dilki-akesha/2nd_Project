<?php
/**
 * Harvestly Buyer module shell.
 * Sidebar + topbar + content, matching the Farmer, Courier Partner and Admin
 * modules. Plain PHP + plain CSS. No frameworks, no external assets.
 */
require_once __DIR__ . '/../../../config/app.php';
requireBuyerAuth();

/** Show and clear a one-request flash message. */
if (!function_exists('buyerFlash')) {
    function buyerFlash(): void {
        if (empty($_SESSION['_buyer_flash'])) return;
        $flash = $_SESSION['_buyer_flash'];
        unset($_SESSION['_buyer_flash']);
        $type = ($flash['success'] ?? false) ? '' : ' error';
        echo '<div class="buyer-alert' . $type . '">' . e($flash['message'] ?? '') . '</div>';
    }
}

/** Unread database notification count for the signed-in Buyer. */
if (!function_exists('buyerUnreadCount')) {
    function buyerUnreadCount(int $buyerId): int {
        return (int)db_scalar(
            'SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0',
            'i',
            [$buyerId],
            0
        );
    }
}

/** Item count in the signed-in Buyer's general cart. */
if (!function_exists('buyerCartCount')) {
    function buyerCartCount(int $buyerId): int {
        return (int)db_scalar(
            'SELECT COALESCE(SUM(ci.quantity),0)
             FROM cart_items ci JOIN carts c ON c.cart_id=ci.cart_id
             WHERE c.buyer_id=?',
            'i',
            [$buyerId],
            0
        );
    }
}

/** Render a status badge using the shared Harvestly status vocabulary. */
if (!function_exists('buyerStatusBadge')) {
    function buyerStatusBadge(?string $status): string {
        $tone = harvestlyStatusTone($status);
        $class = 'buyer-badge' . ($tone !== 'ok' && $tone !== 'muted' ? ' buyer-badge--' . $tone : '');
        return '<span class="' . $class . '">' . e(harvestlyStatusLabel($status)) . '</span>';
    }
}

/** The Buyer sidebar navigation, with local icon names and optional counters. */
if (!function_exists('buyerNavItems')) {
    function buyerNavItems(int $buyerId): array {
        $unread = buyerUnreadCount($buyerId);
        $cart = buyerCartCount($buyerId);
        return [
            ['file' => 'DashboardController.php',      'label' => 'Dashboard',      'icon' => 'dashboard'],
            ['file' => 'ProductController.php',        'label' => 'Browse Products','icon' => 'shopping_bag'],
            ['file' => 'CartController.php',           'label' => 'Cart',           'icon' => 'shopping_cart', 'count' => $cart],
            ['file' => 'OrdersController.php',         'label' => 'My Orders',      'icon' => 'receipt_long'],
            ['file' => 'FeedbackController.php',       'label' => 'Reviews & Issues','icon' => 'rate_review'],
            ['file' => 'NotificationsController.php',  'label' => 'Notifications',  'icon' => 'notifications', 'count' => $unread, 'alert' => true],
            ['file' => 'ProfileController.php',        'label' => 'Profile',        'icon' => 'person'],
        ];
    }
}

/**
 * Open the Buyer page shell.
 *
 * @param string $title  Page title shown in the topbar and browser tab.
 * @param string $active Controller file name that should be highlighted.
 */
function buyer_page_top(string $title, string $active = ''): void {
    $buyerId = currentBuyerId();
    $items = buyerNavItems($buyerId);
    $displayName = (string)($_SESSION['user_name'] ?? 'Buyer');
    $initials = strtoupper(substr($displayName !== '' ? $displayName : 'B', 0, 1));
    ?>
    <!doctype html>
    <html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?= e($title) ?> | Harvestly</title>
        <link rel="stylesheet" href="<?= e(url('CSS/Buyer/shell.css')) ?>">
        <link rel="icon" href="<?= e(url('assets/harvestly-logo.jpeg')) ?>">
        <meta name="csrf-token" content="<?= e(csrfToken()) ?>">
        <meta name="app-base" content="<?= e(BASE_URL) ?>">
        <script src="<?= e(url('JS/Common/session.js')) ?>"></script>
        <script defer src="<?= e(url('JS/Common/validation.js')) ?>"></script>
    </head>
    <body>
    <aside class="buyer-sidebar" id="buyerSidebar">
        <a class="buyer-brand" href="<?= e(buyerRoute('DashboardController.php')) ?>">
            <img src="<?= e(url('assets/harvestly-logo.jpeg')) ?>" alt="Harvestly">
            <span>Harvestly<small>Fresh Local Market</small></span>
        </a>
        <p class="buyer-role">Buyer Workspace</p>
        <nav class="buyer-nav" aria-label="Buyer navigation">
            <?php foreach ($items as $item): ?>
            <a href="<?= e(buyerRoute($item['file'])) ?>"
               class="<?= $active === $item['file'] ? 'active' : '' ?>"
               <?= $active === $item['file'] ? 'aria-current="page"' : '' ?>>
                <span><?= e($item['label']) ?></span>
                <?php if (!empty($item['count'])): ?>
                <span class="buyer-count<?= !empty($item['alert']) ? ' is-alert' : '' ?>"><?= (int)$item['count'] ?></span>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
        </nav>
        <div class="buyer-sidebar-footer">
            <div class="buyer-account">
                <span class="buyer-avatar" aria-hidden="true"><?= e($initials) ?></span>
                <span>
                    <strong><?= e($displayName) ?></strong>
                    <small>Buyer account</small>
                </span>
            </div>
            <form method="post" action="<?= e(url('index.php?action=logout')) ?>">
                <?= csrfField() ?>
                <button class="buyer-logout" type="submit">
                    <span class="material-symbols-outlined" aria-hidden="true">logout</span>
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </aside>

    <div class="buyer-main">
        <header class="buyer-topbar">
            <div>
                <button class="buyer-menu-toggle" type="button" id="buyerMenuToggle" aria-controls="buyerSidebar" aria-label="Toggle navigation">
                    <span class="material-symbols-outlined" aria-hidden="true">menu</span>
                </button>
                <h1><?= e($title) ?></h1>
            </div>
            <div class="buyer-topbar-right">
                <a class="buyer-topbar-link" href="<?= e(buyerRoute('CartController.php')) ?>">
                    <span class="material-symbols-outlined" aria-hidden="true">shopping_cart</span>
                    <span>Cart</span>
                </a>
                <a class="buyer-topbar-link" href="<?= e(buyerRoute('NotificationsController.php')) ?>">
                    <span class="material-symbols-outlined" aria-hidden="true">notifications</span>
                    <span>Notifications</span>
                </a>
            </div>
        </header>

        <main class="buyer-content">
        <?php
}

/** Close the Buyer page shell. */
function buyer_page_bottom(): void {
        ?>
        </main>
    </div>
    <script src="<?= e(url('JS/Common/local-icons.js')) ?>"></script>
    <script src="<?= e(url('JS/Buyer/shell.js')) ?>"></script>
    </body>
    </html>
    <?php
}
