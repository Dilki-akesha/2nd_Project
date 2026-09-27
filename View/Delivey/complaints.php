<?php
$complaints = $data['complaints'] ?? [];
$orders = $data['orders'] ?? [];
$action = 'Controller/Courier/CourierController.php';
?>

<div class="courier-card">
    <h3>Complaints / Issues</h3>
    <p class="courier-note">
        Harvestly uses one common issue workflow for Buyer, Farmer and Courier Partner. Submit an
        issue related to one of your deliveries and Admin will review it. There is no separate dispute
        module or arbitration timer.
    </p>
</div>

<div class="courier-grid-2">
    <div class="courier-card">
        <h3>Submit an Issue</h3>
        <?php if (!$orders): ?>
        <p class="courier-empty">You need at least one delivery before you can report an issue.</p>
        <?php else: ?>
        <form method="post" enctype="multipart/form-data" action="<?= e(url($action)) ?>">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="complaint">
            <input type="hidden" name="return_page" value="complaints">

            <div class="courier-field">
                <span>Related order</span>
                <select name="order_id" required>
                    <option value="">Select an order</option>
                    <?php foreach ($orders as $o): ?>
                    <option value="<?= (int)$o['order_id'] ?>"><?= e(orderPublicId((int)$o['order_id'])) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="courier-field">
                <span>Category</span>
                <select name="category" required>
                    <option>Delivery Issue</option>
                    <option>Farmer Issue</option>
                    <option>Buyer Issue</option>
                    <option>Payment / Earnings</option>
                    <option>Other</option>
                </select>
            </div>
            <div class="courier-field">
                <span>Description</span>
                <textarea name="description" required></textarea>
            </div>
            <div class="courier-field">
                <span>Optional evidence</span>
                <input type="file" name="evidence" accept="image/jpeg,image/png,application/pdf">
                <small>JPG, PNG or PDF; 5 MB maximum.</small>
            </div>
            <button class="courier-btn" type="submit">
                <span class="material-symbols-outlined" aria-hidden="true">report_problem</span>
                <span>Submit Issue</span>
            </button>
        </form>
        <?php endif; ?>
    </div>

    <div class="courier-card">
        <h3>My Issues (<?= count($complaints) ?>)</h3>
        <?php if (!$complaints): ?>
        <p class="courier-empty">You have not submitted any issues.</p>
        <?php else: ?>
        <div class="courier-table-wrap">
            <table class="courier-table">
                <thead>
                    <tr><th>Order</th><th>Category</th><th>Description</th><th>Status</th><th>Admin Response</th></tr>
                </thead>
                <tbody>
                <?php foreach ($complaints as $c): ?>
                <tr>
                    <td class="courier-nowrap"><?= e(orderPublicId((int)$c['order_id'])) ?></td>
                    <td><?= e((string)$c['category']) ?></td>
                    <td style="white-space:normal;min-width:200px"><?= e((string)$c['description']) ?></td>
                    <td>
                        <span class="courier-badge <?= e(harvestlyStatusTone((string)$c['complaint_status'])) ?>">
                            <?= e(harvestlyStatusLabel((string)$c['complaint_status'])) ?>
                        </span>
                    </td>
                    <td style="white-space:normal;min-width:180px"><?= e((string)($c['admin_response'] ?: '—')) ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>
