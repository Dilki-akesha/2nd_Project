<?php

declare(strict_types=1);

final class Orders
{
    private mysqli $db;

    private const DISPLAY_TO_DB = [
        'Order Placed' => 'PENDING_PAYMENT',
        'Pending Payment' => 'PENDING_PAYMENT',
        'Paid' => 'PAID',
        'Accepted' => 'ACCEPTED',
        'Preparing' => 'PREPARING',
        'Ready for Delivery' => 'READY_FOR_DELIVERY',
        'Pending Assignment' => 'PENDING_ASSIGNMENT',
        'Assigned' => 'ASSIGNED',
        'Picked Up' => 'PICKED_UP',
        'In Transit' => 'IN_TRANSIT',
        'Out for Delivery' => 'OUT_FOR_DELIVERY',
        'Delivered' => 'DELIVERED',
        'Completed' => 'COMPLETED',
        'Undeliverable' => 'UNDELIVERABLE',
        'Cancelled' => 'CANCELLED',
    ];

    public function __construct()
    {
        $this->db = db();
    }

    private function displayStatus(string $status): string
    {
        $map = array_flip(self::DISPLAY_TO_DB);
        return $map[$status] ?? ucwords(strtolower(str_replace('_', ' ', $status)));
    }

    private function map(array $order): array
    {
        $id = (int)$order['order_id'];
        $displayStatus = $this->displayStatus((string)$order['order_status']);

        $order['db_id'] = $id;
        $order['id'] = orderPublicId($id);
        $order['order_number'] = $order['id'];
        $order['status_code'] = (string)$order['order_status'];
        $order['status'] = $displayStatus;
        $order['total'] = (float)$order['grand_total'];
        $order['subtotal'] = (float)$order['product_subtotal'];
        $order['service_fee'] = (float)($order['buyer_service_fee'] ?? 0);
        $order['delivery_fee'] = (float)$order['delivery_fee'];
        $order['destination_district'] = (string)($order['destination_district'] ?? '');
        $order['full_name'] = $order['recipient_name'] ?? '';
        $order['phone'] = $order['recipient_phone'] ?? '';
        $order['city'] = $order['delivery_city_town'] ?? '';
        $order['address'] = trim(($order['delivery_address_line1'] ?? '') . ' ' . ($order['delivery_address_line2'] ?? ''));
        $order['postal'] = $order['delivery_postal_code'] ?? '';

        $items = db_fetch_all(
            "SELECT
                oi.product_id,
                oi.product_name_snapshot AS name,
                oi.unit_price_snapshot AS price,
                oi.quantity,
                p.unit_label AS unit,
                (
                    SELECT pi.image_path
                    FROM product_images pi
                    WHERE pi.product_id = oi.product_id
                    ORDER BY pi.is_primary DESC, pi.image_id ASC
                    LIMIT 1
                ) AS image
             FROM order_items oi
             LEFT JOIN products p ON p.product_id = oi.product_id
             WHERE oi.order_id = ?
             ORDER BY oi.order_item_id",
            'i',
            [$id]
        );

        foreach ($items as &$item) {
            $path = trim((string)($item['image'] ?? ''));
            if ($path === '') $path = getProductImage((string)$item['name']);
            if (!preg_match('#^https?://#i', $path) && !str_starts_with($path, '/')) $path = url($path);
            $item['image'] = $path;
            $item['price'] = (float)$item['price'];
            $item['quantity'] = (float)$item['quantity'];
        }
        unset($item);

        $order['items'] = $items;

        $order['date'] = !empty($order['created_at'])
            ? date('M d, Y', strtotime((string)$order['created_at']))
            : 'Recently';

        return $order;
    }

    private function districtIdByName(string $name): int
    {
        return (int)db_scalar(
            'SELECT district_id FROM districts WHERE district_name = ? AND is_active = 1 LIMIT 1',
            's',
            [$name],
            0
        );
    }

