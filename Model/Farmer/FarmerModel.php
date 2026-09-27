<?php
/**
 * Harvestly Farmer module - all database access.
 * MySQLi prepared statements only. No PDO, no external services.
 */
require_once __DIR__ . '/../../config/app.php';

final class FarmerModel
{
    /* ------------------------- Identity & profile ------------------------ */

    public function user(int $farmerId): array
    {
        $row = db_fetch_one(
            "SELECT u.user_id, u.full_name, u.email, u.phone, u.account_status, u.created_at,
                    fp.farm_name, fp.pickup_address_line1, fp.pickup_address_line2,
                    fp.pickup_city_town, fp.pickup_postal_code, fp.district_id, fp.verification_status,
                    d.district_name
             FROM users u
             LEFT JOIN farmer_profiles fp ON fp.farmer_id = u.user_id
             LEFT JOIN districts d ON d.district_id = fp.district_id
             WHERE u.user_id = ? AND u.role = 'FARMER'
             LIMIT 1",
            'i',
            [$farmerId]
        );
        return $row ?: ['full_name' => (string)($_SESSION['user_name'] ?? 'Farmer')];
    }

    public function unreadNotifications(int $farmerId): int
    {
        return (int)db_scalar(
            'SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0',
            'i',
            [$farmerId],
            0
        );
    }

    public function saveContact(int $farmerId, string $name, string $phone): bool
    {
        return db_execute(
            "UPDATE users SET full_name = ?, phone = ? WHERE user_id = ? AND role = 'FARMER'",
            'ssi',
            [$name, $phone, $farmerId]
        );
    }

    public function savePickup(int $farmerId, array $d): bool
    {
        return db_execute(
            'UPDATE farmer_profiles
             SET farm_name = ?, pickup_address_line1 = ?, pickup_address_line2 = ?,
                 pickup_city_town = ?, pickup_postal_code = ?
             WHERE farmer_id = ?',
            'sssssi',
            [
                trim((string)($d['farm_name'] ?? '')),
                trim((string)($d['pickup_address_line1'] ?? '')),
                trim((string)($d['pickup_address_line2'] ?? '')),
                trim((string)($d['pickup_city_town'] ?? '')),
                trim((string)($d['pickup_postal_code'] ?? '')),
                $farmerId,
            ]
        );
    }

    public function verificationDocuments(int $userId): array
    {
        return db_fetch_all(
            'SELECT document_id, document_type, original_file_name, status, rejection_reason, reviewed_at
             FROM verification_documents
             WHERE user_id = ?
             ORDER BY created_at DESC',
            'i',
            [$userId]
        );
    }

    /* ---------------------------- Dashboard ------------------------------ */

    public function productStats(int $farmerId): array
    {
        $row = db_fetch_one(
            "SELECT COUNT(*) AS total,
                    COALESCE(SUM(listing_status = 'ACTIVE'), 0) AS active,
                    COALESCE(SUM(available_quantity <= 0), 0) AS sold_out
             FROM products WHERE farmer_id = ?",
            'i',
            [$farmerId]
        ) ?: [];
        return [
            'total' => (int)($row['total'] ?? 0),
            'active' => (int)($row['active'] ?? 0),
            'sold_out' => (int)($row['sold_out'] ?? 0),
        ];
    }

    public function orderStats(int $farmerId): array
    {
        $row = db_fetch_one(
            "SELECT COUNT(*) AS total,
                    COALESCE(SUM(order_status IN ('PAID','ACCEPTED','PREPARING','READY_FOR_DELIVERY')), 0) AS action_required,
                    COALESCE(SUM(order_status IN ('ASSIGNED','PICKED_UP','IN_TRANSIT','OUT_FOR_DELIVERY')), 0) AS in_delivery,
                    COALESCE(SUM(CASE WHEN order_status IN ('DELIVERED','COMPLETED')
                             THEN product_subtotal - farmer_marketplace_fee ELSE 0 END), 0) AS sales
             FROM orders WHERE farmer_id = ?",
            'i',
            [$farmerId]
        ) ?: [];
        return [
            'total' => (int)($row['total'] ?? 0),
            'action_required' => (int)($row['action_required'] ?? 0),
            'in_delivery' => (int)($row['in_delivery'] ?? 0),
            'sales' => (float)($row['sales'] ?? 0),
        ];
    }

