<?php
/**
 * Harvestly scheduled task: automatic order completion.
 *
 * After a Courier Partner marks a delivery Delivered, a Buyer has a configured
 * confirmation window (default 48 hours) to confirm receipt. Once that window
 * passes, the order is completed automatically.
 *
 * A complaint does not block this automatic completion.
 *
 * Run from Windows Task Scheduler:
 *   php scripts/auto_complete_deliveries.php
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Command-line only.');
}

require_once __DIR__ . '/../config/app.php';

$hours = buyerConfirmationHours();

$rows = db_fetch_all(
    "SELECT o.order_id
     FROM orders o
     LEFT JOIN deliveries d ON d.order_id = o.order_id
     WHERE o.order_status = 'DELIVERED'
       AND COALESCE(o.delivered_at, d.delivered_at) IS NOT NULL
       AND COALESCE(o.delivered_at, d.delivered_at) <= DATE_SUB(NOW(), INTERVAL ? HOUR)",
    'i',
    [$hours]
);

$completed = 0;
$skipped = 0;
foreach ($rows as $row) {
    // automatic = true, buyerId = null (the Buyer did not click Confirm Received).
    if (completeDeliveredOrder((int)$row['order_id'], null, true)) {
        $completed++;
    } else {
        $skipped++;
    }
}

echo sprintf(
    "Confirmation window: %d hours. Completed %d order(s), skipped %d.\n",
    $hours,
    $completed,
    $skipped
);
