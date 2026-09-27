<?php
/**
 * Harvestly model-level integration test.
 *
 * Exercises the full order lifecycle directly against the database, without
 * going through HTTP. Every fixture row created here is removed again at the
 * end, so seed and customer records are never touched.
 *
 * Run from the command line:
 *   php scripts/integration-test.php
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Command-line only.');
}

ini_set('session.save_path', __DIR__ . '/sessions');
$_SERVER['DOCUMENT_ROOT'] = 'C:/xampp/htdocs';
$_SERVER['SCRIPT_NAME'] = '/Harvestly/scripts/integration-test.php';

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../Model/Auth/AuthModel.php';
require_once __DIR__ . '/../Model/Admin/AdminModel.php';
require_once __DIR__ . '/../Model/Farmer/FarmerProductModel.php';
require_once __DIR__ . '/../Model/Farmer/FarmerOrderModel.php';
require_once __DIR__ . '/../Model/Buyer/Cart.php';
require_once __DIR__ . '/../Model/Buyer/Checkout.php';
require_once __DIR__ . '/../Model/Buyer/Feedback.php';
require_once __DIR__ . '/../Model/Courier/CourierModel.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
db();

$passed = [];
$failure = false;
$fixtureUsers = [];
$fixtureOrders = [];
$fixtureProducts = [];
$fixtureCategory = 0;

function check(bool $condition, string $label): void
{
    global $passed;
    if (!$condition) {
        throw new RuntimeException($label);
    }
    $passed[] = $label;
}

function asRole(int $userId, string $role): void
{
    $_SESSION = [
        'user_id' => $userId,
        'role' => $role,
        'user_name' => 'Integration test',
    ];
}

try {
    $suffix = bin2hex(random_bytes(5));
    $password = 'TestOnly-' . bin2hex(random_bytes(8));
    $hash = password_hash($password, PASSWORD_BCRYPT);

    $auth = new AuthModel();
    $admin = new AdminModel();
    $farmerProducts = new FarmerProductModel();
    $farmerOrders = new FarmerOrderModel();
    $courier = new CourierModel();
    $feedback = new Feedback();

    // A district route that no Courier Partner currently covers, so the fixtures
    // are isolated from the seeded coverage routes.
    $route = db_fetch_one(
        "SELECT dd.from_district_id AS origin, dd.to_district_id AS destination,
                a.district_name AS origin_name, b.district_name AS destination_name
         FROM district_distances dd
         JOIN districts a ON a.district_id = dd.from_district_id
         JOIN districts b ON b.district_id = dd.to_district_id
         WHERE dd.from_district_id <> dd.to_district_id
           AND NOT EXISTS (
               SELECT 1 FROM courier_coverage_routes r
               WHERE r.origin_district_id = dd.from_district_id
                 AND r.destination_district_id = dd.to_district_id
           )
         LIMIT 1"
    );
    check((bool)$route, 'An unused district route is available for isolated fixtures');

    /* ----------------------- Registration and approval --------------------- */

    foreach (['buyer', 'farmer', 'courier'] as $role) {
        $email = "audit-$role-$suffix@example.test";
        $name = 'Audit ' . ucfirst($role) . ' ' . $suffix;

        $ok = match ($role) {
            'buyer' => $auth->registerBuyer(
                $name, $email, $hash, '0771234567',
                $route['destination_name'], 'Test address'
            ),
            'farmer' => $auth->registerFarmer(
                $name, $email, $hash, '0771234567', 'Test farm', 'Test farm address',
                $route['origin_name'], null
            ),
            'courier' => $auth->registerCourierPartner(
                $name, 'Test Contact', $email, $hash, '0771234567', 'Test office',
                '', '', $route['origin_name'], null
            ),
        };
        check($ok, "$role registration");

        $user = $auth->findUserByEmail($email);
        $fixtureUsers[$role] = (int)$user['id'];
        check(password_verify($password, $user['password_hash']), "$role password hash verifies");
        check(
            $user['status'] === ($role === 'buyer' ? 'approved' : 'pending'),
            "$role initial account status is " . ($role === 'buyer' ? 'Active' : 'Pending')
        );
    }

    check(
        !$auth->registerBuyer('Bad', "bad-$suffix@example.test", $hash, '0771234567', 'Invalid District', 'x'),
        'Registration with an unknown district is rejected'
    );

    check(
        $admin->updateFarmerVerification($fixtureUsers['farmer'], 'approved'),
        'Admin approves the Farmer'
    );
    check(
        $admin->updateCourierVerification($fixtureUsers['courier'], 'approved'),
        'Admin approves the Courier Partner'
    );
    check(
        $auth->findUserByEmail("audit-farmer-$suffix@example.test")['status'] === 'approved',
        'Approved Farmer can authenticate'
    );
    check(
        $auth->findUserByEmail("audit-courier-$suffix@example.test")['status'] === 'approved',
        'Approved Courier Partner can authenticate'
    );

    /* ------------------------- Password recovery -------------------------- */

    $token = $auth->createPasswordResetToken("audit-buyer-$suffix@example.test");
    check((bool)$token, 'A local password reset token is issued');
    check($auth->resetPassword($token, $hash), 'Password reset succeeds');
    check(!$auth->resetPassword($token, $hash), 'A reset token cannot be reused');

    /* --------------------- Admin CRUD: product categories ----------------- */

    check($admin->createCategory('Audit category ' . $suffix, 'Test', 'active'), 'Admin category Create');
    $fixtureCategory = (int)db()->insert_id;
    check((bool)$admin->getCategoryById($fixtureCategory), 'Admin category Read');
    check(
        $admin->updateCategory($fixtureCategory, 'Audit updated ' . $suffix, 'Updated', 'active'),
        'Admin category Update'
    );

    /* ------------------------ Farmer CRUD: products ----------------------- */

    $product = [
        'product_name' => 'Audit produce ' . $suffix,
        'category_id' => $fixtureCategory,
        'unit_price' => 100,
        'available_quantity' => 20,
        'unit_label' => 'kg',
        'listing_type' => 'AVAILABLE_NOW',
        'listing_status' => 'ACTIVE',
        'growing_method' => 'CONVENTIONAL',
    ];

    $productId = $farmerProducts->save($fixtureUsers['farmer'], $product);
    $fixtureProducts[] = $productId;
    check($productId > 0, 'Farmer product Create');
    check((bool)$farmerProducts->find($productId, $fixtureUsers['farmer']), 'Farmer product Read');

    $product['unit_price'] = 125;
    $farmerProducts->save($fixtureUsers['farmer'], $product, $productId);
    check(
        (float)$farmerProducts->find($productId, $fixtureUsers['farmer'])['unit_price'] === 125.0,
        'Farmer product Update'
    );
    check(
        !$farmerProducts->find($productId, (int)db_scalar("SELECT user_id FROM users WHERE role='FARMER' AND user_id<>? LIMIT 1", 'i', [$fixtureUsers['farmer']], 0)),
        'Farmer product ownership is enforced'
    );

    $disposable = $farmerProducts->save($fixtureUsers['farmer'], $product);
    $fixtureProducts[] = $disposable;
    check($farmerProducts->delete($disposable, $fixtureUsers['farmer']), 'Farmer product Delete');

    /* ------------------------- Buyer CRUD: cart items --------------------- */

    asRole($fixtureUsers['buyer'], 'buyer');
    $cart = new Cart();
    check($cart->add($productId, 2), 'Buyer cart Create');
    check(count($cart->getItems()) === 1, 'Buyer cart Read');
    check($cart->updateQuantity($productId, 3), 'Buyer cart Update');
    check((float)$cart->getItems()[0]['quantity'] === 3.0, 'Buyer quantity update is persisted');
    check($cart->remove($productId), 'Buyer cart Delete');
    check(!$cart->getItems(), 'Buyer cart deletion is persisted');
    $cart->add($productId, 2);

    $checkout = new Checkout();
    $quote = $checkout->quote($cart->getItems(), $route['destination_name']);
    check($quote['routeAvailable'] === false, 'Checkout quote reports an uncovered route before the partner is set up');

    /* --------------- Courier Partner CRUD: coverage routes --------------- */

    $cid = $fixtureUsers['courier'];
    check($courier->addRoute($cid, (int)$route['origin'], (int)$route['destination']), 'Courier route Create');
    check(count($courier->routes($cid)) === 1, 'Courier route Read');

    $routeId = (int)$courier->routes($cid)[0]['route_id'];
    check(
        $courier->updateRoute($cid, $routeId, (int)$route['origin'], (int)$route['destination'], false),
        'Courier route Update'
    );
    check(
        (int)$courier->routes($cid)[0]['is_active'] === 0,
        'Courier route update is persisted'
    );
    check($courier->deleteRoute($cid, $routeId), 'Courier route Delete');
    check(!$courier->routes($cid), 'Courier route deletion is persisted');

    $courier->addRoute($cid, (int)$route['origin'], (int)$route['destination']);
    $courier->setAvailability($cid, 'AVAILABLE');

    /* --------------------------- Checkout flow ---------------------------- */

    $cartItems = $cart->getItems();
    check(
        $courier->addRoute($cid, (int)$route['origin'], (int)$route['destination']),
        'Courier coverage is set up for the fixture route'
    );
    $quote = $checkout->quote($cart->getItems(), $route['destination_name']);
    check($quote['routeAvailable'] === true, 'Checkout quote reports the route once the partner covers it');
    $quoted = $checkout->fees($quote);
    check((float)$quoted['deliveryFee'] > 0, 'Checkout quote applies a delivery fee');
    check(
        (float)$quoted['total'] === round(
            (float)$quoted['subtotal'] + (float)$quoted['serviceFee'] + (float)$quoted['deliveryFee'],
            2
        ),
        'Checkout total equals subtotal plus both fees'
    );
    $cartItems = $cart->getItems();

    $order = $checkout->placeOrder([
        'fullName' => 'Test Buyer',
        'phone' => '0771234567',
        'address' => 'Test address',
        'city' => '',
        'postal' => '',
        'destination_district' => $route['destination_name'],
    ], $cartItems);
    $orderId = (int)$order['db_id'];
    $fixtureOrders[] = $orderId;

    check($order['status'] === 'Pending Payment', 'A new order stays Pending Payment');
    check(
        (float)$farmerProducts->find($productId, $fixtureUsers['farmer'])['available_quantity'] === 18.0,
        'Checkout decrements stock correctly'
    );
    check(!$cart->getItems(), 'Checkout clears the cart');
    check(
        !db_scalar('SELECT COUNT(*) FROM delivery_assignment_offers WHERE order_id = ?', 'i', [$orderId], 0),
        'Checkout does not reserve a Courier Partner'
    );
    check(
        (int)db_scalar('SELECT COUNT(*) FROM order_status_history WHERE order_id = ?', 'i', [$orderId], 0) >= 1,
        'Order creation is recorded in the shared status history'
    );
    check(
        db_scalar('SELECT provider FROM payments WHERE order_id = ?', 'i', [$orderId], '') === 'LOCAL_DEMO',
        'No PayHere transaction is recorded while approval is pending'
    );

    /*
     * Test fixture only: step past the payment boundary that the live app
     * deliberately does not cross until PayHere is integrated. This is never
     * exposed as an application action.
     */
    db_execute("UPDATE orders SET order_status='PAID', paid_at=NOW() WHERE order_id=?", 'i', [$orderId]);
    db_execute(
        "UPDATE payments SET payment_status='SUCCESS', provider='LOCAL_TEST', paid_at=NOW() WHERE order_id=?",
        'i',
        [$orderId]
    );

    /* --------------------- Farmer order state machine -------------------- */

    check($farmerOrders->advance($orderId, $fixtureUsers['farmer']), 'Farmer accepts the paid order');
    check($farmerOrders->advance($orderId, $fixtureUsers['farmer']), 'Farmer marks the order Preparing');
    check($farmerOrders->advance($orderId, $fixtureUsers['farmer']), 'Farmer marks the order Ready for Delivery');

    $offer = db_fetch_one(
        "SELECT offer_id FROM delivery_assignment_offers
         WHERE order_id = ? AND offer_status = 'PENDING'",
        'i',
        [$orderId]
    );
    check((bool)$offer, 'An eligible Courier Partner is offered the order automatically');
    check(attemptAutomaticCourierAssignment($orderId), 'Repeated assignment preserves the live offer');
    check(
        (int)db_scalar('SELECT COUNT(*) FROM delivery_assignment_offers WHERE order_id = ?', 'i', [$orderId], 0) === 1,
        'No duplicate offer is created'
    );

    /* ------------------------ Courier delivery flow ---------------------- */

    [$accepted] = $courier->respondOffer($cid, (int)$offer['offer_id'], 'accept');
    check($accepted, 'Courier Partner accepts the assignment');

    $deliveryId = (int)db_scalar('SELECT delivery_id FROM deliveries WHERE order_id = ?', 'i', [$orderId], 0);
    check($deliveryId > 0, 'A delivery record exists for the order');

    foreach (['PICKED_UP', 'IN_TRANSIT', 'OUT_FOR_DELIVERY', 'DELIVERED'] as $status) {
        [$ok] = $courier->updateDeliveryStatus($cid, $deliveryId, $status);
        check($ok, "Delivery status advances to $status");
    }

    check(
        db_scalar("SELECT order_status FROM orders WHERE order_id = ?", 'i', [$orderId], '') === 'DELIVERED',
        'The order mirrors the delivery status'
    );
    check(
        (bool)db_scalar('SELECT buyer_confirmation_deadline FROM deliveries WHERE delivery_id = ?', 'i', [$deliveryId], ''),
        'A Buyer confirmation deadline is set on delivery'
    );

    /* --------------------- Buyer receipt confirmation -------------------- */

    check(!completeDeliveredOrder($orderId, 999999), 'A different Buyer cannot confirm receipt');
    check(completeDeliveredOrder($orderId, $fixtureUsers['buyer']), 'Buyer confirms receipt');
    check(!completeDeliveredOrder($orderId, $fixtureUsers['buyer']), 'Completion is idempotent');
    check(
        db_scalar(
            "SELECT earning_status FROM earnings WHERE order_id = ? AND beneficiary_type = 'COURIER_PARTNER'",
            'i',
            [$orderId],
            ''
        ) === 'PENDING_PAYOUT',
        'The Courier delivery fee is released to Pending Payout'
    );

    /* --------------------- Reviews and common complaints ------------------ */

    check(
        $feedback->submitReview([
            'order_id' => (string)$orderId,
            'rating' => 5,
            'review_text' => 'Test review',
        ])['success'],
        'A completed order can be reviewed'
    );
    check(
        $feedback->submitComplaint([
            'order_id' => (string)$orderId,
            'category' => 'Other',
            'details' => 'Test issue',
        ])['success'],
        'A Buyer can submit a complaint'
    );

    /* -------------- Confirmation window and delivery attempts ------------- */

    foreach (['timeout', 'attempts'] as $scenario) {
        $cart->add($productId, 1);
        $fixtureOrder = $checkout->placeOrder([
            'fullName' => 'Test Buyer',
            'phone' => '0771234567',
            'address' => 'Test address',
            'city' => '',
            'postal' => '',
            'destination_district' => $route['destination_name'],
        ], $cart->getItems());
        $id = (int)$fixtureOrder['db_id'];
        $fixtureOrders[] = $id;

        db_execute("UPDATE orders SET order_status='READY_FOR_DELIVERY' WHERE order_id=?", 'i', [$id]);
        attemptAutomaticCourierAssignment($id);

        $offerId = (int)db_scalar(
            "SELECT offer_id FROM delivery_assignment_offers
             WHERE order_id = ? AND offer_status = 'PENDING'",
            'i',
            [$id],
            0
        );
        [$assigned] = $courier->respondOffer($cid, $offerId, 'accept');
        check($assigned, "$scenario fixture is assigned to the Courier Partner");

        $deliveryIdForOrder = (int)db_scalar(
            'SELECT delivery_id FROM deliveries WHERE order_id = ?',
            'i',
            [$id],
            0
        );
        foreach (['PICKED_UP', 'IN_TRANSIT', 'OUT_FOR_DELIVERY'] as $status) {
            $courier->updateDeliveryStatus($cid, $deliveryIdForOrder, $status);
        }

        if ($scenario === 'timeout') {
            $courier->updateDeliveryStatus($cid, $deliveryIdForOrder, 'DELIVERED');
            check(
                !completeDeliveredOrder($id, null, true),
                'No automatic completion before the confirmation window ends'
            );

            db_execute(
                'UPDATE orders SET delivered_at = DATE_SUB(NOW(), INTERVAL 49 HOUR) WHERE order_id = ?',
                'i',
                [$id]
            );
            $feedback->submitComplaint([
                'order_id' => (string)$id,
                'category' => 'Other',
                'details' => 'Open complaint',
            ]);
            check(
                completeDeliveredOrder($id, null, true),
                'Automatic completion after the window is not blocked by an open complaint'
            );
        } else {
            [$first] = $courier->buyerUnavailable($cid, $deliveryIdForOrder, 'First attempt');
            check($first, 'A first unsuccessful delivery attempt is recorded');

            [$retry] = $courier->updateDeliveryStatus($cid, $deliveryIdForOrder, 'OUT_FOR_DELIVERY');
            check($retry, 'One further delivery attempt is permitted');

            [$second] = $courier->buyerUnavailable($cid, $deliveryIdForOrder, 'Second attempt');
            check($second, 'A second unsuccessful attempt is recorded');
            check(
                db_scalar('SELECT order_status FROM orders WHERE order_id = ?', 'i', [$id], '') === 'UNDELIVERABLE',
                'A second unsuccessful attempt sets the order Undeliverable'
            );
            [$third] = $courier->buyerUnavailable($cid, $deliveryIdForOrder, 'Third attempt');
            check(!$third, 'A third delivery attempt is rejected');
        }
    }

    echo count($passed) . " integration assertions passed.\n";
} catch (Throwable $e) {
    fwrite(STDERR, 'FAIL after ' . count($passed) . " checks: " . $e->getMessage()
        . ' at ' . $e->getFile() . ':' . $e->getLine() . "\n");
    $failure = true;
} finally {
    // Remove only the fixtures created by this run.
    foreach ($fixtureOrders as $id) {
        db_execute(
            'DELETE da FROM delivery_attempts da
             JOIN deliveries d ON d.delivery_id = da.delivery_id
             WHERE d.order_id = ?',
            'i',
            [$id]
        );
        foreach (['reviews', 'complaints', 'earnings', 'delivery_assignment_offers',
                  'deliveries', 'order_status_history', 'payments', 'order_items'] as $table) {
            db_execute("DELETE FROM $table WHERE order_id = ?", 'i', [$id]);
        }
        db_execute('DELETE FROM notifications WHERE related_order_id = ?', 'i', [$id]);
        db_execute('DELETE FROM orders WHERE order_id = ?', 'i', [$id]);
    }

    foreach ($fixtureUsers as $id) {
        db_execute('DELETE ci FROM cart_items ci JOIN carts c ON c.cart_id = ci.cart_id WHERE c.buyer_id = ?', 'i', [$id]);
        db_execute('DELETE FROM carts WHERE buyer_id = ?', 'i', [$id]);
    }

    foreach ($fixtureProducts as $id) {
        db_execute('DELETE FROM product_images WHERE product_id = ?', 'i', [$id]);
        db_execute('DELETE FROM products WHERE product_id = ?', 'i', [$id]);
    }

    if ($fixtureCategory) {
        check($admin->deleteCategory($fixtureCategory), 'Admin category Delete');
    }

    foreach ($fixtureUsers as $id) {
        foreach ([
            'notifications' => 'user_id',
            'password_reset_tokens' => 'user_id',
            'verification_documents' => 'user_id',
            'courier_coverage_routes' => 'courier_partner_id',
            'buyer_profiles' => 'buyer_id',
            'farmer_profiles' => 'farmer_id',
            'courier_partner_profiles' => 'courier_partner_id',
        ] as $table => $key) {
            db_execute("DELETE FROM $table WHERE $key = ?", 'i', [$id]);
        }
        db_execute('DELETE FROM users WHERE user_id = ?', 'i', [$id]);
    }

    file_put_contents(
        __DIR__ . '/integration-results.json',
        json_encode(['passed' => $passed, 'failure' => $failure], JSON_PRETTY_PRINT | JSON_PRETTY_PRINT)
    );
}

exit($failure ? 1 : 0);
