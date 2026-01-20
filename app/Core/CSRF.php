<?php

namespace App\Core;

class CSRF
{
    public static function token(): string
    {
        if (!isset($_SESSION['_token'])) {
            $_SESSION['_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_token'];
    }

    public static function verify(string $token): bool
    {
        return hash_equals($_SESSION['_token'] ?? '', $token);
    }
}
