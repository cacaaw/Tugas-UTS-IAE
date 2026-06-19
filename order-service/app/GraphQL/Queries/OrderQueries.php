<?php

namespace App\GraphQL\Queries;

use App\Models\Order;
use Illuminate\Support\Facades\Http;

class OrderQueries
{
    /**
     * Get all orders with pagination and filters
     */
    public function orders($rootValue, array $args)
    {
        $first = $args['first'] ?? 15;
        $page = $args['page'] ?? 1;

        $query = Order::query()->latest('id');

        if (isset($args['status'])) {
            $query->where('status', $args['status']);
        }

        if (isset($args['user_id'])) {
            $query->where('user_id', $args['user_id']);
        }

        $orders = $query->paginate($first, ['*'], 'page', $page);

        return [
            'data' => $orders->items(),
            'current_page' => $orders->currentPage(),
            'per_page' => $orders->perPage(),
            'total' => $orders->total(),
        ];
    }

    /**
     * Get a single order by ID
     */
    public function order($rootValue, array $args)
    {
        return Order::find($args['id']);
    }

    /**
     * Get orders by user ID
     */
    public function ordersByUser($rootValue, array $args)
    {
        return Order::where('user_id', $args['user_id'])->latest('id')->get();
    }

    /**
     * Get order statistics
     */
    public function orderStats($rootValue, array $args)
    {
        $allOrders = Order::all();

        $totalRevenue = (float)$allOrders->sum('total_price');
        $totalOrders = $allOrders->count();
        $averageOrder = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;
        $uniqueUsers = $allOrders->pluck('user_id')->unique()->count();

        $statusBreakdown = $allOrders
            ->groupBy('status')
            ->map(fn($group) => [
                'status' => $group->first()->status,
                'count' => $group->count(),
            ])
            ->values();

        return [
            'total_orders' => $totalOrders,
            'total_revenue' => $totalRevenue,
            'average_order_value' => $averageOrder,
            'unique_users' => $uniqueUsers,
            'status_breakdown' => $statusBreakdown,
        ];
    }

    /**
     * Get service health status
     */
    public function serviceHealth($rootValue, array $args)
    {
        return $this->checkServicesHealth();
    }

    private function checkServicesHealth()
    {
        $userServiceStatus = $this->checkServiceStatus('user_service');
        $foodServiceStatus = $this->checkServiceStatus('food_service');

        return [
            'user_service' => $userServiceStatus,
            'food_service' => $foodServiceStatus,
            'queue_status' => 'healthy', // Check queue status if needed
        ];
    }

    private function checkServiceStatus($serviceName)
    {
        try {
            $response = Http::timeout(3)->get(
                rtrim(config('services.' . $serviceName . '.url'), '/') . '/api/health'
            );

            return [
                'status' => $response->successful() ? 'healthy' : 'unhealthy',
                'message' => $response->successful() ? 'Service is running' : 'Service returned error',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'message' => 'Service is unreachable: ' . $e->getMessage(),
            ];
        }
    }
}
