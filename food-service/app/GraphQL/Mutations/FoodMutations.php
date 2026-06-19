<?php

namespace App\GraphQL\Mutations;

use App\Models\Food;

class FoodMutations
{
    /**
     * Create a new food
     */
    public function createFood($rootValue, array $args)
    {
        return Food::create([
            'name' => $args['name'],
            'description' => $args['description'] ?? null,
            'price' => $args['price'],
            'image' => $args['image'] ?? null,
            'qty' => $args['qty'],
        ]);
    }

    /**
     * Update food
     */
    public function updateFood($rootValue, array $args)
    {
        $food = Food::find($args['id']);

        if (!$food) {
            return null;
        }

        $data = [];
        if (isset($args['name'])) {
            $data['name'] = $args['name'];
        }
        if (isset($args['description'])) {
            $data['description'] = $args['description'];
        }
        if (isset($args['price'])) {
            $data['price'] = $args['price'];
        }
        if (isset($args['image'])) {
            $data['image'] = $args['image'];
        }
        if (isset($args['qty'])) {
            $data['qty'] = $args['qty'];
        }

        $food->update($data);

        return $food;
    }

    /**
     * Update food quantity/stock
     */
    public function updateFoodQuantity($rootValue, array $args)
    {
        $food = Food::find($args['id']);

        if (!$food) {
            return null;
        }

        $food->update(['qty' => $args['qty']]);

        return $food;
    }

    /**
     * Delete food
     */
    public function deleteFood($rootValue, array $args)
    {
        $food = Food::find($args['id']);

        if (!$food) {
            return [
                'success' => false,
                'message' => 'Food not found',
            ];
        }

        $food->delete();

        return [
            'success' => true,
            'message' => 'Food deleted successfully',
        ];
    }

    /**
     * Deduct food stock (called by Order Service)
     */
    public function deductStock($rootValue, array $args)
    {
        $food = Food::find($args['id']);

        if (!$food) {
            return [
                'success' => false,
                'message' => 'Food not found',
                'food' => null,
            ];
        }

        if ($food->qty < $args['quantity']) {
            return [
                'success' => false,
                'message' => 'Insufficient stock',
                'food' => $food,
            ];
        }

        $food->decrement('qty', $args['quantity']);

        return [
            'success' => true,
            'message' => 'Stock deducted successfully',
            'food' => $food->fresh(),
        ];
    }
}
