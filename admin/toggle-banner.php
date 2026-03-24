<?php
/**
 * Toggle Banner Status
 * Switches banner between active/inactive
 */
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

require_once 'auth.php';
requireLogin();

require_once __DIR__ . '/../config/database.php';

$conn = getConnection();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    header('Location: banners.php');
    exit;
}

$stmt = $conn->prepare("SELECT status FROM banners WHERE id = ?");
$stmt->execute([$id]);
$banner = $stmt->fetch();

if (!$banner) {
    header('Location: banners.php?error=not_found');
    exit;
}

$newStatus = $banner['status'] === 'active' ? 'inactive' : 'active';

$stmt = $conn->prepare("UPDATE banners SET status = ? WHERE id = ?");
$stmt->execute([$newStatus, $id]);

header('Location: banners.php?toggled=1');
exit;
?>
