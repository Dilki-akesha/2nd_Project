<?php

declare(strict_types=1);

/**
 * Harvestly Buyer reviews and the single common Complaint / Issue workflow.
 *
 * reviews has exactly one `rating` column, so a review is a single Buyer rating
 * for the Farmer and the order. The review window comes from
 * platform_settings.review_window_days (default 14).
 */
final class Feedback
{
    private function orderRow(string $publicId): ?array
    {
        $id = parseOrderPublicId($publicId);
        if ($id <= 0) return null;
        return db_fetch_one(
            'SELECT order_id, buyer_id, farmer_id, order_status, delivered_at, completed_at, grand_total
             FROM orders WHERE order_id = ? AND buyer_id = ? LIMIT 1',
            'ii',
            [$id, currentBuyerId()]
        );
    }

    /** The single order completion timestamp used for the review window. */
    private function completedAt(array $order): ?string
    {
        return $order['completed_at'] ?: ($order['delivered_at'] ?: null);
    }

    public function submitReview(array $data): array
    {
        requireBuyerAuth();
        $order = $this->orderRow(trim((string)($data['order_id'] ?? '')));
        if (!$order || $order['order_status'] !== 'COMPLETED') {
            return ['success' => false, 'message' => 'Only completed orders can be reviewed.'];
        }

        $window = reviewWindowDays();
        $completedAt = $this->completedAt($order);
        if ($completedAt && strtotime((string)$completedAt) < strtotime('-' . $window . ' days')) {
            return ['success' => false, 'message' => 'The ' . $window . '-day review window has closed.'];
        }

        $exists = (int)db_scalar(
            'SELECT COUNT(*) FROM reviews WHERE order_id = ?',
            'i',
            [(int)$order['order_id']],
            0
        );
        if ($exists > 0) {
            return ['success' => false, 'message' => 'A review has already been submitted for this order.'];
        }

        $rawRating = $data['rating'] ?? $data['farmer_rating'] ?? null;
        if (!is_numeric($rawRating) || (int)$rawRating < 1 || (int)$rawRating > 5) {
            return ['success' => false, 'message' => 'Please select a rating from 1 to 5.'];
        }
        $rating = (int)$rawRating;
        $text = trim((string)($data['review_text'] ?? $data['quality_comment'] ?? ''));
        if ($text === '') return ['success' => false, 'message' => 'Please enter your review.'];
        if (mb_strlen($text) > 2000) return ['success' => false, 'message' => 'Review must be 2000 characters or fewer.'];

        $ok = db_execute(
            'INSERT INTO reviews (order_id, buyer_id, farmer_id, rating, review_text)
             VALUES (?, ?, ?, ?, ?)',
            'iiiis',
            [(int)$order['order_id'], currentBuyerId(), (int)$order['farmer_id'], $rating, $text]
        );

        return [
            'success' => $ok,
            'message' => $ok ? 'Thank you. Your review has been submitted.' : 'Unable to submit the review.',
        ];
    }

    public function submitComplaint(array $data): array
    {
        requireBuyerAuth();
        $order = $this->orderRow(trim((string)($data['order_id'] ?? '')));
        if (!$order) return ['success' => false, 'message' => 'Please select one of your orders.'];

        $category = trim((string)($data['category'] ?? ''));
        if (!in_array($category, ['Product issue', 'Delivery issue', 'Payment issue', 'Other'], true)) {
            return ['success' => false, 'message' => 'Please select a valid issue category.'];
        }

        $details = trim((string)($data['details'] ?? $data['description'] ?? ''));
        if ($details === '') return ['success' => false, 'message' => 'Please describe the issue.'];
        if (mb_strlen($details) > 5000) return ['success' => false, 'message' => 'Issue description must be 5000 characters or fewer.'];

        $evidencePath = null;
        if (!empty($_FILES['photos']['name'])) {
            try {
                $evidencePath = handleFileUpload($_FILES['photos'], 'assets/documents/complaints');
            } catch (Exception $e) {
                return ['success' => false, 'message' => $e->getMessage()];
            }
        }

        $ok = db_execute(
            "INSERT INTO complaints
             (order_id, complainant_user_id, complainant_role, category, description, evidence_path, complaint_status)
             VALUES (?, ?, 'BUYER', ?, ?, ?, 'OPEN')",
            'iisss',
            [(int)$order['order_id'], currentBuyerId(), $category, $details, $evidencePath]
        );

        return [
            'success' => $ok,
            'message' => $ok ? 'Your complaint/issue has been submitted.' : 'Unable to submit the complaint.',
        ];
    }

