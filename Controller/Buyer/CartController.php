<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../Model/Buyer/Cart.php';

requireBuyerAuth();
$model = new Cart();

/*
 * Cart item CRUD.
 *   Create  - Add to Cart from a product card or the product details page
 *   Read    - the cart table below
 *   Update  - the inline quantity form
 *   Delete  - the Remove button (single item) and Clear Cart (all items)
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
    $action = (string)($_POST['action'] ?? '');
    $productId = (int)($_POST['id'] ?? 0);
    $quantity = (float)($_POST['quantity'] ?? 0);

    $message = match ($action) {
        'update' => $model->updateQuantity($productId, $quantity)
            ? 'Cart updated.'
            : 'Unable to update that item.',
        'remove' => $model->remove($productId)
            ? 'Item removed from your cart.'
            : 'Unable to remove that item.',
        'clear' => $model->clear()
            ? 'Your cart has been cleared.'
            : 'Unable to clear your cart.',
        default => 'Unknown cart action.',
    };

    $_SESSION['_buyer_flash'] = ['success' => true, 'message' => $message];
    redirect('Controller/Buyer/CartController.php');
}

$cartItems = $model->getItems();
$subtotal = $model->calculateSubtotal($cartItems);
$totalQuantity = $model->calculateQuantity($cartItems);

/*
 * One order can only contain products from a single Farmer, so the cart is
 * grouped by Farmer here and checkout is offered only for a single-Farmer cart.
 * The same rule is enforced again in Checkout::quote() when the order is placed.
 */
$cartFarmers = [];
foreach ($cartItems as $item) {
    $cartFarmers[(int)$item['farmer_id']] = (string)$item['farmer'];
}
$singleFarmerCart = count($cartFarmers) <= 1;

require __DIR__ . '/../../View/Buyer/cart.php';
