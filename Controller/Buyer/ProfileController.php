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
        $action = trim((string)($_POST['action'] ?? 'save_profile'));

        if ($action === 'delete_account') {
            $deleted = $model->delete(currentBuyerId());

            if (!$deleted) {
                throw new RuntimeException('Unable to delete the Buyer account.');
            }

            $_SESSION = [];

            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();
                setcookie(
                    session_name(),
                    '',
                    time() - 42000,
                    $params['path'],
                    $params['domain'],
                    $params['secure'],
                    $params['httponly']
                );
            }

            session_destroy();

            header('Location: ' . BASE_URL . '/Controller/Buyer/AuthController.php');
            exit;
        }

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
