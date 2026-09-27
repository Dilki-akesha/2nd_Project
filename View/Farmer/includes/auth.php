<?php
/**
 * Harvestly Farmer module bootstrap.
 * Every Farmer page includes this first. It enforces the Farmer role,
 * verifies CSRF on POST, and loads the authenticated Farmer record.
 */
require_once __DIR__ . '/../../../config/app.php';
require_once __DIR__ . '/../../../Model/Farmer/FarmerModel.php';

requireFarmerAuth();
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') verifyCsrfToken();

$farmer_id = currentFarmerId();
$farmerModel = new FarmerModel();
$farmer_user = $farmerModel->user($farmer_id);
$conn = db();

if (!function_exists('farmer_redirect')) {
    function farmer_redirect(string $path): void
    {
        redirect('View/Farmer/' . ltrim($path, '/'));
    }
}

/** Store a one-request flash message for the Farmer module. */
if (!function_exists('flash')) {
    function flash(string $type, string $message): void
    {
        $_SESSION['_farmer_flash'] = ['type' => $type, 'message' => $message];
    }
}
