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

// Get post ID from URL
$post_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if($post_id == 0) {
    header("Location: manage-posts.php");
    exit();
}

// Get post data
$stmt = $db->prepare("SELECT * FROM blog_posts WHERE id = ?");
$stmt->bindParam(1, $post_id);
$stmt->execute();
$post = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$post) {
    header("Location: manage-posts.php");
    exit();
}

// Get categories for dropdown
$stmt = $db->query("SELECT id, name FROM blog_categories ORDER BY name");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $content = trim($_POST['content']);
    $category_id = $_POST['category_id'];
    $status = $_POST['status'];
    
    // Validate inputs
    if(empty($title) || empty($description) || empty($content) || empty($category_id)) {
        $error = 'Please fill in all required fields.';
    } else {
        // Handle image upload (optional)
        $image_path = $post['image_path']; // Keep existing image by default
        
        if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $file_type = $_FILES['image']['type'];
            
            if(in_array($file_type, $allowed_types)) {
                $upload_dir = '../uploads/';
                if(!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $file_name = uniqid() . '.' . $file_extension;
                $upload_path = $upload_dir . $file_name;
                
                if(move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                    // Delete old image if it exists
                    if(!empty($post['image_path'])) {
                        $old_image_path = '../' . $post['image_path'];
                        if(file_exists($old_image_path)) {
                            unlink($old_image_path);
                        }
                    }
                    $image_path = 'uploads/' . $file_name;
                } else {
                    $error = 'Failed to upload new image.';
                }
            } else {
                $error = 'Invalid image type. Please upload JPEG, PNG, GIF, or WebP images.';
            }
        }
        
        if(empty($error)) {
            // Generate slug from title
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
            
            try {
                $query = "UPDATE blog_posts SET title = ?, slug = ?, description = ?, content = ?, 
                          category_id = ?, image_path = ?, status = ? WHERE id = ?";
                $stmt = $db->prepare($query);
                $stmt->bindParam(1, $title);
                $stmt->bindParam(2, $slug);
                $stmt->bindParam(3, $description);
                $stmt->bindParam(4, $content);
                $stmt->bindParam(5, $category_id);
                $stmt->bindParam(6, $image_path);
                $stmt->bindParam(7, $status);
                $stmt->bindParam(8, $post_id);
                
                if($stmt->execute()) {
                    $message = 'Blog post updated successfully!';
                    // Update local post data
                    $post['title'] = $title;
                    $post['description'] = $description;
                    $post['content'] = $content;
                    $post['category_id'] = $category_id;
                    $post['image_path'] = $image_path;
                    $post['status'] = $status;
                } else {
                    $error = 'Failed to update blog post.';
                }
            } catch(PDOException $e) {
                $error = 'Database error: ' . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Post - Admin Panel</title>
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
        }
        .form-control, .form-select {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 1rem;
        }
        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .image-preview {
            max-width: 300px;
            max-height: 200px;
            border-radius: 10px;
            border: 2px dashed #e9ecef;
            padding: 1rem;
            text-align: center;
            margin-top: 1rem;
        }
        .image-preview img {
            max-width: 100%;
            max-height: 150px;
            border-radius: 8px;
        }
        .current-image {
            margin-bottom: 1rem;
        }
        .current-image img {
            max-width: 200px;
            max-height: 150px;
            border-radius: 8px;
            border: 2px solid #e9ecef;
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
                        <h4 class="mb-0">Edit Blog Post</h4>
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
                    <div class="form-container">
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
                        
                        <form method="POST" action="" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="title" class="form-label">Post Title *</label>
                                        <input type="text" class="form-control" id="title" name="title" 
                                               value="<?php echo htmlspecialchars($post['title']); ?>" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Short Description *</label>
                                        <textarea class="form-control" id="description" name="description" rows="3" 
                                                  required><?php echo htmlspecialchars($post['description']); ?></textarea>
                                        <div class="form-text">A brief summary of your blog post (max 200 characters)</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="content" class="form-label">Blog Content *</label>
                                        <textarea class="form-control" id="content" name="content" rows="12" 
                                                  required><?php echo htmlspecialchars($post['content']); ?></textarea>
                                        <div class="form-text">Write your full blog post content here</div>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="category_id" class="form-label">Category *</label>
                                        <select class="form-select" id="category_id" name="category_id" required>
                                            <option value="">Select Category</option>
                                            <?php foreach($categories as $category): ?>
                                                <option value="<?php echo $category['id']; ?>" 
                                                        <?php echo $post['category_id'] == $category['id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($category['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="image" class="form-label">Featured Image</label>
                                        <input type="file" class="form-control" id="image" name="image" 
                                               accept="image/*" onchange="previewImage(this)">
                                        <div class="form-text">Leave empty to keep current image</div>
                                        
                                        <!-- Current Image -->
                                        <?php if(!empty($post['image_path'])): ?>
                                            <div class="current-image">
                                                <label class="form-label">Current Image:</label>
                                                <img src="../<?php echo htmlspecialchars($post['image_path']); ?>" 
                                                     alt="Current image">
                                            </div>
                                        <?php endif; ?>
                                        
                                        <!-- New Image Preview -->
                                        <div class="image-preview" id="imagePreview" style="display: none;">
                                            <label class="form-label">New Image Preview:</label>
                                            <div id="previewContent"></div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="status" class="form-label">Status</label>
                                        <select class="form-select" id="status" name="status">
                                            <option value="draft" <?php echo $post['status'] == 'draft' ? 'selected' : ''; ?>>Draft</option>
                                            <option value="published" <?php echo $post['status'] == 'published' ? 'selected' : ''; ?>>Published</option>
                                        </select>
                                    </div>
                                    
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i>Update Post
                                        </button>
                                        <a href="manage-posts.php" class="btn btn-outline-secondary">
                                            <i class="fas fa-arrow-left me-2"></i>Back to Posts
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function previewImage(input) {
            const preview = document.getElementById('imagePreview');
            const previewContent = document.getElementById('previewContent');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    previewContent.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
                    preview.style.display = 'block';
                }
                
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.style.display = 'none';
            }
        }
    </script>
</body>
</html>
