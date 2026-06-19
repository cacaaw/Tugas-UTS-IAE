<?php

namespace App\GraphQL\Queries;

use App\Models\Food;

class FoodQueries
{
    /**
     * Get all foods with pagination
     */
    public function foods($rootValue, array $args)
    {
        $first = $args['first'] ?? 15;
        $page = $args['page'] ?? 1;

        $foods = Food::paginate($first, ['*'], 'page', $page);

        return [
            'data' => $foods->items(),
            'current_page' => $foods->currentPage(),
            'per_page' => $foods->perPage(),
            'total' => $foods->total(),
        ];
    }

    /**
     * Get a single food by ID
     */
    public function food($rootValue, array $args)
    {
        return Food::find($args['id']);
    }

    /**
     * Get foods with low stock
     */
    public function lowStockFoods($rootValue, array $args)
    {
        $threshold = $args['threshold'] ?? 10;

        return Food::where('qty', '<=', $threshold)->get();
    }

    /**
     * Search foods by name
     */
    public function searchFoods($rootValue, array $args)
    {
        $query = $args['query'] ?? '';

        return Food::where('name', 'like', '%' . $query . '%')
            ->orWhere('description', 'like', '%' . $query . '%')
            ->get();
    }
}
