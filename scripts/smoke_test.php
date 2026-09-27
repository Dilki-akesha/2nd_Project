<?php
/*
 * Harvestly interim smoke test.
 * Plain PHP - no external libraries. Uses cURL against the local XAMPP server.
 *
 * Usage: php scripts/smoke_test.php
 */

$base = getenv('HARVESTLY_BASE') ?: 'http://localhost/Harvestly-final/2nd_Project';
$pass = 0;
$fail = 0;
$failures = [];

function req(string $url, ?array $post = null, bool $follow = true): array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => $follow,
        CURLOPT_MAXREDIRS => 5,
        CURLOPT_TIMEOUT => 30,
    ]);
    // Use the per-role cookie jar so each actor keeps its own session.
    $jar = $GLOBALS['currentJar'] ?? '';
    if ($jar !== '') {
        curl_setopt($ch, CURLOPT_COOKIEJAR, $jar);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $jar);
    }
    if ($post !== null) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
    }
    $body = curl_exec($ch);
    $info = curl_getinfo($ch);
    $err = curl_error($ch);
    curl_close($ch);
    return ['status' => (int)$info['http_code'], 'url' => $info['url'], 'body' => (string)$body, 'error' => $err];
}

function csrf(string $url): string
{
    $r = req($url);
    if (preg_match('/name="csrf_token" value="([a-f0-9]+)"/i', $r['body'], $m)) return $m[1];
    if (preg_match('/content="([a-f0-9]{64})"/i', $r['body'], $m)) return $m[1];
    return '';
}

function check(string $label, bool $ok, string $detail = ''): void
{
    global $pass, $fail, $failures;
    if ($ok) {
        $pass++;
        echo "  PASS  $label\n";
    } else {
        $fail++;
        $failures[] = $label . ($detail ? " -- $detail" : '');
        echo "  FAIL  $label" . ($detail ? " -- $detail" : '') . "\n";
    }
}

function cookieJar(string $name): string
{
    $path = sys_get_temp_dir() . '/harvestly_' . $name . '.txt';
    if (file_exists($path)) @unlink($path);
    file_put_contents($path, '');
    return $path;
}

$GLOBALS['currentJar'] = '';

/* ------------------------------------------------------------------ */
echo "\n=== 1. Public pages (no authentication) ===\n";

$publicPages = [
    'index.php'                       => 'Home / Landing',
    'index.php?page=products'         => 'Public Catalogue',
    'index.php?page=login'            => 'Login',
    'index.php?page=role_select'      => 'Role Selection',
    'index.php?page=signup_buyer'     => 'Buyer Registration',
    'index.php?page=signup_farmer'    => 'Farmer Registration',
    'index.php?page=signup_courier'   => 'Courier Partner Registration',
    'index.php?page=forgot_password'  => 'Forgot Password',
    'index.php?page=reset_password'   => 'Reset Password (no token)',
    'index.php?page=pending_approval' => 'Pending Approval',
];
foreach ($publicPages as $q => $label) {
    $r = req("$base/$q");
    check("GET $label", $r['status'] === 200 && stripos($r['body'], 'Fatal error') === false
        && stripos($r['body'], 'Warning:') === false, "status {$r['status']} " . $r['error']);
}

/* Landing page must not reference the missing carrots.jpg any more. */
$landing = req("$base/index.php");
check('Landing has no broken carrots.jpg', strpos($landing['body'], 'carrots.jpg') === false);
check('Landing reads categories from the database', strpos($landing['body'], 'Explore Product Categories') !== false);
check('Landing has no "quality" grade copy', stripos($landing['body'], 'quality, and freshness') === false);

/* The project-scope explainer blocks were removed from the UI, so guard against
   them coming back on the public pages. */
foreach ([
    'Landing has no project-scope notice' => 'Harvestly project notice',
    'Landing has no prototype disclaimer' => 'university interim prototype',
    'Landing has no PayHere explainer' => 'PayHere Sandbox integration is planned',
] as $label => $needle) {
    check($label, stripos($landing['body'], $needle) === false);
}

/* Product details from the DB, not a hard-coded id. */
$prodList = req("$base/index.php?page=products");
preg_match_all('/page=product_details&amp;id=(\d+)/', $prodList['body'], $ids);
$productId = $ids[1][0] ?? null;
check('Catalogue exposes a real product id', $productId !== null, 'no product links found');
if ($productId) {
    $pd = req("$base/index.php?page=product_details&id=$productId");
    check('Public product details renders', $pd['status'] === 200 && strpos($pd['body'], 'Product not found') === false);
}
$badPd = req("$base/index.php?page=product_details&id=999999");
check('Unknown product id shows a not-found page', strpos($badPd['body'], 'Product not found') !== false);

