<?php
// CartController coordinates cart operations between actions/views and CartClass
require_once __DIR__ . '/../classes/CartClass.php';

class CartController
{
    private $cartModel;

    public function __construct()
    {
        $this->cartModel = new CartClass();
    }

    public function isInCart($product_id, $ip)
    {
        return $this->cartModel->isInCart($product_id, $ip);
    }

    public function addToCart($product_id, $qty, $ip, $customer_id = null)
    {
        return $this->cartModel->addToCart($product_id, $qty, $ip, $customer_id);
    }

    public function getCartItems($ip)
    {
        return $this->cartModel->getCartItems($ip);
    }

    public function getCartCount($ip)
    {
        return $this->cartModel->getCartCount($ip);
    }

    public function getCartTotal($ip)
    {
        return $this->cartModel->getCartTotal($ip);
    }

    public function updateQuantity($product_id, $qty, $ip)
    {
        return $this->cartModel->updateQuantity($product_id, $qty, $ip);
    }

    public function removeFromCart($product_id, $ip)
    {
        return $this->cartModel->removeFromCart($product_id, $ip);
    }

    // Task 12: updateQty delegates to the new CartClass method
    public function updateQty($product_id, $new_qty, $ip)
    {
        return $this->cartModel->updateQty($product_id, $new_qty, $ip);
    }

    // task 13

    // Wraps the full payment → order-creation transaction.
    // Returns new order_id on success, false on failure.
    public function processPayment($customer_id, $ip, $reference, $amount)
    {
        return $this->cartModel->processPayment($customer_id, $ip, $reference, $amount);
    }

    // Fetches a complete order (header + items) for invoice display.
    // Returns ['order' => [...], 'items' => [...]] or false.
    public function getOrderById($order_id)
    {
        return $this->cartModel->getOrderById($order_id);
    }
}
