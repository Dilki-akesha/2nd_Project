<?php
/**
 * Admin - User Management.
 * users.account_status is an ENUM of PENDING, ACTIVE, REJECTED, SUSPENDED.
 */
$currentRole = (string)($_GET['role'] ?? 'all');
$currentStatus = (string)($_GET['status'] ?? 'all');

$statusTone = [
    'ACTIVE'    => 'badge-success',
    'PENDING'   => 'badge-pending',
    'SUSPENDED' => 'badge-warning',
    'REJECTED'  => 'badge-danger',
];
$roleLabel = [
    'BUYER' => 'Buyer',
    'FARMER' => 'Farmer',
    'COURIER_PARTNER' => 'Courier Partner',
    'ADMIN' => 'Admin',
];
?>
<div class="page-content">
    <div class="section-header">
        <div class="section-title-group">
            <h1>User Management</h1>
            <p>View, create, edit and manage the account status of Buyers, Farmers and Courier Partner organisations.</p>
        </div>
        <div>
            <button type="button" class="btn btn-primary" data-modal-target="modal-create-user">
                + Create New User
            </button>
        </div>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success"><?= sanitize($_GET['success']) ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger"><?= sanitize($_GET['error']) ?></div>
    <?php endif; ?>

    <div class="card-table-wrapper">
        <div class="table-toolbar">
            <div class="search-box">
                <input type="text" placeholder="Search name, email or phone" data-table-search="users-table">
            </div>

            <form action="index.php" method="GET" class="filter-group">
                <input type="hidden" name="page" value="admin_users">
                <select name="role" class="filter-select" onchange="this.form.submit()" aria-label="Filter by role">
                    <option value="all" <?= $currentRole === 'all' ? 'selected' : '' ?>>All Roles</option>
                    <option value="buyer" <?= $currentRole === 'buyer' ? 'selected' : '' ?>>Buyer</option>
                    <option value="farmer" <?= $currentRole === 'farmer' ? 'selected' : '' ?>>Farmer</option>
                    <option value="courier_partner" <?= $currentRole === 'courier_partner' ? 'selected' : '' ?>>Courier Partner</option>
                    <option value="admin" <?= $currentRole === 'admin' ? 'selected' : '' ?>>Admin</option>
                </select>

                <select name="status" class="filter-select" onchange="this.form.submit()" aria-label="Filter by status">
                    <option value="all" <?= $currentStatus === 'all' ? 'selected' : '' ?>>All Statuses</option>
                    <option value="ACTIVE" <?= $currentStatus === 'ACTIVE' ? 'selected' : '' ?>>Active</option>
                    <option value="PENDING" <?= $currentStatus === 'PENDING' ? 'selected' : '' ?>>Pending</option>
                    <option value="SUSPENDED" <?= $currentStatus === 'SUSPENDED' ? 'selected' : '' ?>>Suspended</option>
                    <option value="REJECTED" <?= $currentStatus === 'REJECTED' ? 'selected' : '' ?>>Rejected</option>
                </select>
            </form>
        </div>

        <table class="data-table" id="users-table">
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Name / Organisation</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Role</th>
                    <th>District</th>
                    <th>Status</th>
                    <th>Registered</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$users): ?>
                <tr>
                    <td colspan="9" style="text-align:center;padding:32px;color:var(--color-outline)">
                        No users found matching the filters.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($users as $u):
                    $uid = (int)$u['id'];
                    $role = strtoupper((string)$u['role']);
                    $status = strtoupper((string)$u['status']);
                ?>
                <tr>
                    <td>#USR-<?= sprintf('%04d', $uid) ?></td>
                    <td>
                        <strong><?= sanitize($u['name']) ?></strong>
                        <?php if (!empty($u['contact_person'])): ?>
                            <div class="text-xs text-muted">Contact: <?= sanitize($u['contact_person']) ?></div>
                        <?php endif; ?>
                    </td>
                    <td><?= sanitize($u['email']) ?></td>
                    <td class="nowrap"><?= sanitize($u['phone'] ?: '—') ?></td>
                    <td><span class="badge badge-info"><?= e($roleLabel[$role] ?? $role) ?></span></td>
                    <td><?= sanitize($u['district'] ?: 'Not set') ?></td>
                    <td>
                        <span class="badge <?= $statusTone[$status] ?? 'badge-pending' ?>">
                            <?= sanitize(harvestlyStatusLabel($status)) ?>
                        </span>
                    </td>
                    <td class="nowrap text-xs">
                        <?= !empty($u['created_at']) ? date('d M Y', strtotime((string)$u['created_at'])) : '—' ?>
                    </td>
                    <td>
                        <div style="display:flex;gap:6px;flex-wrap:wrap">
                            <button type="button" class="btn btn-outline btn-sm" data-modal-target="modal-view-<?= $uid ?>">View</button>
                            <button type="button" class="btn btn-outline btn-sm" data-modal-target="modal-edit-<?= $uid ?>">Edit</button>

                            <?php if ($role !== 'ADMIN'): ?>
                                <?php if ($status === 'ACTIVE'): ?>
                                <form action="index.php?admin_action=update_user_status" method="POST" style="display:inline">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="user_type" value="<?= e(strtolower($role)) ?>">
                                    <input type="hidden" name="user_id" value="<?= $uid ?>">
                                    <input type="hidden" name="status" value="SUSPENDED">
                                    <button type="submit" class="btn btn-outline btn-sm">Suspend</button>
                                </form>
                                <?php else: ?>
                                <form action="index.php?admin_action=update_user_status" method="POST" style="display:inline">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="user_type" value="<?= e(strtolower($role)) ?>">
                                    <input type="hidden" name="user_id" value="<?= $uid ?>">
                                    <input type="hidden" name="status" value="ACTIVE">
                                    <button type="submit" class="btn btn-primary btn-sm">Activate</button>
                                </form>
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php if ($role !== 'ADMIN'): ?>
                            <button type="button" class="btn btn-danger btn-sm" data-modal-target="modal-delete-<?= $uid ?>">Delete</button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($users): ?>