/* ------------------------------------------------------------------ */
echo "\n=== 2. Out-of-scope feature sweep on public pages ===\n";

$banned = [
    'leaflet' => 'map library',
    'openstreetmap' => 'map service',
    'google.maps' => 'map API',
    'bootstrap' => 'CSS framework',
    'tailwind' => 'CSS framework',
    'jquery' => 'JS library',
    'fonts.googleapis' => 'external font',
    'cdn.' => 'CDN',
    'unpkg' => 'CDN',
    'jsdelivr' => 'CDN',
    'firebase' => 'push service',
    'material-icons' => 'icon font',
    'material-symbols' => 'icon font',
    'fa fa-' => 'icon font',
    'grade a' => 'quality grade',
    'grade b' => 'quality grade',
    'dispute' => 'dispute module',
    'otp' => 'OTP',
    'delivery zone' => 'delivery zone',
    'driver' => 'driver',
    'vehicle' => 'vehicle',
    'live tracking' => 'live tracking map',
];
$publicBodies = $landing['body'] . $prodList['body'];
foreach ($banned as $needle => $label) {
    check("Public pages free of $label", stripos($publicBodies, $needle) === false, "found '$needle'");
}

/* ------------------------------------------------------------------ */
echo "\n=== 3. Authentication: signup + login for every actor ===\n";

$stamp = substr((string)time(), -6);
$accounts = [
    'buyer' => [
        'email' => "smokebuyer$stamp@harvestly.lk",
        'post' => [
            'action' => 'signup_buyer',
            'full_name' => 'Smoke Test Buyer',
            'email' => "smokebuyer$stamp@harvestly.lk",
            'password' => 'TestPass123!',
            'confirm_password' => 'TestPass123!',
            'phone' => '0771234567',
            'district' => 'Kandy',
            'address' => '12 Test Lane, Kandy',
        ],
        'home' => 'Controller/Buyer/DashboardController.php',
    ],
    'farmer' => [
        'email' => "smokefarmer$stamp@harvestly.lk",
        'post' => [
            'action' => 'signup_farmer',
            'full_name' => 'Smoke Test Farmer',
            'email' => "smokefarmer$stamp@harvestly.lk",
            'password' => 'TestPass123!',
            'confirm_password' => 'TestPass123!',
            'phone' => '0771234568',
            'farm_name' => 'Smoke Test Farm',
            'farm_address' => 'Farm Road, Kandy',
            'district' => 'Kandy',
        ],
        'home' => 'index.php?page=pending_approval',
    ],
    'courier' => [
        'email' => "smokecourier$stamp@harvestly.lk",
        'post' => [
            'action' => 'signup_courier',
            'company_name' => 'Smoke Test Logistics Ltd',
            'contact_person' => 'Smoke Contact',
            'email' => "smokecourier$stamp@harvestly.lk",
            'password' => 'TestPass123!',
            'confirm_password' => 'TestPass123!',
            'phone' => '0112345678',
            'business_address' => '5 Depot Road, Colombo',
            'office_city' => 'Colombo',
            'office_postal' => '00100',
            'district' => 'Colombo',
        ],
        'home' => 'index.php?page=pending_approval',
    ],
];

/*
 * The Admin account password is read from the environment so no real
 * credential is ever hard-coded in a test file. Run
 * scripts/reset_demo_passwords.php first to set a known local value.
 */
$adminPassword = getenv('HARVESTLY_ADMIN_PASSWORD') ?: 'TestPass123!';

$accounts['admin'] = [
    'email' => 'admin@harvestly.lk',
    'password' => $adminPassword,
    'home' => 'index.php?page=admin_overview',
];
$accounts['buyer']['password'] = 'TestPass123!';
$accounts['farmer']['password'] = 'TestPass123!';
$accounts['courier']['password'] = 'TestPass123!';

$sessions = [];

foreach ($accounts as $role => $account) {
    $jar = cookieJar($role . $stamp);
    $GLOBALS['currentJar'] = $jar;

    if (isset($account['post'])) {
        $action = $account['post']['action'];
        $token = csrf("$base/index.php?page=$action");
        $r = req("$base/index.php?action=$action", $account['post'] + ['csrf_token' => $token]);
        $okBody = stripos($r['body'], 'Fatal error') === false
            && stripos($r['body'], 'could not be created') === false
            && stripos($r['body'], 'could not be submitted') === false
            && stripos($r['body'], 'form session expired') === false;
        check("Register as $role", $r['status'] === 200 && $okBody,
            'status ' . $r['status'] . ' ' . substr(preg_replace('/\s+/', ' ', strip_tags($r['body'])), 0, 140));
    }
    $GLOBALS['currentJar'] = '';
}

