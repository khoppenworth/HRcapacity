<?php

namespace App\Auth;

class TokenRepository
{
    private static array $map = [];

    public static function issue(int $userId): string
    {
        $token = bin2hex(random_bytes(20));
        self::$map[$token] = $userId;

        return $token;
    }

    public static function resolve(string $token): ?int
    {
        return self::$map[$token] ?? null;
    }

    public static function clear(): void
    {
        self::$map = [];
    }
}
