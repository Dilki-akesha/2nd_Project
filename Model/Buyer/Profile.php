<?php

declare(strict_types=1);

final class Profile
{
    public function getBuyer(): array
    {
        requireBuyerAuth();

        $buyer = db_fetch_one(
            "SELECT
                u.user_id AS id,
                u.full_name AS name,
                u.email,
                u.phone,
                u.created_at,
                u.account_status,
                bp.default_address_line1 AS address,
                bp.default_address_line2 AS address2,
                bp.default_city_town AS city,
                bp.default_postal_code AS postal,
                d.district_name AS district
             FROM users u
             LEFT JOIN buyer_profiles bp ON bp.buyer_id = u.user_id
             LEFT JOIN districts d ON d.district_id = bp.default_district_id
             WHERE u.user_id = ? AND u.role = 'BUYER'
             LIMIT 1",
            'i',
            [currentBuyerId()]
        ) ?? [];

        $buyer['registered_at'] = $buyer['created_at'] ?? '';
        $buyer['joined'] = !empty($buyer['created_at'])
            ? date('F Y', strtotime((string)$buyer['created_at']))
            : '';

        return $buyer;
    }

    /** All 25 active districts, for the profile district selector. */
    public function districts(): array
    {
        return db_fetch_all(
            'SELECT district_id, district_name FROM districts WHERE is_active = 1 ORDER BY district_name'
        );
    }

    public function save(array $data, ?array $file = null): array
    {
        requireBuyerAuth();

        $name = trim((string)($data['name'] ?? ''));
        $email = trim((string)($data['email'] ?? ''));
        $phone = trim((string)($data['phone'] ?? ''));
        $city = trim((string)($data['city'] ?? ''));
        $district = trim((string)($data['district'] ?? ''));
        $address = trim((string)($data['address'] ?? ''));
        $address2 = trim((string)($data['address2'] ?? ''));
        $postal = trim((string)($data['postal'] ?? ''));

        if ($name === '' || mb_strlen($name) > 120 || !preg_match('/^[\p{L}][\p{L}\s.\'\-]{1,119}$/u', $name) ||
            !filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 254) {
            throw new RuntimeException('Please enter a valid name (2-120 characters) and email address.');
        }
        if (!preg_match('/^(?:0|\+94)\d{9}$/', preg_replace('/[\s().-]+/', '', $phone))) {
            throw new RuntimeException('Please enter a valid Sri Lankan phone number.');
        }
        if (mb_strlen($address) > 180 || mb_strlen($address2) > 180 || mb_strlen($city) > 100 || ($postal !== '' && !preg_match('/^\d{5}$/', $postal))) {
            throw new RuntimeException('Please check the address, city and postal code lengths/formats.');
        }

        $duplicate = (int)db_scalar(
            'SELECT COUNT(*) FROM users WHERE email = ? AND user_id <> ?',
            'si',
            [$email, currentBuyerId()],
            0
        );
        if ($duplicate > 0) {
            throw new RuntimeException('That email address is already in use.');
        }

        $districtId = (int)db_scalar(
            'SELECT district_id FROM districts WHERE district_name = ? AND is_active = 1 LIMIT 1',
            's',
            [$district],
            0
        );
        if ($districtId <= 0 || $address === '' || $phone === '') {
            throw new RuntimeException('Please enter your address, phone and a valid district.');
        }

        db()->begin_transaction();
        try {
            if (!db_execute(
                "UPDATE users SET full_name = ?, email = ?, phone = ? WHERE user_id = ? AND role = 'BUYER'",
                'sssi',
                [$name, $email, $phone, currentBuyerId()]
            )) {
                throw new RuntimeException('Unable to update user profile.');
            }

            $stmt = db()->prepare(
                'INSERT INTO buyer_profiles
                 (buyer_id, default_address_line1, default_address_line2, default_city_town, default_postal_code, default_district_id)
                 VALUES (?, ?, ?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE
                   default_address_line1 = VALUES(default_address_line1),
                   default_address_line2 = VALUES(default_address_line2),
                   default_city_town = VALUES(default_city_town),
                   default_postal_code = VALUES(default_postal_code),
                   default_district_id = VALUES(default_district_id)'
            );
            $buyerId = currentBuyerId();
            $stmt->bind_param('issssi', $buyerId, $address, $address2, $city, $postal, $districtId);
            if (!$stmt->execute()) throw new RuntimeException('Unable to update buyer delivery details.');
            $stmt->close();

            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;

            db()->commit();
        } catch (Throwable $e) {
            db()->rollback();
            throw $e;
        }

        return $this->getBuyer();
    }

    public function getStats(): array
    {
        requireBuyerAuth();
        $rows = db_fetch_all(
            'SELECT order_status, COUNT(*) AS c
             FROM orders
             WHERE buyer_id = ?
             GROUP BY order_status',
            'i',
            [currentBuyerId()]
        );

        $stats = [
            'total' => 0,
            'completed' => 0,
            'active' => 0,
            'closed' => 0,
        ];

        foreach ($rows as $row) {
            $count = (int)$row['c'];
            $status = (string)$row['order_status'];
            $stats['total'] += $count;
            if ($status === 'COMPLETED') $stats['completed'] += $count;
            elseif (in_array($status, ['CANCELLED', 'REJECTED', 'UNDELIVERABLE'], true)) $stats['closed'] += $count;
            else $stats['active'] += $count;
        }

        return $stats;
    }

    public function delete(int $id): bool
    {
        // Keep Buyer records when they are referenced by orders.
        if ($id <= 0 || $id !== currentBuyerId()) return false;

        $hasOrders = (int)db_scalar(
            'SELECT COUNT(*) FROM orders WHERE buyer_id = ?',
            'i',
            [$id],
            0
        );
        if ($hasOrders > 0) return false;

        return db_execute(
            "DELETE FROM users WHERE user_id = ? AND role = 'BUYER'",
            'i',
            [$id]
        );
    }
}
