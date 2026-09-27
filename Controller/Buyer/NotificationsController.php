<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../Model/Buyer/Notifications.php';

requireBuyerAuth();
$model = new Notifications();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
    $action = (string)($_POST['action'] ?? '');
    if ($action === 'read_all') {
        $model->markAllRead();
    } elseif ($action === 'read') {
        $model->markRead((int)($_POST['id'] ?? 0));
    }
    $query = [];
    if (!empty($_POST['filter'])) $query['filter'] = (string)$_POST['filter'];
    if (!empty($_POST['page'])) $query['page'] = (string)(int)$_POST['page'];
    redirect('Controller/Buyer/NotificationsController.php' . ($query ? '?' . http_build_query($query) : ''));
}

$notifications = $model->getAll(
    (string)($_GET['filter'] ?? 'all'),
    (int)($_GET['page'] ?? 1)
);

require __DIR__ . '/../../View/Buyer/notifications.php';