    public function calculateFees(float $subtotal, int $farmerId, int $destinationDistrictId): array
    {
        $farmerFeePercent = db_setting('farmer_marketplace_fee_percent', 0.0);
        $buyerFeePercent = db_setting('buyer_service_fee_percent', 0.0);
        $baseFee = db_setting('delivery_base_fee', 0.0);
        $perKm = db_setting('delivery_per_km_rate', 0.0);

        $originDistrictId = (int)db_scalar(
            'SELECT district_id FROM farmer_profiles WHERE farmer_id = ? LIMIT 1',
            'i',
            [$farmerId],
            0
        );

        $distance = 0.0;
        if ($originDistrictId > 0 && $destinationDistrictId > 0) {
            $distanceValue = db_scalar(
                'SELECT distance_km FROM district_distances
                 WHERE from_district_id = ? AND to_district_id = ? LIMIT 1',
                'ii',
                [$originDistrictId, $destinationDistrictId],
                null
            );
            if ($distanceValue === null && $originDistrictId !== $destinationDistrictId) throw new RuntimeException('No district reference distance is configured for this route.');
            $distance = (float)$distanceValue;
        }

        $farmerFee = round($subtotal * $farmerFeePercent / 100, 2);
        $buyerFee = round($subtotal * $buyerFeePercent / 100, 2);
        $deliveryFee = round($baseFee + ($distance * $perKm), 2);

        return [$farmerFee, $buyerFee, $deliveryFee];
    }