echo "  (registrations submitted; the pending accounts need Admin approval before login)\n";

/* ------------------------------------------------------------------ */
echo "\n=== 4. Role dashboards ===\n";

function loginAs(string $jarPath, string $email, string $password, string $base): array
{
    $GLOBALS['currentJar'] = $jarPath;
    $token = csrf("$base/index.php?page=login");
    $r = req("$base/index.php?action=login", ['csrf_token' => $token, 'email' => $email, 'password' => $password]);
    $GLOBALS['currentJar'] = '';
    return $r;
}

// Buyer: active immediately, should reach the Buyer dashboard.
$buyerJar = cookieJar("buyer_$stamp");
$r = loginAs($buyerJar, $accounts['buyer']['email'], 'TestPass123!', $base);
check('Buyer login redirects to the Buyer dashboard',
    strpos((string)$r['url'], 'DashboardController.php') !== false, (string)$r['url']);

$GLOBALS['currentJar'] = $buyerJar;
$buyerPages = [
    'Controller/Buyer/DashboardController.php' => 'Buyer Dashboard',
    'Controller/Buyer/ProductController.php'   => 'Buyer Browse Products',
    'Controller/Buyer/ProductDetailsController.php?id=' . $productId => 'Buyer Product Details',
    'Controller/Buyer/CartController.php'      => 'Buyer Cart',
    'Controller/Buyer/OrdersController.php'    => 'Buyer Orders',
    'Controller/Buyer/OrderTrackingController.php?id=999999' => 'Buyer Order Details (unknown order is refused)',
    'Controller/Buyer/FeedbackController.php'  => 'Buyer Reviews and Issues',
    'Controller/Buyer/NotificationsController.php' => 'Buyer Notifications',
    'Controller/Buyer/ProfileController.php'   => 'Buyer Profile',
    'Controller/Buyer/CheckoutController.php'  => 'Buyer Checkout',
    'Controller/Buyer/FarmerStoreController.php' => 'Buyer Farmer Store (redirects without ?farmer=)',
];
foreach ($buyerPages as $q => $label) {
    $r = req("$base/$q");
    $notFound = strpos((string)$r['body'], 'Order not found') !== false;
    $ok = $notFound
        || ($r['status'] === 200
            && stripos($r['body'], 'Fatal error') === false
            && stripos($r['body'], 'Warning:') === false
            && stripos($r['body'], 'Undefined') === false);
    check("Buyer: $label", $ok, "status {$r['status']} " . $r['error']);
}
$GLOBALS['currentJar'] = '';

/* Buyer cart CRUD against the database. */
echo "\n=== 5. Buyer cart CRUD (real database) ===\n";
$GLOBALS['currentJar'] = $buyerJar;

if ($productId) {
    $token = csrf("$base/Controller/Buyer/ProductDetailsController.php?id=$productId");
    $r = req("$base/Controller/Buyer/ProductController.php", [
        'csrf_token' => $token, 'action' => 'add_to_cart', 'id' => $productId, 'qty' => 2,
    ]);
    check('Cart CREATE: add product to cart', strpos((string)$r['body'], 'Added to your cart') !== false,
        substr(trim(strip_tags($r['body'])), 0, 120));

    $cart = req("$base/Controller/Buyer/CartController.php");
    check('Cart READ: item appears in the cart table', strpos($cart['body'], 'Cart subtotal') !== false);

    $token = csrf("$base/Controller/Buyer/CartController.php");
    $r = req("$base/Controller/Buyer/CartController.php", [
        'csrf_token' => $token, 'action' => 'update', 'id' => $productId, 'quantity' => 5,
    ]);
    check('Cart UPDATE: change quantity', strpos((string)$r['body'], 'Cart updated.') !== false);

    $cart = req("$base/Controller/Buyer/CartController.php");
    check('Cart UPDATE persisted (quantity 5 shown)',
        strpos($cart['body'], 'value="5"') !== false || strpos($cart['body'], 'value="5.000"') !== false);

    $token = csrf("$base/Controller/Buyer/CartController.php");
    $r = req("$base/Controller/Buyer/CartController.php", [
        'csrf_token' => $token, 'action' => 'remove', 'id' => $productId,
    ]);
    check('Cart DELETE: remove item', strpos((string)$r['body'], 'Item removed') !== false);

    $cart = req("$base/Controller/Buyer/CartController.php");
    check('Cart DELETE persisted (empty state shown)', stripos($cart['body'], 'Your cart is empty') !== false);

    // Create again, then Clear Cart.
    $token = csrf("$base/Controller/Buyer/CartController.php");
    req("$base/Controller/Buyer/ProductController.php", [
        'csrf_token' => $token, 'action' => 'add_to_cart', 'id' => $productId, 'qty' => 1,
    ]);
    $token = csrf("$base/Controller/Buyer/CartController.php");
    $r = req("$base/Controller/Buyer/CartController.php", ['csrf_token' => $token, 'action' => 'clear']);
    check('Cart DELETE: clear cart', strpos((string)$r['body'], 'cleared') !== false);
} else {
    check('Cart CRUD skipped (no product available)', true);
}
$GLOBALS['currentJar'] = '';

