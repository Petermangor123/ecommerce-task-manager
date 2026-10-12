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
    // Search products matching the given query string; returns an array of rows
    public function searchProducts($query)
    {
        return $this->productModel->searchProducts($query);
    }
}
