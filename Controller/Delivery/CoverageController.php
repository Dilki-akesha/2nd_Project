<?php
session_start();
require_once __DIR__ . '/../../Model/Delivery/Coverage.php';

header('Content-Type: application/json');

// TEMPORARY: Demo courier ID (later from session)
$courierId = $_SESSION['user_id'] ?? 5;
session_write_close();

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$coverage = new Coverage();
$response = ['success' => false, 'message' => 'Invalid action'];

switch ($action) {
    case 'create':
        $originId = intval($_POST['origin_id'] ?? 0);
        $destId = intval($_POST['destination_id'] ?? 0);
        $isActive = ($_POST['is_active'] ?? '1') === '1';

        if (!$originId || !$destId) {
            $response = ['success' => false, 'message' => 'Please select both districts.'];
            break;
        }
        if ($originId === $destId) {
            $response = ['success' => false, 'message' => 'Origin and Destination cannot be the same.'];
            break;
        }
        $response = $coverage->create($courierId, $originId, $destId, $isActive);
        break;

    case 'list':
        $response = ['success' => true, 'routes' => $coverage->getAllByCourier($courierId)];
        break;

    case 'update':
        $routeId = intval($_POST['route_id'] ?? 0);
        $originId = intval($_POST['origin_id'] ?? 0);
        $destId = intval($_POST['destination_id'] ?? 0);
        $isActive = ($_POST['is_active'] ?? '1') === '1';
        if (!$routeId || !$originId || !$destId) {
            $response = ['success' => false, 'message' => 'Missing fields.'];
            break;
        }
        $response = $coverage->update($routeId, $courierId, $originId, $destId, $isActive);
        break;

    case 'toggle':
        $routeId = intval($_POST['route_id'] ?? 0);
        $response = $routeId ? $coverage->toggle($routeId, $courierId) : ['success' => false];
        break;

    case 'delete':
        $routeId = intval($_POST['route_id'] ?? 0);
        $response = $routeId ? $coverage->delete($routeId, $courierId) : ['success' => false];
        break;
}

echo json_encode($response);
?>