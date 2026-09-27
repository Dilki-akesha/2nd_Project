<?php

declare(strict_types=1);

/**
 * Harvestly Buyer product browsing.
 * Products shown here always come from the database. When a table has no
 * records the caller receives an empty array / null and the view renders an
 * empty state - no fake fallback records are displayed.
 */
final class Product
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = db();
    }

    private function imageUrl(?string $path, string $name = '', string $category = ''): string
    {
        $path = trim((string)$path);
        if ($path === '') {
            $path = getProductImage($name, $category);
        }
        if (preg_match('#^https?://#i', $path) || str_starts_with($path, '/')) {
            return $path;
        }
        return url($path);
    }

    private function map(array $row): array
    {
        $row['id'] = (int)$row['id'];
        $row['farmer_id'] = (int)$row['farmer_id'];
        $row['price'] = (float)$row['price'];
        $row['rating'] = (float)($row['rating'] ?? 0);
        $row['reviews'] = (int)($row['reviews'] ?? 0);
        $row['organic'] = ($row['growing_method'] ?? '') === 'ORGANIC';
        $row['stock'] = (float)($row['stock'] ?? 0);
        $row['image'] = $this->imageUrl($row['image'] ?? '', $row['name'] ?? '', $row['category'] ?? '');
        $row['harvest_date'] = (string)($row['harvest_date'] ?? '');
        /*
         * Shelf-life information is shown ONLY where a valid reference row exists.
         * When shelf_life_reference_id is NULL this stays null and the view renders
         * no shelf-life section at all - no placeholder or invented values.
         */
        $row['shelf_reference'] = empty($row['shelf_life_reference_id'])
            ? null
            : db_fetch_one(
                'SELECT * FROM shelf_life_references WHERE shelf_life_reference_id=? AND is_active=1',
                'i',
                [(int)$row['shelf_life_reference_id']]
            );
        return $row;
    }

    private function baseSelect(): string
    {
        return "
            SELECT
                p.product_id AS id, p.shelf_life_reference_id,
                p.farmer_id,
                p.product_name AS name,
                p.unit_price AS price,
                p.unit_label AS unit,
                u.full_name AS farmer,
                COALESCE((
                    SELECT AVG(r.rating) FROM reviews r WHERE r.farmer_id = p.farmer_id
                ), 0) AS rating,
                COALESCE((
                    SELECT COUNT(*) FROM reviews r WHERE r.farmer_id = p.farmer_id
                ), 0) AS reviews,
                p.available_quantity AS stock,
                (
                    SELECT pi.image_path
                    FROM product_images pi
                    WHERE pi.product_id = p.product_id
                    ORDER BY pi.is_primary DESC, pi.image_id ASC
                    LIMIT 1
                ) AS image,
                p.description,
                p.harvest_date,
                COALESCE(fp.farm_name, u.full_name) AS farm,
                p.listing_type,
                p.growing_method,
                c.category_name AS category,
                d.district_name AS district
            FROM products p
            JOIN users u ON u.user_id = p.farmer_id
            JOIN product_categories c ON c.category_id = p.category_id
            JOIN farmer_profiles fp ON fp.farmer_id = p.farmer_id
            LEFT JOIN districts d ON d.district_id = fp.district_id
        ";
    }

    /**
     * Only listings that an approved, active Farmer in an active category may be
     * shown to Buyers.
     */
    private function visibilityClause(): string
    {
        return "WHERE p.listing_status = 'ACTIVE'
                AND u.account_status = 'ACTIVE'
                AND fp.verification_status = 'APPROVED'
                AND c.is_active = 1";
    }

    public function getAllProducts(): array
    {
        $rows = db_fetch_all(
            $this->baseSelect() . ' ' . $this->visibilityClause() . '
             ORDER BY p.created_at DESC, p.product_id DESC'
        );
        return array_map(fn(array $row) => $this->map($row), $rows);
    }

    public function getProductById(int $id): ?array
    {
        $row = db_fetch_one(
            $this->baseSelect() . ' ' . $this->visibilityClause() . '
             AND p.product_id = ?
             LIMIT 1',
            'i',
            [$id]
        );
        return $row ? $this->map($row) : null;
    }

    public function getFarmerStore(string $farmer): ?array
    {
        $rows = db_fetch_all(
            $this->baseSelect() . ' ' . $this->visibilityClause() . '
             AND u.full_name = ?
             ORDER BY p.created_at DESC, p.product_id DESC',
            's',
            [$farmer]
        );

        if (!$rows) return null;

        $products = array_map(fn(array $row) => $this->map($row), $rows);
        $first = $products[0];

        return [
            'name' => (string)$first['farmer'],
            'rating' => round((float)$first['rating'], 1),
            'reviewCount' => (int)$first['reviews'],
            'district' => (string)($first['district'] ?? ''),
            'farm' => (string)($first['farm'] ?? ''),
            'products' => $products,
        ];
    }

    /** Growing methods actually present, used on the Farmer store summary. */
    public function growingMethodLabel(?string $method): string
    {
        return match ((string)$method) {
            'ORGANIC' => 'Organic',
            'CONVENTIONAL' => 'Conventional',
            'MIXED' => 'Mixed',
            default => 'Not specified',
        };
    }
}
