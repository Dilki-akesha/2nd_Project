<?php
/**
 * Shared controller for the Farmer Add Product / Edit Product forms.
 * Does not render the layout - the view does that after this file is required.
 */
require_once __DIR__ . '/../../View/Farmer/includes/auth.php';
require_once __DIR__ . '/../../Model/Farmer/FarmerProductModel.php';

$productModel = new FarmerProductModel();
$id = (int)($_GET['id'] ?? 0);
$product = $id ? $productModel->find($id, $farmer_id) : [];

if ($id && !$product) {
    http_response_code(404);
    flash('error', 'Product not found.');
    farmer_redirect('products.php');
}

$categories = $productModel->categories();
$shelfReferences = $farmerModel->shelfLifeReferences();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    try {
        $saved = $productModel->save($farmer_id, $_POST, $id ?: null);

        if (!empty($_FILES['image']['name'])) {
            $productModel->addImage($saved, $_FILES['image'], true);
        }

        // Remove any images the Farmer ticked for deletion.
        foreach (($_POST['delete_images'] ?? []) as $imageId) {
            $productModel->deleteImage((int)$imageId, $farmer_id);
        }

        flash('success', 'Product saved successfully.');
        farmer_redirect('products.php');
    } catch (Throwable $error) {
        flash('error', $error->getMessage());
        $product = array_merge($product, $_POST);
        $_SESSION['_farmer_form_error'] = true;
    }
}

$existingImages = $id ? $productModel->images($id) : [];
