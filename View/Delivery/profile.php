<?php
require_once __DIR__ . '/../../Model/Delivery/Profile.php';
require_once __DIR__ . '/_districts.php';

$userId = $_SESSION['user_id'] ?? 5;
$profileModel = new Profile();
$profile = $profileModel->get($userId);

$vStatus = $profile['verification_status'] ?? 'PENDING';
$vClass = $vStatus === 'APPROVED' ? 'completed' : ($vStatus === 'REJECTED' ? 'pending' : 'progress');
$vText = $vStatus === 'APPROVED' ? 'Verified Partner' : ($vStatus === 'REJECTED' ? 'Verification Rejected' : 'Pending Verification');

$accStatus = $profile['account_status'] ?? 'ACTIVE';
$accClass = $accStatus === 'ACTIVE' ? 'completed' : 'pending';
$accText = $accStatus === 'ACTIVE' ? 'Active' : 'Suspended';
?>

<div class="page <?= ($page === 'profile' ? 'active' : '') ?>" id="page-profile">
    <div class="section-header">
        <h3>Courier Partner Profile</h3>
        <span class="status-badge <?= $vClass ?>">
            <i class="fas fa-check-circle"></i> <?= $vText ?>
        </span>
    </div>

    <div class="profile-card">
        <div class="profile-header">
            <div class="profile-avatar">
                <i class="fas fa-truck"></i>
            </div>
            <div>
                <h3><?= htmlspecialchars($profile['organisation_name'] ?? 'Organisation') ?></h3>
                <p class="text-muted">Premium Logistics Partner</p>
            </div>
        </div>

        <form id="profileForm" onsubmit="return false;" autocomplete="off">
            <div class="form-row">
                <div class="form-group">
                    <label for="orgName">Organisation Name</label>
                    <input type="text" id="orgName" name="organisation_name" value="<?= htmlspecialchars($profile['organisation_name'] ?? '') ?>" />
                </div>
                <div class="form-group">
                    <label for="contactPerson">Contact Person</label>
                    <input type="text" id="contactPerson" name="contact_person" value="<?= htmlspecialchars($profile['contact_person_name'] ?? '') ?>" />
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="phone">Contact Number</label>
                    <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($profile['phone'] ?? '') ?>" />
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($profile['email'] ?? '') ?>" />
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="address">Organisation Address</label>
                    <input type="text" id="address" name="address" value="<?= htmlspecialchars($profile['office_address_line1'] ?? '') ?>" />
                </div>
                <div class="form-group">
                    <label for="city">City / Town</label>
                    <input type="text" id="city" name="city" value="<?= htmlspecialchars($profile['office_city_town'] ?? '') ?>" />
                </div>
            </div>

            <div class="form-group">
                <label for="district">District</label>
                <select id="district" name="district_id">
                    <option value="">Select district</option>
                    <?php foreach ($sriLankanDistricts as $id => $d): ?>
                        <option value="<?= $id ?>" <?= ($profile['office_district_id'] == $id ? 'selected' : '') ?>>
                            <?= htmlspecialchars($d['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <hr />

            <!-- Account Status -->
            <div class="form-group">
                <label>Account Status</label>
                <div style="display: flex; align-items: center; gap: 12px; padding: 12px 16px; background: var(--md-surface-container-low); border-radius: 8px; margin-top: 4px;">
                    <span class="status-badge <?= $accClass ?>" style="font-size: 12px;">
                        <i class="fas <?= $accStatus === 'ACTIVE' ? 'fa-check-circle' : 'fa-pause-circle' ?>"></i>
                        <?= $accText ?>
                    </span>
                    <span style="font-size: 12px; color: var(--md-on-surface-variant);">
                        <?= $accStatus === 'ACTIVE' ? 'Your account is in good standing.' : 'Contact Admin for assistance.' ?>
                    </span>
                </div>
            </div>

            <!-- Verification Status -->
            <div class="form-group">
                <label>Verification Status</label>
                <div class="verification-badge" style="margin: 8px 0;">
                    <i class="fas fa-certificate"></i>
                    <strong><?= $vText ?></strong>
                    <span><?= $profile['office_district'] ?? 'N/A' ?> Region</span>
                </div>
            </div>

            <div class="form-actions">
                <button class="btn-primary" id="editProfileBtn" type="button">
                    <i class="fas fa-edit"></i> Edit Profile
                </button>
                <button class="btn-primary save-btn" id="saveProfileBtn" type="button">
                    <i class="fas fa-save"></i> Save Changes
                </button>
                <button class="btn-secondary" id="cancelProfileBtn" type="button">Cancel</button>
            </div>
        </form>
    </div>
</div>