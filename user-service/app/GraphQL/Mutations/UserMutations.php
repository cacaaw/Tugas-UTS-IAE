<?php

namespace App\GraphQL\Mutations;

use App\Models\User;
use App\Support\UserToken;
use Illuminate\Support\Facades\Hash;

class UserMutations
{
    /**
     * Register a new active user and return auth token
     */
    public function registerUser($rootValue, array $args)
    {
        $user = User::create([
            'name' => $args['name'],
            'email' => $args['email'],
            'password' => Hash::make($args['password']),
            'is_active' => true,
        ]);

        return [
            'user' => $user,
            'token' => UserToken::issue($user),
            'token_type' => 'Bearer',
        ];
    }

    /**
     * Create a new user
     */
    public function createUser($rootValue, array $args)
    {
        return User::create([
            'name' => $args['name'],
            'email' => $args['email'],
            'password' => Hash::make($args['password']),
            'is_active' => (bool) ($args['is_active'] ?? true),
        ]);
    }

    /**
     * Update user
     */
    public function updateUser($rootValue, array $args)
    {
        $user = User::find($args['id']);

        if (!$user) {
            return null;
        }

        $data = [];
        if (isset($args['name'])) {
            $data['name'] = $args['name'];
        }
        if (isset($args['email'])) {
            $data['email'] = $args['email'];
        }
        if (isset($args['password'])) {
            $data['password'] = Hash::make($args['password']);
        }
        if (array_key_exists('is_active', $args)) {
            $data['is_active'] = (bool) $args['is_active'];
        }

        $user->update($data);

        if (array_key_exists('is_active', $data) && ! $user->is_active) {
            UserToken::revoke($user);
        }

        return $user;
    }

    /**
     * Activate or deactivate user account
     */
    public function updateUserStatus($rootValue, array $args)
    {
        $user = User::find($args['id']);

        if (!$user) {
            return null;
        }

        $user->forceFill([
            'is_active' => (bool) $args['is_active'],
        ])->save();

        if (!$user->is_active) {
            UserToken::revoke($user);
        }

        return $user;
    }

    /**
     * Logout user by revoking token
     */
    public function logout($rootValue, array $args)
    {
        $user = UserToken::resolve($args['token']);

        if (!$user) {
            return [
                'success' => false,
                'message' => 'Invalid token',
            ];
        }

        UserToken::revoke($user);

        return [
            'success' => true,
            'message' => 'Logged out successfully',
        ];
    }

    /**
     * Delete user
     */
    public function deleteUser($rootValue, array $args)
    {
        $user = User::find($args['id']);

        if (!$user) {
            return [
                'success' => false,
                'message' => 'User not found',
            ];
        }

        UserToken::revoke($user);
        $user->delete();

        return [
            'success' => true,
            'message' => 'User deleted successfully',
        ];
    }
}
