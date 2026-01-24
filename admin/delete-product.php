<?php
/**
 * Delete Product Handler
 */

require_once 'auth.php';
requireLogin();

require_once __DIR__ . '/../config/database.php';

if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $conn = getConnection();

    // Get product image filename
    $stmt = $conn->prepare("SELECT image FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();

    if ($product) {
        // Delete image file
        $imagePath = __DIR__ . '/../uploads/products/' . $product['image'];
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }

        // Delete from database
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$id]);
    }
}

header('Location: dashboard.php');
exit;
?>
