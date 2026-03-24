<?php
/**
 * Banners Management Page
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

// Check if banners table exists, create or repair if needed
$tableExists = false;
try {
    $check = $conn->query("SHOW TABLES LIKE 'banners'")->fetch();
    if ($check) {
        $tableExists = true;
        // Auto-repair: add missing columns
        $cols = $conn->query("SHOW COLUMNS FROM banners")->fetchAll(PDO::FETCH_COLUMN);
        $fixes = [
            'title'       => "ALTER TABLE `banners` ADD COLUMN `title` varchar(255) DEFAULT NULL",
            'subtitle'    => "ALTER TABLE `banners` ADD COLUMN `subtitle` varchar(500) DEFAULT NULL",
            'button_text' => "ALTER TABLE `banners` ADD COLUMN `button_text` varchar(100) DEFAULT 'Learn More'",
            'button_link' => "ALTER TABLE `banners` ADD COLUMN `button_link` varchar(255) DEFAULT '#'",
            'status'      => "ALTER TABLE `banners` ADD COLUMN `status` enum('active','inactive') DEFAULT 'active'",
            'sort_order'  => "ALTER TABLE `banners` ADD COLUMN `sort_order` int(11) DEFAULT 0",
            'created_at'  => "ALTER TABLE `banners` ADD COLUMN `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP",
        ];
        foreach ($fixes as $col => $sql) {
            if (!in_array($col, $cols)) {
                try { $conn->exec($sql); } catch (Exception $e) {}
            }
        }
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

// Ensure uploads/banners directory exists
$bannersDir = __DIR__ . '/../uploads/banners';
if (!file_exists($bannersDir)) {
    @mkdir($bannersDir, 0755, true);
}

// Get all banners
$banners = [];
if ($tableExists) {
    try {
        $banners = $conn->query("SELECT * FROM banners ORDER BY sort_order ASC, id ASC")->fetchAll();
    } catch (Exception $e) {
        $banners = [];
    }
}

$success = isset($_GET['added'])   ? 'Banner added successfully!'   :
           (isset($_GET['updated']) ? 'Banner updated successfully!' :
           (isset($_GET['deleted']) ? 'Banner deleted successfully!' :
           (isset($_GET['toggled']) ? 'Banner status updated!'       : '')));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Banners | Sri Lakshmi Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <!-- Header -->
    <header class="admin-header">
        <div class="header-left">
            <h1 class="admin-title">Banners</h1>
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
        <?php if (!$tableExists): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Banners table not found!</strong> Please create it in phpMyAdmin with this SQL:<br>
                <code style="display:block;margin-top:8px;padding:8px;background:rgba(0,0,0,0.3);border-radius:4px;font-size:0.8rem;word-break:break-all;">
                CREATE TABLE IF NOT EXISTS `banners` (`id` int(11) NOT NULL AUTO_INCREMENT, `image` varchar(255) NOT NULL, `title` varchar(255) DEFAULT NULL, `subtitle` varchar(500) DEFAULT NULL, `button_text` varchar(100) DEFAULT 'Learn More', `button_link` varchar(255) DEFAULT '#', `status` enum('active','inactive') DEFAULT 'active', `sort_order` int(11) DEFAULT 0, `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
                </code>
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
                <h2><i class="fas fa-images"></i> All Banners</h2>
                <a href="add-banner.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Banner
                </a>
            </div>
            <div class="card-body">
                <?php if (count($banners) > 0): ?>
                    <div class="banners-table-wrapper">
                        <table class="banners-table">
                            <thead>
                                <tr>
                                    <th>Order</th>
                                    <th>Image</th>
                                    <th>Title</th>
                                    <th>Button</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($banners as $banner): ?>
                                    <?php
                                    $imgSrc = '';
                                    if (!empty($banner['image'])) {
                                        if (file_exists(__DIR__ . '/../uploads/banners/' . $banner['image'])) {
                                            $imgSrc = '../uploads/banners/' . $banner['image'];
                                        } elseif (file_exists(__DIR__ . '/../' . $banner['image'])) {
                                            $imgSrc = '../' . $banner['image'];
                                        }
                                    }
                                    ?>
                                    <tr class="<?php echo $banner['status'] === 'inactive' ? 'row-inactive' : ''; ?>">
                                        <td class="col-order"><?php echo (int)$banner['sort_order']; ?></td>
                                        <td class="col-image">
                                            <?php if ($imgSrc): ?>
                                                <img src="<?php echo htmlspecialchars($imgSrc); ?>" alt="Banner" class="banner-thumb">
                                            <?php else: ?>
                                                <div class="no-image"><i class="fas fa-image"></i></div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="col-title">
                                            <strong><?php echo htmlspecialchars($banner['title'] ?: '—'); ?></strong>
                                            <?php if (!empty($banner['subtitle'])): ?>
                                                <small><?php echo htmlspecialchars($banner['subtitle']); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td class="col-button">
                                            <?php if (!empty($banner['button_text'])): ?>
                                                <span class="btn-label"><?php echo htmlspecialchars($banner['button_text']); ?></span>
                                            <?php else: ?>
                                                —
                                            <?php endif; ?>
                                        </td>
                                        <td class="col-status">
                                            <?php if ($banner['status'] === 'active'): ?>
                                                <span class="badge badge-success"><i class="fas fa-check-circle"></i> Active</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary"><i class="fas fa-pause-circle"></i> Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="col-actions">
                                            <a href="edit-banner.php?id=<?php echo $banner['id']; ?>" class="btn-action btn-action-edit" title="Edit">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="toggle-banner.php?id=<?php echo $banner['id']; ?>"
                                               class="btn-action <?php echo $banner['status'] === 'active' ? 'btn-action-warning' : 'btn-action-success'; ?>"
                                               title="<?php echo $banner['status'] === 'active' ? 'Deactivate' : 'Activate'; ?>">
                                                <i class="fas fa-<?php echo $banner['status'] === 'active' ? 'eye-slash' : 'eye'; ?>"></i>
                                                <?php echo $banner['status'] === 'active' ? 'Hide' : 'Show'; ?>
                                            </a>
                                            <a href="delete-banner.php?id=<?php echo $banner['id']; ?>"
                                               class="btn-action btn-action-delete"
                                               onclick="return confirm('Delete this banner? This cannot be undone.')"
                                               title="Delete">
                                                <i class="fas fa-trash-alt"></i> Delete
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-images"></i>
                        <h3>No Banners Found</h3>
                        <p>Add your first banner to display in the homepage slider</p>
                        <a href="add-banner.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add Banner
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <style>
    .banners-table-wrapper {
        overflow-x: auto;
    }
    .banners-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.9rem;
    }
    .banners-table th {
        background: rgba(255, 193, 7, 0.15);
        color: #ffc107;
        padding: 0.75rem 1rem;
        text-align: left;
        font-weight: 600;
        border-bottom: 2px solid rgba(255, 193, 7, 0.3);
        white-space: nowrap;
    }
    .banners-table td {
        padding: 0.75rem 1rem;
        border-bottom: 1px solid rgba(255,255,255,0.08);
        color: #e0e0e0;
        vertical-align: middle;
    }
    .banners-table tr:last-child td {
        border-bottom: none;
    }
    .banners-table tr:hover td {
        background: rgba(255,193,7,0.05);
    }
    .row-inactive td {
        opacity: 0.6;
    }
    .banner-thumb {
        width: 120px;
        height: 60px;
        object-fit: cover;
        border-radius: 6px;
        border: 2px solid rgba(255,193,7,0.3);
        display: block;
    }
    .no-image {
        width: 120px;
        height: 60px;
        background: rgba(255,255,255,0.05);
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: rgba(255,255,255,0.3);
        font-size: 1.5rem;
    }
    .col-title strong {
        display: block;
        color: #fff;
        font-weight: 600;
    }
    .col-title small {
        color: rgba(255,255,255,0.5);
        font-size: 0.75rem;
        display: block;
        margin-top: 0.2rem;
        max-width: 220px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .btn-label {
        background: rgba(255,193,7,0.15);
        color: #ffc107;
        padding: 0.25rem 0.6rem;
        border-radius: 4px;
        font-size: 0.78rem;
        font-weight: 600;
    }
    .badge {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        padding: 0.3rem 0.7rem;
        border-radius: 20px;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
    }
    .badge-success { background: linear-gradient(135deg,#00c853,#00e676); color:#fff; }
    .badge-secondary { background: linear-gradient(135deg,#757575,#9e9e9e); color:#fff; }
    .col-actions {
        display: flex;
        gap: 0.5rem;
        flex-wrap: nowrap;
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
    .btn-action-warning { background: linear-gradient(135deg,#ff9800,#f57c00); color:#fff; }
    .btn-action-warning:hover { opacity: 0.85; transform: translateY(-1px); }
    .btn-action-success { background: linear-gradient(135deg,#00c853,#00897b); color:#fff; }
    .btn-action-success:hover { opacity: 0.85; transform: translateY(-1px); }

    @media (max-width: 768px) {
        .banners-table th, .banners-table td { padding: 0.5rem 0.6rem; }
        .banner-thumb { width: 80px; height: 45px; }
        .col-actions { flex-direction: column; gap: 0.3rem; }
        .col-title small { max-width: 120px; }
    }
    </style>

    <script src="js/admin.js"></script>
</body>
</html>
