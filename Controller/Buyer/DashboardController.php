<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../Model/Buyer/Dashboard.php';

$dashboardModel = new Dashboard();
$buyerData = $dashboardModel->getBuyerData();
$buyerName = $buyerData['buyerName'];
$notificationCount = $buyerData['notificationCount'];
$cartCount = $buyerData['cartCount'];
require __DIR__ . '/../../View/Buyer/dashboard.php';
