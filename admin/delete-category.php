<?php
/**
 * Delete Category
 * Sri Lakshmi Admin Panel
 */

require_once 'auth.php';
requireLogin();

require_once __DIR__ . '/../config/database.php';

$conn = getConnection();

// Get category ID
$categoryId = $_GET['id'] ?? null;

if (!$categoryId) {
    header('Location: categories.php');
    exit;
}

// Check if category exists
$stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
$stmt->execute([$categoryId]);
$category = $stmt->fetch();

if (!$category) {
    header('Location: categories.php');
    exit;
}

// Delete category image file if exists
if (!empty($category['image'])) {
    $imagePath = __DIR__ . '/../uploads/categories/' . $category['image'];
    if (file_exists($imagePath)) {
        unlink($imagePath);
    }
}

// Set products in this category to NULL (no category)
$stmt = $conn->prepare("UPDATE products SET category_id = NULL WHERE category_id = ?");
$stmt->execute([$categoryId]);

// Delete the category
$stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
if ($stmt->execute([$categoryId])) {
    header('Location: categories.php?deleted=1');
} else {
    header('Location: categories.php?error=delete_failed');
}
exit;
?>
