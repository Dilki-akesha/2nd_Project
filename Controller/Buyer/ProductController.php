<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../Model/Buyer/Product.php';
require_once __DIR__ . '/../../Model/Buyer/Cart.php';

$productModel = new Product();
$cartModel = new Cart();

/*
 * Product CRUD endpoints.
 * These endpoints return JSON so they can be used by the Buyer UI
 * or connected to an admin/farmer screen later.
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = post_string('action');

    if (in_array($action, ['create', 'update', 'delete'], true)) {
        header('Content-Type: application/json; charset=utf-8');

        try {
            if ($action === 'create') {
                $id = $productModel->create($_POST);
                echo json_encode([
                    'success' => true,
                    'message' => 'Product created successfully.',
                    'product' => $productModel->getProductById($id),
                ]);
                exit;
            }

            $id = (int)($_POST['id'] ?? 0);

            if ($action === 'update') {
                $success = $productModel->update($id, $_POST);
                echo json_encode([
                    'success' => $success,
                    'message' => $success ? 'Product updated successfully.' : 'Product could not be updated.',
                    'product' => $success ? $productModel->getProductById($id) : null,
                ]);
                exit;
            }

            $success = $productModel->delete($id);
            echo json_encode([
                'success' => $success,
                'message' => $success
                    ? 'Product deleted successfully.'
                    : 'Product cannot be deleted because it is being used by another record.',
            ]);
            exit;
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'The product operation could not be completed.',
            ]);
            exit;
        }
    }
}

if (($_GET['action'] ?? '') === 'add_to_cart') {
    $id = (int)($_GET['id'] ?? 0);
    $quantity = max(1, (int)($_GET['qty'] ?? 1));

    if (!$productModel->getProductById($id)) {
        redirect('Controller/Buyer/ProductController.php');
    }

    $cartModel->add($id, $quantity);
    redirect('Controller/Buyer/CartController.php');
}

if (($_GET['action'] ?? '') === 'details') {
    $id = (int)($_GET['id'] ?? 0);
    $product = $productModel->getProductById($id);

    if (!$product) {
        http_response_code(404);
        echo 'Product not found';
        exit;
    }

    $images = $product['images'] ?? [$product['image']];
    $farmerImage = $product['farmerImage'] ?? '';

    require __DIR__ . '/../../View/Buyer/product-details.php';
    exit;
}

$search = trim((string)($_GET['search'] ?? ''));
$district = trim((string)($_GET['district'] ?? 'All Districts'));
$maxPrice = (float)($_GET['maxPrice'] ?? 2000);
$organic = isset($_GET['organic']);
$fresh = isset($_GET['fresh']);
$stock = isset($_GET['stock']);
$sort = trim((string)($_GET['sort'] ?? 'Newest'));

$products = $productModel->getAllProducts();

$products = array_values(array_filter(
    $products,
    function (array $product) use ($search, $district, $maxPrice, $organic, $fresh, $stock): bool {
        $searchText = $product['name'] . ' ' . $product['farmer'];

        if ($search !== '' && stripos($searchText, $search) === false) {
            return false;
        }

        if ($district !== '' && $district !== 'All Districts' && stripos($product['farmer'], $district) === false) {
            return false;
        }

        if ($product['price'] > $maxPrice) {
            return false;
        }

        if ($organic && !$product['organic']) {
            return false;
        }

        if ($fresh && !$product['fresh']) {
            return false;
        }

        if ($stock && $product['stock'] <= 0) {
            return false;
        }

        return true;
    }
));

switch ($sort) {
    case 'Price: Low to High':
        usort($products, fn(array $a, array $b) => $a['price'] <=> $b['price']);
        break;

    case 'Price: High to Low':
        usort($products, fn(array $a, array $b) => $b['price'] <=> $a['price']);
        break;

    case 'Best Rated':
        usort($products, fn(array $a, array $b) => $b['rating'] <=> $a['rating']);
        break;

    case 'Popular':
        usort($products, fn(array $a, array $b) => $b['reviews'] <=> $a['reviews']);
        break;
}

$added = isset($_GET['added']) && $_GET['added'] === '1';

require __DIR__ . '/../../View/Buyer/browse-products.php';
