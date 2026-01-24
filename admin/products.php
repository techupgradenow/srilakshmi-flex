<?php
/**
 * Manage Products Page
 * Sri Lakshmi Admin Panel
 */

require_once 'auth.php';
requireLogin();

require_once __DIR__ . '/../config/database.php';

$conn = getConnection();

// Handle delete action
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int) $_GET['delete'];

    // Get product image
    $stmt = $conn->prepare("SELECT image FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();

    if ($product) {
        // Delete image file if exists
        $imagePath = __DIR__ . '/../uploads/products/' . $product['image'];
        if (file_exists($imagePath) && $product['image']) {
            unlink($imagePath);
        }

        // Delete from database
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$id]);
    }

    header('Location: products.php?deleted=1');
    exit;
}

// Handle status toggle
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $id = (int) $_GET['toggle'];
    $stmt = $conn->prepare("UPDATE products SET status = IF(status = 'active', 'inactive', 'active') WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: products.php?updated=1');
    exit;
}

// Get all products with category names
$products = $conn->query("
    SELECT p.*, c.name as category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    ORDER BY p.created_at DESC
")->fetchAll();

$success = '';
if (isset($_GET['deleted'])) $success = 'Product deleted successfully!';
if (isset($_GET['updated'])) $success = 'Product status updated!';
if (isset($_GET['added'])) $success = 'Product added successfully!';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products | Sri Lakshmi Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <!-- Header -->
    <header class="admin-header">
        <div class="header-left">
            <h1 class="admin-title">Manage Products</h1>
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
        <a href="products.php" class="nav-item active">
            <i class="fas fa-box"></i> Products
        </a>
    </nav>

    <!-- Main Content -->
    <main class="admin-main">
        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <!-- Products Card -->
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-box"></i> All Products</h2>
                <a href="add-product.php" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Add New Product
                </a>
            </div>
            <div class="card-body">
                <?php if (empty($products)): ?>
                    <div class="empty-state">
                        <i class="fas fa-box-open"></i>
                        <p>No products found</p>
                        <a href="add-product.php" class="btn btn-primary">Add Your First Product</a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Name</th>
                                    <th>Category</th>
                                    <th>Size</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $product): ?>
                                    <tr>
                                        <td>
                                            <div class="product-thumb">
                                                <?php if ($product['image'] && file_exists(__DIR__ . '/../uploads/products/' . $product['image'])): ?>
                                                    <img src="../uploads/products/<?php echo htmlspecialchars($product['image']); ?>"
                                                         alt="<?php echo htmlspecialchars($product['name']); ?>">
                                                <?php elseif ($product['image'] && file_exists(__DIR__ . '/../' . $product['image'])): ?>
                                                    <img src="../<?php echo htmlspecialchars($product['image']); ?>"
                                                         alt="<?php echo htmlspecialchars($product['name']); ?>">
                                                <?php else: ?>
                                                    <div class="no-image"><i class="fas fa-image"></i></div>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="product-name">
                                                <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                                                <?php if ($product['description']): ?>
                                                    <small><?php echo htmlspecialchars(substr($product['description'], 0, 50)); ?>...</small>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?></td>
                                        <td><?php echo htmlspecialchars($product['size'] ?? '-'); ?></td>
                                        <td>
                                            <span class="status-badge <?php echo $product['status']; ?>">
                                                <?php echo ucfirst($product['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="edit-product.php?id=<?php echo $product['id']; ?>"
                                                   class="btn btn-sm btn-edit" title="Edit">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                                <a href="products.php?toggle=<?php echo $product['id']; ?>"
                                                   class="btn btn-sm btn-toggle" title="Toggle Status">
                                                    <i class="fas fa-eye<?php echo $product['status'] === 'active' ? '' : '-slash'; ?>"></i>
                                                </a>
                                                <a href="products.php?delete=<?php echo $product['id']; ?>"
                                                   class="btn btn-sm btn-delete"
                                                   onclick="return confirm('Are you sure you want to delete this product?')"
                                                   title="Delete">
                                                    <i class="fas fa-trash"></i> Delete
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script src="js/admin.js"></script>
</body>
</html>
