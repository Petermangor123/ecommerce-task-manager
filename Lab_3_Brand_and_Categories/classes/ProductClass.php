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
    // Search products by title, description, or keywords using a prepared statement.
    // Returns an array of matching product rows (empty array if none found).
    public function searchProducts($query)
    {
        $stmt = $this->conn->prepare(
            'SELECT p.product_id, p.product_title, p.product_price,
                    p.product_desc, p.product_image,
                    b.brand_name, c.cat_name
             FROM products p
             LEFT JOIN brands     b ON p.product_brand = b.brand_id
             LEFT JOIN categories c ON p.product_cat   = c.cat_id
             WHERE p.product_title    LIKE ?
                OR p.product_desc     LIKE ?
                OR p.product_keywords LIKE ?
             ORDER BY p.product_title ASC'
        );
        // Bind the same wildcard term to all three placeholders
        $term = '%' . $query . '%';
        $stmt->bind_param('sss', $term, $term, $term);
        $stmt->execute();
        $result   = $stmt->get_result();
        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        $stmt->close();
        return $products;
    }
}
