<?php
require_once __DIR__ . '/../../config/app.php';
if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) redirect('Controller/Buyer/NotificationsController.php');
require __DIR__ . '/includes/layout.php';
buyer_page_top('Notifications', 'NotificationsController.php');

$items = $notifications['items'];
?>

<section class="buyer-title">
    <div>
        <h2>Notifications</h2>
        <p>Order, delivery, payment and account updates stored in Harvestly.</p>
    </div>
    <div class="buyer-actions">
        <?php if ($notifications['unread'] > 0): ?>
        <form method="post" action="<?= e(buyerRoute('NotificationsController.php')) ?>">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="read_all">
            <input type="hidden" name="filter" value="<?= e($notifications['filter']) ?>">
            <input type="hidden" name="page" value="<?= (int)$notifications['page'] ?>">
            <button class="buyer-button" type="submit">
                <span class="material-symbols-outlined" aria-hidden="true">done_all</span>
                <span>Mark All Read (<?= (int)$notifications['unread'] ?>)</span>
            </button>
        </form>
        <?php endif; ?>
    </div>
</section>

<?php buyerFlash(); ?>

<section class="buyer-panel">
    <div class="buyer-actions">
        <?php foreach (['all' => 'All', 'unread' => 'Unread', 'read' => 'Read'] as $key => $label): ?>
        <a class="buyer-button <?= $notifications['filter'] === $key ? '' : 'buyer-ghost' ?> buyer-small"
           href="<?= e(buyerRoute('NotificationsController.php', 'filter=' . $key)) ?>"><?= e($label) ?></a>
        <?php endforeach; ?>
    </div>
</section>

<?php if (!$items): ?>
<section class="buyer-panel buyer-empty">
    <?= $notifications['filter'] === 'unread' ? 'You have no unread notifications.' : 'You have no notifications yet.' ?>
</section>
<?php else: ?>
<?php foreach ($items as $notice): ?>
<article class="buyer-panel">
    <div class="buyer-row buyer-row--between" style="margin-bottom:10px">
        <div class="buyer-product-badges">
            <span class="buyer-badge <?= $notice['unread'] ? '' : 'buyer-badge--muted' ?>">
                <?= $notice['unread'] ? 'Unread' : 'Read' ?>
            </span>
            <span class="buyer-badge buyer-badge--muted"><?= e($notice['type']) ?></span>
        </div>
        <span class="buyer-muted"><?= e($notice['time']) ?></span>
    </div>
    <h3><?= e($notice['title']) ?></h3>
    <p class="buyer-note"><?= e($notice['message']) ?></p>
    <div class="buyer-actions" style="margin-top:14px">
        <a class="buyer-button buyer-secondary buyer-small" href="<?= e($notice['action_url']) ?>">
            <span class="material-symbols-outlined" aria-hidden="true"><?= e($notice['icon']) ?></span>
            <span><?= e($notice['action']) ?></span>
        </a>
        <?php if ($notice['unread']): ?>
        <form method="post" action="<?= e(buyerRoute('NotificationsController.php')) ?>">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="read">
            <input type="hidden" name="id" value="<?= (int)$notice['id'] ?>">
            <input type="hidden" name="filter" value="<?= e($notifications['filter']) ?>">
            <input type="hidden" name="page" value="<?= (int)$notifications['page'] ?>">
            <button class="buyer-button buyer-ghost buyer-small" type="submit">Mark Read</button>
        </form>
        <?php endif; ?>
    </div>
</article>
<?php endforeach; ?>

<?php if ($notifications['pages'] > 1): ?>
<section class="buyer-panel">
    <div class="buyer-actions" style="justify-content:center">
        <?php for ($p = 1; $p <= $notifications['pages']; $p++): ?>
        <a class="buyer-button <?= $p === $notifications['page'] ? '' : 'buyer-ghost' ?> buyer-small"
           href="<?= e(buyerRoute('NotificationsController.php', 'filter=' . $notifications['filter'] . '&page=' . $p)) ?>"><?= $p ?></a>
        <?php endfor; ?>
    </div>
</section>
<?php endif; ?>
<?php endif; ?>

<?php buyer_page_bottom(); ?>
