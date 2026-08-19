<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../Model/Buyer/Orders.php';

$ordersModel = new Orders();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    $orderId = trim((string)($_POST['order_id'] ?? ''));
    $action = trim((string)($_POST['action'] ?? ''));

    try {
        $result = null;

        switch ($action) {
            case 'status':
                $result = $ordersModel->updateStatus(
                    $orderId,
                    trim((string)($_POST['status'] ?? ''))
                );
                break;

            case 'cancel':
                $result = $ordersModel->cancel($orderId);
                break;

            case 'delete':
                $result = $ordersModel->delete($orderId);
                break;

            default:
                http_response_code(422);
                echo json_encode([
                    'success' => false,
                    'message' => 'Unknown order action.',
                ]);
                exit;
        }

        echo json_encode([
            'success' => (bool)$result,
            'order' => is_array($result) ? $result : null,
            'message' => $result ? 'Order updated successfully.' : 'Order could not be updated.',
        ]);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage(),
        ]);
    }

    exit;
}

$orders = $ordersModel->getAllOrders();

require __DIR__ . '/../../View/Buyer/orders.php';
