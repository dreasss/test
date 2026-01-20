<?php include __DIR__ . '/../partials/header.php'; ?>
<div class="page-header">
    <h2><?= $this->localization->get('sync') ?></h2>
</div>
<div class="card">
    <p>Sync queue can be processed manually or via cron.</p>
    <form method="post" action="/admin/sync/run">
        <input type="hidden" name="_token" value="<?= \App\Core\CSRF::token() ?>">
        <button class="btn primary" type="submit">Process queue</button>
    </form>
</div>
<?php include __DIR__ . '/../partials/footer.php'; ?>
