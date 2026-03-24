<?php
/**
 * Delete Banner Handler
 * Deletes banner from DB and its image file from server
 */

require_once 'auth.php';
requireLogin();

require_once __DIR__ . '/../config/database.php';

$conn = getConnection();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    header('Location: banners.php');
    exit;
}

$stmt = $conn->prepare("SELECT * FROM banners WHERE id = ?");
$stmt->execute([$id]);
$banner = $stmt->fetch();

if (!$banner) {
    header('Location: banners.php?error=not_found');
    exit;
}

// Delete image file from server
if (!empty($banner['image'])) {
    $possiblePaths = [
        __DIR__ . '/../uploads/banners/' . $banner['image'],
        __DIR__ . '/../uploads/banners/' . basename($banner['image']),
        __DIR__ . '/../' . $banner['image'],
        __DIR__ . '/../' . ltrim($banner['image'], '/'),
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
$stmt = $conn->prepare("DELETE FROM banners WHERE id = ?");
if ($stmt->execute([$id])) {
    header('Location: banners.php?deleted=1');
} else {
    header('Location: banners.php?error=delete_failed');
}
exit;
?>
