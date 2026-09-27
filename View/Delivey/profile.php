<?php
$courierBase = 'Controller/Courier/CourierController.php?page=';
$isApproved = (string)($profile['verification_status'] ?? 'PENDING') === 'APPROVED';
?>

<div class="courier-card">
    <div class="courier-row between mb">
        <h3 style="margin:0">Organisation Profile</h3>
        <span class="courier-badge <?= $isApproved ? 'ok' : 'warn' ?>">
            <?= e(harvestlyStatusLabel((string)($profile['verification_status'] ?? 'PENDING'))) ?>
        </span>
    </div>
    <p class="courier-note">
        The Courier Partner actor is an organisation or company. Harvestly has no individual driver
        accounts, vehicles, fleets, hubs or live location tracking.
    </p>
</div>

<form class="courier-card" method="post" action="<?= e(url('Controller/Courier/CourierController.php')) ?>">
    <?= csrfField() ?>
    <input type="hidden" name="action" value="profile">
    <input type="hidden" name="return_page" value="profile">

    <div class="courier-grid-2">
        <div class="courier-field">
            <span>Organisation Name</span>
            <input name="organisation_name" maxlength="160" required value="<?= e((string)($profile['organisation_name'] ?? '')) ?>">
        </div>
        <div class="courier-field">
            <span>Contact Person</span>
            <input name="contact_person_name" maxlength="120" value="<?= e((string)($profile['contact_person_name'] ?? '')) ?>">
        </div>
        <div class="courier-field">
            <span>Email (read-only)</span>
            <input value="<?= e((string)($profile['email'] ?? '')) ?>" disabled>
            <small>Contact Harvestly Admin to change the account email.</small>
        </div>
        <div class="courier-field">
            <span>Phone</span>
            <input name="phone" maxlength="25" value="<?= e((string)($profile['phone'] ?? '')) ?>">
        </div>
    </div>

    <div class="courier-field">
        <span>Office Address</span>
        <input name="office_address_line1" maxlength="180" value="<?= e((string)($profile['office_address_line1'] ?? '')) ?>">
    </div>
    <div class="courier-field">
        <span>Address Line 2 (optional)</span>
        <input name="office_address_line2" maxlength="180" value="<?= e((string)($profile['office_address_line2'] ?? '')) ?>">
    </div>

    <div class="courier-grid-2">
        <div class="courier-field">
            <span>City / Town</span>
            <input name="office_city_town" maxlength="100" value="<?= e((string)($profile['office_city_town'] ?? '')) ?>">
        </div>
        <div class="courier-field">
            <span>Postal Code</span>
            <input name="office_postal_code" maxlength="20" value="<?= e((string)($profile['office_postal_code'] ?? '')) ?>">
        </div>
    </div>

    <div class="courier-field">
        <span>Primary Office District</span>
        <select name="office_district_id" required>
            <?php foreach ($districts as $d): ?>
            <option value="<?= (int)$d['district_id'] ?>" <?= (int)($profile['office_district_id'] ?? 0) === (int)$d['district_id'] ? 'selected' : '' ?>>
                <?= e((string)$d['district_name']) ?>
            </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="courier-row">
        <button class="courier-btn" type="submit">
            <span class="material-symbols-outlined" aria-hidden="true">save</span>
            <span>Save Profile</span>
        </button>
        <a class="courier-btn secondary" href="<?= e(url($courierBase . 'coverage')) ?>">
            <span class="material-symbols-outlined" aria-hidden="true">route</span>
            <span>Manage Coverage Routes</span>
        </a>
    </div>
</form>

<div class="courier-card">
    <h3>Account</h3>
    <dl class="courier-kv">
        <dt>Verification status</dt>
        <dd><?= e(harvestlyStatusLabel((string)($profile['verification_status'] ?? 'PENDING'))) ?></dd>
        <dt>Availability</dt>
        <dd><?= e(harvestlyStatusLabel((string)($profile['availability_status'] ?? 'UNAVAILABLE'))) ?></dd>
        <dt>Account status</dt>
        <dd><?= e(harvestlyStatusLabel((string)($profile['account_status'] ?? ''))) ?></dd>
    </dl>
    <p class="courier-note mt">
        Coverage is managed separately as district-to-district routes on the
        <a href="<?= e(url($courierBase . 'coverage')) ?>">Coverage Routes</a> page.
    </p>
</div>
