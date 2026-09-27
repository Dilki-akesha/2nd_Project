<?php
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('Command-line only.'); }
/**
 * Scheduled task: expire unanswered Courier Partner offers and try the next
 * eligible Courier Partner. Run every few minutes/hour using Windows Task Scheduler.
 */
require_once __DIR__ . '/../config/app.php';

$rows = db_fetch_all(
    "SELECT offer_id, order_id, courier_partner_id
     FROM delivery_assignment_offers
     WHERE offer_status='PENDING'
       AND expires_at IS NOT NULL
       AND expires_at <= NOW()"
);

$expired = 0;
$reassigned = 0;
$adminFallback = 0;

foreach ($rows as $row) {
    $offerId = (int)$row['offer_id'];
    $orderId = (int)$row['order_id'];
    $courierId = (int)$row['courier_partner_id'];

    if (!db_execute(
        "UPDATE delivery_assignment_offers
         SET offer_status='EXPIRED', responded_at=NOW(), rejection_reason='No response before offer expiry'
         WHERE offer_id=? AND offer_status='PENDING'",
        'i', [$offerId]
    )) {
        continue;
    }
    $expired++;

    db_execute(
        "INSERT INTO notifications(user_id,notification_type,title,message,related_order_id,is_read)
         VALUES(?,'DELIVERY_ASSIGNMENT','Delivery request expired','The delivery request expired because no response was received in time.',?,0)",
        'ii', [$courierId,$orderId]
    );

    if (attemptAutomaticCourierAssignment($orderId)) {
        $reassigned++;
        continue;
    }

    // Automatic assignment has no eligible Courier Partner left. Admin fallback.
    $alreadyNotified = (int)db_scalar(
        "SELECT COUNT(*) FROM notifications
         WHERE related_order_id=? AND notification_type='DELIVERY_ISSUE'
           AND title='Courier assignment needs Admin review' AND is_read=0",
        'i', [$orderId], 0
    );
    if ($alreadyNotified === 0) {
        $admins = db_fetch_all("SELECT user_id FROM users WHERE role='ADMIN' AND account_status='ACTIVE'");
        foreach ($admins as $admin) {
            db_execute(
                "INSERT INTO notifications(user_id,notification_type,title,message,related_order_id,is_read)
                 VALUES(?,'DELIVERY_ISSUE','Courier assignment needs Admin review','Automatic Courier Partner assignment could not find another eligible partner for this district route.',?,0)",
                'ii', [(int)$admin['user_id'],$orderId]
            );
        }
        $adminFallback++;
    }
}

echo "Expired: {$expired}; reassigned: {$reassigned}; Admin fallback: {$adminFallback}.\n";
