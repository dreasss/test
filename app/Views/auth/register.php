<?php include __DIR__ . '/../partials/header.php'; ?>
<div class="auth-card">
    <h2><?= $this->localization->get('register') ?></h2>
    <form method="post" action="/register" class="form">
        <input type="hidden" name="_token" value="<?= \App\Core\CSRF::token() ?>">
        <label><?= $this->localization->get('first_name') ?>
            <input type="text" name="first_name" required>
        </label>
        <label><?= $this->localization->get('last_name') ?>
            <input type="text" name="last_name" required>
        </label>
        <label><?= $this->localization->get('email') ?>
            <input type="email" name="email" required>
        </label>
        <label><?= $this->localization->get('password') ?>
            <input type="password" name="password" required>
        </label>
        <label><?= $this->localization->get('department') ?>
            <input type="text" name="department">
        </label>
        <label><?= $this->localization->get('building') ?>
            <input type="text" name="building">
        </label>
        <label><?= $this->localization->get('room') ?>
            <input type="text" name="room">
        </label>
        <button class="btn primary" type="submit"><?= $this->localization->get('register') ?></button>
    </form>
</div>
<?php include __DIR__ . '/../partials/footer.php'; ?>
