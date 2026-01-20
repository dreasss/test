<?php
use App\Core\Auth;
use App\Core\CSRF;

$localization = $this->localization ?? ($localization ?? null);
$locale = $localization ? $localization->locale() : 'ru';
$branding = $branding ?? [
    'name_ru' => 'ServiceDesk',
    'name_en' => 'ServiceDesk',
    'slogan_ru' => '',
    'slogan_en' => '',
    'logo_url' => '',
    'color_primary' => '#2563eb',
    'color_secondary' => '#14b8a6',
];
$translate = $localization ? fn(string $key) => $localization->get($key) : fn(string $key) => $key;
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($locale) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ServiceDesk</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <script defer src="/assets/js/app.js"></script>
</head>
<body class="theme" data-primary="<?= htmlspecialchars($branding['color_primary'] ?? '#2563eb') ?>" data-secondary="<?= htmlspecialchars($branding['color_secondary'] ?? '#14b8a6') ?>">
<header class="topbar">
    <div class="brand">
        <?php if (!empty($branding['logo_url'])): ?>
            <img src="<?= htmlspecialchars($branding['logo_url']) ?>" alt="logo" class="brand-logo">
        <?php endif; ?>
        <div>
            <div class="brand-name">
                <?= htmlspecialchars($branding[$locale === 'ru' ? 'name_ru' : 'name_en'] ?? 'ServiceDesk') ?>
            </div>
            <div class="brand-slogan" data-slogan-ru="<?= htmlspecialchars($branding['slogan_ru'] ?? '') ?>" data-slogan-en="<?= htmlspecialchars($branding['slogan_en'] ?? '') ?>"></div>
        </div>
    </div>
    <nav class="nav">
        <a href="/" class="nav-link"><?= $translate('dashboard') ?></a>
        <a href="/tickets" class="nav-link"><?= $translate('tickets') ?></a>
        <a href="/knowledge" class="nav-link"><?= $translate('knowledge_base') ?></a>
        <a href="/news" class="nav-link"><?= $translate('news') ?></a>
        <?php if (Auth::check() && Auth::user()['role'] === 'admin'): ?>
            <a href="/admin" class="nav-link"><?= $translate('admin') ?></a>
        <?php endif; ?>
    </nav>
    <div class="top-actions">
        <form method="post" action="/locale" class="inline-form">
            <input type="hidden" name="_token" value="<?= CSRF::token() ?>">
            <select name="locale" onchange="this.form.submit()" class="select">
                <option value="ru" <?= $locale === 'ru' ? 'selected' : '' ?>>RU</option>
                <option value="en" <?= $locale === 'en' ? 'selected' : '' ?>>EN</option>
            </select>
        </form>
        <button class="theme-toggle" type="button" data-theme-toggle>
            <span class="icon">🌓</span>
        </button>
        <?php if (Auth::check()): ?>
            <form method="post" action="/logout" class="inline-form">
                <input type="hidden" name="_token" value="<?= CSRF::token() ?>">
                <button class="btn ghost" type="submit"><?= $translate('logout') ?></button>
            </form>
        <?php endif; ?>
    </div>
</header>
<main class="container">
