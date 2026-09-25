<?php
require_once __DIR__ . '/Database.php';

class Profile {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function get($userId) {
        $sql = "SELECT 
                    u.user_id, u.full_name, u.email, u.phone, u.account_status,
                    cp.organisation_name, cp.contact_person_name,
                    cp.office_address_line1, cp.office_address_line2,
                    cp.office_city_town, cp.office_postal_code,
                    cp.office_district_id, cp.availability_status,
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

    public function update($userId, $data) {
        try {
            $this->db->beginTransaction();

            // Update users table
            $sql1 = "UPDATE users SET full_name = ?, phone = ?, email = ? WHERE user_id = ?";
            $stmt1 = $this->db->prepare($sql1);
            $stmt1->execute([
                $data['contact_person'],
                $data['phone'],
                $data['email'],
                $userId
            ]);

            // Prepare district_id — must be NULL or valid ID (never 0)
            $districtId = !empty($data['district_id']) && intval($data['district_id']) > 0 
                          ? intval($data['district_id']) 
                          : null;

            // Update courier_partner_profiles with explicit NULL handling
            $sql2 = "UPDATE courier_partner_profiles SET 
                        organisation_name = ?, 
                        contact_person_name = ?,
                        office_address_line1 = ?,
                        office_city_town = ?,
                        office_district_id = ?
                     WHERE courier_partner_id = ?";

            $stmt2 = $this->db->prepare($sql2);
            $stmt2->bindValue(1, $data['organisation_name'], PDO::PARAM_STR);
            $stmt2->bindValue(2, $data['contact_person'], PDO::PARAM_STR);
            $stmt2->bindValue(3, $data['address'], PDO::PARAM_STR);
            $stmt2->bindValue(4, $data['city'], PDO::PARAM_STR);
            if ($districtId === null) {
                $stmt2->bindValue(5, null, PDO::PARAM_NULL);
            } else {
                $stmt2->bindValue(5, $districtId, PDO::PARAM_INT);
            }
            $stmt2->bindValue(6, $userId, PDO::PARAM_INT);
            $stmt2->execute();

            $this->db->commit();
            return ['success' => true, 'message' => 'Profile updated successfully.'];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Update failed: ' . $e->getMessage()];
        }
    }
}
?>