<?php
/**
 * Harvestly shared presentation helpers.
 *
 * The same status labels and tones are used by the Buyer, Farmer, Courier
 * Partner and Admin modules so every role displays a compatible version of the
 * one Harvestly order state.
 */
require_once __DIR__ . '/../config/app.php';

if (!function_exists('harvestlyStatusLabels')) {
    /** The canonical Harvestly order / delivery / earning status vocabulary. */
    function harvestlyStatusLabels(): array
    {
        return [
            'PENDING_PAYMENT'    => 'Pending Payment',
            'PAID'               => 'Paid',
            'ACCEPTED'           => 'Accepted',
            'PREPARING'          => 'Preparing',
            'READY_FOR_DELIVERY' => 'Ready for Delivery',
            'PENDING_ASSIGNMENT' => 'Pending Assignment',
            'ASSIGNED'           => 'Assigned',
            'PICKED_UP'          => 'Picked Up',
            'IN_TRANSIT'         => 'In Transit',
            'OUT_FOR_DELIVERY'   => 'Out for Delivery',
            'DELIVERED'          => 'Delivered',
            'COMPLETED'          => 'Completed',
            'UNDELIVERABLE'      => 'Undeliverable',
            'REJECTED'           => 'Rejected',
            'CANCELLED'          => 'Cancelled',
            // Earnings and complaints reuse the same label helper.
            'HELD'               => 'Held',
            'PENDING_PAYOUT'     => 'Pending Payout',
            'EARNED'             => 'Earned',
            'PAID_OUT'           => 'Paid',
            'OPEN'               => 'Open',
            'UNDER_REVIEW'       => 'Under Review',
            'RESOLVED'           => 'Resolved',
            'AVAILABLE'          => 'Available',
            'UNAVAILABLE'        => 'Unavailable',
            'APPROVED'           => 'Approved',
            'ACTIVE'             => 'Active',
            'PENDING'            => 'Pending',
            'SUSPENDED'          => 'Suspended',
            'INACTIVE'           => 'Inactive',
            'SOLD_OUT'           => 'Sold Out',
            'AVAILABLE_NOW'      => 'Available Now',
            'HARVEST_SOON'       => 'Harvest Soon',
            'SEASONAL'           => 'Seasonal',
            'ORGANIC'            => 'Organic',
            'CONVENTIONAL'       => 'Conventional',
            'MIXED'              => 'Mixed',
            'SUCCESS'            => 'Success',
            'FAILED'             => 'Failed',
        ];
    }
}

if (!function_exists('harvestlyStatusLabel')) {
    /** Human label for any Harvestly status value. */
    function harvestlyStatusLabel(?string $status): string
    {
        $status = strtoupper(trim((string)$status));
        if ($status === '') return 'Unknown';
        $labels = harvestlyStatusLabels();
        return $labels[$status] ?? ucwords(strtolower(str_replace('_', ' ', $status)));
    }
}

if (!function_exists('harvestlyStatusTone')) {
    /**
     * Tone key shared by every role's badge styling.
     * ok | warn | info | bad | muted
     */
    function harvestlyStatusTone(?string $status): string
    {
        switch (strtoupper(trim((string)$status))) {
            case 'COMPLETED':
            case 'PAID':
            case 'PAID_OUT':
            case 'ACCEPTED':
            case 'DELIVERED':
            case 'EARNED':
            case 'RESOLVED':
            case 'APPROVED':
            case 'AVAILABLE':
            case 'ACTIVE':
            case 'SUCCESS':
                return 'ok';
            case 'PENDING_PAYMENT':
            case 'PENDING_ASSIGNMENT':
            case 'READY_FOR_DELIVERY':
            case 'HELD':
            case 'PENDING_PAYOUT':
            case 'OPEN':
            case 'PENDING':
            case 'AVAILABLE_NOW':
            case 'HARVEST_SOON':
            case 'SEASONAL':
                return 'warn';
            case 'PREPARING':
            case 'ASSIGNED':
            case 'PICKED_UP':
            case 'IN_TRANSIT':
            case 'OUT_FOR_DELIVERY':
            case 'UNDER_REVIEW':
            case 'UNAVAILABLE':
                return 'info';
            case 'UNDELIVERABLE':
            case 'REJECTED':
            case 'CANCELLED':
            case 'SUSPENDED':
            case 'FAILED':
            case 'SOLD_OUT':
                return 'bad';
            default:
                return 'muted';
        }
    }
}

if (!function_exists('harvestlyMoney')) {
    /** Format an amount using the Harvestly display currency. */
    function harvestlyMoney($amount): string
    {
        return 'Rs. ' . number_format((float)$amount, 2);
    }
}

if (!function_exists('farmerCourierLabel')) {
    /** Backwards-compatible alias used by the Courier Partner layout. */
    function farmerCourierLabel(?string $status): string
    {
        return harvestlyStatusLabel($status);
    }
}

if (!function_exists('farmer_status_label')) {
    function farmer_status_label(?string $status): string
    {
        return harvestlyStatusLabel($status);
    }
}

if (!function_exists('farmer_status_tone')) {
    function farmer_status_tone(?string $status): string
    {
        return harvestlyStatusTone($status);
    }
}

if (!function_exists('farmer_money')) {
    function farmer_money($amount): string
    {
        return harvestlyMoney($amount);
    }
}

if (!function_exists('money')) {
    function money($amount): string
    {
        return harvestlyMoney($amount);
    }
}
