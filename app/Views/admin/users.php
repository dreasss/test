<?php include __DIR__ . '/../partials/header.php'; ?>
<div class="page-header">
    <h2>Users</h2>
</div>
<div class="grid">
    <?php foreach ($users as $u): ?>
        <div class="card">
            <strong><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?></strong>
            <div class="muted"><?= htmlspecialchars($u['email']) ?></div>
            <div class="badge"><?= htmlspecialchars($u['role']) ?></div>
        </div>
    <?php endforeach; ?>
</div>
<form method="post" action="/admin/users/save" class="form">
    <input type="hidden" name="_token" value="<?= \App\Core\CSRF::token() ?>">
    <h3>Create/Update user</h3>
    <label>ID (for update)
        <input type="number" name="id" value="">
    </label>
    <label>Role
        <select name="role" class="select">
            <option value="user">User</option>
            <option value="agent">Agent</option>
            <option value="admin">Admin</option>
        </select>
    </label>
    <label>First name
        <input type="text" name="first_name">
    </label>
    <label>Last name
        <input type="text" name="last_name">
    </label>
    <label>Email
        <input type="email" name="email">
    </label>
    <label>Department
        <input type="text" name="department">
    </label>
    <label>Building
        <input type="text" name="building">
    </label>
    <label>Room
        <input type="text" name="room">
    </label>
    <label>Locale
        <select name="locale" class="select">
            <option value="ru">RU</option>
            <option value="en">EN</option>
        </select>
    </label>
    <label>Password (optional)
        <input type="password" name="password">
    </label>
    <button class="btn primary" type="submit">Save</button>
</form>
<?php include __DIR__ . '/../partials/footer.php'; ?>