    public function recentOrders(int $farmerId, int $limit = 5): array
    {
        $limit = max(1, min(50, $limit));
        return db_fetch_all(
            "SELECT o.order_id, o.order_status, o.grand_total, o.product_subtotal, o.created_at,
                    b.full_name buyer_name
             FROM orders o JOIN users b ON b.user_id = o.buyer_id
             WHERE o.farmer_id = ?
             ORDER BY o.created_at DESC, o.order_id DESC
             LIMIT ?",
            'ii',
            [$farmerId, $limit]
        );
    }

    /* ----------------------------- Products ------------------------------ */

    public function products(int $farmerId): array
    {
        return db_fetch_all(
            "SELECT p.*, c.category_name,
                    (SELECT pi.image_path FROM product_images pi WHERE pi.product_id = p.product_id
                      ORDER BY pi.is_primary DESC, pi.image_id ASC LIMIT 1) AS image_path
             FROM products p
             JOIN product_categories c ON c.category_id = p.category_id
             WHERE p.farmer_id = ?
             ORDER BY p.created_at DESC, p.product_id DESC",
            'i',
            [$farmerId]
        );
    }

    public function productWithCategory(int $productId, int $farmerId): ?array
    {
        return db_fetch_one(
            'SELECT p.*, c.category_name
             FROM products p JOIN product_categories c ON c.category_id = p.category_id
             WHERE p.product_id = ? AND p.farmer_id = ?
             LIMIT 1',
            'ii',
            [$productId, $farmerId]
        );
    }

    public function productImages(int $productId): array
    {
        return db_fetch_all(
            'SELECT image_id, image_path, is_primary FROM product_images WHERE product_id = ? ORDER BY image_id',
            'i',
            [$productId]
        );
    }

    public function shelfLifeReferences(): array
    {
        return db_fetch_all(
            'SELECT shelf_life_reference_id, product_reference_name
             FROM shelf_life_references WHERE is_active = 1
             ORDER BY product_reference_name'
        );
    }

    public function toggleProductStatus(int $productId, int $farmerId, string $status): bool
    {
        if (!in_array($status, ['ACTIVE', 'INACTIVE', 'SOLD_OUT'], true)) return false;
        return db_execute(
            'UPDATE products SET listing_status = ? WHERE product_id = ? AND farmer_id = ?',
            'sii',
            [$status, $productId, $farmerId]
        );
    }

    /* ---------------------------- Inventory ------------------------------ */

    public function inventory(int $farmerId): array
    {
        return db_fetch_all(
            'SELECT product_id, product_name, available_quantity, unit_label, listing_status
             FROM products WHERE farmer_id = ?
             ORDER BY product_name',
            'i',
            [$farmerId]
        );
    }

    public function updateStock(int $productId, int $farmerId, float $quantity): bool
    {
        if ($quantity < 0) return false;
        return db_execute(
            "UPDATE products
             SET available_quantity = ?,
                 listing_status = IF(? <= 0, 'SOLD_OUT', IF(listing_status = 'SOLD_OUT', 'ACTIVE', listing_status))
             WHERE product_id = ? AND farmer_id = ?",
            'ddii',
            [$quantity, $quantity, $productId, $farmerId]
        );
    }

    /* -------------------- Pre-listings / Harvest Soon -------------------- */

    public function harvestSoonListings(int $farmerId): array
    {
        return db_fetch_all(
            "SELECT product_id, product_name, unit_label, available_quantity, harvest_date,
                    available_from_date, listing_status
             FROM products
             WHERE farmer_id = ? AND listing_type = 'HARVEST_SOON'
             ORDER BY harvest_date IS NULL, harvest_date, product_name",
            'i',
            [$farmerId]
        );
    }

