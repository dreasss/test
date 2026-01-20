<?php include __DIR__ . '/../partials/header.php'; ?>
<div class="page-header">
    <h2><?= $this->localization->get('knowledge_base') ?></h2>
    <?php if (\App\Core\Auth::user()['role'] !== 'user'): ?>
        <a href="/knowledge/editor" class="btn secondary">New article</a>
    <?php endif; ?>
</div>
<div class="grid">
    <?php foreach ($articles as $article): ?>
        <a href="/knowledge/view?id=<?= $article['id'] ?>" class="card">
            <h3><?= htmlspecialchars($article[$locale === 'ru' ? 'title_ru' : 'title_en']) ?></h3>
            <p class="muted">Tags: <?= htmlspecialchars($article['tags']) ?></p>
        </a>
    <?php endforeach; ?>
</div>
<?php include __DIR__ . '/../partials/footer.php'; ?>