    public function createOrder(array $items, array $summary, array $data): array
    {
        requireBuyerAuth();
        if (!$items) throw new RuntimeException('Your cart is empty.');

        $farmerIds = array_values(array_unique(array_map(
            static fn(array $item): int => (int)($item['farmer_id'] ?? 0),
            $items
        )));
        $farmerIds = array_values(array_filter($farmerIds));
        if (count($farmerIds) !== 1) {
            throw new RuntimeException('Please checkout products from one Farmer at a time.');
        }

        $farmerId = $farmerIds[0];
        $destinationDistrictId = $this->districtIdByName(trim((string)$data['destination_district']));
        if ($destinationDistrictId <= 0) throw new RuntimeException('Invalid destination district.');

        // Checkout only proceeds when an approved Courier Partner supports the
        // Farmer pickup district -> Buyer destination district route.
        $originDistrictId = (int)db_scalar(
            'SELECT district_id FROM farmer_profiles WHERE farmer_id = ? LIMIT 1',
            'i',
            [$farmerId],
            0
        );
        if ($originDistrictId <= 0) {
            throw new RuntimeException('The Farmer pickup district is not configured for this product.');
        }

        $eligibleCourierCount = (int)db_scalar(
            "SELECT COUNT(*)
             FROM courier_coverage_routes ccr
             INNER JOIN users u
                ON u.user_id = ccr.courier_partner_id
               AND u.role = 'COURIER_PARTNER'
               AND u.account_status = 'ACTIVE'
             INNER JOIN courier_partner_profiles cp
                ON cp.courier_partner_id = ccr.courier_partner_id
               AND cp.verification_status = 'APPROVED'
             WHERE ccr.origin_district_id = ?
               AND ccr.destination_district_id = ?
               AND ccr.is_active = 1",
            'ii',
            [$originDistrictId, $destinationDistrictId],
            0
        );
        if ($eligibleCourierCount < 1) {
            throw new RuntimeException('Delivery is not currently available for the selected district route.');
        }

        $subtotal = 0.0;
        foreach ($items as $item) {
            $subtotal += (float)$item['price'] * (float)$item['quantity'];
        }
        $subtotal = round($subtotal, 2);
        [$farmerFee, $buyerFee, $deliveryFee] = $this->calculateFees($subtotal, $farmerId, $destinationDistrictId);
        $grandTotal = round($subtotal + $buyerFee + $deliveryFee, 2);

        $this->db->begin_transaction();
        try {
            foreach ($items as $item) {
                $row = db_fetch_one(
                    'SELECT product_name, available_quantity FROM products WHERE product_id = ? FOR UPDATE',
                    'i',
                    [(int)$item['id']]
                );
                if (!$row || (float)$item['quantity'] <= 0 || (float)$row['available_quantity'] < (float)$item['quantity']) {
                    throw new RuntimeException('Not enough stock available for ' . ($row['product_name'] ?? 'one of the selected products') . '.');
                }
            }

            $stmt = $this->db->prepare(
                'INSERT INTO orders
                 (buyer_id, farmer_id, source_type, recipient_name, recipient_phone,
                  delivery_address_line1, delivery_address_line2, delivery_city_town, delivery_postal_code, destination_district_id,
                  product_subtotal, farmer_marketplace_fee, buyer_service_fee, delivery_fee, grand_total, order_status)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, \'PENDING_PAYMENT\')'
            );
            $buyerId = currentBuyerId();
            $name = trim((string)$data['fullName']);
            $phone = trim((string)$data['phone']);
            $address = trim((string)$data['address']);
            $address2 = trim((string)($data['address2'] ?? ''));
            $city = trim((string)$data['city']);
            $postal = trim((string)$data['postal']);
            $sourceType = ($summary['source_type'] ?? 'NORMAL') === 'PREORDER' ? 'PREORDER' : 'NORMAL';
            $stmt->bind_param(
                'iisssssssiddddd',
                $buyerId,
                $farmerId,
                $sourceType,
                $name,
                $phone,
                $address,
                $address2,
                $city,
                $postal,
                $destinationDistrictId,
                $subtotal,
                $farmerFee,
                $buyerFee,
                $deliveryFee,
                $grandTotal
            );
            if (!$stmt->execute()) throw new RuntimeException('Unable to create order.');
            $orderId = (int)$this->db->insert_id;
            $stmt->close();

            // Seed the shared order status timeline so the Buyer, Farmer,
            // Courier Partner and Admin screens all show the same history.
            require_once __DIR__ . '/../OrderWorkflow.php';
            recordOrderStatus($orderId, 'PENDING_PAYMENT', $buyerId, 'Order created by Buyer');

            $itemStmt = $this->db->prepare(
                'INSERT INTO order_items
                 (order_id, product_id, product_name_snapshot, unit_price_snapshot, quantity, line_total)
                 VALUES (?, ?, ?, ?, ?, ?)'
            );
            $stockStmt = $this->db->prepare(
                'UPDATE products
                 SET listing_status = IF(available_quantity - ? <= 0, \'SOLD_OUT\', listing_status),
                     available_quantity = available_quantity - ?
                 WHERE product_id = ? AND available_quantity >= ?'
            );

            foreach ($items as $item) {
                $productId = (int)$item['id'];
                $productName = (string)$item['name'];
                $price = (float)$item['price'];
                $qty = (float)$item['quantity'];
                $line = round($price * $qty, 2);

                $itemStmt->bind_param('iisddd', $orderId, $productId, $productName, $price, $qty, $line);
                if (!$itemStmt->execute()) throw new RuntimeException('Unable to save order item.');

                $stockStmt->bind_param('ddid', $qty, $qty, $productId, $qty);
                if (!$stockStmt->execute() || $stockStmt->affected_rows !== 1) {
                    throw new RuntimeException('Stock changed while placing the order. Please try again.');
                }
            }
            $itemStmt->close();
            $stockStmt->close();

            // PayHere Sandbox is planned but NOT yet approved or integrated.
            // The provider is recorded as LOCAL_DEMO (the schema default) so no
            // payment is ever presented as completed. Payment may only be marked
            // SUCCESS after server-side verification once PayHere is enabled.
            $paymentStmt = $this->db->prepare(
                "INSERT INTO payments (order_id, provider, amount, payment_status)
                 VALUES (?, 'LOCAL_DEMO', ?, 'PENDING')"
            );
            $paymentStmt->bind_param('id', $orderId, $grandTotal);
            if (!$paymentStmt->execute()) throw new RuntimeException('Unable to record the pending payment.');
            $paymentStmt->close();

            if (!notifyUser($buyerId, 'ORDER_UPDATE', 'Order created',
                'Your order ' . orderPublicId($orderId) . ' has been created and is pending payment.', $orderId)) {
                throw new RuntimeException('Unable to record the order notification.');
            }

            $this->db->commit();
            return $this->getOrderById(orderPublicId($orderId)) ?? [];
        } catch (Throwable $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    public function getAllOrders(): array
    {
        requireBuyerAuth();
        $rows = db_fetch_all(
            "SELECT o.*, d.district_name AS destination_district
             FROM orders o
             LEFT JOIN districts d ON d.district_id = o.destination_district_id
             WHERE o.buyer_id = ?
             ORDER BY o.created_at DESC, o.order_id DESC",
            'i',
            [currentBuyerId()]
        );
        return array_map(fn(array $order) => $this->map($order), $rows);
    }

    public function getOrderById(string $orderNumber): ?array
    {
        requireBuyerAuth();
        $id = parseOrderPublicId($orderNumber);
        if ($id <= 0) return null;

        $row = db_fetch_one(
            "SELECT o.*, d.district_name AS destination_district
             FROM orders o
             LEFT JOIN districts d ON d.district_id = o.destination_district_id
             WHERE o.buyer_id = ? AND o.order_id = ?
             LIMIT 1",
            'ii',
            [currentBuyerId(), $id]
        );
        return $row ? $this->map($row) : null;
    }

    public function updateStatus(string $orderNumber, string $status): ?array {
        requireBuyerAuth();
        require_once __DIR__.'/../OrderWorkflow.php';
        if ($status !== 'COMPLETED' || !completeDeliveredOrder(parseOrderPublicId($orderNumber), currentBuyerId())) {
            return null;
        }
        return $this->getOrderById($orderNumber);
    }

    /**
     * The shared order status timeline. Every Harvestly role writes to
     * order_status_history, so the Buyer sees exactly the same history the
     * Farmer, Courier Partner and Admin screens do.
     */
    public function getStatusHistory(int $orderId): array
    {
        return db_fetch_all(
            'SELECT status, note, created_at AS changed_at
             FROM order_status_history
             WHERE order_id = ?
             ORDER BY created_at ASC, history_id ASC',
            'i',
            [$orderId]
        );
    }

    /**
     * Courier Partner organisation handling this order.
     * There are no drivers, vehicles, GPS or live tracking in Harvestly - only
     * the assigned partner organisation and its contact number.
     */
    public function getDeliveryForOrder(int $orderId): ?array
    {
        return db_fetch_one(
            "SELECT d.delivery_status, d.delivered_at, d.assigned_at, d.completed_at,
                    cp.organisation_name, cp.availability_status, u.phone, u.full_name contact_name
             FROM deliveries d
             LEFT JOIN courier_partner_profiles cp ON cp.courier_partner_id = d.courier_partner_id
             LEFT JOIN users u ON u.user_id = d.courier_partner_id
             WHERE d.order_id = ?
             LIMIT 1",
            'i',
            [$orderId]
        );
    }

    public function cancel(string $orderNumber): ?array
    {
        $id = parseOrderPublicId($orderNumber);
        $order = $this->getOrderById($orderNumber);
        if (!$order || !in_array($order['status_code'], ['PENDING_PAYMENT', 'PAID'], true)) return null;

        $this->db->begin_transaction();
        try {
            $items = db_fetch_all(
                'SELECT product_id, quantity FROM order_items WHERE order_id = ?',
                'i',
                [$id]
            );
            foreach ($items as $item) {
                db_execute(
                    "UPDATE products
                     SET available_quantity = available_quantity + ?,
                         listing_status = IF(listing_status = 'SOLD_OUT', 'ACTIVE', listing_status)
                     WHERE product_id = ?",
                    'di',
                    [(float)$item['quantity'], (int)$item['product_id']]
                );
            }
            db_execute(
                "UPDATE orders
                 SET order_status = 'CANCELLED', cancelled_at = NOW(), cancellation_reason = 'Cancelled by Buyer'
                 WHERE order_id = ? AND buyer_id = ?",
                'ii',
                [$id, currentBuyerId()]
            );
            db_execute(
                "UPDATE payments SET payment_status = 'CANCELLED'
                 WHERE order_id = ? AND payment_status = 'PENDING'",
                'i',
                [$id]
            );
            require_once __DIR__ . '/../OrderWorkflow.php';
            recordOrderStatus($id, 'CANCELLED', currentBuyerId(), 'Cancelled by Buyer');
            notifyUser(currentBuyerId(), 'ORDER_UPDATE', 'Order cancelled',
                'Your order ' . orderPublicId($id) . ' has been cancelled.', $id);
            $this->db->commit();
            return $this->getOrderById($orderNumber);
        } catch (Throwable $e) {
            $this->db->rollback();
            return null;
        }
    }

    public function delete(string $orderNumber): bool
    {
        // Harvestly retains order records for order history, reviews and
        // financial audit, so orders are never hard-deleted. A Buyer can only
        // cancel an order that has not yet been accepted by the Farmer.
        return false;
    }
}
