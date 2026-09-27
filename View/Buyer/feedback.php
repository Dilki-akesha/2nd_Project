<?php
require_once __DIR__ . '/../../config/app.php';
if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) redirect('Controller/Buyer/FeedbackController.php');
require __DIR__ . '/includes/layout.php';
buyer_page_top('Reviews & Issues', 'FeedbackController.php');

$reviewWindow = reviewWindowDays();
$confirmHours = buyerConfirmationHours();
?>

<section class="buyer-title">
    <div>
        <h2>Reviews &amp; Issues</h2>
        <p>Review a completed order, or report an issue with any of your orders.</p>
    </div>
</section>

<?php buyerFlash(); ?>

<div class="buyer-columns-3">
    <section class="buyer-panel">
        <h3>Write a review</h3>
        <p class="buyer-note" style="margin-bottom:16px">
            Reviews are available for <?= $reviewWindow ?> days after an order is completed.
        </p>
        <?php if (!$reviewableOrders): ?>
            <p class="buyer-empty">No completed orders are currently eligible for a new review.</p>
        <?php else: ?>
        <form method="post" action="<?= e(buyerRoute('FeedbackController.php')) ?>" data-once="1">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="review">
            <div class="buyer-field">
                <span>Completed order</span>
                <select name="order_id" required>
                    <?php foreach ($reviewableOrders as $r): ?>
                    <option value="<?= (int)$r['order_id'] ?>" <?= (int)($selectedOrder ?? 0) === (int)$r['order_id'] ? 'selected' : '' ?>>
                        <?= e($r['order_number']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="buyer-field">
                <span>Your rating</span>
                <select name="rating" required>
                    <?php for ($rating = 5; $rating >= 1; $rating--): ?>
                    <option value="<?= $rating ?>"><?= $rating ?> / 5</option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="buyer-field">
                <span>Your review</span>
                <textarea name="review_text" rows="4" required></textarea>
            </div>
            <button class="buyer-button" type="submit">
                <span class="material-symbols-outlined" aria-hidden="true">rate_review</span>
                <span>Submit Review</span>
            </button>
        </form>
        <?php endif; ?>
    </section>

    <section class="buyer-panel">
        <h3>Report an issue</h3>
        <p class="buyer-note" style="margin-bottom:16px">
            Reporting an issue does not block the automatic <?= $confirmHours ?>-hour completion of a
            Delivered order.
        </p>
        <?php if (!$complaintOrders): ?>
            <p class="buyer-empty">You need an order before reporting an order issue.</p>
        <?php else: ?>
        <form method="post" enctype="multipart/form-data" action="<?= e(buyerRoute('FeedbackController.php')) ?>" data-once="1">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="complaint">
            <div class="buyer-field">
                <span>Order</span>
                <select name="order_id" required>
                    <?php foreach ($complaintOrders as $r): ?>
                    <option value="<?= (int)$r['order_id'] ?>" <?= (int)($selectedOrder ?? 0) === (int)$r['order_id'] ? 'selected' : '' ?>>
                        <?= e($r['order_number']) ?> &middot; <?= e(harvestlyStatusLabel((string)$r['order_status'])) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="buyer-field">
                <span>Category</span>
                <select name="category" required>
                    <option>Product issue</option>
                    <option>Delivery issue</option>
                    <option>Payment issue</option>
                    <option>Other</option>
                </select>
            </div>
            <div class="buyer-field">
                <span>Description</span>
                <textarea name="details" rows="4" required></textarea>
            </div>
            <div class="buyer-field">
                <span>Optional evidence</span>
                <input type="file" name="photos" accept="image/jpeg,image/png,application/pdf" data-file-target="evidenceName">
                <small>JPG, PNG or PDF; 5 MB maximum.</small>
                <small id="evidenceName"></small>
            </div>
            <button class="buyer-button" type="submit">
                <span class="material-symbols-outlined" aria-hidden="true">report_problem</span>
                <span>Submit Issue</span>
            </button>
        </form>
        <?php endif; ?>
    </section>
</div>

<section class="buyer-panel">
    <h3>Your reviews (<?= count($reviews) ?>)</h3>
    <?php if (!$reviews): ?>
        <p class="buyer-empty">You have not written any reviews yet.</p>
    <?php endif; ?>
    <?php foreach ($reviews as $r): ?>
    <article class="buyer-row" style="align-items:flex-start;padding:16px 0;border-bottom:1px solid var(--hv-border)">
        <div class="buyer-grow">
            <div class="buyer-row" style="gap:10px">
                <strong><?= e(orderPublicId((int)$r['order_id'])) ?></strong>
                <span class="buyer-badge"><?= (int)$r['rating'] ?> / 5</span>
            </div>
            <p class="buyer-note" style="margin-top:8px"><?= nl2br(e((string)$r['review_text'])) ?></p>
            <?php if (!empty($r['farmer_response'])): ?>
                <p class="buyer-note" style="margin-top:8px"><strong>Farmer response:</strong> <?= e((string)$r['farmer_response']) ?></p>
            <?php endif; ?>
        </div>
        <div class="buyer-actions">
            <details class="buyer-details">
                <summary>Edit</summary>
                <form method="post" action="<?= e(buyerRoute('FeedbackController.php')) ?>">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="update_review">
                    <input type="hidden" name="id" value="<?= (int)$r['review_id'] ?>">
                    <div class="buyer-field">
                        <span>Rating</span>
                        <select name="rating" required>
                            <?php for ($rating = 5; $rating >= 1; $rating--): ?>
                            <option value="<?= $rating ?>" <?= (int)$r['rating'] === $rating ? 'selected' : '' ?>><?= $rating ?> / 5</option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="buyer-field">
                        <span>Review</span>
                        <textarea name="review_text" rows="3" required><?= e((string)$r['review_text']) ?></textarea>
                    </div>
                    <button class="buyer-button buyer-secondary buyer-small" type="submit">Save Review</button>
                </form>
            </details>
            <form method="post" action="<?= e(buyerRoute('FeedbackController.php')) ?>"
                  data-confirm="Delete this review?">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="delete_review">
                <input type="hidden" name="id" value="<?= (int)$r['review_id'] ?>">
                <button class="buyer-button buyer-danger buyer-small" type="submit">Delete</button>
            </form>
        </div>
    </article>
    <?php endforeach; ?>
</section>

<section class="buyer-panel">
    <h3>Your issues (<?= count($complaints) ?>)</h3>
    <?php if (!$complaints): ?>
        <p class="buyer-empty">You have not reported any issues.</p>
    <?php endif; ?>
    <?php foreach ($complaints as $c): ?>
    <?php $isOpen = (string)$c['complaint_status'] === 'OPEN'; ?>
    <article class="buyer-row" style="align-items:flex-start;padding:16px 0;border-bottom:1px solid var(--hv-border)">
        <div class="buyer-grow">
            <div class="buyer-row" style="gap:10px">
                <strong><?= e(orderPublicId((int)$c['order_id'])) ?></strong>
                <span class="buyer-badge buyer-badge--muted"><?= e((string)$c['category']) ?></span>
                <?= buyerStatusBadge((string)$c['complaint_status']) ?>
            </div>
            <p class="buyer-note" style="margin-top:8px"><?= nl2br(e((string)$c['description'])) ?></p>
            <?php if (!empty($c['evidence_path'])): ?>
                <p class="buyer-divider-note" style="margin-top:6px">Evidence attached: <?= e(basename((string)$c['evidence_path'])) ?></p>
            <?php endif; ?>
            <?php if (!empty($c['admin_response'])): ?>
                <div class="buyer-alert info" style="margin-top:10px;margin-bottom:0">
                    <strong>Admin response</strong>
                    <?= nl2br(e((string)$c['admin_response'])) ?>
                </div>
            <?php endif; ?>
        </div>
        <?php if ($isOpen): ?>
        <div class="buyer-actions">
            <details class="buyer-details">
                <summary>Edit</summary>
                <form method="post" action="<?= e(buyerRoute('FeedbackController.php')) ?>">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="update_complaint">
                    <input type="hidden" name="id" value="<?= (int)$c['complaint_id'] ?>">
                    <div class="buyer-field">
                        <span>Category</span>
                        <input type="text" name="category" value="<?= e((string)$c['category']) ?>" required>
                    </div>
                    <div class="buyer-field">
                        <span>Description</span>
                        <textarea name="details" rows="3" required><?= e((string)$c['description']) ?></textarea>
                    </div>
                    <button class="buyer-button buyer-secondary buyer-small" type="submit">Save Issue</button>
                </form>
            </details>
            <form method="post" action="<?= e(buyerRoute('FeedbackController.php')) ?>"
                  data-confirm="Delete this issue?">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="delete_complaint">
                <input type="hidden" name="id" value="<?= (int)$c['complaint_id'] ?>">
                <button class="buyer-button buyer-danger buyer-small" type="submit">Delete</button>
            </form>
        </div>
        <?php endif; ?>
    </article>
    <?php endforeach; ?>
</section>

<?php buyer_page_bottom(); ?>
