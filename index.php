<?php
/**
 * Harvestly Front Controller
 *
 * Main-compatible MVC layout:
 * Controller/{Admin,Auth,Main}
 * Model/{Admin,Auth,Main}
 * View/{Admin,Auth,Main,Shared}
 */

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/Controller/Auth/AuthController.php';
require_once __DIR__ . '/Controller/Admin/AdminController.php';
require_once __DIR__ . '/Controller/Main/MainController.php';

$action = $_GET['action'] ?? null;
$adminAction = $_GET['admin_action'] ?? null;
$page = $_GET['page'] ?? 'landing';

// Authentication POST actions
if ($action) {
    $authCtrl = new AuthController();
    switch ($action) {
        case 'login':
            $authCtrl->handleLogin();
            break;
        case 'signup_buyer':
            $authCtrl->handleBuyerSignup();
            break;
        case 'signup_farmer':
            $authCtrl->handleFarmerSignup();
            break;
        case 'signup_courier':
            $authCtrl->handleCourierSignup();
            break;
        case 'request_password_reset':
            $authCtrl->handlePasswordResetRequest();
            break;
        case 'reset_password':
            $authCtrl->handlePasswordReset();
            break;
        case 'logout':
            $authCtrl->handleLogout();
            break;
        default:
            header('Location: index.php');
            exit();
    }
    exit();
}

// Admin POST actions
if ($adminAction) {
    $adminCtrl = new AdminController();
    $adminCtrl->handleAdminAction();
    exit();
}

if (strpos($page, 'admin_') === 0) checkAdminAuth();
require_once __DIR__ . '/View/Shared/header.php';

$isAdminPage = (strpos($page, 'admin_') === 0);

if ($isAdminPage) {
    echo '<div class="app-container">';
    require_once __DIR__ . '/View/Admin/sidebar.php';
    echo '<div class="main-content">';
    $adminCtrl = new AdminController();
    $adminCtrl->renderView($page);
    echo '</div>';
    echo '</div>';
} else {
    echo '<div class="main-content" style="padding-top: 80px;">';

    switch ($page) {
        case 'login':
            require __DIR__ . '/View/Auth/login.php';
            break;
        case 'forgot_password':
            require __DIR__ . '/View/Auth/forgot_password.php';
            break;
        case 'reset_password':
            require __DIR__ . '/View/Auth/reset_password.php';
            break;
        case 'role_select':
            require __DIR__ . '/View/Auth/role_select.php';
            break;
        case 'signup_buyer':
            require __DIR__ . '/View/Auth/signup_buyer.php';
            break;
        case 'signup_farmer':
            require __DIR__ . '/View/Auth/signup_farmer.php';
            break;
        case 'signup_courier':
            require __DIR__ . '/View/Auth/signup_courier.php';
            break;
        case 'pending_approval':
            require __DIR__ . '/View/Auth/pending_approval.php';
            break;
        default:
            $mainCtrl = new MainController();
            $mainCtrl->render($page);
            break;
    }

    echo '</div>';
}

require_once __DIR__ . '/View/Shared/footer.php';
