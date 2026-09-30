<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../Model/Buyer/Product.php';
require_once __DIR__ . '/../../Model/Buyer/Cart.php';

requireBuyerAuth();
$productModel = new Product();
$cartModel = new Cart();

/* Add to Cart. */
if (($_POST['action'] ?? '') === 'add_to_cart') {
    verifyCsrfToken();
    $id = (int)($_POST['id'] ?? 0);
    $quantity = filter_var($_POST['qty'] ?? 1, FILTER_VALIDATE_FLOAT);
    if ($id <= 0 || $quantity === false || !is_finite((float)$quantity) || (float)$quantity <= 0 || (float)$quantity > 100000) {
        $_SESSION['_buyer_flash'] = ['success' => false, 'message' => 'Please select a valid product and quantity.'];
        redirect('Controller/Buyer/ProductController.php');
    }

    if (!$productModel->getProductById($id)) {
        $_SESSION['_buyer_flash'] = ['success' => false, 'message' => 'This product is no longer available.'];
        redirect('Controller/Buyer/ProductController.php');
    }

    if ($cartModel->add($id, $quantity)) {
        $_SESSION['_buyer_flash'] = ['success' => true, 'message' => 'Added to your cart.'];
    } else {
        $_SESSION['_buyer_flash'] = ['success' => false, 'message' => 'The requested quantity is not available.'];
    }
    redirect('Controller/Buyer/CartController.php');
}

$search = trim((string)($_GET['search'] ?? ''));
$district = trim((string)($_GET['district'] ?? ''));
$maxPriceRaw = $_GET['maxPrice'] ?? '';
$maxPrice = $maxPriceRaw !== '' && is_numeric($maxPriceRaw) && is_finite((float)$maxPriceRaw) ? max(0, (float)$maxPriceRaw) : PHP_FLOAT_MAX;
$sort = trim((string)($_GET['sort'] ?? 'Newest'));
$allowedSorts = ['Newest', 'Price: Low to High', 'Price: High to Low', 'Best Rated', 'Popular'];
if (!in_array($sort, $allowedSorts, true)) $sort = 'Newest';
$listingType = trim((string)($_GET['listingType'] ?? 'All Listing Types'));
$growingMethod = trim((string)($_GET['growingMethod'] ?? 'All Growing Methods'));

/* All available listing types, taken from the database enum via the products table. */
$listingTypes = ['All Listing Types', 'Available Now', 'Harvest Soon', 'Seasonal'];
$growingMethods = ['All Growing Methods', 'Organic', 'Conventional', 'Mixed'];

$products = array_values(array_filter(
    $productModel->getAllProducts(),
    function (array $product) use ($search, $district, $maxPrice, $listingType, $growingMethod): bool {
        if ($search !== '' && stripos($product['name'] . ' ' . $product['farmer'] . ' ' . (string)($product['description'] ?? ''), $search) === false) {
            return false;
        }
        if ($district !== '' && strcasecmp((string)($product['district'] ?? ''), $district) !== 0) {
            return false;
        }
        if ((float)$product['price'] > $maxPrice) {
            return false;
        }
        if ($listingType !== 'All Listing Types') {
            $type = harvestlyStatusLabel((string)$product['listing_type']);
            if ($type !== $listingType) return false;
        }
        if ($growingMethod !== 'All Growing Methods') {
            $method = ucfirst(strtolower(str_replace('_', ' ', (string)($product['growing_method'] ?? ''))));
            if ($method !== $growingMethod) return false;
        }
        return true;
    }
));

usort($products, match ($sort) {
    'Price: Low to High' => fn(array $a, array $b) => $a['price'] <=> $b['price'],
    'Price: High to Low' => fn(array $a, array $b) => $b['price'] <=> $a['price'],
    'Best Rated' => fn(array $a, array $b) => $b['rating'] <=> $a['rating'] || $a['name'] <=> $b['name'],
    'Popular' => fn(array $a, array $b) => $b['reviews'] <=> $a['reviews'] || $a['name'] <=> $b['name'],
    default => fn(array $a, array $b) => 0,
});

$districts = db_fetch_all(
    "SELECT district_id, district_name FROM districts WHERE is_active = 1 ORDER BY district_name"
);

require __DIR__ . '/../../View/Buyer/browse-products.php';
