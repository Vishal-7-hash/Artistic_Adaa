<?php
class Database {
    private $host = "localhost:3307";
    private $db_name = "MainDB";
    private $username = "root";
    private $password = "";

    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name;
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            // In a real app, you would log this, not echo it.
            die("Connection error: " . $exception->getMessage());
        }
        return $this->conn;
    }
}
?>