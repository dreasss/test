<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\CSRF;
use App\Models\Branding;
use App\Models\Knowledge;
use App\Models\News;
use App\Models\User;
use App\Services\OneCService;
use PDO;

class AdminController
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

    public function dashboard(): void
    {
        Auth::requireRole(['admin']);
        include __DIR__ . '/../Views/admin/dashboard.php';
    }

    public function users(): void
    {
        Auth::requireRole(['admin', 'agent']);
        $users = User::all($this->db);
        include __DIR__ . '/../Views/admin/users.php';
    }

    public function saveUser(): void
    {
        Auth::requireRole(['admin', 'agent']);
        if (!CSRF::verify($_POST['_token'] ?? '')) {
            http_response_code(419);
            echo 'Invalid CSRF token';
            return;
        }
        $id = (int) ($_POST['id'] ?? 0);
        $data = [
            'role' => $_POST['role'] ?? 'user',
            'first_name' => $_POST['first_name'] ?? '',
            'last_name' => $_POST['last_name'] ?? '',
            'email' => $_POST['email'] ?? '',
            'department' => $_POST['department'] ?? '',
            'building' => $_POST['building'] ?? '',
            'room' => $_POST['room'] ?? '',
            'locale' => $_POST['locale'] ?? $this->config['app']['locale'],
        ];
        if ($id) {
            $existing = User::findById($this->db, $id);
            if ($existing && $existing['role'] === 'admin' && Auth::user()['role'] === 'agent') {
                http_response_code(403);
                echo 'Cannot edit admin';
                return;
            }
            User::update($this->db, $id, $data);
        } else {
            $data['phone'] = '';
            $data['avatar_url'] = '';
            $data['sso_subject'] = null;
            $data['password_hash'] = password_hash($_POST['password'] ?? bin2hex(random_bytes(8)), $this->config['security']['password_algo']);
            User::create($this->db, $data);
        }
        header('Location: /admin/users');
    }

    public function branding(): void
    {
        Auth::requireRole(['admin']);
        $branding = Branding::current($this->db);
        include __DIR__ . '/../Views/admin/branding.php';
    }

    public function saveBranding(): void
    {
        Auth::requireRole(['admin']);
        if (!CSRF::verify($_POST['_token'] ?? '')) {
            http_response_code(419);
            echo 'Invalid CSRF token';
            return;
        }
        $logoUrl = $_POST['logo_url'] ?? '';
        if (!empty($_FILES['logo']['name'])) {
            $ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
            $fileName = uniqid('logo_', true) . '.' . $ext;
            $target = $this->config['uploads']['path'] . '/' . $fileName;
            move_uploaded_file($_FILES['logo']['tmp_name'], $target);
            $logoUrl = $this->config['uploads']['url'] . '/' . $fileName;
        }
        Branding::update($this->db, [
            'name_ru' => $_POST['name_ru'] ?? '',
            'name_en' => $_POST['name_en'] ?? '',
            'slogan_ru' => $_POST['slogan_ru'] ?? '',
            'slogan_en' => $_POST['slogan_en'] ?? '',
            'logo_url' => $logoUrl,
            'color_primary' => $_POST['color_primary'] ?? $this->config['branding']['default_primary'],
            'color_secondary' => $_POST['color_secondary'] ?? $this->config['branding']['default_secondary'],
        ]);
        header('Location: /admin/branding');
    }

    public function sync(): void
    {
        Auth::requireRole(['admin']);
        include __DIR__ . '/../Views/admin/sync.php';
    }

    public function runSync(): void
    {
        Auth::requireRole(['admin']);
        if (!CSRF::verify($_POST['_token'] ?? '')) {
            http_response_code(419);
            echo 'Invalid CSRF token';
            return;
        }
        $sync = new OneCService($this->db, $this->config['one_c']);
        $sync->processQueue();
        header('Location: /admin/sync');
    }

    public function knowledgeDrafts(): void
    {
        Auth::requireRole(['admin', 'agent']);
        $drafts = Knowledge::list($this->db, 'draft');
        include __DIR__ . '/../Views/admin/knowledge_drafts.php';
    }

    public function generateDrafts(): void
    {
        Auth::requireRole(['admin', 'agent']);
        if (!CSRF::verify($_POST['_token'] ?? '')) {
            http_response_code(419);
            echo 'Invalid CSRF token';
            return;
        }
        $stmt = $this->db->query('SELECT title, description FROM tickets ORDER BY created_at DESC LIMIT 50');
        $tickets = $stmt->fetchAll();
        foreach ($tickets as $ticket) {
            $slug = preg_replace('/[^a-z0-9]+/i', '-', strtolower($ticket['title']));
            $slug = trim($slug, '-');
            $exists = $this->db->prepare('SELECT id FROM knowledge_articles WHERE slug = :slug');
            $exists->execute(['slug' => $slug]);
            if ($exists->fetch()) {
                continue;
            }
            Knowledge::create($this->db, [
                'slug' => $slug,
                'title_ru' => $ticket['title'],
                'body_ru' => $ticket['description'],
                'title_en' => $ticket['title'],
                'body_en' => $ticket['description'],
                'tags' => 'auto',
                'created_by' => Auth::user()['id'],
                'updated_by' => Auth::user()['id'],
                'status' => 'draft',
            ]);
        }
        header('Location: /admin/knowledge/drafts');
    }

    public function news(): void
    {
        Auth::requireRole(['admin']);
        $news = News::list($this->db);
        include __DIR__ . '/../Views/admin/news.php';
    }

    public function saveNews(): void
    {
        Auth::requireRole(['admin']);
        if (!CSRF::verify($_POST['_token'] ?? '')) {
            http_response_code(419);
            echo 'Invalid CSRF token';
            return;
        }
        $options = array_filter(array_map('trim', explode(',', $_POST['poll_options'] ?? '')));
        News::create($this->db, [
            'title_ru' => $_POST['title_ru'] ?? '',
            'body_ru' => $_POST['body_ru'] ?? '',
            'title_en' => $_POST['title_en'] ?? '',
            'body_en' => $_POST['body_en'] ?? '',
            'cover_url' => '',
            'publish_at' => $_POST['publish_at'] ?? date('Y-m-d H:i:s'),
            'is_poll' => isset($_POST['is_poll']) ? 1 : 0,
            'poll_options' => json_encode($options, JSON_UNESCAPED_UNICODE),
            'poll_votes' => json_encode([], JSON_UNESCAPED_UNICODE),
        ]);
        header('Location: /admin/news');
    }
}
