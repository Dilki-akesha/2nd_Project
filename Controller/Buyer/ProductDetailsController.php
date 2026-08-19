<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../Model/Buyer/Product.php';

$model = new Product();
$id = (int)($_GET['id'] ?? 1);
$product = $model->getProductById($id);
if (!$product) {
    http_response_code(404);
    $product = $model->getProductById(1);
}
$images = $product['images'] ?? [$product['image'] ?? ''];
$farmerImage = $product['farmerImage'] ?? '';
require __DIR__ . '/../../View/Buyer/product-details.php';
