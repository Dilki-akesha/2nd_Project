<?php
/**
 * Crawl every page as every actor and report anything that is not connected
 * and navigatable.
 *
 * Checks each reachable page for:
 *   - internal links that do not resolve (404, PHP error, or bounce to login)
 *   - dead links (href="#" or javascript: or an empty href)
 *   - images whose file is missing
 *   - form actions that do not resolve
 *   - pages that emit a PHP warning / fatal
 *
 * Usage: php scripts/check_navigation.php
 * Optional: set HARVESTLY_BASE to point at a different deployment.
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Command-line only.');
}

$base = rtrim(getenv('HARVESTLY_BASE') ?: 'http://localhost/Harvestly-final/2nd_Project', '/') . '/';

/** Perform a request, optionally with a cookie jar. */
function req(string $url, string $jar = '', ?array $post = null): array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 6,
        CURLOPT_TIMEOUT => 30,
    ]);
    if ($jar !== '') {
        curl_setopt($ch, CURLOPT_COOKIEJAR, $jar);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $jar);
    }
    if ($post !== null) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
    }
    $body = (string)curl_exec($ch);
    $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $final = (string)curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    $err = curl_error($ch);
    curl_close($ch);
    return ['status' => $status, 'body' => $body, 'final' => $final, 'error' => $err];
}

function csrf(string $base, string $jar, string $page = 'login'): string
{
    $r = req($base . 'index.php?page=' . $page, $jar);
    preg_match('/name="csrf_token"[^>]*value="([^"]+)"/', $r['body'], $m);
    return $m[1] ?? '';
}

function loginAs(string $base, string $jar, string $email, string $password): bool
{
    @unlink($jar);
    $token = csrf($base, $jar);
    $r = req($base . 'index.php?action=login', $jar, [
        'csrf_token' => $token, 'email' => $email, 'password' => $password,
    ]);
    return stripos($r['final'], 'page=login') === false;
}

/**
 * Turn a raw href/src into an absolute URL on this deployment, or null to skip.
 *
 * Handles the three shapes the views use: root-relative (/assets/...), page
 * relative (dashboard.php) and query only (?id=1). HTML entities and spaces in
 * filenames are encoded, otherwise perfectly valid links look like 404s.
 */
function absolutise(string $base, string $pageUrl, string $href): ?string
{
    $href = trim(html_entity_decode($href, ENT_QUOTES, 'UTF-8'));
    if ($href === '' || $href === '#') {
        return null;
    }
    if (preg_match('#^(javascript:|mailto:|tel:|data:)#i', $href)) {
        return null;
    }
    if (preg_match('#^https?://#i', $href)) {
        return strpos($href, $base) === 0 ? encodeUrl($href) : null;
    }

    $parts = parse_url($pageUrl);
    $root = $parts['scheme'] . '://' . $parts['host'] . (isset($parts['port']) ? ':' . $parts['port'] : '');
    $path = $parts['path'] ?? '/';

    if (strpos($href, '//') === 0) {
        return encodeUrl('http:' . $href);
    }
    if (strpos($href, '/') === 0) {
        // Root-relative: resolve against the server root, not the page folder.
        return encodeUrl($root . '/' . ltrim($href, '/'));
    }
    if ($href[0] === '?' || $href[0] === '#') {
        return encodeUrl($root . $path . $href);
    }

    return encodeUrl(rtrim(dirname($root . $path), '/') . '/' . ltrim($href, '/'));
}

/** Percent-encode spaces, which otherwise break the raw request. */
function encodeUrl(string $url): string
{
    $parts = parse_url($url);
    if (!isset($parts['path'])) {
        return $url;
    }
    return $parts['scheme'] . '://' . $parts['host']
        . (isset($parts['port']) ? ':' . $parts['port'] : '')
        . str_replace(' ', '%20', $parts['path'])
        . (isset($parts['query']) ? '?' . $parts['query'] : '');
}

/** Pages that legitimately render the login form, so landing there is correct. */
function isAuthPage(string $url): bool
{
    return (bool)preg_match('/page=(login|forgot_password|reset_password)/', $url);
}

