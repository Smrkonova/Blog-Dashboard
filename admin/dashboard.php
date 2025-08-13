<?php
session_start();

// Check if logged in
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

require_once '../config/database.php';
$database = new Database();
$db = $database->getConnection();

// Get statistics
$stats = [];

// Total posts
$stmt = $db->query("SELECT COUNT(*) as total FROM blog_posts");
$stats['total_posts'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Published posts
$stmt = $db->query("SELECT COUNT(*) as published FROM blog_posts WHERE status = 'published'");
$stats['published_posts'] = $stmt->fetch(PDO::FETCH_ASSOC)['published'];

// Draft posts
$stmt = $db->query("SELECT COUNT(*) as draft_posts FROM blog_posts WHERE status = 'draft'");
$stats['draft_posts'] = $stmt->fetch(PDO::FETCH_ASSOC)['draft_posts'];

// Total categories
$stmt = $db->query("SELECT COUNT(*) as categories FROM blog_categories");
$stats['categories'] = $stmt->fetch(PDO::FETCH_ASSOC)['categories'];

// Recent posts
$stmt = $db->query("SELECT p.*, c.name as category_name FROM blog_posts p 
                    JOIN blog_categories c ON p.category_id = c.id 
                    ORDER BY p.created_at DESC LIMIT 5");
$recent_posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - TechVision Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        .admin-sidebar {
            background: #2c3e50;
            min-height: 100vh;
            padding: 0;
        }
        .admin-sidebar .nav-link {
            color: #ecf0f1;
            padding: 1rem 1.5rem;
            border-left: 3px solid transparent;
            transition: all 0.3s ease;
        }
        .admin-sidebar .nav-link:hover,
        .admin-sidebar .nav-link.active {
            background: #34495e;
            color: #667eea;
            border-left-color: #667eea;
        }
        .admin-sidebar .nav-link i {
            width: 20px;
            margin-right: 10px;
        }
        .admin-header {
            background: white;
            border-bottom: 1px solid #e9ecef;
            padding: 1rem 0;
        }
        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            border: 1px solid #e9ecef;
        }
        .stats-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }
        .stats-icon.blue { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .stats-icon.green { background: linear-gradient(135deg, #56ab2f 0%, #a8e6cf 100%); }
        .stats-icon.orange { background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%); }
        .stats-icon.purple { background: linear-gradient(135deg, #8e2de2 0%, #4a00e0 100%); }
        .recent-posts-table {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }
        .table th {
            background: #f8f9fa;
            border: none;
            padding: 1rem;
            font-weight: 600;
        }
        .table td {
            padding: 1rem;
            border: none;
            border-bottom: 1px solid #e9ecef;
            vertical-align: middle;
        }
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        .status-published { background: #d4edda; color: #155724; }
        .status-draft { background: #fff3cd; color: #856404; }
        .post-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
        }
        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0">
                <div class="admin-sidebar">
                    <div class="p-3 border-bottom border-secondary">
                        <h5 class="text-white mb-0">
                        <img src="../assets/sm-logo.png" alt="">
                        </h5>
                        <small class="text-muted">Admin Panel</small>
                    </div>
                    
                    <nav class="nav flex-column mt-3">
                        <a class="nav-link active" href="dashboard.php">
                            <i class="fas fa-tachometer-alt"></i>Dashboard
                        </a>
                        <a class="nav-link" href="add-post.php">
                            <i class="fas fa-plus-circle"></i>Add New Post
                        </a>
                        <a class="nav-link" href="manage-posts.php">
                            <i class="fas fa-list"></i>Manage Posts
                        </a>
                        <a class="nav-link" href="categories.php">
                            <i class="fas fa-tags"></i>Categories
                        </a>
                        <a class="nav-link" href="logout.php">
                            <i class="fas fa-sign-out-alt"></i>Logout
                        </a>
                    </nav>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 px-0">
                <!-- Header -->
                <div class="admin-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Dashboard</h4>
                        <div class="d-flex align-items-center">
                            <span class="text-muted me-3">Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                            <a href="logout.php" class="btn btn-outline-danger btn-sm">
                                <i class="fas fa-sign-out-alt me-1"></i>Logout
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Content -->
                <div class="p-4">
                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3 mb-3">
                            <div class="stats-card">
                                <div class="d-flex align-items-center">
                                    <div class="stats-icon blue me-3">
                                        <i class="fas fa-newspaper"></i>
                                    </div>
                                    <div>
                                        <h3 class="mb-0"><?php echo $stats['total_posts']; ?></h3>
                                        <p class="text-muted mb-0">Total Posts</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <div class="stats-card">
                                <div class="d-flex align-items-center">
                                    <div class="stats-icon green me-3">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                    <div>
                                        <h3 class="mb-0"><?php echo $stats['published_posts']; ?></h3>
                                        <p class="text-muted mb-0">Published</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <div class="stats-card">
                                <div class="d-flex align-items-center">
                                    <div class="stats-icon orange me-3">
                                        <i class="fas fa-edit"></i>
                                    </div>
                                    <div>
                                        <h3 class="mb-0"><?php echo $stats['draft_posts']; ?></h3>
                                        <p class="text-muted mb-0">Drafts</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <div class="stats-card">
                                <div class="d-flex align-items-center">
                                    <div class="stats-icon purple me-3">
                                        <i class="fas fa-tags"></i>
                                    </div>
                                    <div>
                                        <h3 class="mb-0"><?php echo $stats['categories']; ?></h3>
                                        <p class="text-muted mb-0">Categories</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Recent Posts -->
                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5>Recent Posts</h5>
                                <a href="manage-posts.php" class="btn btn-primary btn-sm">
                                    <i class="fas fa-eye me-1"></i>View All
                                </a>
                            </div>
                            
                            <div class="recent-posts-table">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Title</th>
                                            <th>Category</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if(empty($recent_posts)): ?>
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-4">
                                                    No posts found. <a href="add-post.php">Create your first post</a>
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach($recent_posts as $post): ?>
                                                <tr>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($post['title']); ?></strong>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-secondary"><?php echo htmlspecialchars($post['category_name']); ?></span>
                                                    </td>
                                                    <td>
                                                        <span class="status-badge <?php echo $post['status'] == 'published' ? 'status-published' : 'status-draft'; ?>">
                                                            <?php echo ucfirst($post['status']); ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo date('M d, Y', strtotime($post['created_at'])); ?></td>
                                                    <td>
                                                        <a href="edit-post.php?id=<?php echo $post['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
