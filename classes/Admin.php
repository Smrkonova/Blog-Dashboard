<?php
require_once __DIR__ . '/../config/database.php';

class Admin {
    private $conn;
    private $table_name = "admin_users";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Admin login
    public function login($username, $password) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE username = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $username);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if(password_verify($password, $row['password'])) {
                return $row;
            }
        }
        return false;
    }

    // Check if admin is logged in
    public function isLoggedIn() {
        return isset($_SESSION['admin_id']);
    }

    // Logout
    public function logout() {
        session_destroy();
        return true;
    }
}
?>
