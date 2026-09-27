<?php
$isBuyer = strtolower((string)($_SESSION['role'] ?? '')) === 'buyer';

$image = '';
if ($product) {
    $image = trim((string)($product['image_path'] ?? ''));
    if ($image === '') {
        $image = getProductImage((string)$product['title'], (string)$product['category_name']);
    }
    if (!preg_match('#^https?://#i', $image) && !str_starts_with($image, '/')) {
        $image = url($image);
    }
}
?>
<div class="page-content" style="max-width:1000px;margin:40px auto;padding:0 24px">
    <a href="index.php?page=products" style="font-size:13px;color:var(--color-outline);font-weight:600">&larr; Back to Catalogue</a>

    <?php if (!$product): ?>
        <div class="card" style="margin-top:16px;padding:48px 36px;text-align:center">
            <h1 style="font-size:26px;font-weight:800;margin:0 0 10px">Product not found</h1>
            <p style="color:var(--color-on-surface-variant);margin:0 0 22px">
                This listing is no longer available. It may have been sold out, expired or removed by
                the Farmer or a Harvestly Admin.
            </p>
            <a href="index.php?page=products" class="btn btn-primary">Browse Available Products</a>
        </div>
    <?php else: ?>

    <div style="background:#fff;border-radius:var(--radius-xl);border:1px solid var(--color-outline-variant);padding:36px;margin-top:16px;display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:36px">
        <div style="border-radius:var(--radius-lg);overflow:hidden;height:320px;background:var(--color-surface-container)">
            <img src="<?= sanitize($image) ?>" alt="<?= sanitize($product['title']) ?>" style="width:100%;height:100%;object-fit:cover">
        </div>

        <div style="display:flex;flex-direction:column;gap:16px">
            <div>
                <span class="badge badge-info" style="margin-left:6px">
                    <?= sanitize(harvestlyStatusLabel((string)$product['listing_type'])) ?>
                </span>
                <h1 style="font-size:28px;font-weight:800;color:var(--color-on-surface);margin:8px 0 0">
                    <?= sanitize($product['title']) ?>
                </h1>
                <div style="font-size:14px;color:var(--color-on-surface-variant);margin-top:4px">
                    Listed by <strong><?= sanitize($product['farmer_name']) ?></strong>
                    <?php if (!empty($product['pickup_district'])): ?>
                        &middot; pickup from <?= sanitize($product['pickup_district']) ?> district
                    <?php endif; ?>
                </div>
                <div style="font-size:12px;color:var(--color-outline);margin-top:2px">
                    <?= sanitize($product['category_name']) ?>
                </div>
            </div>

            <div style="font-size:32px;font-weight:800;color:var(--color-primary)">
                <?= harvestlyMoney($product['unit_price']) ?>
                <span style="font-size:14px;font-weight:400;color:var(--color-outline)">/ <?= sanitize($product['unit_label']) ?></span>
            </div>

            <?php if (isset($product['available_quantity'])): ?>
            <div style="font-size:14px;color:var(--color-on-surface-variant)">
                <?= sanitize(rtrim(rtrim(number_format((float)$product['available_quantity'], 3), '0'), '.')) ?>
                <?= sanitize($product['unit_label']) ?> available
            </div>
            <?php endif; ?>

            <p style="font-size:14px;color:var(--color-on-surface-variant);line-height:1.6;margin:0">
                <?= sanitize($product['description'] ?? '') ?>
            </p>

            <div style="margin-top:auto;padding-top:20px;border-top:1px solid var(--color-outline-variant)">
                <?php if ($isBuyer): ?>
                    <form method="post" action="<?= e(buyerRoute('ProductController.php')) ?>">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="add_to_cart">
                        <input type="hidden" name="id" value="<?= (int)$product['id'] ?>">
                        <button class="btn btn-primary" type="submit">Add to Cart</button>
                    </form>
                <?php else: ?>
                    <a href="index.php?page=login" class="btn btn-primary" style="display:block;width:100%;padding:14px;text-align:center">
                        Sign in as a Buyer to place an order
                    </a>
                    <p style="font-size:12px;color:var(--color-outline);text-align:center;margin:12px 0 0">
                        You can browse every Harvestly product without an account.
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
