<?php

namespace App\Models;

use PDO;

class News
{
    public static function list(PDO $db): array
    {
        $stmt = $db->query('SELECT * FROM news_items WHERE publish_at <= NOW() ORDER BY publish_at DESC');
        return $stmt->fetchAll();
    }

    public static function create(PDO $db, array $data): int
    {
        $stmt = $db->prepare('INSERT INTO news_items (title_ru, body_ru, title_en, body_en, cover_url, publish_at, is_poll, poll_options, poll_votes, created_at, updated_at) VALUES (:title_ru, :body_ru, :title_en, :body_en, :cover_url, :publish_at, :is_poll, :poll_options, :poll_votes, NOW(), NOW())');
        $stmt->execute($data);
        return (int) $db->lastInsertId();
    }
}
