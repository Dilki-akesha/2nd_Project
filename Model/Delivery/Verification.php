<?php
require_once __DIR__ . '/Database.php';

class Verification {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // Get organisation profile info
    public function getOrgInfo($userId) {
        $sql = "SELECT 
                    u.full_name, u.email, u.phone,
                    cp.organisation_name, cp.contact_person_name,
                    cp.office_address_line1, cp.office_city_town,
                    cp.verification_status,
                    d.district_name AS office_district
                FROM users u
                LEFT JOIN courier_partner_profiles cp ON u.user_id = cp.courier_partner_id
                LEFT JOIN districts d ON cp.office_district_id = d.district_id
                WHERE u.user_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }

    // Get verification documents
    public function getDocuments($userId) {
        $sql = "SELECT 
                    document_id, document_type, original_file_name, 
                    stored_file_path, status, rejection_reason,
                    DATE_FORMAT(created_at, '%b %d, %Y') AS uploaded_date
                FROM verification_documents
                WHERE user_id = ?
                ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
}
?>