<?php include __DIR__ . '/../partials/header.php'; ?>
<div class="page-header">
    <h2><?= $this->localization->get('new_ticket') ?></h2>
</div>
<form method="post" action="/tickets" enctype="multipart/form-data" class="form">
    <input type="hidden" name="_token" value="<?= \App\Core\CSRF::token() ?>">
    <label>Тема
        <input type="text" name="title" id="typeahead" placeholder="<?= $this->localization->get('typeahead_placeholder') ?>" required>
        <div id="suggestions" class="suggestions"></div>
    </label>
    <label>Описание
        <textarea name="description" rows="4" required></textarea>
    </label>
    <label><?= $this->localization->get('desired_time') ?>
        <input type="datetime-local" name="desired_at">
    </label>
    <div class="priority-group" data-priority-group>
        <input type="hidden" name="priority" value="low" data-priority-input>
        <button type="button" class="priority low active" data-priority="low"><?= $this->localization->get('priority_low') ?></button>
        <button type="button" class="priority medium" data-priority="medium"><?= $this->localization->get('priority_medium') ?></button>
        <button type="button" class="priority high" data-priority="high"><?= $this->localization->get('priority_high') ?></button>
    </div>
    <div class="profile-card">
        <div>
            <label><?= $this->localization->get('first_name') ?>
                <input type="text" name="first_name" value="<?= htmlspecialchars($user['first_name']) ?>" readonly data-profile-input>
            </label>
            <label><?= $this->localization->get('last_name') ?>
                <input type="text" name="last_name" value="<?= htmlspecialchars($user['last_name']) ?>" readonly data-profile-input>
            </label>
        </div>
        <div>
            <label><?= $this->localization->get('building') ?>
                <input type="text" name="building" value="<?= htmlspecialchars($user['building']) ?>" readonly data-profile-input>
            </label>
            <label><?= $this->localization->get('room') ?>
                <input type="text" name="room" value="<?= htmlspecialchars($user['room']) ?>" readonly data-profile-input>
            </label>
        </div>
        <button class="btn ghost" type="button" data-edit-profile>Изменить на один раз</button>
    </div>
    <label>Attachments
        <input type="file" name="attachments[]" multiple>
    </label>
    <div class="actions">
        <button class="btn primary" type="submit"><?= $this->localization->get('send') ?></button>
        <a href="/tickets" class="btn secondary"><?= $this->localization->get('cancel') ?></a>
    </div>
</form>
<?php include __DIR__ . '/../partials/footer.php'; ?>
