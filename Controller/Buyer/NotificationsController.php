<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../Model/Buyer/Notifications.php';
require_once __DIR__ . '/../../Model/Buyer/Orders.php';

$notificationModel = new Notifications();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    $action = trim((string)($_POST['action'] ?? ''));
    $id = (int)($_POST['id'] ?? 0);

    try {
        switch ($action) {
            case 'create':
                $newId = $notificationModel->create($_POST);
                echo json_encode([
                    'success' => true,
                    'message' => 'Notification created successfully.',
                    'notification' => $notificationModel->find($newId),
                ]);
                break;

            case 'read':
                echo json_encode([
                    'success' => $notificationModel->markRead($id),
                ]);
                break;

            case 'read_all':
                echo json_encode([
                    'success' => $notificationModel->markAllRead(),
                ]);
                break;

            case 'update':
                echo json_encode([
                    'success' => $notificationModel->update($id, $_POST),
                ]);
                break;

            case 'delete':
                echo json_encode([
                    'success' => $notificationModel->delete($id),
                ]);
                break;

            default:
                http_response_code(422);
                echo json_encode([
                    'success' => false,
                    'message' => 'Unknown notification action.',
                ]);
        }
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Notification operation failed.',
        ]);
    }

    exit;
}

$notifications = $notificationModel->getAll();
$totalNotifications = count($notifications);
$unreadNotifications = count(
    array_filter($notifications, fn(array $notification) => $notification['unread'])
);

$ordersModel = new Orders();
$allOrders = $ordersModel->getAllOrders();
$totalOrders = count($allOrders);
$latestOrder = $allOrders[0] ?? null;
$totalDeliveries = count(
    array_filter(
        $allOrders,
        fn(array $order) => in_array(
            $order['status'],
            ['In Transit', 'Out for Delivery', 'Delivered'],
            true
        )
    )
);

require __DIR__ . '/../../View/Buyer/notifications.php';
