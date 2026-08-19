<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../Model/Buyer/Orders.php';
require_once __DIR__ . '/../../Model/Buyer/OrderTracking.php';

$ordersModel = new Orders();
$trackingModel = new OrderTracking();
$baseUrl = BASE_URL;
$orderId = trim((string)($_GET['id'] ?? $_GET['order_id'] ?? ''));

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'received') {
    $orderId = trim((string)($_POST['order_id'] ?? ''));
    $order = $ordersModel->updateStatus($orderId, 'Delivered');
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success'=>(bool)$order,'order'=>$order]);
    exit;
}

$order = $orderId !== '' ? $ordersModel->getOrderById($orderId) : null;
$pageTitle = $order ? 'Order Tracking' : 'Order Not Found';
$tracking = $order ? $trackingModel->getTrackingByOrder($order) : null;
if (!$order) http_response_code($orderId === '' ? 400 : 404);
require __DIR__ . '/../../View/Buyer/order-tracking.php';
