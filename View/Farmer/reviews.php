<?php
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/layout.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['review_id'] ?? 0);
    $ok = $farmerModel->respondToReview($id, $farmer_id, (string)($_POST['response'] ?? ''));
    flash($ok ? 'success' : 'error', $ok ? 'Review response saved.' : 'That response could not be saved.');
    farmer_redirect('reviews.php');
}

$reviews = $farmerModel->reviews($farmer_id);
$window = reviewWindowDays();

page_top('Reviews', 'reviews');
?>

<div class="page-title">
    <div>
        <h1>Reviews</h1>
        <p>Buyer feedback on your completed orders. You can respond to any review.</p>
    </div>
</div>

<?php if (!$reviews): ?>
<div class="card">
    <p class="empty">
        No reviews yet. Buyers can review a completed order within <?= $window ?> days of completion.
    </p>
</div>
<?php else: ?>
<?php foreach ($reviews as $r): ?>
<form class="card mb" method="post">
    <?= csrfField() ?>
    <input type="hidden" name="review_id" value="<?= (int)$r['review_id'] ?>">
    <div class="section-head">
        <strong><?= e((string)$r['buyer_name']) ?> &middot; <?= e(orderPublicId((int)$r['order_id'])) ?></strong>
        <span class="badge"><?= str_repeat('★', max(1, min(5, (int)$r['rating']))) ?> <?= (int)$r['rating'] ?>/5</span>
    </div>
    <p class="muted" style="font-size:12px">
        Order status: <?= e(farmer_status_label((string)$r['order_status'])) ?>
        &middot; reviewed <?= e(date('d M Y', strtotime((string)$r['created_at']))) ?>
    </p>
    <?php if (!empty($r['review_text'])): ?>
    <p><?= nl2br(e((string)$r['review_text'])) ?></p>
    <?php endif; ?>
    <div class="field mt">
        <label>Your Response</label>
        <textarea class="textarea" name="response" rows="3" maxlength="1000" placeholder="Thank the Buyer or explain your side."><?= e((string)($r['farmer_response'] ?? '')) ?></textarea>
        <small class="muted">Leave blank and save to remove your response.</small>
    </div>
    <button class="btn" type="submit">Save Response</button>
    <?php if (!empty($r['farmer_responded_at'])): ?>
    <p class="muted mt" style="font-size:12px">You responded on <?= e(date('d M Y, H:i', strtotime((string)$r['farmer_responded_at']))) ?>.</p>
    <?php endif; ?>
</form>
<?php endforeach; ?>
<?php endif; ?>

<?php page_bottom(); ?>
