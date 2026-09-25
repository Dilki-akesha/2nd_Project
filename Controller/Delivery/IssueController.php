<?php
session_start();
$userId = $_SESSION['user_id'] ?? 5;
session_write_close();

header('Content-Type: application/json');

require_once __DIR__ . '/../../Model/Delivery/Issue.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$issue = new Issue();
$response = ['success' => false, 'message' => 'Invalid action'];

switch ($action) {
    case 'list':
        $response = ['success' => true, 'issues' => $issue->getAllByUser($userId)];
        break;

    case 'create':
        $data = [
            'order_id' => $_POST['order_id'] ?? null,
            'category' => trim($_POST['category'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'evidence_path' => null
        ];

        // File upload
        if (isset($_FILES['evidence']) && $_FILES['evidence']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../uploads/issues/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $ext = pathinfo($_FILES['evidence']['name'], PATHINFO_EXTENSION);
            $filename = uniqid('issue_') . '.' . $ext;
            move_uploaded_file($_FILES['evidence']['tmp_name'], $uploadDir . $filename);
            $data['evidence_path'] = 'uploads/issues/' . $filename;
        }

        if (!$data['category']) {
            $response = ['success' => false, 'message' => 'Please select a category.'];
            break;
        }
        if (!$data['description']) {
            $response = ['success' => false, 'message' => 'Please provide a description.'];
            break;
        }

        $response = $issue->create($userId, $data);
        break;
}

echo json_encode($response);
?>