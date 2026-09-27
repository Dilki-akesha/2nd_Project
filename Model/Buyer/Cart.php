<?php

declare(strict_types=1);

final class Cart
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = db();
    }

    private function getCartId(): int
    {
        requireBuyerAuth();
        $buyerId = currentBuyerId();

        $row = db_fetch_one(
            'SELECT cart_id FROM carts WHERE buyer_id = ? LIMIT 1',
            'i',
            [$buyerId]
        );
        if ($row) return (int)$row['cart_id'];

        $stmt = $this->db->prepare('INSERT INTO carts (buyer_id) VALUES (?)');
        $stmt->bind_param('i', $buyerId);
        if (!$stmt->execute()) {
            $stmt->close();
            return 0;
        }
        $id = (int)$this->db->insert_id;
        $stmt->close();
        return $id;
    }

    public function getItems(?string $farmer = null): array
    {
        requireBuyerAuth();

        $rows = db_fetch_all(
            "SELECT
                p.product_id AS id,
                p.farmer_id,
                p.product_name AS name,
                u.full_name AS farmer,
                ci.quantity,
                p.unit_price AS price,
                p.unit_label AS unit,
               
                (
                    SELECT pi.image_path
                    FROM product_images pi
                    WHERE pi.product_id = p.product_id
                    ORDER BY pi.is_primary DESC, pi.image_id ASC
                    LIMIT 1
                ) AS image
             FROM cart_items ci
             INNER JOIN carts c ON c.cart_id = ci.cart_id
             INNER JOIN products p ON p.product_id = ci.product_id
             INNER JOIN users u ON u.user_id = p.farmer_id
             WHERE c.buyer_id = ?
             ORDER BY ci.cart_item_id",
            'i',
            [currentBuyerId()]
        );

        foreach ($rows as &$row) {
            $row['id'] = (int)$row['id'];
            $row['farmer_id'] = (int)$row['farmer_id'];
            $row['quantity'] = (float)$row['quantity'];
            $row['price'] = (float)$row['price'];
            $path = trim((string)($row['image'] ?? ''));
            if ($path === '') {
                $path = getProductImage((string)$row['name']);
            }
            if (!preg_match('#^https?://#i', $path) && !str_starts_with($path, '/')) {
                $path = url($path);
            }
            $row['image'] = $path;
        }
        unset($row);

        if ($farmer === null || trim($farmer) === '') return $rows;
        $farmer = trim($farmer);

        return array_values(array_filter(
            $rows,
            static fn(array $item): bool => trim((string)$item['farmer']) === $farmer
        ));
    }

    public function add(int $productId, float $quantity = 1.0): bool
    {
        requireBuyerAuth();
        $cartId = $this->getCartId();
        if ($cartId <= 0) return false;

        $product = db_fetch_one(
            "SELECT product_id, available_quantity
             FROM products
             WHERE product_id = ? AND listing_status = 'ACTIVE'
             LIMIT 1",
            'i',
            [$productId]
        );
        if (!$product || (float)$product['available_quantity'] <= 0) return false;

        $quantity = min(max(0.001, $quantity), (float)$product['available_quantity']);
        return db_execute(
            'INSERT INTO cart_items (cart_id, product_id, quantity)
             VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE quantity = LEAST(quantity + VALUES(quantity), ?)',
            'iidd',
            [$cartId, $productId, $quantity, (float)$product['available_quantity']]
        );
    }

    public function updateQuantity(int $productId, float $quantity): bool
    {
        requireBuyerAuth();
        if ($quantity <= 0) return $this->remove($productId);

        return db_execute(
            'UPDATE cart_items ci
             INNER JOIN carts c ON c.cart_id = ci.cart_id
             INNER JOIN products p ON p.product_id = ci.product_id
             SET ci.quantity = LEAST(?, p.available_quantity)
             WHERE c.buyer_id = ? AND ci.product_id = ?',
            'dii',
            [$quantity, currentBuyerId(), $productId]
        );
    }

    public function remove(int $productId): bool
    {
        requireBuyerAuth();
        return db_execute(
            'DELETE ci
             FROM cart_items ci
             INNER JOIN carts c ON c.cart_id = ci.cart_id
             WHERE c.buyer_id = ? AND ci.product_id = ?',
            'ii',
            [currentBuyerId(), $productId]
        );
    }

    public function clear(): bool
    {
        requireBuyerAuth();
        return db_execute(
            'DELETE ci
             FROM cart_items ci
             INNER JOIN carts c ON c.cart_id = ci.cart_id
             WHERE c.buyer_id = ?',
            'i',
            [currentBuyerId()]
        );
    }

    /** Base delivery fee. The full figure (base + district distance) is calculated at checkout. */
    public function getDeliveryFee(): float
    {
        return db_setting('delivery_base_fee', 0.0);
    }

    public function calculateSubtotal(array $items): float
    {
        $subtotal = 0.0;
        foreach ($items as $item) {
            $subtotal += (float)$item['quantity'] * (float)$item['price'];
        }
        return round($subtotal, 2);
    }

    public function calculateQuantity(array $items): float
    {
        $quantity = 0.0;
        foreach ($items as $item) $quantity += (float)$item['quantity'];
        return round($quantity, 3);
    }
}
