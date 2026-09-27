<?php
/**
 * Shared Harvestly application helpers.
 * Core PHP + MySQLi only.
 */

declare(strict_types=1);

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/status.php';
require_once __DIR__ . '/../Model/OrderWorkflow.php';

if (!defined('APP_NAME')) {
    define('APP_NAME', 'Harvestly');
}
if (!defined('BASE_URL')) {
    // Detect the project URL from its filesystem location under Apache's
    // document root. This keeps redirects working even if the folder is
    // renamed (for example Harvestly_No_External_Libraries/Harvestly).
    $projectRoot = realpath(dirname(__DIR__));
    $documentRoot = isset($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : false;
    $detectedBase = '/Harvestly';

    if ($projectRoot && $documentRoot) {
        $projectNorm = str_replace('\\', '/', $projectRoot);
        $documentNorm = rtrim(str_replace('\\', '/', $documentRoot), '/');
        if (stripos($projectNorm, $documentNorm) === 0) {
            $relative = substr($projectNorm, strlen($documentNorm));
            $relative = '/' . trim(str_replace('\\', '/', $relative), '/');
            $detectedBase = $relative === '/' ? '' : $relative;
        }
    }

    define('BASE_URL', $detectedBase);
}

function db(): mysqli {
    return getDBConnection();
}

function url(string $path = ''): string {
    return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
}

function redirect(string $path): void {
    $target = preg_match('#^https?://#i', $path) ? $path : url($path);
    header('Location: ' . $target);
    exit();
}

function e($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function post_string(string $key, string $default = ''): string {
    return trim((string)($_POST[$key] ?? $default));
}

function buyerRoute(string $controller, string $query = ''): string {
    $path = 'Controller/Buyer/' . ltrim($controller, '/');
    return url($path . ($query !== '' ? ('?' . ltrim($query, '?')) : ''));
}

function currentBuyerId(): int {
    $role = strtolower((string)($_SESSION['role'] ?? ''));
    return isset($_SESSION['user_id']) && $role === 'buyer' ? (int)$_SESSION['user_id'] : 0;
}

/** Recheck database status on every protected request, including existing sessions. */
function requireActiveRole(string $role): void {
    $databaseRole = $role === 'courier' ? 'COURIER_PARTNER' : strtoupper($role);
    $user = db_fetch_one('SELECT role, account_status FROM users WHERE user_id=?', 'i', [(int)($_SESSION['user_id'] ?? 0)]);
    $approved = true;
    if ($user && $user['role'] === 'FARMER') {
        $approved = db_scalar('SELECT verification_status FROM farmer_profiles WHERE farmer_id=?', 'i', [(int)$_SESSION['user_id']]) === 'APPROVED';
    } elseif ($user && $user['role'] === 'COURIER_PARTNER') {
        $approved = db_scalar('SELECT verification_status FROM courier_partner_profiles WHERE courier_partner_id=?', 'i', [(int)$_SESSION['user_id']]) === 'APPROVED';
    }
    if (!$user || $user['role'] !== $databaseRole || $user['account_status'] !== 'ACTIVE' || !$approved) {
        /*
         * A signed-in user who opens a page belonging to a different role is sent
         * to their OWN dashboard instead of being bounced to the login screen.
         */
        $home = [
            'ADMIN' => 'index.php?page=admin_overview',
            'BUYER' => 'Controller/Buyer/DashboardController.php',
            'FARMER' => 'View/Farmer/dashboard.php',
            'COURIER_PARTNER' => 'Controller/Courier/CourierController.php?page=dashboard',
        ];

        if ($user && $user['account_status'] === 'ACTIVE' && isset($home[$user['role']])) {
            $ownHome = $home[$user['role']];
            $ownScript = basename((string)parse_url(url($ownHome), PHP_URL_PATH));
            if (basename((string)($_SERVER['SCRIPT_NAME'] ?? '')) !== $ownScript) {
                redirect($ownHome);
            }
        }

        redirect('index.php?page=login&error=' . urlencode('Please sign in with an active, approved ' . ($role === 'courier' ? 'Courier Partner' : ucfirst($role)) . ' account.'));
    }
}

function currentFarmerId(): int {
    $role = strtolower((string)($_SESSION['role'] ?? ''));
    return isset($_SESSION['user_id']) && $role === 'farmer' ? (int)$_SESSION['user_id'] : 0;
}

function currentCourierId(): int {
    $role = strtolower((string)($_SESSION['role'] ?? ''));
    return isset($_SESSION['user_id']) && in_array($role, ['courier', 'courier_partner'], true)
        ? (int)$_SESSION['user_id'] : 0;
}

function requireBuyerAuth(): void {
    requireActiveRole('buyer');
    if (currentBuyerId() <= 0) {
        header('Location: ' . url('index.php?page=login&error=' . urlencode('Please login as a Buyer to continue.')));
        exit();
    }
}

function requireFarmerAuth(): void {
    requireActiveRole('farmer');
    if (currentFarmerId() <= 0) {
        header('Location: ' . url('index.php?page=login&error=' . urlencode('Please login as a Farmer to continue.')));
        exit();
    }
}

function requireCourierAuth(): void {
    requireActiveRole('courier');
    if (currentCourierId() <= 0) {
        header('Location: ' . url('index.php?page=login&error=' . urlencode('Please login as a Courier Partner to continue.')));
        exit();
    }
}

function bindMysqliParams(mysqli_stmt $stmt, string $types, array &$params): void {
    if ($types === '' || !$params) return;
    // A mismatch here would make bind_param() throw, so it is treated as a bug
    // loudly rather than silently failing the write.
    if (strlen($types) !== count($params)) {
        $sql = '';
        foreach (debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 4) as $frame) {
            if (isset($frame['args'][0]) && is_string($frame['args'][0])) {
                $sql = $frame['args'][0];
                break;
            }
        }
        throw new InvalidArgumentException(
            'Prepared statement parameter mismatch: ' . strlen($types) . ' type(s) for ' . count($params) .
            ' value(s). SQL: ' . preg_replace('/\s+/', ' ', trim($sql))
        );
    }
    $refs = [$types];
    foreach ($params as $key => &$value) {
        $refs[] = &$value;
    }
    $stmt->bind_param(...$refs);
}

function db_fetch_all(string $sql, string $types = '', array $params = []): array {
    $stmt = db()->prepare($sql);
    if (!$stmt) return [];
    bindMysqliParams($stmt, $types, $params);
    if (!$stmt->execute()) {
        $stmt->close();
        return [];
    }
    $result = $stmt->get_result();
    $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();
    return $rows;
}

function db_fetch_one(string $sql, string $types = '', array $params = []): ?array {
    $rows = db_fetch_all($sql, $types, $params);
    return $rows[0] ?? null;
}

function db_scalar(string $sql, string $types = '', array $params = [], $default = null) {
    $row = db_fetch_one($sql, $types, $params);
    if (!$row) return $default;
    return reset($row);
}

function db_execute(string $sql, string $types = '', array $params = []): bool {
    $stmt = db()->prepare($sql);
    if (!$stmt) return false;
    bindMysqliParams($stmt, $types, $params);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function db_setting(string $key, float $default = 0.0): float {
    $value = db_scalar(
        'SELECT setting_value FROM platform_settings WHERE setting_key = ? LIMIT 1',
        's',
        [$key],
        null
    );
    return $value === null ? $default : (float)$value;
}

function orderPublicId(int $orderId): string {
    return 'ORD-' . date('Y') . '-' . $orderId;
}

function parseOrderPublicId(string $value): int {
    if (preg_match('/(\d+)\s*$/', trim($value), $m)) {
        return (int)$m[1];
    }
    return (int)$value;
}


/**
 * Offer a Ready-for-Delivery order to the next eligible Courier Partner.
 * Eligibility: approved + active + currently available + active district route.
 * Selection is based only on approval, availability, and the required district coverage route.
 */
function attemptAutomaticCourierAssignment(int $orderId): bool {
    require_once __DIR__.'/../Model/Courier/AssignmentModel.php';
    return offerCourierAssignment($orderId);
}
