<?php
/**
 * AdminModel - Handles all Database Operations for the Harvestly Administrator Module
 * Targets the single `harvestly` database created by
 * database/harvestly_final_clean_install.sql, using MySQLi prepared statements.
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
    public function getWeeklyOrderCounts(): array {
        return db_fetch_all('SELECT DATE(created_at) day,COUNT(*) total FROM orders WHERE created_at>=DATE_SUB(CURDATE(),INTERVAL 6 DAY) GROUP BY DATE(created_at) ORDER BY day');
    }
    public function getOverviewKPIs() {
        $kpis = [];

        try {
            // users.account_status is an ENUM of PENDING, ACTIVE, REJECTED, SUSPENDED.
            $res = $this->db->query("SELECT COUNT(*) as c FROM users WHERE role = 'FARMER' AND account_status = 'ACTIVE'");
            $kpis['total_farmers'] = $res ? (int)$res->fetch_assoc()['c'] : 0;

            $res = $this->db->query("SELECT COUNT(*) as c FROM users WHERE role = 'BUYER' AND account_status = 'ACTIVE'");
            $kpis['total_buyers'] = $res ? (int)$res->fetch_assoc()['c'] : 0;

            $res = $this->db->query("SELECT COUNT(*) as c FROM users WHERE role = 'COURIER_PARTNER' AND account_status = 'ACTIVE'");
            $kpis['total_couriers'] = $res ? (int)$res->fetch_assoc()['c'] : 0;

            $res = $this->db->query("SELECT COUNT(*) as c FROM orders");
            $kpis['total_orders'] = $res ? (int)$res->fetch_assoc()['c'] : 0;

            // complaints.complaint_status is an ENUM of OPEN, UNDER_REVIEW, RESOLVED, REJECTED.
            $res = $this->db->query("SELECT COUNT(*) as c FROM complaints WHERE complaint_status IN ('OPEN', 'UNDER_REVIEW')");
            $kpis['active_complaints'] = $res ? (int)$res->fetch_assoc()['c'] : 0;

            $res = $this->db->query("SELECT COUNT(*) as c FROM users WHERE account_status = 'PENDING'");
            $kpis['pending_verifications'] = $res ? (int)$res->fetch_assoc()['c'] : 0;

            // Orders that reached Delivered or Completed in the last 7 days.
            $res = $this->db->query("
                SELECT COALESCE(SUM(o.product_subtotal - o.farmer_marketplace_fee + o.delivery_fee), 0) AS s
                FROM orders o
                WHERE o.order_status IN ('DELIVERED', 'COMPLETED')
                  AND COALESCE(o.completed_at, o.delivered_at) >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            ");
            $kpis['weekly_settlements'] = $res ? (float)$res->fetch_assoc()['s'] : 0.00;
        } catch (Exception $e) {
            $kpis = [
                'total_farmers' => 0, 'total_buyers' => 0, 'total_couriers' => 0,
                'total_orders' => 0, 'active_complaints' => 0, 'pending_verifications' => 0,
                'weekly_settlements' => 0.00
            ];
        }

        return $kpis;
    }

    public function getRecentActivityLog() {
        $activities = [];
        try {
            $res1 = $this->db->query("SELECT 'New Order' as type, CONCAT('Order #ORD-', YEAR(created_at), '-', order_id, ' placed') as detail, created_at FROM orders ORDER BY created_at DESC LIMIT 5");
            if ($res1) {
                $activities = array_merge($activities, $res1->fetch_all(MYSQLI_ASSOC));
            }

            $res2 = $this->db->query("SELECT 'User Registration' as type, CONCAT(full_name, ' (', role, ') registered') as detail, created_at FROM users WHERE account_status='PENDING' ORDER BY created_at DESC LIMIT 5");
            if ($res2) {
                $activities = array_merge($activities, $res2->fetch_all(MYSQLI_ASSOC));
            }

            usort($activities, function($a, $b) {
                return strtotime($b['created_at']) - strtotime($a['created_at']);
            });
        } catch (Exception $e) {}

        return array_slice($activities, 0, 6);
    }

    /* ------------------------------------------------------------------
     * 2. USER MANAGEMENT
     * ------------------------------------------------------------------ */
    public function getAllUsers($roleFilter = 'all', $statusFilter = 'all') {
        $sql = "
            SELECT u.user_id as id, u.full_name as name, u.email, u.phone, u.role, u.account_status as status, u.created_at,
                   d.district_name as district, d.province_name as province,
                   COALESCE(bp.default_address_line1, fp.pickup_address_line1, cp.office_address_line1, '') as detail,
                   cp.contact_person_name as contact_person,
                   vd.document_id as document_id,
                   vd.stored_file_path as document_path
            FROM users u
            LEFT JOIN buyer_profiles bp ON u.user_id = bp.buyer_id
            LEFT JOIN farmer_profiles fp ON u.user_id = fp.farmer_id
            LEFT JOIN courier_partner_profiles cp ON u.user_id = cp.courier_partner_id
            LEFT JOIN districts d ON (fp.district_id = d.district_id OR bp.default_district_id = d.district_id OR cp.office_district_id = d.district_id)
            LEFT JOIN verification_documents vd ON u.user_id = vd.user_id
            WHERE 1=1
        ";

        if ($roleFilter !== 'all') {
            $roleMap = ['buyer' => 'BUYER', 'farmer' => 'FARMER', 'courier' => 'COURIER_PARTNER', 'courier_partner' => 'COURIER_PARTNER', 'admin' => 'ADMIN'];
            $mapped = $roleMap[strtolower($roleFilter)] ?? strtoupper($roleFilter);
            $mappedClean = $this->db->real_escape_string($mapped);
            $sql .= " AND u.role = '$mappedClean'";
        }

        if ($statusFilter !== 'all') {
            $normalizedStatus = strtoupper((string)$statusFilter);
            $statusClean = $this->db->real_escape_string($normalizedStatus);
            $sql .= " AND u.account_status = '$statusClean'";
        }

        $sql .= " GROUP BY u.user_id ORDER BY u.created_at DESC";
        $result = $this->db->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    private function districtIdByName(string $districtName): ?int {
        $stmt = $this->db->prepare("SELECT district_id FROM districts WHERE district_name=? AND is_active=1 LIMIT 1");
        if (!$stmt) return null;
        $stmt->bind_param("s", $districtName);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row ? (int)$row['district_id'] : null;
    }

    public function getDistricts(): array {
        $res = $this->db->query("SELECT district_id,district_name FROM districts WHERE is_active=1 ORDER BY district_name");
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function createUser($role, $data) {
        $passwordHash = password_hash($data['password'], PASSWORD_BCRYPT);
        $roleMap = ['buyer' => 'BUYER', 'farmer' => 'FARMER', 'courier' => 'COURIER_PARTNER'];
        $roleKey = strtolower((string)$role);
        $roleEnum = $roleMap[$roleKey] ?? null;
        if (!$roleEnum) return false;

        $status = strtoupper((string)($data['status'] ?? ($roleKey === 'buyer' ? 'ACTIVE' : 'PENDING')));
        if (!in_array($status, ['ACTIVE','PENDING','SUSPENDED','REJECTED'], true)) $status = 'PENDING';
        $districtId = $this->districtIdByName((string)($data['district'] ?? ''));
        if (!$districtId) return false;

        $this->db->begin_transaction();
        try {
            $stmt = $this->db->prepare("INSERT INTO users (role,full_name,email,password_hash,phone,account_status) VALUES (?,?,?,?,?,?)");
            if (!$stmt) throw new RuntimeException('Unable to create user.');
            $phone = (string)($data['phone'] ?? '');
            $stmt->bind_param("ssssss", $roleEnum, $data['name'], $data['email'], $passwordHash, $phone, $status);
            if (!$stmt->execute()) throw new RuntimeException('Unable to create user.');
            $insertId = (int)$this->db->insert_id;
            $stmt->close();

            $address = trim((string)($data['address'] ?? ''));
            if ($roleKey === 'buyer') {
                if (!db_execute("INSERT INTO buyer_profiles(buyer_id,default_address_line1,default_district_id) VALUES(?,?,?)", 'isi', [$insertId,$address,$districtId])) throw new RuntimeException('Unable to create Buyer profile.');
            } elseif ($roleKey === 'farmer') {
                $verification = $status === 'ACTIVE' ? 'APPROVED' : ($status === 'REJECTED' ? 'REJECTED' : 'PENDING');
                if (!db_execute("INSERT INTO farmer_profiles(farmer_id,pickup_address_line1,district_id,verification_status) VALUES(?,?,?,?)", 'isis', [$insertId,$address,$districtId,$verification])) throw new RuntimeException('Unable to create Farmer profile.');
            } else {
                $contact = trim((string)($data['contact_person'] ?? ''));
                $verification = $status === 'ACTIVE' ? 'APPROVED' : ($status === 'REJECTED' ? 'REJECTED' : 'PENDING');
                if (!db_execute("INSERT INTO courier_partner_profiles(courier_partner_id,organisation_name,contact_person_name,office_address_line1,office_district_id,availability_status,verification_status) VALUES(?,?,?,?,?,'UNAVAILABLE',?)", 'isssis', [$insertId,$data['name'],$contact,$address,$districtId,$verification])) throw new RuntimeException('Unable to create Courier profile.');
            }
            $this->db->commit();
            return $insertId;
        } catch (Throwable $e) {
            $this->db->rollback();
            return false;
        }
    }

    public function updateUserDetails($role, $id, $data) {
        $roleKey = strtolower((string)$role);
        if ($roleKey === 'courier_partner') $roleKey = 'courier';
        $status = strtoupper((string)$data['status']);
        $districtId = $this->districtIdByName((string)($data['district'] ?? ''));
        if (!$districtId) return false;

        $this->db->begin_transaction();
        try {
            if (!db_execute("UPDATE users SET full_name=?,email=?,phone=?,account_status=? WHERE user_id=?", 'ssssi', [$data['name'],$data['email'],$data['phone'],$status,(int)$id])) throw new RuntimeException('Unable to update user.');
            $address = trim((string)($data['address'] ?? ''));
            if ($roleKey === 'buyer') {
                db_execute("UPDATE buyer_profiles SET default_address_line1=?,default_district_id=? WHERE buyer_id=?", 'sii', [$address,$districtId,(int)$id]);
            } elseif ($roleKey === 'farmer') {
                db_execute("UPDATE farmer_profiles SET pickup_address_line1=?,district_id=? WHERE farmer_id=?", 'sii', [$address,$districtId,(int)$id]);
            } elseif ($roleKey === 'courier') {
                $contact = trim((string)($data['contact_person'] ?? ''));
                db_execute("UPDATE courier_partner_profiles SET organisation_name=?,contact_person_name=?,office_address_line1=?,office_district_id=? WHERE courier_partner_id=?", 'sssii', [$data['name'],$contact,$address,$districtId,(int)$id]);
            }
            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            $this->db->rollback();
            return false;
        }
    }

    /**
     * Hard-delete a user account.
     *
     * Harvestly keeps order history, reviews, complaints and financial records, so
     * a user that is referenced anywhere must be suspended instead of deleted.
     * The caller is responsible for reading canDeleteUser() before showing a
     * Delete control.
     */
    public function deleteUser($role, $id) {
        $id = (int)$id;
        if ($id <= 0) return false;

        $blockers = [
            'orders' => 'SELECT COUNT(*) FROM orders WHERE buyer_id = ? OR farmer_id = ?',
            'products' => 'SELECT COUNT(*) FROM products WHERE farmer_id = ?',
            'deliveries' => 'SELECT COUNT(*) FROM deliveries WHERE courier_partner_id = ?',
            'complaints' => 'SELECT COUNT(*) FROM complaints WHERE complainant_user_id = ?',
            'reviews' => 'SELECT COUNT(*) FROM reviews WHERE buyer_id = ? OR farmer_id = ?',
            'earnings' => 'SELECT COUNT(*) FROM earnings WHERE beneficiary_user_id = ?',
            'coverage routes' => 'SELECT COUNT(*) FROM courier_coverage_routes WHERE courier_partner_id = ?',
        ];
        foreach ($blockers as $query) {
            if ((int)db_scalar($query, 'ii', [$id, $id], 0) > 0) return false;
        }

        $this->db->begin_transaction();
        try {
            if (!db_execute('DELETE FROM cart_items WHERE cart_id IN (SELECT cart_id FROM carts WHERE buyer_id = ?)', 'i', [$id])) {
                throw new RuntimeException('Cart could not be cleared.');
            }
            db_execute('DELETE FROM carts WHERE buyer_id = ?', 'i', [$id]);
            db_execute('DELETE FROM buyer_profiles WHERE buyer_id = ?', 'i', [$id]);
            db_execute('DELETE FROM farmer_profiles WHERE farmer_id = ?', 'i', [$id]);
            db_execute('DELETE FROM courier_partner_profiles WHERE courier_partner_id = ?', 'i', [$id]);
            db_execute('DELETE FROM verification_documents WHERE user_id = ?', 'i', [$id]);
            db_execute('DELETE FROM notifications WHERE user_id = ?', 'i', [$id]);
            if (!db_execute("DELETE FROM users WHERE user_id = ?", 'i', [$id])) {
                throw new RuntimeException('User could not be deleted.');
            }
            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            $this->db->rollback();
            return false;
        }
    }

    /**
     * Why a user account cannot be hard-deleted, or an empty string when it can.
     */
    public function deleteBlocker($id): string {
        $id = (int)$id;
        $checks = [
            'order' => 'SELECT COUNT(*) FROM orders WHERE buyer_id = ? OR farmer_id = ?',
            'product listing' => 'SELECT COUNT(*) FROM products WHERE farmer_id = ?',
            'delivery' => 'SELECT COUNT(*) FROM deliveries WHERE courier_partner_id = ?',
            'complaint' => 'SELECT COUNT(*) FROM complaints WHERE complainant_user_id = ?',
            'review' => 'SELECT COUNT(*) FROM reviews WHERE buyer_id = ? OR farmer_id = ?',
            'earning' => 'SELECT COUNT(*) FROM earnings WHERE beneficiary_user_id = ?',
            'coverage route' => 'SELECT COUNT(*) FROM courier_coverage_routes WHERE courier_partner_id = ?',
        ];
        foreach ($checks as $label => $query) {
            if ((int)db_scalar($query, 'ii', [$id, $id], 0) > 0) {
                return 'This user has a ' . $label . ' record and cannot be deleted. Suspend the account instead.';
            }
        }
        return '';
    }

    public function updateUserStatus($userType, $userId, $status) {
        $allowed = ['PENDING', 'ACTIVE', 'REJECTED', 'SUSPENDED'];
        $statusUpper = strtoupper(trim((string)$status));
        if (!in_array($statusUpper, $allowed, true)) return false;
        $stmt = $this->db->prepare("UPDATE users SET account_status = ? WHERE user_id = ?");
        if (!$stmt) return false;
        $stmt->bind_param("si", $statusUpper, $userId);
        $res = $stmt->execute();
        $stmt->close();
        return $res;
    }

    /* ------------------------------------------------------------------
     * 3. FARMER & COURIER APPROVALS
     * ------------------------------------------------------------------ */
    public function getPendingFarmers() {
        $result = $this->db->query("
            SELECT u.user_id as id, u.full_name, u.email, u.phone, u.account_status as status, u.created_at,
                   fp.farm_name, fp.nic_number, d.district_name as district, vd.document_id, vd.stored_file_path as id_document_path, vd.original_file_name
            FROM users u
            JOIN farmer_profiles fp ON u.user_id = fp.farmer_id
            LEFT JOIN districts d ON fp.district_id = d.district_id
            LEFT JOIN verification_documents vd ON u.user_id = vd.user_id
            WHERE u.role = 'FARMER' AND (u.account_status IN ('PENDING', 'REJECTED') OR fp.verification_status = 'PENDING')
            GROUP BY u.user_id
            ORDER BY u.created_at DESC
        ");
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getPendingCouriers() {
        $result = $this->db->query("
            SELECT u.user_id as id,
                   COALESCE(NULLIF(cp.organisation_name, ''), u.full_name) as company_name,
                   cp.contact_person_name as contact_person, cp.nic_number, u.email, u.phone,
                   u.account_status as status, u.created_at, cp.verification_status,
                   d.district_name as district,
                   vd.document_id, vd.stored_file_path as verification_document_path, vd.original_file_name
            FROM users u
            JOIN courier_partner_profiles cp ON u.user_id = cp.courier_partner_id
            LEFT JOIN districts d ON cp.office_district_id = d.district_id
            LEFT JOIN verification_documents vd ON u.user_id = vd.user_id
            WHERE u.role = 'COURIER_PARTNER'
              AND (u.account_status IN ('PENDING', 'REJECTED') OR cp.verification_status = 'PENDING')
            GROUP BY u.user_id
            ORDER BY u.created_at DESC
        ");
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    /** Approved, active Courier Partner organisations for the Admin override dropdown. */
    public function getApprovedCouriers() {
        return db_fetch_all(
            "SELECT cp.courier_partner_id AS id,
                    COALESCE(NULLIF(cp.organisation_name, ''), u.full_name) AS organisation_name,
                    d.district_name AS office_district
             FROM courier_partner_profiles cp
             JOIN users u ON u.user_id = cp.courier_partner_id
             LEFT JOIN districts d ON d.district_id = cp.office_district_id
             WHERE u.role = 'COURIER_PARTNER'
               AND u.account_status = 'ACTIVE'
               AND cp.verification_status = 'APPROVED'
             ORDER BY cp.organisation_name"
        );
    }

    public function updateFarmerVerification($id, $status, $reason = null) {
        $userStatus = ($status === 'approved' || $status === 'APPROVED') ? 'ACTIVE' : 'REJECTED';
        $statusUpper = strtoupper($status);

        $stmtU = $this->db->prepare("UPDATE users SET account_status = ? WHERE user_id = ?");
        if ($stmtU) {
            $stmtU->bind_param("si", $userStatus, $id);
            $stmtU->execute();
            $stmtU->close();
        }

        $stmtP = $this->db->prepare("UPDATE farmer_profiles SET verification_status = ? WHERE farmer_id = ?");
        if ($stmtP) {
            $stmtP->bind_param("si", $statusUpper, $id);
            $stmtP->execute();
            $stmtP->close();
        }

        $stmtV = $this->db->prepare("UPDATE verification_documents SET status = ?, rejection_reason = ? WHERE user_id = ?");
        if ($stmtV) {
            $stmtV->bind_param("ssi", $statusUpper, $reason, $id);
            $res = $stmtV->execute();
            $stmtV->close();
            return $res;
        }
        return false;
    }

    public function updateCourierVerification($id, $status, $reason = null) {
        $userStatus = ($status === 'approved' || $status === 'APPROVED') ? 'ACTIVE' : 'REJECTED';
        $statusUpper = strtoupper($status);

        $stmtU = $this->db->prepare("UPDATE users SET account_status = ? WHERE user_id = ?");
        if ($stmtU) {
            $stmtU->bind_param("si", $userStatus, $id);
            $stmtU->execute();
            $stmtU->close();
        }

        $stmtC = $this->db->prepare("UPDATE courier_partner_profiles SET verification_status = ? WHERE courier_partner_id = ?");
        if ($stmtC) {
            $stmtC->bind_param("si", $statusUpper, $id);
            $stmtC->execute();
            $stmtC->close();
        }

        $stmtV = $this->db->prepare("UPDATE verification_documents SET status = ?, rejection_reason = ? WHERE user_id = ?");
        if ($stmtV) {
            $stmtV->bind_param("ssi", $statusUpper, $reason, $id);
            $res = $stmtV->execute();
            $stmtV->close();
            return $res;
        }
        return false;
    }

    /* ------------------------------------------------------------------
     * 4. PRODUCT MANAGEMENT & LISTINGS MONITOR
     * ------------------------------------------------------------------ */
    public function getAllProducts() {
        $result = $this->db->query("
            SELECT p.product_id as id, p.product_name as title, p.unit_price as price_per_unit, p.unit_label as unit_type,
                   p.available_quantity, p.listing_status as status, p.listing_type,
                     c.category_name as category, u.full_name as farmer_name, d.district_name as district
            FROM products p
            JOIN product_categories c ON p.category_id = c.category_id
            JOIN users u ON p.farmer_id = u.user_id
                 LEFT JOIN farmer_profiles fp ON p.farmer_id = fp.farmer_id
                 LEFT JOIN districts d ON fp.district_id = d.district_id
            ORDER BY p.created_at DESC
        ");
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    /*
     * products.listing_status is an ENUM of
     * ACTIVE, INACTIVE, EXPIRED, SOLD_OUT - anything else is rejected so the
     * UPDATE can never fail because of an invalid enum value.
     */
    public function updateProductStatus($productId, $status) {
        $allowed = ['ACTIVE', 'INACTIVE', 'EXPIRED', 'SOLD_OUT'];
        $statusUpper = strtoupper(trim((string)$status));
        if (!in_array($statusUpper, $allowed, true)) {
            return false;
        }
        $stmt = $this->db->prepare("UPDATE products SET listing_status = ? WHERE product_id = ?");
        if (!$stmt) return false;
        $stmt->bind_param("si", $statusUpper, $productId);
        $res = $stmt->execute();
        $stmt->close();
        return $res;
    }

    /* ------------------------------------------------------------------
     * 5. PRODUCT CATEGORIES (ADMIN CRUD FEATURE)
     * ------------------------------------------------------------------ */
    public function getAllCategories($search = '', $statusFilter = 'all') {
        $sql = "SELECT category_id as id, category_name, description, is_active as status, created_at FROM product_categories WHERE 1=1";
        $types = '';
        $params = [];
        if ($search !== '') {
            $sql .= " AND category_name LIKE ?";
            $types .= 's';
            $params[] = '%' . $search . '%';
        }
        if ($statusFilter !== 'all') {
            $sql .= " AND is_active = ?";
            $types .= 'i';
            $params[] = ($statusFilter === 'active' || $statusFilter === '1') ? 1 : 0;
        }
        $sql .= " ORDER BY category_id DESC";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) return [];
        if ($params) $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        $stmt->close();

        foreach ($rows as &$row) {
            $row['status'] = ($row['status'] == 1) ? 'active' : 'inactive';
            $row['description'] = $row['description'] ?? '';
        }

        return $rows;
    }

    public function getCategoryById($id) {
        $stmt = $this->db->prepare("SELECT category_id as id, category_name, description, is_active as status FROM product_categories WHERE category_id = ?");
        if (!$stmt) return null;
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res ? $res->fetch_assoc() : null;
        $stmt->close();

        if ($row) {
            $row['status'] = ($row['status'] == 1) ? 'active' : 'inactive';
            $row['description'] = $row['description'] ?? '';
        }
        return $row;
    }

    public function createCategory($name, $description = '', $status = 'active') {
        $activeVal = ($status === 'active' || $status === '1') ? 1 : 0;
        $stmt = $this->db->prepare("INSERT INTO product_categories (category_name, description, is_active) VALUES (?, ?, ?)");
        if (!$stmt) return false;
        $stmt->bind_param("ssi", $name, $description, $activeVal);
        $res = $stmt->execute();
        $stmt->close();
        return $res;
    }

    public function updateCategory($id, $name, $description = '', $status = 'active') {
        $activeVal = ($status === 'active' || $status === '1') ? 1 : 0;
        $stmt = $this->db->prepare("UPDATE product_categories SET category_name = ?, description = ?, is_active = ? WHERE category_id = ?");
        if (!$stmt) return false;
        $stmt->bind_param("ssii", $name, $description, $activeVal, $id);
        $res = $stmt->execute();
        $stmt->close();
        return $res;
    }

    public function deleteCategory($id) {
        $stmt = $this->db->prepare("DELETE FROM product_categories WHERE category_id = ?");
        if (!$stmt) return false;
        $stmt->bind_param("i", $id);
        $res = $stmt->execute();
        $stmt->close();
        return $res;
    }

    public function deactivateCategory($id) {
        $stmt = $this->db->prepare("UPDATE product_categories SET is_active = 0 WHERE category_id = ?");
        if (!$stmt) return false;
        $stmt->bind_param("i", $id);
        $res = $stmt->execute();
        $stmt->close();
        return $res;
    }

    /* ------------------------------------------------------------------
     * 6. ORDERS & MANUAL OVERRIDE
     * ------------------------------------------------------------------ */
    public function getAllOrders() {
        /*
         * payments has no unique key on order_id, so the payment status is read
         * through a scalar sub-select to avoid duplicating order rows.
         */
        $result = $this->db->query("
            SELECT o.order_id as id,
                   CONCAT('ORD-', YEAR(o.created_at), '-', o.order_id) as order_number,
                   o.grand_total as total_amount,
                   o.delivery_fee, o.order_status as status, o.created_at,
                   o.delivery_address_line1 as delivery_address,
                   d.courier_partner_id as courier_id,
                   b.full_name as buyer_name,
                   f.full_name as farmer_name,
                   COALESCE(cp.organisation_name, cp.full_name) as courier_name,
                   dest.district_name as destination_district,
                   orig.district_name as origin_district,
                   (SELECT pm.payment_status FROM payments pm
                     WHERE pm.order_id = o.order_id ORDER BY pm.payment_id DESC LIMIT 1) as payment_status
            FROM orders o
            JOIN users b ON o.buyer_id = b.user_id
            JOIN users f ON o.farmer_id = f.user_id
            LEFT JOIN deliveries d ON o.order_id = d.order_id
            LEFT JOIN courier_partner_profiles cp ON cp.courier_partner_id = d.courier_partner_id
            LEFT JOIN users cpu ON cpu.user_id = d.courier_partner_id
            LEFT JOIN districts dest ON dest.district_id = o.destination_district_id
            LEFT JOIN farmer_profiles fp ON fp.farmer_id = o.farmer_id
            LEFT JOIN districts orig ON orig.district_id = fp.district_id
            ORDER BY o.created_at DESC, o.order_id DESC
        ");
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    /**
     * Admin fallback / override for an order whose automatic Courier Partner
     * assignment failed (Pending Assignment).
     *
     * The override is recorded in delivery_assignment_offers, order_status_history
     * and notifications so the Courier Partner side audit trail stays complete,
     * and the COURIER_DELIVERY earning is created once the order is assigned.
     */
    public function overrideDeliveryAssignment($orderId, $courierId) {
        $orderId = (int)$orderId;
        $courierId = (int)$courierId;
        $adminId = (int)($_SESSION['user_id'] ?? 1);
        if ($orderId <= 0 || $courierId <= 0) return false;

        // The chosen Courier Partner must be an approved, active organisation.
        $eligible = (int)db_scalar(
            "SELECT COUNT(*)
             FROM courier_partner_profiles cp
             JOIN users u ON u.user_id = cp.courier_partner_id
             WHERE cp.courier_partner_id = ?
               AND u.role = 'COURIER_PARTNER'
               AND u.account_status = 'ACTIVE'
               AND cp.verification_status = 'APPROVED'",
            'i',
            [$courierId],
            0
        );
        if ($eligible < 1) return false;

        $order = db_fetch_one(
            "SELECT order_id, delivery_fee, order_status FROM orders WHERE order_id = ? LIMIT 1",
            'i',
            [$orderId]
        );
        if (!$order) return false;

        $this->db->begin_transaction();
        try {
            if (!db_execute(
                "INSERT INTO deliveries (order_id, courier_partner_id, delivery_status, assigned_at)
                 VALUES (?, ?, 'ASSIGNED', NOW())
                 ON DUPLICATE KEY UPDATE
                   courier_partner_id = VALUES(courier_partner_id),
                   delivery_status = 'ASSIGNED',
                   assigned_at = NOW()",
                'ii',
                [$orderId, $courierId]
            )) {
                throw new RuntimeException('Delivery record could not be updated.');
            }

            // Close any offers still waiting for a response on this order.
            db_execute(
                "UPDATE delivery_assignment_offers SET offer_status = 'CANCELLED', responded_at = NOW()
                 WHERE order_id = ? AND offer_status = 'PENDING'",
                'i',
                [$orderId]
            );

            // Record the Admin assignment in the offer audit trail.
            if ((int)db_scalar(
                'SELECT COUNT(*) FROM delivery_assignment_offers WHERE order_id = ? AND courier_partner_id = ?',
                'ii',
                [$orderId, $courierId],
                0
            ) === 0) {
                db_execute(
                    "INSERT INTO delivery_assignment_offers
                     (order_id, courier_partner_id, offer_status, offered_at, expires_at, responded_at)
                     VALUES (?, ?, 'ACCEPTED', NOW(), NOW(), NOW())",
                    'ii',
                    [$orderId, $courierId]
                );
            }

            if (!db_execute(
                "UPDATE orders SET order_status = 'ASSIGNED' WHERE order_id = ?",
                'i',
                [$orderId]
            )) {
                throw new RuntimeException('Order could not be updated.');
            }

            if (!recordOrderStatus($orderId, 'ASSIGNED', $adminId, 'Courier Partner assigned manually by Admin')) {
                throw new RuntimeException('Status history could not be recorded.');
            }

            $deliveryFee = (float)$order['delivery_fee'];
            if ($deliveryFee > 0 && (int)db_scalar(
                "SELECT COUNT(*) FROM earnings
                 WHERE order_id = ? AND beneficiary_type = 'COURIER_PARTNER' AND earning_type = 'COURIER_DELIVERY'",
                'i',
                [$orderId],
                0
            ) === 0) {
                db_execute(
                    "INSERT INTO earnings
                     (order_id, beneficiary_type, beneficiary_user_id, earning_type, amount, earning_status)
                     VALUES (?, 'COURIER_PARTNER', ?, 'COURIER_DELIVERY', ?, 'HELD')",
                    'iid',
                    [$orderId, $courierId, $deliveryFee]
                );
            }

            notifyUser($courierId, 'DELIVERY_ASSIGNMENT', 'New delivery request',
                'A Harvestly Admin assigned order ' . orderPublicId($orderId) . ' to your organisation.', $orderId);

            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            $this->db->rollback();
            return false;
        }
    }

    /* ------------------------------------------------------------------
     * 7. DELIVERIES MANAGEMENT
     * ------------------------------------------------------------------ */
    public function getAllDeliveries() {
        $result = $this->db->query("
            SELECT d.delivery_id as id, o.order_id,
                   CONCAT('ORD-', YEAR(o.created_at), '-', o.order_id) as order_number,
                   b.full_name as buyer_name, dist_dest.district_name as destination_district,
                   f.full_name as farmer_name, dist_orig.district_name as origin_district,
                   COALESCE(cp.organisation_name, cu.full_name) as courier_name,
                   o.delivery_fee, d.delivery_status, d.created_at as order_date,
                   d.assigned_at, d.picked_up_at, d.in_transit_at, d.out_for_delivery_at,
                   d.delivered_at, d.completed_at
            FROM deliveries d
            JOIN orders o ON o.order_id = d.order_id
            JOIN users b ON o.buyer_id = b.user_id
            JOIN users f ON o.farmer_id = f.user_id
            LEFT JOIN courier_partner_profiles cp ON cp.courier_partner_id = d.courier_partner_id
            LEFT JOIN users cu ON cu.user_id = d.courier_partner_id
            LEFT JOIN districts dist_dest ON dist_dest.district_id = o.destination_district_id
            LEFT JOIN farmer_profiles fp ON fp.farmer_id = o.farmer_id
            LEFT JOIN districts dist_orig ON dist_orig.district_id = fp.district_id
            ORDER BY d.updated_at DESC
        ");
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    /* ------------------------------------------------------------------
     * 8. PENDING ASSIGNMENTS
     * ------------------------------------------------------------------ */
    public function getPendingAssignments() {
        $result = $this->db->query("
            SELECT o.order_id as id, CONCAT('ORD-', YEAR(o.created_at), '-', o.order_id) as order_number,
                   b.full_name as buyer_name, dist_dest.district_name as destination_district,
                   f.full_name as farmer_name, dist_orig.district_name as origin_district,
                   o.order_status as reason
            FROM orders o
            JOIN users b ON o.buyer_id = b.user_id
            JOIN users f ON o.farmer_id = f.user_id
            LEFT JOIN farmer_profiles fp ON f.user_id = fp.farmer_id
            LEFT JOIN districts dist_orig ON fp.district_id = dist_orig.district_id
            LEFT JOIN districts dist_dest ON o.destination_district_id = dist_dest.district_id
            LEFT JOIN deliveries d ON o.order_id = d.order_id
            WHERE d.courier_partner_id IS NULL OR o.order_status = 'PENDING_ASSIGNMENT'
            GROUP BY o.order_id
            ORDER BY o.created_at DESC
        ");
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    /* ------------------------------------------------------------------
     * 9. DISTRICT DISTANCE REFERENCE DATA
     * ------------------------------------------------------------------ */
    public function getAllDistricts() {
        return db_fetch_all(
            "SELECT district_id, district_name, province_name, is_active
             FROM districts ORDER BY district_name"
        );
    }

    public function getAllDistrictDistances($fromDistrict = '', $toDistrict = '') {
        $sql = "
            SELECT dd.distance_id as id, d1.district_name as from_district, d2.district_name as to_district, dd.distance_km
            FROM district_distances dd
            JOIN districts d1 ON dd.from_district_id = d1.district_id
            JOIN districts d2 ON dd.to_district_id = d2.district_id
            WHERE 1 = 1";
        $params = [];
        $types = '';

        if ($fromDistrict !== '') {
            $sql .= " AND d1.district_name LIKE ?";
            $params[] = '%' . $fromDistrict . '%';
            $types .= 's';
        }
        if ($toDistrict !== '') {
            $sql .= " AND d2.district_name LIKE ?";
            $params[] = '%' . $toDistrict . '%';
            $types .= 's';
        }

        $sql .= " ORDER BY d1.district_name ASC, d2.district_name ASC";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) return [];
        if ($params) $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $distances = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        $stmt->close();
        return $distances;
    }

    public function createDistrictDistance($from, $to, $km) {
        $fId = 0; $tId = 0;
        $stmtF = $this->db->prepare("SELECT district_id FROM districts WHERE district_name = ?");
        if ($stmtF) {
            $stmtF->bind_param("s", $from);
            $stmtF->execute();
            $res = $stmtF->get_result();
            if ($row = $res->fetch_assoc()) $fId = (int)$row['district_id'];
            $stmtF->close();
        }

        $stmtT = $this->db->prepare("SELECT district_id FROM districts WHERE district_name = ?");
        if ($stmtT) {
            $stmtT->bind_param("s", $to);
            $stmtT->execute();
            $res = $stmtT->get_result();
            if ($row = $res->fetch_assoc()) $tId = (int)$row['district_id'];
            $stmtT->close();
        }

        if ($fId && $tId) {
            $stmt = $this->db->prepare("INSERT INTO district_distances (from_district_id, to_district_id, distance_km) VALUES (?, ?, ?)");
            if ($stmt) {
                $kmVal = (float)$km;
                $stmt->bind_param("iid", $fId, $tId, $kmVal);
                $res = $stmt->execute();
                $stmt->close();
                return $res;
            }
        }
        return false;
    }

    public function updateDistrictDistance($id, $from, $to, $km) {
        $stmt = $this->db->prepare("UPDATE district_distances SET distance_km = ? WHERE distance_id = ?");
        if (!$stmt) return false;
        $kmVal = (float)$km;
        $stmt->bind_param("di", $kmVal, $id);
        $res = $stmt->execute();
        $stmt->close();
        return $res;
    }

    public function deleteDistrictDistance($id) {
        $stmt = $this->db->prepare("DELETE FROM district_distances WHERE distance_id = ?");
        if (!$stmt) return false;
        $stmt->bind_param("i", $id);
        $res = $stmt->execute();
        $stmt->close();
        return $res;
    }

    /* ------------------------------------------------------------------
    * 10. COMPLAINTS & ISSUES
     * ------------------------------------------------------------------ */
    public function getAllComplaints() {
        $result = $this->db->query("
                 SELECT c.complaint_id as id, CONCAT('ORD-', YEAR(o.created_at), '-', o.order_id) as order_number, o.order_id,
                     u.full_name as complainant_name, c.complainant_role as user_role, c.category,
                     c.complaint_status as status, c.description, c.evidence_path, c.created_at
            FROM complaints c
            JOIN orders o ON c.order_id = o.order_id
            JOIN users u ON c.complainant_user_id = u.user_id
            ORDER BY c.created_at DESC
        ");
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    /**
     * Admin reviews and updates one complaint in the single common issue workflow.
     * complaints.complaint_status is an ENUM of OPEN, UNDER_REVIEW, RESOLVED, REJECTED.
     */
    public function resolveComplaint($complaintId, $status, $adminResponse) {
        $allowed = ['OPEN', 'UNDER_REVIEW', 'RESOLVED', 'REJECTED'];
        $statusUpper = strtoupper(trim((string)$status));
        if (!in_array($statusUpper, $allowed, true)) return false;

        $adminId = (int)($_SESSION['user_id'] ?? 0);
        $adminResponse = trim((string)$adminResponse);

        $this->db->begin_transaction();
        try {
            if (!db_execute(
                'UPDATE complaints
                 SET complaint_status = ?, admin_response = ?,
                     reviewed_by = ?, reviewed_at = NOW(),
                     resolved_at = CASE WHEN ? IN (\'RESOLVED\', \'REJECTED\') THEN NOW() ELSE resolved_at END
                 WHERE complaint_id = ?',
                'ssssi',
                [$statusUpper, $adminResponse !== '' ? $adminResponse : null, $adminId ?: null, $statusUpper, $complaintId]
            )) {
                throw new RuntimeException('Complaint could not be updated.');
            }

            // Tell the complainant the outcome through a database notification.
            $complaint = db_fetch_one(
                'SELECT order_id, complainant_user_id FROM complaints WHERE complaint_id = ? LIMIT 1',
                'i',
                [$complaintId]
            );
            if ($complaint) {
                notifyUser(
                    (int)$complaint['complainant_user_id'],
                    'COMPLAINT_UPDATE',
                    'Your issue was reviewed',
                    'Your reported issue is now ' . harvestlyStatusLabel($statusUpper)
                        . ($adminResponse !== '' ? ': ' . $adminResponse : '.'),
                    (int)$complaint['order_id']
                );
            }

            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            $this->db->rollback();
            return false;
        }
    }

    /* ------------------------------------------------------------------
     * 11. PAYMENTS
     * ------------------------------------------------------------------ */
    public function getAllPayments() {
        $result = $this->db->query("
            SELECT p.payment_id as id, CONCAT('ORD-', YEAR(o.created_at), '-', o.order_id) as order_number, o.order_id,
                   b.full_name as buyer_name, p.amount, p.provider, p.provider_reference, p.payment_status, p.created_at, p.paid_at
            FROM payments p
            JOIN orders o ON p.order_id = o.order_id
            JOIN users b ON o.buyer_id = b.user_id
            ORDER BY p.created_at DESC
        ");
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    /* ------------------------------------------------------------------
     * 12. SETTLEMENTS & EARNINGS
     * ------------------------------------------------------------------ */
    public function getSettlements() {
        $result = $this->db->query("
            SELECT CONCAT('SET-', LPAD(s.settlement_id, 4, '0'), '-', LPAD(si.settlement_item_id, 3, '0')) as reference_no,
                   LOWER(e.beneficiary_type) as user_type,
                   e.beneficiary_user_id as user_id,
                   e.amount,
                   LOWER(s.settlement_status) as status,
                   s.created_at as settlement_date
            FROM settlement_items si
            JOIN settlements s ON si.settlement_id = s.settlement_id
            JOIN earnings e ON si.earning_id = e.earning_id
            ORDER BY s.created_at DESC, si.settlement_item_id DESC
        ");
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    /** Real earnings totals used by the Settlements monitoring page. */
    public function getSettlementSummary(): array {
        $row = db_fetch_one(
            "SELECT
                COALESCE(SUM(CASE WHEN earning_status = 'PENDING_PAYOUT' THEN amount ELSE 0 END), 0) AS pending_payout,
                COALESCE(SUM(CASE WHEN earning_status = 'HELD' THEN amount ELSE 0 END), 0) AS held,
                COALESCE(SUM(CASE WHEN earning_status = 'PAID' THEN amount ELSE 0 END), 0) AS paid
             FROM earnings"
        ) ?: [];

        $byType = db_fetch_all(
            "SELECT beneficiary_type, COALESCE(SUM(amount), 0) AS total, COUNT(*) AS entries
             FROM earnings
             WHERE earning_status IN ('PENDING_PAYOUT', 'PAID')
             GROUP BY beneficiary_type
             ORDER BY beneficiary_type"
        );

        $platformFees = (float)db_scalar(
            "SELECT COALESCE(SUM(CASE WHEN earning_type = 'FARMER_MARKETPLACE_FEE' THEN amount
                                      WHEN earning_type = 'BUYER_SERVICE_FEE' THEN amount ELSE 0 END), 0)
             FROM earnings"
        );
        $gross = (float)db_scalar(
            "SELECT COALESCE(SUM(product_subtotal + buyer_service_fee), 0) FROM orders
             WHERE order_status IN ('DELIVERED','COMPLETED')"
        );

        return [
            'pending_payout' => (float)($row['pending_payout'] ?? 0),
            'held' => (float)($row['held'] ?? 0),
            'paid' => (float)($row['paid'] ?? 0),
            'by_type' => $byType,
            'platform_fees' => $platformFees,
            'platform_share_percent' => $gross > 0 ? round(($platformFees / $gross) * 100, 2) : 0.0,
        ];
    }

    /* ------------------------------------------------------------------
     * 13. REPORTS & SETTINGS & NOTIFICATIONS
     * ------------------------------------------------------------------ */
    public function generateReportData($reportType, $startDate, $endDate) {
        if ($reportType === 'settlements') {
            $sql = "
                SELECT CONCAT('SET-', LPAD(s.settlement_id, 4, '0'), '-', LPAD(si.settlement_item_id, 3, '0')) as reference_no,
                       LOWER(e.beneficiary_type) as user_type,
                       e.beneficiary_user_id as user_id,
                       e.amount,
                       LOWER(s.settlement_status) as status,
                       s.created_at as settlement_date
                FROM settlement_items si
                JOIN settlements s ON si.settlement_id = s.settlement_id
                JOIN earnings e ON si.earning_id = e.earning_id
                WHERE DATE(s.created_at) BETWEEN ? AND ?
                ORDER BY s.created_at DESC, si.settlement_item_id DESC";
        } elseif ($reportType === 'farmers') {
            $sql = "
                SELECT full_name, email, phone, LOWER(account_status) as status, created_at
                FROM users
                WHERE role = 'FARMER' AND DATE(created_at) BETWEEN ? AND ?
                ORDER BY created_at DESC";
        } else {
            $sql = "
                SELECT order_id as order_number, grand_total as total_amount, delivery_fee,
                       LOWER(order_status) as status, created_at
                FROM orders
                WHERE DATE(created_at) BETWEEN ? AND ?
                ORDER BY created_at DESC";
        }

        $stmt = $this->db->prepare($sql);
        if (!$stmt) return [];
        $stmt->bind_param("ss", $startDate, $endDate);
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
        $stmt->close();
        return $rows;
    }

    public function getNotificationsLog() {
        $result = $this->db->query("
            SELECT n.notification_id as id,
                   CASE
                       WHEN n.notification_type LIKE '%FARMER%' THEN 'farmers'
                       WHEN n.notification_type LIKE '%COURIER%' THEN 'couriers'
                       WHEN u.role = 'BUYER' THEN 'buyers'
                       ELSE 'all'
                   END as recipient_scope,
                   n.title as subject, n.message, n.created_at as sent_at
            FROM notifications n
            LEFT JOIN users u ON n.user_id = u.user_id
            ORDER BY n.created_at DESC
        ");
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    /**
     * Send a database notification to a recipient scope.
     * Harvestly notifications are local database rows only - no push, WebSocket,
     * SMS or email service is used.
     *
     * @param string $scope  all | buyers | farmers | couriers
     * @param int    $recipientId  Used only for the "single user" scope.
     * @return int  number of notifications created
     */
    public function createNotification($scope, $recipientId, $subject, $message) {
        $subject = trim((string)$subject);
        $message = trim((string)$message);
        if ($subject === '' || $message === '') return 0;

        $roleByScope = [
            'all'      => null,
            'buyers'   => 'BUYER',
            'farmers'  => 'FARMER',
            'couriers' => 'COURIER_PARTNER',
        ];
        if (!array_key_exists($scope, $roleByScope)) return 0;

        $role = $roleByScope[$scope];
        if ($role === null && (int)$recipientId > 0) {
            $user = db_fetch_one(
                "SELECT user_id FROM users WHERE user_id = ? AND account_status = 'ACTIVE' LIMIT 1",
                'i',
                [(int)$recipientId]
            );
            if (!$user) return 0;
            $recipients = [(int)$user['user_id']];
        } elseif ($role === null) {
            $recipients = array_map(
                static fn(array $r): int => (int)$r['user_id'],
                db_fetch_all("SELECT user_id FROM users WHERE account_status = 'ACTIVE' ORDER BY user_id")
            );
        } else {
            $recipients = array_map(
                static fn(array $r): int => (int)$r['user_id'],
                db_fetch_all(
                    "SELECT user_id FROM users WHERE role = ? AND account_status = 'ACTIVE' ORDER BY user_id",
                    's',
                    [$role]
                )
            );
        }

        $created = 0;
        foreach ($recipients as $userId) {
            if (db_execute(
                'INSERT INTO notifications (user_id, notification_type, title, message, is_read) VALUES (?, ?, ?, ?, 0)',
                'isss',
                [$userId, 'ADMIN_BROADCAST', $subject, $message]
            )) {
                $created++;
            }
        }
        return $created;
    }

    public function getPlatformSettings() {
        $result = $this->db->query("SELECT setting_key, setting_value FROM platform_settings");
        $settings = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
        }
        return $settings;
    }

    /**
     * Settings the interim scope fixes by agreement. They are rendered read-only
     * in the Admin settings form and are never overwritten from user input.
     */
    public static function lockedSettings(): array {
        return [
            'buyer_confirmation_hours' => '48',
            'review_window_days'        => '14',
            'max_delivery_attempts'     => '2',
        ];
    }

    /**
     * Validate every value BEFORE writing anything, so a bad value can never
     * leave the settings table half updated.
     *
     * @return true|string  true on success, otherwise the error message.
     */
    public function updatePlatformSettings($settingsArray, $adminId = 1) {
        $locked = self::lockedSettings();
        $clean = [];

        foreach ($settingsArray as $key => $val) {
            if (array_key_exists($key, $locked)) continue;
            $val = trim((string)$val);
            if ($val === '' || !is_numeric($val) || (float)$val < 0) {
                return 'The value for "' . $key . '" must be zero or greater.';
            }
            if (str_ends_with($key, '_percent') && (float)$val > 100) {
                return 'A percentage setting cannot be greater than 100.';
            }
            $clean[$key] = $val;
        }
        if (!$clean) return true;

        $this->db->begin_transaction();
        try {
            foreach ($clean as $key => $val) {
                if (!db_execute(
                    "INSERT INTO platform_settings (setting_key, setting_value, value_type, updated_by)
                     VALUES (?, ?, 'DECIMAL', ?)
                     ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_by = VALUES(updated_by)",
                    'ssi',
                    [$key, $val, (int)$adminId]
                )) {
                    throw new RuntimeException('Setting "' . $key . '" could not be saved.');
                }
            }
            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            $this->db->rollback();
            return $e->getMessage();
        }
    }

    public function getAdminProfile($adminId = 1) {
        $stmt = $this->db->prepare("SELECT user_id as id, full_name, email, role, created_at FROM users WHERE user_id = ? AND role = 'ADMIN'");
        if ($stmt) {
            $stmt->bind_param("i", $adminId);
            $stmt->execute();
            $res = $stmt->get_result();
            $profile = $res ? $res->fetch_assoc() : null;
            $stmt->close();
            if ($profile) return $profile;
        }
        return ['id' => 1, 'full_name' => 'System Administrator', 'email' => 'admin@gmail.com', 'role' => 'Administrator', 'created_at' => date('Y-m-d H:i:s')];
    }

    public function updateAdminProfile($adminId, $fullName, $email, $newPassword = null) {
        if ($newPassword) {
            $hash = password_hash($newPassword, PASSWORD_BCRYPT);
            $stmt = $this->db->prepare("UPDATE users SET full_name = ?, email = ?, password_hash = ? WHERE user_id = ?");
            if (!$stmt) return false;
            $stmt->bind_param("sssi", $fullName, $email, $hash, $adminId);
            $res = $stmt->execute();
            $stmt->close();
            return $res;
        } else {
            $stmt = $this->db->prepare("UPDATE users SET full_name = ?, email = ? WHERE user_id = ?");
            if (!$stmt) return false;
            $stmt->bind_param("ssi", $fullName, $email, $adminId);
            $res = $stmt->execute();
            $stmt->close();
            return $res;
        }
    }
}
