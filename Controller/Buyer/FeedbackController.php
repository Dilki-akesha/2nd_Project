<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../Model/Buyer/Feedback.php';

$model = new Feedback();
$orderId = trim((string)($_GET['order_id'] ?? $_POST['order_id'] ?? ''));
if ($orderId === '') {
    $latestOrderStmt = db()->prepare('SELECT order_number FROM orders WHERE user_id = ? ORDER BY created_at DESC, id DESC LIMIT 1');
    $latestOrderStmt->execute([currentBuyerId()]);
    $orderId = (string)($latestOrderStmt->fetchColumn() ?: '');
}
$farmerName = "Sunil's Organic Farm";
$reviewMessage = '';
$complaintMessage = '';
$reviews = $model->getReviews();
$complaints = $model->getComplaints();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = trim((string)($_POST['action'] ?? ''));

    if ($action === 'delete_review') {
        $success = $model->deleteReview((int)($_POST['id'] ?? 0));
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => $success,
            'message' => $success ? 'Review deleted successfully.' : 'Review could not be deleted.',
        ]);
        exit;
    }

    if ($action === 'update_review') {
        $success = $model->updateReview((int)($_POST['id'] ?? 0), $_POST);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => $success,
            'message' => $success ? 'Review updated successfully.' : 'Review could not be updated.',
        ]);
        exit;
    }

    if ($action === 'delete_complaint') {
        $success = $model->deleteComplaint((int)($_POST['id'] ?? 0));
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => $success,
            'message' => $success ? 'Complaint deleted successfully.' : 'Complaint cannot be deleted now.',
        ]);
        exit;
    }

    if (isset($_POST['submit_review'])) {
        $result = $model->submitReview($_POST);
        $reviewMessage = $result['message'];
    }

    if (isset($_POST['submit_complaint'])) {
        $result = $model->submitComplaint($_POST);
        $complaintMessage = $result['message'];
    }

    $reviews = $model->getReviews();
    $complaints = $model->getComplaints();
}

require __DIR__ . '/../../View/Buyer/feedback.php';
