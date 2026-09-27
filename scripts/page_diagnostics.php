<?php
/**
 * Diagnostic helper: fetch each role page as that role and report any PHP
 * diagnostic text. Used only while fixing issues.
 */
require_once __DIR__ . '/../config/app.php';

$base = getenv('HARVESTLY_BASE') ?: 'http://localhost/Harvestly-final/2nd_Project';

function fetch(string $url, string $jar, ?array $post = null): array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_FOLLOWLOCATION => 1,
        CURLOPT_COOKIEJAR => $jar,
        CURLOPT_COOKIEFILE => $jar,
        CURLOPT_TIMEOUT => 30,
    ]);
    if ($post !== null) {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
    }
    $b = curl_exec($ch);
    $i = curl_getinfo($ch);
    curl_close($ch);
    return [$i['http_code'], (string)$b];
}

function token(string $html): string
{
    preg_match('/name="csrf_token" value="([a-f0-9]+)"/', $html, $m);
    return $m[1] ?? '';
}

function diagnostics(string $html): array
{
    $found = [];
    foreach (['Fatal error', 'Parse error', 'Warning:', 'Notice:', 'Deprecated:', 'Undefined ', 'Uncaught'] as $n) {
        if (stripos($html, $n) === false) continue;
        if (preg_match_all('/' . preg_quote($n, '/') . '.{0,240}/s', strip_tags($html), $m)) {
            foreach ($m[0] as $hit) {
                $found[] = preg_replace('/\s+/', ' ', $hit);
            }
        }
    }
    return array_slice(array_unique($found), 0, 6);
}

$roles = [
    'buyer'   => ['buyer@gmail.com', 'testpass123', [
        'Controller/Buyer/DashboardController.php',
        'Controller/Buyer/ProductController.php',
        'Controller/Buyer/CartController.php',
        'Controller/Buyer/OrdersController.php',
        'Controller/Buyer/FeedbackController.php',
        'Controller/Buyer/NotificationsController.php',
        'Controller/Buyer/ProfileController.php',
        'Controller/Buyer/CheckoutController.php',
    ]],
    'farmer'  => ['farmer@gmail.com', 'testpass123', [
        'dashboard.php', 'products.php', 'add-product.php', 'inventory.php', 'orders.php',
        'harvest-soon.php', 'sales.php', 'earnings.php', 'reviews.php', 'report-issue.php',
        'notifications.php', 'profile.php',
    ]],
    'courier' => ['courier@gmail.com', 'testpass123', [
        'dashboard', 'requests', 'assigned', 'coverage', 'history', 'earnings',
        'complaints', 'notifications', 'profile',
    ]],
    'admin'   => ['admin@gmail.com', 'testpass123', [
        'admin_overview', 'admin_users', 'admin_farmer_approvals', 'admin_courier_approvals',
        'admin_listings', 'admin_categories', 'admin_orders', 'admin_deliveries',
        'admin_pending_assignments', 'admin_district_distances', 'admin_complaints',
        'admin_payments', 'admin_settlements', 'admin_settings', 'admin_notifications',
        'admin_reports', 'admin_profile',
    ]],
];

$totalProblems = 0;
foreach ($roles as $role => [$email, $password, $pages]) {
    $jar = sys_get_temp_dir() . '/hv_diag_' . $role . '.txt';
    @unlink($jar);
    touch($jar);

    [, $html] = fetch("$base/index.php?page=login", $jar);
    [, $html] = fetch("$base/index.php?action=login", $jar, [
        'csrf_token' => token($html), 'email' => $email, 'password' => $password,
    ]);

    echo "\n--- $role ---\n";
    foreach ($pages as $page) {
        $url = match ($role) {
            'farmer'  => "$base/View/Farmer/$page",
            'courier' => "$base/Controller/Courier/CourierController.php?page=$page",
            default   => "$base/index.php?page=$page",
        };
        [$status, $body] = fetch($url, $jar);
        $problems = diagnostics($body);
        if ($problems || $status >= 400) {
            $totalProblems++;
            echo "  [$status] $page\n";
            foreach ($problems as $p) echo "      $p\n";
        } else {
            echo "  [$status] $page  OK\n";
        }
    }
}

echo "\nPages with problems: $totalProblems\n";
