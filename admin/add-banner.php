<?php
/**
 * Add Banner Page
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

// Check if banners table exists, create if needed
$tableExists = false;
try {
    $check = $conn->query("SHOW TABLES LIKE 'banners'")->fetch();
    if ($check) {
        $tableExists = true;
    }
} catch (Exception $e) {}

if (!$tableExists) {
    try {
        $conn->exec("CREATE TABLE IF NOT EXISTS `banners` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `image` varchar(255) NOT NULL,
            `title` varchar(255) DEFAULT NULL,
            `subtitle` varchar(500) DEFAULT NULL,
            `button_text` varchar(100) DEFAULT 'Learn More',
            `button_link` varchar(255) DEFAULT '#',
            `status` enum('active','inactive') DEFAULT 'active',
            `sort_order` int(11) DEFAULT 0,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $tableExists = true;
    } catch (Exception $e) {}
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title'] ?? '');
    $subtitle    = trim($_POST['subtitle'] ?? '');
    $button_text = trim($_POST['button_text'] ?? 'Learn More');
    $button_link = trim($_POST['button_link'] ?? '#');
    $status      = $_POST['status'] ?? 'active';
    $sort_order  = (int)($_POST['sort_order'] ?? 0);
    $filename    = '';

    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $error = 'Please upload a banner image';
    } else {
        $file = $_FILES['image'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 5 * 1024 * 1024;

        if (!in_array($file['type'], $allowedTypes)) {
            $error = 'Invalid file type. Only JPG, PNG, GIF, WEBP allowed';
        } elseif ($file['size'] > $maxSize) {
            $error = 'File too large. Maximum 5MB allowed';
        } else {
            $uploadDir = __DIR__ . '/../uploads/banners/';
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
        $stmt = $conn->prepare("INSERT INTO banners (image, title, subtitle, button_text, button_link, status, sort_order) VALUES (?,?,?,?,?,?,?)");
        if ($stmt->execute([$filename, $title, $subtitle, $button_text, $button_link, $status, $sort_order])) {
            header('Location: banners.php?added=1');
            exit;
        } else {
            $error = 'Failed to save banner to database';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Banner | Sri Lakshmi Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <!-- Header -->
    <header class="admin-header">
        <div class="header-left">
            <h1 class="admin-title">Add Banner</h1>
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
        <a href="banners.php" class="nav-item active">
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

        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-plus-circle"></i> Banner Information</h2>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data" class="product-form">

                    <div class="form-group">
                        <label for="image">Banner Image *</label>
                        <div class="file-upload-wrapper">
                            <input type="file" id="image" name="image" accept="image/*" required>
                            <div class="file-upload-box" id="dropZone">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <span>Upload Banner Image</span>
                                <small>Click to browse or drag and drop</small>
                                <small>JPG, PNG, GIF, WEBP (Max 5MB) — Recommended: 1920×600px</small>
                            </div>
                            <div class="image-preview" id="imagePreview"></div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="title">Title (Optional)</label>
                        <input type="text" id="title" name="title" class="form-control"
                               placeholder="e.g. Best Flex Printing in Kovilpatti"
                               value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>">
                        <small class="form-hint">Displayed as heading over the banner image</small>
                    </div>

                    <div class="form-group">
                        <label for="subtitle">Subtitle (Optional)</label>
                        <input type="text" id="subtitle" name="subtitle" class="form-control"
                               placeholder="e.g. Quality you can trust"
                               value="<?php echo htmlspecialchars($_POST['subtitle'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="button_text">Button Text (Optional)</label>
                        <input type="text" id="button_text" name="button_text" class="form-control"
                               placeholder="e.g. Get Started"
                               value="<?php echo htmlspecialchars($_POST['button_text'] ?? 'Learn More'); ?>">
                    </div>

                    <div class="form-group">
                        <label for="button_link">Button Link (Optional)</label>
                        <input type="text" id="button_link" name="button_link" class="form-control"
                               placeholder="e.g. contact.html"
                               value="<?php echo htmlspecialchars($_POST['button_link'] ?? '#'); ?>">
                        <small class="form-hint">Use relative links like contact.html, service.html, or # to hide button</small>
                    </div>

                    <div class="form-group">
                        <label for="sort_order">Display Order</label>
                        <input type="number" id="sort_order" name="sort_order" class="form-control"
                               placeholder="0" min="0" value="<?php echo (int)($_POST['sort_order'] ?? 0); ?>">
                        <small class="form-hint">Lower number = shown first. Use 0, 1, 2... to order banners</small>
                    </div>

                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status" class="form-control">
                            <option value="active" <?php echo (!isset($_POST['status']) || $_POST['status'] === 'active') ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo (isset($_POST['status']) && $_POST['status'] === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Add Banner
                        </button>
                        <a href="banners.php" class="btn btn-secondary">
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
