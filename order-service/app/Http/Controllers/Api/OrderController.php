<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessOrder;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Order::query()->latest('id');

        if ($request->filled('user_id')) {
            $query->where('user_id', (int) $request->query('user_id'));
        }

        return response()->json($query->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'min:1'],
            'food_id' => ['required', 'integer', 'min:1'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $userResponse = Http::timeout(30)->get(
            rtrim(config('services.user_service.url'), '/').'/api/users/'.$validated['user_id']
        );

        if (! $userResponse->successful()) {
            return response()->json([
                'message' => 'Failed to fetch user from UserService',
            ], 502);
        }

        $foodResponse = Http::timeout(30)->get(
            rtrim(config('services.food_service.url'), '/').'/api/foods/'.$validated['food_id']
        );

        if (! $foodResponse->successful()) {
            return response()->json([
                'message' => 'Failed to fetch food from FoodService',
            ], 502);
        }

        $user = $userResponse->json();
        $food = $foodResponse->json();

        $currentQty = (int) ($food['qty'] ?? 0);
        $orderQty = (int) $validated['quantity'];

        if ($currentQty < $orderQty) {
            return response()->json([
                'message' => 'Insufficient food stock',
            ], 400);
        }

        $unitPrice = (float) ($food['price'] ?? 0);
        $totalPrice = $unitPrice * $orderQty;

        $order = Order::create([
            'user_id' => $validated['user_id'],
            'user_name' => $user['name'] ?? 'Unknown User',
            'food_id' => $validated['food_id'],
            'food_name' => $food['name'] ?? 'Unknown Food',
            'unit_price' => $unitPrice,
            'quantity' => $orderQty,
            'total_price' => $totalPrice,
            'status' => 'pending',
        ]);

        // Dispatch job to process the order (deduct stock asynchronously)
        ProcessOrder::dispatch($order);

        return response()->json($order, 201);
    }

    public function show(int $id): JsonResponse
    {
        $order = Order::find($id);

        if (! $order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        return response()->json($order);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $order = Order::find($id);

        if (! $order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $validated = $request->validate([
            'user_id' => ['sometimes', 'required', 'integer', 'min:1'],
            'food_id' => ['sometimes', 'required', 'integer', 'min:1'],
            'quantity' => ['sometimes', 'required', 'integer', 'min:1'],
            'status' => ['sometimes', 'required', 'string', 'max:50'],
        ]);

        if (array_key_exists('user_id', $validated)) {
            $userResponse = Http::timeout(30)->get(
                rtrim(config('services.user_service.url'), '/').'/api/users/'.$validated['user_id']
            );

            if (! $userResponse->successful()) {
                return response()->json([
                    'message' => 'Failed to fetch user from UserService',
                ], 502);
            }

            $user = $userResponse->json();
            $order->user_id = (int) $validated['user_id'];
            $order->user_name = $user['name'] ?? 'Unknown User';
        }

        if (array_key_exists('food_id', $validated)) {
            $foodResponse = Http::timeout(30)->get(
                rtrim(config('services.food_service.url'), '/').'/api/foods/'.$validated['food_id']
            );

            if (! $foodResponse->successful()) {
                return response()->json([
                    'message' => 'Failed to fetch food from FoodService',
                ], 502);
            }

            $food = $foodResponse->json();
            $order->food_id = (int) $validated['food_id'];
            $order->food_name = $food['name'] ?? 'Unknown Food';
            $order->unit_price = (float) ($food['price'] ?? 0);
        }

        if (array_key_exists('quantity', $validated)) {
            $order->quantity = (int) $validated['quantity'];
        }

        if (array_key_exists('status', $validated)) {
            $order->status = $validated['status'];
        }

        $order->total_price = (float) $order->unit_price * (int) $order->quantity;
        $order->save();

        return response()->json($order);
    }

    public function destroy(int $id): JsonResponse
    {
        $order = Order::find($id);

        if (! $order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $order->delete();

        return response()->json(['message' => 'Order deleted']);
    }
}
