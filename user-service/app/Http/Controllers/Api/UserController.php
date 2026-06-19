<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\OrderSummary;
use App\Support\UserToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $user = User::create($validated + ['is_active' => true]);
        $token = UserToken::issue($user);

        return response()->json($this->authPayload($user, $token), 201);
    }

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        if (! $user->is_active) {
            return response()->json(['message' => 'Account is inactive'], 403);
        }

        $token = UserToken::issue($user);

        return response()->json($this->authPayload($user, $token));
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);

        if ($user instanceof JsonResponse) {
            return $user;
        }

        UserToken::revoke($user);

        return response()->json(['message' => 'Logged out']);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);

        if ($user instanceof JsonResponse) {
            return $user;
        }

        return response()->json($this->publicUser($user));
    }

    public function index(): JsonResponse
    {
        return response()->json(
            User::select(['id', 'name', 'email', 'is_active', 'created_at', 'updated_at'])->get()
        );
    }

    public function show(int $id): JsonResponse
    {
        $user = User::find($id);

        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json($this->publicUser($user));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $user = User::create($validated);

        return response()->json($this->publicUser($user), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user = User::find($id);

        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', 'max:255', 'unique:users,email,'.$id],
            'password' => ['sometimes', 'required', 'string', 'min:6'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $user->fill($validated);
        $user->save();

        if (array_key_exists('is_active', $validated) && ! $user->is_active) {
            UserToken::revoke($user);
        }

        return response()->json($this->publicUser($user));
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $user = User::find($id);

        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $validated = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        $user->forceFill([
            'is_active' => (bool) $validated['is_active'],
        ])->save();

        if (! $user->is_active) {
            UserToken::revoke($user);
        }

        return response()->json($this->publicUser($user));
    }

    public function destroy(int $id): JsonResponse
    {
        $user = User::find($id);

        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        UserToken::revoke($user);
        $user->delete();

        return response()->json(['message' => 'User deleted']);
    }

    public function orders(int $id): JsonResponse
    {
        $user = User::select(['id', 'name', 'email'])->find($id);

        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $orders = $this->fetchOrdersForUser($id);

        if ($orders instanceof JsonResponse) {
            return $orders;
        }

        return response()->json([
            'user' => $this->publicUser($user),
            'orders' => $orders,
        ]);
    }

    public function orderSummary(int $id): JsonResponse
    {
        $user = User::find($id);

        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return $this->summaryResponse($user);
    }

    public function myOrderSummary(Request $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);

        if ($user instanceof JsonResponse) {
            return $user;
        }

        return $this->summaryResponse($user);
    }

    private function authenticatedUser(Request $request): User|JsonResponse
    {
        $user = UserToken::resolve($request->bearerToken());

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        if (! $user->is_active) {
            return response()->json(['message' => 'Account is inactive'], 403);
        }

        return $user;
    }

    private function summaryResponse(User $user): JsonResponse
    {
        $orders = $this->fetchOrdersForUser($user->id);

        if ($orders instanceof JsonResponse) {
            return $orders;
        }

        return response()->json([
            'user' => $this->publicUser($user),
            'summary' => OrderSummary::fromOrders($orders),
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>|JsonResponse
     */
    private function fetchOrdersForUser(int $userId): array|JsonResponse
    {
        try {
            $response = Http::timeout(60)->get(
                rtrim(config('services.order_service.url'), '/').'/api/orders',
                ['user_id' => $userId]
            );
        } catch (\Throwable) {
            return response()->json([
                'message' => 'Failed to fetch orders from OrderService',
            ], 502);
        }

        if (! $response->successful()) {
            return response()->json([
                'message' => 'Failed to fetch orders from OrderService',
            ], 502);
        }

        $orders = $response->json();

        return is_array($orders) ? $orders : [];
    }

    /**
     * @return array<string, mixed>
     */
    private function publicUser(User $user): array
    {
        return $user->only(['id', 'name', 'email', 'is_active', 'created_at', 'updated_at']);
    }

    /**
     * @return array<string, mixed>
     */
    private function authPayload(User $user, string $token): array
    {
        return [
            'user' => $this->publicUser($user),
            'token' => $token,
            'token_type' => 'Bearer',
        ];
    }
}
