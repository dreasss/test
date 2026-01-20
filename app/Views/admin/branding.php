<?php include __DIR__ . '/../partials/header.php'; ?>
<div class="page-header">
    <h2><?= $this->localization->get('branding') ?></h2>
</div>
<form method="post" action="/admin/branding" enctype="multipart/form-data" class="form">
    <input type="hidden" name="_token" value="<?= \App\Core\CSRF::token() ?>">
    <label>Name RU
        <input type="text" name="name_ru" value="<?= htmlspecialchars($branding['name_ru']) ?>">
    </label>
    <label>Name EN
        <input type="text" name="name_en" value="<?= htmlspecialchars($branding['name_en']) ?>">
    </label>
    <label>Slogan RU
        <input type="text" name="slogan_ru" value="<?= htmlspecialchars($branding['slogan_ru']) ?>">
    </label>
    <label>Slogan EN
        <input type="text" name="slogan_en" value="<?= htmlspecialchars($branding['slogan_en']) ?>">
    </label>
    <label>Logo (.gif allowed)
        <input type="file" name="logo">
        <input type="text" name="logo_url" value="<?= htmlspecialchars($branding['logo_url']) ?>" placeholder="/uploads/logo.gif">
    </label>
    <label>Primary color
        <input type="color" name="color_primary" value="<?= htmlspecialchars($branding['color_primary']) ?>">
    </label>
    <label>Secondary color
        <input type="color" name="color_secondary" value="<?= htmlspecialchars($branding['color_secondary']) ?>">
    </label>
    <button class="btn primary" type="submit"><?= $this->localization->get('save') ?></button>
</form>
<?php include __DIR__ . '/../partials/footer.php'; ?>
