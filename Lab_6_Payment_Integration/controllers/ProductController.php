<?php
// ProductController coordinates brand and product operations between actions/views and the model
require_once __DIR__ . '/../classes/ProductClass.php';
class ProductController
{
    private $productModel;
    public function __construct()
    {
        $this->productModel = new ProductClass();
    }
    // Add a new brand
    public function addBrand($name)
    {
        return $this->productModel->addBrand($name);
    }
    // Return all brands ordered alphabetically
    public function getAllBrands()
    {
        return $this->productModel->getAllBrands();
    }
    // Fetch a single brand row by ID; returns the row or false
    public function getBrandById($id)
    {
        return $this->productModel->getBrandById($id);
    }
    // Update a brand's name by ID; returns true on success, false on failure
    public function updateBrand($id, $name)
    {
        return $this->productModel->updateBrand($id, $name);
    }
    // Add a new category
    public function addCategory($name)
    {
        return $this->productModel->addCategory($name);
    }
    // Return all categories ordered alphabetically
    public function getAllCategories()
    {
        return $this->productModel->getAllCategories();
    }
    // Fetch a single category row by ID; returns the row or false
    public function getCategoryById($id)
    {
        return $this->productModel->getCategoryById($id);
    }
    // Update a category's name by ID; returns true on success, false on failure
    public function updateCategory($id, $name)
    {
        return $this->productModel->updateCategory($id, $name);
    }
    // Return the total number of registered customers
    public function getCustomerCount()
    {
        return $this->productModel->getCustomerCount();
    }
    // Return all products with category and brand names
    public function getAllProducts()
    {
        return $this->productModel->getAllProducts();
    }
    // Fetch a single product row by ID with category/brand names; returns the row or false
    public function getProductById($id)
    {
        return $this->productModel->getProductById($id);
    }
    // Add a new product; returns true on success, false on failure
    public function addProduct($cat, $brand, $title, $price, $desc, $image_filename, $keywords)
    {
        return $this->productModel->addProduct($cat, $brand, $title, $price, $desc, $image_filename, $keywords);
    }
    // Update a product by ID; returns true on success, false on failure
    public function updateProduct($id, $cat, $brand, $title, $price, $desc, $image_filename, $keywords)
    {
        return $this->productModel->updateProduct($id, $cat, $brand, $title, $price, $desc, $image_filename, $keywords);
    }
    // Return a random selection of products for the homepage
    public function getFeaturedProducts($limit = 6)
    {
        return $this->productModel->getFeaturedProducts($limit);
    }
    // Return all products in a given category
    public function getProductsByCategory($cat_id)
    {
        return $this->productModel->getProductsByCategory($cat_id);
    }
    // Return all products from a given brand
    public function getProductsByBrand($brand_id)
    {
        return $this->productModel->getProductsByBrand($brand_id);
    }
    // Search products by title or keywords
    public function searchProducts($query)
    {
        return $this->productModel->searchProducts($query);
    }
}
