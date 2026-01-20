<?php

namespace App\Services;

class AIService
{
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function respond(string $prompt): string
    {
        if (!$this->config['enabled'] || empty($this->config['api_key'])) {
            throw new \RuntimeException('AI provider not configured');
        }
        $payload = [
            'model' => 'gpt-4o-mini',
            'messages' => [
                ['role' => 'system', 'content' => 'You are a helpful ServiceDesk assistant.'],
                ['role' => 'user', 'content' => $prompt],
            ],
        ];
        $ch = curl_init($this->config['endpoint']);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->config['api_key'],
            ],
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
        ]);
        $response = curl_exec($ch);
        $err = curl_error($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($err) {
            throw new \RuntimeException($err);
        }
        if ($status < 200 || $status >= 300) {
            throw new \RuntimeException('AI request failed: ' . $status);
        }
        $data = json_decode($response, true) ?: [];
        return $data['choices'][0]['message']['content'] ?? '';
    }
}
