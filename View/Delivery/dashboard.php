<?php
require_once __DIR__ . '/../../Model/Delivery/Notification.php';

$userId = $_SESSION['user_id'] ?? 5;
$notifModel = new Notification();
$recentNotifications = array_slice($notifModel->getByUser($userId), 0, 3);
?>

<!-- Stats Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <span class="stat-icon blue"><i class="fas fa-clipboard-list"></i></span>
        <div class="stat-value">4</div>
        <div class="stat-label">New Assignment Offers</div>
    </div>
    <div class="stat-card">
        <span class="stat-icon green"><i class="fas fa-clock"></i></span>
        <div class="stat-value">8</div>
        <div class="stat-label">Active Deliveries</div>
    </div>
    <div class="stat-card">
        <span class="stat-icon orange"><i class="fas fa-check-circle"></i></span>
        <div class="stat-value">47</div>
        <div class="stat-label">Completed Deliveries</div>
    </div>
    <div class="stat-card">
        <span class="stat-icon orange"><i class="fas fa-dollar-sign"></i></span>
        <div class="stat-value">LKR 12,450</div>
        <div class="stat-label">Pending Earnings</div>
    </div>
    <div class="stat-card">
        <span class="stat-icon purple"><i class="fas fa-bell"></i></span>
        <div class="stat-value">3</div>
        <div class="stat-label">Unread Notifications</div>
    </div>
    <div class="stat-card">
        <span class="stat-icon purple"><i class="fas fa-star"></i></span>
        <div class="stat-value">4.8 ★</div>
        <div class="stat-label">Rating</div>
    </div>
</div>

<!-- Availability Toggle -->
<div class="availability-toggle-wrap">
    <span class="toggle-label">Availability</span>
    <div class="toggle-switch" id="availabilityToggle">
        <input type="checkbox" id="availCheckbox" checked />
        <label for="availCheckbox" class="slider"></label>
    </div>
    <span class="toggle-status" id="availStatus">Available</span>
</div>

<!-- Recent Delivery Requests -->
<div class="section-header">
    <h3>Recent Delivery Requests</h3>
    <a href="?page=requests" class="btn-outline">
        View All <i class="fas fa-arrow-right"></i>
    </a>
</div>
<div class="table-wrap">
    <div class="table-scroll">
        <table>
            <thead>
                <tr><th>ID</th><th>Pickup</th><th>Delivery</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>#HV-9982</strong></td>
                    <td>Sunny Valley Farm</td>
                    <td>Metropolis Market</td>
                    <td><button class="btn-sm btn-accept">Accept</button></td>
                </tr>
                <tr>
                    <td><strong>#HV-9985</strong></td>
                    <td>Oakwood Dairy</td>
                    <td>Green Grove Dist.</td>
                    <td><button class="btn-sm btn-accept">Accept</button></td>
                </tr>
                <tr>
                    <td><strong>#HV-9989</strong></td>
                    <td>Riverbend Orchards</td>
                    <td>Downtown Food Co.</td>
                    <td><button class="btn-sm btn-accept">Accept</button></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Recent Assigned Deliveries -->
<div class="section-header">
    <h3>Recent Assigned Deliveries</h3>
    <a href="?page=assigned" class="btn-outline">View All</a>
</div>
<div class="table-wrap">
    <div class="table-scroll">
        <table>
            <thead>
                <tr><th>Order ID</th><th>Status</th><th>ETA</th><th>Track Map</th></tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>#HV-9712</strong></td>
                    <td><span class="status-badge transit">IN TRANSIT</span></td>
                    <td>25 min</td>
                    <td><button class="btn-sm btn-primary" onclick="window.location.href='?page=tracking'"><i class="fas fa-map"></i> Track</button></td>
                </tr>
                <tr>
                    <td><strong>#HV-9715</strong></td>
                    <td><span class="status-badge picked">PICKED UP</span></td>
                    <td>15 min</td>
                    <td><button class="btn-sm btn-primary" onclick="window.location.href='?page=tracking'"><i class="fas fa-map"></i> Track</button></td>
                </tr>
                <tr>
                    <td><strong>#HV-9721</strong></td>
                    <td><span class="status-badge transit">IN TRANSIT</span></td>
                    <td>40 min</td>
                    <td><button class="btn-sm btn-primary" onclick="window.location.href='?page=tracking'"><i class="fas fa-map"></i> Track</button></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Bonus Banner -->
<div class="bonus-banner">
    <div>
        <h4><i class="fas fa-star"></i> Earn extra bonuses this weekend!</h4>
        <p>Complete 10 more deliveries to unlock the $150 Partner Reward.</p>
    </div>
    <button class="btn-white">View Performance</button>
</div>

<!-- Recent Notifications (from DB) -->
<div class="section-header" style="margin-top:24px;">
    <h3>Recent Notifications</h3>
    <a href="?page=notifications" class="btn-outline">
        View All <i class="fas fa-arrow-right"></i>
    </a>
</div>
<div class="table-wrap">
    <div class="table-scroll">
        <table>
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Title &amp; Message</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recentNotifications)): ?>
                    <tr>
                        <td colspan="4" style="text-align:center; padding:24px; color:var(--md-on-surface-variant);">
                            No notifications yet.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($recentNotifications as $n):
                        $isRead = $n['is_read'] == 1;
                    ?>
                        <tr>
                            <td>
                                <span class="status-badge transit" style="font-size:10px;">
                                    <?= htmlspecialchars($n['notification_type']) ?>
                                </span>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($n['title']) ?></strong>
                                <p style="font-size:12px;color:var(--md-on-surface-variant);margin:2px 0;">
                                    <?= htmlspecialchars(substr($n['message'], 0, 80)) ?>...
                                </p>
                            </td>
                            <td style="font-size:12px;"><?= htmlspecialchars($n['created_at']) ?></td>
                            <td>
                                <span class="status-badge <?= $isRead ? 'completed' : 'pending' ?>">
                                    <?= $isRead ? 'Read' : 'Unread' ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>