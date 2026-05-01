<?php
/**
 * Categories Management Page
 * Sri Lakshmi Admin Panel
 */

require_once 'auth.php';
requireLogin();

require_once __DIR__ . '/../config/database.php';

$conn = getConnection();

// Get all categories
$categories = $conn->query("SELECT * FROM categories ORDER BY display_order ASC")->fetchAll();

$success = isset($_GET['added']) ? 'Category added successfully!' :
           (isset($_GET['deleted']) ? 'Category deleted successfully!' : '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories | Sri Lakshmi Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <!-- Header -->
    <header class="admin-header">
        <div class="header-left">
            <h1 class="admin-title">Categories</h1>
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
        <a href="ourwork.php" class="nav-item">
            <i class="fas fa-briefcase"></i> Our Work
        </a>
        <a href="enquiries.php" class="nav-item">
            <i class="fas fa-envelope"></i> Enquiries
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

        <!-- Categories Overview -->
        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-header">
                <h2><i class="fas fa-list"></i> All Categories</h2>
                <a href="add-category.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Category
                </a>
            </div>
            <div class="card-body">
                <?php if (count($categories) > 0): ?>
                    <!-- Categories Grid -->
                    <div class="categories-grid-admin">
                        <?php foreach ($categories as $category): ?>
                            <div class="category-card-admin <?php echo $category['status'] === 'active' ? 'active' : 'inactive'; ?>">
                                <div class="category-card-header">
                                    <div class="category-icon-admin">
                                        <?php
                                        $imagePath = '';
                                        if (!empty($category['image'])) {
                                            if (file_exists(__DIR__ . '/../uploads/categories/' . $category['image'])) {
                                                $imagePath = '../uploads/categories/' . $category['image'];
                                            } elseif (file_exists(__DIR__ . '/../' . $category['image'])) {
                                                $imagePath = '../' . $category['image'];
                                            }
                                        }

                                        if ($imagePath):
                                        ?>
                                            <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="<?php echo htmlspecialchars($category['name']); ?>" class="category-image-admin">
                                        <?php else: ?>
                                            <i class="fas <?php
                                                $icon = 'fa-tag';
                                                if (stripos($category['name'], 'bag') !== false) $icon = 'fa-shopping-bag';
                                                elseif (stripos($category['name'], 'card') !== false || stripos($category['name'], 'invitation') !== false) $icon = 'fa-envelope';
                                                elseif (stripos($category['name'], 'banner') !== false || stripos($category['name'], 'flex') !== false) $icon = 'fa-flag';
                                                elseif (stripos($category['name'], 'poster') !== false) $icon = 'fa-image';
                                                elseif (stripos($category['name'], 'medal') !== false || stripos($category['name'], 'trophy') !== false) $icon = 'fa-trophy';
                                                elseif (stripos($category['name'], 'uv') !== false || stripos($category['name'], 'print') !== false) $icon = 'fa-print';
                                                elseif (stripos($category['name'], 'design') !== false || stripos($category['name'], 'custom') !== false) $icon = 'fa-palette';
                                                echo $icon;
                                            ?>"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="category-status-badge">
                                        <?php if ($category['status'] === 'active'): ?>
                                            <span class="badge badge-success"><i class="fas fa-check-circle"></i> Active</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary"><i class="fas fa-pause-circle"></i> Inactive</span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="category-card-body">
                                    <h3 class="category-title"><?php echo htmlspecialchars($category['name']); ?></h3>
                                    <div class="category-meta">
                                        <span class="category-id"><i class="fas fa-hashtag"></i> <?php echo $category['id']; ?></span>
                                        <span class="category-date"><i class="far fa-calendar"></i> <?php echo date('M d, Y', strtotime($category['created_at'])); ?></span>
                                    </div>
                                </div>

                                <div class="category-card-actions">
                                    <a href="edit-category.php?id=<?php echo $category['id']; ?>"
                                       class="btn-action btn-action-edit"
                                       title="Edit Category">
                                        <i class="fas fa-edit"></i>
                                        <span>Edit</span>
                                    </a>
                                    <a href="delete-category.php?id=<?php echo $category['id']; ?>"
                                       class="btn-action btn-action-delete"
                                       onclick="return confirm('Are you sure you want to delete this category? Products in this category will be set to \'No Category\'.')"
                                       title="Delete Category">
                                        <i class="fas fa-trash-alt"></i>
                                        <span>Delete</span>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-list"></i>
                        <h3>No Categories Found</h3>
                        <p>Add your first category to organize products</p>
                        <a href="add-category.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add Category
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <style>
    /* Categories Grid Layout */
    .categories-grid-admin {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
        gap: 1.25rem;
        margin-top: 1.5rem;
    }

    /* Category Card */
    .category-card-admin {
        background: linear-gradient(135deg, #1a2f4d 0%, #0f1d2e 100%);
        border: 2px solid rgba(255, 193, 7, 0.3);
        border-radius: 16px;
        padding: 1.25rem;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4);
    }

    .category-card-admin::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 5px;
        background: linear-gradient(90deg, #ffc107 0%, #ff9800 50%, #ffc107 100%);
        transform: scaleX(0);
        transform-origin: left;
        transition: transform 0.4s ease;
    }

    .category-card-admin::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 300px;
        height: 300px;
        background: radial-gradient(circle, rgba(255, 193, 7, 0.1) 0%, transparent 70%);
        transform: translate(-50%, -50%);
        opacity: 0;
        transition: opacity 0.4s ease;
    }

    .category-card-admin:hover::before {
        transform: scaleX(1);
    }

    .category-card-admin:hover::after {
        opacity: 1;
    }

    .category-card-admin:hover {
        transform: translateY(-12px) scale(1.02);
        border-color: #ffc107;
        box-shadow: 0 16px 45px rgba(255, 193, 7, 0.4), 0 0 30px rgba(255, 193, 7, 0.2);
        background: linear-gradient(135deg, #244062 0%, #132437 100%);
    }

    .category-card-admin.inactive {
        opacity: 0.65;
        border-color: rgba(150, 150, 150, 0.3);
    }

    .category-card-admin.inactive:hover {
        border-color: rgba(150, 150, 150, 0.6);
    }

    /* Category Card Header */
    .category-card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1rem;
    }

    .category-icon-admin {
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, #ffc107 0%, #ff9800 50%, #ffc107 100%);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 10px 25px rgba(255, 193, 7, 0.5), inset 0 -2px 10px rgba(0, 0, 0, 0.2);
        transition: all 0.4s ease;
        position: relative;
        z-index: 1;
    }

    .category-icon-admin::before {
        content: '';
        position: absolute;
        inset: -3px;
        background: linear-gradient(135deg, #ffc107, #ff9800);
        border-radius: 14px;
        opacity: 0;
        transition: opacity 0.4s ease;
        z-index: -1;
        filter: blur(8px);
    }

    .category-card-admin:hover .category-icon-admin {
        transform: rotate(15deg) scale(1.15);
        box-shadow: 0 15px 40px rgba(255, 193, 7, 0.7), inset 0 -2px 10px rgba(0, 0, 0, 0.2);
    }

    .category-card-admin:hover .category-icon-admin::before {
        opacity: 1;
    }

    .category-icon-admin i {
        font-size: 1.5rem;
        color: #0a2854;
        filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
    }

    .category-image-admin {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 12px;
    }

    .category-status-badge {
        display: flex;
        align-items: center;
    }

    .category-status-badge .badge {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        font-size: 0.7rem;
        padding: 0.4rem 0.8rem;
        border-radius: 20px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    }

    .category-status-badge .badge-success {
        background: linear-gradient(135deg, #00c853 0%, #00e676 100%);
        color: #ffffff;
        border: 2px solid rgba(0, 230, 118, 0.3);
    }

    .category-status-badge .badge-secondary {
        background: linear-gradient(135deg, #757575 0%, #9e9e9e 100%);
        color: #ffffff;
        border: 2px solid rgba(158, 158, 158, 0.3);
    }

    .category-status-badge .badge i {
        font-size: 0.9rem;
    }

    /* Category Card Body */
    .category-card-body {
        margin-bottom: 1rem;
    }

    .category-title {
        font-size: 1.15rem;
        font-weight: 800;
        color: #ffffff;
        margin: 0 0 0.65rem 0;
        line-height: 1.3;
        letter-spacing: 0.5px;
        text-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
        transition: all 0.3s ease;
    }

    .category-card-admin:hover .category-title {
        color: #ffc107;
        text-shadow: 0 0 20px rgba(255, 193, 7, 0.5);
    }

    .category-meta {
        display: flex;
        gap: 1.5rem;
        flex-wrap: wrap;
    }

    .category-meta span {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        font-size: 0.75rem;
        color: rgba(255, 193, 7, 0.9);
        font-weight: 600;
        background: rgba(255, 193, 7, 0.1);
        padding: 0.3rem 0.6rem;
        border-radius: 6px;
        transition: all 0.3s ease;
    }

    .category-card-admin:hover .category-meta span {
        background: rgba(255, 193, 7, 0.2);
        color: #ffc107;
    }

    .category-meta i {
        font-size: 0.9rem;
        color: #ffc107;
    }

    /* Category Card Actions */
    .category-card-actions {
        display: flex;
        gap: 0.65rem;
        padding-top: 1rem;
        border-top: 2px solid rgba(255, 193, 7, 0.15);
    }

    .btn-action {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.65rem 0.9rem;
        border-radius: 10px;
        text-decoration: none;
        font-size: 0.8rem;
        font-weight: 700;
        transition: all 0.3s ease;
        border: 2px solid transparent;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        position: relative;
        overflow: hidden;
    }

    .btn-action::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.3);
        transform: translate(-50%, -50%);
        transition: width 0.5s ease, height 0.5s ease;
    }

    .btn-action:hover::before {
        width: 300px;
        height: 300px;
    }

    .btn-action i {
        font-size: 0.95rem;
        position: relative;
        z-index: 1;
    }

    .btn-action span {
        font-size: 0.75rem;
        position: relative;
        z-index: 1;
    }

    .btn-action-edit {
        background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
        color: #0a2854;
        border-color: #ffc107;
        box-shadow: 0 4px 15px rgba(255, 193, 7, 0.3);
    }

    .btn-action-edit:hover {
        background: linear-gradient(135deg, #ffcd38 0%, #ffa726 100%);
        color: #0a2854;
        border-color: #ffd54f;
        transform: translateY(-3px) scale(1.05);
        box-shadow: 0 8px 25px rgba(255, 193, 7, 0.6);
    }

    .btn-action-delete {
        background: linear-gradient(135deg, #ff3d00 0%, #dd2c00 100%);
        color: #ffffff;
        border-color: #ff3d00;
        box-shadow: 0 4px 15px rgba(255, 61, 0, 0.3);
    }

    .btn-action-delete:hover {
        background: linear-gradient(135deg, #ff6333 0%, #ff3d00 100%);
        color: #ffffff;
        border-color: #ff6333;
        transform: translateY(-3px) scale(1.05);
        box-shadow: 0 8px 25px rgba(255, 61, 0, 0.6);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .categories-grid-admin {
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        .category-card-admin {
            padding: 1rem;
        }

        .category-icon-admin {
            width: 45px;
            height: 45px;
        }

        .category-icon-admin i {
            font-size: 1.3rem;
        }

        .category-title {
            font-size: 1rem;
        }

        .btn-action span {
            display: none;
        }

        .btn-action {
            padding: 0.6rem;
        }
    }

    @media (min-width: 769px) and (max-width: 1024px) {
        .categories-grid-admin {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    </style>

    <script src="js/admin.js"></script>
</body>
</html>
