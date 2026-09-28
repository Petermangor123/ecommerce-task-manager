<?php
// CustomerClass handles all database operations related to customers
require_once '../core/db_class.php';
class CustomerClass extends Database
{
    // Check whether an email address is already registered
    public function emailExists($email)
    {
        $stmt = $this->conn->prepare(
            'SELECT customer_email FROM customer WHERE customer_email = ?'
        );
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $exists = $result->num_rows > 0;
        $stmt->close();
        return $exists;
    }
    // Insert a new customer row and return the full row on success
    public function addCustomer($name, $email, $pass, $country, $city, $contact)
    {
        // Hash the password before storing it
        $hash = password_hash($pass, PASSWORD_BCRYPT);
        $stmt = $this->conn->prepare(
            'INSERT INTO customer
                (customer_name, customer_email, customer_pass,
                 customer_country, customer_city, customer_contact)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->bind_param('ssssss', $name, $email, $hash, $country, $city, $contact);
        $success = $stmt->execute();
        $new_id  = $this->conn->insert_id; // ID of the inserted row
        $stmt->close();
        if (!$success) {
            return false;
        }
        // Fetch and return the newly created row so the caller gets customer_id and user_role
        $row = $this->getCustomerByEmail($email);
        return $row ?: false;
    }
    // Fetch a single customer row by email address
    public function getCustomerByEmail($email)
    {
        $stmt = $this->conn->prepare(
            'SELECT * FROM customer WHERE customer_email = ?'
        );
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $row    = $result->fetch_assoc();
        $stmt->close();
        return $row ?: false;
    }
    // Verify the customer's credentials and return the row on success
    public function login($email, $pass)
    {
        $row = $this->getCustomerByEmail($email);
        // Check the submitted password against the stored bcrypt hash
        if ($row && password_verify($pass, $row['customer_pass'])) {
            return $row;
        }
        return false;
    }
}
