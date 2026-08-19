<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../Model/Buyer/Registration.php';

$model = new Registration();
$message = '';
$messageType = '';

$formData = array_merge([
    'fullName' => '',
    'email' => '',
    'phone' => '',
    'district' => '',
    'city' => '',
    'password' => '',
    'confirmPassword' => '',
    'terms' => false,
], $_POST);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $model->validate($_POST);
    $message = $result['message'];
    $messageType = $result['type'];

    if ($messageType === 'success') {
        $buyerId = $model->create($_POST);

        session_regenerate_id(true);
        $_SESSION['logged_in'] = true;
        $_SESSION['buyer_id'] = $buyerId;
        $_SESSION['buyer_name'] = $_POST['fullName'];
        $_SESSION['buyer_email'] = $_POST['email'];

        redirect('Controller/Buyer/DashboardController.php');
    }
}

require __DIR__ . '/../../View/Buyer/registration.php';
