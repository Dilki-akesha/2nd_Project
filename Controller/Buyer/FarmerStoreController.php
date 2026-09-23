<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../Model/Buyer/Product.php';

$farmer = trim((string)($_GET['farmer'] ?? ''));

if ($farmer === '') {
    redirect('Controller/Buyer/ProductController.php');
}

$model = new Product();
$store = $model->getFarmerStore($farmer);

if ($store === null) {
    http_response_code(404);
    echo 'Farmer store not found.';
    exit;
}

require __DIR__ . '/../../View/Buyer/farmer-store.php';
