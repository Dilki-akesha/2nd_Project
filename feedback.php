<?php

declare(strict_types=1);

// Compatibility entry point for older links/bookmarks.
$orderId = trim((string)($_GET['order_id'] ?? ''));
$target = '/Harvestly/Controller/Buyer/FeedbackController.php';
if ($orderId !== '') {
    $target .= '?order_id=' . urlencode($orderId);
}
header('Location: ' . $target, true, 302);
exit;