<?php foreach ($users as $u):
    $uid = (int)$u['id'];
    $role = strtoupper((string)$u['role']);
?>
<!-- View user -->
<div class="modal-backdrop" id="modal-view-<?= $uid ?>">
    <div class="modal-card">
        <div class="modal-header">
            <div class="modal-title">User Details &mdash; #USR-<?= sprintf('%04d', $uid) ?></div>
            <button type="button" class="modal-close-btn" data-modal-close>&times;</button>
        </div>
        <div class="modal-body" style="font-size:14px;display:grid;gap:8px">
            <div><strong>Name / Organisation:</strong> <?= sanitize($u['name']) ?></div>
            <div><strong>Email:</strong> <?= sanitize($u['email']) ?></div>
            <div><strong>Phone:</strong> <?= sanitize($u['phone'] ?: '—') ?></div>
            <div><strong>Role:</strong> <?= e($roleLabel[$role] ?? $role) ?></div>
            <?php if (!empty($u['contact_person'])): ?>
            <div><strong>Contact Person:</strong> <?= sanitize($u['contact_person']) ?></div>
            <?php endif; ?>
            <div><strong>District:</strong> <?= sanitize($u['district'] ?: 'Not set') ?></div>
            <div><strong>Address:</strong> <?= sanitize($u['detail'] ?: 'Not provided') ?></div>
            <div>
                <strong>Account Status:</strong>
                <span class="badge <?= $statusTone[strtoupper((string)$u['status'])] ?? 'badge-pending' ?>">
                    <?= sanitize(harvestlyStatusLabel((string)$u['status'])) ?>
                </span>
            </div>
            <?php if (!empty($u['document_id'])): ?>
            <div>
                <strong>Verification Attachment:</strong>
                <a href="<?= e(url('Controller/Admin/DocumentController.php?id=' . (int)$u['document_id'])) ?>"
                   style="color:var(--color-primary);font-weight:700">
                    Download (Admin only)
                </a>
            </div>
            <?php endif; ?>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline btn-sm" data-modal-close>Close</button>
        </div>
    </div>
