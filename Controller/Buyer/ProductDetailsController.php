<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../Model/Buyer/Product.php';

requireBuyerAuth();
$model = new Product();

$id = (int)($_GET['id'] ?? 0);
$product = $id > 0 ? $model->getProductById($id) : null;
if (!$product) {
    http_response_code(404);
    $product = null;
}

require __DIR__ . '/../../View/Buyer/product-details.php';
