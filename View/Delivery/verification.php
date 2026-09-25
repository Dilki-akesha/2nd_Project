<?php
require_once __DIR__ . '/../../Model/Delivery/Verification.php';

$userId = $_SESSION['user_id'] ?? 5;
$verification = new Verification();
$orgInfo = $verification->getOrgInfo($userId);
$documents = $verification->getDocuments($userId);

$status = $orgInfo['verification_status'] ?? 'PENDING';
$statusClass = $status === 'APPROVED' ? 'completed' : ($status === 'REJECTED' ? 'pending' : 'progress');
$statusText = $status === 'APPROVED' ? 'Approved' : ($status === 'REJECTED' ? 'Rejected' : 'Pending');
?>

<div class="page <?= ($page === 'verification' ? 'active' : '') ?>" id="page-verification">
    <div class="section-header">
        <h3>Verification</h3>
        <span class="status-badge <?= $statusClass ?>">
            <i class="fas fa-certificate"></i> <?= $statusText ?>
        </span>
    </div>

    <div class="table-wrap" style="padding: 28px; max-width: 800px;">
        <h4 style="margin-bottom: 20px;">
            <i class="fas fa-building" style="color: var(--md-primary);"></i>
            Organisation Information
        </h4>

        <div class="form-row">
            <div class="form-group">
                <label>Organisation Name</label>
                <input type="text" value="<?= htmlspecialchars($orgInfo['organisation_name'] ?? '—') ?>" readonly />
            </div>
            <div class="form-group">
                <label>Contact Person</label>
                <input type="text" value="<?= htmlspecialchars($orgInfo['contact_person_name'] ?? '—') ?>" readonly />
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Email</label>
                <input type="email" value="<?= htmlspecialchars($orgInfo['email'] ?? '—') ?>" readonly />
            </div>
            <div class="form-group">
                <label>Contact Number</label>
                <input type="text" value="<?= htmlspecialchars($orgInfo['phone'] ?? '—') ?>" readonly />
            </div>
        </div>

        <div class="form-group">
            <label>Organisation Address</label>
            <input type="text" value="<?= htmlspecialchars(($orgInfo['office_address_line1'] ?? '') . ', ' . ($orgInfo['office_city_town'] ?? '') . ', ' . ($orgInfo['office_district'] ?? '')) ?>" readonly />
        </div>

        <hr />

        <h4 style="margin-bottom: 16px;">
            <i class="fas fa-file-alt" style="color: var(--md-primary);"></i>
            Supporting Verification Documents
        </h4>

        <?php if (empty($documents)): ?>
            <div style="text-align: center; padding: 32px; color: var(--md-on-surface-variant); background: var(--md-surface-container-low); border-radius: 8px;">
                <i class="fas fa-folder-open" style="font-size: 32px; display: block; margin-bottom: 12px;"></i>
                No verification documents uploaded yet.
            </div>
        <?php else: ?>
            <div class="table-wrap" style="box-shadow: none; border: 1px solid var(--md-outline-variant);">
                <table>
                    <thead>
                        <tr>
                            <th>Document Type</th>
                            <th>File Name</th>
                            <th>Uploaded</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($documents as $doc):
                            $dStatus = $doc['status'];
                            $dClass = $dStatus === 'APPROVED' ? 'completed' : ($dStatus === 'REJECTED' ? 'pending' : 'progress');
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($doc['document_type']) ?></td>
                                <td><?= htmlspecialchars($doc['original_file_name'] ?? '—') ?></td>
                                <td><?= htmlspecialchars($doc['uploaded_date']) ?></td>
                                <td><span class="status-badge <?= $dClass ?>"><?= ucfirst(strtolower($dStatus)) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <div class="coverage-info-banner" style="margin-top: 20px;">
            <i class="fas fa-info-circle"></i>
            <div>
                <strong>Verification Note</strong>
                <p>Admin reviews submitted documents. Verification status updates automatically once approved or rejected. Contact support for any questions.</p>
            </div>
        </div>
    </div>
</div>