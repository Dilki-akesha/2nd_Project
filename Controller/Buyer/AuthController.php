<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../Model/Buyer/Buyer.php';

$model = new Buyer();
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = post_string('email');
    $password = (string)($_POST['password'] ?? '');

    $errors = $model->validateLogin($email, $password);

    if ($errors) {
        $message = $errors[0];
        $messageType = 'error';
    } else {
        $buyer = $model->findByEmail($email);

        session_regenerate_id(true);
        $_SESSION['logged_in'] = true;
        $_SESSION['buyer_id'] = (int)$buyer['id'];
        $_SESSION['buyer_name'] = $buyer['name'];
        $_SESSION['buyer_email'] = $buyer['email'];

        redirect('Controller/Buyer/DashboardController.php');
    }
}

require __DIR__ . '/../../View/Buyer/login.php';
