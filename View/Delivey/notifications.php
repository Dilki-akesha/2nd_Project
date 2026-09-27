<?php $notifications = $data['notifications'] ?? []; ?>
<?php $unread = 0; foreach ($notifications as $n) { if (!$n['is_read']) $unread++; } ?>

<div class="courier-card">
    <div class="courier-row between">
        <div>
            <h3 style="margin:0">Notifications</h3>
        </div>
        <?php if ($unread > 0): ?>
        <form method="post" action="<?= e(url('Controller/Courier/CourierController.php')) ?>">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="mark_notifications_read">
            <input type="hidden" name="return_page" value="notifications">
            <button class="courier-btn secondary small" type="submit">
                <span class="material-symbols-outlined" aria-hidden="true">done_all</span>
                <span>Mark All Read (<?= $unread ?>)</span>
            </button>
        </form>
        <?php endif; ?>
    </div>
</div>

<?php if (!$notifications): ?>
<div class="courier-card courier-empty">You have no notifications.</div>
<?php else: ?>
<div class="courier-table-wrap courier-card">
    <table class="courier-table">
        <thead>
            <tr><th>Notification</th><th>Message</th><th>Date</th><th>Status</th></tr>
        </thead>
        <tbody>
        <?php foreach ($notifications as $n): ?>
        <tr>
            <td><strong><?= e((string)$n['title']) ?></strong></td>
            <td style="white-space:normal;min-width:240px"><?= e((string)$n['message']) ?></td>
            <td class="courier-nowrap"><?= e(date('d M Y, H:i', strtotime((string)$n['created_at']))) ?></td>
            <td>
                <span class="courier-badge <?= $n['is_read'] ? 'muted' : 'warn' ?>">
                    <?= $n['is_read'] ? 'Read' : 'Unread' ?>
                </span>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
