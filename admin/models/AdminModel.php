<?php
/**
 * AdminModel - Handles all Database Operations for the Administrator Module
 */

require_once __DIR__ . '/../../config/database.php';

class AdminModel {
    private $db;

    public function __construct() {
        $this->db = getDBConnection();
    }

    /* ------------------------------------------------------------------
     * 1. OVERVIEW & KPI METRICS
     * ------------------------------------------------------------------ */
    public function getOverviewKPIs() {
        $kpis = [];

        // Total Farmers
        $stmt = $this->db->query("SELECT COUNT(*) FROM farmers WHERE status = 'approved'");
        $kpis['total_farmers'] = $stmt->fetchColumn();

        // Total Buyers
        $stmt = $this->db->query("SELECT COUNT(*) FROM buyers WHERE status = 'active'");
        $kpis['total_buyers'] = $stmt->fetchColumn();

        // Total Courier Partners
        $stmt = $this->db->query("SELECT COUNT(*) FROM courier_partners WHERE status = 'approved'");
        $kpis['total_couriers'] = $stmt->fetchColumn();

        // Total Orders
        $stmt = $this->db->query("SELECT COUNT(*) FROM orders");
        $kpis['total_orders'] = $stmt->fetchColumn();

        // Active Disputes
        $stmt = $this->db->query("SELECT COUNT(*) FROM complaints WHERE status IN ('open', 'investigating')");
        $kpis['active_disputes'] = $stmt->fetchColumn();

        // Pending Verifications
        $stmtFarmers = $this->db->query("SELECT COUNT(*) FROM farmers WHERE status = 'pending'");
        $stmtCouriers = $this->db->query("SELECT COUNT(*) FROM courier_partners WHERE status = 'pending'");
        $kpis['pending_verifications'] = $stmtFarmers->fetchColumn() + $stmtCouriers->fetchColumn();

        // Weekly Settlement Total
        $stmt = $this->db->query("SELECT COALESCE(SUM(amount), 0) FROM settlements WHERE settlement_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)");
        $kpis['weekly_settlements'] = $stmt->fetchColumn();

        return $kpis;
    }

