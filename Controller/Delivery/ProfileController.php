<?php
session_start();
$userId = $_SESSION['user_id'] ?? 5;
session_write_close();

header('Content-Type: application/json');

require_once __DIR__ . '/../../Model/Delivery/Profile.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$profile = new Profile();
$response = ['success' => false, 'message' => 'Invalid action'];

switch ($action) {
    case 'get':
        $response = ['success' => true, 'profile' => $profile->get($userId)];
        break;

    case 'update':
        $data = [
            'organisation_name' => trim($_POST['organisation_name'] ?? ''),
            'contact_person' => trim($_POST['contact_person'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'address' => trim($_POST['address'] ?? ''),
            'city' => trim($_POST['city'] ?? ''),
            'district_id' => !empty($_POST['district_id']) ? intval($_POST['district_id']) : null
        ];

        if (!$data['organisation_name'] || !$data['contact_person'] || !$data['email']) {
            $response = ['success' => false, 'message' => 'Required fields missing.'];
            break;
        }

        $response = $profile->update($userId, $data);
        break;
}

echo json_encode($response);
?>