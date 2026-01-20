<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\CSRF;
use App\Models\User;
use App\Services\SSOAuth;
use PDO;

class AuthController
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

    public function showLogin(): void
    {
        include __DIR__ . '/../Views/auth/login.php';
    }

    public function login(): void
    {
        if (!CSRF::verify($_POST['_token'] ?? '')) {
            http_response_code(419);
            echo 'Invalid CSRF token';
            return;
        }
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $user = User::findByEmail($this->db, $email);
        if (!$user || !password_verify($password, $user['password_hash'])) {
            $_SESSION['error'] = 'Invalid credentials';
            header('Location: /login');
            return;
        }
        Auth::login($user);
        header('Location: /');
    }

    public function showRegister(): void
    {
        include __DIR__ . '/../Views/auth/register.php';
    }

    public function register(): void
    {
        if (!CSRF::verify($_POST['_token'] ?? '')) {
            http_response_code(419);
            echo 'Invalid CSRF token';
            return;
        }
        $data = [
            'role' => 'user',
            'first_name' => trim($_POST['first_name'] ?? ''),
            'last_name' => trim($_POST['last_name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'department' => trim($_POST['department'] ?? ''),
            'building' => trim($_POST['building'] ?? ''),
            'room' => trim($_POST['room'] ?? ''),
            'locale' => $_SESSION['locale'] ?? $this->config['app']['locale'],
            'avatar_url' => '',
            'sso_subject' => null,
            'password_hash' => password_hash($_POST['password'] ?? '', $this->config['security']['password_algo']),
        ];
        $id = User::create($this->db, $data);
        $user = User::findById($this->db, $id);
        Auth::login($user);
        header('Location: /');
    }

    public function logout(): void
    {
        if (!CSRF::verify($_POST['_token'] ?? '')) {
            http_response_code(419);
            echo 'Invalid CSRF token';
            return;
        }
        Auth::logout();
        header('Location: /login');
    }

    public function oidcLogin(): void
    {
        if (!$this->config['oidc']['enabled']) {
            http_response_code(403);
            echo 'SSO is disabled';
            return;
        }
        $sso = new SSOAuth($this->config['oidc']);
        header('Location: ' . $sso->authUrl());
    }

    public function oidcCallback(): void
    {
        if (!$this->config['oidc']['enabled']) {
            http_response_code(403);
            echo 'SSO is disabled';
            return;
        }
        $code = $_GET['code'] ?? '';
        $state = $_GET['state'] ?? '';
        $sso = new SSOAuth($this->config['oidc']);
        $result = $sso->handleCallback($code, $state);
        $claims = $result['claims'];
        $email = $claims['email'] ?? '';
        $subject = $claims['sub'] ?? '';
        $user = User::findByEmail($this->db, $email);
        if (!$user) {
            $id = User::create($this->db, [
                'role' => 'user',
                'first_name' => $claims['given_name'] ?? 'User',
                'last_name' => $claims['family_name'] ?? '',
                'email' => $email,
                'phone' => '',
                'department' => $claims['department'] ?? '',
                'building' => $claims['building'] ?? '',
                'room' => $claims['room'] ?? '',
                'locale' => $_SESSION['locale'] ?? $this->config['app']['locale'],
                'avatar_url' => $claims['picture'] ?? '',
                'sso_subject' => $subject,
                'password_hash' => password_hash(bin2hex(random_bytes(16)), $this->config['security']['password_algo']),
            ]);
            $user = User::findById($this->db, $id);
        }
        Auth::login($user);
        header('Location: /');
    }

    public function setLocale(): void
    {
        $locale = $_POST['locale'] ?? $this->config['app']['locale'];
        if (!in_array($locale, $this->config['app']['locales'], true)) {
            $locale = $this->config['app']['locale'];
        }
        $_SESSION['locale'] = $locale;
        $redirect = $_SERVER['HTTP_REFERER'] ?? '/';
        header('Location: ' . $redirect);
    }
}
