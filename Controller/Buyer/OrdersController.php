<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../Model/Buyer/Orders.php';

requireBuyerAuth();
$ordersModel = new Orders();

/*
 * A Buyer may cancel their own order while it is still Pending Payment or Paid.
 * Orders are never hard-deleted - Harvestly keeps order history, reviews and
 * financial records.
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
    $orderId = trim((string)($_POST['order_id'] ?? ''));
    if ($orderId === '' || strlen($orderId) > 50 || !preg_match('/^[A-Za-z0-9_-]+$/', $orderId)) {
        $_SESSION['_buyer_flash'] = ['success' => false, 'message' => 'Invalid order reference.'];
        redirect('Controller/Buyer/OrdersController.php');
    }
    $order = $ordersModel->cancel($orderId);
    $_SESSION['_buyer_flash'] = [
        'success' => $order !== null,
        'message' => $order !== null
            ? 'Your order has been cancelled.'
            : 'This order can no longer be cancelled. Orders can only be cancelled before the Farmer accepts them.',
    ];
    redirect('Controller/Buyer/OrdersController.php');
}

$orders = $ordersModel->getAllOrders();

require __DIR__ . '/../../View/Buyer/orders.php';
