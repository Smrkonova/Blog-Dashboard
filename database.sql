-- Create database for IT Company Blog
CREATE DATABASE IF NOT EXISTS it_company_blog;
USE it_company_blog;

-- Admin users table
CREATE TABLE admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Blog categories table
CREATE TABLE blog_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Blog posts table
CREATE TABLE blog_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    description TEXT NOT NULL,
    content LONGTEXT NOT NULL,
    category_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    status ENUM('draft', 'published') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES blog_categories(id) ON DELETE CASCADE
);

-- Insert default admin user (username: admin, password: admin123)
-- This hash is generated using PHP's password_hash() function
INSERT INTO admin_users (username, password, email) VALUES 
('admin', '$2y$10$YourNewHashHere', 'admin@techvision.com');

-- Insert default categories
INSERT INTO blog_categories (name, slug) VALUES 
('Web Development', 'web-development'),
('Mobile Apps', 'mobile-apps'),
('AI & Machine Learning', 'ai-machine-learning'),
('Digital Transformation', 'digital-transformation'),
('Cybersecurity', 'cybersecurity'); 
