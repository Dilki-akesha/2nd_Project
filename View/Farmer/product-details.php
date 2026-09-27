<?php
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/layout.php';

$id = (int)($_GET['id'] ?? 0);
$product = $farmerModel->productWithCategory($id, $farmer_id);
if (!$product) {
    http_response_code(404);
    flash('error', 'Product not found.');
    farmer_redirect('products.php');
}

$images = $farmerModel->productImages($id);
$shelfReference = null;
if (!empty($product['shelf_life_reference_id'])) {
    $shelfReference = db_fetch_one(
        'SELECT * FROM shelf_life_references WHERE shelf_life_reference_id = ? AND is_active = 1',
        'i',
        [(int)$product['shelf_life_reference_id']]
    );
}

page_top('Product Details', 'products');
?>

<div class="page-title">
    <div>
        <h1><?= e((string)$product['product_name']) ?></h1>
        <p>Listing details from the Harvestly database.</p>
    </div>
    <div class="row">
        <a class="btn secondary" href="products.php">Back to Products</a>
        <a class="btn" href="edit-product.php?id=<?= $id ?>">Edit</a>
    </div>
</div>

<?php if ($images): ?>
<div class="card">
    <div class="row">
        <?php foreach ($images as $img): ?>
        <img src="<?= e(url((string)$img['image_path'])) ?>" alt=""
             style="width:150px;height:150px;object-fit:cover;border-radius:10px;border:1px solid var(--line)">
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<div class="card">
    <dl class="kv">
        <dt>Category</dt>
        <dd><?= e((string)$product['category_name']) ?></dd>

        <dt>Price</dt>
        <dd><?= farmer_money($product['unit_price']) ?> / <?= e((string)$product['unit_label']) ?></dd>

        <dt>Stock</dt>
        <dd><?= e(rtrim(rtrim(number_format((float)$product['available_quantity'], 3), '0'), '.')) ?> <?= e((string)$product['unit_label']) ?></dd>

        <dt>Listing Type</dt>
        <dd><?= e(farmer_status_label((string)$product['listing_type'])) ?></dd>

        <dt>Growing Method</dt>
        <dd><?= e($product['growing_method'] ? farmer_status_label((string)$product['growing_method']) : 'Not specified') ?></dd>

        <dt>Status</dt>
        <dd><span class="status-badge <?= (string)$product['listing_status'] === 'ACTIVE' ? 'ok' : 'gray' ?>"><?= e((string)$product['listing_status']) ?></span></dd>

        <dt>Harvest Date</dt>
        <dd><?= e((string)($product['harvest_date'] ?: '—')) ?></dd>

        <dt>Available From</dt>
        <dd><?= e((string)($product['available_from_date'] ?: '—')) ?></dd>

        <?php if ((string)$product['listing_type'] === 'SEASONAL'): ?>
        <dt>Season</dt>
        <dd><?= e((string)($product['season_start_date'] ?: '—')) ?> to <?= e((string)($product['season_end_date'] ?: '—')) ?></dd>
        <?php endif; ?>

        <dt>Best Before</dt>
        <dd><?= e((string)($product['best_before_date'] ?: '—')) ?></dd>
    </dl>

    <?php if (!empty($product['description'])): ?>
    <hr style="border:0;border-top:1px solid var(--line);margin:18px 0">
    <strong>Description</strong>
    <p class="muted"><?= nl2br(e((string)$product['description'])) ?></p>
    <?php endif; ?>

    <?php if (!empty($product['storage_guidance'])): ?>
    <p class="notice mt"><strong>Your storage guidance:</strong> <?= e((string)$product['storage_guidance']) ?></p>
    <?php endif; ?>
</div>

<?php if ($shelfReference): ?>
<div class="card">
    <h2>Storage reference information</h2>
    <p class="muted">
        Reference record for <?= e((string)$shelfReference['product_reference_name']) ?>. This is
        informational only. Harvestly does not derive a guaranteed best-before date from it.
    </p>
    <dl class="kv mt">
        <?php
        $pairs = [
            'Temperature (°C)' => ['temperature_min_c', 'temperature_max_c'],
            'Relative humidity (%)' => ['humidity_min_percent', 'humidity_max_percent'],
            'Storage life (days)' => ['storage_life_min_days', 'storage_life_max_days'],
        ];
        foreach ($pairs as $label => $keys):
            $min = $shelfReference[$keys[0]];
            $max = $shelfReference[$keys[1]];
            if ($min === null || $max === null) continue;
        ?>
        <dt><?= e($label) ?></dt>
        <dd><?= e((string)$min) ?> &ndash; <?= e((string)$max) ?></dd>
        <?php endforeach; ?>
    </dl>
    <?php if (!empty($shelfReference['storage_guidance'])): ?>
    <p class="muted mt"><?= nl2br(e((string)$shelfReference['storage_guidance'])) ?></p>
    <?php endif; ?>
</div>
<?php else: ?>
<div class="card">
    <p class="muted">
        No storage reference is attached to this product, so Harvestly shows no shelf-life section for it.
    </p>
</div>
<?php endif; ?>

<?php page_bottom(); ?>
