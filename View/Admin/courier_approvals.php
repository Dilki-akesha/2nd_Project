<?php
/**
 * Admin - Courier Partner Registration Approvals.
 * A Courier Partner is an organisation. The account is created Pending and
 * cannot open the Courier dashboard until an Admin approves it here.
 */
?>
<div class="view-container">
    <div class="page-header mb-4">
        <h1>Courier Partner Registration Approvals</h1>
        <p class="text-sm text-muted">Review delivery organisations and their supporting verification documents before approving access.</p>
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
                        <th>Organisation</th>
                        <th>Contact Person</th>
                        <th>Contact</th>
                        <th>Office District</th>
                        <th>Verification Document</th>
                        <th>Status</th>
                        <th style="text-align:right">Decision</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!$pendingCouriers): ?>
                    <tr>
                        <td colspan="7" style="text-align:center;padding:32px;color:var(--color-outline)">
                            No Courier Partner registrations are awaiting approval.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($pendingCouriers as $courier): ?>
                    <tr>
                        <td>
                            <strong><?= sanitize($courier['company_name']) ?></strong>
                            <div class="text-xs text-muted">
                                <?= !empty($courier['created_at']) ? 'Applied ' . date('d M Y', strtotime((string)$courier['created_at'])) : '' ?>
                            </div>
                        </td>
                        <td><?= sanitize($courier['contact_person'] ?: 'Not provided') ?></td>
                        <td class="text-xs">
                            <div><?= sanitize($courier['email']) ?></div>
                            <div class="text-muted"><?= sanitize($courier['phone'] ?: '—') ?></div>
                        </td>
                        <td><?= sanitize($courier['district'] ?: 'Not set') ?></td>
                        <td>
                            <?php if (!empty($courier['document_id'])): ?>
                                <a class="badge badge-info" href="<?= e(url('Controller/Admin/DocumentController.php?id=' . (int)$courier['document_id'])) ?>">
                                    <?= sanitize($courier['original_file_name'] ?: 'Document') ?>
                                </a>
                            <?php else: ?>
                                <span class="text-xs text-muted">No document uploaded</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge badge-warning">
                                <?= sanitize(harvestlyStatusLabel((string)$courier['status'])) ?>
                            </span>
                        </td>
                        <td style="text-align:right">
                            <div style="display:flex;gap:8px;justify-content:flex-end;flex-wrap:wrap">
                                <form method="POST" action="index.php?admin_action=verify_courier" style="display:inline">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="courier_id" value="<?= (int)$courier['id'] ?>">
                                    <input type="hidden" name="status" value="approved">
                                    <button type="submit" class="btn btn-sm btn-primary">Approve</button>
                                </form>
                                <form method="POST" action="index.php?admin_action=verify_courier" style="display:inline"
                                      onsubmit="return confirm('Reject this Courier Partner organisation?');">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="courier_id" value="<?= (int)$courier['id'] ?>">
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

    </div>
</div>
