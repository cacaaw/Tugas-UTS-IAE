<?php

namespace App\GraphQL\Mutations;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserMutations
{
    /**
     * Create a new user
     */
    public function createUser($rootValue, array $args)
    {
        return User::create([
            'name' => $args['name'],
            'email' => $args['email'],
            'password' => Hash::make($args['password']),
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

        $user->update($data);

        return $user;
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

        $user->delete();

        return [
            'success' => true,
            'message' => 'User deleted successfully',
        ];
    }
}
