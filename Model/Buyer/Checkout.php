<?php

declare(strict_types=1);

require_once __DIR__ . '/Cart.php';
require_once __DIR__ . '/Orders.php';

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
        $deliveryFee = $quantity > 0 ? $this->cart->getDeliveryFee() : 0.0;

        return [
            'subtotal' => $subtotal,
            'quantity' => $quantity,
            'deliveryFee' => $deliveryFee,
            'total' => $subtotal + $deliveryFee,
        ];
    }

    public function validate(array $data): array
    {
        $requiredFields = [
            'fullName',
            'phone',
            'city',
            'address',
            'postal',
            'payment',
        ];

        foreach ($requiredFields as $field) {
            if (trim((string)($data[$field] ?? '')) === '') {
                return [
                    'valid' => false,
                    'message' => 'Please complete all required fields.',
                ];
            }
        }

        $phone = preg_replace('/\D+/', '', (string)$data['phone']);

        if (strlen($phone) < 9 || strlen($phone) > 12) {
            return [
                'valid' => false,
                'message' => 'Please enter a valid phone number.',
            ];
        }

        $payment = (string)$data['payment'];

        if (!in_array($payment, ['card', 'cash', 'bank'], true)) {
            return [
                'valid' => false,
                'message' => 'Invalid payment method.',
            ];
        }

        if ($payment === 'card') {
            $cardNumber = preg_replace(
                '/\D+/',
                '',
                (string)($data['cardNumber'] ?? '')
            );
            $expiry = trim((string)($data['expiry'] ?? ''));
            $cvv = preg_replace(
                '/\D+/',
                '',
                (string)($data['cvv'] ?? '')
            );

            if (
                strlen($cardNumber) < 13 ||
                strlen($cardNumber) > 19 ||
                !preg_match('/^\d{2}\/\d{2}$/', $expiry) ||
                strlen($cvv) !== 3
            ) {
                return [
                    'valid' => false,
                    'message' => 'Please enter valid demo card details.',
                ];
            }
        }

        return [
            'valid' => true,
            'message' => '',
        ];
    }

    public function placeOrder(array $data, array $items): array
    {
        $order = $this->orders->createOrder(
            $items,
            $this->getSummary($items),
            $data
        );

        $this->cart->clear();

        return $order;
    }
}
