<?php include __DIR__ . '/partials/header.php'; ?>
<div class="hero">
    <div>
        <h1><?= $this->localization->get('dashboard') ?> · <?= htmlspecialchars($user['first_name']) ?></h1>
        <p class="muted"><?= htmlspecialchars($user['department'] ?: 'ServiceDesk') ?></p>
        <div class="actions">
            <?php if ($user['role'] === 'user'): ?>
                <a href="/tickets/create" class="btn primary"><?= $this->localization->get('new_ticket') ?></a>
            <?php else: ?>
                <a href="/tickets" class="btn primary"><?= $this->localization->get('tickets') ?></a>
            <?php endif; ?>
            <a href="/knowledge" class="btn secondary"><?= $this->localization->get('knowledge_base') ?></a>
        </div>
    </div>
    <div class="status-cards">
        <div class="status-card">
            <div class="badge">RBAC</div>
            <div><?= strtoupper($user['role']) ?></div>
            <span class="muted">Role-based access control</span>
        </div>
        <div class="status-card">
            <div class="badge danger"><?= $this->localization->get('overdue') ?></div>
            <div><?= count($overdue) ?></div>
            <span class="muted">24h SLA</span>
        </div>
    </div>
</div>

<section class="grid">
    <?php foreach ($news as $item): ?>
        <article class="card">
            <h3><?= htmlspecialchars($item[$locale === 'ru' ? 'title_ru' : 'title_en']) ?></h3>
            <p><?= nl2br(htmlspecialchars($item[$locale === 'ru' ? 'body_ru' : 'body_en'])) ?></p>
            <?php if ($item['is_poll']): ?>
                <?php $options = json_decode($item['poll_options'], true) ?: []; ?>
                <form method="post" action="/polls/vote" class="poll">
                    <input type="hidden" name="_token" value="<?= \App\Core\CSRF::token() ?>">
                    <input type="hidden" name="id" value="<?= $item['id'] ?>">
                    <?php foreach ($options as $option): ?>
                        <label class="radio">
                            <input type="radio" name="choice" value="<?= htmlspecialchars($option) ?>" required>
                            <span><?= htmlspecialchars($option) ?></span>
                        </label>
                    <?php endforeach; ?>
                    <button class="btn secondary" type="submit">Vote</button>
                </form>
            <?php endif; ?>
        </article>
    <?php endforeach; ?>
</section>
<?php include __DIR__ . '/partials/footer.php'; ?>
