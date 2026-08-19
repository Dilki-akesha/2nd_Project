<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../Model/Buyer/Checkout.php';

$checkoutModel = new Checkout();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    try {
        $items = $checkoutModel->getCartItems();
        $summary = $checkoutModel->getSummary($items);

        if ($summary['quantity'] <= 0) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Your cart is empty.',
            ]);
            exit;
        }

        $validation = $checkoutModel->validate($_POST);

        if (!$validation['valid']) {
            http_response_code(422);
            echo json_encode([
                'success' => false,
                'message' => $validation['message'],
            ]);
            exit;
        }

        $order = $checkoutModel->placeOrder($_POST, $items);

        echo json_encode([
            'success' => true,
            'message' => 'Order placed successfully.',
            'order' => [
                'id' => $order['id'],
                'total' => $order['total'],
                'status' => $order['status'],
            ],
            'redirect' => buyerRoute('OrdersController.php'),
        ]);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage() ?: 'Unable to place the order.',
        ]);
    }

    exit;
}

$cartItems = $checkoutModel->getCartItems();
$summary = $checkoutModel->getSummary($cartItems);

if ($summary['quantity'] <= 0) {
    redirect('Controller/Buyer/CartController.php');
}

$subtotal = $summary['subtotal'];
$deliveryFee = $summary['deliveryFee'];
$total = $summary['total'];
$totalQuantity = $summary['quantity'];

require __DIR__ . '/../../View/Buyer/checkout.php';
