<?php
$routes = $data['routes'] ?? [];
$action = 'Controller/Courier/CourierController.php';
?>

<div class="courier-card">
    <h3>District Coverage Routes</h3>
</div>

<div class="courier-card">
    <h3>Add a Coverage Route</h3>
    <?php if (count($districts) < 2): ?>
    <p class="courier-empty">At least two active districts are required before routes can be created.</p>
    <?php else: ?>
    <form method="post" action="<?= e(url($action)) ?>">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="add_route">
        <input type="hidden" name="return_page" value="coverage">
        <div class="courier-grid-2">
            <div class="courier-field">
                <span>Origin (pickup) district</span>
                <select name="origin_district_id" required>
                    <option value="">Select a district</option>
                    <?php foreach ($districts as $d): ?>
                    <option value="<?= (int)$d['district_id'] ?>"><?= e((string)$d['district_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="courier-field">
                <span>Destination district</span>
                <select name="destination_district_id" required>
                    <option value="">Select a district</option>
                    <?php foreach ($districts as $d): ?>
                    <option value="<?= (int)$d['district_id'] ?>"><?= e((string)$d['district_name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <small>The same district may be used for both when you deliver within one district.</small>
            </div>
        </div>
        <button class="courier-btn" type="submit">
            <span class="material-symbols-outlined" aria-hidden="true">add</span>
            <span>Add Route</span>
        </button>
    </form>
    <?php endif; ?>
</div>

<div class="courier-card">
    <h3>Your Routes (<?= count($routes) ?>)</h3>
    <?php if (!$routes): ?>
    <p class="courier-empty">
        You have no coverage routes yet. Add your first pickup-district to destination-district route
        above. Without an active route you will not receive any assignment offers.
    </p>
    <?php else: ?>
    <?php foreach ($routes as $r): ?>
    <div style="border:1px solid var(--hv-border);border-radius:var(--hv-radius);padding:16px;margin-bottom:14px">
        <div class="courier-row between mb">
            <span class="courier-route">
                <?= e((string)$r['origin']) ?>
                <span class="material-symbols-outlined" aria-hidden="true">arrow_forward</span>
                <?= e((string)$r['destination']) ?>
            </span>
            <span class="courier-badge <?= (int)$r['is_active'] === 1 ? 'ok' : 'muted' ?>">
                <?= (int)$r['is_active'] === 1 ? 'Active' : 'Inactive' ?>
            </span>
        </div>

        <form method="post" action="<?= e(url($action)) ?>">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="update_route">
            <input type="hidden" name="return_page" value="coverage">
            <input type="hidden" name="route_id" value="<?= (int)$r['route_id'] ?>">
            <div class="courier-grid-2">
                <div class="courier-field">
                    <span>Origin (pickup) district</span>
                    <select name="origin_district_id" required>
                        <?php foreach ($districts as $d): ?>
                        <option value="<?= (int)$d['district_id'] ?>" <?= (int)$r['origin_district_id'] === (int)$d['district_id'] ? 'selected' : '' ?>>
                            <?= e((string)$d['district_name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="courier-field">
                    <span>Destination district</span>
                    <select name="destination_district_id" required>
                        <?php foreach ($districts as $d): ?>
                        <option value="<?= (int)$d['district_id'] ?>" <?= (int)$r['destination_district_id'] === (int)$d['district_id'] ? 'selected' : '' ?>>
                            <?= e((string)$d['district_name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="courier-field">
                <label class="courier-row" style="gap:8px;cursor:pointer">
                    <input type="checkbox" name="is_active" value="1" <?= (int)$r['is_active'] === 1 ? 'checked' : '' ?> style="width:auto">
                    <span style="margin:0">Route is active and eligible for assignment offers</span>
                </label>
            </div>
            <button class="courier-btn" type="submit">
                <span class="material-symbols-outlined" aria-hidden="true">save</span>
                <span>Save Changes</span>
            </button>
        </form>

        <form method="post" action="<?= e(url($action)) ?>" class="mt"
              onsubmit="return confirm('Remove this coverage route?');">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="delete_route">
            <input type="hidden" name="return_page" value="coverage">
            <input type="hidden" name="route_id" value="<?= (int)$r['route_id'] ?>">
            <button class="courier-btn danger small" type="submit">
                <span class="material-symbols-outlined" aria-hidden="true">delete</span>
                <span>Remove Route</span>
            </button>
        </form>
    </div>
    <?php endforeach; ?>
    <p class="courier-note">
        Re-adding a route you previously removed reactivates it, because Harvestly keeps one row per
        route per organisation.
    </p>
    <?php endif; ?>
</div>
