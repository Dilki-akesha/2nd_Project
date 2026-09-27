<?php
/**
 * Harvestly Farmer product CRUD.
 * Create, Read, Update and Delete for products - the Farmer CRUD feature.
 * MySQLi prepared statements only.
 */
require_once __DIR__ . '/../../config/app.php';

final class FarmerProductModel
{
    public function categories(): array
    {
        return db_fetch_all(
            'SELECT category_id, category_name FROM product_categories WHERE is_active = 1 ORDER BY category_name'
        );
    }

    public function find(int $id, int $farmer): ?array
    {
        return db_fetch_one(
            'SELECT * FROM products WHERE product_id = ? AND farmer_id = ? LIMIT 1',
            'ii',
            [$id, $farmer]
        );
    }

    private function validDate(string $value): bool
    {
        return $value === '' || (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) && date('Y-m-d', strtotime($value)) === $value);
    }

    public function save(int $farmer, array $data, ?int $id = null): int
    {
        $name = trim((string)($data['product_name'] ?? ''));
        $category = (int)($data['category_id'] ?? 0);
        $price = filter_var($data['unit_price'] ?? null, FILTER_VALIDATE_FLOAT);
        $quantity = filter_var($data['available_quantity'] ?? null, FILTER_VALIDATE_FLOAT);
        $type = (string)($data['listing_type'] ?? '');
        $status = (string)($data['listing_status'] ?? '');
        $method = ($data['growing_method'] ?? '') !== '' ? (string)$data['growing_method'] : null;
        $unit = trim((string)($data['unit_label'] ?? 'kg')) ?: 'kg';

        if ($name === '' || mb_strlen($name) > 150) {
            throw new RuntimeException('Enter a product name of 150 characters or fewer.');
        }
        if ($price === false || $price <= 0) {
            throw new RuntimeException('Enter a price greater than zero.');
        }
        if ($quantity === false || $quantity < 0) {
            throw new RuntimeException('Enter a stock quantity of zero or more.');
        }
        if (mb_strlen($unit) > 30) {
            throw new RuntimeException('The unit label must be 30 characters or fewer.');
        }
        if (!in_array($type, ['AVAILABLE_NOW', 'HARVEST_SOON', 'SEASONAL'], true)) {
            throw new RuntimeException('Choose a valid listing type.');
        }
        if (!in_array($status, ['ACTIVE', 'INACTIVE', 'SOLD_OUT'], true)) {
            throw new RuntimeException('Choose a valid listing status.');
        }
        if ($method !== null && !in_array($method, ['ORGANIC', 'CONVENTIONAL', 'MIXED'], true)) {
            throw new RuntimeException('Choose a valid growing method.');
        }
        if (!db_scalar('SELECT COUNT(*) FROM product_categories WHERE category_id = ? AND is_active = 1', 'i', [$category], 0)) {
            throw new RuntimeException('Choose an active product category.');
        }

        $dates = [];
        foreach (['harvest_date', 'available_from_date', 'best_before_date', 'season_start_date', 'season_end_date'] as $key) {
            $value = trim((string)($data[$key] ?? ''));
            if (!$this->validDate($value)) {
                throw new RuntimeException('Enter valid dates in YYYY-MM-DD format.');
            }
            $dates[] = $value !== '' ? $value : null;
        }

        // A SEASONAL listing needs a season window to be meaningful.
        if ($type === 'SEASONAL' && $dates[3] === null && $dates[4] === null) {
            throw new RuntimeException('A Seasonal listing needs a season start and/or end date.');
        }

        /*
         * Storage / shelf-life reference is optional and may only be set when a
         * real, active row exists in shelf_life_references. When no reference is
         * chosen, shelf_life_reference_id stays NULL and Harvestly shows no
         * shelf-life section for the product.
         */
        $shelfReference = (int)($data['shelf_life_reference_id'] ?? 0);
        if ($shelfReference > 0 && !db_scalar(
            'SELECT COUNT(*) FROM shelf_life_references WHERE shelf_life_reference_id = ? AND is_active = 1',
            'i',
            [$shelfReference],
            0
        )) {
            throw new RuntimeException('The selected storage reference is not available.');
        }
        $shelfReference = $shelfReference > 0 ? $shelfReference : null;

        $params = [
            $category,
            $name,
            trim((string)($data['description'] ?? '')),
            (float)$price,
            (float)$quantity,
            $unit,
            $type,
            $method,
            $dates[0],
            $dates[1],
            $dates[2],
            $dates[3],
            $dates[4],
            $shelfReference,
            trim((string)($data['storage_guidance'] ?? '')),
            $status,
        ];

        /*
         * 16 column values, then either (product_id, farmer_id) for the UPDATE
         * or (farmer_id) for the INSERT.
         *   1 category_id          i
         *   2 product_name         s
         *   3 description          s
         *   4 unit_price           d
         *   5 available_quantity   d
         *   6 unit_label           s
         *   7 listing_type         s
         *   8 growing_method       s
         *   9-13 harvest / available_from / best_before / season_start / season_end  s x5
         *  14 shelf_life_reference_id  i
         *  15 storage_guidance    s
         *  16 listing_status      s
         */
        $types = 'issddss' . 's' . 'sssss' . 'iss';

        if ($id) {
            if (!$this->find($id, $farmer)) throw new RuntimeException('Product not found.');
            $ok = db_execute(
                'UPDATE products SET
                    category_id = ?, product_name = ?, description = ?, unit_price = ?,
                    available_quantity = ?, unit_label = ?, listing_type = ?, growing_method = ?,
                    harvest_date = ?, available_from_date = ?, best_before_date = ?,
                    season_start_date = ?, season_end_date = ?, shelf_life_reference_id = ?,
                    storage_guidance = ?, listing_status = ?
                 WHERE product_id = ? AND farmer_id = ?',
                $types . 'ii',
                [...$params, $id, $farmer]
            );
        } else {
            $ok = db_execute(
                'INSERT INTO products
                    (category_id, product_name, description, unit_price, available_quantity, unit_label,
                     listing_type, growing_method, harvest_date, available_from_date, best_before_date,
                     season_start_date, season_end_date, shelf_life_reference_id, storage_guidance,
                     listing_status, farmer_id)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                $types . 'i',
                [...$params, $farmer]
            );
            $id = (int)db()->insert_id;
        }

        if (!$ok) throw new RuntimeException('Unable to save product.');
        return $id;
    }

    /**
     * Delete a product. Products already referenced by a cart, order or preorder
     * are protected so order history stays intact.
     */
    public function delete(int $id, int $farmer): bool
    {
        if (!$this->find($id, $farmer)) return false;
        foreach (['cart_items', 'order_items', 'preorders'] as $table) {
            if ((int)db_scalar("SELECT COUNT(*) FROM $table WHERE product_id = ?", 'i', [$id], 0) > 0) return false;
        }
        db()->begin_transaction();
        try {
            if (!db_execute('DELETE FROM product_images WHERE product_id = ?', 'i', [$id])) {
                throw new RuntimeException('Images could not be removed.');
            }
            if (!db_execute('DELETE FROM products WHERE product_id = ? AND farmer_id = ?', 'ii', [$id, $farmer])) {
                throw new RuntimeException('Product could not be removed.');
            }
            db()->commit();
            return true;
        } catch (Throwable $e) {
            db()->rollback();
            return false;
        }
    }

    /** Add a product image. JPG and PNG only, 5 MB maximum. */
    public function addImage(int $productId, array $file, bool $makePrimary = true): void
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return;
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        if (!in_array($mime, ['image/jpeg', 'image/png'], true)) {
            throw new RuntimeException('Product images must be JPG or PNG.');
        }
        $path = handleFileUpload($file, 'assets/images/products');
        if ($makePrimary) {
            db_execute('UPDATE product_images SET is_primary = 0 WHERE product_id = ?', 'i', [$productId]);
        }
        if (!db_execute(
            'INSERT INTO product_images(product_id, image_path, is_primary) VALUES (?, ?, ?)',
            'isi',
            [$productId, $path, $makePrimary ? 1 : 0]
        )) {
            throw new RuntimeException('Unable to save image.');
        }
    }

    public function images(int $productId): array
    {
        return db_fetch_all(
            'SELECT image_id, image_path, is_primary FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, image_id',
            'i',
            [$productId]
        );
    }

    public function deleteImage(int $imageId, int $farmer): bool
    {
        if (!db_fetch_one(
            'SELECT pi.image_id FROM product_images pi
             JOIN products p ON p.product_id = pi.product_id
             WHERE pi.image_id = ? AND p.farmer_id = ? LIMIT 1',
            'ii',
            [$imageId, $farmer]
        )) {
            return false;
        }
        return db_execute('DELETE FROM product_images WHERE image_id = ?', 'i', [$imageId]);
    }

    public function setPrimaryImage(int $imageId, int $farmer): bool
    {
        $image = db_fetch_one(
            'SELECT pi.image_id, pi.product_id FROM product_images pi
             JOIN products p ON p.product_id = pi.product_id
             WHERE pi.image_id = ? AND p.farmer_id = ? LIMIT 1',
            'ii',
            [$imageId, $farmer]
        );
        if (!$image) return false;
        db()->begin_transaction();
        try {
            db_execute('UPDATE product_images SET is_primary = 0 WHERE product_id = ?', 'i', [(int)$image['product_id']]);
            db_execute('UPDATE product_images SET is_primary = 1 WHERE image_id = ?', 'i', [$imageId]);
            db()->commit();
            return true;
        } catch (Throwable $e) {
            db()->rollback();
            return false;
        }
    }
}
