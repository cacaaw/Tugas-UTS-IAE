<?php

namespace App\GraphQL\Queries;

use App\Models\User;
use App\Support\OrderSummary;
use App\Support\UserToken;
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

        return [
            'user' => $user,
            'orders' => $this->ordersForUser($user->id),
        ];
    }

    /**
     * Get user order summary from Order Service
     */
    public function userOrderSummary($rootValue, array $args)
    {
        $user = User::find($args['id']);

        if (!$user) {
            return null;
        }

        return [
            'user' => $user,
            'summary' => OrderSummary::fromOrders($this->ordersForUser($user->id)),
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

        if (!$user->is_active) {
            return null;
        }

        return [
            'user' => $user,
            'token' => UserToken::issue($user),
            'token_type' => 'Bearer',
        ];
    }

    /**
     * Get authenticated user by token
     */
    public function me($rootValue, array $args)
    {
        $user = UserToken::resolve($args['token']);

        if (!$user || !$user->is_active) {
            return null;
        }

        return $user;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function ordersForUser(int $userId): array
    {
        try {
            $response = Http::timeout(60)->get(
                rtrim(config('services.order_service.url'), '/') . '/api/orders',
                ['user_id' => $userId]
            );
        } catch (\Throwable) {
            return [];
        }

        if (!$response->successful()) {
            return [];
        }

        $orders = $response->json();

        return is_array($orders) ? $orders : [];
    }
}
