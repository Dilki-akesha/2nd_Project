<?php
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/layout.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['all'])) {
        $farmerModel->markAllNotificationsRead($farmer_id);
    } else {
        $farmerModel->markNotificationRead((int)($_POST['id'] ?? 0), $farmer_id);
    }
    farmer_redirect('notifications.php');
}

$notifications = $farmerModel->notifications($farmer_id, 100);
$unread = 0;
foreach ($notifications as $n) {
    if (!$n['is_read']) $unread++;
}

page_top('Notifications', 'notifications');
?>

<div class="page-title">
    <div>
        <h1>Notifications</h1>
        <p>Order, delivery and account updates stored in the Harvestly database.</p>
    </div>
    <?php if ($unread > 0): ?>
    <form method="post">
        <?= csrfField() ?>
        <button class="btn secondary" name="all" type="submit">Mark All Read (<?= $unread ?>)</button>
    </form>
    <?php endif; ?>
</div>

<?php if (!$notifications): ?>
<div class="card empty">You have no notifications.</div>
<?php endif; ?>

<?php foreach ($notifications as $n): ?>
<div class="card mb">
    <div class="section-head">
        <strong><?= e((string)$n['title']) ?></strong>
        <span class="badge <?= $n['is_read'] ? 'gray' : '' ?>"><?= $n['is_read'] ? 'Read' : 'Unread' ?></span>
    </div>
    <p class="muted"><?= e((string)$n['message']) ?></p>
    <small class="muted"><?= e(date('d M Y, H:i', strtotime((string)$n['created_at']))) ?></small>
    <div class="row mt">
        <?php if (!empty($n['related_order_id'])): ?>
        <a class="btn secondary small" href="order-details.php?id=<?= (int)$n['related_order_id'] ?>">View Order</a>
        <?php endif; ?>
        <?php if (!$n['is_read']): ?>
        <form method="post">
            <?= csrfField() ?>
            <input type="hidden" name="id" value="<?= (int)$n['notification_id'] ?>">
            <button class="btn secondary small" type="submit">Mark Read</button>
        </form>
        <?php endif; ?>
    </div>
</div>
<?php endforeach; ?>

<?php page_bottom(); ?>
