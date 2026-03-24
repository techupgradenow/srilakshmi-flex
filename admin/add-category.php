<?php
/**
 * Add Category Page
 * Sri Lakshmi Admin Panel
 */

require_once 'auth.php';
requireLogin();

require_once __DIR__ . '/../config/database.php';

$conn = getConnection();

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $status = $_POST['status'] ?? 'active';
    $image_path = trim($_POST['image_path'] ?? '');

    // Validate required fields
    if (empty($name)) {
        $error = 'Category name is required';
    } else {
        // Check if category already exists
        $stmt = $conn->prepare("SELECT id FROM categories WHERE name = ?");
        $stmt->execute([$name]);

        if ($stmt->fetch()) {
            $error = 'A category with this name already exists';
        } else {
            $filename = '';

            // Check if file was uploaded
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['image'];
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                $maxSize = 5 * 1024 * 1024; // 5MB

                if (!in_array($file['type'], $allowedTypes)) {
                    $error = 'Invalid file type. Only JPG, PNG, GIF, WEBP allowed';
                } elseif ($file['size'] > $maxSize) {
                    $error = 'File too large. Maximum size is 5MB';
                } else {
                    // Create uploads directory if not exists
                    $uploadDir = __DIR__ . '/../uploads/categories/';
                    if (!file_exists($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }

                    // Generate unique filename
                    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $filename = time() . '_' . uniqid() . '.' . $extension;
                    $filepath = $uploadDir . $filename;

                    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                        $error = 'Failed to upload image';
                    }
                }
            } elseif (!empty($image_path)) {
                // Use manual image path
                $filename = $image_path;
            }

            // Insert category if no error
            if (empty($error)) {
                // Get the next display order (max + 1)
                $maxOrder = $conn->query("SELECT COALESCE(MAX(display_order), 0) FROM categories")->fetchColumn();
                $displayOrder = $maxOrder + 1;

                $stmt = $conn->prepare("INSERT INTO categories (name, image, status, display_order) VALUES (?, ?, ?, ?)");

                if ($stmt->execute([$name, $filename, $status, $displayOrder])) {
                    header('Location: categories.php?added=1');
                    exit;
                } else {
                    $error = 'Failed to add category';
                }
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
    <title>Add Category | Sri Lakshmi Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <!-- Header -->
    <header class="admin-header">
        <div class="header-left">
            <h1 class="admin-title">Add Category</h1>
        </div>
        <div class="header-right">
            <a href="../index.html" target="_blank" class="btn btn-outline">
                <i class="fas fa-home"></i> View Site
            </a>
            <a href="logout.php" class="btn btn-danger">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </header>

    <!-- Navigation -->
    <nav class="admin-nav">
        <a href="index.php" class="nav-item">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
        <a href="categories.php" class="nav-item active">
            <i class="fas fa-list"></i> Categories
        </a>
        <a href="products.php" class="nav-item">
            <i class="fas fa-box"></i> Products
        </a>
        <a href="banners.php" class="nav-item">
            <i class="fas fa-images"></i> Banners
        </a>
    </nav>

    <!-- Main Content -->
    <main class="admin-main">
        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- Add Category Card -->
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-plus-circle"></i> Category Information</h2>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data" class="product-form">
                    <div class="form-group">
                        <label for="name">Category Name *</label>
                        <input type="text" id="name" name="name" class="form-control"
                               placeholder="Enter category name (e.g., Wedding Cards, Flex Banners)" required
                               value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="image">Category Image</label>
                        <div class="file-upload-wrapper">
                            <input type="file" id="image" name="image" accept="image/*">
                            <div class="file-upload-box" id="dropZone">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <span>Upload Category Image</span>
                                <small>Click to browse or drag and drop</small>
                                <small>JPG, PNG, GIF, WEBP (Max 5MB)</small>
                            </div>
                            <div class="image-preview" id="imagePreview"></div>
                        </div>
                    </div>

                    <div class="form-divider">
                        <span>OR enter image path manually</span>
                    </div>

                    <div class="form-group">
                        <input type="text" id="image_path" name="image_path" class="form-control"
                               placeholder="images/category-name.jpg"
                               value="<?php echo htmlspecialchars($_POST['image_path'] ?? ''); ?>">
                        <small class="form-hint">Enter relative path from frontend folder (e.g., images/category-name.jpg)</small>
                    </div>

                    <div class="form-group">
                        <label for="status">Status *</label>
                        <select id="status" name="status" class="form-control" required>
                            <option value="active" <?php echo (!isset($_POST['status']) || $_POST['status'] === 'active') ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo (isset($_POST['status']) && $_POST['status'] === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                        <small class="form-hint">Inactive categories won't be shown in product dropdown</small>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Add Category
                        </button>
                        <a href="categories.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script src="js/admin.js"></script>
    <script>
        // File upload preview
        const imageInput = document.getElementById('image');
        const dropZone = document.getElementById('dropZone');
        const preview = document.getElementById('imagePreview');

        imageInput.addEventListener('change', function(e) {
            handleFile(e.target.files[0]);
        });

        dropZone.addEventListener('click', () => imageInput.click());

        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('dragover');
        });

        dropZone.addEventListener('dragleave', () => {
            dropZone.classList.remove('dragover');
        });

        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('dragover');
            const file = e.dataTransfer.files[0];
            if (file && file.type.startsWith('image/')) {
                imageInput.files = e.dataTransfer.files;
                handleFile(file);
            }
        });

        function handleFile(file) {
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = '<img src="' + e.target.result + '" alt="Preview"><button type="button" class="remove-preview" onclick="removePreview()"><i class="fas fa-times"></i></button>';
                    preview.style.display = 'block';
                    dropZone.style.display = 'none';
                }
                reader.readAsDataURL(file);
            }
        }

        function removePreview() {
            preview.style.display = 'none';
            dropZone.style.display = 'flex';
            imageInput.value = '';
            preview.innerHTML = '';
        }
    </script>
</body>
</html>