/* ------------------------------------------------------------------ */
echo "\n=== 6. Pending Farmer / Courier cannot reach dashboards ===\n";

$farmerJar = cookieJar("farmer_$stamp");
$r = loginAs($farmerJar, $accounts['farmer']['email'], 'TestPass123!', $base);
check('Pending Farmer login is blocked',
    strpos((string)$r['url'], 'pending_approval') !== false, (string)$r['url']);

$GLOBALS['currentJar'] = $farmerJar;
$r = req("$base/View/Farmer/dashboard.php");
check('Pending Farmer dashboard is refused (redirects to login)',
    strpos((string)$r['url'], 'page=login') !== false, (string)$r['url']);
$GLOBALS['currentJar'] = '';

$courierJar = cookieJar("courier_$stamp");
$r = loginAs($courierJar, $accounts['courier']['email'], 'TestPass123!', $base);
check('Pending Courier Partner login is blocked',
    strpos((string)$r['url'], 'pending_approval') !== false, (string)$r['url']);

$GLOBALS['currentJar'] = $courierJar;
$r = req("$base/Controller/Courier/CourierController.php?page=dashboard");
check('Pending Courier Partner dashboard is refused',
    strpos((string)$r['url'], 'page=login') !== false, (string)$r['url']);
$GLOBALS['currentJar'] = '';

/* ------------------------------------------------------------------ */
echo "\n=== 7. Cross-role access control ===\n";

/*
 * A signed-in user who opens another role's page is redirected to their OWN
 * dashboard, never to the other role's dashboard and never into a loop.
 */
$GLOBALS['currentJar'] = $buyerJar;
$r = req("$base/View/Farmer/dashboard.php");
check('Buyer opening the Farmer page lands on the Buyer dashboard',
    strpos((string)$r['url'], 'DashboardController.php') !== false, (string)$r['url']);
$r = req("$base/Controller/Courier/CourierController.php?page=dashboard");
check('Buyer opening the Courier page lands on the Buyer dashboard',
    strpos((string)$r['url'], 'DashboardController.php') !== false, (string)$r['url']);
$r = req("$base/index.php?page=admin_overview");
check('Buyer opening the Admin console lands on the Buyer dashboard',
    strpos((string)$r['url'], 'DashboardController.php') !== false, (string)$r['url']);
$GLOBALS['currentJar'] = '';

/* ------------------------------------------------------------------ */
echo "\n=== 8. Admin console ===\n";

$adminJar = cookieJar("admin_$stamp");
$r = loginAs($adminJar, 'admin@harvestly.lk', $adminPassword, $base);
check('Admin login redirects to the Admin console',
    strpos((string)$r['url'], 'admin_overview') !== false, (string)$r['url']);

$adminPages = [
    'admin_overview', 'admin_users', 'admin_farmer_approvals', 'admin_courier_approvals',
    'admin_listings', 'admin_categories', 'admin_orders', 'admin_deliveries',
    'admin_pending_assignments', 'admin_district_distances', 'admin_complaints',
    'admin_payments', 'admin_settlements', 'admin_settings', 'admin_notifications',
    'admin_reports', 'admin_profile',
];
$GLOBALS['currentJar'] = $adminJar;
foreach ($adminPages as $page) {
    $r = req("$base/index.php?page=$page");
    check("Admin page: $page", $r['status'] === 200
        && stripos($r['body'], 'Fatal error') === false
        && stripos($r['body'], 'Warning:') === false
        && stripos($r['body'], 'Undefined') === false,
        "status {$r['status']}");
}

/* Approve the freshly created pending accounts through the real Admin action. */
echo "\n=== 9. Admin approvals ===\n";

/**
 * Find the approve-form hidden id belonging to a specific account email.
 * The approvals table lists every pending account, so the row must be matched
 * by email rather than taking the first row.
 */
