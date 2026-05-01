<?php
/**
 * Delete Work Image Handler
 * Deletes image from DB and its file from server
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

$stmt = $conn->prepare("SELECT * FROM work_images WHERE id = ?");
$stmt->execute([$id]);
$image = $stmt->fetch();

if (!$image) {
    header('Location: ourwork.php?error=not_found');
    exit;
}

// Delete image file from server
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

// Delete from DB
$stmt = $conn->prepare("DELETE FROM work_images WHERE id = ?");
if ($stmt->execute([$id])) {
    header('Location: ourwork.php?img_deleted=1');
} else {
    header('Location: ourwork.php?error=delete_failed');
}
exit;
?>
