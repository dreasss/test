<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Models\Branding;
use App\Models\News;
use App\Models\Ticket;
use PDO;

class HomeController
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
        if (!Auth::check()) {
            header('Location: /login');
            return;
        }
        $user = Auth::user();
        $branding = Branding::current($this->db);
        $news = News::list($this->db);
        $overdue = Ticket::overdue($this->db);
        include __DIR__ . '/../Views/home.php';
    }
}
