<?php
require __DIR__ . '/../../Controller/Farmer/ProductFormController.php';
require __DIR__ . '/includes/layout.php';
page_top('Edit Product', 'products');
?>

<div class="page-title">
    <div>
        <h1>Edit Product</h1>
        <p>Update <?= e((string)($product['product_name'] ?? 'this listing')) ?>.</p>
    </div>
    <a class="btn secondary" href="products.php">Back</a>
</div>

<form class="card" method="post" enctype="multipart/form-data">
    <?= csrfField() ?>
    <div class="form-grid">
        <div class="field">
            <label>Product Name</label>
            <input class="input" name="product_name" maxlength="150" required value="<?= e((string)($product['product_name'] ?? '')) ?>">
        </div>
        <div class="field">
            <label>Category</label>
            <select class="select" name="category_id" required>
                <?php foreach ($categories as $c): ?>
                <option value="<?= (int)$c['category_id'] ?>" <?= (int)($product['category_id'] ?? 0) === (int)$c['category_id'] ? 'selected' : '' ?>>
                    <?= e((string)$c['category_name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field full">
            <label>Description</label>
            <textarea class="textarea" name="description" rows="3"><?= e((string)($product['description'] ?? '')) ?></textarea>
        </div>
        <div class="field">
            <label>Price (Rs.)</label>
            <input class="input" type="number" step="0.01" min="0.01" name="unit_price" required
                   value="<?= e((string)($product['unit_price'] ?? '')) ?>">
        </div>
        <div class="field">
            <label>Stock Quantity</label>
            <input class="input" type="number" step="0.001" min="0" name="available_quantity" required
                   value="<?= e((string)($product['available_quantity'] ?? '0')) ?>">
        </div>
        <div class="field">
            <label>Unit</label>
            <input class="input" name="unit_label" maxlength="30" value="<?= e((string)($product['unit_label'] ?? 'kg')) ?>">
        </div>
        <div class="field">
            <label>Listing Type</label>
            <select class="select" name="listing_type">
                <?php foreach (['AVAILABLE_NOW' => 'Available Now', 'HARVEST_SOON' => 'Harvest Soon (pre-listing)', 'SEASONAL' => 'Seasonal'] as $v => $label): ?>
                <option value="<?= $v ?>" <?= (string)($product['listing_type'] ?? 'AVAILABLE_NOW') === $v ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label>Growing Method</label>
            <select class="select" name="growing_method">
                <option value="">Not specified</option>
                <?php foreach (['ORGANIC' => 'Organic', 'CONVENTIONAL' => 'Conventional', 'MIXED' => 'Mixed'] as $v => $label): ?>
                <option value="<?= $v ?>" <?= (string)($product['growing_method'] ?? '') === $v ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label>Listing Status</label>
            <select class="select" name="listing_status">
                <?php foreach (['ACTIVE' => 'Active', 'INACTIVE' => 'Inactive', 'SOLD_OUT' => 'Sold Out'] as $v => $label): ?>
                <option value="<?= $v ?>" <?= (string)($product['listing_status'] ?? 'ACTIVE') === $v ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label>Harvest Date</label>
            <input class="input" type="date" name="harvest_date" value="<?= e((string)($product['harvest_date'] ?? '')) ?>">
        </div>
        <div class="field">
            <label>Available From</label>
            <input class="input" type="date" name="available_from_date" value="<?= e((string)($product['available_from_date'] ?? '')) ?>">
        </div>
        <div class="field">
            <label>Season Start</label>
            <input class="input" type="date" name="season_start_date" value="<?= e((string)($product['season_start_date'] ?? '')) ?>">
        </div>
        <div class="field">
            <label>Season End</label>
            <input class="input" type="date" name="season_end_date" value="<?= e((string)($product['season_end_date'] ?? '')) ?>">
        </div>
        <div class="field full">
            <label>Storage / Shelf-Life Reference (optional)</label>
            <select class="select" name="shelf_life_reference_id">
                <option value="">No reference</option>
                <?php foreach ($shelfReferences as $ref): ?>
                <option value="<?= (int)$ref['shelf_life_reference_id'] ?>"
                    <?= (int)($product['shelf_life_reference_id'] ?? 0) === (int)$ref['shelf_life_reference_id'] ? 'selected' : '' ?>>
                    <?= e((string)$ref['product_reference_name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
            <small class="muted">Leave blank to show no shelf-life section for this product.</small>
        </div>
        <div class="field full">
            <label>Own Storage Guidance (optional)</label>
            <input class="input" name="storage_guidance" value="<?= e((string)($product['storage_guidance'] ?? '')) ?>">
        </div>
    </div>

    <?php if ($existingImages): ?>
    <div class="field full mt">
        <label>Current Images</label>
        <div class="row">
            <?php foreach ($existingImages as $img): ?>
            <label class="row" style="gap:8px">
                <img src="<?= e(url((string)$img['image_path'])) ?>" alt="" style="width:96px;height:96px;object-fit:cover;border-radius:8px;border:1px solid var(--line)">
                <span>
                    <?php if ((int)$img['is_primary'] === 1): ?>
                        <span class="badge">Primary</span><br>
                    <?php endif; ?>
                    <span class="muted" style="font-size:12px">Delete</span>
                    <input type="checkbox" name="delete_images[]" value="<?= (int)$img['image_id'] ?>">
                </span>
            </label>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="field full">
        <label>Replace Primary Image (JPG or PNG, 5 MB maximum)</label>
        <input type="file" name="image" accept="image/jpeg,image/png">
    </div>

    <div class="form-actions">
        <a class="btn secondary" href="products.php">Cancel</a>
        <button class="btn" type="submit">Update Product</button>
    </div>
</form>

<?php page_bottom(); ?>
