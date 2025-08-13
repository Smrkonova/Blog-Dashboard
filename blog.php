<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

// Get search and category filters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category_filter = isset($_GET['category']) ? (int)$_GET['category'] : 0;

// Build query
$where_conditions = ["p.status = 'published'"];
$params = [];

if(!empty($search)) {
    $where_conditions[] = "(p.title LIKE ? OR p.description LIKE ? OR p.content LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

if($category_filter > 0) {
    $where_conditions[] = "p.category_id = ?";
    $params[] = $category_filter;
}

$where_clause = implode(" AND ", $where_conditions);

// Get blog posts
$posts = [];
try {
    $query = "SELECT p.*, c.name as category_name FROM blog_posts p 
              JOIN blog_categories c ON p.category_id = c.id 
              WHERE $where_clause 
              ORDER BY p.created_at DESC";
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    // Handle error silently for now
}

// Get categories for sidebar
$categories = [];
try {
    $stmt = $db->query("SELECT c.*, COUNT(p.id) as post_count FROM blog_categories c 
                        LEFT JOIN blog_posts p ON c.id = p.category_id AND p.status = 'published'
                        GROUP BY c.id ORDER BY c.name");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    // Handle error silently for now
}

// Get recent posts for sidebar
$recent_posts = [];
try {
    $stmt = $db->query("SELECT p.*, c.name as category_name FROM blog_posts p 
                        JOIN blog_categories c ON p.category_id = c.id 
                        WHERE p.status = 'published' 
                        ORDER BY p.created_at DESC LIMIT 5");
    $recent_posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    // Handle error silently for now
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog - TechVision Pro</title>
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

    <!-- Blog Header -->
    <section class="blog-header">
        <div class="container">
            <h1>Our <span class="text-gradient">Blog</span></h1>
            <p class="lead">Insights, tips, and stories from our team of experts</p>
        </div>
    </section>

    <!-- Blog Content -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <!-- Main Content -->
                <div class="col-lg-8">
                    <!-- Search and Filter -->
                    <div class="mb-4">
                        <form method="GET" action="" class="row g-3">
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="search" 
                                       placeholder="Search posts..." 
                                       value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-4">
                                <select class="form-select" name="category">
                                    <option value="">All Categories</option>
                                    <?php foreach($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>" 
                                                <?php echo $category_filter == $category['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['name']); ?> 
                                            (<?php echo $category['post_count']; ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Blog Posts -->
                    <?php if(empty($posts)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-search fa-3x text-muted mb-3"></i>
                            <h5>No posts found</h5>
                            <p class="text-muted">
                                <?php if(!empty($search) || $category_filter > 0): ?>
                                    Try adjusting your search criteria or browse all posts.
                                <?php else: ?>
                                    Check back soon for our latest insights and articles!
                                <?php endif; ?>
                            </p>
                            <?php if(!empty($search) || $category_filter > 0): ?>
                                <a href="blog.php" class="btn btn-primary">View All Posts</a>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="blog-grid">
                            <?php foreach($posts as $post): ?>
                                <div class="blog-card">
                                    <?php if(!empty($post['image_path'])): ?>
                                        <img src="<?php echo htmlspecialchars($post['image_path']); ?>" 
                                             alt="<?php echo htmlspecialchars($post['title']); ?>" class="blog-image">
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
                                        <a href="blog-detail.php?slug=<?php echo $post['slug']; ?>" class="read-more">
                                            Read More <i class="fas fa-arrow-right ms-1"></i>
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <div class="blog-sidebar">
                        <!-- Search -->
                        <div class="sidebar-section">
                            <h4>Search</h4>
                            <form method="GET" action="">
                                <div class="search-box">
                                    <input type="text" name="search" placeholder="Search posts..." 
                                           value="<?php echo htmlspecialchars($search); ?>">
                                    <button type="submit"><i class="fas fa-search"></i></button>
                                </div>
                            </form>
                        </div>

                        <!-- Categories -->
                        <div class="sidebar-section">
                            <h4>Categories</h4>
                            <div class="list-group list-group-flush">
                                <a href="blog.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center <?php echo $category_filter == 0 ? 'active' : ''; ?>">
                                    All Categories
                                    <span class="badge bg-primary rounded-pill">
                                        <?php echo array_sum(array_column($categories, 'post_count')); ?>
                                    </span>
                                </a>
                                <?php foreach($categories as $category): ?>
                                    <a href="blog.php?category=<?php echo $category['id']; ?>" 
                                       class="list-group-item list-group-item-action d-flex justify-content-between align-items-center <?php echo $category_filter == $category['id'] ? 'active' : ''; ?>">
                                        <?php echo htmlspecialchars($category['name']); ?>
                                        <span class="badge bg-primary rounded-pill"><?php echo $category['post_count']; ?></span>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Recent Posts -->
                        <div class="sidebar-section">
                            <h4>Recent Posts</h4>
                            <?php if(empty($recent_posts)): ?>
                                <p class="text-muted">No posts yet</p>
                            <?php else: ?>
                                <?php foreach($recent_posts as $post): ?>
                                    <div class="recent-post">
                                        <?php if(!empty($post['image_path'])): ?>
                                            <img src="<?php echo htmlspecialchars($post['image_path']); ?>" 
                                                 alt="<?php echo htmlspecialchars($post['title']); ?>">
                                        <?php else: ?>
                                            <div class="bg-light d-flex align-items-center justify-content-center" 
                                                 style="width: 60px; height: 60px; border-radius: 10px;">
                                                <i class="fas fa-image text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div class="recent-post-content">
                                            <h6><a href="blog-detail.php?slug=<?php echo $post['slug']; ?>" 
                                                   class="text-decoration-none"><?php echo htmlspecialchars($post['title']); ?></a></h6>
                                            <small><?php echo date('M d, Y', strtotime($post['created_at'])); ?></small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
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
