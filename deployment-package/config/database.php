<?php
class Database {
    private $host = 'localhost'; // Hostinger database host
    private $db_name = 'your_database_name'; // You'll get this from Hostinger
    private $username = 'your_database_username'; // You'll get this from Hostinger
    private $password = 'your_database_password'; // You'll get this from Hostinger
    private $conn;
    
    public function getConnection() {
        $this->conn = null;
        
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8",
                $this->username,
                $this->password,
                array(
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                )
            );
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        
        return $this->conn;
    }
}
?>
