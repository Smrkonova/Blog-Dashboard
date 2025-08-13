<?php
require_once 'config/database.php';

// Get latest blog posts
$database = new Database();
$db = $database->getConnection();

$latest_posts = [];
try {
    $query = "SELECT p.*, c.name as category_name FROM blog_posts p 
              JOIN blog_categories c ON p.category_id = c.id 
              WHERE p.status = 'published' 
              ORDER BY p.created_at DESC LIMIT 3";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $latest_posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    // Handle error silently for now
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechVision Pro - Leading IT Solutions Company</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-code me-2"></i>TechVision Pro
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="blog.php">Blog</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#services">Services</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Contact</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin/login.php" target="_blank">Admin</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h1>Transform Your Business with <span class="text-gradient">Cutting-Edge IT Solutions</span></h1>
            <p>We specialize in web development, mobile apps, AI solutions, and digital transformation that drive real business results.</p>
            <a href="blog.php" class="btn btn-primary">
                <i class="fas fa-rocket me-2"></i>Explore Our Insights
            </a>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="services">
        <div class="container">
            <div class="section-header">
                <h2>Our <span class="text-gradient">Services</span></h2>
                <p class="lead">Comprehensive IT solutions tailored to your business needs</p>
            </div>
            <div class="row">
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fas fa-globe"></i>
                        </div>
                        <h4>Web Development</h4>
                        <p>Custom websites and web applications built with modern technologies and best practices.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <h4>Mobile Apps</h4>
                        <p>Native and cross-platform mobile applications for iOS and Android platforms.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fas fa-robot"></i>
                        </div>
                        <h4>AI & Machine Learning</h4>
                        <p>Intelligent solutions that automate processes and provide data-driven insights.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="about">
        <div class="container">
            <div class="about-content">
                <div class="about-text">
                    <h3>About <span class="text-gradient">TechVision Pro</span></h3>
                    <p>We are a forward-thinking IT company dedicated to helping businesses thrive in the digital age. With years of experience and a passion for innovation, we deliver cutting-edge solutions that drive growth and efficiency.</p>
                    <p>Our team of experts combines technical expertise with business acumen to create solutions that not only meet your current needs but also prepare you for future challenges.</p>
                    <a href="#contact" class="btn btn-outline">Learn More</a>
                </div>
                <div class="about-image">
                    <img src="https://images.unsplash.com/photo-1551434678-e076c223a692?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80" alt="Team collaboration">
                </div>
            </div>
        </div>
    </section>

    <!-- Latest Blog Posts Section -->
    <section class="latest-posts">
        <div class="container">
            <div class="section-header">
                <h2>Latest <span class="text-gradient">Insights</span></h2>
                <p class="lead">Stay updated with the latest trends and technologies</p>
            </div>
            
            <?php if(empty($latest_posts)): ?>
                <div class="row">
                    <div class="col-12 text-center">
                        <div class="py-5">
                            <i class="fas fa-newspaper fa-3x text-muted mb-3"></i>
                            <h5>No blog posts yet</h5>
                            <p class="text-muted">Check back soon for our latest insights and articles!</p>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="blog-grid">
                    <?php foreach($latest_posts as $post): ?>
                        <div class="blog-card">
                            <?php if(!empty($post['image_path'])): ?>
                                <img src="<?php echo htmlspecialchars($post['image_path']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" class="blog-image">
                            <?php else: ?>
                                <div class="blog-image bg-light d-flex align-items-center justify-content-center">
                                    <i class="fas fa-image text-muted fa-2x"></i>
                                </div>
                            <?php endif; ?>
                            
                            <div class="blog-content">
                                <div class="blog-meta">
                                    <span class="blog-category"><?php echo htmlspecialchars($post['category_name']); ?></span>
                                    <span><i class="fas fa-calendar me-1"></i><?php echo date('M d, Y', strtotime($post['created_at'])); ?></span>
                                </div>
                                <h4><?php echo htmlspecialchars($post['title']); ?></h4>
                                <p><?php echo htmlspecialchars(substr($post['description'], 0, 120)); ?>...</p>
                                <a href="blog-detail.php?slug=<?php echo $post['slug']; ?>" class="read-more">Read More <i class="fas fa-arrow-right ms-1"></i></a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="text-center mt-4">
                    <a href="blog.php" class="btn btn-outline">View All Posts</a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="contact">
        <div class="container">
            <div class="section-header">
                <h2>Get In <span class="text-gradient">Touch</span></h2>
                <p class="lead">Ready to start your digital transformation journey? Let's talk!</p>
            </div>
            
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="contact-form">
                        <form>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name" class="form-label">Name</label>
                                        <input type="text" class="form-control" id="name" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" required>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="subject" class="form-label">Subject</label>
                                <input type="text" class="form-control" id="subject" required>
                            </div>
                            <div class="form-group">
                                <label for="message" class="form-label">Message</label>
                                <textarea class="form-control" id="message" rows="5" required></textarea>
                            </div>
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-2"></i>Send Message
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>TechVision Pro</h5>
                    <p>Leading IT solutions company helping businesses transform through technology.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p>&copy; 2024 TechVision Pro. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