/* The seed pages for each actor, which the sidebar of that actor links to. */
$actors = [
    'Buyer' => [
        'email' => 'buyer@gmail.com',
        'seeds' => [
            'Controller/Buyer/DashboardController.php',
            'Controller/Buyer/ProductController.php',
            'Controller/Buyer/CartController.php',
            'Controller/Buyer/OrdersController.php',
            'Controller/Buyer/NotificationsController.php',
            'Controller/Buyer/ProfileController.php',
            'Controller/Buyer/FeedbackController.php',
        ],
    ],
    'Farmer' => [
        'email' => 'farmer@gmail.com',
        'seeds' => [
            'View/Farmer/dashboard.php', 'View/Farmer/products.php', 'View/Farmer/add-product.php',
            'View/Farmer/inventory.php', 'View/Farmer/orders.php', 'View/Farmer/harvest-soon.php',
            'View/Farmer/sales.php', 'View/Farmer/earnings.php', 'View/Farmer/reviews.php',
            'View/Farmer/report-issue.php', 'View/Farmer/notifications.php', 'View/Farmer/profile.php',
        ],
    ],
    'Courier Partner' => [
        'email' => 'courier@gmail.com',
        'seeds' => [
            'Controller/Courier/CourierController.php?page=dashboard',
            'Controller/Courier/CourierController.php?page=requests',
            'Controller/Courier/CourierController.php?page=assigned',
            'Controller/Courier/CourierController.php?page=coverage',
            'Controller/Courier/CourierController.php?page=history',
            'Controller/Courier/CourierController.php?page=earnings',
            'Controller/Courier/CourierController.php?page=complaints',
            'Controller/Courier/CourierController.php?page=notifications',
            'Controller/Courier/CourierController.php?page=profile',
        ],
    ],
    'Admin' => [
        'email' => 'admin@gmail.com',
        'seeds' => [
            'index.php?page=admin_overview', 'index.php?page=admin_users',
            'index.php?page=admin_farmer_approvals', 'index.php?page=admin_courier_approvals',
            'index.php?page=admin_listings', 'index.php?page=admin_categories',
            'index.php?page=admin_orders', 'index.php?page=admin_deliveries',
            'index.php?page=admin_pending_assignments', 'index.php?page=admin_district_distances',
            'index.php?page=admin_complaints', 'index.php?page=admin_payments',
            'index.php?page=admin_settlements', 'index.php?page=admin_settings',
            'index.php?page=admin_notifications', 'index.php?page=admin_reports',
            'index.php?page=admin_profile',
        ],
    ],
];

$problems = [];
$pagesChecked = 0;
$linksChecked = 0;

