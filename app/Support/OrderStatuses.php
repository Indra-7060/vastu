<?php

namespace App\Support;

class OrderStatuses
{
    public const PLACED = 'placed';
    public const PACKED = 'packed';
    public const SHIPPED = 'shipped';
    public const DELIVERED = 'delivered';
    public const CANCELLED = 'cancelled';

    public static function all(): array
    {
        return [
            self::PLACED => 'Placed',
            self::PACKED => 'Packed',
            self::SHIPPED => 'Shipped',
            self::DELIVERED => 'Delivered',
            self::CANCELLED => 'Cancelled',
        ];
    }

    public static function label(string $status): string
    {
        return self::all()[$status] ?? ucfirst($status);
    }

    public static function keys(): array
    {
        return array_keys(self::all());
    }

    /**
     * Next allowed statuses from current status.
     */
    public static function nextOptions(string $current): array
    {
        $current = strtolower($current);

        // Treat legacy "pending" same as placed
        if ($current === 'pending') {
            $current = self::PLACED;
        }

        return match ($current) {
            self::PLACED => [
                self::PACKED => 'Packed',
                self::SHIPPED => 'Shipped',
                self::DELIVERED => 'Delivered',
                self::CANCELLED => 'Cancelled',
            ],
            self::PACKED => [
                self::SHIPPED => 'Shipped',
                self::DELIVERED => 'Delivered',
                self::CANCELLED => 'Cancelled',
            ],
            self::SHIPPED => [
                self::DELIVERED => 'Delivered',
                self::CANCELLED => 'Cancelled',
            ],
            self::DELIVERED, self::CANCELLED => [],
            default => [
                self::PACKED => 'Packed',
                self::SHIPPED => 'Shipped',
                self::DELIVERED => 'Delivered',
                self::CANCELLED => 'Cancelled',
            ],
        };
    }

    public static function defaultMessage(string $status): string
    {
        return match (strtolower($status)) {
            self::PLACED => 'Your Order has been placed.',
            self::PACKED => 'Your Order has been packed.',
            self::SHIPPED => 'Your Order has been shipped.',
            self::DELIVERED => 'Your Order has been delivered.',
            self::CANCELLED => 'Your order has been cancelled.',
            default => 'Order status updated.',
        };
    }

    public static function badgeClass(string $status): string
    {
        return match (strtolower($status)) {
            self::CANCELLED => 'badge-cancelled',
            self::DELIVERED => 'badge-delivered',
            self::SHIPPED => 'badge-shipped',
            self::PACKED => 'badge-packed',
            default => 'badge-placed',
        };
    }
}
