<?php
require_once __DIR__ . '/../../config/app.php';
if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) redirect('Controller/Buyer/CartController.php');
require __DIR__ . '/includes/layout.php';
buyer_page_top('Your Cart', 'CartController.php');
?>

<section class="buyer-title">
    <div>
        <h2>Your Cart</h2>
        <p>Review your produce before checkout.</p>
    </div>
    <div class="buyer-actions">
        <a class="buyer-button buyer-secondary" href="<?= e(buyerRoute('ProductController.php')) ?>">
            <span class="material-symbols-outlined" aria-hidden="true">arrow_back</span>
            <span>Continue Shopping</span>
        </a>
    </div>
</section>

<?php buyerFlash(); ?>

<?php if (!$cartItems): ?>
<section class="buyer-panel buyer-empty">
    Your cart is empty. <a href="<?= e(buyerRoute('ProductController.php')) ?>">Browse products</a> to add your first item.
</section>
<?php else: ?>

<section class="buyer-panel">
    <p class="buyer-note">
        One order can contain products from a single Farmer only. If your cart has items from more
        than one Farmer, remove the extras you want to order later and check out one Farmer at a time.
    </p>
    <div class="buyer-table-wrap">
        <table class="buyer-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Farmer</th>
                    <th class="buyer-num">Unit price</th>
                    <th>Quantity</th>
                    <th class="buyer-num">Subtotal</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($cartItems as $item): ?>
                <tr>
                    <td class="buyer-product-cell">
                        <?php if (!empty($item['image'])): ?>
                            <img src="<?= e($item['image']) ?>" alt="">
                        <?php endif; ?>
                        <a href="<?= e(buyerRoute('ProductDetailsController.php', 'id=' . (int)$item['id'])) ?>"><?= e($item['name']) ?></a>
                    </td>
                    <td><?= e($item['farmer']) ?></td>
                    <td class="buyer-num"><?= e(harvestlyMoney($item['price'])) ?> / <?= e($item['unit']) ?></td>
                    <td>
                        <form method="post" action="<?= e(buyerRoute('CartController.php')) ?>" class="buyer-actions">
                            <?= csrfField() ?>
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="id" value="<?= (int)$item['id'] ?>">
                            <input type="number" step="0.001" style="width:92px"
                                   aria-label="Quantity for <?= e($item['name']) ?>"
                                   name="quantity" min="0.001" value="<?= e($item['quantity']) ?>" required>
                            <button class="buyer-button buyer-secondary buyer-small" type="submit">Update</button>
                        </form>
                    </td>
                    <td class="buyer-num"><?= e(harvestlyMoney((float)$item['price'] * (float)$item['quantity'])) ?></td>
                    <td>
                        <form method="post" action="<?= e(buyerRoute('CartController.php')) ?>"
                              data-confirm="Remove <?= e($item['name']) ?> from your cart?">
                            <?= csrfField() ?>
                            <input type="hidden" name="action" value="remove">
                            <input type="hidden" name="id" value="<?= (int)$item['id'] ?>">
                            <button class="buyer-button buyer-danger buyer-small" type="submit">Remove</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4">Cart subtotal</td>
                    <td class="buyer-num"><?= e(harvestlyMoney($subtotal)) ?></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
</section>

<section class="buyer-panel">
    <h3>Checkout summary</h3>
    <?php if (!$singleFarmerCart): ?>
        <div class="buyer-alert warn">
            <strong>Checkout unavailable</strong>
            Your cart has products from <?= count($cartFarmers) ?> Farmers
            (<?= e(implode(', ', $cartFarmers)) ?>). Remove the items you want to order later so that
            only one Farmer's products remain, then check out.
        </div>
    <?php endif; ?>
    <dl class="buyer-kv">
        <dt>Cart subtotal</dt>
        <dd><?= e(harvestlyMoney($subtotal)) ?></dd>
        <dt>Delivery fee</dt>
        <dd>Calculated at checkout</dd>
        <dt>Buyer service fee</dt>
        <dd>Calculated at checkout</dd>
    </dl>
    <div class="buyer-actions" style="margin-top:20px">
        <?php if ($singleFarmerCart): ?>
            <a class="buyer-button" href="<?= e(buyerRoute('CheckoutController.php')) ?>">
                <span class="material-symbols-outlined" aria-hidden="true">shopping_bag</span>
                <span>Proceed to Checkout</span>
            </a>
        <?php else: ?>
            <span class="buyer-button buyer-secondary" aria-disabled="true">
                <span class="material-symbols-outlined" aria-hidden="true">shopping_bag</span>
                <span>Proceed to Checkout</span>
            </span>
        <?php endif; ?>
        <form method="post" action="<?= e(buyerRoute('CartController.php')) ?>" data-confirm="Clear all items from your cart?">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="clear">
            <button class="buyer-button buyer-danger" type="submit">Clear Cart</button>
        </form>
    </div>
</section>

<?php endif; ?>

<?php buyer_page_bottom(); ?>
