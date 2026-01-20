<?php include __DIR__ . '/../partials/header.php'; ?>
<div class="page-header">
    <h2>Knowledge Drafts</h2>
    <form method="post" action="/admin/knowledge/generate" class="inline-form">
        <input type="hidden" name="_token" value="<?= \App\Core\CSRF::token() ?>">
        <button class="btn secondary" type="submit">Generate drafts</button>
    </form>
</div>
<div class="grid">
    <?php foreach ($drafts as $draft): ?>
        <a class="card" href="/knowledge/editor?id=<?= $draft['id'] ?>">
            <h3><?= htmlspecialchars($draft['title_ru']) ?></h3>
            <p class="muted">Draft</p>
        </a>
    <?php endforeach; ?>
</div>
<?php include __DIR__ . '/../partials/footer.php'; ?>
