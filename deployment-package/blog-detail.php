<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

// Get post slug from URL
$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';

if(empty($slug)) {
    header("Location: blog.php");
    exit();
}

// Get post data
$post = null;
try {
    $query = "SELECT p.*, c.name as category_name FROM blog_posts p 
              JOIN blog_categories c ON p.category_id = c.id 
              WHERE p.slug = ? AND p.status = 'published'";
    $stmt = $db->prepare($query);
    $stmt->bindParam(1, $slug);
    $stmt->execute();
    
    if($stmt->rowCount() == 0) {
        header("Location: blog.php");
        exit();
    }
    
    $post = $stmt->fetch(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    header("Location: blog.php");
    exit();
}

// Get related posts
$related_posts = [];
try {
    $query = "SELECT p.*, c.name as category_name FROM blog_posts p 
              JOIN blog_categories c ON p.category_id = c.id 
              WHERE p.status = 'published' AND p.category_id = ? AND p.id != ? 
              ORDER BY p.created_at DESC LIMIT 3";
    $stmt = $db->prepare($query);
    $stmt->bindParam(1, $post['category_id']);
    $stmt->bindParam(2, $post['id']);
    $stmt->execute();
    $related_posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    // Handle error silently for now
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($post['title']); ?> - TechVision Pro</title>
    <meta name="description" content="<?php echo htmlspecialchars($post['description']); ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .blog-detail-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 4rem 0;
            text-align: center;
        }
        .blog-detail-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            line-height: 1.2;
        }
        .blog-detail-meta {
            display: flex;
            justify-content: center;
            gap: 2rem;
            flex-wrap: wrap;
            font-size: 1.1rem;
        }
        .blog-detail-meta span {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .blog-detail-image {
            width: 100%;
            max-height: 500px;
            object-fit: cover;
            border-radius: 15px;
            margin: 2rem 0;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        .blog-content-wrapper {
            background: white;
            padding: 3rem;
            border-radius: 20px;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.08);
            margin: 2rem 0;
        }
        .blog-content-wrapper h2 {
            color: #2c3e50;
            font-weight: 700;
            margin-bottom: 1.5rem;
            font-size: 2.2rem;
        }
        .blog-content-wrapper .lead {
            font-size: 1.3rem;
            color: #6c757d;
            line-height: 1.6;
            margin-bottom: 2rem;
            font-weight: 400;
        }
        .blog-content {
            font-size: 1.1rem;
            line-height: 1.8;
            color: #2c3e50;
        }
        .blog-content p {
            margin-bottom: 1.5rem;
        }
        .blog-content h3, .blog-content h4 {
            color: #2c3e50;
            margin: 2rem 0 1rem 0;
            font-weight: 600;
        }
        .social-share {
            background: #f8f9fa;
            padding: 2rem;
            border-radius: 15px;
            text-align: center;
            margin: 2rem 0;
        }
        .social-share span {
            font-weight: 600;
            color: #2c3e50;
            font-size: 1.1rem;
        }
        .social-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            color: white;
            text-decoration: none;
            margin: 0 0.5rem;
            transition: all 0.3s ease;
            font-size: 1.2rem;
        }
        .social-btn:hover {
            transform: translateY(-3px);
            color: white;
            text-decoration: none;
        }
        .social-btn.facebook { background: #1877f2; }
        .social-btn.twitter { background: #1da1f2; }
        .social-btn.linkedin { background: #0077b5; }
        .related-articles {
            background: white;
            padding: 3rem;
            border-radius: 20px;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.08);
            margin: 2rem 0;
        }
        .related-articles h3 {
            color: #2c3e50;
            font-weight: 700;
            margin-bottom: 2rem;
            text-align: center;
            font-size: 2rem;
        }
        .blog-sidebar {
            position: sticky;
            top: 2rem;
        }
        .sidebar-section {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
        }
        .sidebar-section h4 {
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 1.5rem;
            font-size: 1.3rem;
        }
        .list-group-item {
            border: none;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 0.5rem;
            transition: all 0.3s ease;
        }
        .list-group-item:hover {
            background: #f8f9fa;
            transform: translateX(5px);
        }
        .list-group-item.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        .tag-cloud {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        .tag {
            background: #e9ecef;
            color: #6c757d;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }
        .tag:hover {
            background: #667eea;
            color: white;
            text-decoration: none;
            transform: translateY(-2px);
        }
        .back-to-blog {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .back-to-blog:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
            color: white;
        }
        .post-meta-item {
            background: rgba(255, 255, 255, 0.1);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
        }
        @media (max-width: 768px) {
            .blog-detail-header h1 {
                font-size: 2rem;
            }
            .blog-detail-meta {
                flex-direction: column;
                gap: 1rem;
            }
            .blog-content-wrapper,
            .related-articles {
                padding: 2rem;
            }
            .social-share {
                padding: 1.5rem;
            }
        }
    </style>
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
                        <a class="nav-link active" href="blog.php">Blog</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#services">Services</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#contact">Contact</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin/login.php" target="_blank">Admin</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Blog Detail Header -->
    <section class="blog-detail-header">
        <div class="container">
            <h1><?php echo htmlspecialchars($post['title']); ?></h1>
            <div class="blog-detail-meta">
                <span class="post-meta-item">
                    <i class="fas fa-user me-2"></i>TechVision Pro Team
                </span>
                <span class="post-meta-item">
                    <i class="fas fa-calendar me-2"></i><?php echo date('M d, Y', strtotime($post['created_at'])); ?>
                </span>
                <span class="post-meta-item">
                    <i class="fas fa-tag me-2"></i><?php echo htmlspecialchars($post['category_name']); ?>
                </span>
            </div>
        </div>
    </section>

    <!-- Blog Content -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <!-- Main Content -->
                <div class="col-lg-8">
                    <!-- Featured Image -->
                    <?php if(!empty($post['image_path'])): ?>
                        <img src="<?php echo htmlspecialchars($post['image_path']); ?>" 
                             alt="<?php echo htmlspecialchars($post['title']); ?>" class="blog-detail-image">
                    <?php endif; ?>

                    <!-- Blog Content -->
                    <div class="blog-content-wrapper">
                        <h2><?php echo htmlspecialchars($post['title']); ?></h2>
                        <p class="lead"><?php echo htmlspecialchars($post['description']); ?></p>
                        
                        <div class="blog-content">
                            <?php echo nl2br(htmlspecialchars($post['content'])); ?>
                        </div>
                    </div>

                    <!-- Social Sharing -->
                    <div class="social-share">
                        <span class="me-3">Share this post:</span>
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" 
                           class="social-btn facebook" target="_blank" title="Share on Facebook">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>&text=<?php echo urlencode($post['title']); ?>" 
                           class="social-btn twitter" target="_blank" title="Share on Twitter">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" 
                           class="social-btn linkedin" target="_blank" title="Share on LinkedIn">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                    </div>

                    <!-- Related Articles -->
                    <?php if(!empty($related_posts)): ?>
                        <div class="related-articles">
                            <h3>Related Articles</h3>
                            <div class="row">
                                <?php foreach($related_posts as $related_post): ?>
                                    <div class="col-md-4 mb-3">
                                        <div class="blog-card">
                                            <?php if(!empty($related_post['image_path'])): ?>
                                                <img src="<?php echo htmlspecialchars($related_post['image_path']); ?>" 
                                                     alt="<?php echo htmlspecialchars($related_post['title']); ?>" class="blog-image">
                                            <?php else: ?>
                                                <div class="blog-image bg-light d-flex align-items-center justify-content-center">
                                                    <i class="fas fa-image text-muted fa-2x"></i>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <div class="blog-content">
                                                <div class="blog-meta">
                                                    <span class="blog-category"><?php echo htmlspecialchars($related_post['category_name']); ?></span>
                                                    <span><i class="fas fa-calendar me-1"></i><?php echo date('M d, Y', strtotime($related_post['created_at'])); ?></span>
                                                </div>
                                                <h4><?php echo htmlspecialchars($related_post['title']); ?></h4>
                                                <p><?php echo htmlspecialchars(substr($related_post['description'], 0, 100)); ?>...</p>
                                                <a href="blog-detail.php?slug=<?php echo $related_post['slug']; ?>" class="read-more">
                                                    Read More <i class="fas fa-arrow-right ms-1"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <div class="blog-sidebar">
                        <!-- Back to Blog -->
                        <div class="sidebar-section">
                            <a href="blog.php" class="btn back-to-blog w-100">
                                <i class="fas fa-arrow-left me-2"></i>Back to Blog
                            </a>
                        </div>

                        <!-- Post Meta -->
                        <div class="sidebar-section">
                            <h4>Post Details</h4>
                            <div class="list-group list-group-flush">
                                <div class="list-group-item d-flex justify-content-between">
                                    <span>Category:</span>
                                    <span class="badge bg-primary"><?php echo htmlspecialchars($post['category_name']); ?></span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between">
                                    <span>Published:</span>
                                    <span><?php echo date('M d, Y', strtotime($post['created_at'])); ?></span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between">
                                    <span>Reading Time:</span>
                                    <span><?php echo ceil(str_word_count($post['content']) / 200); ?> min read</span>
                                </div>
                            </div>
                        </div>

                        <!-- Categories -->
                        <div class="sidebar-section">
                            <h4>Categories</h4>
                            <div class="list-group list-group-flush">
                                <?php
                                try {
                                    $stmt = $db->query("SELECT c.*, COUNT(p.id) as post_count FROM blog_categories c 
                                                      LEFT JOIN blog_posts p ON c.id = p.category_id AND p.status = 'published'
                                                      GROUP BY c.id ORDER BY c.name");
                                    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                    
                                    foreach($categories as $category):
                                ?>
                                    <a href="blog.php?category=<?php echo $category['id']; ?>" 
                                       class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                        <?php echo htmlspecialchars($category['name']); ?>
                                        <span class="badge bg-primary rounded-pill"><?php echo $category['post_count']; ?></span>
                                    </a>
                                <?php 
                                    endforeach;
                                } catch(PDOException $e) {
                                    // Handle error silently for now
                                }
                                ?>
                            </div>
                        </div>

                        <!-- Tags -->
                        <div class="sidebar-section">
                            <h4>Popular Tags</h4>
                            <div class="tag-cloud">
                                <a href="blog.php" class="tag">Technology</a>
                                <a href="blog.php" class="tag">Web Development</a>
                                <a href="blog.php" class="tag">Mobile Apps</a>
                                <a href="blog.php" class="tag">AI</a>
                                <a href="blog.php" class="tag">Digital Transformation</a>
                                <a href="blog.php" class="tag">Cybersecurity</a>
                            </div>
                        </div>
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
