<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Models\Ticket;
use App\Models\Knowledge;
use App\Models\News;
use App\Models\Message;
use App\Services\OneCService;
use PDO;

class ApiController
{
    private PDO $db;
    private array $config;
    private $localization;

    public function __construct(PDO $db, array $config, $localization)
    {
        $this->db = $db;
        $this->config = $config;
        $this->localization = $localization;
    }

    public function suggestions(): void
    {
        Auth::requireRole(['admin', 'agent', 'user']);
        $q = trim($_GET['q'] ?? '');
        $result = ['phrases' => []];
        if ($q !== '') {
            $stmt = $this->db->prepare('SELECT title_ru AS phrase FROM knowledge_articles WHERE title_ru LIKE :q OR title_en LIKE :q LIMIT 5');
            $stmt->execute(['q' => '%' . $q . '%']);
            $result['phrases'] = array_column($stmt->fetchAll(), 'phrase');
            $stmt = $this->db->prepare('SELECT title FROM tickets WHERE title LIKE :q LIMIT 5');
            $stmt->execute(['q' => '%' . $q . '%']);
            $result['phrases'] = array_merge($result['phrases'], array_column($stmt->fetchAll(), 'title'));
        }
        header('Content-Type: application/json');
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    public function ssoCallback(): void
    {
        Auth::requireRole(['admin']);
        header('Content-Type: application/json');
        echo json_encode(['status' => 'ok']);
    }

    public function tickets(): void
    {
        Auth::requireRole(['admin', 'agent', 'user']);
        $tickets = Ticket::listForUser($this->db, Auth::user(), $_GET['filter'] ?? null);
        header('Content-Type: application/json');
        echo json_encode($tickets, JSON_UNESCAPED_UNICODE);
    }

    public function createTicket(): void
    {
        Auth::requireRole(['admin', 'user']);
        $data = json_decode(file_get_contents('php://input'), true) ?: [];
        $ticketId = Ticket::create($this->db, [
            'author_id' => Auth::user()['id'],
            'assignee_id' => null,
            'title' => $data['title'] ?? '',
            'description' => $data['description'] ?? '',
            'priority' => $data['priority'] ?? 'low',
            'desired_at' => $data['desired_at'] ?? null,
            'status' => 'new',
            'building' => $data['building'] ?? '',
            'room' => $data['room'] ?? '',
            'attachments' => json_encode($data['attachments'] ?? []),
        ]);
        $sync = new OneCService($this->db, $this->config['one_c']);
        $sync->enqueue('ticket', $ticketId, 'create', $data);
        header('Content-Type: application/json');
        echo json_encode(['id' => $ticketId]);
    }

    public function updateTicket(): void
    {
        Auth::requireRole(['admin', 'agent']);
        $data = json_decode(file_get_contents('php://input'), true) ?: [];
        Ticket::update($this->db, (int) $data['id'], $data);
        header('Content-Type: application/json');
        echo json_encode(['status' => 'ok']);
    }

    public function messages(): void
    {
        Auth::requireRole(['admin', 'agent', 'user']);
        $ticketId = (int) ($_GET['ticket_id'] ?? 0);
        $messages = Message::list($this->db, $ticketId);
        header('Content-Type: application/json');
        echo json_encode($messages, JSON_UNESCAPED_UNICODE);
    }

    public function createMessage(): void
    {
        Auth::requireRole(['admin', 'agent', 'user']);
        $data = json_decode(file_get_contents('php://input'), true) ?: [];
        $id = Message::create($this->db, [
            'ticket_id' => $data['ticket_id'],
            'author_id' => Auth::user()['id'],
            'body' => $data['body'] ?? '',
            'attachments' => json_encode($data['attachments'] ?? []),
            'is_system' => 0,
        ]);
        header('Content-Type: application/json');
        echo json_encode(['id' => $id]);
    }

    public function knowledge(): void
    {
        Auth::requireRole(['admin', 'agent', 'user']);
        $articles = Knowledge::list($this->db);
        header('Content-Type: application/json');
        echo json_encode($articles, JSON_UNESCAPED_UNICODE);
    }

    public function createKnowledge(): void
    {
        Auth::requireRole(['admin', 'agent']);
        $data = json_decode(file_get_contents('php://input'), true) ?: [];
        $id = Knowledge::create($this->db, [
            'slug' => $data['slug'] ?? uniqid('article_'),
            'title_ru' => $data['title_ru'] ?? '',
            'body_ru' => $data['body_ru'] ?? '',
            'title_en' => $data['title_en'] ?? '',
            'body_en' => $data['body_en'] ?? '',
            'tags' => $data['tags'] ?? '',
            'created_by' => Auth::user()['id'],
            'updated_by' => Auth::user()['id'],
            'status' => $data['status'] ?? 'draft',
        ]);
        header('Content-Type: application/json');
        echo json_encode(['id' => $id]);
    }

    public function updateKnowledge(): void
    {
        Auth::requireRole(['admin', 'agent']);
        $data = json_decode(file_get_contents('php://input'), true) ?: [];
        Knowledge::update($this->db, (int) $data['id'], $data);
        header('Content-Type: application/json');
        echo json_encode(['status' => 'ok']);
    }

    public function news(): void
    {
        Auth::requireRole(['admin', 'agent', 'user']);
        $news = News::list($this->db);
        header('Content-Type: application/json');
        echo json_encode($news, JSON_UNESCAPED_UNICODE);
    }

    public function pollVote(): void
    {
        Auth::requireRole(['admin', 'agent', 'user']);
        $data = json_decode(file_get_contents('php://input'), true) ?: [];
        $stmt = $this->db->prepare('SELECT poll_votes FROM news_items WHERE id = :id');
        $stmt->execute(['id' => (int) $data['id']]);
        $item = $stmt->fetch();
        $votes = json_decode($item['poll_votes'] ?? '{}', true) ?: [];
        $choice = $data['choice'] ?? '';
        $votes[$choice] = ($votes[$choice] ?? 0) + 1;
        $stmt = $this->db->prepare('UPDATE news_items SET poll_votes = :votes WHERE id = :id');
        $stmt->execute(['votes' => json_encode($votes, JSON_UNESCAPED_UNICODE), 'id' => (int) $data['id']]);
        header('Content-Type: application/json');
        echo json_encode(['status' => 'ok']);
    }

    public function exportOneC(): void
    {
        Auth::requireRole(['admin']);
        $data = json_decode(file_get_contents('php://input'), true) ?: [];
        $sync = new OneCService($this->db, $this->config['one_c']);
        $sync->enqueue('manual_export', 0, 'export', $data);
        header('Content-Type: application/json');
        echo json_encode(['status' => 'queued']);
    }
}
