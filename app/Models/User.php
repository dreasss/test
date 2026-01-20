<?php

namespace App\Models;

use App\Core\DB;
use PDO;

class User
{
    public static function findByEmail(PDO $db, string $email): ?array
    {
        $stmt = $db->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function findById(PDO $db, int $id): ?array
    {
        $stmt = $db->prepare('SELECT * FROM users WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function create(PDO $db, array $data): int
    {
        $stmt = $db->prepare('INSERT INTO users (role, first_name, last_name, email, phone, department, building, room, locale, avatar_url, sso_subject, password_hash, created_at, updated_at) VALUES (:role, :first_name, :last_name, :email, :phone, :department, :building, :room, :locale, :avatar_url, :sso_subject, :password_hash, NOW(), NOW())');
        $stmt->execute($data);
        return (int) $db->lastInsertId();
    }

    public static function update(PDO $db, int $id, array $data): void
    {
        $fields = [];
        foreach ($data as $key => $value) {
            $fields[] = "$key = :$key";
        }
        $sql = 'UPDATE users SET ' . implode(', ', $fields) . ', updated_at = NOW() WHERE id = :id';
        $data['id'] = $id;
        $stmt = $db->prepare($sql);
        $stmt->execute($data);
    }

    public static function all(PDO $db): array
    {
        return $db->query('SELECT * FROM users ORDER BY created_at DESC')->fetchAll();
    }
}
