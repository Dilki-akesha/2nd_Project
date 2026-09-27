<?php
/**
 * Public produce catalogue. Every product shown comes from the database.
 */
$productImage = static function (array $p): string {
    $image = trim((string)($p['image_path'] ?? ''));
    if ($image === '') {
        $image = getProductImage((string)$p['title'], (string)$p['category_name']);
    }
    if (!preg_match('#^https?://#i', $image) && !str_starts_with($image, '/')) {
        $image = url($image);
    }
    return $image;
};
?>
<div class="page-content" style="max-width:1280px;margin:40px auto;padding:0 24px">
    <div style="margin-bottom:28px">
        <h1 style="font-size:32px;font-weight:800;color:var(--color-on-surface);margin:0">Public Produce Catalogue</h1>
        <p style="font-size:15px;color:var(--color-on-surface-variant);margin:6px 0 0">
            Browse produce listed by approved Harvestly Farmers.
        </p>
    </div>

    <form method="GET" action="index.php" style="background:#fff;border-radius:var(--radius-xl);padding:16px;border:1px solid var(--color-outline-variant);margin-bottom:28px;display:flex;flex-wrap:wrap;gap:16px;align-items:center">
        <input type="hidden" name="page" value="products">

        <div style="flex:1;min-width:240px">
            <input type="search" name="search" class="form-control" placeholder="Search produce, Farmer or category" value="<?= sanitize($search) ?>">
        </div>

        <div style="width:220px">
            <select name="category" class="form-control" aria-label="Category">
                <option value="all">All Categories</option>
                <?php foreach ($categoryOptions as $categoryName): ?>
                <option value="<?= sanitize($categoryName) ?>" <?= $catFilter === $categoryName ? 'selected' : '' ?>>
                    <?= sanitize($categoryName) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Apply Filter</button>
        <a href="index.php?page=products" class="btn btn-outline">Reset</a>
    </form>

    <p style="font-size:14px;color:var(--color-outline);margin:0 0 18px">
        <?= count($products) ?> <?= count($products) === 1 ? 'listing' : 'listings' ?> found
    </p>

    <?php if (!$products): ?>
        <p class="notice" style="text-align:center">
            No produce listings match your filters. Try a different search term or clear the filter.
        </p>
    <?php else: ?>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:24px">
        <?php foreach ($products as $p): ?>
        <article style="background:#fff;border-radius:var(--radius-lg);border:1px solid var(--color-outline-variant);overflow:hidden;display:flex;flex-direction:column;box-shadow:var(--shadow-sm)">
            <a href="index.php?page=product_details&amp;id=<?= (int)$p['id'] ?>" style="display:block;height:220px;overflow:hidden;position:relative;background:var(--color-surface-container)">
                <img src="<?= sanitize($productImage($p)) ?>" alt="<?= sanitize($p['title']) ?>" style="width:100%;height:100%;object-fit:cover">
                <span class="badge badge-info" style="position:absolute;bottom:12px;left:12px">
                    <?= sanitize(harvestlyStatusLabel((string)$p['listing_type'])) ?>
                </span>
            </a>
            <div style="padding:20px;display:flex;flex-direction:column;gap:10px;flex:1">
                <div>
                    <h3 style="font-size:18px;font-weight:700;color:var(--color-on-surface);margin:0"><?= sanitize($p['title']) ?></h3>
                    <div style="font-size:13px;color:var(--color-on-surface-variant);margin-top:2px">
                        <?= sanitize($p['farmer_name']) ?>
                        <?php if (!empty($p['pickup_district'])): ?>
                            &middot; <?= sanitize($p['pickup_district']) ?> district
                        <?php endif; ?>
                    </div>
                </div>
                <div style="font-size:12px;color:var(--color-outline)"><?= sanitize($p['category_name']) ?></div>
                <div style="display:flex;justify-content:space-between;align-items:center;margin-top:auto;padding-top:12px;border-top:1px solid var(--color-outline-variant)">
                    <div>
                        <span style="font-size:22px;font-weight:800;color:var(--color-primary)"><?= harvestlyMoney($p['unit_price']) ?></span>
                        <span style="font-size:12px;color:var(--color-outline)"> / <?= sanitize($p['unit_label']) ?></span>
                    </div>
                    <a href="index.php?page=product_details&amp;id=<?= (int)$p['id'] ?>" class="btn btn-primary btn-sm">View Product</a>
                </div>
            </div>
        </article>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
