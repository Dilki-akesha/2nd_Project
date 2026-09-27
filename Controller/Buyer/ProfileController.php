<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../Model/Buyer/Profile.php';

requireBuyerAuth();
$model = new Profile();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
    try {
        $buyer = $model->save($_POST);
        $_SESSION['_buyer_flash'] = ['success' => true, 'message' => 'Profile updated successfully.'];
    } catch (Throwable $e) {
        $_SESSION['_buyer_flash'] = ['success' => false, 'message' => $e->getMessage()];
    }
    redirect('Controller/Buyer/ProfileController.php');
}

$buyer = $model->getBuyer();
$orderStats = $model->getStats();
$districts = $model->districts();

require __DIR__ . '/../../View/Buyer/profile.php';
