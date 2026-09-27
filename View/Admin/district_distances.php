<?php
/**
 * Admin - District Distance Reference Data.
 *
 * These stored district-to-district distances drive the delivery fee formula:
 *   Delivery Fee = Base Delivery Fee + (District Reference Distance x Per-Kilometre Rate)
 *
 * This is an approximate district reference table, not a GPS or live distance source.
 */
$fromDistrict = $fromDistrict ?? '';
$toDistrict = $toDistrict ?? '';
$allDistricts = $allDistricts ?? [];
?>
<div class="view-container">
    <div class="page-header mb-4" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px">
        <div>
            <h1>District Distance Reference</h1>
            <p class="text-sm text-muted">
                Stored district-to-district reference distances used for the delivery fee calculation.
            </p>
        </div>
        <button type="button" class="btn btn-primary" data-modal-target="modal-add-distance">+ Add District Pair</button>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success mb-4"><?= sanitize($_GET['success']) ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger mb-4"><?= sanitize($_GET['error']) ?></div>
    <?php endif; ?>

    <div class="card mb-4">
        <form method="GET" action="index.php" style="display:flex;flex-wrap:wrap;gap:16px;align-items:flex-end">
            <input type="hidden" name="page" value="admin_district_distances">
            <div style="flex:1;min-width:200px">
                <label class="form-label" for="from-district">From District</label>
                <input type="search" id="from-district" name="from_district" class="input-field"
                       value="<?= sanitize($fromDistrict) ?>" placeholder="e.g. Kandy">
            </div>
            <div style="flex:1;min-width:200px">
                <label class="form-label" for="to-district">To District</label>
                <input type="search" id="to-district" name="to_district" class="input-field"
                       value="<?= sanitize($toDistrict) ?>" placeholder="e.g. Colombo">
            </div>
            <button type="submit" class="btn btn-primary">Search</button>
            <?php if ($fromDistrict !== '' || $toDistrict !== ''): ?>
            <a href="index.php?page=admin_district_distances" class="btn btn-outline">Reset</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="card" style="padding:0;overflow:hidden">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>From District</th>
                        <th>To District</th>
                        <th>Reference Distance (km)</th>
                        <th style="text-align:right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!$distances): ?>
                    <tr>
                        <td colspan="5" style="text-align:center;padding:32px;color:var(--color-outline)">
                            No district distance reference rows match your search.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($distances as $dist): ?>
                    <tr>
                        <td>#DIST-<?= sprintf('%04d', (int)$dist['id']) ?></td>
                        <td><strong><?= sanitize($dist['from_district']) ?></strong></td>
                        <td><strong><?= sanitize($dist['to_district']) ?></strong></td>
                        <td><span class="badge badge-info"><?= number_format((float)$dist['distance_km'], 2) ?> km</span></td>
                        <td style="text-align:right">
                            <div style="display:flex;gap:6px;justify-content:flex-end">
                                <button type="button" class="btn btn-sm btn-secondary"
                                        data-modal-target="modal-edit-distance"
                                        data-distance-id="<?= (int)$dist['id'] ?>"
                                        data-from="<?= sanitize($dist['from_district']) ?>"
                                        data-to="<?= sanitize($dist['to_district']) ?>"
                                        data-km="<?= e((string)$dist['distance_km']) ?>">Edit</button>
                                <form method="POST" action="index.php?admin_action=delete_district_distance" style="display:inline"
                                      onsubmit="return confirm('Remove this district distance reference? Orders using this route will not be able to calculate a delivery fee.');">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="distance_id" value="<?= (int)$dist['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card mt-4">
        <p class="text-sm text-muted" style="margin:0">
            <strong>How this table is used.</strong> At checkout, Harvestly looks up the reference
            distance for the Farmer pickup district and the Buyer destination district, then applies
            <em>base delivery fee + (reference distance &times; per-kilometre rate)</em>. This is an
            approximate district-level calculation and is not a GPS, live or address-to-address
            distance. Both directions must exist for a route, or the checkout will report that no fee
            can be calculated.
        </p>
    </div>
</div>

<!-- Add district pair -->
<div class="modal-backdrop" id="modal-add-distance">
    <div class="modal-card" style="max-width:520px">
        <div class="modal-header">
            <div class="modal-title">Add District Distance Reference</div>
            <button type="button" class="modal-close-btn" data-modal-close>&times;</button>
        </div>
        <form method="POST" action="index.php?admin_action=add_district_distance">
            <?= csrfField() ?>
            <div class="modal-body" style="display:grid;gap:14px">
                <div class="form-group">
                    <label class="form-label" for="add-from">From District *</label>
                    <select id="add-from" name="from_district" class="form-control" required>
                        <option value="">Select a district</option>
                        <?php foreach ($allDistricts as $d): ?>
                        <option value="<?= sanitize($d['district_name']) ?>"
                            <?= (int)$d['is_active'] === 1 ? '' : 'disabled' ?>>
                            <?= sanitize($d['district_name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="add-to">To District *</label>
                    <select id="add-to" name="to_district" class="form-control" required>
                        <option value="">Select a district</option>
                        <?php foreach ($allDistricts as $d): ?>
                        <option value="<?= sanitize($d['district_name']) ?>"
                            <?= (int)$d['is_active'] === 1 ? '' : 'disabled' ?>>
                            <?= sanitize($d['district_name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="add-km">Reference Distance (km) *</label>
                    <input type="number" id="add-km" name="distance_km" class="form-control"
                           step="0.01" min="0" required placeholder="e.g. 170.50">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline btn-sm" data-modal-close>Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm">Save Distance</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit district pair -->
<div class="modal-backdrop" id="modal-edit-distance">
    <div class="modal-card" style="max-width:520px">
        <div class="modal-header">
            <div class="modal-title">Edit District Distance Reference</div>
            <button type="button" class="modal-close-btn" data-modal-close>&times;</button>
        </div>
        <form method="POST" action="index.php?admin_action=update_district_distance">
            <?= csrfField() ?>
            <input type="hidden" name="distance_id" id="edit-dist-id">
            <div class="modal-body" style="display:grid;gap:14px">
                <div class="form-group">
                    <label class="form-label">From District</label>
                    <input type="text" id="edit-from" class="input-field" readonly>
                </div>
                <div class="form-group">
                    <label class="form-label">To District</label>
                    <input type="text" id="edit-to" class="input-field" readonly>
                </div>
                <div class="form-group">
                    <label class="form-label" for="edit-km">Reference Distance (km) *</label>
                    <input type="number" id="edit-km" name="distance_km" class="form-control"
                           step="0.01" min="0" required>
                    <small class="muted">Only the distance value can be changed. Delete and re-add the pair to change its districts.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline btn-sm" data-modal-close>Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm">Update Distance</button>
            </div>
        </form>
    </div>
</div>

<script>
/* Fill the edit form from the clicked row's data attributes. */
(function () {
    document.addEventListener('click', function (event) {
        var trigger = event.target.closest('[data-modal-target="modal-edit-distance"]');
        if (!trigger) return;
        document.getElementById('edit-dist-id').value = trigger.getAttribute('data-distance-id');
        document.getElementById('edit-from').value = trigger.getAttribute('data-from');
        document.getElementById('edit-to').value = trigger.getAttribute('data-to');
        document.getElementById('edit-km').value = trigger.getAttribute('data-km');
    });
})();
</script>
