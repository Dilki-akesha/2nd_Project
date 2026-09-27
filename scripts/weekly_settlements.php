<?php
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('Command-line only.'); }
/**
 * Scheduled task: record weekly settlement of eligible pending payouts.
 * This updates Harvestly's internal records only; it does NOT transfer real money.
 */
require_once __DIR__ . '/../config/app.php';

$rows = db_fetch_all(
    "SELECT earning_id, amount
     FROM earnings
     WHERE earning_status='PENDING_PAYOUT'
       AND beneficiary_user_id IS NOT NULL
     ORDER BY earning_id"
);

if (!$rows) {
    echo "No pending payouts to settle.\n";
    exit;
}

$total = 0.0;
foreach ($rows as $row) $total += (float)$row['amount'];
$periodEnd = date('Y-m-d');
$periodStart = date('Y-m-d', strtotime('-6 days'));
$db = db();
$db->begin_transaction();

try {
    $stmt = $db->prepare(
        "INSERT INTO settlements(period_start,period_end,total_amount,settlement_status,processed_at)
         VALUES(?,?,?,'PROCESSING',NOW())"
    );
    $stmt->bind_param('ssd', $periodStart, $periodEnd, $total);
    if (!$stmt->execute()) throw new RuntimeException('Unable to create settlement.');
    $settlementId = (int)$db->insert_id;
    $stmt->close();

    foreach ($rows as $row) {
        $earningId = (int)$row['earning_id'];
        if (!db_execute(
            "INSERT INTO settlement_items(settlement_id,earning_id) VALUES(?,?)",
            'ii', [$settlementId,$earningId]
        )) throw new RuntimeException('Unable to add settlement item.');
        if (!db_execute(
            "UPDATE earnings SET earning_status='PAID',settled_at=NOW() WHERE earning_id=? AND earning_status='PENDING_PAYOUT'",
            'i', [$earningId]
        )) throw new RuntimeException('Unable to settle earning.');
    }

    db_execute(
        "UPDATE settlements SET settlement_status='COMPLETED',processed_at=NOW() WHERE settlement_id=?",
        'i', [$settlementId]
    );
    $db->commit();
    echo "Settlement {$settlementId} completed for " . count($rows) . " earning(s), total LKR " . number_format($total, 2) . ".\n";
} catch (Throwable $e) {
    $db->rollback();
    fwrite(STDERR, "Settlement failed: " . $e->getMessage() . "\n");
    exit(1);
}
