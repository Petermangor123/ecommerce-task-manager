<?php
// Base Database class — opens a mysqli connection that model classes inherit
class Database {
    protected $conn;
    public function __construct() {
        require_once __DIR__ . '/db_cred.php';
        // Create the connection using the defined credentials
        $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        // Stop execution if the connection fails
        if ($this->conn->connect_error) {
            error_log($this->conn->connect_error);
            die('Connection failed.');
        }
    }
}
