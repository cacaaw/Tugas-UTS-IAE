<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Str;

class UserToken
{
    public static function issue(User $user): string
    {
        $token = Str::random(80);

        $user->forceFill([
            'api_token_hash' => self::hash($token),
            'api_token_issued_at' => now(),
        ])->save();

        return $token;
    }

    public static function resolve(?string $token): ?User
    {
        if (! $token) {
            return null;
        }

        return User::where('api_token_hash', self::hash($token))->first();
    }

    public static function revoke(User $user): void
    {
        $user->forceFill([
            'api_token_hash' => null,
            'api_token_issued_at' => null,
        ])->save();
    }

    private static function hash(string $token): string
    {
        return hash('sha256', $token);
    }
}
