<?php
/**
 * Harvestly shared order / delivery workflow helpers.
 * Core PHP + MySQLi only. No external services.
 */
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/status.php';

/**
 * Append one row to the order status timeline.
 * changed_by_user_id is nullable in the schema (system / scheduled actions),
 * so the NULL case is written explicitly instead of bound as an integer.
 */
function recordOrderStatus(int $orderId, string $status, ?int $changedBy, string $note = ''): bool {
    if ($changedBy !== null && $changedBy > 0) {
        return db_execute(
            'INSERT INTO order_status_history(order_id,status,changed_by_user_id,note) VALUES(?,?,?,?)',
            'isis',
            [$orderId, $status, $changedBy, $note]
        );
    }
    return db_execute(
        'INSERT INTO order_status_history(order_id,status,changed_by_user_id,note) VALUES(?,?,NULL,?)',
        'iss',
        [$orderId, $status, $note]
    );
}

/** Insert a database notification for one Harvestly user. */
function notifyUser(int $userId, string $type, string $title, string $message, ?int $orderId = null): bool {
    return db_execute(
        'INSERT INTO notifications(user_id,notification_type,title,message,related_order_id,is_read) VALUES(?,?,?,?,?,0)',
        'isssi',
        [$userId, $type, $title, $message, $orderId]
    );
}

/** Hours a Buyer has to confirm receipt before Harvestly completes automatically. */
function buyerConfirmationHours(): int {
    $hours = (int)db_setting('buyer_confirmation_hours', 48);
    return $hours > 0 ? $hours : 48;
}

/** Days after completion during which a Buyer may still review an order. */
function reviewWindowDays(): int {
    $days = (int)db_setting('review_window_days', 14);
    return $days > 0 ? $days : 14;
}

/** Maximum delivery attempts allowed before an order becomes Undeliverable. */
function maxDeliveryAttempts(): int {
    $attempts = (int)db_setting('max_delivery_attempts', 2);
    return $attempts > 0 ? $attempts : 2;
}

/**
 * Complete a Delivered order exactly once.
 *
 * Buyer clicking "Confirm Received" passes $buyerId.
 * The scheduled auto-completion script passes $automatic = true and no buyerId,
 * and is only allowed once the configured confirmation window has elapsed.
 */
function completeDeliveredOrder(int $orderId, ?int $buyerId = null, bool $automatic = false): bool {
    $db = db();
    $db->begin_transaction();
    try {
        $order = db_fetch_one(
            'SELECT order_id,buyer_id,order_status,delivered_at FROM orders WHERE order_id=? FOR UPDATE',
            'i',
            [$orderId]
        );
        if (!$order || $order['order_status'] !== 'DELIVERED') {
            $db->rollback();
            return false;
        }
        if ($buyerId !== null && (int)$order['buyer_id'] !== $buyerId) {
            $db->rollback();
            return false;
        }
        if ($automatic) {
            $window = buyerConfirmationHours();
            $elapsed = (int)db_scalar(
                'SELECT TIMESTAMPDIFF(HOUR,delivered_at,NOW()) FROM orders WHERE order_id=?',
                'i',
                [$orderId],
                0
            );
            if (empty($order['delivered_at']) || $elapsed < $window) {
                $db->rollback();
                return false;
            }
        }

        $note = $automatic
            ? 'Automatically completed after the ' . buyerConfirmationHours() . '-hour Buyer confirmation window'
            : 'Buyer confirmed receipt';

        $commands = [
            ["UPDATE orders SET order_status='COMPLETED',completed_at=NOW() WHERE order_id=?", 'i', [$orderId]],
            ["UPDATE deliveries SET delivery_status='COMPLETED',completed_at=NOW() WHERE order_id=? AND delivery_status='DELIVERED'", 'i', [$orderId]],
            ["UPDATE earnings SET earning_status='PENDING_PAYOUT' WHERE order_id=? AND beneficiary_type='COURIER_PARTNER' AND earning_status='HELD'", 'i', [$orderId]],
            ["INSERT INTO notifications(user_id,notification_type,title,message,related_order_id,is_read) VALUES(?,'ORDER_UPDATE','Order completed',?,?,0)", 'isi', [(int)$order['buyer_id'], $automatic ? 'Your order was completed automatically after the ' . buyerConfirmationHours() . '-hour confirmation window.' : 'Thank you for confirming receipt.', $orderId]],
        ];
        foreach ($commands as [$sql, $types, $params]) {
            if (!db_execute($sql, $types, $params)) {
                throw new RuntimeException('Completion could not be recorded.');
            }
        }
        if (!recordOrderStatus($orderId, 'COMPLETED', $automatic ? null : $buyerId, $note)) {
            throw new RuntimeException('Completion could not be recorded.');
        }
        $db->commit();
        return true;
    } catch (Throwable $e) {
        $db->rollback();
        return false;
    }
}
