<?php
// CartClass handles cart rows keyed by visitor IP (ip_add)
require_once __DIR__ . '/../core/db_class.php';

class CartClass extends Database
{
    // Return true if this product is already in the cart for the given IP
    public function isInCart($product_id, $ip)
    {
        $product_id = (int)$product_id;
        $ip         = (string)$ip;
        $stmt       = $this->conn->prepare(
            'SELECT p_id FROM cart WHERE p_id = ? AND ip_add = ? LIMIT 1'
        );
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param('is', $product_id, $ip);
        $stmt->execute();
        $result = $stmt->get_result();
        $row    = $result ? $result->fetch_assoc() : null;
        $stmt->close();
        return (bool)$row;
    }

    // Insert a cart row. Guest carts leave c_id NULL; logged-in users may store c_id.
    public function addToCart($product_id, $qty, $ip, $customer_id = null)
    {
        $product_id = (int)$product_id;
        $qty        = (int)$qty;
        $ip         = (string)$ip;

        if ($product_id < 1 || $qty < 1 || $ip === '') {
            return false;
        }

        if ($customer_id !== null) {
            $customer_id = (int)$customer_id;
            if ($customer_id < 1) {
                $customer_id = null;
            }
        }

        if ($customer_id !== null) {
            $stmt = $this->conn->prepare(
                'INSERT INTO cart (p_id, ip_add, c_id, qty) VALUES (?, ?, ?, ?)'
            );
            if (!$stmt) {
                return false;
            }
            $stmt->bind_param('isii', $product_id, $ip, $customer_id, $qty);
        } else {
            $stmt = $this->conn->prepare(
                'INSERT INTO cart (p_id, ip_add, qty) VALUES (?, ?, ?)'
            );
            if (!$stmt) {
                return false;
            }
            $stmt->bind_param('isi', $product_id, $ip, $qty);
        }

        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

    // Return cart rows joined with product details for this IP
    public function getCartItems($ip)
    {
        $ip   = (string)$ip;
        $stmt = $this->conn->prepare(
            'SELECT cart.*, products.product_title, products.product_price, products.product_image
             FROM cart
             JOIN products ON cart.p_id = products.product_id
             WHERE cart.ip_add = ?'
        );
        if (!$stmt) {
            return [];
        }
        $stmt->bind_param('s', $ip);
        $stmt->execute();
        $result = $stmt->get_result();
        $rows   = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
        }
        $stmt->close();
        return $rows;
    }

    // Count cart rows (items), not the sum of quantities
    public function getCartCount($ip)
    {
        $ip   = (string)$ip;
        $stmt = $this->conn->prepare(
            'SELECT COUNT(*) AS total FROM cart WHERE ip_add = ?'
        );
        if (!$stmt) {
            return 0;
        }
        $stmt->bind_param('s', $ip);
        $stmt->execute();
        $result = $stmt->get_result();
        $row    = $result ? $result->fetch_assoc() : null;
        $stmt->close();
        return $row ? (int)$row['total'] : 0;
    }

    // SUM(qty * product_price) for this IP
    public function getCartTotal($ip)
    {
        $total = 0.0;
        foreach ($this->getCartItems($ip) as $item) {
            $total += ((int)$item['qty']) * ((float)$item['product_price']);
        }
        return $total;
    }

    // Update quantity for a cart row that belongs to this IP
    public function updateQuantity($product_id, $qty, $ip)
    {
        $product_id = (int)$product_id;
        $qty        = (int)$qty;
        $ip         = (string)$ip;
        if ($product_id < 1 || $qty < 1 || $ip === '') {
            return false;
        }
        $stmt = $this->conn->prepare(
            'UPDATE cart SET qty = ? WHERE p_id = ? AND ip_add = ?'
        );
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param('iis', $qty, $product_id, $ip);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

    // Delete a cart row that belongs to this IP
    public function removeFromCart($product_id, $ip)
    {
        $product_id = (int)$product_id;
        $ip         = (string)$ip;
        if ($product_id < 1 || $ip === '') {
            return false;
        }
        $stmt = $this->conn->prepare(
            'DELETE FROM cart WHERE p_id = ? AND ip_add = ?'
        );
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param('is', $product_id, $ip);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

    // Task 12 alias: update quantity for a cart row belonging to this IP.
    // Validates that product_id >= 1 and new_qty >= 1 before touching the DB.
    public function updateQty($product_id, $new_qty, $ip)
    {
        $product_id = (int)$product_id;
        $new_qty    = (int)$new_qty;
        $ip         = (string)$ip;
        if ($product_id < 1 || $new_qty < 1 || $ip === '') {
            return false;
        }
        $stmt = $this->conn->prepare(
            'UPDATE cart SET qty = ? WHERE p_id = ? AND ip_add = ?'
        );
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param('iis', $new_qty, $product_id, $ip);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }
}
