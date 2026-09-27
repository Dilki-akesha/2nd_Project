<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../Model/Buyer/Orders.php';

requireBuyerAuth();

$model = new Orders();
$orderId = (string)($_GET['id'] ?? $_GET['order_id'] ?? '');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    verifyCsrfToken();
    $postedOrderId = trim((string)($_POST['order_id'] ?? ''));
    $ok = $model->updateStatus($postedOrderId, 'COMPLETED');
    $_SESSION['_buyer_flash'] = [
        'success' => $ok,
        'message' => $ok
            ? 'Thank you. Your order is now Completed.'
            : 'This order cannot be confirmed. Only a Delivered order can be completed by the Buyer.',
    ];
    redirect('Controller/Buyer/OrderTrackingController.php?id=' . urlencode($postedOrderId));
}

$order = $model->getOrderById($orderId);
if (!$order) {
    http_response_code(404);
}
$history = $order ? $model->getStatusHistory((int)$order['db_id']) : [];
$delivery = $order ? $model->getDeliveryForOrder((int)$order['db_id']) : null;

require __DIR__ . '/../../View/Buyer/order-tracking.php';
