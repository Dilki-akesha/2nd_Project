<?php
require_once __DIR__ . '/Database.php';

class DeliveryAttempt {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // Get current attempt count for a delivery
    public function getCount($deliveryId) {
        $sql = "SELECT COUNT(*) FROM delivery_attempts WHERE delivery_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$deliveryId]);
        return (int) $stmt->fetchColumn();
    }

    // Record a failed attempt
    public function recordUnavailable($deliveryId, $notes = null) {
        $currentCount = $this->getCount($deliveryId);
        $maxAttempts = 2;

        if ($currentCount >= $maxAttempts) {
            return [
                'success' => false,
                'message' => 'Maximum delivery attempts already reached.',
                'attempt_count' => $currentCount
            ];
        }

        $attemptNumber = $currentCount + 1;

        $sql = "INSERT INTO delivery_attempts 
                (delivery_id, attempt_number, attempt_result, notes, attempted_at) 
                VALUES (?, ?, 'BUYER_UNAVAILABLE', ?, NOW())";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$deliveryId, $attemptNumber, $notes]);

        // If this was the 2nd attempt → mark delivery as UNDELIVERABLE
        if ($attemptNumber >= $maxAttempts) {
            $updateSql = "UPDATE deliveries 
                          SET delivery_status = 'UNDELIVERABLE' 
                          WHERE delivery_id = ?";
            $updateStmt = $this->db->prepare($updateSql);
            $updateStmt->execute([$deliveryId]);

            return [
                'success' => true,
                'attempt_count' => $attemptNumber,
                'status' => 'UNDELIVERABLE',
                'message' => 'Second failed attempt recorded. Order marked as Undeliverable. Admin has been notified.'
            ];
        }

        return [
            'success' => true,
            'attempt_count' => $attemptNumber,
            'status' => 'IN_TRANSIT',
            'message' => 'Failed delivery attempt recorded. One more attempt is allowed.'
        ];
    }
}
?>