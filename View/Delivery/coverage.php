<?php
require_once __DIR__ . '/../../Model/Delivery/Database.php';
require_once __DIR__ . '/../../Model/Delivery/Coverage.php';

$courierId = $_SESSION['user_id'] ?? 5;

require_once __DIR__ . '/_districts.php';

$coverageModel = new Coverage();
$demoCoverageRoutes = $coverageModel->getAllByCourier($courierId);
?>

<div class="page <?= ($page === 'coverage' ? 'active' : '') ?>" id="page-coverage">

    <div class="section-header">
        <div>
            <h3>Manage Coverage Routes</h3>
            <p class="coverage-subtitle">Define district-to-district routes your organisation supports. Orders matching these routes will be offered to you automatically.</p>
        </div>
        <button class="btn-primary" id="addRouteBtn"><i class="fas fa-plus"></i> Add Route</button>
    </div>

    <div class="coverage-info-banner">
        <i class="fas fa-info-circle"></i>
        <div>
            <strong>Directional Routes</strong>
            <p>Colombo → Kandy does NOT automatically cover Kandy → Colombo. Add each direction separately.</p>
        </div>
    </div>

    <div class="coverage-filters">
        <input type="text" id="routeSearch" placeholder="Search district..." />
        <select id="routeStatusFilter">
            <option value="all">All Statuses</option>
            <option value="active">Active Only</option>
            <option value="inactive">Inactive Only</option>
        </select>
    </div>

    <div class="table-wrap">
        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th style="width:80px;">Route ID</th>
                        <th>Origin District</th>
                        <th style="width:40px;"></th>
                        <th>Destination District</th>
                        <th>Status</th>
                        <th>Added On</th>
                        <th style="width:320px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="routesTableBody">
                    <?php if (empty($demoCoverageRoutes)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 40px; color: var(--md-on-surface-variant);">
                                <i class="fas fa-route" style="font-size: 32px; display: block; margin-bottom: 12px;"></i>
                                No coverage routes yet. Click "Add Route" to get started.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($demoCoverageRoutes as $route):
                            $a = $route['is_active'] == 1;
                        ?>
                            <tr data-route-id="<?= $route['route_id'] ?>"
                                data-origin-id="<?= $route['origin_id'] ?>"
                                data-origin="<?= htmlspecialchars($route['origin_name']) ?>"
                                data-destination-id="<?= $route['destination_id'] ?>"
                                data-destination="<?= htmlspecialchars($route['destination_name']) ?>"
                                data-status="<?= $a ? 'active' : 'inactive' ?>">
                                <td><strong>#RT-<?= str_pad($route['route_id'], 4, '0', STR_PAD_LEFT) ?></strong></td>
                                <td><?= htmlspecialchars($route['origin_name']) ?></td>
                                <td class="route-arrow"><i class="fas fa-arrow-right"></i></td>
                                <td><?= htmlspecialchars($route['destination_name']) ?></td>
                                <td><span class="status-badge <?= $a ? 'completed' : 'pending' ?> route-status-badge"><?= $a ? 'Active' : 'Inactive' ?></span></td>
                                <td><?= htmlspecialchars($route['created_date']) ?></td>
                                <td>
                                    <button class="btn-sm btn-outline edit-route-btn"><i class="fas fa-edit"></i> Edit</button>
                                    <button class="btn-sm <?= $a ? 'btn-warning' : 'btn-accept' ?> toggle-route-btn">
                                        <i class="fas <?= $a ? 'fa-pause' : 'fa-play' ?>"></i> <?= $a ? 'Deactivate' : 'Reactivate' ?>
                                    </button>
                                    <button class="btn-sm btn-reject delete-route-btn">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="table-footer">
            <span id="routeCount"><?= count($demoCoverageRoutes) ?> routes total</span>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- ADD / EDIT ROUTE MODAL — CLEAN VERSION (no duplicates) -->
<!-- ============================================================ -->
<div id="routeModal" class="route-modal">
    <div class="route-modal-overlay" onclick="closeRouteModal()"></div>
    <div class="route-modal-box">
        <div class="route-modal-header">
            <h3 id="routeModalTitle"><i class="fas fa-route"></i> Add Coverage Route</h3>
            <button class="route-modal-close" onclick="closeRouteModal()">&times;</button>
        </div>
        <form id="routeForm" onsubmit="return false;">
            <input type="hidden" id="routeEditId" value="" />
            <div class="route-modal-body">

                <div class="form-row">
                    <div class="form-group">
                        <label for="routeOrigin">Origin District <span style="color:#ba1a1a;">*</span></label>
                        <select id="routeOrigin" required>
                            <option value="">Select origin district</option>
                            <?php foreach ($sriLankanDistricts as $id => $d): ?>
                                <option value="<?= $id ?>" data-name="<?= htmlspecialchars($d['name']) ?>"><?= htmlspecialchars($d['name']) ?> (<?= htmlspecialchars($d['province']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="routeDestination">Destination District <span style="color:#ba1a1a;">*</span></label>
                        <select id="routeDestination" required>
                            <option value="">Select destination district</option>
                            <?php foreach ($sriLankanDistricts as $id => $d): ?>
                                <option value="<?= $id ?>" data-name="<?= htmlspecialchars($d['name']) ?>"><?= htmlspecialchars($d['name']) ?> (<?= htmlspecialchars($d['province']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="route-direction-preview">
                    <span id="previewOrigin">Origin</span>
                    <i class="fas fa-arrow-right"></i>
                    <span id="previewDestination">Destination</span>
                </div>

                <div class="form-group">
                    <label for="routeStatus">Status</label>
                    <select id="routeStatus">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <div id="routeFormError" class="route-form-error" style="display:none;"></div>
            </div>
            <div class="route-modal-footer">
                <button type="button" class="btn-secondary" onclick="closeRouteModal()">Cancel</button>
                <button type="button" class="btn-primary" id="saveRouteBtn">
                    <i class="fas fa-save"></i> <span id="saveRouteBtnText">Save Route</span>
                </button>
            </div>
        </form>
    </div>
</div>

<div id="routeToast" class="route-toast">
    <i class="fas fa-check-circle"></i>
    <span id="routeToastText">Saved</span>
</div>