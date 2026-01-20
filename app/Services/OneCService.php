<?php

namespace App\Services;

use PDO;

class OneCService
{
    private array $config;
    private PDO $db;

    public function __construct(PDO $db, array $config)
    {
        $this->db = $db;
        $this->config = $config;
    }

    public function enqueue(string $entityType, int $entityId, string $action, array $payload): void
    {
        $stmt = $this->db->prepare('INSERT INTO sync_queue (entity_type, entity_id, action, payload, attempt_count, status, created_at, updated_at) VALUES (:entity_type, :entity_id, :action, :payload, 0, :status, NOW(), NOW())');
        $stmt->execute([
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'action' => $action,
            'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE),
            'status' => 'pending',
        ]);
    }

    public function processQueue(): void
    {
        $stmt = $this->db->query("SELECT * FROM sync_queue WHERE status = 'pending' ORDER BY created_at ASC LIMIT 20");
        $items = $stmt->fetchAll();
        foreach ($items as $item) {
            $this->processItem($item);
        }
    }

    private function processItem(array $item): void
    {
        $provider = $this->config['provider'] ?? 'itilium';
        $providerConfig = $this->config['providers'][$provider] ?? null;
        if (!$providerConfig || empty($providerConfig['base_url'])) {
            $this->markFailed($item['id'], 'Provider not configured');
            return;
        }
        $payload = json_decode($item['payload'], true) ?: [];
        $endpoint = rtrim($providerConfig['base_url'], '/') . '/api/sync';
        $response = $this->sendRequest($endpoint, $providerConfig['token'] ?? '', $payload);
        if ($response['ok']) {
            $stmt = $this->db->prepare("UPDATE sync_queue SET status = 'done', updated_at = NOW() WHERE id = :id");
            $stmt->execute(['id' => $item['id']]);
            return;
        }
        $this->markFailed($item['id'], $response['error']);
    }

    private function markFailed(int $id, string $error): void
    {
        $stmt = $this->db->prepare("UPDATE sync_queue SET status = 'failed', attempt_count = attempt_count + 1, last_error = :last_error, updated_at = NOW() WHERE id = :id");
        $stmt->execute(['id' => $id, 'last_error' => $error]);
    }

    private function sendRequest(string $url, string $token, array $payload): array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token,
            ],
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
            CURLOPT_TIMEOUT => 15,
        ]);
        $body = curl_exec($ch);
        $err = curl_error($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($err) {
            return ['ok' => false, 'error' => $err];
        }
        if ($status >= 200 && $status < 300) {
            return ['ok' => true, 'body' => $body];
        }
        return ['ok' => false, 'error' => 'HTTP ' . $status];
    }
}
