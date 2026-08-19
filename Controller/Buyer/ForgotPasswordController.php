<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../Model/Buyer/ForgotPassword.php';
$model = new ForgotPassword();
$message = '';
$messageType = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $model->validate(post_string('email'));
    $message = $result['message'];
    $messageType = $result['success'] ? 'success' : 'error';
}
require __DIR__ . '/../../View/Buyer/forgot-password.php';
