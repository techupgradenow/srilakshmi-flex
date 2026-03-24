<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Testing Banner Files</h2><pre>";

echo "PHP Version: " . PHP_VERSION . "\n\n";

// Test DB
echo "=== DB Connection ===\n";
try {
    require_once __DIR__ . '/config/database.php';
    $conn = getConnection();
    echo "DB: OK\n";

    $tables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables: " . implode(', ', $tables) . "\n";

    $count = $conn->query("SELECT COUNT(*) FROM banners")->fetchColumn();
    echo "Banners count: $count\n\n";
} catch (Exception $e) {
    echo "DB Error: " . $e->getMessage() . "\n\n";
}

// Test include files
echo "=== File Check ===\n";
$files = [
    'admin/auth.php',
    'admin/banners.php',
    'admin/add-banner.php',
    'admin/edit-banner.php',
    'admin/delete-banner.php',
    'admin/toggle-banner.php',
    'admin/login.php',
    'api/banners.php',
    'config/database.php',
];
foreach ($files as $f) {
    $path = __DIR__ . '/' . $f;
    $exists = file_exists($path);
    $size = $exists ? filesize($path) : 0;
    echo ($exists ? "OK" : "MISSING") . " - $f ($size bytes)\n";
}

echo "\n=== Uploads Dir ===\n";
$dir = __DIR__ . '/uploads/banners';
echo "uploads/banners: " . (file_exists($dir) ? "EXISTS" : "MISSING") . "\n";
echo "Writable: " . (is_writable($dir) ? "YES" : "NO") . "\n";

// Try loading add-banner.php to catch the error
echo "\n=== Testing add-banner.php syntax ===\n";
try {
    $code = file_get_contents(__DIR__ . '/admin/add-banner.php');
    $tokens = @token_get_all($code);
    echo "Syntax parse: OK (" . count($tokens) . " tokens)\n";
} catch (Exception $e) {
    echo "Parse error: " . $e->getMessage() . "\n";
}

// Check PHP error log
echo "\n=== Last PHP Error ===\n";
$err = error_get_last();
echo $err ? print_r($err, true) : "No errors\n";

echo "</pre>";
