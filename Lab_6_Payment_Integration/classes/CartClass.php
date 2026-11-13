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

    // task 13: save the order after paystack says the payment went through
    // Wraps the entire order-creation workflow in a single DB transaction.
    // On success returns the new order_id; on any failure rolls back and
    // returns false.  The paystack_ref UNIQUE constraint prevents the same
    // Paystack reference from ever creating two orders.
    //
    // Parameters:
    //   $customer_id  — logged-in customer (from $_SESSION['customer_id'])
    //   $ip           — visitor IP (from get_ip()) used to look up cart rows
    //   $reference    — Paystack transaction reference (already server-verified)
    //   $amount       — verified amount in GHS (float, e.g. 450.00)
    public function processPayment($customer_id, $ip, $reference, $amount)
    {
        $customer_id = (int)$customer_id;
        $ip          = (string)$ip;
        $reference   = (string)$reference;
        $amount      = (float)$amount;

        if ($customer_id < 1 || $ip === '' || $reference === '' || $amount <= 0) {
            return false;
        }

        // --- Idempotency: reject duplicate Paystack references immediately ---
        $check = $this->conn->prepare(
            'SELECT pay_id FROM payment WHERE paystack_ref = ? LIMIT 1'
        );
        if (!$check) {
            return false;
        }
        $check->bind_param('s', $reference);
        $check->execute();
        $check->store_result();
        if ($check->num_rows > 0) {
            $check->close();
            return false; // already processed
        }
        $check->close();

        // --- Fetch current cart items for this IP ---
        $items = $this->getCartItems($ip);
        if (empty($items)) {
            return false; // nothing to order
        }

        // --- Begin transaction ---
        $this->conn->begin_transaction();

        try {
            // 1. Insert into orders
            $invoice_no  = mt_rand(100000, 999999);
            $order_date  = date('Y-m-d');
            $order_status = 'paid';

            $stmt_order = $this->conn->prepare(
                'INSERT INTO orders (customer_id, invoice_no, order_date, order_status)
                 VALUES (?, ?, ?, ?)'
            );
            if (!$stmt_order) {
                throw new Exception('prepare orders failed: ' . $this->conn->error);
            }
            $stmt_order->bind_param('iiss', $customer_id, $invoice_no, $order_date, $order_status);
            if (!$stmt_order->execute()) {
                throw new Exception('execute orders failed: ' . $stmt_order->error);
            }
            $order_id = (int)$this->conn->insert_id;
            $stmt_order->close();

            // 2. Insert order details — one row per cart item
            $stmt_detail = $this->conn->prepare(
                'INSERT INTO orderdetails (order_id, product_id, qty, unit_price)
                 VALUES (?, ?, ?, ?)'
            );
            if (!$stmt_detail) {
                throw new Exception('prepare orderdetails failed: ' . $this->conn->error);
            }
            foreach ($items as $item) {
                $p_id       = (int)$item['p_id'];
                $qty        = (int)$item['qty'];
                $unit_price = (float)$item['product_price'];
                $stmt_detail->bind_param('iiid', $order_id, $p_id, $qty, $unit_price);
                if (!$stmt_detail->execute()) {
                    throw new Exception('execute orderdetails failed: ' . $stmt_detail->error);
                }
            }
            $stmt_detail->close();

            // 3. Insert into payment table
            $currency     = 'GHS';
            $payment_date = date('Y-m-d');
            $stmt_pay = $this->conn->prepare(
                'INSERT INTO payment (amt, customer_id, order_id, currency, payment_date, paystack_ref)
                 VALUES (?, ?, ?, ?, ?, ?)'
            );
            if (!$stmt_pay) {
                throw new Exception('prepare payment failed: ' . $this->conn->error);
            }
            $stmt_pay->bind_param('diisss', $amount, $customer_id, $order_id, $currency, $payment_date, $reference);
            if (!$stmt_pay->execute()) {
                throw new Exception('execute payment failed: ' . $stmt_pay->error);
            }
            $stmt_pay->close();

            // 4. Clear the cart for this IP
            $stmt_clear = $this->conn->prepare(
                'DELETE FROM cart WHERE ip_add = ?'
            );
            if (!$stmt_clear) {
                throw new Exception('prepare cart delete failed: ' . $this->conn->error);
            }
            $stmt_clear->bind_param('s', $ip);
            if (!$stmt_clear->execute()) {
                throw new Exception('execute cart delete failed: ' . $stmt_clear->error);
            }
            $stmt_clear->close();

            // 5. All good — commit
            $this->conn->commit();
            return $order_id;

        } catch (Exception $e) {
            $this->conn->rollback();
            error_log('[processPayment] ' . $e->getMessage());
            return false;
        }
    }

    // task 13: load one order for the invoice
    // Returns a single array with 'order' (header info + customer details)
    // and 'items' (array of product rows with quantities, unit prices, subtotals).
    // Returns false if the order does not exist.
    public function getOrderById($order_id)
    {
        $order_id = (int)$order_id;
        if ($order_id < 1) {
            return false;
        }

        // Order header + customer info
        $stmt_order = $this->conn->prepare(
            'SELECT o.order_id, o.invoice_no, o.order_date, o.order_status,
                    c.customer_id, c.customer_name, c.customer_email,
                    c.customer_country, c.customer_city,
                    p.amt, p.currency, p.paystack_ref, p.payment_date
             FROM orders o
             JOIN customer c  ON o.customer_id  = c.customer_id
             LEFT JOIN payment p ON p.order_id  = o.order_id
             WHERE o.order_id = ?
             LIMIT 1'
        );
        if (!$stmt_order) {
            return false;
        }
        $stmt_order->bind_param('i', $order_id);
        $stmt_order->execute();
        $result_order = $stmt_order->get_result();
        $order        = $result_order ? $result_order->fetch_assoc() : null;
        $stmt_order->close();

        if (!$order) {
            return false;
        }

        // Order line items
        $stmt_items = $this->conn->prepare(
            'SELECT od.product_id, od.qty, od.unit_price,
                    pr.product_title, pr.product_image,
                    (od.qty * od.unit_price) AS line_total
             FROM orderdetails od
             JOIN products pr ON od.product_id = pr.product_id
             WHERE od.order_id = ?
             ORDER BY pr.product_title ASC'
        );
        if (!$stmt_items) {
            return false;
        }
        $stmt_items->bind_param('i', $order_id);
        $stmt_items->execute();
        $result_items = $stmt_items->get_result();
        $items = [];
        if ($result_items) {
            while ($row = $result_items->fetch_assoc()) {
                $items[] = $row;
            }
        }
        $stmt_items->close();

        return [
            'order' => $order,
            'items' => $items,
        ];
    }
}