function findApprovalId(string $html, string $field, string $email): ?string
{
    // Each table row contains the account email plus its hidden id field.
    if (preg_match_all('/<tr>(.*?)<\/tr>/s', $html, $rows)) {
        foreach ($rows[1] as $row) {
            if (stripos($row, $email) === false) continue;
            if (preg_match('/name="' . preg_quote($field, '/') . '" value="(\d+)"/', $row, $m)) {
                return $m[1];
            }
        }
    }
    return null;
}

$approvals = req("$base/index.php?page=admin_farmer_approvals");
$farmerToApprove = findApprovalId($approvals['body'], 'farmer_id', $accounts['farmer']['email']);
if ($farmerToApprove) {
    $token = csrf("$base/index.php?page=admin_farmer_approvals");
    $r = req("$base/index.php?admin_action=verify_farmer", [
        'csrf_token' => $token, 'farmer_id' => $farmerToApprove, 'status' => 'approved',
    ]);
    check('Admin approves the smoke-test Farmer',
        strpos((string)$r['url'], 'admin_farmer_approvals') !== false, (string)$r['url']);
} else {
    check('Admin approves the smoke-test Farmer', false, 'smoke Farmer not found on the approvals page');
}

$courierApprovals = req("$base/index.php?page=admin_courier_approvals");
$courierToApprove = findApprovalId($courierApprovals['body'], 'courier_id', $accounts['courier']['email']);
if ($courierToApprove) {
    $token = csrf("$base/index.php?page=admin_courier_approvals");
    $r = req("$base/index.php?admin_action=verify_courier", [
        'csrf_token' => $token, 'courier_id' => $courierToApprove, 'status' => 'approved',
    ]);
    check('Admin approves the smoke-test Courier Partner',
        strpos((string)$r['url'], 'admin_courier_approvals') !== false, (string)$r['url']);
} else {
    check('Admin approves the smoke-test Courier Partner', false, 'smoke Courier not found on the approvals page');
}

/* The Admin pages must show no PHP diagnostics once real data is present. */
foreach (['admin_farmer_approvals', 'admin_courier_approvals'] as $page) {
    $r = req("$base/index.php?page=$page");
    $found = '';
    foreach (['Warning:', 'Fatal error:', 'Notice:', 'Deprecated:', 'Undefined '] as $n) {
        if (stripos($r['body'], $n) !== false) $found = $n;
    }
    check("Admin page $page has no PHP diagnostics", $found === '', $found);
}

/* ------------------------------------------------------------------ */
echo "\n=== 10. Approved Farmer module + product CRUD ===\n";

$farmerJar = cookieJar("farmer_$stamp");
$r = loginAs($farmerJar, $accounts['farmer']['email'], 'TestPass123!', $base);
check('Approved Farmer login reaches the Farmer dashboard',
    strpos((string)$r['url'], 'View/Farmer/dashboard.php') !== false, (string)$r['url']);

$GLOBALS['currentJar'] = $farmerJar;
$farmerPages = [
    'dashboard.php'        => 'Farmer Dashboard',
    'products.php'         => 'Farmer Products',
    'add-product.php'      => 'Farmer Add Product',
    'inventory.php'        => 'Farmer Inventory',
    'orders.php'           => 'Farmer Orders',
    'harvest-soon.php'     => 'Farmer Pre-Listings',
    'sales.php'            => 'Farmer Sales',
    'earnings.php'         => 'Farmer Earnings',
    'reviews.php'          => 'Farmer Reviews',
    'report-issue.php'     => 'Farmer Report Issue',
    'notifications.php'    => 'Farmer Notifications',
    'profile.php'          => 'Farmer Profile',
];
foreach ($farmerPages as $q => $label) {
    $r = req("$base/View/Farmer/$q");
    check("Farmer page: $label", $r['status'] === 200
        && stripos($r['body'], 'Fatal error') === false
        && stripos($r['body'], 'Warning:') === false
        && stripos($r['body'], 'Undefined') === false,
        "status {$r['status']}");
}

// CREATE
$token = csrf("$base/View/Farmer/add-product.php");
$r = req("$base/View/Farmer/add-product.php", [
    'csrf_token' => $token,
    'product_name' => "Smoke Test Product $stamp",
    'category_id' => '1',
    'description' => 'Created by the Harvestly smoke test.',
    'unit_price' => '125.50',
    'available_quantity' => '40',
    'unit_label' => 'kg',
    'listing_type' => 'AVAILABLE_NOW',
    'growing_method' => 'ORGANIC',
    'listing_status' => 'ACTIVE',
]);
$created = strpos((string)$r['body'], 'Product saved successfully') !== false;
check('Farmer CRUD CREATE: product created', $created, substr(trim(strip_tags($r['body'])), 0, 160));