</div>

<!-- Edit user -->
<div class="modal-backdrop" id="modal-edit-<?= $uid ?>">
    <div class="modal-card">
        <div class="modal-header">
            <div class="modal-title">Edit User &mdash; #USR-<?= sprintf('%04d', $uid) ?></div>
            <button type="button" class="modal-close-btn" data-modal-close>&times;</button>
        </div>
        <form action="index.php?admin_action=update_user_details" method="POST">
            <?= csrfField() ?>
            <div class="modal-body" style="display:grid;gap:12px">
                <input type="hidden" name="user_id" value="<?= $uid ?>">
                <input type="hidden" name="role" value="<?= e(strtolower($role)) ?>">

                <div class="form-group">
                    <label class="form-label" for="e-name-<?= $uid ?>">
                        <?= $role === 'COURIER_PARTNER' ? 'Organisation Name' : 'Full Name' ?>
                    </label>
                    <input type="text" id="e-name-<?= $uid ?>" name="name" class="form-control" maxlength="160" required
                           value="<?= sanitize($u['name']) ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="e-email-<?= $uid ?>">Email</label>
                    <input type="email" id="e-email-<?= $uid ?>" name="email" class="form-control" required
                           value="<?= sanitize($u['email']) ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="e-phone-<?= $uid ?>">Phone</label>
                    <input type="text" id="e-phone-<?= $uid ?>" name="phone" class="form-control" maxlength="25"
                           value="<?= sanitize($u['phone']) ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="e-dist-<?= $uid ?>">District</label>
                    <select id="e-dist-<?= $uid ?>" name="district" class="form-control" required>
                        <?php foreach (($districts ?? []) as $d): ?>
                        <option value="<?= sanitize($d['district_name']) ?>"
                            <?= ($u['district'] ?? '') === $d['district_name'] ? 'selected' : '' ?>>
                            <?= sanitize($d['district_name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="e-addr-<?= $uid ?>">Address</label>
                    <textarea id="e-addr-<?= $uid ?>" name="address" class="form-control" rows="2"><?= sanitize($u['detail'] ?? '') ?></textarea>
                </div>

                <?php if ($role === 'COURIER_PARTNER'): ?>
                <div class="form-group">
                    <label class="form-label" for="e-contact-<?= $uid ?>">Contact Person</label>
                    <input type="text" id="e-contact-<?= $uid ?>" name="contact_person" class="form-control" maxlength="120"
                           value="<?= sanitize($u['contact_person'] ?? '') ?>">
                </div>
                <?php endif; ?>

                <div class="form-group">
                    <label class="form-label" for="e-status-<?= $uid ?>">Account Status</label>
                    <select id="e-status-<?= $uid ?>" name="status" class="form-control" required>
                        <?php foreach (['ACTIVE', 'PENDING', 'SUSPENDED', 'REJECTED'] as $s): ?>
                        <option value="<?= $s ?>" <?= strtoupper((string)$u['status']) === $s ? 'selected' : '' ?>>
                            <?= e(harvestlyStatusLabel($s)) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline btn-sm" data-modal-close>Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete user -->
<?php if ($role !== 'ADMIN'): ?>
<div class="modal-backdrop" id="modal-delete-<?= $uid ?>">
    <div class="modal-card">
        <div class="modal-header">
            <div class="modal-title" style="color:var(--color-error)">Confirm Delete User</div>
            <button type="button" class="modal-close-btn" data-modal-close>&times;</button>
        </div>
        <form action="index.php?admin_action=delete_user" method="POST">
            <?= csrfField() ?>
            <div class="modal-body">
                <input type="hidden" name="role" value="<?= e(strtolower($role)) ?>">
                <input type="hidden" name="user_id" value="<?= $uid ?>">
                <p style="font-size:14px">
                    Permanently delete <strong><?= sanitize($u['name']) ?></strong> (<?= sanitize($u['email']) ?>)?
                </p>
                <p class="text-xs" style="color:var(--color-error);margin-top:10px">
                    A user who has orders, product listings, deliveries, reviews, complaints, earnings
                    or coverage routes cannot be deleted &mdash; suspend that account instead so the
                    history stays intact.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline btn-sm" data-modal-close>Cancel</button>
                <button type="submit" class="btn btn-danger btn-sm">Delete User</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>
<?php endforeach; ?>
<?php endif; ?>

<!-- Create new user -->
<div class="modal-backdrop" id="modal-create-user">
    <div class="modal-card" style="max-width:560px">
        <div class="modal-header">
            <div class="modal-title">Create New User Account</div>
            <button type="button" class="modal-close-btn" data-modal-close>&times;</button>
        </div>
        <form action="index.php?admin_action=create_user" method="POST">
            <?= csrfField() ?>
            <div class="modal-body" style="display:grid;gap:12px">
                <div class="form-group">
                    <label class="form-label" for="c-role">Role</label>
                    <select id="c-role" name="role" class="form-control" required onchange="toggleRoleFields(this.value)">
                        <option value="buyer">Buyer</option>
                        <option value="farmer">Farmer</option>
                        <option value="courier">Courier Partner (Organisation)</option>
                    </select>
                    <small class="muted">Admin accounts are never created from this screen.</small>
                </div>

                <div class="form-group">
                    <label class="form-label" for="c-name" id="c-name-label">Full Name</label>
                    <input type="text" id="c-name" name="name" class="form-control" maxlength="160" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="c-email">Email Address</label>
                    <input type="email" id="c-email" name="email" class="form-control" autocomplete="off" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="c-pass">Initial Password</label>
                    <input type="password" id="c-pass" name="password" class="form-control" minlength="8" autocomplete="new-password" required>
                    <small class="muted">At least 8 characters. The user should change it after signing in.</small>
                </div>

                <div class="form-group">
                    <label class="form-label" for="c-phone">Phone Number</label>
                    <input type="tel" id="c-phone" name="phone" class="form-control" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="c-dist">District</label>
                    <select id="c-dist" name="district" class="form-control" required>
                        <option value="">Select a district</option>
                        <?php foreach (($districts ?? []) as $d): ?>
                        <option value="<?= sanitize($d['district_name']) ?>"><?= sanitize($d['district_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="c-addr">Address</label>
                    <textarea id="c-addr" name="address" class="form-control" rows="2" required></textarea>
                </div>

                <div id="role-extra-courier" style="display:none">
                    <div class="form-group">
                        <label class="form-label" for="c-contact-p">Contact Person</label>
                        <input type="text" id="c-contact-p" name="contact_person" class="form-control" maxlength="120">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="c-status">Account Status</label>
                    <select id="c-status" name="status" class="form-control" required>
                        <option value="ACTIVE">Active</option>
                        <option value="PENDING">Pending</option>
                        <option value="SUSPENDED">Suspended</option>
                    </select>
                    <small class="muted">Farmer and Courier Partner accounts are normally created as Pending and approved later.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline btn-sm" data-modal-close>Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm">Create User</button>
            </div>
        </form>
    </div>
</div>

<script>
/* Show the Courier-only contact field and relabel the name field. */
function toggleRoleFields(role) {
    var courierBox = document.getElementById('role-extra-courier');
    var nameLabel = document.getElementById('c-name-label');
    courierBox.style.display = role === 'courier' ? 'block' : 'none';
    nameLabel.textContent = role === 'courier' ? 'Organisation Name' : 'Full Name';
}
</script>
