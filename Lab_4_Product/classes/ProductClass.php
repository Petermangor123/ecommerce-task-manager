<?php
// ProductClass handles all database operations related to products, brands, and categories
require_once __DIR__ . '/../core/db_class.php';
class ProductClass extends Database
{
    // Insert a new brand and return true on success, false on failure
    public function addBrand($name)
    {
        $stmt = $this->conn->prepare(
            'INSERT INTO brands (brand_name) VALUES (?)'
        );
        $stmt->bind_param('s', $name);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }
    // Return all brands ordered alphabetically
    public function getAllBrands()
    {
        $result = $this->conn->query(
            'SELECT * FROM brands ORDER BY brand_name ASC'
        );
        if (!$result) {
            return [];
        }
        $brands = [];
        while ($row = $result->fetch_assoc()) {
            $brands[] = $row;
        }
        return $brands;
    }
    // Fetch a single brand row by its ID; returns the row array or false
    public function getBrandById($id)
    {
        $stmt = $this->conn->prepare(
            'SELECT * FROM brands WHERE brand_id = ?'
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row    = $result->fetch_assoc();
        $stmt->close();
        return $row ?: false;
    }
    // Update a brand's name by ID; returns true on success, false on failure
    public function updateBrand($id, $name)
    {
        $stmt = $this->conn->prepare(
            'UPDATE brands SET brand_name = ? WHERE brand_id = ?'
        );
        $stmt->bind_param('si', $name, $id);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }
    // Insert a new category and return true on success, false on failure
    public function addCategory($name)
    {
        $stmt = $this->conn->prepare(
            'INSERT INTO categories (cat_name) VALUES (?)'
        );
        $stmt->bind_param('s', $name);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }
    // Return all categories ordered alphabetically
    public function getAllCategories()
    {
        $result = $this->conn->query(
            'SELECT * FROM categories ORDER BY cat_name ASC'
        );
        if (!$result) {
            return [];
        }
        $categories = [];
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
        return $categories;
    }
    // Fetch a single category row by its ID; returns the row array or false
    public function getCategoryById($id)
    {
        $stmt = $this->conn->prepare(
            'SELECT * FROM categories WHERE cat_id = ?'
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row    = $result->fetch_assoc();
        $stmt->close();
        return $row ?: false;
    }
    // Update a category's name by ID; returns true on success, false on failure
    public function updateCategory($id, $name)
    {
        $stmt = $this->conn->prepare(
            'UPDATE categories SET cat_name = ? WHERE cat_id = ?'
        );
        $stmt->bind_param('si', $name, $id);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }
    // Return the total number of registered customers
    public function getCustomerCount()
    {
        $result = $this->conn->query('SELECT COUNT(*) AS total FROM customer');
        if (!$result) {
            return 0;
        }
        $row = $result->fetch_assoc();
        return (int)$row['total'];
    }
    // Return all products with category name and brand name, ordered by title
    public function getAllProducts()
    {
        $result = $this->conn->query(
            'SELECT p.*, c.cat_name, b.brand_name
             FROM products p
             JOIN categories c ON p.product_cat = c.cat_id
             JOIN brands b ON p.product_brand = b.brand_id
             ORDER BY p.product_title ASC'
        );
        if (!$result) {
            return [];
        }
        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        return $products;
    }
    // Fetch a single product row by ID with category and brand names; returns the row or false
    public function getProductById($id)
    {
        $stmt = $this->conn->prepare(
            'SELECT p.*, c.cat_name, b.brand_name
             FROM products p
             JOIN categories c ON p.product_cat = c.cat_id
             JOIN brands b ON p.product_brand = b.brand_id
             WHERE p.product_id = ?'
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row    = $result->fetch_assoc();
        $stmt->close();
        return $row ?: false;
    }
    // Insert a new product; returns true on success, false on failure
    public function addProduct($cat, $brand, $title, $price, $desc, $image_filename, $keywords)
    {
        $stmt = $this->conn->prepare(
            'INSERT INTO products
             (product_cat, product_brand, product_title, product_price,
              product_desc, product_image, product_keywords)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->bind_param('iisdsss', $cat, $brand, $title, $price, $desc, $image_filename, $keywords);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }
    // Update a product by ID; returns true on success, false on failure
    public function updateProduct($id, $cat, $brand, $title, $price, $desc, $image_filename, $keywords)
    {
        $stmt = $this->conn->prepare(
            'UPDATE products
             SET product_cat = ?, product_brand = ?, product_title = ?,
                 product_price = ?, product_desc = ?, product_image = ?,
                 product_keywords = ?
             WHERE product_id = ?'
        );
        $stmt->bind_param('iisdsssi', $cat, $brand, $title, $price, $desc, $image_filename, $keywords, $id);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }
    // Return a random selection of products for the homepage
    public function getFeaturedProducts($limit = 6)
    {
        $limit  = max(1, (int)$limit);
        $sql    = 'SELECT p.product_id, p.product_title, p.product_price, p.product_image,
                          c.cat_name, b.brand_name
                   FROM products p
                   JOIN categories c ON p.product_cat = c.cat_id
                   JOIN brands b ON p.product_brand = b.brand_id
                   ORDER BY RAND()
                   LIMIT ' . $limit;
        $result = $this->conn->query($sql);
        if (!$result) {
            return [];
        }
        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
    }
    // Return all products in a given category
    public function getProductsByCategory($cat_id)
    {
        $stmt = $this->conn->prepare(
            'SELECT p.product_id, p.product_title, p.product_price, p.product_image,
                    c.cat_name, b.brand_name
             FROM products p
             JOIN categories c ON p.product_cat = c.cat_id
             JOIN brands b ON p.product_brand = b.brand_id
             WHERE p.product_cat = ?
             ORDER BY p.product_title ASC'
        );
        $stmt->bind_param('i', $cat_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $rows   = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        $stmt->close();
        return $rows;
    }
    // Return all products from a given brand
    public function getProductsByBrand($brand_id)
    {
        $stmt = $this->conn->prepare(
            'SELECT p.product_id, p.product_title, p.product_price, p.product_image,
                    c.cat_name, b.brand_name
             FROM products p
             JOIN categories c ON p.product_cat = c.cat_id
             JOIN brands b ON p.product_brand = b.brand_id
             WHERE p.product_brand = ?
             ORDER BY p.product_title ASC'
        );
        $stmt->bind_param('i', $brand_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $rows   = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        $stmt->close();
        return $rows;
    }
    // Search products by title or keywords
    public function searchProducts($query)
    {
        $pattern = '%' . $query . '%';
        $stmt    = $this->conn->prepare(
            'SELECT p.product_id, p.product_title, p.product_price, p.product_image,
                    c.cat_name, b.brand_name
             FROM products p
             JOIN categories c ON p.product_cat = c.cat_id
             JOIN brands b ON p.product_brand = b.brand_id
             WHERE p.product_title LIKE ? OR p.product_keywords LIKE ?
             ORDER BY p.product_title ASC'
        );
        $stmt->bind_param('ss', $pattern, $pattern);
        $stmt->execute();
        $result = $stmt->get_result();
        $rows   = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        $stmt->close();
        return $rows;
    }
}
