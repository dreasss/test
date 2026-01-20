<?php include __DIR__ . '/../partials/header.php'; ?>
<div class="page-header">
    <h2><?= $this->localization->get('tickets') ?></h2>
    <?php if (\App\Core\Auth::user()['role'] === 'user'): ?>
        <a href="/tickets/create" class="btn primary"><?= $this->localization->get('new_ticket') ?></a>
    <?php endif; ?>
</div>
<div class="filters">
    <a href="/tickets?filter=open" class="chip">Open</a>
    <a href="/tickets?filter=assigned" class="chip">Assigned</a>
    <a href="/tickets?filter=closed" class="chip">Closed</a>
    <a href="/tickets?filter=overdue" class="chip">Overdue</a>
</div>
<form class="search" method="get" action="/tickets">
    <input type="text" name="q" placeholder="Search" value="<?= htmlspecialchars($query) ?>">
    <button class="btn secondary" type="submit">Go</button>
</form>
<div class="list">
    <?php foreach ($tickets as $ticket): ?>
        <?php
        $overdue = in_array($ticket['status'], ['new','assigned','in_progress','waiting_user','reopened'], true) && (!$ticket['last_agent_reply_at'] || strtotime($ticket['last_agent_reply_at']) < time() - 86400);
        ?>
        <a href="/tickets/view?id=<?= $ticket['id'] ?>" class="card ticket <?= $ticket['status'] === 'resolved' ? 'resolved' : '' ?>">
            <div>
                <div class="ticket-title">
                    <?= htmlspecialchars($ticket['title']) ?>
                    <?php if ($overdue): ?>
                        <span class="badge warning">!</span>
                    <?php endif; ?>
                </div>
                <div class="muted">#<?= $ticket['id'] ?> · <?= htmlspecialchars($ticket['building']) ?> <?= htmlspecialchars($ticket['room']) ?></div>
            </div>
            <span class="badge status <?= htmlspecialchars($ticket['status']) ?>"><?= htmlspecialchars($ticket['status']) ?></span>
        </a>
    <?php endforeach; ?>
</div>
<?php include __DIR__ . '/../partials/footer.php'; ?>
