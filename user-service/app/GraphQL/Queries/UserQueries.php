<?php

namespace App\GraphQL\Queries;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

class UserQueries
{
    /**
     * Get all users with pagination
     */
    public function users($rootValue, array $args)
    {
        $first = $args['first'] ?? 15;
        $page = $args['page'] ?? 1;

        $users = User::paginate($first, ['*'], 'page', $page);

        return [
            'data' => $users->items(),
            'current_page' => $users->currentPage(),
            'per_page' => $users->perPage(),
            'total' => $users->total(),
        ];
    }

    /**
     * Get a single user by ID
     */
    public function user($rootValue, array $args)
    {
        return User::find($args['id']);
    }

    /**
     * Get user with their orders from Order Service
     */
    public function userWithOrders($rootValue, array $args)
    {
        $user = User::find($args['id']);

        if (!$user) {
            return null;
        }

        // Call Order Service to get user's orders
        try {
            $response = Http::timeout(5)->get(
                rtrim(config('services.order_service.url'), '/') . '/api/orders',
                ['user_id' => $user->id]
            );

            $orders = $response->successful() ? $response->json() : [];
        } catch (\Exception $e) {
            $orders = [];
        }

        return [
            'user' => $user,
            'orders' => $orders,
        ];
    }

    /**
     * Login user
     */
    public function login($rootValue, array $args)
    {
        $user = User::where('email', $args['email'])->first();

        if (!$user || !Hash::check($args['password'], $user->password)) {
            return null;
        }

        return [
            'user' => $user,
            'token' => null, // Can integrate JWT if needed
        ];
    }
}
