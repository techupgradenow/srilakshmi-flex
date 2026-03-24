<?php
/**
 * Delete Category Handler
 * Deletes category from DB, its image file from server,
 * and sets all products in this category to NULL
 */

require_once 'auth.php';
requireLogin();

require_once __DIR__ . '/../config/database.php';

$conn = getConnection();

$categoryId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$categoryId) {
    header('Location: categories.php');
    exit;
}

// Get category before deleting
$stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
$stmt->execute([$categoryId]);
$category = $stmt->fetch();

if (!$category) {
    header('Location: categories.php?error=not_found');
    exit;
}

// Delete all product images belonging to this category, then set category_id to NULL
$stmt = $conn->prepare("SELECT image FROM products WHERE category_id = ?");
$stmt->execute([$categoryId]);
$products = $stmt->fetchAll();

foreach ($products as $product) {
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
                break;
            }
        }
    }
}

// Delete all products in this category from DB
$stmt = $conn->prepare("DELETE FROM products WHERE category_id = ?");
$stmt->execute([$categoryId]);

// Delete category image file from server
if (!empty($category['image'])) {
    $possiblePaths = [
        __DIR__ . '/../uploads/categories/' . $category['image'],
        __DIR__ . '/../uploads/categories/' . basename($category['image']),
        __DIR__ . '/../' . $category['image'],
        __DIR__ . '/../' . ltrim($category['image'], '/'),
    ];
    foreach ($possiblePaths as $path) {
        $realPath = realpath($path);
        if ($realPath && file_exists($realPath)) {
            unlink($realPath);
            break;
        }
    }
}

// Delete category from DB
$stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
if ($stmt->execute([$categoryId])) {
    header('Location: categories.php?deleted=1');
} else {
    header('Location: categories.php?error=delete_failed');
}
exit;
?>
