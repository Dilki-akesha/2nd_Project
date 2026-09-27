<?php
/**
 * ProductModel - public product catalogue queries used by the Main module.
 * MySQLi only. All public listings come from the database, and only from
 * approved, active Farmers in active categories.
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/helpers.php';

class ProductModel
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = getDBConnection();
    }

    /** Active product category names, for the public category filter. */
    public function getActiveCategories(): array
    {
        $result = $this->db->query("SELECT category_name FROM product_categories WHERE is_active = 1 ORDER BY category_name ASC");
        $categories = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $categories[] = $row['category_name'];
            }
        }
        return $categories;
    }

    /** Active categories with the number of publicly visible listings. */
    public function getCategorySummary(): array
    {
        $result = $this->db->query("
            SELECT c.category_name, COUNT(p.product_id) AS product_count
            FROM product_categories c
            LEFT JOIN products p ON p.category_id = c.category_id AND p.listing_status = 'ACTIVE'
            LEFT JOIN users fu ON fu.user_id = p.farmer_id AND fu.account_status = 'ACTIVE'
            LEFT JOIN farmer_profiles fp ON fp.farmer_id = fu.user_id AND fp.verification_status = 'APPROVED'
            WHERE c.is_active = 1
            GROUP BY c.category_id, c.category_name
            ORDER BY c.category_name ASC
        ");
        if (!$result) return [];
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /** The newest publicly visible listings, used by the landing page. */
    public function getFeaturedProducts(int $limit = 3): array
    {
        $limit = max(1, min(12, $limit));
        $stmt = $this->db->prepare("
            SELECT p.product_id AS id,
                   p.product_name AS title,
                   p.unit_price,
                   p.unit_label,
                   p.listing_type,
                   c.category_name,
                   u.full_name AS farmer_name,
                   d.district_name AS pickup_district,
                   (
                       SELECT pi.image_path FROM product_images pi
                       WHERE pi.product_id = p.product_id
                       ORDER BY pi.is_primary DESC, pi.image_id ASC LIMIT 1
                   ) AS image_path
            FROM products p
            JOIN product_categories c ON c.category_id = p.category_id AND c.is_active = 1
            JOIN users u ON u.user_id = p.farmer_id AND u.account_status = 'ACTIVE'
            JOIN farmer_profiles fp ON fp.farmer_id = u.user_id AND fp.verification_status = 'APPROVED'
            LEFT JOIN districts d ON d.district_id = fp.district_id
            WHERE p.listing_status = 'ACTIVE'
            ORDER BY p.created_at DESC, p.product_id DESC
            LIMIT ?
        ");
        if (!$stmt) return [];
        $stmt->bind_param('i', $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        $stmt->close();
        return $rows;
    }

    public function getActiveProducts($category = 'all', $search = ''): array
    {
        $categories = $this->getActiveCategories();
        if ($category !== 'all' && $category !== '' && !in_array($category, $categories, true)) {
            $category = 'all';
        }

        $sql = "
            SELECT p.product_id AS id,
                   p.product_name AS title,
                   p.unit_price,
                   p.unit_label,
                   p.listing_type,
                   c.category_name,
                   u.full_name AS farmer_name,
                   d.district_name AS pickup_district,
                   (
                       SELECT pi.image_path FROM product_images pi
                       WHERE pi.product_id = p.product_id
                       ORDER BY pi.is_primary DESC, pi.image_id ASC LIMIT 1
                   ) AS image_path
            FROM products p
            JOIN product_categories c ON c.category_id = p.category_id AND c.is_active = 1
            JOIN users u ON u.user_id = p.farmer_id AND u.account_status = 'ACTIVE'
            JOIN farmer_profiles fp ON fp.farmer_id = u.user_id AND fp.verification_status = 'APPROVED'
            LEFT JOIN districts d ON d.district_id = fp.district_id
            WHERE p.listing_status = 'ACTIVE'
        ";

        $params = [];
        $types = '';

        if ($category !== 'all' && $category !== '') {
            $sql .= " AND c.category_name = ?";
            $params[] = $category;
            $types .= 's';
        }

        if ($search !== '') {
            $sql .= " AND (p.product_name LIKE ? OR u.full_name LIKE ? OR c.category_name LIKE ?)";
            $pattern = '%' . $search . '%';
            $params[] = $pattern;
            $params[] = $pattern;
            $params[] = $pattern;
            $types .= 'sss';
        }

        $sql .= " ORDER BY p.created_at DESC, p.product_id DESC";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) return [];
        if ($params) $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        $stmt->close();
        return $rows;
    }

    /**
     * A single publicly visible product.
     * listing_status, category and Farmer approval are all enforced so a
     * removed or unapproved listing is never reachable by direct id.
     */
    public function getProductById($id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT p.product_id AS id,
                   p.product_name AS title,
                   p.unit_price,
                   p.unit_label,
                   p.listing_type,
                   p.available_quantity,
                   p.description,
                   c.category_name,
                   u.full_name AS farmer_name,
                   d.district_name AS pickup_district,
                   (
                       SELECT pi.image_path FROM product_images pi
                       WHERE pi.product_id = p.product_id
                       ORDER BY pi.is_primary DESC, pi.image_id ASC LIMIT 1
                   ) AS image_path
            FROM products p
            JOIN product_categories c ON c.category_id = p.category_id AND c.is_active = 1
            JOIN users u ON u.user_id = p.farmer_id AND u.account_status = 'ACTIVE'
            JOIN farmer_profiles fp ON fp.farmer_id = u.user_id AND fp.verification_status = 'APPROVED'
            LEFT JOIN districts d ON d.district_id = fp.district_id
            WHERE p.product_id = ? AND p.listing_status = 'ACTIVE'
            LIMIT 1
        ");
        if (!$stmt) return null;
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result ? $result->fetch_assoc() : null;
        $stmt->close();
        return $row ?: null;
    }
}
