<?php
session_start();
$userId = $_SESSION['user_id'] ?? 5;
session_write_close();

header('Content-Type: application/json');

require_once __DIR__ . '/../../Model/Delivery/Verification.php';

$verification = new Verification();

echo json_encode([
    'success' => true,
    'org' => $verification->getOrgInfo($userId),
    'documents' => $verification->getDocuments($userId)
]);
?>