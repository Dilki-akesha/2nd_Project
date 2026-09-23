<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/app.php';

$farmer = trim((string)($_GET['farmer'] ?? ''));

if ($farmer === '') {
    redirect('Controller/Buyer/ProductController.php');
}

$db = db();

$stmt = $db->prepare(
    'SELECT name, email, phone, district, city, address, profile_image
     FROM users
     WHERE name = ?
     LIMIT 1'
);
$stmt->execute([$farmer]);
$contact = $stmt->fetch() ?: null;

require __DIR__ . '/../../View/Buyer/farmer-contact.php';
