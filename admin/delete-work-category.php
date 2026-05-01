<?php
/**
 * Delete Work Category Handler
 * Deletes category, all its images from DB, and image files from server
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

require_once 'auth.php';
requireLogin();

require_once __DIR__ . '/../config/database.php';

$conn = getConnection();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    header('Location: ourwork.php');
    exit;
}

$stmt = $conn->prepare("SELECT * FROM work_categories WHERE id = ?");
$stmt->execute([$id]);
$category = $stmt->fetch();

if (!$category) {
    header('Location: ourwork.php?error=not_found');
    exit;
}

// Delete all images in this category (files + DB rows)
try {
    $imgStmt = $conn->prepare("SELECT * FROM work_images WHERE category_id = ?");
    $imgStmt->execute([$id]);
    $categoryImages = $imgStmt->fetchAll();

    foreach ($categoryImages as $image) {
        if (!empty($image['image'])) {
            $possiblePaths = [
                __DIR__ . '/../uploads/work/' . $image['image'],
                __DIR__ . '/../uploads/work/' . basename($image['image']),
                __DIR__ . '/../' . $image['image'],
                __DIR__ . '/../' . ltrim($image['image'], '/'),
            ];
            foreach ($possiblePaths as $path) {
                $realPath = realpath($path);
                if ($realPath && file_exists($realPath)) {
                    unlink($realPath);
                    break;
                }
            }
        }
    }

    // Delete all image rows for this category
    $delImgStmt = $conn->prepare("DELETE FROM work_images WHERE category_id = ?");
    $delImgStmt->execute([$id]);
} catch (Exception $e) {}

// Delete category from DB
$stmt = $conn->prepare("DELETE FROM work_categories WHERE id = ?");
if ($stmt->execute([$id])) {
    header('Location: ourwork.php?cat_deleted=1');
} else {
    header('Location: ourwork.php?error=delete_failed');
}
exit;
?>