    public function getReviewableOrders(): array
    {
        requireBuyerAuth();
        $window = reviewWindowDays();
        $rows = db_fetch_all(
            "SELECT o.order_id, o.completed_at, o.delivered_at, o.grand_total
             FROM orders o
             LEFT JOIN reviews r ON r.order_id = o.order_id
             WHERE o.buyer_id = ? AND o.order_status = 'COMPLETED'
               AND COALESCE(o.completed_at, o.delivered_at) >= DATE_SUB(NOW(), INTERVAL ? DAY)
               AND r.review_id IS NULL
             ORDER BY COALESCE(o.completed_at, o.delivered_at) DESC",
            'ii',
            [currentBuyerId(), $window]
        );
        return array_map(static fn(array $r): array => [
            'order_id' => (int)$r['order_id'],
            'order_number' => orderPublicId((int)$r['order_id']),
            'completed_at' => $r['completed_at'] ?: $r['delivered_at'],
            'total' => (float)$r['grand_total'],
        ], $rows);
    }

    public function getComplaintOrders(): array
    {
        requireBuyerAuth();
        $rows = db_fetch_all(
            "SELECT order_id, order_status, delivered_at, grand_total
             FROM orders
             WHERE buyer_id = ?
               AND order_status NOT IN ('PENDING_PAYMENT','CANCELLED','REJECTED')
             ORDER BY created_at DESC, order_id DESC",
            'i',
            [currentBuyerId()]
        );
        return array_map(static fn(array $r): array => [
            'order_id' => (int)$r['order_id'],
            'order_number' => orderPublicId((int)$r['order_id']),
            'order_status' => (string)$r['order_status'],
            'delivered_at' => $r['delivered_at'],
            'total' => (float)$r['grand_total'],
        ], $rows);
    }

    public function getReviews(): array
    {
        requireBuyerAuth();
        return db_fetch_all(
            'SELECT r.*, o.order_id
             FROM reviews r
             JOIN orders o ON o.order_id = r.order_id
             WHERE r.buyer_id = ?
             ORDER BY r.created_at DESC, r.review_id DESC',
            'i',
            [currentBuyerId()]
        );
    }

    public function updateReview(int $id, array $data): bool
    {
        $window = reviewWindowDays();
        $exists = db_fetch_one(
            "SELECT r.review_id
             FROM reviews r
             JOIN orders o ON o.order_id = r.order_id
             WHERE r.review_id = ? AND r.buyer_id = ?
               AND COALESCE(o.completed_at, o.delivered_at) >= DATE_SUB(NOW(), INTERVAL ? DAY)",
            'iii',
            [$id, currentBuyerId(), $window]
        );
        if (!$exists) return false;

        $rawRating = $data['rating'] ?? $data['farmer_rating'] ?? null;
        if (!is_numeric($rawRating) || (int)$rawRating < 1 || (int)$rawRating > 5) return false;
        $rating = (int)$rawRating;
        // The stored review_text is edited verbatim - no label prefixing, so
        // re-saving an existing review cannot duplicate prefixes.
        $text = trim((string)($data['review_text'] ?? $data['quality_comment'] ?? ''));
        if ($text === '' || mb_strlen($text) > 2000) return false;

        return db_execute(
            'UPDATE reviews SET rating = ?, review_text = ?
             WHERE review_id = ? AND buyer_id = ?',
            'isii',
            [$rating, $text, $id, currentBuyerId()]
        );
    }

    public function deleteReview(int $id): bool
    {
        return db_execute(
            'DELETE FROM reviews WHERE review_id = ? AND buyer_id = ?',
            'ii',
            [$id, currentBuyerId()]
        );
    }

    public function getComplaints(): array
    {
        requireBuyerAuth();
        return db_fetch_all(
            "SELECT c.*, o.order_id
             FROM complaints c
             JOIN orders o ON o.order_id = c.order_id
             WHERE c.complainant_user_id = ? AND c.complainant_role = 'BUYER'
             ORDER BY c.created_at DESC, c.complaint_id DESC",
            'i',
            [currentBuyerId()]
        );
    }

    public function updateComplaint(int $id, array $data): bool
    {
        $category = trim((string)($data['category'] ?? ''));
        $details = trim((string)($data['details'] ?? ''));
        if (!in_array($category, ['Product issue', 'Delivery issue', 'Payment issue', 'Other'], true) ||
            $details === '' || mb_strlen($details) > 5000) return false;
        return db_execute(
            "UPDATE complaints
             SET category = ?, description = ?
             WHERE complaint_id = ? AND complainant_user_id = ?
               AND complainant_role = 'BUYER' AND complaint_status = 'OPEN'",
            'ssii',
            [$category, $details, $id, currentBuyerId()]
        );
    }

    public function deleteComplaint(int $id): bool
    {
        return db_execute(
            "DELETE FROM complaints
             WHERE complaint_id = ? AND complainant_user_id = ?
               AND complainant_role = 'BUYER' AND complaint_status = 'OPEN'",
            'ii',
            [$id, currentBuyerId()]
        );
    }
}
