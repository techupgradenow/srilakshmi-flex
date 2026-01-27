<?php
/**
 * Edit Category Page
 * Sri Lakshmi Admin Panel
 */

require_once 'auth.php';
requireLogin();

require_once __DIR__ . '/../config/database.php';

$conn = getConnection();

$error = '';
$success = '';

// Get category ID
$categoryId = $_GET['id'] ?? null;

if (!$categoryId) {
    header('Location: categories.php');
    exit;
}

// Fetch category details
$stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
$stmt->execute([$categoryId]);
$category = $stmt->fetch();

if (!$category) {
    header('Location: categories.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $status = $_POST['status'] ?? 'active';
    $image_path = trim($_POST['image_path'] ?? '');
    $remove_image = isset($_POST['remove_image']);

    // Validate required fields
    if (empty($name)) {
        $error = 'Category name is required';
    } else {
        // Check if another category with same name exists
        $stmt = $conn->prepare("SELECT id FROM categories WHERE name = ? AND id != ?");
        $stmt->execute([$name, $categoryId]);

        if ($stmt->fetch()) {
            $error = 'A category with this name already exists';
        } else {
            $filename = $category['image']; // Keep existing image by default

            // Handle image removal
            if ($remove_image) {
                // Delete old image file
                if ($filename && file_exists(__DIR__ . '/../uploads/categories/' . $filename)) {
                    unlink(__DIR__ . '/../uploads/categories/' . $filename);
                }
                $filename = '';
            }
            // Check if new file was uploaded
            elseif (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['image'];
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                $maxSize = 5 * 1024 * 1024; // 5MB

                if (!in_array($file['type'], $allowedTypes)) {
                    $error = 'Invalid file type. Only JPG, PNG, GIF, WEBP allowed';
                } elseif ($file['size'] > $maxSize) {
                    $error = 'File too large. Maximum size is 5MB';
                } else {
                    // Delete old image if exists
                    if ($filename && file_exists(__DIR__ . '/../uploads/categories/' . $filename)) {
                        unlink(__DIR__ . '/../uploads/categories/' . $filename);
                    }

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
            }
            // Check if manual image path was provided
            elseif (!empty($image_path) && $image_path != $category['image']) {
                // Delete old uploaded image if exists
                if ($filename && file_exists(__DIR__ . '/../uploads/categories/' . $filename)) {
                    unlink(__DIR__ . '/../uploads/categories/' . $filename);
                }
                $filename = $image_path;
            }

            // Update category if no error
            if (empty($error)) {
                $stmt = $conn->prepare("UPDATE categories SET name = ?, image = ?, status = ? WHERE id = ?");

                if ($stmt->execute([$name, $filename, $status, $categoryId])) {
                    $success = 'Category updated successfully!';
                    // Refresh category data
                    $category['name'] = $name;
                    $category['image'] = $filename;
                    $category['status'] = $status;
                } else {
                    $error = 'Failed to update category';
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
    <title>Edit Category | Sri Lakshmi Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <!-- Header -->
    <header class="admin-header">
        <div class="header-left">
            <h1 class="admin-title">Edit Category</h1>
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
    </nav>

    <!-- Main Content -->
    <main class="admin-main">
        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <!-- Edit Category Card -->
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-edit"></i> Edit Category Information</h2>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data" class="product-form">
                    <div class="form-group">
                        <label for="name">Category Name *</label>
                        <input type="text" id="name" name="name" class="form-control"
                               placeholder="Enter category name" required
                               value="<?php echo htmlspecialchars($category['name']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="image">Category Image</label>

                        <?php if (!empty($category['image'])): ?>
                            <div class="current-image" style="margin-bottom: 1rem;">
                                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #666;">Current Image:</label>
                                <?php
                                $imagePath = '';
                                if (file_exists(__DIR__ . '/../uploads/categories/' . $category['image'])) {
                                    $imagePath = '../uploads/categories/' . $category['image'];
                                } elseif (file_exists(__DIR__ . '/../' . $category['image'])) {
                                    $imagePath = '../' . $category['image'];
                                }
                                ?>
                                <?php if ($imagePath): ?>
                                    <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="Current category image"
                                         style="max-width: 200px; max-height: 200px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                                    <div style="margin-top: 0.75rem;">
                                        <label style="display: flex; align-items: center; gap: 0.5rem; font-weight: normal; cursor: pointer;">
                                            <input type="checkbox" name="remove_image" value="1">
                                            <span style="color: #dc3545;">Remove current image</span>
                                        </label>
                                    </div>
                                <?php else: ?>
                                    <p style="color: #999; font-size: 0.9rem;">Image path: <?php echo htmlspecialchars($category['image']); ?> (file not found)</p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <div class="file-upload-wrapper">
                            <input type="file" id="image" name="image" accept="image/*">
                            <div class="file-upload-box" id="dropZone">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <span>Upload New Category Image</span>
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
                               value="<?php echo htmlspecialchars($category['image'] ?? ''); ?>">
                        <small class="form-hint">Enter relative path from frontend folder (e.g., images/category-name.jpg)</small>
                    </div>

                    <div class="form-group">
                        <label for="status">Status *</label>
                        <select id="status" name="status" class="form-control" required>
                            <option value="active" <?php echo $category['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo $category['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                        <small class="form-hint">Inactive categories won't be shown in product dropdown</small>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Category
                        </button>
                        <a href="categories.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Categories
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
