<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\CSRF;
use App\Models\Knowledge;
use PDO;

class KnowledgeController
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
        $articles = Knowledge::list($this->db);
        include __DIR__ . '/../Views/knowledge/index.php';
    }

    public function show(): void
    {
        Auth::requireRole(['admin', 'agent', 'user']);
        $id = (int) ($_GET['id'] ?? 0);
        $article = Knowledge::find($this->db, $id);
        include __DIR__ . '/../Views/knowledge/show.php';
    }

    public function editor(): void
    {
        Auth::requireRole(['admin', 'agent']);
        $id = (int) ($_GET['id'] ?? 0);
        $article = $id ? Knowledge::find($this->db, $id) : null;
        include __DIR__ . '/../Views/knowledge/editor.php';
    }

    public function save(): void
    {
        Auth::requireRole(['admin', 'agent']);
        if (!CSRF::verify($_POST['_token'] ?? '')) {
            http_response_code(419);
            echo 'Invalid CSRF token';
            return;
        }
        $data = [
            'slug' => $_POST['slug'] ?? uniqid('article_'),
            'title_ru' => $_POST['title_ru'] ?? '',
            'body_ru' => $_POST['body_ru'] ?? '',
            'title_en' => $_POST['title_en'] ?? '',
            'body_en' => $_POST['body_en'] ?? '',
            'tags' => $_POST['tags'] ?? '',
            'created_by' => Auth::user()['id'],
            'updated_by' => Auth::user()['id'],
            'status' => $_POST['status'] ?? 'draft',
        ];
        $id = (int) ($_POST['id'] ?? 0);
        if ($id) {
            Knowledge::update($this->db, $id, $data);
        } else {
            Knowledge::create($this->db, $data);
        }
        header('Location: /knowledge');
    }

    public function vote(): void
    {
        Auth::requireRole(['admin', 'agent', 'user']);
        if (!CSRF::verify($_POST['_token'] ?? '')) {
            http_response_code(419);
            echo 'Invalid CSRF token';
            return;
        }
        $id = (int) ($_POST['id'] ?? 0);
        $type = $_POST['type'] ?? 'up';
        $field = $type === 'down' ? 'usefulness_down' : 'usefulness_up';
        $stmt = $this->db->prepare("UPDATE knowledge_articles SET $field = $field + 1 WHERE id = :id");
        $stmt->execute(['id' => $id]);
        header('Location: /knowledge/view?id=' . $id);
    }
}
