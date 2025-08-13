-- Production Database Setup for Hostinger
-- Database: Your database name (from Hostinger)
-- Run this in phpMyAdmin or MySQL console

-- Create admin_users table
CREATE TABLE IF NOT EXISTS `admin_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create blog_categories table
CREATE TABLE IF NOT EXISTS `blog_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create blog_posts table
CREATE TABLE IF NOT EXISTS `blog_posts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text,
  `content` longtext NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `category_id` int(11) NOT NULL,
  `status` enum('draft','published') DEFAULT 'draft',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `blog_posts_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `blog_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin user (username: admin, password: admin123)
-- IMPORTANT: Change this password after first login!
INSERT INTO `admin_users` (`username`, `password`, `email`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@test.smrkonova.com');

-- Insert default categories
INSERT INTO `blog_categories` (`name`, `slug`) VALUES
('Technology', 'technology'),
('Web Development', 'web-development'),
('Mobile Apps', 'mobile-apps'),
('Cloud Computing', 'cloud-computing'),
('Cybersecurity', 'cybersecurity');

-- Insert sample blog posts
INSERT INTO `blog_posts` (`title`, `slug`, `description`, `content`, `category_id`, `status`, `created_at`) VALUES
('Welcome to TechVision Pro', 'welcome-to-techvision-pro', 'Your trusted partner in IT solutions and digital transformation.', '<p>Welcome to TechVision Pro, where innovation meets expertise. We are your trusted partner in navigating the complex world of technology and digital transformation.</p><p>Our team of experienced professionals combines deep technical knowledge with business acumen to deliver solutions that drive real results for your organization.</p><h3>What We Offer</h3><ul><li>Custom Software Development</li><li>Web and Mobile Applications</li><li>Cloud Infrastructure Solutions</li><li>Digital Transformation Consulting</li><li>Cybersecurity Services</li></ul><p>Stay tuned for more insights, tips, and updates from the world of technology!</p>', 1, 'published', NOW()),
('The Future of Web Development', 'future-of-web-development', 'Exploring emerging trends and technologies shaping the web development landscape.', '<p>The web development landscape is constantly evolving, with new technologies and frameworks emerging at a rapid pace. In this post, we\'ll explore some of the most exciting trends that are shaping the future of web development.</p><h3>Key Trends to Watch</h3><h4>1. Progressive Web Apps (PWAs)</h4><p>PWAs are becoming increasingly popular as they offer a native app-like experience while maintaining the accessibility of web applications.</p><h4>2. Serverless Architecture</h4><p>Serverless computing is revolutionizing how we build and deploy applications, offering scalability and cost-effectiveness.</p><h4>3. AI-Powered Development</h4><p>Artificial intelligence is being integrated into development tools, making coding more efficient and intelligent.</p><p>These trends represent just the beginning of what\'s to come in web development. The future is bright and full of possibilities!</p>', 2, 'published', NOW()),
('Mobile App Development Best Practices', 'mobile-app-development-best-practices', 'Essential guidelines for creating successful mobile applications.', '<p>Mobile app development requires careful planning and execution to ensure success in today\'s competitive market. Here are some best practices that can help you create apps that users love.</p><h3>Design Principles</h3><ul><li><strong>User-Centered Design:</strong> Always prioritize user experience and needs</li><li><strong>Consistent Interface:</strong> Maintain visual and functional consistency</li><li><strong>Performance Optimization:</strong> Ensure fast loading times and smooth operation</li></ul><h3>Development Guidelines</h3><ul><li><strong>Cross-Platform Compatibility:</strong> Consider using frameworks like React Native or Flutter</li><li><strong>Regular Testing:</strong> Implement comprehensive testing strategies</li><li><strong>Security First:</strong> Prioritize data protection and privacy</li></ul><p>By following these best practices, you can create mobile apps that stand out in the market and provide exceptional user experiences.</p>', 3, 'published', NOW());
