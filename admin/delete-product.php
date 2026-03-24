<?php
/**
 * Delete Product Handler
 * Deletes product record from DB AND image file from server
 */

require_once 'auth.php';
requireLogin();

require_once __DIR__ . '/../config/database.php';

if (!isset($_GET['id'])) {
    header('Location: products.php');
    exit;
}

$id = (int) $_GET['id'];
if ($id <= 0) {
    header('Location: products.php');
    exit;
}

$conn = getConnection();

// Get product image before deleting
$stmt = $conn->prepare("SELECT image FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if ($product) {
    // Delete image file from server (check all possible paths)
    if (!empty($product['image'])) {
        $possiblePaths = [
            __DIR__ . '/../uploads/products/' . $product['image'],
            __DIR__ . '/../uploads/products/' . basename($product['image']),
            __DIR__ . '/../' . $product['image'],
            __DIR__ . '/../' . ltrim($product['image'], '/'),
        ];

        foreach ($possiblePaths as $path) {
            $realPath = realpath($path);
            if ($realPath && file_exists($realPath)) {
                unlink($realPath);
                break; // File deleted, stop checking
            }
        }
    }

    // Delete from database
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);

    header('Location: products.php?deleted=1');
} else {
    header('Location: products.php?error=not_found');
}
exit;
?>
