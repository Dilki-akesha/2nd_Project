<?php
require_once __DIR__ . '/Database.php';

class Issue {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // Create new issue/complaint
    public function create($userId, $data) {
        // Get a default order_id if not provided
        $orderId = intval($data['order_id'] ?? 0);
        if (!$orderId) {
            $stmt = $this->db->query("SELECT order_id FROM orders LIMIT 1");
            $orderId = $stmt->fetchColumn() ?: 1;
        }

        $sql = "INSERT INTO complaints 
                (order_id, complainant_user_id, complainant_role, category, description, evidence_path, complaint_status) 
                VALUES (?, ?, 'COURIER_PARTNER', ?, ?, ?, 'OPEN')";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $orderId,
            $userId,
            $data['category'],
            $data['description'],
            $data['evidence_path'] ?? null
        ]);

        return [
            'success' => true,
            'complaint_id' => $this->db->lastInsertId(),
            'message' => 'Issue submitted. Admin will review it within 24-48 hours.'
        ];
    }

    // Get all issues for this courier
    public function getAllByUser($userId) {
        $sql = "SELECT 
                    complaint_id, order_id, category, description,
                    complaint_status, admin_response,
                    DATE_FORMAT(created_at, '%b %d, %Y') AS created_date
                FROM complaints
                WHERE complainant_user_id = ? AND complainant_role = 'COURIER_PARTNER'
                ORDER BY created_at DESC
                LIMIT 50";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
}
?>