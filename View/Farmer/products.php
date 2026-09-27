<?php
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/../../Model/Farmer/FarmerProductModel.php';

$productModel = new FarmerProductModel();

/* Delete product. Products referenced by an order are protected. */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $ok = $productModel->delete((int)$_POST['delete_id'], $farmer_id);
    flash(
        $ok ? 'success' : 'error',
        $ok ? 'Product deleted.' : 'This product is in use. Deactivate it to preserve order history.'
    );
    farmer_redirect('products.php');
}

/* Activate / deactivate a listing. */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_id'])) {
    $id = (int)$_POST['toggle_id'];
    $next = ($_POST['status'] ?? '') === 'ACTIVE' ? 'INACTIVE' : 'ACTIVE';
    $ok = $farmerModel->toggleProductStatus($id, $farmer_id, $next);
    flash(
        $ok ? 'success' : 'error',
        $ok ? 'Product status updated.' : 'That product could not be updated.'
    );
    farmer_redirect('products.php');
}

$products = $farmerModel->products($farmer_id);

page_top('Products', 'products');
?>

<div class="page-title">
    <div>
        <h1>My Products</h1>
        <p>Create, view, edit and delete your Harvestly listings.</p>
    </div>
    <a class="btn" href="add-product.php">+ Add Product</a>
</div>

<div class="card">
    <?php if (!$products): ?>
        <p class="empty">You have no products yet. Use <strong>Add Product</strong> to create your first listing.</p>
    <?php else: ?>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Category</th>
                    <th>Listing Type</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($products as $r): ?>
                <tr>
                    <td><strong><?= e((string)$r['product_name']) ?></strong></td>
                    <td><?= e((string)$r['category_name']) ?></td>
                    <td><?= e(farmer_status_label((string)$r['listing_type'])) ?></td>
                    <td class="nowrap"><?= farmer_money($r['unit_price']) ?> / <?= e((string)$r['unit_label']) ?></td>
                    <td class="nowrap"><?= e(rtrim(rtrim(number_format((float)$r['available_quantity'], 3), '0'), '.')) ?> <?= e((string)$r['unit_label']) ?></td>
                    <td><span class="status-badge <?= (string)$r['listing_status'] === 'ACTIVE' ? 'ok' : 'gray' ?>"><?= e((string)$r['listing_status']) ?></span></td>
                    <td>
                        <div class="row">
                            <a class="btn secondary small nowrap" href="product-details.php?id=<?= (int)$r['product_id'] ?>">View</a>
                            <a class="btn secondary small nowrap" href="edit-product.php?id=<?= (int)$r['product_id'] ?>">Edit</a>
                            <form method="post" style="display:inline">
                                <?= csrfField() ?>
                                <input type="hidden" name="toggle_id" value="<?= (int)$r['product_id'] ?>">
                                <input type="hidden" name="status" value="<?= e((string)$r['listing_status']) ?>">
                                <button class="btn secondary small" type="submit"><?= (string)$r['listing_status'] === 'ACTIVE' ? 'Deactivate' : 'Activate' ?></button>
                            </form>
                            <form method="post" style="display:inline"
                                  onsubmit="return confirm('Permanently delete this product? This cannot be undone.');">
                                <?= csrfField() ?>
                                <input type="hidden" name="delete_id" value="<?= (int)$r['product_id'] ?>">
                                <button class="btn danger small" type="submit">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?php page_bottom(); ?>
