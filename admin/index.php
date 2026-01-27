<?php
/**
 * Admin Dashboard
 * Sri Lakshmi Admin Panel
 */

require_once 'auth.php';
requireLogin();

require_once __DIR__ . '/../config/database.php';

$conn = getConnection();

// Get total products count
$totalProducts = $conn->query("SELECT COUNT(*) FROM products")->fetchColumn();

// Get active products count
$activeProducts = $conn->query("SELECT COUNT(*) FROM products WHERE status = 'active'")->fetchColumn();

// Get categories count
$totalCategories = $conn->query("SELECT COUNT(*) FROM categories")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Sri Lakshmi Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <!-- Header -->
    <header class="admin-header">
        <div class="header-left">
            <h1 class="admin-title">Sri Lakshmi Admin Panel</h1>
            <p class="admin-welcome">Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></p>
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
        <a href="index.php" class="nav-item active">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
        <a href="categories.php" class="nav-item">
            <i class="fas fa-list"></i> Categories
        </a>
        <a href="products.php" class="nav-item">
            <i class="fas fa-box"></i> Products
        </a>
    </nav>

    <!-- Main Content -->
    <main class="admin-main">
        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-box"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-label">Total Products</span>
                    <span class="stat-value"><?php echo $totalProducts; ?></span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon success">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-label">Active Products</span>
                    <span class="stat-value"><?php echo $activeProducts; ?></span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <i class="fas fa-list"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-label">Categories</span>
                    <span class="stat-value"><?php echo $totalCategories; ?></span>
                </div>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-info-circle"></i> Quick Links</h2>
            </div>
            <div class="card-body">
                <div class="quick-links">
                    <a href="categories.php" class="btn btn-primary">
                        <i class="fas fa-list"></i> Manage Categories
                    </a>
                    <a href="products.php" class="btn btn-primary">
                        <i class="fas fa-box"></i> Manage Products
                    </a>
                    <a href="add-product.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Product
                    </a>
                </div>
            </div>
        </div>
    </main>

    <script src="js/admin.js"></script>
</body>
</html>
