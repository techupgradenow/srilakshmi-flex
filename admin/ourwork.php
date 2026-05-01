<?php
/**
 * Our Work Management Page
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

// Check if work_categories table exists, create or repair if needed
$catTableExists = false;
try {
    $check = $conn->query("SHOW TABLES LIKE 'work_categories'")->fetch();
    if ($check) {
        $catTableExists = true;
        // Auto-repair: add missing columns
        $cols = $conn->query("SHOW COLUMNS FROM work_categories")->fetchAll(PDO::FETCH_COLUMN);
        $fixes = [
            'name'       => "ALTER TABLE `work_categories` ADD COLUMN `name` varchar(255) NOT NULL",
            'status'     => "ALTER TABLE `work_categories` ADD COLUMN `status` enum('active','inactive') DEFAULT 'active'",
            'sort_order' => "ALTER TABLE `work_categories` ADD COLUMN `sort_order` int(11) DEFAULT 0",
            'created_at' => "ALTER TABLE `work_categories` ADD COLUMN `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP",
        ];
        foreach ($fixes as $col => $sql) {
            if (!in_array($col, $cols)) {
                try { $conn->exec($sql); } catch (Exception $e) {}
            }
        }
    }
} catch (Exception $e) {}

if (!$catTableExists) {
    try {
        $conn->exec("CREATE TABLE IF NOT EXISTS `work_categories` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL,
            `status` enum('active','inactive') DEFAULT 'active',
            `sort_order` int(11) DEFAULT 0,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $catTableExists = true;
    } catch (Exception $e) {}
}

// Check if work_images table exists, create or repair if needed
$imgTableExists = false;
try {
    $check = $conn->query("SHOW TABLES LIKE 'work_images'")->fetch();
    if ($check) {
        $imgTableExists = true;
        // Auto-repair: add missing columns
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
    }
} catch (Exception $e) {}

if (!$imgTableExists) {
    try {
        $conn->exec("CREATE TABLE IF NOT EXISTS `work_images` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `category_id` int(11) NOT NULL,
            `image` varchar(255) NOT NULL,
            `title` varchar(255) DEFAULT NULL,
            `sort_order` int(11) DEFAULT 0,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $imgTableExists = true;
    } catch (Exception $e) {}
}

// Ensure uploads/work directory exists
$workDir = __DIR__ . '/../uploads/work';
if (!file_exists($workDir)) {
    @mkdir($workDir, 0755, true);
}

// Get all categories with image counts
$categories = [];
if ($catTableExists) {
    try {
        $categories = $conn->query("SELECT c.*, (SELECT COUNT(*) FROM work_images WHERE category_id = c.id) as image_count FROM work_categories c ORDER BY c.sort_order ASC, c.id ASC")->fetchAll();
    } catch (Exception $e) {
        $categories = [];
    }
}

// Get all images grouped by category
$images = [];
if ($imgTableExists) {
    try {
        $allImages = $conn->query("SELECT * FROM work_images ORDER BY sort_order ASC, id ASC")->fetchAll();
        foreach ($allImages as $img) {
            $images[$img['category_id']][] = $img;
        }
    } catch (Exception $e) {
        $images = [];
    }
}

$success = isset($_GET['cat_added'])   ? 'Category added successfully!'   :
           (isset($_GET['cat_updated']) ? 'Category updated successfully!' :
           (isset($_GET['cat_deleted']) ? 'Category deleted successfully!' :
           (isset($_GET['img_added'])   ? 'Image added successfully!'     :
           (isset($_GET['img_deleted']) ? 'Image deleted successfully!'   : ''))));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Work | Sri Lakshmi Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <!-- Header -->
    <header class="admin-header">
        <div class="header-left">
            <h1 class="admin-title">Our Work</h1>
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
        <?php if (!$catTableExists): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Work tables not found!</strong> Please check database connection.
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-briefcase"></i> Work Categories</h2>
                <a href="add-work-category.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Category
                </a>
            </div>
            <div class="card-body">
                <?php if (count($categories) > 0): ?>
                    <?php foreach ($categories as $category): ?>
                        <div class="work-category-block">
                            <div class="work-category-header">
                                <div class="work-category-info">
                                    <h3><?php echo htmlspecialchars($category['name']); ?></h3>
                                    <span class="work-category-meta">
                                        <?php echo (int)$category['image_count']; ?> image<?php echo $category['image_count'] != 1 ? 's' : ''; ?>
                                        &bull; Order: <?php echo (int)$category['sort_order']; ?>
                                        &bull;
                                        <?php if ($category['status'] === 'active'): ?>
                                            <span class="badge badge-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <div class="work-category-actions">
                                    <a href="add-work-image.php?category_id=<?php echo $category['id']; ?>" class="btn-action btn-action-success" title="Add Image">
                                        <i class="fas fa-plus"></i> Add Image
                                    </a>
                                    <a href="edit-work-category.php?id=<?php echo $category['id']; ?>" class="btn-action btn-action-edit" title="Edit Category">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="delete-work-category.php?id=<?php echo $category['id']; ?>"
                                       class="btn-action btn-action-delete"
                                       onclick="return confirm('Delete this category and ALL its images? This cannot be undone.')"
                                       title="Delete Category">
                                        <i class="fas fa-trash-alt"></i> Delete
                                    </a>
                                </div>
                            </div>

                            <?php if (!empty($images[$category['id']])): ?>
                                <div class="work-images-grid">
                                    <?php foreach ($images[$category['id']] as $image): ?>
                                        <?php
                                        $imgSrc = '';
                                        if (!empty($image['image'])) {
                                            if (file_exists(__DIR__ . '/../uploads/work/' . $image['image'])) {
                                                $imgSrc = '../uploads/work/' . $image['image'];
                                            } elseif (file_exists(__DIR__ . '/../' . $image['image'])) {
                                                $imgSrc = '../' . $image['image'];
                                            }
                                        }
                                        ?>
                                        <div class="work-image-card">
                                            <?php if ($imgSrc): ?>
                                                <img src="<?php echo htmlspecialchars($imgSrc); ?>" alt="<?php echo htmlspecialchars($image['title'] ?: 'Work Image'); ?>" class="work-image-thumb">
                                            <?php else: ?>
                                                <div class="no-image work-image-thumb"><i class="fas fa-image"></i></div>
                                            <?php endif; ?>
                                            <div class="work-image-info">
                                                <small><?php echo htmlspecialchars($image['title'] ?: '—'); ?></small>
                                                <a href="delete-work-image.php?id=<?php echo $image['id']; ?>"
                                                   class="btn-action btn-action-delete btn-action-sm"
                                                   onclick="return confirm('Delete this image? This cannot be undone.')"
                                                   title="Delete Image">
                                                    <i class="fas fa-trash-alt"></i>
                                                </a>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="work-images-empty">
                                    <small>No images yet. Click "Add Image" to upload.</small>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-briefcase"></i>
                        <h3>No Work Categories Found</h3>
                        <p>Add your first category to showcase your work</p>
                        <a href="add-work-category.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add Category
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <style>
    .work-category-block {
        background: rgba(255,255,255,0.05);
        border-radius: 8px;
        margin-bottom: 1.5rem;
        border: 1px solid rgba(255,255,255,0.1);
        overflow: hidden;
    }
    .work-category-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid rgba(255,255,255,0.08);
        flex-wrap: wrap;
        gap: 0.75rem;
    }
    .work-category-info h3 {
        color: #fff;
        font-size: 1.05rem;
        font-weight: 600;
        margin: 0 0 0.25rem;
    }
    .work-category-meta {
        color: rgba(255,255,255,0.5);
        font-size: 0.8rem;
    }
    .work-category-actions {
        display: flex;
        gap: 0.5rem;
        flex-wrap: nowrap;
    }
    .work-images-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 1rem;
        padding: 1rem 1.25rem;
    }
    .work-image-card {
        background: rgba(255,255,255,0.05);
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid rgba(255,255,255,0.08);
    }
    .work-image-thumb {
        width: 100%;
        height: 120px;
        object-fit: cover;
        display: block;
    }
    .work-image-card .no-image {
        width: 100%;
        height: 120px;
        background: rgba(255,255,255,0.05);
        display: flex;
        align-items: center;
        justify-content: center;
        color: rgba(255,255,255,0.3);
        font-size: 2rem;
    }
    .work-image-info {
        padding: 0.5rem 0.75rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.5rem;
    }
    .work-image-info small {
        color: rgba(255,255,255,0.6);
        font-size: 0.75rem;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        flex: 1;
    }
    .work-images-empty {
        padding: 1.5rem 1.25rem;
        text-align: center;
    }
    .work-images-empty small {
        color: rgba(255,255,255,0.4);
    }
    .btn-action {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.4rem 0.75rem;
        border-radius: 8px;
        text-decoration: none;
        font-size: 0.75rem;
        font-weight: 700;
        white-space: nowrap;
        transition: all 0.2s ease;
        border: 1px solid transparent;
    }
    .btn-action-edit { background: linear-gradient(135deg,#ffc107,#ff9800); color:#0a2854; }
    .btn-action-edit:hover { opacity: 0.85; transform: translateY(-1px); }
    .btn-action-delete { background: linear-gradient(135deg,#ff3d00,#dd2c00); color:#fff; }
    .btn-action-delete:hover { opacity: 0.85; transform: translateY(-1px); }
    .btn-action-success { background: linear-gradient(135deg,#00c853,#00897b); color:#fff; }
    .btn-action-success:hover { opacity: 0.85; transform: translateY(-1px); }
    .btn-action-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.7rem;
    }

    @media (max-width: 768px) {
        .work-category-header { flex-direction: column; align-items: flex-start; }
        .work-category-actions { width: 100%; }
        .work-images-grid { grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 0.75rem; padding: 0.75rem; }
        .work-image-thumb { height: 90px; }
    }
    </style>

    <script src="js/admin.js"></script>
</body>
</html>