// READ + find the new product id
$products = req("$base/View/Farmer/products.php");
$newProductId = null;
if (preg_match('/product-details\.php\?id=(\d+)">\s*View/', $products['body'], $m)) $newProductId = $m[1];
if (preg_match('/href="product-details\.php\?id=(\d+)"/', $products['body'], $m)) $newProductId = $m[1];
check('Farmer CRUD READ: new product listed', strpos($products['body'], "Smoke Test Product $stamp") !== false);
check('Farmer CRUD: product id resolvable', $newProductId !== null);

if ($newProductId) {
    // UPDATE
    $token = csrf("$base/View/Farmer/edit-product.php?id=$newProductId");
    $r = req("$base/View/Farmer/edit-product.php?id=$newProductId", [
        'csrf_token' => $token,
        'product_name' => "Smoke Test Product $stamp EDITED",
        'category_id' => '1',
        'description' => 'Updated by the Harvestly smoke test.',
        'unit_price' => '150.00',
        'available_quantity' => '60',
        'unit_label' => 'kg',
        'listing_type' => 'AVAILABLE_NOW',
        'growing_method' => 'CONVENTIONAL',
        'listing_status' => 'ACTIVE',
    ]);
    check('Farmer CRUD UPDATE: product updated', strpos((string)$r['body'], 'Product saved successfully') !== false,
        substr(trim(strip_tags($r['body'])), 0, 160));

    $detail = req("$base/View/Farmer/product-details.php?id=$newProductId");
    check('Farmer CRUD UPDATE persisted',
        strpos($detail['body'], "Smoke Test Product $stamp EDITED") !== false
        && strpos($detail['body'], '150.00') !== false);

    // DELETE
    $token = csrf("$base/View/Farmer/products.php");
    $r = req("$base/View/Farmer/products.php", ['csrf_token' => $token, 'delete_id' => $newProductId]);
    check('Farmer CRUD DELETE: product deleted', strpos((string)$r['body'], 'Product deleted.') !== false,
        substr(trim(strip_tags($r['body'])), 0, 160));

    $products = req("$base/View/Farmer/products.php");
    check('Farmer CRUD DELETE persisted',
        strpos($products['body'], "Smoke Test Product $stamp EDITED") === false);
}
$GLOBALS['currentJar'] = '';

/* ------------------------------------------------------------------ */
echo "\n=== 11. Courier Partner module + coverage route CRUD ===\n";

$courierJar = cookieJar("courier_$stamp");
$r = loginAs($courierJar, $accounts['courier']['email'], 'TestPass123!', $base);
check('Approved Courier Partner login reaches the Courier dashboard',
    strpos((string)$r['url'], 'CourierController.php') !== false, (string)$r['url']);

$GLOBALS['currentJar'] = $courierJar;
$courierBase = "$base/Controller/Courier/CourierController.php";
$courierPages = [
    'dashboard'     => 'Courier Dashboard',
    'requests'      => 'Courier Assignment Offers',
    'assigned'      => 'Courier Active Deliveries',
    'coverage'      => 'Courier Coverage Routes',
    'history'       => 'Courier Delivery History',
    'earnings'      => 'Courier Earnings',
    'complaints'    => 'Courier Complaints',
    'notifications' => 'Courier Notifications',
    'profile'       => 'Courier Organisation Profile',
];
foreach ($courierPages as $page => $label) {
    $r = req("$courierBase?page=$page");
    check("Courier page: $label", $r['status'] === 200
        && stripos($r['body'], 'Fatal error') === false
        && stripos($r['body'], 'Warning:') === false
        && stripos($r['body'], 'Undefined') === false,
        "status {$r['status']}");
}
$r = req("$courierBase?page=not_a_real_page");
check('Courier unknown page falls back to the dashboard',
    stripos($r['body'], 'Availability') !== false);

// Coverage route CREATE
$token = csrf("$courierBase?page=coverage");
$r = req($courierBase, [
    'csrf_token' => $token, 'action' => 'add_route',
    'origin_district_id' => '12', 'destination_district_id' => '20', 'return_page' => 'coverage',
]);
check('Courier CRUD CREATE: coverage route added', strpos((string)$r['body'], 'Coverage route added') !== false,
    substr(trim(strip_tags($r['body'])), 0, 200));

$coverage = req("$courierBase?page=coverage");
check('Courier CRUD READ: route visible', strpos($coverage['body'], 'Kandy') !== false);

