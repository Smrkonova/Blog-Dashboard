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

// Handle post deletion
if(isset($_POST['delete_post']) && isset($_POST['post_id'])) {
    $post_id = (int)$_POST['post_id'];
    
    // Get image path before deletion
    $stmt = $db->prepare("SELECT image_path FROM blog_posts WHERE id = ?");
    $stmt->bindParam(1, $post_id);
    $stmt->execute();
    $post = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if($post) {
        // Delete the post
        $stmt = $db->prepare("DELETE FROM blog_posts WHERE id = ?");
        $stmt->bindParam(1, $post_id);
        
        if($stmt->execute()) {
            // Delete the image file
            if(!empty($post['image_path'])) {
                $image_path = '../' . $post['image_path'];
                if(file_exists($image_path)) {
                    unlink($image_path);
                }
            }
            $success_message = "Post deleted successfully!";
        } else {
            $error_message = "Failed to delete post.";
        }
    }
}

// Get all posts
$stmt = $db->query("SELECT p.*, c.name as category_name FROM blog_posts p 
                    JOIN blog_categories c ON p.category_id = c.id 
                    ORDER BY p.created_at DESC");
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Posts - Admin Panel</title>
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
        .posts-table {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            border: 1px solid #e9ecef;
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
            width: 80px;
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
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #6c757d;
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
                            <i class="fas fa-code me-2"></i>TechVision Pro
                        </h5>
                        <small class="text-muted">Admin Panel</small>
                    </div>
                    
                    <nav class="nav flex-column mt-3">
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-tachometer-alt"></i>Dashboard
                        </a>
                        <a class="nav-link" href="add-post.php">
                            <i class="fas fa-plus-circle"></i>Add New Post
                        </a>
                        <a class="nav-link active" href="manage-posts.php">
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
                        <h4 class="mb-0">Manage Blog Posts</h4>
                        <div class="d-flex align-items-center">
                            <a href="add-post.php" class="btn btn-primary me-3">
                                <i class="fas fa-plus me-1"></i>Add New Post
                            </a>
                            <span class="text-muted me-3">Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                            <a href="logout.php" class="btn btn-outline-danger btn-sm">
                                <i class="fas fa-sign-out-alt me-1"></i>Logout
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Content -->
                <div class="p-4">
                    <?php if(isset($success_message)): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success_message); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if(isset($error_message)): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error_message); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="posts-table">
                        <?php if(empty($posts)): ?>
                            <div class="empty-state">
                                <i class="fas fa-newspaper fa-3x mb-3"></i>
                                <h5>No blog posts yet</h5>
                                <p>Start by creating your first blog post!</p>
                                <a href="add-post.php" class="btn btn-primary">
                                    <i class="fas fa-plus me-1"></i>Create First Post
                                </a>
                            </div>
                        <?php else: ?>
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Image</th>
                                        <th>Title</th>
                                        <th>Category</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($posts as $post): ?>
                                        <tr>
                                            <td>
                                                <?php if(!empty($post['image_path'])): ?>
                                                    <img src="../<?php echo htmlspecialchars($post['image_path']); ?>" 
                                                         alt="<?php echo htmlspecialchars($post['title']); ?>" 
                                                         class="post-image">
                                                <?php else: ?>
                                                    <div class="bg-light d-flex align-items-center justify-content-center post-image">
                                                        <i class="fas fa-image text-muted"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($post['title']); ?></strong>
                                                <br>
                                                <small class="text-muted"><?php echo htmlspecialchars(substr($post['description'], 0, 100)); ?>...</small>
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
                                                <div class="action-buttons">
                                                    <a href="../blog-detail.php?slug=<?php echo $post['slug']; ?>" 
                                                       class="btn btn-sm btn-outline-info" target="_blank" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="edit-post.php?id=<?php echo $post['id']; ?>" 
                                                       class="btn btn-sm btn-outline-primary" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                                            onclick="confirmDelete(<?php echo $post['id']; ?>, '<?php echo htmlspecialchars(addslashes($post['title'])); ?>')" 
                                                            title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete "<span id="postTitle"></span>"?</p>
                    <p class="text-danger"><small>This action cannot be undone.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="post_id" id="postId">
                        <button type="submit" name="delete_post" class="btn btn-danger">Delete Post</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmDelete(postId, postTitle) {
            document.getElementById('postId').value = postId;
            document.getElementById('postTitle').textContent = postTitle;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }
    </script>
</body>
</html>
