<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../Model/Buyer/Profile.php';

$model = new Profile();
$buyer = $model->getBuyer();
$orderStats = $model->getStats();
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $buyer = $model->save(
            $_POST,
            $_FILES['profile_image'] ?? null
        );
        $success = 'Profile updated successfully.';
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

require __DIR__ . '/../../View/Buyer/profile.php';
