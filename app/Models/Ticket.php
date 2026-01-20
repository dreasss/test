<?php

namespace App\Models;

use PDO;

class Ticket
{
    public static function create(PDO $db, array $data): int
    {
        $stmt = $db->prepare('INSERT INTO tickets (author_id, assignee_id, title, description, priority, desired_at, status, building, room, attachments, created_at, updated_at) VALUES (:author_id, :assignee_id, :title, :description, :priority, :desired_at, :status, :building, :room, :attachments, NOW(), NOW())');
        $stmt->execute($data);
        return (int) $db->lastInsertId();
    }

    public static function find(PDO $db, int $id): ?array
    {
        $stmt = $db->prepare('SELECT * FROM tickets WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $ticket = $stmt->fetch();
        return $ticket ?: null;
    }

    public static function update(PDO $db, int $id, array $data): void
    {
        $fields = [];
        foreach ($data as $key => $value) {
            $fields[] = "$key = :$key";
        }
        $sql = 'UPDATE tickets SET ' . implode(', ', $fields) . ', updated_at = NOW() WHERE id = :id';
        $data['id'] = $id;
        $stmt = $db->prepare($sql);
        $stmt->execute($data);
    }

    public static function listForUser(PDO $db, array $user, ?string $filter = null): array
    {
        $where = [];
        $params = [];
        if ($user['role'] === 'user') {
            $where[] = 'author_id = :user_id';
            $params['user_id'] = $user['id'];
        }
        if ($user['role'] === 'agent') {
            if ($filter === 'assigned') {
                $where[] = 'assignee_id = :user_id';
                $params['user_id'] = $user['id'];
            }
        }
        if ($filter === 'open') {
            $where[] = "status IN ('new','assigned','in_progress','waiting_user','reopened')";
        }
        if ($filter === 'closed') {
            $where[] = "status IN ('resolved','closed')";
        }
        $sql = 'SELECT * FROM tickets';
        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY created_at DESC';
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function search(PDO $db, array $user, string $query): array
    {
        $params = ['query' => '%' . $query . '%'];
        $conditions = '(title LIKE :query OR description LIKE :query OR building LIKE :query OR room LIKE :query)';
        if ($user['role'] === 'user') {
            $conditions .= ' AND author_id = :user_id';
            $params['user_id'] = $user['id'];
        }
        $stmt = $db->prepare('SELECT * FROM tickets WHERE ' . $conditions . ' ORDER BY created_at DESC');
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function overdue(PDO $db): array
    {
        $stmt = $db->query("SELECT * FROM tickets WHERE status IN ('new','assigned','in_progress','waiting_user','reopened') AND (last_agent_reply_at IS NULL OR last_agent_reply_at < DATE_SUB(NOW(), INTERVAL 24 HOUR))");
        return $stmt->fetchAll();
    }
}
