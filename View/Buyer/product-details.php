<?php
require_once __DIR__ . '/../../config/app.php';
if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    redirect('Controller/Buyer/ProductDetailsController.php?' . ($_SERVER['QUERY_STRING'] ?? ''));
}
require __DIR__ . '/includes/layout.php';
buyer_page_top('Product Details', 'ProductController.php');

if (!$product) {
    ?>
    <section class="buyer-panel">
        <h3>Product not found</h3>
        <p class="buyer-note">This listing is no longer available.</p>
        <div class="buyer-actions" style="margin-top:16px">
            <a class="buyer-button" href="<?= e(buyerRoute('ProductController.php')) ?>">Back to Browse Products</a>
        </div>
    </section>
    <?php
    buyer_page_bottom();
    return;
}

$stock = (float)$product['stock'];
?>

<div class="buyer-actions no-print" style="margin-bottom:18px">
    <a class="buyer-button buyer-ghost" href="<?= e(buyerRoute('ProductController.php')) ?>">
        <span class="material-symbols-outlined" aria-hidden="true">arrow_back</span>
        <span>Back to Browse Products</span>
    </a>
    <?php if (!empty($product['farmer'])): ?>
    <a class="buyer-button buyer-ghost" href="<?= e(buyerRoute('FarmerStoreController.php', 'farmer=' . urlencode((string)$product['farmer']))) ?>">
        <span class="material-symbols-outlined" aria-hidden="true">storefront</span>
        <span>Visit this Farmer's store</span>
    </a>
    <?php endif; ?>
</div>

<div class="buyer-detail-layout">
    <div>
        <img class="buyer-detail-image" src="<?= e($product['image']) ?>" alt="<?= e($product['name']) ?>">
    </div>

    <div>
        <section class="buyer-panel">
            <div class="buyer-product-badges" style="margin-bottom:10px">
                <span class="buyer-badge buyer-badge--muted"><?= e(harvestlyStatusLabel((string)$product['listing_type'])) ?></span>
                <?php if (!empty($product['growing_method'])): ?>
                <span class="buyer-badge"><?= e(ucfirst(strtolower(str_replace('_', ' ', (string)$product['growing_method'])))) ?></span>
                <?php endif; ?>
                <?php if ($stock <= 0): ?>
                <span class="buyer-badge buyer-badge--danger">Sold out</span>
                <?php endif; ?>
            </div>
            <h2><?= e($product['name']) ?></h2>
            <p class="buyer-note">
                Sold by
                <?php if (!empty($product['farmer'])): ?>
                <a href="<?= e(buyerRoute('FarmerStoreController.php', 'farmer=' . urlencode((string)$product['farmer']))) ?>"><?= e($product['farmer']) ?></a>
                <?php else: ?>this Farmer<?php endif; ?>
                <?php if (!empty($product['district'])): ?>
                &middot; pickup from <?= e($product['district']) ?> district
                <?php endif; ?>
            </p>
            <p class="buyer-price" style="font-size:24px;margin:12px 0 4px"><?= e(harvestlyMoney($product['price'])) ?> <span style="font-size:14px;font-weight:400;color:var(--hv-muted)">/ <?= e($product['unit']) ?></span></p>
            <p class="buyer-muted"><?= e(rtrim(rtrim(number_format($stock, 3), '0'), '.')) ?> <?= e($product['unit']) ?> available</p>
            <?php if (!empty($product['description'])): ?>
            <hr class="buyer-hr">
            <p class="buyer-note"><?= nl2br(e((string)$product['description'])) ?></p>
            <?php endif; ?>

            <hr class="buyer-hr">
            <?php if ($stock > 0): ?>
            <form method="post" action="<?= e(buyerRoute('ProductController.php')) ?>">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="add_to_cart">
                <input type="hidden" name="id" value="<?= (int)$product['id'] ?>">
                <div class="buyer-field">
                    <span>Quantity (<?= e($product['unit']) ?>)</span>
                    <input type="number" name="qty" min="0.001" step="0.001"
                           max="<?= e(rtrim(rtrim(number_format($stock, 3), '0'), '.')) ?>" value="1" required>
                    <small>Up to <?= e(rtrim(rtrim(number_format($stock, 3), '0'), '.')) ?> <?= e($product['unit']) ?> available.</small>
                </div>
                <button class="buyer-button" type="submit">
                    <span class="material-symbols-outlined" aria-hidden="true">shopping_cart</span>
                    <span>Add to Cart</span>
                </button>
            </form>
            <?php else: ?>
            <p class="buyer-note">This product is currently unavailable for purchase.</p>
            <?php endif; ?>
        </section>

        <?php
        /*
         * Storage / shelf-life information is shown ONLY when the product has a
         * valid reference row in shelf_life_references. When
         * shelf_life_reference_id is NULL no shelf-life section is rendered at
         * all - no placeholder values and no invented storage conditions.
         *
         * The information is reference-only. Harvestly does not derive a
         * guaranteed "best before" date from a maximum storage-life value.
         */
        if (!empty($product['shelf_reference'])):
        $reference = $product['shelf_reference'];
        ?>
        <section class="buyer-panel">
            <h3>Storage reference information</h3>
            <p class="buyer-note">
                Reference guidance for <?= e((string)$reference['product_reference_name']) ?>. This is
                informational only and is not a guaranteed best-before date.
            </p>
            <dl class="buyer-kv">
            <?php
            $rows = [
                'Temperature (°C)' => ['temperature_min_c', 'temperature_max_c'],
                'Relative humidity (%)' => ['humidity_min_percent', 'humidity_max_percent'],
                'Storage life (days)' => ['storage_life_min_days', 'storage_life_max_days'],
            ];
            foreach ($rows as $label => $keys):
                $min = $reference[$keys[0]] ?? null;
                $max = $reference[$keys[1]] ?? null;
                if ($min === null || $max === null) continue;
            ?>
                <dt><?= e($label) ?></dt>
                <dd><?= e((string)$min) ?> &ndash; <?= e((string)$max) ?></dd>
            <?php endforeach; ?>
            </dl>
            <?php if (!empty($reference['storage_guidance'])): ?>
                <p class="buyer-note" style="margin-top:14px"><?= nl2br(e((string)$reference['storage_guidance'])) ?></p>
            <?php endif; ?>
            <?php if (!empty($reference['source_reference'])): ?>
                <p class="buyer-divider-note" style="margin-top:10px">Source: <?= e((string)$reference['source_reference']) ?></p>
            <?php endif; ?>
        </section>
        <?php endif; ?>
    </div>
</div>

<?php buyer_page_bottom(); ?>
