<?php
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/layout.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = (int)($_POST['order_id'] ?? 0);

    if (!$farmerModel->ownsOrder($orderId, $farmer_id)) {
        flash('error', 'That order does not belong to you.');
        farmer_redirect('report-issue.php');
    }

    $evidencePath = null;
    if (!empty($_FILES['evidence']['name'])) {
        try {
            $evidencePath = handleFileUpload($_FILES['evidence'], 'assets/documents/complaints');
        } catch (Throwable $uploadError) {
            flash('error', $uploadError->getMessage());
            farmer_redirect('report-issue.php');
        }
    }

    $ok = $farmerModel->addComplaint(
        $farmer_id,
        $orderId,
        trim((string)($_POST['category'] ?? 'Other')),
        trim((string)($_POST['description'] ?? '')),
        $evidencePath
    );
    flash($ok ? 'success' : 'error', $ok ? 'Issue submitted.' : 'Please complete the category and description.');
    farmer_redirect('report-issue.php');
}

$complaintOrders = $farmerModel->complaintOrders($farmer_id);
$complaints = $farmerModel->complaints($farmer_id);

page_top('Report Issue', 'report-issue');
?>

<div class="page-title">
    <div>
        <h1>Report Issue</h1>
        <p>Submit and track issues related to one of your orders. Admin reviews every issue.</p>
    </div>
</div>

<div class="card mb">
    <h2>Submit an Issue</h2>
    <?php if (!$complaintOrders): ?>
        <p class="empty">You need at least one order before you can report an order issue.</p>
    <?php else: ?>
    <form method="post" enctype="multipart/form-data">
        <?= csrfField() ?>
        <div class="form-grid">
            <div class="field">
                <label>Order</label>
                <select class="select" name="order_id" required>
                    <?php foreach ($complaintOrders as $r): ?>
                    <option value="<?= (int)$r['order_id'] ?>">
                        <?= e(orderPublicId((int)$r['order_id'])) ?> &middot; <?= e(farmer_status_label((string)$r['order_status'])) ?> &middot; <?= farmer_money($r['grand_total']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label>Category</label>
                <select class="select" name="category" required>
                    <option>Delivery Issue</option>
                    <option>Buyer Issue</option>
                    <option>Product Issue</option>
                    <option>Payment / Earnings</option>
                    <option>Other</option>
                </select>
            </div>
            <div class="field full">
                <label>Description</label>
                <textarea class="textarea" name="description" rows="4" required></textarea>
            </div>
            <div class="field full">
                <label>Evidence (optional)</label>
                <input type="file" name="evidence" accept="image/jpeg,image/png,application/pdf">
                <small class="muted">JPG, PNG or PDF; 5 MB maximum.</small>
            </div>
        </div>
        <button class="btn" type="submit">Submit Issue</button>
    </form>
    <?php endif; ?>
</div>

<div class="card">
    <h2>My Issues</h2>
    <?php if (!$complaints): ?>
        <p class="empty">You have not submitted any issues yet.</p>
    <?php else: ?>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Category</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Admin Response</th>
                    <th>Submitted</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($complaints as $c): ?>
                <tr>
                    <td class="nowrap"><?= e(orderPublicId((int)$c['order_id'])) ?></td>
                    <td><?= e((string)$c['category']) ?></td>
                    <td style="white-space:normal;min-width:220px"><?= e((string)$c['description']) ?></td>
                    <td><span class="status-badge <?= e(farmer_status_tone((string)$c['complaint_status'])) ?>"><?= e(farmer_status_label((string)$c['complaint_status'])) ?></span></td>
                    <td style="white-space:normal;min-width:200px"><?= e((string)($c['admin_response'] ?: '—')) ?></td>
                    <td class="nowrap"><?= e(date('d M Y', strtotime((string)$c['created_at']))) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?php page_bottom(); ?>