foreach ($actors as $actor => $cfg) {
    $jar = tempnam(sys_get_temp_dir(), 'nav');
    if (!loginAs($base, $jar, $cfg['email'], 'testpass123')) {
        $problems[] = "$actor: could not sign in as {$cfg['email']}";
        @unlink($jar);
        continue;
    }

    $queue = [];
    foreach ($cfg['seeds'] as $seed) {
        $queue[] = $base . $seed;
    }
    $seen = [];
    $fromPublic = strpos($base, 'Harvestly-final') !== false;

    while ($queue) {
        $url = array_shift($queue);
        if (isset($seen[$url])) {
            continue;
        }
        $seen[$url] = true;
        if (count($seen) > 160) {
            break;
        }

        $r = req($url, $jar);
        $pagesChecked++;
        $label = $actor . ' ' . str_replace($base, '', $url);

        if ($r['status'] !== 200) {
            $problems[] = "$label -> HTTP {$r['status']}";
            continue;
        }
        if (preg_match('/(Fatal error|Parse error|Warning:|Notice:|Deprecated:)/', $r['body'])) {
            $problems[] = "$label -> PHP diagnostic in output";
            continue;
        }

        // Links
        preg_match_all('/<a\b[^>]*\shref\s*=\s*["\']([^"\']*)["\']/i', $r['body'], $m);
        foreach ($m[1] as $href) {
            $trimmed = trim($href);
            if ($trimmed === '#' || $trimmed === '' || stripos($trimmed, 'javascript:') === 0) {
                $problems[] = "$label -> dead link href=\"$trimmed\"";
                continue;
            }
            $abs = absolutise($base, $r['final'], $href);
            if ($abs === null) {
                continue;
            }
            $linksChecked++;
            $check = req($abs, $jar);
            if ($check['status'] !== 200) {
                $problems[] = "$label -> link $href -> HTTP {$check['status']}";
            } elseif (preg_match('/page=login/', $check['final']) && !isAuthPage($abs)) {
                $problems[] = "$label -> link $href -> bounced to login";
            } else {
                $queue[] = $abs;
            }
        }

        // Images
        preg_match_all('/<img\b[^>]*\ssrc\s*=\s*["\']([^"\']*)["\']/i', $r['body'], $im);
        foreach ($im[1] as $src) {
            $abs = absolutise($base, $r['final'], $src);
            if ($abs === null) {
                continue;
            }
            $check = req($abs, $jar);
            if ($check['status'] !== 200) {
                $problems[] = "$label -> image $src -> HTTP {$check['status']}";
            }
        }

        // Form actions
        preg_match_all('/<form\b[^>]*\saction\s*=\s*["\']([^"\']*)["\']/i', $r['body'], $fm);
        foreach ($fm[1] as $action) {
            $abs = absolutise($base, $r['final'], $action);
            if ($abs === null) {
                continue;
            }
            $check = req($abs, $jar);
            if ($check['status'] !== 200) {
                $problems[] = "$label -> form action $action -> HTTP {$check['status']}";
            }
        }
    }
    @unlink($jar);
}

/* Public pages too. */
$jar = tempnam(sys_get_temp_dir(), 'nav');
$publicSeeds = [
    'index.php', 'index.php?page=products', 'index.php?page=login', 'index.php?page=role_select',
    'index.php?page=signup_buyer', 'index.php?page=signup_farmer', 'index.php?page=signup_courier',
    'index.php?page=forgot_password', 'index.php?page=reset_password', 'index.php?page=pending_approval',
];
foreach ($publicSeeds as $seed) {
    $url = $base . $seed;
    $r = req($url, $jar);
    $pagesChecked++;
    if ($r['status'] !== 200) {
        $problems[] = "Public $seed -> HTTP {$r['status']}";
        continue;
    }
    if (preg_match('/(Fatal error|Parse error|Warning:|Notice:|Deprecated:)/', $r['body'])) {
        $problems[] = "Public $seed -> PHP diagnostic in output";
    }
    preg_match_all('/<a\b[^>]*\shref\s*=\s*["\']([^"\']*)["\']/i', $r['body'], $m);
    foreach ($m[1] as $href) {
        $trimmed = trim($href);
        if ($trimmed === '#' || $trimmed === '') {
            $problems[] = "Public $seed -> dead link href=\"$trimmed\"";
            continue;
        }
        $abs = absolutise($base, $r['final'], $href);
        if ($abs === null) {
            continue;
        }
        $linksChecked++;
        $check = req($abs, $jar);
        if ($check['status'] !== 200) {
            $problems[] = "Public $seed -> link $href -> HTTP {$check['status']}";
        }
    }
    preg_match_all('/<img\b[^>]*\ssrc\s*=\s*["\']([^"\']*)["\']/i', $r['body'], $im);
    foreach ($im[1] as $src) {
        $abs = absolutise($base, $r['final'], $src);
        if ($abs === null) {
            continue;
        }
        $check = req($abs, $jar);
        if ($check['status'] !== 200) {
            $problems[] = "Public $seed -> image $src -> HTTP {$check['status']}";
        }
    }
}
@unlink($jar);

echo "Pages fetched : $pagesChecked\n";
echo "Links checked : $linksChecked\n\n";
if (!$problems) {
    echo "RESULT: OK - every page, link, image and form action resolves.\n";
    exit(0);
}
echo 'RESULT: ' . count($problems) . " problem(s):\n";
foreach (array_unique($problems) as $p) {
    echo "  - $p\n";
}
exit(1);
