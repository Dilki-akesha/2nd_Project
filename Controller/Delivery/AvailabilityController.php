<?php
session_start();
require_once __DIR__ . '/../../Model/Delivery/Availability.php';

header('Content-Type: application/json');

$courierId = $_SESSION['user_id'] ?? 5;
session_write_close();

$action = $_GET['action'] ?? $_POST['action'] ?? '';

$availability = new Availability();
$response = ['success' => false, 'message' => 'Invalid action'];

switch ($action) {
    case 'get':
        $response = [
            'success' => true,
            'availability_status' => $availability->get($courierId)
        ];
        break;

    case 'set':
        $status = strtoupper($_POST['status'] ?? '');
        $response = $availability->set($courierId, $status);
        break;
}

echo json_encode($response);
?>