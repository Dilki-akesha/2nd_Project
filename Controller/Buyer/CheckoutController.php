<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../Model/Buyer/Checkout.php';

requireBuyerAuth();
$checkoutModel = new Checkout();
$buyerId = currentBuyerId();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
    $destination = (string)($_POST['destination_district'] ?? '');

    try {
        $items = $checkoutModel->getCartItems();
        $summary = $checkoutModel->getSummary($items);
        if ($summary['quantity'] <= 0) {
            throw new RuntimeException('Your cart is empty.');
        }

        $validation = $checkoutModel->validate($_POST);
        if (!$validation['valid']) {
            throw new RuntimeException($validation['message']);
        }

        // Confirm district route availability before creating the order.
        $quote = $checkoutModel->fees($checkoutModel->quote($items, $destination));
        if (!$quote['routeAvailable']) {
            throw new RuntimeException(
                'No approved Courier Partner currently covers the route from ' . $quote['originDistrict']
                . ' to ' . $quote['destinationDistrict'] . '. Please choose another destination district.'
            );
        }

        $order = $checkoutModel->placeOrder($_POST, $items);
        $_SESSION['_buyer_flash'] = [
            'success' => true,
            'message' => 'Order ' . $order['id'] . ' created. It is currently Pending Payment because PayHere '
                . 'Sandbox integration is not yet approved, so no payment has been collected.',
        ];
        redirect('Controller/Buyer/OrderTrackingController.php?id=' . urlencode((string)$order['db_id']));
    } catch (Throwable $e) {
        $_SESSION['_buyer_flash'] = ['success' => false, 'message' => $e->getMessage()];
        redirect('Controller/Buyer/CheckoutController.php' . ($destination !== '' ? '?destination_district=' . urlencode($destination) : ''));
    }
}

$cartItems = $checkoutModel->getCartItems();
$summary = $checkoutModel->getSummary($cartItems);
if (!$cartItems || $summary['quantity'] <= 0) {
    redirect('Controller/Buyer/CartController.php');
}

$profile = $checkoutModel->buyerProfile($buyerId);
$districts = $checkoutModel->districts();
$destination = (string)($_GET['destination_district'] ?? $profile['district_name'] ?? '');

$quote = null;
$quoteError = '';
if ($destination !== '') {
    try {
        $candidate = $checkoutModel->quote($cartItems, $destination);
        if ($candidate['routeAvailable'] && $candidate['distanceKm'] !== null) {
            $quote = $checkoutModel->fees($candidate);
        } else {
            $quote = $candidate;
            $quoteError = $candidate['routeAvailable']
                ? 'No district reference distance is configured for this route, so the delivery fee cannot be calculated.'
                : 'No approved Courier Partner currently covers the route from ' . $candidate['originDistrict']
                    . ' to ' . $candidate['destinationDistrict'] . '.';
        }
    } catch (Throwable $e) {
        $quoteError = $e->getMessage();
    }
}

$subtotal = $summary['subtotal'];

require __DIR__ . '/../../View/Buyer/checkout.php';
