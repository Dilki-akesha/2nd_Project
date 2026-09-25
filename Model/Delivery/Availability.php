<?php
require_once __DIR__ . '/Database.php';

class Availability {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function get($courierId) {
        $sql = "SELECT availability_status FROM courier_partner_profiles 
                WHERE courier_partner_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$courierId]);
        $row = $stmt->fetch();
        return $row ? $row['availability_status'] : 'UNAVAILABLE';
    }

    public function set($courierId, $status) {
        // Validate status
        if (!in_array($status, ['AVAILABLE', 'UNAVAILABLE'])) {
            return ['success' => false, 'message' => 'Invalid status'];
        }

        $sql = "UPDATE courier_partner_profiles 
                SET availability_status = ? 
                WHERE courier_partner_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$status, $courierId]);

        return [
            'success' => true,
            'availability_status' => $status
        ];
    }
}
?>