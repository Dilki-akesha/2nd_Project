<?php

declare(strict_types=1);

require_once __DIR__ . '/Cart.php';
require_once __DIR__ . '/Orders.php';

/**
 * Harvestly Buyer checkout.
 *
 * The cart is a general cart. Because the orders table stores a single farmer_id
 * per order, one order may only contain products from ONE Farmer. This is
 * enforced simply at checkout - there is no grouped/complex cart.
 *
 * Delivery eligibility is the Farmer pickup district -> Buyer destination district
 * route, and at least one approved Courier Partner must support it. The Courier
 * Partner is NOT reserved at checkout.
 */
final class Checkout
{
    private Cart $cart;
    private Orders $orders;

    public function __construct()
    {
        $this->cart = new Cart();
        $this->orders = new Orders();
    }

    public function getCartItems(): array
    {
        return $this->cart->getItems();
    }

    public function getSummary(array $items): array
    {
        $subtotal = $this->cart->calculateSubtotal($items);
        $quantity = $this->cart->calculateQuantity($items);
        $servicePercent = db_setting('buyer_service_fee_percent', 0.0);
        $serviceFee = round($subtotal * $servicePercent / 100, 2);

        return [
            'subtotal' => $subtotal,
            'quantity' => $quantity,
            'serviceFee' => $serviceFee,
            'farmerIds' => array_values(array_unique(array_map(
                static fn(array $item): int => (int)($item['farmer_id'] ?? 0),
                $items
            ))),
        ];
    }

    /** District reference distance in km, or null when the pair is not configured. */
    public function routeDistance(int $farmerId, int $destinationDistrictId): ?float
    {
        $origin = (int)db_scalar(
            'SELECT district_id FROM farmer_profiles WHERE farmer_id = ? LIMIT 1',
            'i',
            [$farmerId],
            0
        );
        if ($origin <= 0) return null;
        $value = db_scalar(
            'SELECT distance_km FROM district_distances WHERE from_district_id = ? AND to_district_id = ? LIMIT 1',
            'ii',
            [$origin, $destinationDistrictId],
            null
        );
        if ($value === null && $origin !== $destinationDistrictId) return null;
        return (float)$value;
    }

    /**
     * Full checkout quote: fee breakdown plus the delivery eligibility result.
     */
    public function quote(array $items, string $destination): array
    {
        $summary = $this->getSummary($items);
        $farmerIds = array_values(array_filter($summary['farmerIds']));
        if (count($farmerIds) !== 1) {
            throw new RuntimeException('Each order must contain products from one Farmer. Please update your cart.');
        }
        $farmerId = $farmerIds[0];

        $districtId = (int)db_scalar(
            'SELECT district_id FROM districts WHERE district_name = ? AND is_active = 1 LIMIT 1',
            's',
            [$destination],
            0
        );
        if ($districtId <= 0) {
            throw new RuntimeException('Select your destination district to check delivery and fees.');
        }

        $origin = (int)db_scalar(
            'SELECT district_id FROM farmer_profiles WHERE farmer_id = ? LIMIT 1',
            'i',
            [$farmerId],
            0
        );
        if ($origin <= 0) {
            throw new RuntimeException('This Farmer has no pickup district configured, so delivery cannot be calculated.');
        }

        // District-to-district route coverage check. The Courier Partner is not reserved here.
        $available = (int)db_scalar(
            "SELECT COUNT(*)
             FROM courier_coverage_routes r
             JOIN users u ON u.user_id = r.courier_partner_id AND u.role = 'COURIER_PARTNER' AND u.account_status = 'ACTIVE'
             JOIN courier_partner_profiles cp ON cp.courier_partner_id = r.courier_partner_id AND cp.verification_status = 'APPROVED'
             WHERE r.origin_district_id = ? AND r.destination_district_id = ? AND r.is_active = 1",
            'ii',
            [$origin, $districtId],
            0
        );

        $originName = (string)db_scalar('SELECT district_name FROM districts WHERE district_id = ?', 'i', [$origin], '');
        $baseFee = db_setting('delivery_base_fee', 0.0);
        $perKm = db_setting('delivery_per_km_rate', 0.0);
        $distance = $this->routeDistance($farmerId, $districtId);

        return array_merge($summary, [
            'farmerId' => $farmerId,
            'originDistrict' => $originName,
            'destinationDistrict' => $destination,
            'distanceKm' => $distance,
            'baseFee' => round($baseFee, 2),
            'perKmRate' => round($perKm, 2),
            'routeAvailable' => $available > 0,
            'serviceFee' => null,   // filled in below once fees are calculable
            'deliveryFee' => null,
            'total' => null,
        ]);
    }

    /**
     * Final fee figures. Uses the same calculateFees() the order is stored with so
     * what the Buyer sees is exactly what is saved.
     */
    public function fees(array $quote): array
    {
        if ($quote['distanceKm'] === null) {
            throw new RuntimeException('No district reference distance is configured for this route.');
        }
        [$farmerFee, $serviceFee, $deliveryFee] = $this->orders->calculateFees(
            (float)$quote['subtotal'],
            (int)$quote['farmerId'],
            (int)db_scalar('SELECT district_id FROM districts WHERE district_name = ? AND is_active = 1 LIMIT 1', 's', [$quote['destinationDistrict']], 0)
        );
        $quote['farmerFee'] = $farmerFee;
        $quote['serviceFee'] = $serviceFee;
        $quote['deliveryFee'] = $deliveryFee;
        $quote['total'] = round((float)$quote['subtotal'] + $serviceFee + $deliveryFee, 2);
        return $quote;
    }

    public function validate(array $data): array
    {
        foreach (['fullName', 'phone', 'address', 'destination_district'] as $field) {
            if (trim((string)($data[$field] ?? '')) === '') {
                return ['valid' => false, 'message' => 'Please complete all required delivery details.'];
            }
        }

        $phone = preg_replace('/\D+/', '', (string)$data['phone']);
        if (strlen((string)$phone) < 9 || strlen((string)$phone) > 12) {
            return ['valid' => false, 'message' => 'Please enter a valid phone number.'];
        }

        $exists = (int)db_scalar(
            'SELECT COUNT(*) FROM districts WHERE district_name = ? AND is_active = 1',
            's',
            [trim((string)$data['destination_district'])],
            0
        );
        if ($exists < 1) {
            return ['valid' => false, 'message' => 'Please select a valid destination district.'];
        }

        return ['valid' => true, 'message' => ''];
    }

    public function placeOrder(array $data, array $items): array
    {
        $order = $this->orders->createOrder($items, $this->getSummary($items), $data);
        $this->cart->clear();
        return $order;
    }

    /** Buyer contact + default address used to pre-fill the delivery form. */
    public function buyerProfile(int $buyerId): array
    {
        return db_fetch_one(
            'SELECT u.full_name, u.phone,
                    bp.default_address_line1, bp.default_address_line2, bp.default_city_town, bp.default_postal_code,
                    d.district_name
             FROM users u
             LEFT JOIN buyer_profiles bp ON bp.buyer_id = u.user_id
             LEFT JOIN districts d ON d.district_id = bp.default_district_id
             WHERE u.user_id = ? AND u.role = \'BUYER\'
             LIMIT 1',
            'i',
            [$buyerId]
        ) ?? [];
    }

    /** All 25 active districts, for the destination selector. */
    public function districts(): array
    {
        return db_fetch_all('SELECT district_id, district_name FROM districts WHERE is_active = 1 ORDER BY district_name');
    }
}
