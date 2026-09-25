<?php
session_start();
require_once __DIR__ . '/../../Model/Delivery/Notification.php';

header('Content-Type: application/json');

$userId = $_SESSION['user_id'] ?? 5;
session_write_close();  

$action = $_GET['action'] ?? $_POST['action'] ?? '';

$notification = new Notification();
$response = ['success' => false, 'message' => 'Invalid action'];

switch ($action) {
    case 'list':
        $response = [
            'success' => true,
            'notifications' => $notification->getByUser($userId),
            'unread_count' => $notification->getUnreadCount($userId)
        ];
        break;

    case 'markRead':
        $id = intval($_POST['notification_id'] ?? 0);
        $response = $id ? $notification->markRead($id, $userId) : ['success' => false];
        break;

    case 'markAllRead':
        $response = $notification->markAllRead($userId);
        break;

    case 'unreadCount':
        $response = [
            'success' => true,
            'unread_count' => $notification->getUnreadCount($userId)
        ];
        break;
}

echo json_encode($response);
?>