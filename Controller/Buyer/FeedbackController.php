<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../Model/Buyer/Feedback.php';

requireBuyerAuth();
$model = new Feedback();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
    try {
        $action = (string)($_POST['action'] ?? '');
        $id = (int)($_POST['id'] ?? 0);

        if ($action === 'review') {
            $result = $model->submitReview($_POST);
        } elseif ($action === 'complaint') {
            $result = $model->submitComplaint($_POST);
        } else {
            $ok = match ($action) {
                'update_review' => $model->updateReview($id, $_POST),
                'delete_review' => $model->deleteReview($id),
                'update_complaint' => $model->updateComplaint($id, $_POST),
                'delete_complaint' => $model->deleteComplaint($id),
                default => false,
            };
            $result = [
                'success' => $ok,
                'message' => $ok ? 'Changes saved.' : 'Unable to make that change.',
            ];
        }
        $_SESSION['_buyer_flash'] = $result;
    } catch (Throwable $e) {
        $_SESSION['_buyer_flash'] = ['success' => false, 'message' => $e->getMessage()];
    }
    redirect('Controller/Buyer/FeedbackController.php');
}

$reviews = $model->getReviews();
$complaints = $model->getComplaints();
$reviewableOrders = $model->getReviewableOrders();
$complaintOrders = $model->getComplaintOrders();
$selectedOrder = parseOrderPublicId((string)($_GET['order_id'] ?? ''));

require __DIR__ . '/../../View/Buyer/feedback.php';
