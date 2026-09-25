<?php
session_start();
$userId = $_SESSION['user_id'] ?? 5;
session_write_close();

header('Content-Type: application/json');

require_once __DIR__ . '/../../Model/Delivery/DeliveryAttempt.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$attempt = new DeliveryAttempt();
$response = ['success' => false, 'message' => 'Invalid action'];

switch ($action) {
    case 'getCount':
        $deliveryId = intval($_GET['delivery_id'] ?? 0);
        $response = [
            'success' => true,
            'attempt_count' => $attempt->getCount($deliveryId)
        ];
        break;

    case 'record':
        $deliveryId = intval($_POST['delivery_id'] ?? 0);
        $notes = trim($_POST['notes'] ?? '');

        if (!$deliveryId) {
            $response = ['success' => false, 'message' => 'Missing delivery ID.'];
            break;
        }

        $response = $attempt->recordUnavailable($deliveryId, $notes ?: null);
        break;
}

echo json_encode($response);
?>