<?php
/**
 * Add Work Image Page
 * Sri Lakshmi Admin Panel
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

require_once 'auth.php';
requireLogin();

require_once __DIR__ . '/../config/database.php';

$conn = getConnection();

// Check if work_images table exists, create if needed
try {
    $check = $conn->query("SHOW TABLES LIKE 'work_images'")->fetch();
    if ($check) {
        $cols = $conn->query("SHOW COLUMNS FROM work_images")->fetchAll(PDO::FETCH_COLUMN);
        $fixes = [
            'category_id' => "ALTER TABLE `work_images` ADD COLUMN `category_id` int(11) NOT NULL",
            'image'       => "ALTER TABLE `work_images` ADD COLUMN `image` varchar(255) NOT NULL",
            'title'       => "ALTER TABLE `work_images` ADD COLUMN `title` varchar(255) DEFAULT NULL",
            'sort_order'  => "ALTER TABLE `work_images` ADD COLUMN `sort_order` int(11) DEFAULT 0",
            'created_at'  => "ALTER TABLE `work_images` ADD COLUMN `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP",
        ];
        foreach ($fixes as $col => $sql) {
            if (!in_array($col, $cols)) {
                try { $conn->exec($sql); } catch (Exception $e) {}
            }
        }
    } else {
        $conn->exec("CREATE TABLE IF NOT EXISTS `work_images` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `category_id` int(11) NOT NULL,
            `image` varchar(255) NOT NULL,
            `title` varchar(255) DEFAULT NULL,
            `sort_order` int(11) DEFAULT 0,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }
} catch (Exception $e) {}

$category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
if (!$category_id) {
    header('Location: ourwork.php');
    exit;
}

// Load category name for display
$catStmt = $conn->prepare("SELECT * FROM work_categories WHERE id = ?");
$catStmt->execute([$category_id]);
$category = $catStmt->fetch();

if (!$category) {
    header('Location: ourwork.php?error=category_not_found');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title      = trim($_POST['title'] ?? '');
    $sort_order = (int)($_POST['sort_order'] ?? 0);
    $category_id = (int)($_POST['category_id'] ?? $category_id);
    $filename   = '';

    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $error = 'Please upload an image';
    } else {
        $file = $_FILES['image'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 5 * 1024 * 1024;

        if (!in_array($file['type'], $allowedTypes)) {
            $error = 'Invalid file type. Only JPG, PNG, GIF, WEBP allowed';
        } elseif ($file['size'] > $maxSize) {
            $error = 'File too large. Maximum 5MB allowed';
        } else {
            $uploadDir = __DIR__ . '/../uploads/work/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $filename = time() . '_' . uniqid() . '.' . $ext;
            if (!move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
                $error = 'Failed to upload image. Check folder permissions.';
                $filename = '';
            }
        }
    }

    if (empty($error) && !empty($filename)) {
        try {
            $stmt = $conn->prepare("INSERT INTO work_images (category_id, image, title, sort_order) VALUES (?,?,?,?)");
            if ($stmt->execute([$category_id, $filename, $title, $sort_order])) {
                header('Location: ourwork.php?img_added=1');
                exit;
            } else {
                $error = 'Failed to save image to database';
            }
        } catch (Exception $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Work Image | Sri Lakshmi Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <!-- Header -->
    <header class="admin-header">
        <div class="header-left">
            <h1 class="admin-title">Add Work Image</h1>
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
        <a href="products.php" class="nav-item">
            <i class="fas fa-box"></i> Products
        </a>
        <a href="banners.php" class="nav-item">
            <i class="fas fa-images"></i> Banners
        </a>
        <a href="ourwork.php" class="nav-item active">
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

        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-plus-circle"></i> Add Image to "<?php echo htmlspecialchars($category['name']); ?>"</h2>
                <a href="ourwork.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data" class="product-form">
                    <input type="hidden" name="category_id" value="<?php echo (int)$category_id; ?>">

                    <div class="form-group">
                        <label for="image">Image *</label>
                        <div class="file-upload-wrapper">
                            <input type="file" id="image" name="image" accept="image/*" required>
                            <div class="file-upload-box" id="dropZone">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <span>Upload Work Image</span>
                                <small>Click to browse or drag and drop</small>
                                <small>JPG, PNG, GIF, WEBP (Max 5MB)</small>
                            </div>
                            <div class="image-preview" id="imagePreview"></div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="title">Title (Optional)</label>
                        <input type="text" id="title" name="title" class="form-control"
                               placeholder="e.g. Wedding Banner for Client"
                               value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>">
                        <small class="form-hint">Optional caption for the image</small>
                    </div>

                    <div class="form-group">
                        <label for="sort_order">Display Order</label>
                        <input type="number" id="sort_order" name="sort_order" class="form-control"
                               placeholder="0" min="0" value="<?php echo (int)($_POST['sort_order'] ?? 0); ?>">
                        <small class="form-hint">Lower number = shown first</small>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Add Image
                        </button>
                        <a href="ourwork.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script src="js/admin.js"></script>
    <script>
        const imageInput = document.getElementById('image');
        const dropZone   = document.getElementById('dropZone');
        const preview    = document.getElementById('imagePreview');

        imageInput.addEventListener('change', function(e) { handleFile(e.target.files[0]); });
        dropZone.addEventListener('click', () => imageInput.click());

        dropZone.addEventListener('dragover', (e) => { e.preventDefault(); dropZone.classList.add('dragover'); });
        dropZone.addEventListener('dragleave', () => dropZone.classList.remove('dragover'));
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
            if (!file) return;
            const reader = new FileReader();
            reader.onload = (e) => {
                preview.innerHTML = '<img src="' + e.target.result + '" alt="Preview" style="max-height:200px;border-radius:8px;"><button type="button" class="remove-preview" onclick="removePreview()"><i class="fas fa-times"></i></button>';
                preview.style.display = 'block';
                dropZone.style.display = 'none';
            };
            reader.readAsDataURL(file);
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
