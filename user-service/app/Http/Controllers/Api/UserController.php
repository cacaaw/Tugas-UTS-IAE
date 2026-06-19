<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
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

        return response()->json($user->only(['id', 'name', 'email']));
    }

    public function index(): JsonResponse
    {
        return response()->json(User::select(['id', 'name', 'email'])->get());
    }

    public function show(int $id): JsonResponse
    {
        $user = User::select(['id', 'name', 'email'])->find($id);

        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json($user);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $user = User::create($validated);

        return response()->json(
            $user->only(['id', 'name', 'email']),
            201
        );
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
        ]);

        $user->fill($validated);
        $user->save();

        return response()->json($user->only(['id', 'name', 'email']));
    }

    public function destroy(int $id): JsonResponse
    {
        $user = User::find($id);

        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted']);
    }

    public function orders(int $id): JsonResponse
    {
        $user = User::select(['id', 'name', 'email'])->find($id);

        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $response = Http::timeout(5)->get(
            rtrim(config('services.order_service.url'), '/').'/api/orders',
            ['user_id' => $id]
        );

        if (! $response->successful()) {
            return response()->json([
                'message' => 'Failed to fetch orders from OrderService',
            ], 502);
        }

        return response()->json([
            'user' => $user,
            'orders' => $response->json(),
        ]);
    }
}
