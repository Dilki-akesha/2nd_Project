<?php

declare(strict_types=1);

final class Orders
{
    private PDO $db;

    private const STATUSES = [
        'Order Placed',
        'Accepted',
        'In Transit',
        'Out for Delivery',
        'Delivered',
        'Cancelled',
    ];

    public function __construct()
    {
        $this->db = db();
    }

    private function map(array $order): array
    {
        $order['db_id'] = (int)$order['db_id'];
        $order['id'] = $order['order_number'];
        $order['total'] = (float)$order['total'];
        $order['subtotal'] = (float)$order['subtotal'];
        $order['delivery_fee'] = (float)$order['delivery_fee'];

        $itemStmt = $this->db->prepare(
            'SELECT product_id, name, price, quantity, unit, image
             FROM order_items
             WHERE order_id = ?
             ORDER BY id'
        );
        $itemStmt->execute([$order['db_id']]);

        $order['items'] = $itemStmt->fetchAll();
        foreach ($order['items'] as &$item) {
            if (mb_strtolower(trim((string)($item['name'] ?? ''))) === 'coconut') {
                $item['image'] = url('assets/coconut-sri-lanka.jpg');
            }
        }
        unset($item);
        $order['images'] = array_values(
            array_filter(array_column($order['items'], 'image'))
        );
        $status = (string)$order['status'];
        $statusClasses = [
            'Order Placed' => 'placed',
            'Accepted' => 'accepted',
            'In Transit' => 'transit',
            'Out for Delivery' => 'transit',
            'Delivered' => 'delivered',
            'Cancelled' => 'cancelled',
        ];

        $order['status_class'] = $statusClasses[$status] ?? 'placed';
        $order['date'] = !empty($order['created_at'])
            ? date('M d, Y', strtotime((string)$order['created_at']))
            : 'Recently';

        $order['button_class'] = $status === 'Cancelled' ? 'tracking' : 'tracking';
        $order['button'] = $status === 'Cancelled' ? 'View Order' : ($status === 'Delivered' ? 'View Tracking' : 'Track Order');
        $order['button_icon'] = $status === 'Cancelled' ? 'receipt_long' : ($status === 'Delivered' ? 'check_circle' : 'local_shipping');

        return $order;
    }

    public function createOrder(array $items, array $summary, array $data): array
    {
        if (!$items) {
            throw new RuntimeException('Your cart is empty.');
        }

        $this->db->beginTransaction();

        try {
            $this->validateStock($items);

            $orderNumber = 'ORD-' . date('Y') . '-' . strtoupper(
                substr(bin2hex(random_bytes(5)), 0, 8)
            );

            $orderStmt = $this->db->prepare(
                'INSERT INTO orders
                    (order_number, user_id, full_name, phone, city, address, postal,
                     payment_method, subtotal, delivery_fee, total, status)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );

            $orderStmt->execute([
                $orderNumber,
                currentBuyerId(),
                trim((string)$data['fullName']),
                trim((string)$data['phone']),
                trim((string)$data['city']),
                trim((string)$data['address']),
                trim((string)$data['postal']),
                trim((string)$data['payment']),
                $summary['subtotal'],
                $summary['deliveryFee'],
                $summary['total'],
                'Order Placed',
            ]);

            $orderId = (int)$this->db->lastInsertId();

            $itemStmt = $this->db->prepare(
                'INSERT INTO order_items
                    (order_id, product_id, name, price, quantity, unit, image)
                 VALUES (?, ?, ?, ?, ?, ?, ?)'
            );

            $stockStmt = $this->db->prepare(
                'UPDATE products
                 SET stock = stock - ?
                 WHERE id = ? AND stock >= ?'
            );

            foreach ($items as $item) {
                $quantity = (int)$item['quantity'];

                $itemStmt->execute([
                    $orderId,
                    (int)$item['id'],
                    $item['name'],
                    $item['price'],
                    $quantity,
                    $item['unit'] ?? 'kg',
                    $item['image'] ?? '',
                ]);

                $stockStmt->execute([
                    $quantity,
                    (int)$item['id'],
                    $quantity,
                ]);

                if ($stockStmt->rowCount() !== 1) {
                    throw new RuntimeException(
                        'One of the selected products is no longer available in the requested quantity.'
                    );
                }
            }

            $notificationStmt = $this->db->prepare(
                'INSERT INTO notifications
                    (user_id, type, title, message, action_label, action_url, is_read)
                 VALUES (?, ?, ?, ?, ?, ?, 0)'
            );
            $notificationStmt->execute([
                currentBuyerId(),
                'Orders',
                'Order Confirmed',
                'Your order ' . $orderNumber . ' has been confirmed.',
                'View Orders',
                url('Controller/Buyer/OrdersController.php'),
            ]);

            $this->db->commit();

            return $this->getOrderById($orderNumber);
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $e;
        }
    }

