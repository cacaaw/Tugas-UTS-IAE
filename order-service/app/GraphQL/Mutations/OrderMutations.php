<?php

namespace App\GraphQL\Mutations;

use App\Jobs\ProcessOrder;
use App\Models\Order;
use Illuminate\Support\Facades\Http;

class OrderMutations
{
    /**
     * Create a new order
     */
    public function createOrder($rootValue, array $args)
    {
        try {
            // Validate user
            $userResponse = Http::timeout(30)->get(
                rtrim(config('services.user_service.url'), '/') . '/api/users/' . $args['user_id']
            );

            if (!$userResponse->successful()) {
                return [
                    'success' => false,
                    'order' => null,
                    'message' => 'User not found',
                    'error' => 'Failed to fetch user from User Service',
                ];
            }

            // Validate food
            $foodResponse = Http::timeout(30)->get(
                rtrim(config('services.food_service.url'), '/') . '/api/foods/' . $args['food_id']
            );

            if (!$foodResponse->successful()) {
                return [
                    'success' => false,
                    'order' => null,
                    'message' => 'Food not found',
                    'error' => 'Failed to fetch food from Food Service',
                ];
            }

            $user = $userResponse->json();
            $food = $foodResponse->json();

            $currentQty = (int)($food['qty'] ?? 0);
            $orderQty = (int)$args['quantity'];

            if ($currentQty < $orderQty) {
                return [
                    'success' => false,
                    'order' => null,
                    'message' => 'Insufficient food stock',
                    'error' => 'Not enough stock available',
                ];
            }

            $unitPrice = (float)($food['price'] ?? 0);
            $totalPrice = $unitPrice * $orderQty;

            $order = Order::create([
                'user_id' => $args['user_id'],
                'user_name' => $user['name'] ?? 'Unknown User',
                'food_id' => $args['food_id'],
                'food_name' => $food['name'] ?? 'Unknown Food',
                'unit_price' => $unitPrice,
                'quantity' => $orderQty,
                'total_price' => $totalPrice,
                'status' => 'pending',
            ]);

            // Dispatch job to process order asynchronously
            ProcessOrder::dispatch($order);

            return [
                'success' => true,
                'order' => $order,
                'message' => 'Order created successfully',
                'error' => null,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'order' => null,
                'message' => 'Error creating order',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Update order status
     */
    public function updateOrderStatus($rootValue, array $args)
    {
        $order = Order::find($args['id']);

        if (!$order) {
            return null;
        }

        $order->update(['status' => $args['status']]);

        return $order;
    }

    /**
     * Update order
     */
    public function updateOrder($rootValue, array $args)
    {
        $order = Order::find($args['id']);

        if (!$order) {
            return null;
        }

        $data = [];
        if (isset($args['user_id'])) {
            $data['user_id'] = $args['user_id'];
        }
        if (isset($args['food_id'])) {
            $data['food_id'] = $args['food_id'];
        }
        if (isset($args['quantity'])) {
            $data['quantity'] = $args['quantity'];
        }
        if (isset($args['status'])) {
            $data['status'] = $args['status'];
        }

        $order->update($data);

        return $order->fresh();
    }

    /**
     * Delete order
     */
    public function deleteOrder($rootValue, array $args)
    {
        $order = Order::find($args['id']);

        if (!$order) {
            return [
                'success' => false,
                'message' => 'Order not found',
            ];
        }

        $order->delete();

        return [
            'success' => true,
            'message' => 'Order deleted successfully',
        ];
    }

    /**
     * Retry processing order
     */
    public function retryOrder($rootValue, array $args)
    {
        $order = Order::find($args['id']);

        if (!$order) {
            return [
                'success' => false,
                'order' => null,
                'message' => 'Order not found',
                'error' => 'Order does not exist',
            ];
        }

        // Reset order status and dispatch job again
        $order->update(['status' => 'pending']);
        ProcessOrder::dispatch($order);

        return [
            'success' => true,
            'order' => $order,
            'message' => 'Order processing retried',
            'error' => null,
        ];
    }
}
