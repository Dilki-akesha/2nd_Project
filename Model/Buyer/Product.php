<?php

declare(strict_types=1);

final class Product
{
    private PDO $db;

    public function __construct()
    {
        $this->db = db();
    }

    private function normalizeProduct(array $product): array
    {
        if (mb_strtolower(trim((string)($product['name'] ?? ''))) === 'coconut') {
            $product['image'] = url('assets/coconut-sri-lanka.jpg');
        }
        return $this->normalizeProduct($product);
    }

    private function map(array $product): array
    {
        $product['id'] = (int)$product['id'];
        $product['price'] = (float)$product['price'];
        $product['rating'] = (float)$product['rating'];
        $product['reviews'] = (int)$product['reviews'];
        $product['fresh'] = (bool)$product['fresh'];
        $product['organic'] = (bool)$product['organic'];
        $product['stock'] = (int)$product['stock'];
        $product['farmer_rating'] = (float)$product['farmer_rating'];
        $product['images'] = !empty($product['image']) ? [$product['image']] : [];
        $product['farmerImage'] = '';

        return $product;
    }

    public function getAllProducts(): array
    {
        $stmt = $this->db->query(
            'SELECT * FROM products ORDER BY created_at DESC, id DESC'
        );

        return array_map(fn(array $row) => $this->map($row), $stmt->fetchAll());
    }

    public function getProductById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM products WHERE id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        $product = $stmt->fetch();

        return $product ? $this->map($product) : null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO products
                (name, price, unit, farmer, rating, reviews, fresh, organic, stock,
                 image, description, harvest_date, farm, farmer_rating, experience, delivery)
             VALUES (?, ?, ?, ?, 0, 0, ?, ?, ?, ?, ?, ?, ?, 0, ?, ?)'
        );

        $stmt->execute([
            trim((string)$data['name']),
            max(0, (float)$data['price']),
            trim((string)$data['unit']),
            trim((string)$data['farmer']),
            !empty($data['fresh']) ? 1 : 0,
            !empty($data['organic']) ? 1 : 0,
            max(0, (int)($data['stock'] ?? 0)),
            trim((string)($data['image'] ?? '')),
            trim((string)($data['description'] ?? '')),
            trim((string)($data['harvest_date'] ?? 'Today')),
            trim((string)($data['farm'] ?? $data['farmer'])),
            trim((string)($data['experience'] ?? '')),
            trim((string)($data['delivery'] ?? '')),
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE products
             SET name = ?, price = ?, unit = ?, farmer = ?, stock = ?, image = ?,
                 description = ?, organic = ?, fresh = ?, harvest_date = ?, farm = ?,
                 experience = ?, delivery = ?
             WHERE id = ?'
        );

        return $stmt->execute([
            trim((string)$data['name']),
            max(0, (float)$data['price']),
            trim((string)$data['unit']),
            trim((string)$data['farmer']),
            max(0, (int)$data['stock']),
            trim((string)($data['image'] ?? '')),
            trim((string)($data['description'] ?? '')),
            !empty($data['organic']) ? 1 : 0,
            !empty($data['fresh']) ? 1 : 0,
            trim((string)($data['harvest_date'] ?? 'Today')),
            trim((string)($data['farm'] ?? $data['farmer'])),
            trim((string)($data['experience'] ?? '')),
            trim((string)($data['delivery'] ?? '')),
            $id,
        ]);
    }

    public function delete(int $id): bool
    {
        try {
            $stmt = $this->db->prepare('DELETE FROM products WHERE id = ?');
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
