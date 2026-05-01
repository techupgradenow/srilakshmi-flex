<?php
/**
 * Our Work API
 * Returns active work categories with their images as JSON for the frontend
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../config/database.php';

try {
    $conn = getConnection();

    // Check if work_categories table exists, create if needed
    $check = $conn->query("SHOW TABLES LIKE 'work_categories'")->fetch();
    if (!$check) {
        try {
            $conn->exec("CREATE TABLE IF NOT EXISTS `work_categories` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `name` varchar(255) NOT NULL,
                `status` enum('active','inactive') DEFAULT 'active',
                `sort_order` int(11) DEFAULT 0,
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        } catch (Exception $e) {
            echo json_encode(['success' => true, 'categories' => []]);
            exit;
        }
    } else {
        // Auto-repair: add missing columns
        $cols = $conn->query("SHOW COLUMNS FROM work_categories")->fetchAll(PDO::FETCH_COLUMN);
        $fixes = [
            'name'       => "ALTER TABLE `work_categories` ADD COLUMN `name` varchar(255) NOT NULL",
            'status'     => "ALTER TABLE `work_categories` ADD COLUMN `status` enum('active','inactive') DEFAULT 'active'",
            'sort_order' => "ALTER TABLE `work_categories` ADD COLUMN `sort_order` int(11) DEFAULT 0",
        ];
        foreach ($fixes as $col => $sql) {
            if (!in_array($col, $cols)) {
                try { $conn->exec($sql); } catch (Exception $e) {}
            }
        }
    }

    // Check if work_images table exists, create if needed
    $check2 = $conn->query("SHOW TABLES LIKE 'work_images'")->fetch();
    if (!$check2) {
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
        } catch (Exception $e) {
            echo json_encode(['success' => true, 'categories' => []]);
            exit;
        }
    } else {
        $cols = $conn->query("SHOW COLUMNS FROM work_images")->fetchAll(PDO::FETCH_COLUMN);
        $fixes = [
            'category_id' => "ALTER TABLE `work_images` ADD COLUMN `category_id` int(11) NOT NULL",
            'image'       => "ALTER TABLE `work_images` ADD COLUMN `image` varchar(255) NOT NULL",
            'title'       => "ALTER TABLE `work_images` ADD COLUMN `title` varchar(255) DEFAULT NULL",
            'sort_order'  => "ALTER TABLE `work_images` ADD COLUMN `sort_order` int(11) DEFAULT 0",
        ];
        foreach ($fixes as $col => $sql) {
            if (!in_array($col, $cols)) {
                try { $conn->exec($sql); } catch (Exception $e) {}
            }
        }
    }

    // Get active categories
    $catStmt = $conn->query("SELECT id, name FROM work_categories WHERE status = 'active' ORDER BY sort_order ASC, id ASC");
    $catRows = $catStmt->fetchAll();

    // Get all images
    $imgStmt = $conn->query("SELECT id, category_id, image, title FROM work_images ORDER BY sort_order ASC, id ASC");
    $imgRows = $imgStmt->fetchAll();

    // Group images by category
    $imagesByCategory = [];
    foreach ($imgRows as $img) {
        $imagesByCategory[$img['category_id']][] = $img;
    }

    // Build image URLs
    $siteUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
    $scriptDir = rtrim(str_replace('\\', '/', dirname(dirname($_SERVER['SCRIPT_NAME']))), '/');

    $categories = [];
    foreach ($catRows as $cat) {
        $catImages = [];
        if (!empty($imagesByCategory[$cat['id']])) {
            foreach ($imagesByCategory[$cat['id']] as $img) {
                $imageUrl = '';
                if (!empty($img['image'])) {
                    $filePath = __DIR__ . '/../uploads/work/' . $img['image'];
                    if (file_exists($filePath)) {
                        $imageUrl = $siteUrl . $scriptDir . '/uploads/work/' . $img['image'];
                    }
                }
                $catImages[] = [
                    'id'    => (int)$img['id'],
                    'image' => $imageUrl,
                    'title' => $img['title'],
                ];
            }
        }
        $categories[] = [
            'id'     => (int)$cat['id'],
            'name'   => $cat['name'],
            'images' => $catImages,
        ];
    }

    echo json_encode(['success' => true, 'categories' => $categories]);

} catch (Exception $e) {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $isProd = ($host === 'srilakshmiads.in' || $host === 'www.srilakshmiads.in');
    $errMsg = $isProd ? $e->getMessage() : $e->getMessage();
    echo json_encode(['success' => false, 'categories' => [], 'error' => $errMsg, 'line' => $e->getLine()]);
}
?>
