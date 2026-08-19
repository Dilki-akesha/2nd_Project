<?php

$cartItems = $cartItems ?? [];

$subtotal = (float)($subtotal ?? 0);

$deliveryFee = (float)($deliveryFee ?? 0);

$total = (float)($total ?? 0);

$totalQuantity = (int)($totalQuantity ?? 0);

$success = $success ?? false;

$successOrderId = $successOrderId ?? null;

$error = $error ?? "";

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Harvestly - Checkout</title>

<link
    href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap"
    rel="stylesheet"
>

<link
    href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,0"
    rel="stylesheet"
>

<link
    rel="stylesheet"
    href="/Harvestly/css/Buyer/checkout.css"
>

</head>


<body>


<div class="checkout-page">


<!-- =====================================================
     HEADER
====================================================== -->

<header class="checkout-header">

    <a
        href="/Harvestly/Controller/Buyer/CartController.php"
        class="back-link"
    >

        <span class="material-symbols-outlined">
            arrow_back
        </span>

        Back to Cart

    </a>


    <a
        href="/Harvestly/Controller/Buyer/DashboardController.php"
        class="brand"
    >

        <img src="/Harvestly/assets/harvestly-logo.jpeg" alt="Harvestly" style="height:34px;width:auto;display:block;object-fit:contain;">

    </a>


    <div class="header-space"></div>

</header>


<!-- =====================================================
     ERROR
====================================================== -->

<?php if (!empty($error)): ?>

<div class="checkout-error">

    <?= htmlspecialchars($error) ?>

</div>

<?php endif; ?>


<!-- =====================================================
     MAIN
====================================================== -->

<main class="checkout-container">


<div class="checkout-title">

    <h1>
        Checkout
    </h1>

</div>


<form
    id="checkoutForm"
    action="/Harvestly/Controller/Buyer/CheckoutController.php"
    method="POST"
    class="checkout-grid"
>


<!-- =====================================================
     LEFT
====================================================== -->

<section class="checkout-left">


<!-- DELIVERY -->

<div class="checkout-card">

<div class="card-heading">

<div class="heading-icon">

<span class="material-symbols-outlined">
    local_shipping
</span>

</div>

<h2>
    Delivery Address
</h2>

</div>


<div class="form-grid">


<div class="form-group full">

<label>
    Full Name
</label>

<input
    type="text"
    name="fullName"
    placeholder="John Doe"
    required
>

</div>


<div class="form-group full">

<label>
    Address Line 1
</label>

<input
    type="text"
    name="address"
    placeholder="123 Farm Road"
    required
>

</div>


<div class="form-group">

<label>
    City
</label>

<input
    type="text"
    name="city"
    placeholder="Colombo"
    required
>

</div>


<div class="form-group">

<label>
    Postal Code
</label>

<input
    type="text"
    name="postal"
    placeholder="00100"
    required
>

</div>


<div class="form-group full">

<label>
    Phone Number
</label>

<input
    type="tel"
    name="phone"
    placeholder="07X XXX XXXX"
    required
>

</div>


</div>

</div>



<!-- PAYMENT -->

<div class="checkout-card">

<div class="card-heading">

<div class="heading-icon">

<span class="material-symbols-outlined">
    payments
</span>

</div>

<h2>
    Payment Method
</h2>

</div>


<div class="payment-methods">


<label
    class="payment-method selected"
    data-method="card"
>

<input
    type="radio"
    name="payment"
    value="card"
    checked
>

<div class="radio-circle"></div>

<div class="payment-content">

<strong>
    Credit/Debit Card
</strong>

<span>
    Pay securely with Visa or Mastercard.
</span>

</div>

<span class="material-symbols-outlined payment-icon">
    credit_card
</span>

</label>



<label
    class="payment-method"
    data-method="cash"
>

<input
    type="radio"
    name="payment"
    value="cash"
>

<div class="radio-circle"></div>

<div class="payment-content">

<strong>
    Cash on Delivery
</strong>

<span>
    Pay when your order arrives.
</span>

</div>

<span class="material-symbols-outlined payment-icon">
    payments
</span>

</label>



<label
    class="payment-method"
    data-method="bank"
>

<input
    type="radio"
    name="payment"
    value="bank"
>

<div class="radio-circle"></div>

<div class="payment-content">

<strong>
    Bank Transfer
</strong>

<span>
    Direct transfer to our account.
</span>

</div>

<span class="material-symbols-outlined payment-icon">
    account_balance
</span>

</label>


</div>


<!-- CARD DETAILS -->

