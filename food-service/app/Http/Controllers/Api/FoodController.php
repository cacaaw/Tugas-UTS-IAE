<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Food;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FoodController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Food::all());
    }

    public function show(int $id): JsonResponse
    {
        $food = Food::find($id);

        if (! $food) {
            return response()->json(['message' => 'Food not found'], 404);
        }

        return response()->json($food);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'string'],
            'qty' => ['required', 'integer', 'min:0'],
        ]);

        $food = Food::create($validated);

        return response()->json($food, 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $food = Food::find($id);

        if (! $food) {
            return response()->json(['message' => 'Food not found'], 404);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'description' => ['sometimes', 'nullable', 'string'],
            'image' => ['sometimes', 'nullable', 'string'],
            'qty' => ['sometimes', 'required', 'integer', 'min:0'],
        ]);

        $food->fill($validated);
        $food->save();

        return response()->json($food);
    }

    public function stock(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'quantity' => ['sometimes', 'integer', 'min:0'],
        ]);

        $food = Food::find($id);

        if (! $food) {
            return response()->json(['message' => 'Food not found'], 404);
        }

        $requestedQuantity = array_key_exists('quantity', $validated)
            ? (int) $validated['quantity']
            : null;

        return response()->json([
            'food_id' => $food->id,
            'name' => $food->name,
            'qty' => (int) $food->qty,
            'requested_quantity' => $requestedQuantity,
            'is_available' => $requestedQuantity === null
                ? (int) $food->qty > 0
                : (int) $food->qty >= $requestedQuantity,
        ]);
    }

    public function updateStock(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:0'],
            'operation' => ['required', 'string', 'in:increase,decrease,set'],
        ]);

        $result = DB::transaction(function () use ($id, $validated): array {
            $food = Food::whereKey($id)->lockForUpdate()->first();

            if (! $food) {
                return ['status' => 404, 'payload' => ['message' => 'Food not found']];
            }

            $currentQty = (int) $food->qty;
            $quantity = (int) $validated['quantity'];

            $newQty = match ($validated['operation']) {
                'increase' => $currentQty + $quantity,
                'decrease' => $currentQty - $quantity,
                'set' => $quantity,
            };

            if ($newQty < 0) {
                return [
                    'status' => 409,
                    'payload' => [
                        'message' => 'Insufficient food stock',
                        'food_id' => $food->id,
                        'current_qty' => $currentQty,
                        'requested_quantity' => $quantity,
                    ],
                ];
            }

            $food->qty = $newQty;
            $food->save();

            return [
                'status' => 200,
                'payload' => [
                    'message' => 'Food stock updated',
                    'food_id' => $food->id,
                    'operation' => $validated['operation'],
                    'previous_qty' => $currentQty,
                    'qty' => (int) $food->qty,
                    'food' => $food->fresh(),
                ],
            ];
        });

        return response()->json($result['payload'], $result['status']);
    }

    public function destroy(int $id): JsonResponse
    {
        $food = Food::find($id);

        if (! $food) {
            return response()->json(['message' => 'Food not found'], 404);
        }

        $food->delete();

        return response()->json(['message' => 'Food deleted']);
    }
}
