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

    if (!in_array($action, ['update', 'remove', 'clear'], true)) {
        $_SESSION['_buyer_flash'] = ['success' => false, 'message' => 'Invalid cart action.'];
        redirect('Controller/Buyer/CartController.php');
    }
    if (in_array($action, ['update', 'remove'], true) && $productId <= 0) {
        $_SESSION['_buyer_flash'] = ['success' => false, 'message' => 'Invalid product selected.'];
        redirect('Controller/Buyer/CartController.php');
    }
    if ($action === 'update' && (!is_finite($quantity) || $quantity <= 0 || $quantity > 100000)) {
        $_SESSION['_buyer_flash'] = ['success' => false, 'message' => 'Please enter a valid quantity greater than zero.'];
        redirect('Controller/Buyer/CartController.php');
    }

    $ok = match ($action) {
        'update' => $model->updateQuantity($productId, $quantity),
        'remove' => $model->remove($productId),
        'clear' => $model->clear(),
        default => false,
    };
    $message = $ok ? match ($action) {
        'update' => 'Cart updated.',
        'remove' => 'Item removed from your cart.',
        'clear' => 'Your cart has been cleared.',
        default => 'Cart updated.',
    } : 'Unable to complete that cart action.';

    $_SESSION['_buyer_flash'] = ['success' => $ok, 'message' => $message];
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