<div
    id="cardDetails"
    class="card-details"
>

<div class="form-group full">

<label>
    Card Number
</label>

<input
    type="text"
    name="cardNumber"
    id="cardNumber"
    placeholder="0000 0000 0000 0000"
    maxlength="19"
>

</div>


<div class="card-small-row">

<div class="form-group">

<label>
    Expiry Date
</label>

<input
    type="text"
    name="expiry"
    id="expiry"
    placeholder="MM/YY"
    maxlength="5"
>

</div>


<div class="form-group">

<label>
    CVV
</label>

<input
    type="password"
    name="cvv"
    id="cvv"
    placeholder="123"
    maxlength="3"
>

</div>

</div>


<p class="demo-note">
    Demo checkout only — no real payment is processed.
</p>

</div>


<!-- BANK DETAILS -->

<div
    id="bankDetails"
    class="bank-details hidden"
>

<h3>
    Bank Transfer Details
</h3>

<p>
<strong>Bank:</strong>
Bank of Ceylon
</p>

<p>
<strong>Account Name:</strong>
Harvestly Pvt Ltd
</p>

<p>
<strong>Account Number:</strong>
1234567890
</p>

<p>
<strong>Branch:</strong>
Colombo
</p>

</div>


</div>


</section>


<!-- =====================================================
     RIGHT
====================================================== -->

<aside class="checkout-right">


<div class="summary-card">

<h2>
    Order Summary
</h2>


<div class="summary-divider"></div>


<div class="summary-products">


<?php if (empty($cartItems)): ?>

<p class="empty-cart">
    Your cart is empty.
</p>

<?php endif; ?>


<?php foreach ($cartItems as $item): ?>


<?php

$itemQty = (int)(
    $item['quantity']
    ?? $item['qty']
    ?? 1
);

$itemPrice = (float)(
    $item['price']
    ?? 0
);

$itemTotal = $itemQty * $itemPrice;

?>


<div class="summary-product">


<img
    src="<?= htmlspecialchars(
        $item['image']
        ?? 'https://via.placeholder.com/80'
    ) ?>"
    alt="<?= htmlspecialchars(
        $item['name']
        ?? 'Product'
    ) ?>"
>


<div class="product-info">

<strong>
<?= htmlspecialchars(
    $item['name']
    ?? 'Product'
) ?>
</strong>

<span>
Qty: <?= $itemQty ?>
</span>

</div>


<strong class="product-price">

Rs.
<?= number_format($itemTotal) ?>

</strong>


</div>


<?php endforeach; ?>


</div>


<div class="summary-divider"></div>


<div class="summary-row">

<span>
    Subtotal
</span>

<strong>
    Rs. <?= number_format($subtotal) ?>
</strong>

</div>


<div class="summary-row">

<span>
    Delivery Fee
</span>

<strong>
    Rs. <?= number_format($deliveryFee) ?>
</strong>

</div>


<div class="summary-total">

<span>
    Total
</span>

<strong>
    Rs. <?= number_format($total) ?>
</strong>

</div>


<button
    type="submit"
    id="confirmOrderBtn"
    class="confirm-order-btn"
    <?= empty($cartItems) ? 'disabled' : '' ?>
>

<span>
    Confirm Order
</span>

<span class="material-symbols-outlined">
    check_circle
</span>

</button>


<div class="secure-checkout">

<span class="material-symbols-outlined">
    verified_user
</span>

Secure Checkout

</div>


</div>


</aside>


</form>


</main>


<!-- =====================================================
     SUCCESS MODAL
====================================================== -->

<?php if ($success): ?>

<div
    id="successModal"
    class="modal"
>

<div class="success-box">


<div class="success-icon">

<span class="material-symbols-outlined">
    check
</span>

</div>


<span class="success-label">
    ORDER CONFIRMED
</span>


<h2>
    Order Placed Successfully!
</h2>


<p>

Your order

<strong>
    <?= htmlspecialchars($successOrderId) ?>
</strong>

has been placed and is now being processed.

</p>


<button
    type="button"
    id="viewOrders"
    class="view-orders"
>

View My Orders

<span class="material-symbols-outlined">
    arrow_forward
</span>

</button>


</div>

</div>

<?php endif; ?>


<!-- =====================================================
     FOOTER
====================================================== -->

<footer class="checkout-footer">

<p>
© 2026 Harvestly.
Bridging Sri Lankan Fields to Your Table.
</p>

</footer>


</div>


<script src="/Harvestly/js/Buyer/checkout.js"></script>

</body>

</html>