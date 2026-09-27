<?php
/**
 * Admin - Farmer Registration Approvals.
 * A Farmer account is created Pending and cannot open the Farmer dashboard
 * until an Admin approves it here.
 */
?>
<div class="view-container">
    <div class="page-header mb-4">
        <h1>Farmer Registration Approvals</h1>
        <p class="text-sm text-muted">Review Farmer applications and any supporting verification document before approving access.</p>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success mb-4"><?= sanitize($_GET['success']) ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger mb-4"><?= sanitize($_GET['error']) ?></div>
    <?php endif; ?>

    <div class="card" style="padding:0;overflow:hidden">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Farmer</th>
                        <th>Farm</th>
                        <th>Contact</th>
                        <th>Pickup District</th>
                        <th>Verification Document</th>
                        <th>Status</th>
                        <th style="text-align:right">Decision</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!$pendingFarmers): ?>
                    <tr>
                        <td colspan="7" style="text-align:center;padding:32px;color:var(--color-outline)">
                            No Farmer registrations are awaiting approval.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($pendingFarmers as $farmer): ?>
                    <tr>
                        <td>
                            <strong><?= sanitize($farmer['full_name']) ?></strong>
                            <div class="text-xs text-muted">
                                <?= !empty($farmer['created_at']) ? 'Applied ' . date('d M Y', strtotime((string)$farmer['created_at'])) : '' ?>
                            </div>
                        </td>
                        <td><?= sanitize($farmer['farm_name'] ?: 'Not provided') ?></td>
                        <td class="text-xs">
                            <div><?= sanitize($farmer['email']) ?></div>
                            <div class="text-muted"><?= sanitize($farmer['phone'] ?: '—') ?></div>
                        </td>
                        <td><?= sanitize($farmer['district'] ?: 'Not set') ?></td>
                        <td>
                            <?php if (!empty($farmer['document_id'])): ?>
                                <a class="badge badge-info" href="<?= e(url('Controller/Admin/DocumentController.php?id=' . (int)$farmer['document_id'])) ?>">
                                    <?= sanitize($farmer['original_file_name'] ?: 'Document') ?>
                                </a>
                            <?php else: ?>
                                <span class="text-xs text-muted">No document uploaded</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge badge-warning">
                                <?= sanitize(harvestlyStatusLabel((string)$farmer['status'])) ?>
                            </span>
                        </td>
                        <td style="text-align:right">
                            <div class="flex justify-end gap-2" style="display:flex;gap:8px;justify-content:flex-end;flex-wrap:wrap">
                                <form method="POST" action="index.php?admin_action=verify_farmer" style="display:inline">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="farmer_id" value="<?= (int)$farmer['id'] ?>">
                                    <input type="hidden" name="status" value="approved">
                                    <button type="submit" class="btn btn-sm btn-primary">Approve</button>
                                </form>
                                <form method="POST" action="index.php?admin_action=verify_farmer" style="display:inline"
                                      onsubmit="return confirm('Reject this Farmer application?');">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="farmer_id" value="<?= (int)$farmer['id'] ?>">
                                    <input type="hidden" name="status" value="rejected">
                                    <input type="text" name="rejection_reason" class="input-field" style="max-width:190px"
                                           placeholder="Rejection reason (optional)" maxlength="500">
                                    <button type="submit" class="btn btn-sm btn-danger">Reject</button>
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
            <strong>What approval does.</strong> Approving sets the Farmer's
            <code>verification_status</code> to <code>APPROVED</code> and the account status to
            <code>ACTIVE</code>, which is what allows the Farmer dashboard to open. Rejecting sets
            both to a rejected state so the account cannot sign in. Uploaded verification documents
            are only readable through the Admin-gated document controller.
        </p>
    </div>
</div>
