<?php
// CustomerController coordinates registration and login between the action and the model
require_once __DIR__ . '/../classes/CustomerClass.php';
class CustomerController
{
    private $customerModel;
    public function __construct()
    {
        $this->customerModel = new CustomerClass();
    }
    // Handle customer registration
    public function register($data)
    {
        // Reject the request if the email is already in use
        if ($this->customerModel->emailExists($data['email'])) {
            return ['success' => false, 'error' => 'Email already registered'];
        }
        $customer = $this->customerModel->addCustomer(
            $data['name'],
            $data['email'],
            $data['pass'],
            $data['country'],
            $data['city'],
            $data['contact']
        );
        if ($customer) {
            // Return the new customer's key fields so the action can set the session
            return [
                'success'        => true,
                'customer_id'    => $customer['customer_id'],
                'customer_name'  => $customer['customer_name'],
                'customer_email' => $customer['customer_email'],
                'user_role'      => $customer['user_role'],
            ];
        }
        return ['success' => false, 'error' => 'Registration failed'];
    }
    // Handle customer login
    public function login($email, $pass)
    {
        $customer = $this->customerModel->login($email, $pass);
        if ($customer) {
            // Pass the full customer row back so the action can populate the session
            return ['success' => true, 'customer' => $customer];
        }
        return ['success' => false, 'error' => 'Invalid email or password.'];
    }
}
