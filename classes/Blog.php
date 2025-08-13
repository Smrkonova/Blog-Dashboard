<?php
require_once __DIR__ . '/../config/database.php';

class Blog {
    private $conn;
    private $table_name = "blog_posts";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Get all published blog posts
    public function getAllPosts() {
        $query = "SELECT p.*, c.name as category_name 
                  FROM " . $this->table_name . " p 
                  LEFT JOIN blog_categories c ON p.category_id = c.id 
                  ORDER BY p.published_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }

    // Get blog post by slug
    public function getPostBySlug($slug) {
        $query = "SELECT p.*, c.name as category_name 
                  FROM " . $this->table_name . " p 
                  LEFT JOIN blog_categories c ON p.category_id = c.id 
                  WHERE p.slug = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $slug);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get posts by category
    public function getPostsByCategory($category_id) {
        $query = "SELECT p.*, c.name as category_name 
                  FROM " . $this->table_name . " p 
                  LEFT JOIN blog_categories c ON p.category_id = c.id 
                  WHERE p.category_id = ? 
                  ORDER BY p.published_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $category_id);
        $stmt->execute();
        
        return $stmt;
    }

    // Get all categories
    public function getAllCategories() {
        $query = "SELECT * FROM blog_categories ORDER BY name";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }

    // Create new blog post (Admin only)
    public function createPost($title, $description, $content, $image_path, $category_id) {
        $slug = $this->createSlug($title);
        
        $query = "INSERT INTO " . $this->table_name . " 
                  (title, slug, description, content, image_path, category_id) 
                  VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $title);
        $stmt->bindParam(2, $slug);
        $stmt->bindParam(3, $description);
        $stmt->bindParam(4, $content);
        $stmt->bindParam(5, $image_path);
        $stmt->bindParam(6, $category_id);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Update blog post (Admin only)
    public function updatePost($id, $title, $description, $content, $image_path, $category_id) {
        $slug = $this->createSlug($title);
        
        $query = "UPDATE " . $this->table_name . " 
                  SET title = ?, slug = ?, description = ?, content = ?, 
                      image_path = ?, category_id = ?, updated_at = CURRENT_TIMESTAMP 
                  WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $title);
        $stmt->bindParam(2, $slug);
        $stmt->bindParam(3, $description);
        $stmt->bindParam(4, $content);
        $stmt->bindParam(5, $image_path);
        $stmt->bindParam(6, $category_id);
        $stmt->bindParam(7, $id);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Delete blog post (Admin only)
    public function deletePost($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Get post by ID (Admin only)
    public function getPostById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Create URL-friendly slug
    private function createSlug($string) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string)));
        return $slug;
    }
}
?>
