<?php include __DIR__ . '/../partials/header.php'; ?>
<div class="auth-card">
    <h2><?= $this->localization->get('login') ?></h2>
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert"><?= htmlspecialchars($_SESSION['error']) ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    <form method="post" action="/login" class="form">
        <input type="hidden" name="_token" value="<?= \App\Core\CSRF::token() ?>">
        <label><?= $this->localization->get('email') ?>
            <input type="email" name="email" required>
        </label>
        <label><?= $this->localization->get('password') ?>
            <input type="password" name="password" required>
        </label>
        <button class="btn primary" type="submit"><?= $this->localization->get('local_login') ?></button>
    </form>
    <div class="divider">or</div>
    <?php if ($this->config['oidc']['enabled']): ?>
        <a href="/oidc/login" class="btn secondary full"><?= $this->localization->get('sso_login') ?></a>
    <?php else: ?>
        <div class="muted">SSO disabled</div>
    <?php endif; ?>
    <p class="muted"><a href="/register"><?= $this->localization->get('register') ?></a></p>
</div>
<?php include __DIR__ . '/../partials/footer.php'; ?>
