<?php

namespace App\Models;

use PDO;

class Message
{
    public static function create(PDO $db, array $data): int
    {
        $stmt = $db->prepare('INSERT INTO messages (ticket_id, author_id, body, attachments, created_at, is_system) VALUES (:ticket_id, :author_id, :body, :attachments, NOW(), :is_system)');
        $stmt->execute($data);
        return (int) $db->lastInsertId();
    }

    public static function list(PDO $db, int $ticketId): array
    {
        $stmt = $db->prepare('SELECT m.*, u.first_name, u.last_name, u.avatar_url FROM messages m JOIN users u ON m.author_id = u.id WHERE ticket_id = :ticket_id ORDER BY m.created_at ASC');
        $stmt->execute(['ticket_id' => $ticketId]);
        return $stmt->fetchAll();
    }
}
