<?php
session_start();

$action = $_GET['action'] ?? '';

if ($action === 'logout') {
    $_SESSION = [];
    session_destroy();
    header('Location: ../View/Delivery/index.php?loggedout=1');
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid action']);
?>