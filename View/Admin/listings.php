<?php
/**
 * Admin - Listings Monitor.
 * products.listing_status is an ENUM of ACTIVE, INACTIVE, EXPIRED, SOLD_OUT.
 */
$statusTone = [
    'ACTIVE'   => 'badge-success',
    'INACTIVE' => 'badge-pending',
    'EXPIRED'  => 'badge-danger',
    'SOLD_OUT' => 'badge-danger',
];
?>
<div class="page-content">
    <div class="section-header">
        <div class="section-title-group">
            <h1>Listings Monitor</h1>
            <p>Moderate product listings from Farmers.</p>
        </div>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success"><?= sanitize($_GET['success']); ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger"><?= sanitize($_GET['error']); ?></div>
    <?php endif; ?>

    <div class="card-table-wrapper">
        <div class="table-toolbar">
            <div class="search-box">
                <input type="text" placeholder="Search product title or Farmer..." data-table-search="listings-table">
            </div>
        </div>

        <table class="data-table" id="listings-table">
            <thead>
                <tr>
                    <th>Listing ID</th>
                    <th>Product</th>
                    <th>Category</th>
                    <th>Farmer (Pickup District)</th>
                    <th>Price / Unit</th>
                    <th>Stock</th>
                    <th>Listing Type</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($products)): ?>
                <tr>
                    <td colspan="9" style="text-align:center;padding:32px;color:var(--color-outline)">
                        No produce listings found.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($products as $p):
                    $status = strtoupper((string)$p['status']);
                ?>
                <tr>
                    <td>#PRD-<?= sprintf('%04d', (int)$p['id']); ?></td>
                    <td><strong><?= sanitize($p['title']); ?></strong></td>
                    <td><?= sanitize($p['category']); ?></td>
                    <td>
                        <?= sanitize($p['farmer_name']); ?>
                        <div class="muted" style="font-size:12px"><?= sanitize($p['district'] ?: 'District not set'); ?></div>
                    </td>
                    <td class="nowrap"><strong>Rs. <?= number_format((float)$p['price_per_unit'], 2); ?></strong> / <?= sanitize($p['unit_type']); ?></td>
                    <td class="nowrap"><?= e(rtrim(rtrim(number_format((float)$p['available_quantity'] ?? 0, 3), '0'), '.')); ?></td>
                    <td><?= sanitize(harvestlyStatusLabel((string)$p['listing_type'])); ?></td>
                    <td>
                        <span class="badge <?= $statusTone[$status] ?? 'badge-pending'; ?>">
                            <?= sanitize(harvestlyStatusLabel($status)); ?>
                        </span>
                    </td>
                    <td>
                        <div style="display:flex;gap:6px;flex-wrap:wrap">
                            <?php if ($status === 'ACTIVE'): ?>
                                <form action="index.php?admin_action=update_product_status" method="POST" style="display:inline">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="product_id" value="<?= (int)$p['id']; ?>">
                                    <input type="hidden" name="status" value="INACTIVE">
                                    <button type="submit" class="btn btn-outline btn-sm">Deactivate</button>
                                </form>
                                <form action="index.php?admin_action=update_product_status" method="POST" style="display:inline"
                                      onsubmit="return confirm('Mark this listing as Expired? Buyers will no longer be able to purchase it.');">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="product_id" value="<?= (int)$p['id']; ?>">
                                    <input type="hidden" name="status" value="EXPIRED">
                                    <button type="submit" class="btn btn-danger btn-sm">Expire</button>
                                </form>
                            <?php elseif ($status === 'SOLD_OUT'): ?>
                                <form action="index.php?admin_action=update_product_status" method="POST" style="display:inline">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="product_id" value="<?= (int)$p['id']; ?>">
                                    <input type="hidden" name="status" value="INACTIVE">
                                    <button type="submit" class="btn btn-outline btn-sm">Set Inactive</button>
                                </form>
                            <?php else: ?>
                                <form action="index.php?admin_action=update_product_status" method="POST" style="display:inline">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="product_id" value="<?= (int)$p['id']; ?>">
                                    <input type="hidden" name="status" value="ACTIVE">
                                    <button type="submit" class="btn btn-primary btn-sm">Re-activate</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="card" style="margin-top:20px">
        <p class="muted" style="margin:0">
            Setting a listing to <strong>Inactive</strong> or <strong>Expired</strong> removes it from
            Buyer browsing immediately. Listings that are already part of an order are preserved so
            order history stays intact.
        </p>
    </div>
</div>
