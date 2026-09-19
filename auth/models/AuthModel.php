<?php
/**
 * AuthModel - Handles User Authentication and Registration Queries for all Roles
 */

require_once __DIR__ . '/../../config/database.php';

class AuthModel {
    private $db;

    public function __construct() {
        $this->db = getDBConnection();
    }

    /**
     * Search user across all 4 tables by email
     */
    public function findUserByEmail($email) {
        // Check Admin
        $stmt = $this->db->prepare("SELECT id, full_name as name, email, password_hash, 'admin' as role, 'approved' as status FROM admins WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();
        if ($user) return $user;

        // Check Buyer
        $stmt = $this->db->prepare("SELECT id, full_name as name, email, password_hash, 'buyer' as role, status FROM buyers WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();
        if ($user) return $user;

        // Check Farmer
        $stmt = $this->db->prepare("SELECT id, full_name as name, email, password_hash, 'farmer' as role, status FROM farmers WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();
        if ($user) return $user;

        // Check Courier Partner
        $stmt = $this->db->prepare("SELECT id, company_name as name, email, password_hash, 'courier' as role, status FROM courier_partners WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();
        if ($user) return $user;

        return false;
    }

    /**
     * Register Buyer (immediate activation)
     */
    public function registerBuyer($fullName, $email, $passwordHash, $phone, $province, $district, $address) {
        $stmt = $this->db->prepare("
            INSERT INTO buyers (full_name, email, password_hash, phone, province, district, address, status)
            VALUES (:full_name, :email, :password_hash, :phone, :province, :district, :address, 'active')
        ");
        return $stmt->execute([
            'full_name' => $fullName,
            'email' => $email,
            'password_hash' => $passwordHash,
            'phone' => $phone,
            'province' => $province,
            'district' => $district,
            'address' => $address
        ]);
    }

    /**
     * Register Farmer (Pending Admin Approval)
     */
    public function registerFarmer($fullName, $email, $passwordHash, $phone, $nicNumber, $farmAddress, $district, $idDocumentPath) {
        $stmt = $this->db->prepare("
            INSERT INTO farmers (full_name, email, password_hash, phone, nic_number, farm_address, district, id_document_path, status)
            VALUES (:full_name, :email, :password_hash, :phone, :nic_number, :farm_address, :district, :id_document_path, 'pending')
        ");
        return $stmt->execute([
            'full_name' => $fullName,
            'email' => $email,
            'password_hash' => $passwordHash,
            'phone' => $phone,
            'nic_number' => $nicNumber,
            'farm_address' => $farmAddress,
            'district' => $district,
            'id_document_path' => $idDocumentPath
        ]);
    }

    /**
     * Register Courier Partner Company (Pending Admin Verification)
     */
    public function registerCourierPartner($companyName, $contactPerson, $email, $passwordHash, $phone, $brnNumber, $businessAddress, $district, $registrationCertPath) {
        $stmt = $this->db->prepare("
            INSERT INTO courier_partners (company_name, contact_person, email, password_hash, phone, brn_number, business_address, district, registration_cert_path, status)
            VALUES (:company_name, :contact_person, :email, :password_hash, :phone, :brn_number, :business_address, :district, :registration_cert_path, 'pending')
        ");
        return $stmt->execute([
            'company_name' => $companyName,
            'contact_person' => $contactPerson,
            'email' => $email,
            'password_hash' => $passwordHash,
            'phone' => $phone,
            'brn_number' => $brnNumber,
            'business_address' => $businessAddress,
            'district' => $district,
            'registration_cert_path' => $registrationCertPath
        ]);
    }
}
