<?php

use App\Core\Auth;
use App\Core\CSRF;
use App\Core\DB;
use App\Core\Localization;
use App\Core\Router;
use App\Controllers\AuthController;
use App\Controllers\HomeController;
use App\Controllers\TicketController;
use App\Controllers\KnowledgeController;
use App\Controllers\NewsController;
use App\Controllers\AdminController;
use App\Controllers\ApiController;

require __DIR__ . '/../vendor/autoload.php';

$config = require __DIR__ . '/../app/Config/config.php';

session_name($config['security']['session_name']);
session_start();

$locale = $_SESSION['locale'] ?? $config['app']['locale'];
$strings = require __DIR__ . '/../app/Resources/strings.php';
$localization = new Localization($locale, $strings);

$db = DB::conn($config['db']);

$router = new Router();

$homeController = new HomeController($db, $config, $localization);
$authController = new AuthController($db, $config, $localization);
$ticketController = new TicketController($db, $config, $localization);
$knowledgeController = new KnowledgeController($db, $config, $localization);
$newsController = new NewsController($db, $config, $localization);
$adminController = new AdminController($db, $config, $localization);
$apiController = new ApiController($db, $config, $localization);

$router->get('/', fn() => $homeController->index());
$router->get('/login', fn() => $authController->showLogin());
$router->post('/login', fn() => $authController->login());
$router->post('/logout', fn() => $authController->logout());
$router->get('/register', fn() => $authController->showRegister());
$router->post('/register', fn() => $authController->register());
$router->get('/oidc/login', fn() => $authController->oidcLogin());
$router->get('/oidc/callback', fn() => $authController->oidcCallback());
$router->post('/locale', fn() => $authController->setLocale());

$router->get('/tickets', fn() => $ticketController->index());
$router->get('/tickets/create', fn() => $ticketController->create());
$router->post('/tickets', fn() => $ticketController->store());
$router->get('/tickets/view', fn() => $ticketController->show());
$router->post('/tickets/update', fn() => $ticketController->update());
$router->post('/tickets/message', fn() => $ticketController->message());
$router->post('/tickets/assign', fn() => $ticketController->assign());
$router->post('/tickets/status', fn() => $ticketController->status());

$router->get('/knowledge', fn() => $knowledgeController->index());
$router->get('/knowledge/view', fn() => $knowledgeController->show());
$router->get('/knowledge/editor', fn() => $knowledgeController->editor());
$router->post('/knowledge/save', fn() => $knowledgeController->save());
$router->post('/knowledge/vote', fn() => $knowledgeController->vote());

$router->get('/news', fn() => $newsController->index());
$router->post('/polls/vote', fn() => $newsController->vote());

$router->get('/admin', fn() => $adminController->dashboard());
$router->get('/admin/users', fn() => $adminController->users());
$router->post('/admin/users/save', fn() => $adminController->saveUser());
$router->get('/admin/branding', fn() => $adminController->branding());
$router->post('/admin/branding', fn() => $adminController->saveBranding());
$router->get('/admin/sync', fn() => $adminController->sync());
$router->post('/admin/sync/run', fn() => $adminController->runSync());
$router->get('/admin/knowledge/drafts', fn() => $adminController->knowledgeDrafts());
$router->post('/admin/knowledge/generate', fn() => $adminController->generateDrafts());
$router->get('/admin/news', fn() => $adminController->news());
$router->post('/admin/news/save', fn() => $adminController->saveNews());

$router->get('/api/suggestions', fn() => $apiController->suggestions());
$router->post('/api/auth/sso/callback', fn() => $apiController->ssoCallback());
$router->get('/api/tickets', fn() => $apiController->tickets());
$router->post('/api/tickets', fn() => $apiController->createTicket());
$router->post('/api/tickets/update', fn() => $apiController->updateTicket());
$router->get('/api/tickets/messages', fn() => $apiController->messages());
$router->post('/api/tickets/messages', fn() => $apiController->createMessage());
$router->get('/api/knowledge', fn() => $apiController->knowledge());
$router->post('/api/knowledge', fn() => $apiController->createKnowledge());
$router->post('/api/knowledge/update', fn() => $apiController->updateKnowledge());
$router->get('/api/news', fn() => $apiController->news());
$router->post('/api/polls/vote', fn() => $apiController->pollVote());
$router->post('/api/sync/1c/export', fn() => $apiController->exportOneC());

$router->dispatch($_SERVER['REQUEST_METHOD'], parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
