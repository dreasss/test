<?php include __DIR__ . '/../partials/header.php'; ?>
<div class="card">
    <h2><?= htmlspecialchars($article[$locale === 'ru' ? 'title_ru' : 'title_en']) ?></h2>
    <div class="content">
        <?= nl2br(htmlspecialchars($article[$locale === 'ru' ? 'body_ru' : 'body_en'])) ?>
    </div>
    <form method="post" action="/knowledge/vote" class="inline-form">
        <input type="hidden" name="_token" value="<?= \App\Core\CSRF::token() ?>">
        <input type="hidden" name="id" value="<?= $article['id'] ?>">
        <button class="btn ghost" name="type" value="up" type="submit"><?= $this->localization->get('helpful') ?></button>
        <button class="btn ghost" name="type" value="down" type="submit"><?= $this->localization->get('not_helpful') ?></button>
    </form>
</div>
<?php include __DIR__ . '/../partials/footer.php'; ?>