    public function getRecentActivityLog() {
        // Return unified list of recent system activities
        $activities = [];
        
        $stmt = $this->db->query("SELECT 'New Order' as type, CONCAT('Order ', order_number, ' placed') as detail, created_at FROM orders ORDER BY created_at DESC LIMIT 5");
        $activities = array_merge($activities, $stmt->fetchAll());

        $stmt = $this->db->query("SELECT 'Farmer Verification' as type, CONCAT(full_name, ' registered for review') as detail, created_at FROM farmers WHERE status='pending' ORDER BY created_at DESC LIMIT 5");
        $activities = array_merge($activities, $stmt->fetchAll());

        usort($activities, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        return array_slice($activities, 0, 6);
    }

    /* ------------------------------------------------------------------
     * 2. USER MANAGEMENT
     * ------------------------------------------------------------------ */
    public function getAllUsers($roleFilter = 'all', $statusFilter = 'all') {
        $users = [];

        if ($roleFilter === 'all' || $roleFilter === 'buyer') {
            $sql = "SELECT id, full_name as name, email, phone, 'Buyer' as role, status, created_at, address as detail, district, province, NULL as nic_number, NULL as brn_number, NULL as document_path FROM buyers WHERE 1=1";
            if ($statusFilter !== 'all') $sql .= " AND status = '$statusFilter'";
            $users = array_merge($users, $this->db->query($sql)->fetchAll());
        }

        if ($roleFilter === 'all' || $roleFilter === 'farmer') {
            $sql = "SELECT id, full_name as name, email, phone, 'Farmer' as role, status, created_at, farm_address as detail, district, NULL as province, nic_number, NULL as brn_number, id_document_path as document_path FROM farmers WHERE 1=1";
            if ($statusFilter !== 'all') $sql .= " AND status = '$statusFilter'";
            $users = array_merge($users, $this->db->query($sql)->fetchAll());
        }

        if ($roleFilter === 'all' || $roleFilter === 'courier') {
            $sql = "SELECT id, company_name as name, email, phone, 'Courier Partner' as role, status, created_at, business_address as detail, district, NULL as province, NULL as nic_number, brn_number, registration_cert_path as document_path, contact_person FROM courier_partners WHERE 1=1";
            if ($statusFilter !== 'all') $sql .= " AND status = '$statusFilter'";
            $users = array_merge($users, $this->db->query($sql)->fetchAll());
        }

        usort($users, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        return $users;
    }

    public function createUser($role, $data) {
        $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
        $roleLower = strtolower($role);

        if ($roleLower === 'buyer') {
            $stmt = $this->db->prepare("
                INSERT INTO buyers (full_name, email, password_hash, phone, province, district, address, status)
                VALUES (:name, :email, :pass, :phone, :province, :district, :address, :status)
            ");
            return $stmt->execute([
                'name' => $data['name'],
                'email' => $data['email'],
                'pass' => $passwordHash,
                'phone' => $data['phone'],
                'province' => $data['province'] ?? 'Western',
                'district' => $data['district'] ?? 'Colombo',
                'address' => $data['address'] ?? '',
                'status' => $data['status'] ?? 'active'
            ]);
        } else if ($roleLower === 'farmer') {
            $stmt = $this->db->prepare("
                INSERT INTO farmers (full_name, email, password_hash, phone, nic_number, farm_address, district, id_document_path, status)
                VALUES (:name, :email, :pass, :phone, :nic, :address, :district, :doc, :status)
            ");
            return $stmt->execute([
                'name' => $data['name'],
                'email' => $data['email'],
                'pass' => $passwordHash,
                'phone' => $data['phone'],
                'nic' => $data['nic_number'] ?? '199000000000',
                'address' => $data['address'] ?? '',
                'district' => $data['district'] ?? 'Nuwara Eliya',
                'doc' => $data['document_path'] ?? 'nic_placeholder.jpg',
                'status' => $data['status'] ?? 'approved'
            ]);
        } else if ($roleLower === 'courier' || $roleLower === 'courier partner') {
            $stmt = $this->db->prepare("
                INSERT INTO courier_partners (company_name, contact_person, email, password_hash, phone, brn_number, business_address, district, registration_cert_path, status)
                VALUES (:name, :contact, :email, :pass, :phone, :brn, :address, :district, :doc, :status)
            ");
            return $stmt->execute([
                'name' => $data['name'],
                'contact' => $data['contact_person'] ?? $data['name'],
                'email' => $data['email'],
                'pass' => $passwordHash,
                'phone' => $data['phone'],
                'brn' => $data['brn_number'] ?? 'PV-000000',
                'address' => $data['address'] ?? '',
                'district' => $data['district'] ?? 'Colombo',
                'doc' => $data['document_path'] ?? 'brn_placeholder.pdf',
                'status' => $data['status'] ?? 'approved'
            ]);
        }
        return false;
    }

    public function updateUserDetails($role, $id, $data) {
        $roleLower = strtolower($role);

        if ($roleLower === 'buyer') {
            $stmt = $this->db->prepare("
                UPDATE buyers 
                SET full_name = :name, email = :email, phone = :phone, district = :district, address = :address, status = :status
                WHERE id = :id
            ");
            return $stmt->execute([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'district' => $data['district'],
                'address' => $data['address'],
                'status' => $data['status'],
                'id' => $id
            ]);
        } else if ($roleLower === 'farmer') {
            $stmt = $this->db->prepare("
                UPDATE farmers 
                SET full_name = :name, email = :email, phone = :phone, nic_number = :nic, district = :district, farm_address = :address, status = :status
                WHERE id = :id
            ");
            return $stmt->execute([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'nic' => $data['nic_number'],
                'district' => $data['district'],
                'address' => $data['address'],
                'status' => $data['status'],
                'id' => $id
            ]);
        } else if ($roleLower === 'courier' || $roleLower === 'courier partner') {
            $stmt = $this->db->prepare("
                UPDATE courier_partners 
                SET company_name = :name, contact_person = :contact, email = :email, phone = :phone, brn_number = :brn, district = :district, business_address = :address, status = :status
                WHERE id = :id
            ");
            return $stmt->execute([
                'name' => $data['name'],
                'contact' => $data['contact_person'] ?? $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'brn' => $data['brn_number'],
                'district' => $data['district'],
                'address' => $data['address'],
                'status' => $data['status'],
                'id' => $id
            ]);
        }
        return false;
    }

    public function deleteUser($role, $id) {
        $tableMap = [
            'buyer' => 'buyers',
            'farmer' => 'farmers',
            'courier' => 'courier_partners',
            'courier partner' => 'courier_partners'
        ];
        $table = $tableMap[strtolower($role)] ?? null;
        if (!$table) return false;

        $stmt = $this->db->prepare("DELETE FROM {$table} WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function updateUserStatus($userType, $userId, $status) {
        $tableMap = [
            'buyer' => 'buyers',
            'farmer' => 'farmers',
            'courier' => 'courier_partners',
            'courier partner' => 'courier_partners'
        ];
        $table = $tableMap[strtolower($userType)] ?? null;
        if (!$table) return false;

        $stmt = $this->db->prepare("UPDATE {$table} SET status = :status WHERE id = :id");
        return $stmt->execute(['status' => $status, 'id' => $userId]);
    }

    /* ------------------------------------------------------------------
     * 3. VERIFICATION QUEUE
     * ------------------------------------------------------------------ */
    public function getPendingFarmers() {
        $stmt = $this->db->query("SELECT * FROM farmers WHERE status IN ('pending', 'resubmit_requested') ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }

    public function getPendingCouriers() {
        $stmt = $this->db->query("SELECT * FROM courier_partners WHERE status IN ('pending', 'resubmit_requested') ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }

    public function updateFarmerVerification($id, $status, $reason = null) {
        $stmt = $this->db->prepare("UPDATE farmers SET status = :status, rejection_reason = :reason WHERE id = :id");
        return $stmt->execute(['status' => $status, 'reason' => $reason, 'id' => $id]);
    }

    public function updateCourierVerification($id, $status, $reason = null) {
        $stmt = $this->db->prepare("UPDATE courier_partners SET status = :status, rejection_reason = :reason WHERE id = :id");
        return $stmt->execute(['status' => $status, 'reason' => $reason, 'id' => $id]);
    }

    /* ------------------------------------------------------------------
     * 4. LISTINGS MONITOR
     * ------------------------------------------------------------------ */
    public function getAllProducts() {
        $stmt = $this->db->query("
            SELECT p.*, f.full_name as farmer_name 
            FROM products p
            JOIN farmers f ON p.farmer_id = f.id
            ORDER BY p.created_at DESC
        ");
        return $stmt->fetchAll();
    }

    public function updateProductStatus($productId, $status) {
        $stmt = $this->db->prepare("UPDATE products SET status = :status WHERE id = :id");
        return $stmt->execute(['status' => $status, 'id' => $productId]);
    }

    /* ------------------------------------------------------------------
     * 5. ORDERS MONITOR & MANUAL OVERRIDE
     * ------------------------------------------------------------------ */
    public function getAllOrders() {
        $stmt = $this->db->query("
            SELECT o.*, b.full_name as buyer_name, cp.company_name as courier_name
            FROM orders o
            JOIN buyers b ON o.buyer_id = b.id
            LEFT JOIN courier_partners cp ON o.courier_id = cp.id
            ORDER BY o.created_at DESC
        ");
        return $stmt->fetchAll();
    }

    public function getOrderDetails($orderId) {
        $stmt = $this->db->prepare("
            SELECT o.*, b.full_name as buyer_name, b.email as buyer_email, b.phone as buyer_phone, cp.company_name as courier_name
            FROM orders o
            JOIN buyers b ON o.buyer_id = b.id
            LEFT JOIN courier_partners cp ON o.courier_id = cp.id
            WHERE o.id = :id
        ");
        $stmt->execute(['id' => $orderId]);
        $order = $stmt->fetch();

        if ($order) {
            $stmtItems = $this->db->prepare("
                SELECT oi.*, p.title as product_title, p.grade, f.full_name as farmer_name
                FROM order_items oi
                JOIN products p ON oi.product_id = p.id
                JOIN farmers f ON p.farmer_id = f.id
                WHERE oi.order_id = :id
            ");
            $stmtItems->execute(['id' => $orderId]);
            $order['items'] = $stmtItems->fetchAll();
        }

        return $order;
    }

    public function overrideDeliveryAssignment($orderId, $courierId) {
        $stmt = $this->db->prepare("UPDATE orders SET courier_id = :courier_id, status = 'assigned' WHERE id = :id");
        $success = $stmt->execute(['courier_id' => $courierId, 'id' => $orderId]);

        if ($success) {
            // Update or create delivery row
            $stmtDel = $this->db->prepare("
                INSERT INTO deliveries (order_id, courier_id, pickup_status, delivery_status, assigned_at)
                VALUES (:order_id, :courier_id, 'pending', 'pending', NOW())
                ON DUPLICATE KEY UPDATE courier_id = :courier_id, assigned_at = NOW()
            ");
            $stmtDel->execute(['order_id' => $orderId, 'courier_id' => $courierId]);
        }

        return $success;
    }

    /* ------------------------------------------------------------------
     * 6. COMPLAINTS & DISPUTES
     * ------------------------------------------------------------------ */
    public function getAllComplaints() {
        $stmt = $this->db->query("
            SELECT c.*, b.full_name as buyer_name, o.order_number
            FROM complaints c
            JOIN buyers b ON c.buyer_id = b.id
            JOIN orders o ON c.order_id = o.id
            ORDER BY c.created_at DESC
        ");
        return $stmt->fetchAll();
    }

    public function resolveComplaint($complaintId, $status, $resolutionNotes) {
        $stmt = $this->db->prepare("UPDATE complaints SET status = :status, resolution_notes = :notes WHERE id = :id");
        return $stmt->execute(['status' => $status, 'notes' => $resolutionNotes, 'id' => $complaintId]);
    }

    /* ------------------------------------------------------------------
     * 7. REPORTS GENERATOR
     * ------------------------------------------------------------------ */
    public function generateReportData($reportType, $startDate, $endDate) {
        $data = [];
        if ($reportType === 'orders') {
            $stmt = $this->db->prepare("SELECT order_number, total_amount, delivery_fee, status, created_at FROM orders WHERE DATE(created_at) BETWEEN :start AND :end");
            $stmt->execute(['start' => $startDate, 'end' => $endDate]);
            $data = $stmt->fetchAll();
        } else if ($reportType === 'settlements') {
            $stmt = $this->db->prepare("SELECT reference_no, user_type, user_id, amount, status, settlement_date FROM settlements WHERE settlement_date BETWEEN :start AND :end");
            $stmt->execute(['start' => $startDate, 'end' => $endDate]);
            $data = $stmt->fetchAll();
        } else {
            $stmt = $this->db->prepare("SELECT full_name, email, phone, status, created_at FROM farmers WHERE DATE(created_at) BETWEEN :start AND :end");
            $stmt->execute(['start' => $startDate, 'end' => $endDate]);
            $data = $stmt->fetchAll();
        }
        return $data;
    }

    /* ------------------------------------------------------------------
     * 8. REGIONS & HUBS
     * ------------------------------------------------------------------ */
    public function getDistricts() {
        $stmt = $this->db->query("SELECT d.*, z.zone_name, h.hub_name FROM districts d LEFT JOIN zone_fee_tiers z ON d.zone_fee_tier_id = z.id LEFT JOIN hubs h ON d.primary_hub_id = h.id");
        return $stmt->fetchAll();
    }

    public function getZoneFeeTiers() {
        $stmt = $this->db->query("SELECT * FROM zone_fee_tiers ORDER BY id ASC");
        return $stmt->fetchAll();
    }

    public function getHubs() {
        $stmt = $this->db->query("SELECT * FROM hubs ORDER BY id ASC");
        return $stmt->fetchAll();
    }

    public function getCourierCoverage() {
        $stmt = $this->db->query("SELECT cc.*, cp.company_name, d.district_name FROM courier_coverage cc JOIN courier_partners cp ON cc.courier_id = cp.id JOIN districts d ON cc.district_id = d.id");
        return $stmt->fetchAll();
    }

    public function updateZoneFeeTier($tierId, $baseFee, $perKgFee) {
        $stmt = $this->db->prepare("UPDATE zone_fee_tiers SET base_fee = :base, per_kg_fee = :kg WHERE id = :id");
        return $stmt->execute(['base' => $baseFee, 'kg' => $perKgFee, 'id' => $tierId]);
    }

    /* ------------------------------------------------------------------
     * 9. SETTLEMENTS
     * ------------------------------------------------------------------ */
    public function getSettlements() {
        $stmt = $this->db->query("SELECT * FROM settlements ORDER BY settlement_date DESC");
        return $stmt->fetchAll();
    }

    /* ------------------------------------------------------------------
     * 10. NOTIFICATIONS
     * ------------------------------------------------------------------ */
    public function getNotificationsLog() {
        $stmt = $this->db->query("SELECT * FROM notifications_log ORDER BY sent_at DESC");
        return $stmt->fetchAll();
    }

    public function createNotification($scope, $recipientId, $subject, $message) {
        $stmt = $this->db->prepare("INSERT INTO notifications_log (recipient_scope, recipient_id, subject, message, created_by) VALUES (:scope, :rec_id, :subject, :msg, 'Admin')");
        return $stmt->execute(['scope' => $scope, 'rec_id' => $recipientId, 'subject' => $subject, 'msg' => $message]);
    }

    /* ------------------------------------------------------------------
     * 11. PLATFORM SETTINGS
     * ------------------------------------------------------------------ */
    public function getPlatformSettings() {
        $stmt = $this->db->query("SELECT * FROM platform_settings");
        $settings = [];
        foreach ($stmt->fetchAll() as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return $settings;
    }

    public function updatePlatformSettings($settingsArray) {
        $stmt = $this->db->prepare("INSERT INTO platform_settings (setting_key, setting_value) VALUES (:key, :val) ON DUPLICATE KEY UPDATE setting_value = :val");
        foreach ($settingsArray as $key => $val) {
            $stmt->execute(['key' => $key, 'val' => $val]);
        }
        return true;
    }
}