if (preg_match('/name="route_id" value="(\d+)"/', $coverage['body'], $m)) {
    $routeId = $m[1];
    // UPDATE (deactivate)
    $token = csrf("$courierBase?page=coverage");
    $r = req($courierBase, [
        'csrf_token' => $token, 'action' => 'update_route', 'route_id' => $routeId,
        'origin_district_id' => '12', 'destination_district_id' => '20',
        'return_page' => 'coverage',
    ]);
    check('Courier CRUD UPDATE: route deactivated', strpos((string)$r['body'], 'Coverage route updated') !== false,
        substr(trim(strip_tags($r['body'])), 0, 200));

    $coverage = req("$courierBase?page=coverage");
    check('Courier CRUD UPDATE persisted (Inactive badge)',
        strpos($coverage['body'], 'Inactive') !== false);

    // Reactivate then DELETE
    $token = csrf("$courierBase?page=coverage");
    req($courierBase, [
        'csrf_token' => $token, 'action' => 'update_route', 'route_id' => $routeId,
        'origin_district_id' => '12', 'destination_district_id' => '20', 'is_active' => '1',
        'return_page' => 'coverage',
    ]);
    $token = csrf("$courierBase?page=coverage");
    $r = req($courierBase, [
        'csrf_token' => $token, 'action' => 'delete_route', 'route_id' => $routeId,
        'return_page' => 'coverage',
    ]);
    check('Courier CRUD DELETE: route removed', strpos((string)$r['body'], 'Coverage route removed') !== false,
        substr(trim(strip_tags($r['body'])), 0, 200));
}

// Availability toggle
$token = csrf("$courierBase?page=dashboard");
$r = req($courierBase, ['csrf_token' => $token, 'action' => 'availability', 'status' => 'AVAILABLE', 'return_page' => 'dashboard']);
check('Courier availability toggle works', strpos((string)$r['body'], 'marked as available') !== false,
    substr(trim(strip_tags($r['body'])), 0, 200));
$GLOBALS['currentJar'] = '';

/* ------------------------------------------------------------------ */
echo "\n=== 12. Admin category CRUD (Admin's CRUD entity) ===\n";

$GLOBALS['currentJar'] = $adminJar;
$catName = "Smoke Category $stamp";
$token = csrf("$base/index.php?page=admin_categories");
$r = req("$base/index.php?admin_action=create_category", [
    'csrf_token' => $token, 'category_name' => $catName, 'description' => 'Smoke test category',
]);
check('Admin CRUD CREATE: category created',
    strpos((string)$r['url'], 'admin_categories') !== false
    && stripos((string)$r['body'], 'Unable to create') === false,
    (string)$r['url']);

$cats = req("$base/index.php?page=admin_categories");
check('Admin CRUD READ: category listed', strpos($cats['body'], $catName) !== false);

if (preg_match('/update_category.*?category_id" value="(\d+)"/s', $cats['body'], $m)
    || preg_match('/name="category_id" value="(\d+)"/', $cats['body'], $m)) {
    $catId = $m[1];
    $token = csrf("$base/index.php?page=admin_categories");
    $r = req("$base/index.php?admin_action=update_category", [
        'csrf_token' => $token, 'category_id' => $catId,
        'category_name' => "$catName EDITED", 'description' => 'Updated', 'status' => 'active',
    ]);
    check('Admin CRUD UPDATE: category updated',
        strpos((string)$r['url'], 'admin_categories') !== false
        && stripos((string)$r['body'], 'could not be updated') === false,
        (string)$r['url']);

    $cats = req("$base/index.php?page=admin_categories");
    check('Admin CRUD UPDATE persisted', strpos($cats['body'], "$catName EDITED") !== false);

    $token = csrf("$base/index.php?page=admin_categories");
    $r = req("$base/index.php?admin_action=delete_category", [
        'csrf_token' => $token, 'category_id' => $catId,
    ]);
    check('Admin CRUD DELETE: category deleted',
        strpos((string)$r['url'], 'admin_categories') !== false
        && stripos((string)$r['body'], 'could not be deleted') === false,
        (string)$r['url']);

    $cats = req("$base/index.php?page=admin_categories");
    check('Admin CRUD DELETE persisted', strpos($cats['body'], "$catName EDITED") === false);
}
$GLOBALS['currentJar'] = '';

/* ------------------------------------------------------------------ */
echo "\n=== 13. Out-of-scope sweep on all role pages ===\n";

