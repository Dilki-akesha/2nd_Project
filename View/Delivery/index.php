<?php
/**
 * ============================================================
 * COURIER PARTNER DASHBOARD — Main Layout
 * ============================================================
 * 
 * INTEGRATION NOTES FOR TEAM LEADER:
 * 
 * 1. AUTH INTEGRATION (Lines marked with ⚠️ TODO):
 *    - After integration, uncomment the auth check block
 *    - Replace `$_SESSION['user_id'] = 5;` demo fallback
 *    - Ensure `../Auth/login.php` exists
 * 
 * 2. SESSION VARIABLES REQUIRED:
 *    - $_SESSION['user_id']  → courier partner's user ID
 *    - $_SESSION['role']     → 'COURIER_PARTNER'
 *    - $_SESSION['name']     → display name (optional)
 * 
 * 3. LOGOUT INTEGRATION:
 *    - Team's AuthController should handle: AuthController.php?action=logout
 *    - Must destroy session + redirect to: index.php?loggedout=1
 */

session_start();

// ⚡ Prevent browser caching
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

// ============================================================
// LOGOUT PAGE — Show success message
// ============================================================
if (isset($_GET['loggedout'])) {
    session_destroy();
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Logged Out — Harvestly</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body { font-family: system-ui, -apple-system, sans-serif; background: #f8f9fa; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
        .logout-box { background: white; padding: 48px 40px; border-radius: 12px; box-shadow: 0 8px 30px rgba(0,0,0,0.12); text-align: center; max-width: 420px; width: 90%; }
        .icon-wrap { width: 80px; height: 80px; margin: 0 auto 20px; background: #e8f5e9; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
        .icon-wrap i { font-size: 40px; color: #0d631b; }
        .logout-box h2 { margin: 0 0 12px; color: #191c1d; font-size: 22px; font-weight: 600; }
        .logout-box p { color: #6b7280; margin: 0 0 28px; font-size: 14px; line-height: 1.6; }
        .logout-box a { display: inline-block; padding: 12px 32px; background: #0d631b; color: white; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 14px; }
        .logout-box a:hover { background: #005312; }
    </style>
</head>
<body>
    <div class="logout-box">
        <div class="icon-wrap"><i class="fas fa-check-circle"></i></div>
        <h2>Logged Out Successfully</h2>
        <p>You have been safely logged out of your Courier Partner account.</p>
        <!-- ⚠️ TODO (Team Leader): Update this URL to team's login page -->
        <a href="../Auth/login.php"><i class="fas fa-sign-in-alt"></i> Login Again</a>
    </div>
</body>
</html>
    <?php
    exit;
}

// ============================================================
// ⚠️ TODO (Team Leader): UNCOMMENT THIS BLOCK AFTER INTEGRATION
// ============================================================
/* 
if (!isset($_SESSION['user_id'])) {
    header('Location: ../Auth/login.php');
    exit;
}

if (isset($_SESSION['role']) && $_SESSION['role'] !== 'COURIER_PARTNER') {
    session_destroy();
    header('Location: ../Auth/login.php?error=wrong_role');
    exit;
}
*/

// ============================================================
// ⚠️ DEMO MODE — REMOVE AFTER INTEGRATION
// ============================================================
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 5;
    $_SESSION['role'] = 'COURIER_PARTNER';
    $_SESSION['name'] = 'Nimal Silva';
}

$userId = $_SESSION['user_id'];
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// ============================================================
// PAGE TITLES
// ============================================================
$titles = [
    'dashboard'     => 'Dashboard <span>| overview</span>',
    'coverage'      => 'Coverage Routes <span>| manage</span>',
    'availability'  => 'Availability <span>| status</span>',
    'requests'      => 'Assignment Offers <span>| manage</span>',
    'assigned'      => 'Active Deliveries <span>| active</span>',
    'tracking'      => 'Delivery Tracking <span>| #HLY-9821</span>',
    'history'       => 'Delivery History <span>| records</span>',
    'earnings'      => 'Earnings / Payouts <span>| analytics</span>',
    'complaints'    => 'Report Issue <span>| support</span>',
    'notifications' => 'Notifications <span>| inbox</span>',
    'profile'       => 'Profile <span>| settings</span>',
    'verification'  => 'Verification <span>| status</span>'
];

$allowed = [
    'dashboard', 'requests', 'assigned', 'tracking',
    'history', 'earnings', 'complaints', 'profile',
    'coverage', 'availability', 'notifications', 'verification'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Harvestly — Courier Partner</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <link rel="stylesheet" href="../../CSS/Delivery/style.css" />
</head>
<body>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- ============================================================ -->
    <!-- SIDEBAR -->
    <!-- ============================================================ -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <div class="logo-wrapper">
                <img src="../../logo.png" alt="Harvestly Logo" class="logo-img" id="sidebarLogo" />
                <div class="logo-fallback" id="sidebarLogoFallback">
                    <i class="fas fa-leaf"></i>
                </div>
            </div>
            <h1>Harvest<span>ly</span></h1>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-label">Main</div>
            <a href="?page=dashboard" class="<?= ($page === 'dashboard' ? 'active' : '') ?>">
                <i class="fas fa-th-large"></i> Dashboard
            </a>
            <a href="?page=coverage" class="<?= ($page === 'coverage' ? 'active' : '') ?>">
                <i class="fas fa-route"></i> Coverage Routes
            </a>
            <a href="?page=availability" class="<?= ($page === 'availability' ? 'active' : '') ?>">
                <i class="fas fa-toggle-on"></i> Availability
            </a>

            <div class="nav-label">Operations</div>
            <a href="?page=requests" class="<?= ($page === 'requests' ? 'active' : '') ?>">
                <i class="fas fa-clipboard-list"></i> Assignment Offers
            </a>
            <a href="?page=assigned" class="<?= ($page === 'assigned' ? 'active' : '') ?>">
                <i class="fas fa-truck"></i> Active Deliveries
            </a>
            <a href="?page=tracking" class="<?= ($page === 'tracking' ? 'active' : '') ?>">
                <i class="fas fa-map-marked-alt"></i> Delivery Tracking
            </a>

            <div class="nav-label">Records &amp; Finance</div>
            <a href="?page=history" class="<?= ($page === 'history' ? 'active' : '') ?>">
                <i class="fas fa-history"></i> Delivery History
            </a>
            <a href="?page=earnings" class="<?= ($page === 'earnings' ? 'active' : '') ?>">
                <i class="fas fa-wallet"></i> Earnings / Payouts
            </a>
            <a href="?page=verification" class="<?= ($page === 'verification' ? 'active' : '') ?>">
                <i class="fas fa-certificate"></i> Verification
            </a>

            <div class="nav-label">Support</div>
            <a href="?page=complaints" class="<?= ($page === 'complaints' ? 'active' : '') ?>">
                <i class="fas fa-exclamation-triangle"></i> Report Issue
            </a>
            <a href="?page=notifications" class="<?= ($page === 'notifications' ? 'active' : '') ?>">
                <i class="fas fa-bell"></i> Notifications
                <span class="badge" style="margin-left: auto; background: #ba1a1a; color: #fff; font-size: 10px; padding: 2px 6px; border-radius: 10px;">2</span>
            </a>
            <a href="?page=profile" class="<?= ($page === 'profile' ? 'active' : '') ?>">
                <i class="fas fa-user-circle"></i> Profile
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="avatar">
                <?= strtoupper(substr($_SESSION['name'] ?? 'MT', 0, 2)) ?>
            </div>
            <div class="user-info">
                <div class="name"><?= htmlspecialchars($_SESSION['name'] ?? 'Courier Partner') ?></div>
                <div class="role">Courier Partner</div>
            </div>
            <div class="logout-btn" id="logoutBtn" title="Logout">
                <i class="fas fa-sign-out-alt"></i>
            </div>
        </div>
    </aside>

    <!-- ============================================================ -->
    <!-- MAIN CONTENT -->
    <!-- ============================================================ -->
    <div class="main">

        <header class="topbar">
            <div class="topbar-left">
                <button class="menu-toggle" id="menuToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h2 id="pageTitle">
                    <?= isset($titles[$page]) ? $titles[$page] : 'Dashboard <span>| overview</span>' ?>
                </h2>
            </div>
            <div class="topbar-right">
                <span class="partner-badge">
                    <i class="fas fa-check-circle"></i> Verified Partner
                </span>
                <a href="?page=notifications" class="notif-btn" style="text-decoration: none; color: inherit;">
                    <i class="fas fa-bell"></i>
                    <span class="badge">3</span>
                </a>
            </div>
        </header>

        <div class="page-content">
            <?php
                if (in_array($page, $allowed)) {
                    $viewFile = __DIR__ . '/' . $page . '.php';
                    if (file_exists($viewFile)) {
                        include $viewFile;
                    } else {
                        include __DIR__ . '/dashboard.php';
                    }
                } else {
                    include __DIR__ . '/dashboard.php';
                }
            ?>
        </div>
    </div>

    <!-- ===== MODULAR SCRIPTS ===== -->
    <script src="../../JS/Delivery/_sidebar.js"></script>
    <script src="../../JS/Delivery/_auth.js"></script>
    <script src="../../JS/Delivery/_requests.js"></script>
    <script src="../../JS/Delivery/_tracking.js"></script>
    <script src="../../JS/Delivery/_complaints.js"></script>
    <script src="../../JS/Delivery/_profile.js"></script>
    <script src="../../JS/Delivery/_availability.js"></script>
    <script src="../../JS/Delivery/_earnings.js"></script>
    <script src="../../JS/Delivery/_coverage.js"></script>
    <script src="../../JS/Delivery/_notifications.js"></script>

    <!-- ===== LOGOUT HANDLER — INLINE ===== -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const oldBtn = document.getElementById('logoutBtn');
        if (!oldBtn) return;

        const newBtn = oldBtn.cloneNode(true);
        oldBtn.parentNode.replaceChild(newBtn, oldBtn);

        newBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            if (confirm('Are you sure you want to logout?')) {
                // ⚠️ TODO (Team Leader): Update to team's logout controller
                window.location.href = '../../Controller/AuthController.php?action=logout';
            }
        });
    });
    </script>

    <!-- ===== LOGO FALLBACK ===== -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebarLogo = document.getElementById('sidebarLogo');
        const sidebarFallback = document.getElementById('sidebarLogoFallback');

        function checkLogo(img, fallback) {
            if (!img || !fallback) return;
            img.onerror = function() {
                img.style.display = 'none';
                fallback.style.display = 'flex';
            };
            img.onload = function() {
                img.style.display = 'block';
                fallback.style.display = 'none';
            };
            if (img.complete) {
                if (img.naturalWidth === 0) {
                    img.style.display = 'none';
                    fallback.style.display = 'flex';
                } else {
                    img.style.display = 'block';
                    fallback.style.display = 'none';
                }
            }
        }
        checkLogo(sidebarLogo, sidebarFallback);
    });
    </script>

</body>
</html>