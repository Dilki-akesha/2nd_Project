<?php
require_once __DIR__ . '/../../config/app.php';
if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) redirect('Controller/Buyer/ProductController.php');
require __DIR__ . '/includes/layout.php';
buyer_page_top('Browse Products', 'ProductController.php');
?>

<section class="buyer-title">
    <div>
        <h2>Browse Products</h2>
        <p>Fresh produce from approved Farmers across Sri Lanka.</p>
    </div>
    <div class="buyer-actions">
        <a class="buyer-button buyer-secondary" href="<?= e(buyerRoute('CartController.php')) ?>">
            <span class="material-symbols-outlined" aria-hidden="true">shopping_cart</span>
            <span>View Cart</span>
        </a>
    </div>
</section>

<form class="buyer-panel" method="get" action="<?= e(buyerRoute('ProductController.php')) ?>">
    <div class="buyer-filter-grid">
        <div>
            <label class="buyer-field" for="search"><span>Search products or Farmers</span></label>
            <input id="search" type="search" name="search" value="<?= e($search) ?>" placeholder="Try carrots or a Farmer's name">
        </div>
        <div>
            <label class="buyer-field" for="district"><span>Farmer pickup district</span></label>
            <select id="district" name="district">
                <option value="">All Districts</option>
                <?php foreach ($districts as $d): ?>
                <option value="<?= e($d['district_name']) ?>" <?= $district === $d['district_name'] ? 'selected' : '' ?>><?= e($d['district_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="buyer-field" for="listingType"><span>Listing type</span></label>
            <select id="listingType" name="listingType">
                <?php foreach (['All Listing Types', 'Available Now', 'Harvest Soon', 'Seasonal'] as $value): ?>
                <option value="<?= e($value) ?>" <?= $listingType === $value ? 'selected' : '' ?>><?= e($value) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="buyer-field" for="growingMethod"><span>Growing method</span></label>
            <select id="growingMethod" name="growingMethod">
                <?php foreach (['All Growing Methods', 'Organic', 'Conventional', 'Mixed'] as $value): ?>
                <option value="<?= e($value) ?>" <?= $growingMethod === $value ? 'selected' : '' ?>><?= e($value) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="buyer-field" for="maxPrice"><span>Maximum unit price (Rs.)</span></label>
            <input id="maxPrice" type="number" min="0" step="0.01" name="maxPrice"
                   value="<?= $maxPrice === PHP_FLOAT_MAX ? '' : e($maxPrice) ?>" placeholder="Any price">
        </div>
        <div>
            <label class="buyer-field" for="sort"><span>Sort by</span></label>
            <select id="sort" name="sort">
                <?php foreach (['Newest', 'Price: Low to High', 'Price: High to Low', 'Best Rated', 'Popular'] as $value): ?>
                <option value="<?= e($value) ?>" <?= $sort === $value ? 'selected' : '' ?>><?= e($value) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <div class="buyer-actions" style="margin-top:18px">
        <button class="buyer-button" type="submit">
            <span class="material-symbols-outlined" aria-hidden="true">search</span>
            <span>Apply Filters</span>
        </button>
        <a class="buyer-button buyer-ghost" href="<?= e(buyerRoute('ProductController.php')) ?>">Clear Filters</a>
    </div>
</form>

<p class="buyer-muted"><?= count($products) ?> product<?= count($products) === 1 ? '' : 's' ?> found</p>

<?php if (!$products): ?>
<div class="buyer-panel buyer-empty">No products match your filters.</div>
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
            </div>
            <h3><a href="<?= e(buyerRoute('ProductDetailsController.php', 'id=' . (int)$product['id'])) ?>"><?= e($product['name']) ?></a></h3>
            <p><?= e($product['farmer']) ?> &middot; <?= e($product['district'] ?: 'District not set') ?></p>
            <p class="buyer-price"><?= e(harvestlyMoney($product['price'])) ?> / <?= e($product['unit']) ?></p>
            <p class="buyer-product-meta"><?= e(rtrim(rtrim(number_format((float)$product['stock'], 3), '0'), '.')) ?> <?= e($product['unit']) ?> available</p>
            <div class="buyer-product-foot">
                <a class="buyer-button buyer-secondary buyer-small" href="<?= e(buyerRoute('ProductDetailsController.php', 'id=' . (int)$product['id'])) ?>">View Details</a>
                <?php if ((float)$product['stock'] > 0): ?>
                <form method="post" action="<?= e(buyerRoute('ProductController.php')) ?>">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="add_to_cart">
                    <input type="hidden" name="id" value="<?= (int)$product['id'] ?>">
                    <button class="buyer-button buyer-small" type="submit">Add to Cart</button>
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

<?php buyer_page_bottom(); ?>