$allBodies = '';
foreach (['Controller/Buyer/DashboardController.php', 'Controller/Buyer/CheckoutController.php',
          'Controller/Buyer/FeedbackController.php', 'Controller/Buyer/OrderTrackingController.php',
          'Controller/Buyer/ProductDetailsController.php', 'Controller/Buyer/ProfileController.php'] as $q) {
    $GLOBALS['currentJar'] = $buyerJar;
    $allBodies .= req("$base/$q")['body'];
}
$GLOBALS['currentJar'] = $farmerJar;
foreach (array_keys($farmerPages) as $q) $allBodies .= req("$base/View/Farmer/$q")['body'];
$GLOBALS['currentJar'] = $courierJar;
foreach (array_keys($courierPages) as $p) $allBodies .= req("$courierBase?page=$p")['body'];
$GLOBALS['currentJar'] = $adminJar;
foreach ($adminPages as $p) $allBodies .= req("$base/index.php?page=$p")['body'];
$GLOBALS['currentJar'] = '';

$roleBanned = [
    'fonts.googleapis' => 'external font',
    'cdn.jsdelivr'     => 'CDN',
    'unpkg.com'        => 'CDN',
    'bootstrap'        => 'CSS framework',
    'tailwind'         => 'CSS framework',
    'jquery'           => 'JS library',
    'firebase'         => 'push service',
    'google.maps'      => 'map API',
    'leaflet'          => 'map library',
    'openstreetmap'    => 'map service',
    'new PDO'          => 'PDO',
    '$pdo'             => 'PDO',
    'delivery zone'    => 'delivery zone',
    'quality grade'    => 'quality grade',
    'Grade A'          => 'quality grade',
    '90/10'            => 'fixed revenue split',
    'PayHere Sandbox is now integrated' => 'false payment claim',
];
foreach ($roleBanned as $needle => $label) {
    check("Role pages free of $label", stripos($allBodies, $needle) === false, "found '$needle'");
}
check('Role pages free of href="#" dead links', strpos($allBodies, 'href="#"') === false);
check('Role pages free of "Coming Soon"', stripos($allBodies, 'Coming Soon') === false
    && stripos($allBodies, 'coming soon') === false);

/* Actors must only be Buyer / Farmer / Courier Partner / Admin. */
foreach (['Home Gardener', 'home_gardener', 'Delivery Person', 'delivery_person', 'Seller account', 'Rider'] as $needle) {
    check("No legacy actor '$needle'", stripos($allBodies, $needle) === false);
}

/* ------------------------------------------------------------------ */
echo "\n=== 14. CSRF protection ===\n";
$GLOBALS['currentJar'] = $buyerJar;
$r = req("$base/Controller/Buyer/CartController.php?x=1", ['action' => 'clear']);
check('Cart POST without a CSRF token is rejected',
    stripos($r['body'], 'form session expired') !== false || $r['status'] === 403,
    "status {$r['status']}");

$GLOBALS['currentJar'] = $adminJar;
$r = req("$base/index.php?admin_action=delete_category", ['category_id' => '1']);
check('Admin POST without a CSRF token is rejected',
    stripos($r['body'], 'form session expired') !== false || $r['status'] === 403,
    "status {$r['status']}");
$GLOBALS['currentJar'] = '';

/* ------------------------------------------------------------------ */
echo "\n=== 15. Logout ===\n";
$GLOBALS['currentJar'] = $buyerJar;
$token = csrf("$base/Controller/Buyer/DashboardController.php");
$r = req("$base/index.php?action=logout", ['csrf_token' => $token]);
check('Buyer logout returns to the login page',
    strpos((string)$r['url'], 'page=login') !== false, (string)$r['url']);
$r = req("$base/Controller/Buyer/DashboardController.php");
check('Buyer session is gone after logout',
    strpos((string)$r['url'], 'page=login') !== false, (string)$r['url']);
$GLOBALS['currentJar'] = '';

/* ------------------------------------------------------------------ */
echo "\n=== 16. No PHP warnings on any page ===\n";
$GLOBALS['currentJar'] = $buyerJar;
$noise = ['Warning:', 'Fatal error:', 'Notice:', 'Deprecated:', 'Undefined '];
foreach (['Controller/Buyer/DashboardController.php', 'Controller/Buyer/Products.php',
          'Controller/Buyer/CartController.php', 'Controller/Buyer/OrdersController.php',
          'Controller/Buyer/NotificationsController.php', 'Controller/Buyer/ProfileController.php',
          'Controller/Buyer/FeedbackController.php'] as $q) {
    $r = req("$base/$q");
    $found = '';
    foreach ($noise as $n) if (stripos($r['body'], $n) !== false) $found = $n;
    check("No PHP diagnostics in $q", $found === '', $found);
}
$GLOBALS['currentJar'] = '';

/* ------------------------------------------------------------------ */
echo "\n=====================================\n";
echo "PASSED: $pass   FAILED: $fail\n";
if ($failures) {
    echo "\nFailures:\n";
    foreach ($failures as $f) echo "  - $f\n";
}
echo "=====================================\n";
exit($fail > 0 ? 1 : 0);
