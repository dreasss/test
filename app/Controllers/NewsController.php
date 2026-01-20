<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\CSRF;
use App\Models\News;
use PDO;

class NewsController
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
        $news = News::list($this->db);
        include __DIR__ . '/../Views/news/index.php';
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
        $choice = $_POST['choice'] ?? '';
        $stmt = $this->db->prepare('SELECT poll_votes FROM news_items WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $item = $stmt->fetch();
        $votes = json_decode($item['poll_votes'] ?? '{}', true) ?: [];
        $votes[$choice] = ($votes[$choice] ?? 0) + 1;
        $stmt = $this->db->prepare('UPDATE news_items SET poll_votes = :votes WHERE id = :id');
        $stmt->execute(['votes' => json_encode($votes, JSON_UNESCAPED_UNICODE), 'id' => $id]);
        header('Location: /news');
    }
}
