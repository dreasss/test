<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\CSRF;
use App\Models\Ticket;
use App\Models\Message;
use App\Models\User;
use App\Services\OneCService;
use PDO;

class TicketController
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

    public function index(): void
    {
        Auth::requireRole(['admin', 'agent', 'user']);
        $filter = $_GET['filter'] ?? null;
        $query = $_GET['q'] ?? '';
        $user = Auth::user();
        if ($query) {
            $tickets = Ticket::search($this->db, $user, $query);
        } elseif ($filter === 'overdue') {
            $tickets = Ticket::overdue($this->db);
            if ($user['role'] === 'user') {
                $tickets = array_values(array_filter($tickets, fn($t) => $t['author_id'] === $user['id']));
            }
            if ($user['role'] === 'agent') {
                $tickets = array_values(array_filter($tickets, fn($t) => $t['assignee_id'] === $user['id'] || $t['assignee_id'] === null));
            }
        } else {
            $tickets = Ticket::listForUser($this->db, $user, $filter);
        }
        include __DIR__ . '/../Views/tickets/index.php';
    }

    public function create(): void
    {
        Auth::requireRole(['user', 'admin']);
        $user = Auth::user();
        include __DIR__ . '/../Views/tickets/create.php';
    }

    public function store(): void
    {
        Auth::requireRole(['user', 'admin']);
        if (!CSRF::verify($_POST['_token'] ?? '')) {
            http_response_code(419);
            echo 'Invalid CSRF token';
            return;
        }
        $attachments = $this->handleUploads('attachments');
        $data = [
            'author_id' => Auth::user()['id'],
            'assignee_id' => null,
            'title' => trim($_POST['title'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'priority' => $_POST['priority'] ?? 'low',
            'desired_at' => $_POST['desired_at'] ?: null,
            'status' => 'new',
            'building' => $_POST['building'] ?? '',
            'room' => $_POST['room'] ?? '',
            'attachments' => json_encode($attachments),
        ];
        $ticketId = Ticket::create($this->db, $data);
        $sync = new OneCService($this->db, $this->config['one_c']);
        $sync->enqueue('ticket', $ticketId, 'create', $data);
        header('Location: /tickets/view?id=' . $ticketId);
    }

    public function show(): void
    {
        Auth::requireRole(['admin', 'agent', 'user']);
        $id = (int) ($_GET['id'] ?? 0);
        $ticket = Ticket::find($this->db, $id);
        if (!$ticket || !Auth::canViewTicket($ticket)) {
            http_response_code(403);
            include __DIR__ . '/../Views/errors/403.php';
            return;
        }
        $messages = Message::list($this->db, $id);
        include __DIR__ . '/../Views/tickets/show.php';
    }

    public function update(): void
    {
        Auth::requireRole(['admin', 'agent', 'user']);
        if (!CSRF::verify($_POST['_token'] ?? '')) {
            http_response_code(419);
            echo 'Invalid CSRF token';
            return;
        }
        $id = (int) ($_POST['id'] ?? 0);
        $ticket = Ticket::find($this->db, $id);
        if (!$ticket || !Auth::canEditTicket($ticket)) {
            http_response_code(403);
            include __DIR__ . '/../Views/errors/403.php';
            return;
        }
        Ticket::update($this->db, $id, [
            'title' => trim($_POST['title'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'priority' => $_POST['priority'] ?? 'low',
        ]);
        header('Location: /tickets/view?id=' . $id);
    }

    public function message(): void
    {
        Auth::requireRole(['admin', 'agent', 'user']);
        if (!CSRF::verify($_POST['_token'] ?? '')) {
            http_response_code(419);
            echo 'Invalid CSRF token';
            return;
        }
        $ticketId = (int) ($_POST['ticket_id'] ?? 0);
        $ticket = Ticket::find($this->db, $ticketId);
        if (!$ticket || !Auth::canChatInTicket($ticket)) {
            http_response_code(403);
            include __DIR__ . '/../Views/errors/403.php';
            return;
        }
        $attachments = $this->handleUploads('attachments');
        Message::create($this->db, [
            'ticket_id' => $ticketId,
            'author_id' => Auth::user()['id'],
            'body' => trim($_POST['body'] ?? ''),
            'attachments' => json_encode($attachments),
            'is_system' => 0,
        ]);
        if (Auth::user()['role'] === 'agent') {
            Ticket::update($this->db, $ticketId, ['last_agent_reply_at' => date('Y-m-d H:i:s')]);
        } else {
            Ticket::update($this->db, $ticketId, ['last_user_reply_at' => date('Y-m-d H:i:s')]);
        }
        header('Location: /tickets/view?id=' . $ticketId);
    }

    public function assign(): void
    {
        Auth::requireRole(['agent', 'admin']);
        if (!CSRF::verify($_POST['_token'] ?? '')) {
            http_response_code(419);
            echo 'Invalid CSRF token';
            return;
        }
        $id = (int) ($_POST['id'] ?? 0);
        Ticket::update($this->db, $id, [
            'assignee_id' => Auth::user()['id'],
            'status' => 'assigned',
        ]);
        header('Location: /tickets/view?id=' . $id);
    }

    public function status(): void
    {
        Auth::requireRole(['agent', 'admin', 'user']);
        if (!CSRF::verify($_POST['_token'] ?? '')) {
            http_response_code(419);
            echo 'Invalid CSRF token';
            return;
        }
        $id = (int) ($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? 'new';
        Ticket::update($this->db, $id, ['status' => $status]);
        header('Location: /tickets/view?id=' . $id);
    }

    private function handleUploads(string $key): array
    {
        $files = $_FILES[$key] ?? null;
        if (!$files || empty($files['name'][0])) {
            return [];
        }
        $uploaded = [];
        $allowed = $this->config['uploads']['allowed'];
        $maxSize = $this->config['uploads']['max_size'];
        foreach ($files['name'] as $index => $name) {
            if ($files['error'][$index] !== UPLOAD_ERR_OK) {
                continue;
            }
            if ($files['size'][$index] > $maxSize) {
                continue;
            }
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed, true)) {
                continue;
            }
            $fileName = uniqid('file_', true) . '.' . $ext;
            $target = $this->config['uploads']['path'] . '/' . $fileName;
            move_uploaded_file($files['tmp_name'][$index], $target);
            $uploaded[] = $this->config['uploads']['url'] . '/' . $fileName;
        }
        return $uploaded;
    }
}
