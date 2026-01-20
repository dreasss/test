<?php include __DIR__ . '/../partials/header.php'; ?>
<div class="page-header">
    <h2><?= $this->localization->get('news') ?></h2>
</div>
<section class="grid">
    <?php foreach ($news as $item): ?>
        <article class="card">
            <h3><?= htmlspecialchars($item[$locale === 'ru' ? 'title_ru' : 'title_en']) ?></h3>
            <p><?= nl2br(htmlspecialchars($item[$locale === 'ru' ? 'body_ru' : 'body_en'])) ?></p>
            <?php if ($item['is_poll']): ?>
                <?php $options = json_decode($item['poll_options'], true) ?: []; ?>
                <?php $votes = json_decode($item['poll_votes'], true) ?: []; ?>
                <form method="post" action="/polls/vote" class="poll">
                    <input type="hidden" name="_token" value="<?= \App\Core\CSRF::token() ?>">
                    <input type="hidden" name="id" value="<?= $item['id'] ?>">
                    <?php foreach ($options as $option): ?>
                        <label class="radio">
                            <input type="radio" name="choice" value="<?= htmlspecialchars($option) ?>" required>
                            <span><?= htmlspecialchars($option) ?> (<?= $votes[$option] ?? 0 ?>)</span>
                        </label>
                    <?php endforeach; ?>
                    <button class="btn secondary" type="submit">Vote</button>
                </form>
            <?php endif; ?>
        </article>
    <?php endforeach; ?>
</section>
<?php include __DIR__ . '/../partials/footer.php'; ?>
