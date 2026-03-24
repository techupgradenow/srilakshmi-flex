<?php
/**
 * Banners API
 * Returns active banners as JSON for the frontend slider
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../config/database.php';

try {
    $conn = getConnection();

    // Check if banners table exists, try to create if not
    $check = $conn->query("SHOW TABLES LIKE 'banners'")->fetch();
    if (!$check) {
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
        } catch (Exception $e) {
            // Can't create table, return empty
            echo json_encode(['success' => true, 'banners' => []]);
            exit;
        }
    }

    $stmt = $conn->query("SELECT id, image, title, subtitle, button_text, button_link FROM banners WHERE status = 'active' ORDER BY sort_order ASC, id ASC");
    $rows = $stmt->fetchAll();

    // Build image URLs
    $siteUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
    $scriptDir = rtrim(str_replace('\\', '/', dirname(dirname($_SERVER['SCRIPT_NAME']))), '/');

    $banners = [];
    foreach ($rows as $row) {
        $imageUrl = '';
        if (!empty($row['image'])) {
            $filePath = __DIR__ . '/../uploads/banners/' . $row['image'];
            if (file_exists($filePath)) {
                $imageUrl = $siteUrl . $scriptDir . '/uploads/banners/' . $row['image'];
            }
        }
        $banners[] = [
            'id'          => (int)$row['id'],
            'image'       => $imageUrl,
            'title'       => $row['title'],
            'subtitle'    => $row['subtitle'],
            'button_text' => $row['button_text'],
            'button_link' => $row['button_link'],
        ];
    }

    echo json_encode(['success' => true, 'banners' => $banners]);

} catch (Exception $e) {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $isProd = ($host === 'srilakshmiads.in' || $host === 'www.srilakshmiads.in');
    $errMsg = $isProd ? $e->getMessage() : $e->getMessage();
    echo json_encode(['success' => false, 'banners' => [], 'error' => $errMsg, 'line' => $e->getLine()]);
}
?>
