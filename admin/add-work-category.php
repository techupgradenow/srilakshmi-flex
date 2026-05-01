<?php
/**
 * Add Work Category Page
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

// Check if work_categories table exists, create if needed
try {
    $check = $conn->query("SHOW TABLES LIKE 'work_categories'")->fetch();
    if ($check) {
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
    } else {
        $conn->exec("CREATE TABLE IF NOT EXISTS `work_categories` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL,
            `status` enum('active','inactive') DEFAULT 'active',
            `sort_order` int(11) DEFAULT 0,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }
} catch (Exception $e) {}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name       = trim($_POST['name'] ?? '');
    $status     = $_POST['status'] ?? 'active';
    $sort_order = (int)($_POST['sort_order'] ?? 0);

    if (empty($name)) {
        $error = 'Please enter a category name';
    }

    if (empty($error)) {
        try {
            $stmt = $conn->prepare("INSERT INTO work_categories (name, status, sort_order) VALUES (?,?,?)");
            if ($stmt->execute([$name, $status, $sort_order])) {
                header('Location: ourwork.php?cat_added=1');
                exit;
            } else {
                $error = 'Failed to save category to database';
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
    <title>Add Work Category | Sri Lakshmi Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <!-- Header -->
    <header class="admin-header">
        <div class="header-left">
            <h1 class="admin-title">Add Work Category</h1>
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
                <h2><i class="fas fa-plus-circle"></i> Category Information</h2>
            </div>
            <div class="card-body">
                <form method="POST" class="product-form">

                    <div class="form-group">
                        <label for="name">Category Name *</label>
                        <input type="text" id="name" name="name" class="form-control"
                               placeholder="e.g. Flex Banners, Wedding Cards, LED Boards"
                               required value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="sort_order">Display Order</label>
                        <input type="number" id="sort_order" name="sort_order" class="form-control"
                               placeholder="0" min="0" value="<?php echo (int)($_POST['sort_order'] ?? 0); ?>">
                        <small class="form-hint">Lower number = shown first. Use 0, 1, 2... to order categories</small>
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
                            <i class="fas fa-save"></i> Add Category
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
</body>
</html>
