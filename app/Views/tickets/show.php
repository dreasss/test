<?php include __DIR__ . '/../partials/header.php'; ?>
<div class="page-header">
    <div>
        <h2><?= htmlspecialchars($ticket['title']) ?></h2>
        <div class="muted">#<?= $ticket['id'] ?> · <?= htmlspecialchars($ticket['status']) ?></div>
    </div>
    <div class="actions">
        <?php if (\App\Core\Auth::user()['role'] === 'agent' || \App\Core\Auth::user()['role'] === 'admin'): ?>
            <form method="post" action="/tickets/assign" class="inline-form">
                <input type="hidden" name="_token" value="<?= \App\Core\CSRF::token() ?>">
                <input type="hidden" name="id" value="<?= $ticket['id'] ?>">
                <button class="btn secondary" type="submit">Assign to me</button>
            </form>
        <?php endif; ?>
        <form method="post" action="/tickets/status" class="inline-form">
            <input type="hidden" name="_token" value="<?= \App\Core\CSRF::token() ?>">
            <input type="hidden" name="id" value="<?= $ticket['id'] ?>">
            <select name="status" onchange="this.form.submit()" class="select">
                <?php foreach (['new','assigned','in_progress','waiting_user','resolved','closed','reopened'] as $status): ?>
                    <option value="<?= $status ?>" <?= $ticket['status'] === $status ? 'selected' : '' ?>><?= $status ?></option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>
</div>
<div class="chat">
    <?php foreach ($messages as $message): ?>
        <div class="message <?= $message['is_system'] ? 'system' : '' ?>">
            <div class="avatar"><?= strtoupper(substr($message['first_name'], 0, 1)) ?></div>
            <div class="bubble">
                <div class="meta">
                    <?= htmlspecialchars($message['first_name'] . ' ' . $message['last_name']) ?> · <?= htmlspecialchars($message['created_at']) ?>
                </div>
                <div><?= nl2br(htmlspecialchars($message['body'])) ?></div>
                <?php $attachments = json_decode($message['attachments'], true) ?: []; ?>
                <?php if ($attachments): ?>
                    <div class="attachments">
                        <?php foreach ($attachments as $file): ?>
                            <a href="<?= htmlspecialchars($file) ?>" target="_blank">Attachment</a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<form method="post" action="/tickets/message" enctype="multipart/form-data" class="form chat-form">
    <input type="hidden" name="_token" value="<?= \App\Core\CSRF::token() ?>">
    <input type="hidden" name="ticket_id" value="<?= $ticket['id'] ?>">
    <textarea name="body" rows="3" placeholder="Message" required></textarea>
    <input type="file" name="attachments[]" multiple>
    <button class="btn primary" type="submit">Send</button>
</form>
<?php include __DIR__ . '/../partials/footer.php'; ?>
