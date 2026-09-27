<?php
/**
 * Harvestly automatic Courier Partner assignment.
 *
 * Simplified final flow (no workload ranking, no AI selection, no GPS proximity):
 *   Ready for Delivery
 *        -> find an eligible Courier Partner
 *        -> offer the assignment
 *        -> Courier Partner accepts
 *   If the offer is rejected or expires, the next eligible partner is attempted.
 *   Only when no eligible partner exists does the order fall back to
 *   Pending Assignment for Admin assistance.
 */
require_once __DIR__ . '/../../config/app.php';

function offerCourierAssignment(int $orderId): bool {
    $db = db();
    $db->begin_transaction();
    try {
        $order = db_fetch_one(
            'SELECT o.order_status,o.destination_district_id,fp.district_id origin_district_id
             FROM orders o JOIN farmer_profiles fp ON fp.farmer_id=o.farmer_id
             WHERE o.order_id=? FOR UPDATE',
            'i',
            [$orderId]
        );
        if (!$order || !in_array($order['order_status'], ['READY_FOR_DELIVERY', 'PENDING_ASSIGNMENT'], true)) {
            $db->rollback();
            return false;
        }

        // An unanswered offer is still live - do not offer a second time.
        if (db_fetch_one(
            "SELECT offer_id FROM delivery_assignment_offers WHERE order_id=? AND offer_status='PENDING' AND expires_at>NOW()",
            'i',
            [$orderId]
        )) {
            $db->commit();
            return true;
        }

        // Expire anything that timed out so the next partner can be attempted.
        db_execute(
            "UPDATE delivery_assignment_offers SET offer_status='EXPIRED',responded_at=NOW() WHERE order_id=? AND offer_status='PENDING' AND expires_at<=NOW()",
            'i',
            [$orderId]
        );

        /*
         * Eligibility is only: approved + active + available + an active
         * origin -> destination district coverage route that this partner has not
         * already rejected, let expire, or had cancelled for this order.
         *
         * The ORDER BY is a simple fair rotation so the same organisation is not
         * offered every order. It is not workload ranking, AI selection or
         * proximity-based selection.
         */
        $courier = db_fetch_one(
            "SELECT cp.courier_partner_id
             FROM courier_partner_profiles cp
             JOIN users u ON u.user_id=cp.courier_partner_id
             JOIN courier_coverage_routes r ON r.courier_partner_id=cp.courier_partner_id
             WHERE u.role='COURIER_PARTNER'
               AND u.account_status='ACTIVE'
               AND cp.verification_status='APPROVED'
               AND cp.availability_status='AVAILABLE'
               AND r.is_active=1
               AND r.origin_district_id=?
               AND r.destination_district_id=?
               AND NOT EXISTS(
                     SELECT 1 FROM delivery_assignment_offers a
                     WHERE a.order_id=?
                       AND a.courier_partner_id=cp.courier_partner_id
                       AND a.offer_status IN ('REJECTED','EXPIRED','CANCELLED')
               )
             ORDER BY
               (SELECT MAX(a2.offered_at) FROM delivery_assignment_offers a2
                 WHERE a2.courier_partner_id=cp.courier_partner_id) IS NULL DESC,
               (SELECT MAX(a2.offered_at) FROM delivery_assignment_offers a2
                 WHERE a2.courier_partner_id=cp.courier_partner_id) ASC,
               cp.courier_partner_id ASC
             LIMIT 1",
            'iii',
            [(int)$order['origin_district_id'], (int)$order['destination_district_id'], $orderId]
        );

        // Every order always has exactly one delivery row.
        if (!db_execute(
            "INSERT INTO deliveries(order_id,delivery_status) VALUES(?,'PENDING_ASSIGNMENT')
             ON DUPLICATE KEY UPDATE order_id=VALUES(order_id)",
            'i',
            [$orderId]
        )) {
            throw new RuntimeException('Delivery record could not be prepared.');
        }

        if (!$courier) {
            // Automatic assignment failed - Admin intervention.
            db_execute("UPDATE orders SET order_status='PENDING_ASSIGNMENT' WHERE order_id=?", 'i', [$orderId]);
            if (!db_scalar(
                "SELECT COUNT(*) FROM notifications WHERE related_order_id=? AND title='Courier assignment needs Admin review'",
                'i',
                [$orderId],
                0
            )) {
                db_execute(
                    "INSERT INTO notifications(user_id,notification_type,title,message,related_order_id,is_read)
                     SELECT user_id,'DELIVERY_ISSUE','Courier assignment needs Admin review',
                            'No eligible available Courier Partner covers this district route.',?,0
                     FROM users WHERE role='ADMIN' AND account_status='ACTIVE'",
                    'i',
                    [$orderId]
                );
            }
            $db->commit();
            return false;
        }

        $courierId = (int)$courier['courier_partner_id'];
        $minutes = max(5, (int)db_setting('courier_assignment_response_minutes', 30));
        if (!db_execute(
            "INSERT INTO delivery_assignment_offers(order_id,courier_partner_id,offer_status,offered_at,expires_at)
             VALUES(?,?,'PENDING',NOW(),DATE_ADD(NOW(),INTERVAL ? MINUTE))",
            'iii',
            [$orderId, $courierId, $minutes]
        )) {
            throw new RuntimeException('Assignment offer could not be created.');
        }
        db_execute("UPDATE orders SET order_status='READY_FOR_DELIVERY' WHERE order_id=?", 'i', [$orderId]);
        db_execute(
            "INSERT INTO notifications(user_id,notification_type,title,message,related_order_id,is_read)
             VALUES(?,'DELIVERY_ASSIGNMENT','New delivery request',
                    'A delivery request matching your district coverage awaits a response.',?,0)",
            'ii',
            [$courierId, $orderId]
        );
        $db->commit();
        return true;
    } catch (Throwable $e) {
        $db->rollback();
        return false;
    }
}
