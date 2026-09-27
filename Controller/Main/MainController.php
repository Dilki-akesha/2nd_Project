<?php
/**
 * MainController - renders the public Harvestly landing and catalogue pages.
 * Public pages never require authentication.
 */

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../Model/Main/ProductModel.php';

class MainController
{
    private ProductModel $productModel;

    public function __construct()
    {
        $this->productModel = new ProductModel();
    }

    public function render($page)
    {
        switch ($page) {
            case 'products':
                $search = trim((string)($_GET['search'] ?? ''));
                $catFilter = trim((string)($_GET['category'] ?? 'all'));
                $categoryOptions = $this->productModel->getActiveCategories();
                if ($catFilter !== 'all' && $catFilter !== '' && !in_array($catFilter, $categoryOptions, true)) {
                    $catFilter = 'all';
                }
                $products = $this->productModel->getActiveProducts($catFilter, $search);
                require __DIR__ . '/../../View/Main/products.php';
                break;

            case 'product_details':
                $id = (int)($_GET['id'] ?? 0);
                $product = $id > 0 ? $this->productModel->getProductById($id) : null;
                if (!$product) {
                    http_response_code(404);
                }
                require __DIR__ . '/../../View/Main/product-details.php';
                break;

            case 'landing':
            default:
                $categories = $this->productModel->getCategorySummary();
                $featuredProducts = $this->productModel->getFeaturedProducts(3);
                require __DIR__ . '/../../View/Main/landing.php';
                break;
        }
    }
}
