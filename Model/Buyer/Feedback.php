<?php

declare(strict_types=1);

final class Feedback
{
    public function submitReview(array $data): array
    {
        $farmerRating = (int)($data['farmer_rating'] ?? 0);
        $deliveryRating = (int)($data['delivery_rating'] ?? 0);

        if ($farmerRating < 1 || $farmerRating > 5 || $deliveryRating < 1 || $deliveryRating > 5) {
            return [
                'success' => false,
                'message' => 'Please give a rating from 1 to 5 for both areas.',
            ];
        }

        $orderId = !empty($data['order_id']) ? (string)$data['order_id'] : null;

        if ($orderId !== null) {
            $check = db()->prepare(
                'SELECT id FROM orders WHERE order_number = ? AND user_id = ? LIMIT 1'
            );
            $check->execute([$orderId, currentBuyerId()]);

            if (!$check->fetchColumn()) {
                return ['success' => false, 'message' => 'The selected order could not be found.'];
            }
        }

        $stmt = db()->prepare(
            'INSERT INTO feedback
                (user_id, order_id, farmer_rating, delivery_rating, quality_comment, delivery_comment)
             VALUES (?,
                 (SELECT id FROM orders WHERE order_number = ? AND user_id = ?),
                 ?, ?, ?, ?)'
        );

        $stmt->execute([
            currentBuyerId(),
            $orderId,
            currentBuyerId(),
            $farmerRating,
            $deliveryRating,
            trim((string)($data['quality_comment'] ?? '')),
            trim((string)($data['delivery_comment'] ?? '')),
        ]);

        return [
            'success' => true,
            'message' => 'Thank you! Your review has been submitted successfully.',
        ];
    }

    public function submitComplaint(array $data): array
    {
        $category = trim((string)($data['category'] ?? ''));
        $details = trim((string)($data['details'] ?? ''));
        $orderId = !empty($data['order_id']) ? (string)$data['order_id'] : null;

        if ($category === '' || $details === '') {
            return [
                'success' => false,
                'message' => 'Please select a complaint category and enter details.',
            ];
        }

        if ($orderId !== null) {
            $check = db()->prepare(
                'SELECT id FROM orders WHERE order_number = ? AND user_id = ? LIMIT 1'
            );
            $check->execute([$orderId, currentBuyerId()]);

            if (!$check->fetchColumn()) {
                return ['success' => false, 'message' => 'The selected order could not be found.'];
            }
        }

        $stmt = db()->prepare(
            'INSERT INTO complaints (user_id, order_id, category, details)
             VALUES (?,
                 (SELECT id FROM orders WHERE order_number = ? AND user_id = ?),
                 ?, ?)'
        );
        $stmt->execute([
            currentBuyerId(),
            $orderId,
            currentBuyerId(),
            $category,
            $details,
        ]);

        return [
            'success' => true,
            'message' => 'Your complaint has been submitted successfully.',
        ];
    }

    public function getReviews(): array
    {
        $stmt = db()->prepare(
            'SELECT * FROM feedback WHERE user_id = ? ORDER BY created_at DESC, id DESC'
        );
        $stmt->execute([currentBuyerId()]);
        return $stmt->fetchAll();
    }

    public function updateReview(int $id, array $data): bool
    {
        $stmt = db()->prepare(
            'UPDATE feedback
             SET farmer_rating = ?, delivery_rating = ?, quality_comment = ?, delivery_comment = ?
             WHERE id = ? AND user_id = ?'
        );
        return $stmt->execute([
            (int)$data['farmer_rating'],
            (int)$data['delivery_rating'],
            trim((string)($data['quality_comment'] ?? '')),
            trim((string)($data['delivery_comment'] ?? '')),
            $id,
            currentBuyerId(),
        ]);
    }

    public function deleteReview(int $id): bool
    {
        $stmt = db()->prepare(
            'DELETE FROM feedback WHERE id = ? AND user_id = ?'
        );
        return $stmt->execute([$id, currentBuyerId()]);
    }

    public function getComplaints(): array
    {
        $stmt = db()->prepare(
            'SELECT c.*, o.order_number
             FROM complaints c
             LEFT JOIN orders o ON o.id = c.order_id
             WHERE c.user_id = ?
             ORDER BY c.created_at DESC, c.id DESC'
        );
        $stmt->execute([currentBuyerId()]);
        return $stmt->fetchAll();
    }

    public function updateComplaint(int $id, array $data): bool
    {
        $stmt = db()->prepare(
            'UPDATE complaints
             SET category = ?, details = ?
             WHERE id = ? AND user_id = ? AND status = \'Open\''
        );
        return $stmt->execute([
            trim((string)$data['category']),
            trim((string)$data['details']),
            $id,
            currentBuyerId(),
        ]);
    }

    public function deleteComplaint(int $id): bool
    {
        $stmt = db()->prepare(
            'DELETE FROM complaints WHERE id = ? AND user_id = ? AND status = \'Open\''
        );
        return $stmt->execute([$id, currentBuyerId()]);
    }
}
