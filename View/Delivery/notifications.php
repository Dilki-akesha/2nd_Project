<?php
require_once __DIR__ . '/../../Model/Delivery/Notification.php';

$userId = $_SESSION['user_id'] ?? 5;
$notifModel = new Notification();
$notifications = $notifModel->getByUser($userId);
$unreadCount = $notifModel->getUnreadCount($userId);
?>

<div class="page <?= ($page === 'notifications' ? 'active' : '') ?>" id="page-notifications">
    <div class="section-header">
        <div>
            <h3>Notifications</h3>
            <p style="font-size:13px;color:var(--md-on-surface-variant);margin-top:4px;">
                <span id="unreadCount"><?= $unreadCount ?></span> unread
            </p>
        </div>
        <button class="btn-outline" id="markAllReadBtn">
            <i class="fas fa-check-double"></i> Mark All as Read
        </button>
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
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="notifBody">
                    <?php if (empty($notifications)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 40px; color: var(--md-on-surface-variant);">
                                <i class="fas fa-bell-slash" style="font-size: 32px; display: block; margin-bottom: 12px;"></i>
                                No notifications yet.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($notifications as $n):
                            $isRead = $n['is_read'] == 1;
                        ?>
                            <tr data-notif-id="<?= $n['notification_id'] ?>" class="<?= $isRead ? '' : 'unread-row' ?>">
                                <td>
                                    <span class="status-badge transit">
                                        <?= htmlspecialchars($n['notification_type']) ?>
                                    </span>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($n['title']) ?></strong>
                                    <p style="font-size:12px;color:var(--md-on-surface-variant);margin:2px 0;">
                                        <?= htmlspecialchars($n['message']) ?>
                                    </p>
                                </td>
                                <td><?= htmlspecialchars($n['created_at']) ?></td>
                                <td>
                                    <span class="status-badge <?= $isRead ? 'completed' : 'pending' ?> notif-status">
                                        <?= $isRead ? 'Read' : 'Unread' ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($isRead): ?>
                                        <button class="btn-sm btn-outline" disabled>Read</button>
                                    <?php else: ?>
                                        <button class="btn-sm btn-outline mark-read-btn">Mark Read</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>