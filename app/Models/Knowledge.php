<?php

namespace App\Models;

use PDO;

class Knowledge
{
    public static function list(PDO $db, string $status = 'published'): array
    {
        $stmt = $db->prepare('SELECT * FROM knowledge_articles WHERE status = :status ORDER BY updated_at DESC');
        $stmt->execute(['status' => $status]);
        return $stmt->fetchAll();
    }

    public static function find(PDO $db, int $id): ?array
    {
        $stmt = $db->prepare('SELECT * FROM knowledge_articles WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $article = $stmt->fetch();
        return $article ?: null;
    }

    public static function create(PDO $db, array $data): int
    {
        $stmt = $db->prepare('INSERT INTO knowledge_articles (slug, title_ru, body_ru, title_en, body_en, tags, created_by, updated_by, status, usefulness_up, usefulness_down, created_at, updated_at) VALUES (:slug, :title_ru, :body_ru, :title_en, :body_en, :tags, :created_by, :updated_by, :status, 0, 0, NOW(), NOW())');
        $stmt->execute($data);
        return (int) $db->lastInsertId();
    }

    public static function update(PDO $db, int $id, array $data): void
    {
        $fields = [];
        foreach ($data as $key => $value) {
            $fields[] = "$key = :$key";
        }
        $sql = 'UPDATE knowledge_articles SET ' . implode(', ', $fields) . ', updated_at = NOW() WHERE id = :id';
        $data['id'] = $id;
        $stmt = $db->prepare($sql);
        $stmt->execute($data);
    }
}
