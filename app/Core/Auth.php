<?php

namespace App\Core;

use App\Models\User;

class Auth
{
    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public static function check(): bool
    {
        return isset($_SESSION['user']);
    }

    public static function login(array $user): void
    {
        $_SESSION['user'] = $user;
    }

    public static function logout(): void
    {
        unset($_SESSION['user']);
    }

    public static function requireRole(array $roles): void
    {
        if (!self::check()) {
            header('Location: /login');
            exit;
        }
        $role = self::user()['role'] ?? null;
        if (!in_array($role, $roles, true)) {
            http_response_code(403);
            include __DIR__ . '/../Views/errors/403.php';
            exit;
        }
    }

    public static function canManageUsers(): bool
    {
        $role = self::user()['role'] ?? null;
        return in_array($role, ['admin', 'agent'], true);
    }

    public static function canViewTicket(array $ticket): bool
    {
        $user = self::user();
        if (!$user) {
            return false;
        }
        if ($user['role'] === 'admin') {
            return true;
        }
        if ($user['role'] === 'agent') {
            return $ticket['assignee_id'] === $user['id'] || $ticket['status'] !== 'closed';
        }
        return $ticket['author_id'] === $user['id'];
    }

    public static function canChatInTicket(array $ticket): bool
    {
        return self::canViewTicket($ticket);
    }

    public static function canEditTicket(array $ticket): bool
    {
        $user = self::user();
        if (!$user) {
            return false;
        }
        if ($user['role'] === 'admin') {
            return true;
        }
        if ($user['role'] === 'agent') {
            return true;
        }
        return $ticket['author_id'] === $user['id'] && $ticket['status'] === 'new';
    }
}
