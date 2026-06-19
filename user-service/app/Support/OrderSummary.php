<?php

namespace App\Support;

class OrderSummary
{
    /**
     * @param  array<int, array<string, mixed>>  $orders
     * @return array<string, mixed>
     */
    public static function fromOrders(array $orders): array
    {
        $totalOrders = count($orders);
        $totalSpent = array_reduce(
            $orders,
            fn (float $carry, array $order): float => $carry + (float) ($order['total_price'] ?? 0),
            0.0
        );

        $statusCounts = [];
        $activeOrders = 0;
        $completedOrders = 0;
        $failedOrders = 0;
        $cancelledOrders = 0;

        foreach ($orders as $order) {
            $status = strtolower((string) ($order['status'] ?? 'unknown'));
            $statusCounts[$status] = ($statusCounts[$status] ?? 0) + 1;

            if (in_array($status, ['pending', 'processing'], true)) {
                $activeOrders++;
            }

            if (in_array($status, ['created', 'completed', 'delivered'], true)) {
                $completedOrders++;
            }

            if (str_starts_with($status, 'failed')) {
                $failedOrders++;
            }

            if (in_array($status, ['cancelled', 'canceled'], true)) {
                $cancelledOrders++;
            }
        }

        return [
            'total_orders' => $totalOrders,
            'total_spent' => $totalSpent,
            'average_order_value' => $totalOrders > 0 ? $totalSpent / $totalOrders : 0,
            'active_orders' => $activeOrders,
            'completed_orders' => $completedOrders,
            'failed_orders' => $failedOrders,
            'cancelled_orders' => $cancelledOrders,
            'latest_order' => $orders[0] ?? null,
            'status_breakdown' => array_map(
                fn (string $status, int $count): array => [
                    'status' => $status,
                    'count' => $count,
                ],
                array_keys($statusCounts),
                $statusCounts
            ),
        ];
    }
}
