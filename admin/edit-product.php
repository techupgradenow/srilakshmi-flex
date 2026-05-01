<?php
/**
 * Edit Product Page
 * Sri Lakshmi Admin Panel
 */

require_once 'auth.php';
requireLogin();

require_once __DIR__ . '/../config/database.php';

$conn = getConnection();

// Get product ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    header('Location: products.php');
    exit;
}

// Get product data
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: products.php');
    exit;
}

// Get all categories
$categories = $conn->query("SELECT * FROM categories WHERE status = 'active' ORDER BY display_order ASC")->fetchAll();

$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $category_id = $_POST['category_id'] ?? null;
    $description = trim($_POST['description'] ?? '');
    $size = trim($_POST['size'] ?? '');
    $price = trim($_POST['price'] ?? '0.00');
    $status = $_POST['status'] ?? 'active';
    $image_path = trim($_POST['image_path'] ?? '');

    // Validate required fields
    if (empty($name)) {
        $error = 'Product name is required';
    } elseif (empty($category_id)) {
        $error = 'Please select a category';
    } elseif (empty($description)) {
        $error = 'Product description is required';
    } elseif (!is_numeric($price) || $price < 0) {
        $error = 'Please enter a valid price';
    } else {
        $filename = $product['image']; // Keep existing image by default

        // Check if new file was uploaded
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
                $uploadDir = __DIR__ . '/../uploads/products/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                // Generate unique filename
                $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $newFilename = time() . '_' . uniqid() . '.' . $extension;
                $filepath = $uploadDir . $newFilename;

                if (move_uploaded_file($file['tmp_name'], $filepath)) {
                    // Delete old image if exists in uploads folder
                    $oldImagePath = $uploadDir . $product['image'];
                    if (file_exists($oldImagePath) && $product['image']) {
                        unlink($oldImagePath);
                    }
                    $filename = $newFilename;
                } else {
                    $error = 'Failed to upload image';
                }
            }
        } elseif (!empty($image_path) && $image_path !== $product['image']) {
            // Use manual image path if changed
            $filename = $image_path;
        }

        // Update product if no error
        if (empty($error)) {
            $stmt = $conn->prepare("
                UPDATE products
                SET name = ?, category_id = ?, description = ?, image = ?, size = ?, price = ?, status = ?
                WHERE id = ?
            ");

            if ($stmt->execute([$name, $category_id, $description, $filename, $size, $price, $status, $id])) {
                header('Location: products.php?updated=1');
                exit;
            } else {
                $error = 'Failed to update product';
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
    <title>Edit Product | Sri Lakshmi Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <!-- Header -->
    <header class="admin-header">
        <div class="header-left">
            <h1 class="admin-title">Edit Product</h1>
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
        <a href="categories.php" class="nav-item">
            <i class="fas fa-list"></i> Categories
        </a>
        <a href="products.php" class="nav-item active">
            <i class="fas fa-box"></i> Products
        </a>
        <a href="banners.php" class="nav-item">
            <i class="fas fa-images"></i> Banners
        </a>
        <a href="ourwork.php" class="nav-item">
            <i class="fas fa-briefcase"></i> Our Work
        </a>
        <a href="enquiries.php" class="nav-item">
            <i class="fas fa-envelope"></i> Enquiries
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

        <!-- Edit Product Card -->
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-edit"></i> Edit Product Information</h2>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data" class="product-form">
                    <div class="form-group">
                        <label for="name">Product Name *</label>
                        <input type="text" id="name" name="name" class="form-control"
                               placeholder="Enter product name" required
                               value="<?php echo htmlspecialchars($product['name']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="category_id">Category *</label>
                        <select id="category_id" name="category_id" class="form-control" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>"
                                    <?php echo ($product['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="description">Description *</label>
                        <textarea id="description" name="description" class="form-control" rows="4"
                                  placeholder="Enter product description" required><?php echo htmlspecialchars($product['description']); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="image">Product Image</label>
                        <?php if ($product['image']): ?>
                            <div class="current-image">
                                <p><strong>Current Image:</strong></p>
                                <?php if (file_exists(__DIR__ . '/../uploads/products/' . $product['image'])): ?>
                                    <img src="../uploads/products/<?php echo htmlspecialchars($product['image']); ?>" alt="Current">
                                <?php elseif (file_exists(__DIR__ . '/../' . $product['image'])): ?>
                                    <img src="../<?php echo htmlspecialchars($product['image']); ?>" alt="Current">
                                <?php else: ?>
                                    <p class="text-muted">Image file not found: <?php echo htmlspecialchars($product['image']); ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        <div class="file-upload-wrapper">
                            <input type="file" id="image" name="image" accept="image/*">
                            <div class="file-upload-box" id="dropZone">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <span>Upload New Image</span>
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
                               placeholder="images/product-name.jpg"
                               value="<?php echo htmlspecialchars($product['image']); ?>">
                        <small class="form-hint">Enter relative path from frontend folder (e.g., images/product-name.jpg)</small>
                    </div>

                    <div class="form-group">
                        <label for="size">Size (Optional)</label>
                        <input type="text" id="size" name="size" class="form-control"
                               placeholder="10x12 inches"
                               value="<?php echo htmlspecialchars($product['size'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="price">Price (₹) *</label>
                        <input type="number" id="price" name="price" class="form-control"
                               placeholder="Enter price" step="0.01" min="0" required
                               value="<?php echo htmlspecialchars($product['price'] ?? '0.00'); ?>">
                        <small class="form-hint">Enter product price in rupees (e.g., 499.00)</small>
                    </div>

                    <div class="form-group">
                        <label for="status">Status *</label>
                        <select id="status" name="status" class="form-control" required>
                            <option value="active" <?php echo ($product['status'] === 'active') ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo ($product['status'] === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Product
                        </button>
                        <a href="products.php" class="btn btn-secondary">
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