    private function validateStock(array $items): void
    {
        $stmt = $this->db->prepare(
            'SELECT name, stock FROM products WHERE id = ? LIMIT 1'
        );

        foreach ($items as $item) {
            $stmt->execute([(int)$item['id']]);
            $product = $stmt->fetch();

            if (!$product || (int)$product['stock'] < (int)$item['quantity']) {
                throw new RuntimeException(
                    'Not enough stock available for ' . ($product['name'] ?? 'one of your products') . '.'
                );
            }
        }
    }

    public function getAllOrders(): array
    {
        $stmt = $this->db->prepare(
            'SELECT o.*, o.id AS db_id
             FROM orders o
             WHERE o.user_id = ?
             ORDER BY o.created_at DESC, o.id DESC'
        );
        $stmt->execute([currentBuyerId()]);

        return array_map(
            fn(array $order) => $this->map($order),
            $stmt->fetchAll()
        );
    }

    public function getOrderById(string $orderNumber): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT o.*, o.id AS db_id
             FROM orders o
             WHERE o.user_id = ? AND o.order_number = ?
             LIMIT 1'
        );
        $stmt->execute([currentBuyerId(), $orderNumber]);
        $order = $stmt->fetch();

        return $order ? $this->map($order) : null;
    }

    public function updateStatus(string $orderNumber, string $status): ?array
    {
        if (!in_array($status, self::STATUSES, true)) {
            return null;
        }

        $order = $this->getOrderById($orderNumber);
        if (!$order) {
            return null;
        }

        $stmt = $this->db->prepare(
            'UPDATE orders
             SET status = ?
             WHERE user_id = ? AND order_number = ?'
        );
        $stmt->execute([$status, currentBuyerId(), $orderNumber]);

        return $this->getOrderById($orderNumber);
    }

    public function cancel(string $orderNumber): ?array
    {
        $order = $this->getOrderById($orderNumber);

        if (!$order || !in_array($order['status'], ['Order Placed', 'Accepted'], true)) {
            return null;
        }

        $this->db->beginTransaction();

        try {
            $restoreStmt = $this->db->prepare(
                'UPDATE products p
                 INNER JOIN order_items oi ON oi.product_id = p.id
                 SET p.stock = p.stock + oi.quantity
                 WHERE oi.order_id = ?'
            );
            $restoreStmt->execute([$order['db_id']]);

            $statusStmt = $this->db->prepare(
                'UPDATE orders
                 SET status = \'Cancelled\'
                 WHERE user_id = ? AND order_number = ?'
            );
            $statusStmt->execute([currentBuyerId(), $orderNumber]);

            $this->db->commit();
            return $this->getOrderById($orderNumber);
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function delete(string $orderNumber): bool
    {
        $order = $this->getOrderById($orderNumber);

        if (!$order || $order['status'] !== 'Cancelled') {
            return false;
        }

        $stmt = $this->db->prepare(
            'DELETE FROM orders WHERE user_id = ? AND order_number = ?'
        );

        return $stmt->execute([currentBuyerId(), $orderNumber]);
    }
}
