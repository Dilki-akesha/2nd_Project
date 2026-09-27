<?php
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/layout.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ok = $farmerModel->updateHarvestDates(
        (int)($_POST['id'] ?? 0),
        $farmer_id,
        trim((string)($_POST['harvest_date'] ?? '')),
        trim((string)($_POST['available_from_date'] ?? ''))
    );
    flash(
        $ok ? 'success' : 'error',
        $ok ? 'Harvest dates updated.' : 'Enter valid dates, or this product is not a Harvest Soon pre-listing.'
    );
    farmer_redirect('harvest-soon.php');
}

$listings = $farmerModel->harvestSoonListings($farmer_id);
page_top('Pre-Listings', 'harvest-soon');
?>

<div class="page-title">
    <div>
        <h1>Pre-Listings / Harvest Soon</h1>
        <p>Maintain the harvest and availability dates for your Harvest Soon pre-listings.</p>
    </div>
    <a class="btn" href="add-product.php?listing_type=HARVEST_SOON">+ Add Pre-Listing</a>
</div>

<?php if (!$listings): ?>
<div class="card">
    <p class="empty">
        You have no Harvest Soon pre-listings. Create a product with the
        <strong>Harvest Soon (pre-listing)</strong> listing type to publish one.
    </p>
    <div class="row mt">
        <a class="btn" href="add-product.php?listing_type=HARVEST_SOON">+ Add Pre-Listing</a>
        <a class="btn secondary" href="products.php">Manage All Products</a>
    </div>
</div>
<?php else: ?>
<?php foreach ($listings as $r): ?>
<form class="card mb" method="post">
    <?= csrfField() ?>
    <input type="hidden" name="id" value="<?= (int)$r['product_id'] ?>">
    <div class="section-head">
        <strong><?= e((string)$r['product_name']) ?></strong>
        <span class="badge">Harvest Soon</span>
    </div>
    <p class="muted" style="font-size:12px">
        Planned stock: <?= e(rtrim(rtrim(number_format((float)$r['available_quantity'], 3), '0'), '.')) ?> <?= e((string)$r['unit_label']) ?>
        &middot; status <?= e(farmer_status_label((string)$r['listing_status'])) ?>
    </p>
    <div class="form-grid">
        <div class="field">
            <label>Harvest Date</label>
            <input class="input" type="date" name="harvest_date" value="<?= e((string)($r['harvest_date'] ?? '')) ?>">
        </div>
        <div class="field">
            <label>Available From</label>
            <input class="input" type="date" name="available_from_date" value="<?= e((string)($r['available_from_date'] ?? '')) ?>">
        </div>
    </div>
    <button class="btn" type="submit">Update Dates</button>
</form>
<?php endforeach; ?>
<?php endif; ?>

<?php page_bottom(); ?>
