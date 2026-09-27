<?php
require_once __DIR__ . '/../../config/app.php';
if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    redirect('Controller/Buyer/FarmerStoreController.php' . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : ''));
}
require __DIR__ . '/includes/layout.php';

$store = $store ?? [];
$products = $store['products'] ?? [];
$name = (string)($store['name'] ?? 'Farmer');
$rating = (float)($store['rating'] ?? 0);
$reviewCount = (int)($store['reviewCount'] ?? 0);
$initials = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $name) ?: 'F', 0, 2));

buyer_page_top('Farmer Store', 'ProductController.php');

if ($store === null) {
    ?>
    <section class="buyer-panel">
        <h3>Farmer store not found</h3>
        <p class="buyer-note">This Farmer has no active listings, or the store is no longer available.</p>
        <div class="buyer-actions" style="margin-top:16px">
            <a class="buyer-button" href="<?= e(buyerRoute('ProductController.php')) ?>">Browse Products</a>
        </div>
    </section>
    <?php
    buyer_page_bottom();
    return;
}
?>

<div class="buyer-actions no-print" style="margin-bottom:18px">
    <a class="buyer-button buyer-ghost" href="<?= e(buyerRoute('ProductController.php')) ?>">
        <span class="material-symbols-outlined" aria-hidden="true">arrow_back</span>
        <span>Browse Products</span>
    </a>
</div>

<section class="buyer-panel">
    <div class="buyer-row" style="gap:18px">
        <span class="buyer-avatar" style="width:60px;height:60px;font-size:22px" aria-hidden="true"><?= e($initials) ?></span>
        <div class="buyer-grow">
            <div class="buyer-product-badges" style="margin-bottom:6px">
                <span class="buyer-badge">
                    <span class="material-symbols-outlined" aria-hidden="true" style="font-size:14px;vertical-align:-2px">verified</span>
                    Approved Harvestly Farmer
                </span>
            </div>
            <h2 style="margin:0"><?= e($name) ?></h2>
            <p class="buyer-note" style="margin-top:6px">
                <?php if ($reviewCount > 0): ?>
                    <?= e(number_format($rating, 1)) ?> / 5 from <?= $reviewCount ?> review<?= $reviewCount === 1 ? '' : 's' ?>
                <?php else: ?>
                    No reviews yet
                <?php endif; ?>
                <?php if (!empty($store['district'])): ?>
                    &middot; pickup from <?= e($store['district']) ?> district
                <?php endif; ?>
            </p>
        </div>
    </div>

    <hr class="buyer-hr">
    <div class="buyer-columns-3">
        <dl class="buyer-kv">
            <dt>Farm / pickup</dt>
            <dd><?= e((string)($store['farm'] ?: ($store['district'] ?: 'Not provided'))) ?></dd>
        </dl>
        <dl class="buyer-kv">
            <dt>Growing method</dt>
            <dd><?= e(harvestlyStatusLabel((string)($products[0]['growing_method'] ?? ''))) ?></dd>
        </dl>
        <dl class="buyer-kv">
            <dt>Active listings</dt>
            <dd><?= count($products) ?></dd>
        </dl>
    </div>
    <p class="buyer-note" style="margin-top:14px">
        Delivery is available when an approved, available Courier Partner organisation supports the
        route from this Farmer's pickup district to your destination district. Fees use stored district
        reference distances. Harvestly does not use maps, live tracking, drivers or vehicles.
    </p>
</section>

<section class="buyer-panel">
    <h3>Products by <?= e($name) ?></h3>
    <?php if (!$products): ?>
        <p class="buyer-empty">This Farmer has no active listings at the moment.</p>
    <?php else: ?>
    <div class="buyer-product-grid">
        <?php foreach ($products as $product): ?>
        <article class="buyer-product">
            <a href="<?= e(buyerRoute('ProductDetailsController.php', 'id=' . (int)$product['id'])) ?>">
                <img src="<?= e($product['image']) ?>" alt="<?= e($product['name']) ?>">
            </a>
            <div class="buyer-product-content">
                <div class="buyer-product-badges">
                    <span class="buyer-badge buyer-badge--muted"><?= e(harvestlyStatusLabel((string)$product['listing_type'])) ?></span>
                    <?php if (!empty($product['organic'])): ?>
                    <span class="buyer-badge">Organic</span>
                    <?php endif; ?>
                </div>
                <h3><a href="<?= e(buyerRoute('ProductDetailsController.php', 'id=' . (int)$product['id'])) ?>"><?= e($product['name']) ?></a></h3>
                <p class="buyer-product-meta">
                    <?= e(rtrim(rtrim(number_format((float)$product['stock'], 3), '0'), '.')) ?> <?= e($product['unit']) ?> available
                </p>
                <p class="buyer-price"><?= e(harvestlyMoney($product['price'])) ?> / <?= e($product['unit']) ?></p>
                <div class="buyer-product-foot">
                    <a class="buyer-button buyer-secondary buyer-small" href="<?= e(buyerRoute('ProductDetailsController.php', 'id=' . (int)$product['id'])) ?>">View Product</a>
                    <?php if ((float)$product['stock'] > 0): ?>
                    <form method="post" action="<?= e(buyerRoute('ProductController.php')) ?>">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="add_to_cart">
                        <input type="hidden" name="id" value="<?= (int)$product['id'] ?>">
                        <button class="buyer-button buyer-small" type="submit">Add</button>
                    </form>
                    <?php else: ?>
                    <span class="buyer-badge buyer-badge--muted">Sold out</span>
                    <?php endif; ?>
                </div>
            </div>
        </article>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</section>

<?php buyer_page_bottom(); ?>
