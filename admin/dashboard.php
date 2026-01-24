<?php
/**
 * Admin Dashboard
 */

require_once 'auth.php';
requireLogin();

require_once __DIR__ . '/../config/database.php';

$success = '';
$error = '';

// Handle product upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $productName = trim($_POST['product_name'] ?? '');

    // Validate product name
    if (empty($productName)) {
        $error = 'Please enter product name';
    }
    // Validate image upload
    elseif (!isset($_FILES['product_image']) || $_FILES['product_image']['error'] !== UPLOAD_ERR_OK) {
        $error = 'Please select an image to upload';
    } else {
        $file = $_FILES['product_image'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 5 * 1024 * 1024; // 5MB

        // Validate file type
        if (!in_array($file['type'], $allowedTypes)) {
            $error = 'Invalid file type. Only JPG, PNG, GIF, WEBP allowed';
        }
        // Validate file size
        elseif ($file['size'] > $maxSize) {
            $error = 'File too large. Maximum size is 5MB';
        } else {
            // Create uploads directory if not exists
            $uploadDir = __DIR__ . '/../uploads/products/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = time() . '_' . uniqid() . '.' . $extension;
            $filepath = $uploadDir . $filename;

            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                // Save to database
                $conn = getConnection();
                $stmt = $conn->prepare("INSERT INTO products (name, image) VALUES (?, ?)");

                if ($stmt->execute([$productName, $filename])) {
                    $success = 'Product added successfully!';
                } else {
                    $error = 'Failed to save product to database';
                    unlink($filepath); // Remove uploaded file
                }
            } else {
                $error = 'Failed to upload image';
            }
        }
    }
}

// Get all products
$conn = getConnection();
$products = $conn->query("SELECT * FROM products ORDER BY created_at DESC")->fetchAll();
$productCount = count($products);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | ProPrint Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <i class="fas fa-print"></i>
                <span>ProPrint Admin</span>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item active">
                    <i class="fas fa-home"></i> Dashboard
                </a>
                <a href="#add-product" class="nav-item">
                    <i class="fas fa-plus-circle"></i> Add Product
                </a>
                <a href="#products" class="nav-item">
                    <i class="fas fa-box"></i> View Products
                </a>
                <a href="../index.html" class="nav-item" target="_blank">
                    <i class="fas fa-external-link-alt"></i> View Website
                </a>
                <a href="logout.php" class="nav-item logout">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="top-bar">
                <h1>Dashboard</h1>
                <div class="user-info">
                    <i class="fas fa-user-circle"></i>
                    <span><?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                </div>
            </header>

            <div class="content">
                <!-- Stats Card -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-box"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $productCount; ?></h3>
                            <p>Total Products</p>
                        </div>
                    </div>
                </div>

                <!-- Alerts -->
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <!-- Add Product Form -->
                <div class="card" id="add-product">
                    <div class="card-header">
                        <h2><i class="fas fa-plus-circle"></i> Add New Product</h2>
                    </div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data" class="product-form">
                            <div class="form-group">
                                <label for="product_name">Product Name *</label>
                                <input type="text" id="product_name" name="product_name" placeholder="Enter product name" required>
                            </div>

                            <div class="form-group">
                                <label for="product_image">Product Image *</label>
                                <div class="file-upload">
                                    <input type="file" id="product_image" name="product_image" accept="image/*" required>
                                    <div class="file-upload-label">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                        <span>Choose image or drag here</span>
                                        <small>JPG, PNG, GIF, WEBP (Max 5MB)</small>
                                    </div>
                                    <div class="image-preview" id="imagePreview"></div>
                                </div>
                            </div>

                            <button type="submit" name="add_product" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Add Product
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Products List -->
                <div class="card" id="products">
                    <div class="card-header">
                        <h2><i class="fas fa-box"></i> All Products</h2>
                    </div>
                    <div class="card-body">
                        <?php if (empty($products)): ?>
                            <div class="empty-state">
                                <i class="fas fa-box-open"></i>
                                <p>No products added yet</p>
                            </div>
                        <?php else: ?>
                            <div class="products-grid">
                                <?php foreach ($products as $product): ?>
                                    <div class="product-card">
                                        <div class="product-image">
                                            <img src="../uploads/products/<?php echo htmlspecialchars($product['image']); ?>"
                                                 alt="<?php echo htmlspecialchars($product['name']); ?>">
                                        </div>
                                        <div class="product-info">
                                            <h4><?php echo htmlspecialchars($product['name']); ?></h4>
                                            <small>Added: <?php echo date('M d, Y', strtotime($product['created_at'])); ?></small>
                                        </div>
                                        <a href="delete-product.php?id=<?php echo $product['id']; ?>"
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Are you sure you want to delete this product?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Image preview
        document.getElementById('product_image').addEventListener('change', function(e) {
            const preview = document.getElementById('imagePreview');
            const file = e.target.files[0];

            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = '<img src="' + e.target.result + '" alt="Preview">';
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>
