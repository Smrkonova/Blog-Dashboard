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

$message = '';
$error = '';

// Handle category deletion
if(isset($_POST['delete_category']) && isset($_POST['category_id'])) {
    $category_id = (int)$_POST['category_id'];
    
    // Check if category has posts
    $stmt = $db->prepare("SELECT COUNT(*) as post_count FROM blog_posts WHERE category_id = ?");
    $stmt->bindParam(1, $category_id);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if($result['post_count'] > 0) {
        $error = "Cannot delete category. It has " . $result['post_count'] . " associated blog posts.";
    } else {
        // Delete the category
        $stmt = $db->prepare("DELETE FROM blog_categories WHERE id = ?");
        $stmt->bindParam(1, $category_id);
        
        if($stmt->execute()) {
            $message = "Category deleted successfully!";
        } else {
            $error = "Failed to delete category.";
        }
    }
}

// Handle category addition/editing
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['category_name'])) {
    $category_name = trim($_POST['category_name']);
    $category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
    
    if(empty($category_name)) {
        $error = 'Please enter a category name.';
    } else {
        // Generate slug from name
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $category_name)));
        
        try {
            if($category_id > 0) {
                // Update existing category
                $query = "UPDATE blog_categories SET name = ?, slug = ? WHERE id = ?";
                $stmt = $db->prepare($query);
                $stmt->bindParam(1, $category_name);
                $stmt->bindParam(2, $slug);
                $stmt->bindParam(3, $category_id);
                
                if($stmt->execute()) {
                    $message = 'Category updated successfully!';
                } else {
                    $error = 'Failed to update category.';
                }
            } else {
                // Add new category
                $query = "INSERT INTO blog_categories (name, slug) VALUES (?, ?)";
                $stmt = $db->prepare($query);
                $stmt->bindParam(1, $category_name);
                $stmt->bindParam(2, $slug);
                
                if($stmt->execute()) {
                    $message = 'Category added successfully!';
                } else {
                    $error = 'Failed to add category.';
                }
            }
        } catch(PDOException $e) {
            if(strpos($e->getMessage(), 'Duplicate entry') !== false) {
                $error = 'A category with this name already exists.';
            } else {
                $error = 'Database error: ' . $e->getMessage();
            }
        }
    }
}

// Get all categories with post counts
$stmt = $db->query("SELECT c.*, COUNT(p.id) as post_count FROM blog_categories c 
                    LEFT JOIN blog_posts p ON c.id = p.category_id 
                    GROUP BY c.id ORDER BY c.name");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get category for editing (if edit mode)
$edit_category = null;
if(isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    foreach($categories as $cat) {
        if($cat['id'] == $edit_id) {
            $edit_category = $cat;
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories - Admin Panel</title>
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
        .form-container {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            border: 1px solid #e9ecef;
            margin-bottom: 2rem;
        }
        .categories-table {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            border: 1px solid #e9ecef;
        }
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 1rem;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
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
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #6c757d;
        }
        .category-form-row {
            display: flex;
            gap: 1rem;
            align-items: end;
        }
        .category-form-row .form-group {
            flex: 1;
        }
        .category-form-row .btn-group {
            flex-shrink: 0;
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
                        <a class="nav-link" href="manage-posts.php">
                            <i class="fas fa-list"></i>Manage Posts
                        </a>
                        <a class="nav-link active" href="categories.php">
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
                        <h4 class="mb-0">Manage Categories</h4>
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
                    <?php if($message): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($message); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if($error): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Add/Edit Category Form -->
                    <div class="form-container">
                        <h5 class="mb-3">
                            <?php if($edit_category): ?>
                                <i class="fas fa-edit me-2"></i>Edit Category
                            <?php else: ?>
                                <i class="fas fa-plus me-2"></i>Add New Category
                            <?php endif; ?>
                        </h5>
                        
                        <form method="POST" action="">
                            <div class="category-form-row">
                                <div class="form-group">
                                    <label for="category_name" class="form-label">Category Name *</label>
                                    <input type="text" class="form-control" id="category_name" name="category_name" 
                                           value="<?php echo $edit_category ? htmlspecialchars($edit_category['name']) : ''; ?>" 
                                           placeholder="Enter category name" required>
                                </div>
                                
                                <div class="btn-group">
                                    <?php if($edit_category): ?>
                                        <input type="hidden" name="category_id" value="<?php echo $edit_category['id']; ?>">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-1"></i>Update Category
                                        </button>
                                        <a href="categories.php" class="btn btn-secondary">
                                            <i class="fas fa-times me-1"></i>Cancel
                                        </a>
                                    <?php else: ?>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-plus me-1"></i>Add Category
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Categories Table -->
                    <div class="categories-table">
                        <?php if(empty($categories)): ?>
                            <div class="empty-state">
                                <i class="fas fa-tags fa-3x mb-3"></i>
                                <h5>No categories yet</h5>
                                <p>Start by creating your first category!</p>
                            </div>
                        <?php else: ?>
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Category Name</th>
                                        <th>Slug</th>
                                        <th>Posts Count</th>
                                        <th>Created Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($categories as $category): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($category['name']); ?></strong>
                                            </td>
                                            <td>
                                                <code class="text-muted"><?php echo htmlspecialchars($category['slug']); ?></code>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo $category['post_count'] > 0 ? 'primary' : 'secondary'; ?>">
                                                    <?php echo $category['post_count']; ?> posts
                                                </span>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($category['created_at'])); ?></td>
                                            <td>
                                                <div class="action-buttons">
                                                    <a href="?edit=<?php echo $category['id']; ?>" 
                                                       class="btn btn-sm btn-outline-primary" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <?php if($category['post_count'] == 0): ?>
                                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                                                onclick="confirmDelete(<?php echo $category['id']; ?>, '<?php echo htmlspecialchars(addslashes($category['name'])); ?>')" 
                                                                title="Delete">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    <?php endif; ?>
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
                    <p>Are you sure you want to delete "<span id="categoryName"></span>"?</p>
                    <p class="text-danger"><small>This action cannot be undone.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="category_id" id="categoryId">
                        <button type="submit" name="delete_category" class="btn btn-danger">Delete Category</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmDelete(categoryId, categoryName) {
            document.getElementById('categoryId').value = categoryId;
            document.getElementById('categoryName').textContent = categoryName;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }
    </script>
</body>
</html>
