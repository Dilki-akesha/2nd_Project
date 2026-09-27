<?php
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/layout.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn->begin_transaction();
    try {
        $name = trim((string)($_POST['full_name'] ?? ''));
        if ($name === '') {
            throw new RuntimeException('Please enter your full name.');
        }
        if (!$farmerModel->saveContact($farmer_id, $name, trim((string)($_POST['phone'] ?? '')))) {
            throw new RuntimeException('Unable to update your contact details.');
        }
        if (!$farmerModel->savePickup($farmer_id, $_POST)) {
            throw new RuntimeException('Unable to update your pickup details.');
        }
        $conn->commit();
        $_SESSION['user_name'] = $name;
        flash('success', 'Profile updated.');
        farmer_redirect('profile.php');
    } catch (Throwable $e) {
        $conn->rollback();
        flash('error', $e->getMessage());
        farmer_redirect('profile.php');
    }
}

$profile = $farmerModel->user($farmer_id);
$documents = $farmerModel->verificationDocuments($farmer_id);

page_top('Farmer Profile', 'profile');
?>

<div class="page-title">
    <div>
        <h1>Farmer Profile</h1>
        <p>Manage your contact details and pickup location.</p>
    </div>
</div>

<form class="card mb" method="post">
    <?= csrfField() ?>
    <div class="form-grid">
        <div class="field">
            <label>Full Name</label>
            <input class="input" name="full_name" maxlength="120" required value="<?= e((string)($profile['full_name'] ?? '')) ?>">
        </div>
        <div class="field">
            <label>Email (read-only)</label>
            <input class="input" value="<?= e((string)($profile['email'] ?? '')) ?>" disabled>
            <small class="muted">Contact Harvestly Admin to change your email.</small>
        </div>
        <div class="field">
            <label>Phone</label>
            <input class="input" name="phone" maxlength="25" value="<?= e((string)($profile['phone'] ?? '')) ?>">
        </div>
        <div class="field">
            <label>Farm Name</label>
            <input class="input" name="farm_name" maxlength="150" value="<?= e((string)($profile['farm_name'] ?? '')) ?>">
        </div>
        <div class="field">
            <label>Pickup Address</label>
            <input class="input" name="pickup_address_line1" maxlength="180" required value="<?= e((string)($profile['pickup_address_line1'] ?? '')) ?>">
        </div>
        <div class="field">
            <label>Address Line 2 (optional)</label>
            <input class="input" name="pickup_address_line2" maxlength="180" value="<?= e((string)($profile['pickup_address_line2'] ?? '')) ?>">
        </div>
        <div class="field">
            <label>City / Town</label>
            <input class="input" name="pickup_city_town" maxlength="100" value="<?= e((string)($profile['pickup_city_town'] ?? '')) ?>">
        </div>
        <div class="field">
            <label>Postal Code</label>
            <input class="input" name="pickup_postal_code" maxlength="20" value="<?= e((string)($profile['pickup_postal_code'] ?? '')) ?>">
        </div>
        <div class="field">
            <label>Pickup District (read-only)</label>
            <input class="input" value="<?= e((string)($profile['district_name'] ?? 'Not set')) ?>" disabled>
            <small class="muted">Your pickup district drives Courier Partner route matching and the delivery fee. Contact Harvestly Admin to change it.</small>
        </div>
        <div class="field">
            <label>Verification Status (read-only)</label>
            <input class="input" value="<?= e((string)($profile['verification_status'] ?? 'PENDING')) ?>" disabled>
        </div>
    </div>
    <div class="row mt">
        <button class="btn" type="submit">Save Profile</button>
        <a class="btn secondary" href="dashboard.php">Cancel</a>
    </div>
</form>

<div class="card">
    <h2>Supporting Verification Documents</h2>
    <p class="muted">
        Documents you submitted at registration. Only Harvestly Admins can open them &mdash; they are
        never served as public files.
    </p>
    <?php if (!$documents): ?>
        <p class="empty">No verification documents on record.</p>
    <?php else: ?>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr><th>Type</th><th>File</th><th>Status</th><th>Reviewed</th></tr>
            </thead>
            <tbody>
            <?php foreach ($documents as $d): ?>
                <tr>
                    <td><?= e((string)$d['document_type']) ?></td>
                    <td><?= e((string)($d['original_file_name'] ?: 'Document')) ?></td>
                    <td><span class="status-badge <?= (string)$d['status'] === 'APPROVED' ? 'ok' : ((string)$d['status'] === 'REJECTED' ? 'bad' : 'warn') ?>"><?= e(farmer_status_label((string)$d['status'])) ?></span></td>
                    <td class="nowrap"><?= e((string)($d['reviewed_at'] ?: '—')) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?php page_bottom(); ?>
