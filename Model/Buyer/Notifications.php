<?php

declare(strict_types=1);

/**
 * Harvestly Buyer notifications.
 * Simple database notifications only - no push, WebSocket, SMS or email service.
 */
final class Notifications
{
    private const PER_PAGE = 25;

    private function map(array $row): array
    {
        $typeKey = strtolower(str_replace('_', ' ', trim((string)($row['notification_type'] ?? 'System'))));

        $type = match (true) {
            str_contains($typeKey, 'assignment') => 'Delivery',
            str_contains($typeKey, 'order') => 'Orders',
            str_contains($typeKey, 'deliver') => 'Delivery',
            str_contains($typeKey, 'payment') => 'Payments',
            str_contains($typeKey, 'complaint'), str_contains($typeKey, 'issue') => 'Issues',
            str_contains($typeKey, 'review') => 'Reviews',
            str_contains($typeKey, 'verif'), str_contains($typeKey, 'account') => 'Account',
            default => 'System',
        };

        $iconByType = [
            'Orders' => 'shopping_bag',
            'Delivery' => 'local_shipping',
            'Payments' => 'payments',
            'Issues' => 'report_problem',
            'Reviews' => 'rate_review',
            'Account' => 'verified_user',
            'System' => 'info',
        ];

        $actionByType = [
            'Orders' => 'View Orders',
            'Delivery' => 'View Orders',
            'Payments' => 'View Orders',
            'Issues' => 'View Issues',
            'Reviews' => 'View Reviews',
            'Account' => 'View Profile',
            'System' => 'Browse Products',
        ];

        $urlByType = [
            'Orders' => buyerRoute('OrdersController.php'),
            'Delivery' => buyerRoute('OrdersController.php'),
            'Payments' => buyerRoute('OrdersController.php'),
            'Issues' => buyerRoute('FeedbackController.php'),
            'Reviews' => buyerRoute('FeedbackController.php'),
            'Account' => buyerRoute('ProfileController.php'),
            'System' => buyerRoute('ProductController.php'),
        ];

        $row['id'] = (int)($row['notification_id'] ?? 0);
        $row['type'] = $type;
        $row['unread'] = !(bool)($row['is_read'] ?? 0);
        $row['icon'] = $iconByType[$type] ?? 'notifications';
        $row['action'] = $actionByType[$type] ?? 'View';
        $row['action_url'] = $urlByType[$type] ?? buyerRoute('DashboardController.php');

        // When a notification refers to one of this Buyer's orders, link straight
        // to that order's details page.
        if (!empty($row['related_order_id'])) {
            $publicId = orderPublicId((int)$row['related_order_id']);
            if (in_array($type, ['Orders', 'Delivery', 'Payments'], true)) {
                $row['action'] = 'View Order';
                $row['action_url'] = buyerRoute('OrderTrackingController.php', 'id=' . urlencode($publicId));
            }
        }

        $row['time'] = !empty($row['created_at'])
            ? date('d M Y, H:i', strtotime((string)$row['created_at']))
            : 'Just now';

        return $row;
    }

    public function getAll(string $filter = 'all', int $page = 1): array
    {
        requireBuyerAuth();
        $conditions = 'user_id = ?';
        $types = 'i';
        $params = [currentBuyerId()];

        if ($filter === 'unread') {
            $conditions .= ' AND is_read = 0';
        } elseif ($filter === 'read') {
            $conditions .= ' AND is_read = 1';
        }

        $total = (int)db_scalar("SELECT COUNT(*) FROM notifications WHERE $conditions", $types, $params, 0);

        $page = max(1, $page);
        $offset = ($page - 1) * self::PER_PAGE;
        $rows = db_fetch_all(
            "SELECT * FROM notifications
             WHERE $conditions
             ORDER BY created_at DESC, notification_id DESC
             LIMIT ? OFFSET ?",
            $types . 'ii',
            array_merge($params, [self::PER_PAGE, $offset])
        );

        $unreadCount = (int)db_scalar(
            'SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0',
            'i',
            [currentBuyerId()],
            0
        );

        return [
            'items' => array_map(fn(array $row) => $this->map($row), $rows),
            'total' => $total,
            'unread' => $unreadCount,
            'page' => $page,
            'pages' => max(1, (int)ceil($total / self::PER_PAGE)),
            'filter' => in_array($filter, ['all', 'unread', 'read'], true) ? $filter : 'all',
        ];
    }

    public function markRead(int $id): bool
    {
        return db_execute(
            'UPDATE notifications SET is_read = 1, read_at = NOW()
             WHERE notification_id = ? AND user_id = ? AND is_read = 0',
            'ii',
            [$id, currentBuyerId()]
        );
    }

    public function markAllRead(): bool
    {
        return db_execute(
            'UPDATE notifications SET is_read = 1, read_at = NOW()
             WHERE user_id = ? AND is_read = 0',
            'i',
            [currentBuyerId()]
        );
    }
}
