<?php
/**
 * Cache Clear Utility
 * Clears OPcache and LiteSpeed cache on Hostinger
 * DELETE this file after use!
 */

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

echo "<h2>Cache Clear Results</h2><pre>";

// Clear OPcache
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "✅ OPcache cleared!\n";
} else {
    echo "⚠️ OPcache not available\n";
}

// Clear individual files from OPcache
$files = [
    __DIR__ . '/admin/banners.php',
    __DIR__ . '/admin/add-banner.php',
    __DIR__ . '/admin/edit-banner.php',
    __DIR__ . '/admin/delete-banner.php',
    __DIR__ . '/admin/toggle-banner.php',
    __DIR__ . '/admin/login.php',
    __DIR__ . '/admin/auth.php',
    __DIR__ . '/admin/index.php',
    __DIR__ . '/api/banners.php',
    __DIR__ . '/config/database.php',
];

if (function_exists('opcache_invalidate')) {
    foreach ($files as $f) {
        if (file_exists($f)) {
            opcache_invalidate($f, true);
            echo "✅ Invalidated: " . basename(dirname($f)) . "/" . basename($f) . "\n";
        } else {
            echo "❌ Missing: " . basename(dirname($f)) . "/" . basename($f) . "\n";
        }
    }
}

// LiteSpeed cache purge
if (isset($_SERVER['LSWS_EDITION']) || stripos($_SERVER['SERVER_SOFTWARE'] ?? '', 'litespeed') !== false) {
    header('X-LiteSpeed-Purge: *');
    echo "\n✅ LiteSpeed cache purge header sent!\n";
} else {
    echo "\n⚠️ Not LiteSpeed server\n";
}

// Show file info
echo "\n=== File Status ===\n";
echo "PHP Version: " . PHP_VERSION . "\n\n";
foreach ($files as $f) {
    if (file_exists($f)) {
        $size = filesize($f);
        $time = date('Y-m-d H:i:s', filemtime($f));
        $name = basename(dirname($f)) . "/" . basename($f);
        echo "OK  $name  ($size bytes, modified: $time)\n";
    }
}

// Test DB
echo "\n=== DB Test ===\n";
try {
    require_once __DIR__ . '/config/database.php';
    $conn = getConnection();
    $tables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "Connected! Tables: " . implode(', ', $tables) . "\n";

    $count = $conn->query("SELECT COUNT(*) FROM banners")->fetchColumn();
    echo "Banners: $count rows\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// Test add-banner.php for errors
echo "\n=== Syntax Check ===\n";
error_reporting(E_ALL);
ini_set('display_errors', 1);
foreach ($files as $f) {
    if (file_exists($f)) {
        $output = null;
        $retval = null;
        exec('php -l ' . escapeshellarg($f) . ' 2>&1', $output, $retval);
        $name = basename(dirname($f)) . "/" . basename($f);
        echo ($retval === 0 ? "✅" : "❌") . " $name: " . implode(' ', $output) . "\n";
    }
}

echo "\n</pre>";
echo "<p><strong>Now try:</strong> <a href='/admin/banners.php'>Admin Banners</a> | <a href='/admin/add-banner.php'>Add Banner</a> | <a href='/api/banners.php'>API</a></p>";
echo "<p style='color:red;'>⚠️ DELETE this file (clear-cache.php) after use!</p>";
