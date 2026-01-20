<?php include __DIR__ . '/../partials/header.php'; ?>
<div class="page-header">
    <h2>News & Polls</h2>
</div>
<form method="post" action="/admin/news/save" class="form">
    <input type="hidden" name="_token" value="<?= \App\Core\CSRF::token() ?>">
    <label>Title RU
        <input type="text" name="title_ru" required>
    </label>
    <label>Body RU
        <textarea name="body_ru" rows="3" required></textarea>
    </label>
    <label>Title EN
        <input type="text" name="title_en" required>
    </label>
    <label>Body EN
        <textarea name="body_en" rows="3" required></textarea>
    </label>
    <label>Publish at
        <input type="datetime-local" name="publish_at" required>
    </label>
    <label>
        <input type="checkbox" name="is_poll"> Is poll
    </label>
    <label>Poll options (comma separated)
        <input type="text" name="poll_options">
    </label>
    <button class="btn primary" type="submit">Publish</button>
</form>
<section class="grid">
    <?php foreach ($news as $item): ?>
        <div class="card">
            <strong><?= htmlspecialchars($item['title_ru']) ?></strong>
            <p class="muted"><?= htmlspecialchars($item['publish_at']) ?></p>
        </div>
    <?php endforeach; ?>
</section>
<?php include __DIR__ . '/../partials/footer.php'; ?>
