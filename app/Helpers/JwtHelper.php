<?php

namespace App\Helpers;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtHelper
{
    // Encode JWT
    public static function encode(array $payload): string
    {
        $secretKey = config('app.jwt_secret');
        return JWT::encode($payload, $secretKey, 'HS256');
    }

    // Decode JWT
    public static function decode(string $token): ?object
    {
        try {
            $secretKey = config('app.jwt_secret');
            return JWT::decode($token, new Key($secretKey, 'HS256'));
        } catch (\Throwable $e) {
            return null; // Invalid token
        }
    }
}
