<?php
require_once __DIR__ . '/../../config/app.php';
if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) redirect('Controller/Buyer/CheckoutController.php');
require __DIR__ . '/includes/layout.php';
buyer_page_top('Checkout', 'CartController.php');

$canOrder = $quote !== null && $quote['routeAvailable'];
$baseFee = (float)db_setting('delivery_base_fee', 0);
$perKm = (float)db_setting('delivery_per_km_rate', 0);
?>

<section class="buyer-title">
    <div>
        <h2>Checkout</h2>
        <p>Check district delivery availability, then confirm your delivery details.</p>
    </div>
    <div class="buyer-actions">
        <a class="buyer-button buyer-ghost" href="<?= e(buyerRoute('CartController.php')) ?>">
            <span class="material-symbols-outlined" aria-hidden="true">arrow_back</span>
            <span>Back to Cart</span>
        </a>
    </div>
</section>

<?php buyerFlash(); ?>

<section class="buyer-panel">
    <h3>1. Destination district</h3>
    <form method="get" action="<?= e(buyerRoute('CheckoutController.php')) ?>" class="buyer-filter-grid">
        <div>
            <label class="buyer-field" for="destination"><span>Destination district</span></label>
            <select id="destination" name="destination_district" required>
                <option value="">Select a district</option>
                <?php foreach ($districts as $d): ?>
                <option value="<?= e($d['district_name']) ?>" <?= $destination === $d['district_name'] ? 'selected' : '' ?>><?= e($d['district_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <button class="buyer-button" type="submit">
                <span class="material-symbols-outlined" aria-hidden="true">search</span>
                <span>Check Delivery &amp; Fees</span>
            </button>
        </div>
    </form>
</section>

<?php if ($quoteError !== ''): ?>
<div class="buyer-alert warn">
    <strong>Delivery not available for this route</strong>
    <?= e($quoteError) ?>
</div>
<?php endif; ?>

<div class="buyer-detail-layout">
    <div>
        <?php if ($quote && $quote['routeAvailable']): ?>
        <form class="buyer-panel" method="post" action="<?= e(buyerRoute('CheckoutController.php')) ?>" data-once="1">
            <?= csrfField() ?>
            <input type="hidden" name="destination_district" value="<?= e($destination) ?>">

            <h3>2. Delivery details</h3>
            <div class="buyer-form-grid">
                <div class="buyer-field">
                    <span>Recipient name</span>
                    <input type="text" name="fullName" value="<?= e((string)($profile['full_name'] ?? '')) ?>" required>
                </div>
                <div class="buyer-field">
                    <span>Phone</span>
                    <input type="tel" name="phone" value="<?= e((string)($profile['phone'] ?? '')) ?>" required>
                </div>
                <div class="buyer-field buyer-field--wide">
                    <span>Street address</span>
                    <input type="text" name="address" value="<?= e((string)($profile['default_address_line1'] ?? '')) ?>" required>
                </div>
                <div class="buyer-field buyer-field--wide">
                    <span>Apartment / landmark (optional)</span>
                    <input type="text" name="address2" value="<?= e((string)($profile['default_address_line2'] ?? '')) ?>">
                </div>
                <div class="buyer-field">
                    <span>City / town (optional)</span>
                    <input type="text" name="city" value="<?= e((string)($profile['default_city_town'] ?? '')) ?>">
                </div>
                <div class="buyer-field">
                    <span>Postal code (optional)</span>
                    <input type="text" name="postal" value="<?= e((string)($profile['default_postal_code'] ?? '')) ?>">
                </div>
            </div>

            <hr class="buyer-hr">
            <h3>3. Payment</h3>
            <div class="buyer-alert info" style="margin-bottom:16px">
                <strong>PayHere Sandbox &mdash; planned, not yet approved</strong>
                Harvestly is structurally ready for PayHere Sandbox, but approval has not been received.
                No payment is collected at this step, no card number or CVV is ever requested or stored,
                and this order will be created in <em>Pending Payment</em>. When PayHere is enabled, a
                payment may only be marked successful after server-side verification.
            </div>

            <button class="buyer-button" type="submit">
                <span class="material-symbols-outlined" aria-hidden="true">receipt_long</span>
                <span>Place Order (Pending Payment)</span>
            </button>
        </form>
        <?php else: ?>
        <section class="buyer-panel">
            <h3>2. Delivery details</h3>
            <p class="buyer-note">Select a destination district above and confirm delivery availability to continue.</p>
        </section>
        <?php endif; ?>
    </div>

    <div>
        <section class="buyer-panel">
            <h3>Order summary</h3>
            <div class="buyer-table-wrap">
                <table class="buyer-table">
                    <thead>
                        <tr><th>Product</th><th class="buyer-num">Amount</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($cartItems as $item): ?>
                        <tr>
                            <td><?= e($item['name']) ?> &times; <?= e(rtrim(rtrim(number_format((float)$item['quantity'], 3), '0'), '.')) ?> <?= e($item['unit']) ?></td>
                            <td class="buyer-num"><?= e(harvestlyMoney((float)$item['price'] * (float)$item['quantity'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <hr class="buyer-hr">
            <dl class="buyer-kv">
                <dt>Product subtotal</dt>
                <dd><?= e(harvestlyMoney($subtotal)) ?></dd>
                <?php if ($quote && $quote['routeAvailable']): ?>
                <dt>Buyer service fee</dt>
                <dd><?= e(harvestlyMoney($quote['serviceFee'])) ?></dd>
                <dt>Delivery fee</dt>
                <dd><?= e(harvestlyMoney($quote['deliveryFee'])) ?></dd>
                <dt class="buyer-kv-total">Total</dt>
                <dd class="buyer-kv-total"><?= e(harvestlyMoney($quote['total'])) ?></dd>
                <?php else: ?>
                <dt>Buyer service fee</dt>
                <dd>Calculated after district check</dd>
                <dt>Delivery fee</dt>
                <dd>Calculated after district check</dd>
                <?php endif; ?>
            </dl>
        </section>

        <?php if ($quote && $quote['routeAvailable']): ?>
        <section class="buyer-panel">
            <h3>Delivery fee calculation</h3>
            <dl class="buyer-kv">
                <dt>Pickup district</dt>
                <dd><?= e($quote['originDistrict']) ?></dd>
                <dt>Destination district</dt>
                <dd><?= e($quote['destinationDistrict']) ?></dd>
                <dt>District reference distance</dt>
                <dd><?= e(number_format((float)$quote['distanceKm'], 2)) ?> km</dd>
                <dt>Base delivery fee</dt>
                <dd><?= e(harvestlyMoney($baseFee)) ?></dd>
                <dt>Per-kilometre rate</dt>
                <dd><?= e(harvestlyMoney($perKm)) ?> / km</dd>
            </dl>
            <p class="buyer-note" style="margin-top:14px">
                Delivery fee = base delivery fee + (district reference distance &times; per-kilometre rate).
            </p>
        </section>
        <?php endif; ?>
    </div>
</div>

<?php buyer_page_bottom(); ?>