    public function updateHarvestDates(int $productId, int $farmerId, string $harvestDate, string $availableFrom): bool
    {
        foreach ([$harvestDate, $availableFrom] as $value) {
            if ($value === '') continue;
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) || date('Y-m-d', strtotime($value)) !== $value) {
                return false;
            }
        }
        return db_execute(
            "UPDATE products SET harvest_date = ?, available_from_date = ?
             WHERE product_id = ? AND farmer_id = ? AND listing_type = 'HARVEST_SOON'",
            'ssii',
            [$harvestDate ?: null, $availableFrom ?: null, $productId, $farmerId]
        );
    }

    /* ------------------------------ Orders ------------------------------- */

    public function orders(int $farmerId): array
    {
        return db_fetch_all(
            'SELECT o.*, b.full_name buyer_name, d.district_name destination_district
             FROM orders o
             JOIN users b ON b.user_id = o.buyer_id
             LEFT JOIN districts d ON d.district_id = o.destination_district_id
             WHERE o.farmer_id = ?
             ORDER BY o.created_at DESC, o.order_id DESC',
            'i',
            [$farmerId]
        );
    }

    public function order(int $orderId, int $farmerId): ?array
    {
        return db_fetch_one(
            'SELECT o.*, b.full_name buyer_name, b.email buyer_email, b.phone buyer_phone,
                    d.district_name destination_district
             FROM orders o
             JOIN users b ON b.user_id = o.buyer_id
             LEFT JOIN districts d ON d.district_id = o.destination_district_id
             WHERE o.order_id = ? AND o.farmer_id = ?
             LIMIT 1',
            'ii',
            [$orderId, $farmerId]
        );
    }

    public function orderItems(int $orderId): array
    {
        return db_fetch_all(
            'SELECT oi.*, p.unit_label
             FROM order_items oi LEFT JOIN products p ON p.product_id = oi.product_id
             WHERE oi.order_id = ?
             ORDER BY oi.order_item_id',
            'i',
            [$orderId]
        );
    }

    /** The shared order status timeline, same rows the Buyer and Admin see. */
    public function orderHistory(int $orderId): array
    {
        return db_fetch_all(
            'SELECT status, note, created_at FROM order_status_history
             WHERE order_id = ? ORDER BY created_at ASC, history_id ASC',
            'i',
            [$orderId]
        );
    }

    public function deliveryForOrder(int $orderId): ?array
    {
        return db_fetch_one(
            "SELECT d.delivery_status, d.delivered_at, d.completed_at,
                    cp.organisation_name, u.phone
             FROM deliveries d
             LEFT JOIN courier_partner_profiles cp ON cp.courier_partner_id = d.courier_partner_id
             LEFT JOIN users u ON u.user_id = d.courier_partner_id
             WHERE d.order_id = ? LIMIT 1",
            'i',
            [$orderId]
        );
    }

    public function deliveryAttempts(int $deliveryId): array
    {
        return db_fetch_all(
            'SELECT attempt_number, attempt_result, notes, attempted_at
             FROM delivery_attempts WHERE delivery_id = ? ORDER BY attempt_number',
            'i',
            [$deliveryId]
        );
    }

    /* ------------------------ Sales & earnings --------------------------- */

    public function sales(int $farmerId): array
    {
        return db_fetch_all(
            "SELECT o.order_id, o.product_subtotal, o.farmer_marketplace_fee, o.delivery_fee,
                    o.order_status, o.created_at, b.full_name buyer_name
             FROM orders o JOIN users b ON b.user_id = o.buyer_id
             WHERE o.farmer_id = ? AND o.order_status IN ('DELIVERED','COMPLETED')
             ORDER BY o.created_at DESC, o.order_id DESC",
            'i',
            [$farmerId]
        );
    }

    public function earnings(int $farmerId): array
    {
        $totals = db_fetch_one(
            "SELECT
                COALESCE(SUM(CASE WHEN earning_status = 'PENDING_PAYOUT' THEN amount ELSE 0 END), 0) AS pending_payout,
                COALESCE(SUM(CASE WHEN earning_status = 'PAID' THEN amount ELSE 0 END), 0) AS paid,
                COALESCE(SUM(CASE WHEN earning_status = 'HELD' THEN amount ELSE 0 END), 0) AS held,
                COALESCE(SUM(CASE WHEN earning_status = 'CANCELLED' THEN amount ELSE 0 END), 0) AS cancelled
             FROM earnings
             WHERE beneficiary_user_id = ? AND beneficiary_type = 'FARMER' AND earning_type = 'FARMER_NET'",
            'i',
            [$farmerId]
        ) ?: [];

        $rows = db_fetch_all(
            "SELECT e.earning_id, e.order_id, e.amount, e.earning_status, e.created_at, e.settled_at
             FROM earnings e
             WHERE e.beneficiary_user_id = ? AND e.beneficiary_type = 'FARMER' AND e.earning_type = 'FARMER_NET'
             ORDER BY e.created_at DESC, e.earning_id DESC",
            'i',
            [$farmerId]
        );

        return [
            'totals' => [
                'pending_payout' => (float)($totals['pending_payout'] ?? 0),
                'paid' => (float)($totals['paid'] ?? 0),
                'held' => (float)($totals['held'] ?? 0),
                'cancelled' => (float)($totals['cancelled'] ?? 0),
            ],
            'rows' => $rows,
        ];
    }

    /* ------------------------------ Reviews ------------------------------ */

    public function reviews(int $farmerId): array
    {
        return db_fetch_all(
            'SELECT r.*, b.full_name buyer_name, o.order_status
             FROM reviews r
             JOIN users b ON b.user_id = r.buyer_id
             JOIN orders o ON o.order_id = r.order_id
             WHERE r.farmer_id = ?
             ORDER BY r.created_at DESC, r.review_id DESC',
            'i',
            [$farmerId]
        );
    }

    public function respondToReview(int $reviewId, int $farmerId, string $response): bool
    {
        $response = trim($response);
        if ($response === '') {
            return db_execute(
                'UPDATE reviews SET farmer_response = NULL, farmer_responded_at = NULL
                 WHERE review_id = ? AND farmer_id = ?',
                'ii',
                [$reviewId, $farmerId]
            );
        }
        if (mb_strlen($response) > 1000) return false;
        return db_execute(
            'UPDATE reviews SET farmer_response = ?, farmer_responded_at = NOW()
             WHERE review_id = ? AND farmer_id = ?',
            'sii',
            [$response, $reviewId, $farmerId]
        );
    }

    /* ---------------------- Complaints / Report Issue -------------------- */

    public function complaintOrders(int $farmerId): array
    {
        return db_fetch_all(
            'SELECT order_id, order_status, grand_total, created_at
             FROM orders WHERE farmer_id = ?
             ORDER BY created_at DESC, order_id DESC',
            'i',
            [$farmerId]
        );
    }

    public function ownsOrder(int $orderId, int $farmerId): bool
    {
        return (bool)db_fetch_one(
            'SELECT order_id FROM orders WHERE order_id = ? AND farmer_id = ? LIMIT 1',
            'ii',
            [$orderId, $farmerId]
        );
    }

    public function addComplaint(int $farmerId, int $orderId, string $category, string $description, ?string $evidence): bool
    {
        if ($category === '' || mb_strlen($category) > 100) return false;
        if ($description === '') return false;
        return db_execute(
            "INSERT INTO complaints
             (order_id, complainant_user_id, complainant_role, category, description, evidence_path, complaint_status)
             VALUES (?, ?, 'FARMER', ?, ?, ?, 'OPEN')",
            'iisss',
            [$orderId, $farmerId, $category, $description, $evidence]
        );
    }

    public function complaints(int $farmerId): array
    {
        return db_fetch_all(
            "SELECT c.*, o.order_id
             FROM complaints c JOIN orders o ON o.order_id = c.order_id
             WHERE c.complainant_user_id = ? AND c.complainant_role = 'FARMER'
             ORDER BY c.created_at DESC, c.complaint_id DESC",
            'i',
            [$farmerId]
        );
    }

    /* -------------------------- Notifications ---------------------------- */

    public function notifications(int $farmerId, int $limit = 100): array
    {
        $limit = max(1, min(200, $limit));
        return db_fetch_all(
            'SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC, notification_id DESC LIMIT ?',
            'ii',
            [$farmerId, $limit]
        );
    }

    public function markNotificationRead(int $notificationId, int $farmerId): bool
    {
        return db_execute(
            'UPDATE notifications SET is_read = 1, read_at = NOW()
             WHERE notification_id = ? AND user_id = ? AND is_read = 0',
            'ii',
            [$notificationId, $farmerId]
        );
    }

    public function markAllNotificationsRead(int $farmerId): bool
    {
        return db_execute(
            'UPDATE notifications SET is_read = 1, read_at = NOW() WHERE user_id = ? AND is_read = 0',
            'i',
            [$farmerId]
        );
    }
}
