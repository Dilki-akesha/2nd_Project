<?php
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/layout.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ok = $farmerModel->updateStock((int)($_POST['id'] ?? 0), $farmer_id, (float)($_POST['qty'] ?? 0));
    flash($ok ? 'success' : 'error', $ok ? 'Stock updated.' : 'That product could not be updated.');
    farmer_redirect('inventory.php');
}

$inventory = $farmerModel->inventory($farmer_id);
page_top('Inventory', 'inventory');
?>

<div class="page-title">
    <div>
        <h1>Inventory &amp; Stock</h1>
        <p>Update the stock quantity Harvestly shows to Buyers.</p>
    </div>
    <a class="btn secondary" href="products.php">Manage Listings</a>
</div>

<div class="card">
    <?php if (!$inventory): ?>
        <p class="empty">You have no products yet. <a href="add-product.php">Add your first product</a>.</p>
    <?php else: ?>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Current Stock</th>
                    <th>Status</th>
                    <th>Update Stock</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($inventory as $r): ?>
                <tr>
                    <td><?= e((string)$r['product_name']) ?></td>
                    <td class="nowrap"><?= e(rtrim(rtrim(number_format((float)$r['available_quantity'], 3), '0'), '.')) ?> <?= e((string)$r['unit_label']) ?></td>
                    <td><span class="status-badge <?= (string)$r['listing_status'] === 'ACTIVE' ? 'ok' : ((string)$r['listing_status'] === 'SOLD_OUT' ? 'bad' : 'gray') ?>"><?= e((string)$r['listing_status']) ?></span></td>
                    <td>
                        <form method="post" class="row">
                            <?= csrfField() ?>
                            <input type="hidden" name="id" value="<?= (int)$r['product_id'] ?>">
                            <input class="input" style="max-width:120px" type="number" step="0.001" min="0"
                                   name="qty" value="<?= e((string)$r['available_quantity']) ?>" aria-label="Stock for <?= e((string)$r['product_name']) ?>">
                            <button class="btn small" type="submit">Update</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <p class="muted mt">
        Setting stock to zero automatically marks the listing as Sold Out. Restocking a Sold Out
        listing brings it back to Active.
    </p>
    <?php endif; ?>
</div>

<?php page_bottom(); ?>
