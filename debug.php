<?php
/**
 * Production Diagnostic Tool
 * DELETE THIS FILE AFTER DEBUGGING
 */

echo "<h1>Production Environment Diagnostic</h1>";
echo "<style>body{font-family:Arial,sans-serif;padding:20px;} .ok{color:green;} .error{color:red;} .warn{color:orange;} pre{background:#f5f5f5;padding:10px;border-radius:5px;}</style>";

// 1. PHP Version
echo "<h2>1. PHP Environment</h2>";
echo "<p>PHP Version: <strong>" . phpversion() . "</strong></p>";

// 2. Required Extensions
echo "<h2>2. Required PHP Extensions</h2>";
$extensions = ['pdo', 'pdo_mysql', 'mysqli', 'session', 'fileinfo'];
foreach ($extensions as $ext) {
    $status = extension_loaded($ext) ? "<span class='ok'>OK</span>" : "<span class='error'>MISSING</span>";
    echo "<p>$ext: $status</p>";
}

// 3. Server Info
echo "<h2>3. Server Information</h2>";
echo "<p>HTTP Host: <strong>" . ($_SERVER['HTTP_HOST'] ?? 'Not Set') . "</strong></p>";
echo "<p>Document Root: <strong>" . ($_SERVER['DOCUMENT_ROOT'] ?? 'Not Set') . "</strong></p>";
echo "<p>Script Path: <strong>" . __DIR__ . "</strong></p>";

// 4. Database Connection Test
echo "<h2>4. Database Connection Test</h2>";
try {
    require_once 'config/database.php';
    echo "<p>DB_HOST: <strong>" . DB_HOST . "</strong></p>";
    echo "<p>DB_NAME: <strong>" . DB_NAME . "</strong></p>";
    echo "<p>DB_USER: <strong>" . DB_USER . "</strong></p>";

    $conn = getConnection();
    echo "<p class='ok'>Database Connection: SUCCESS</p>";

    // Check tables
    echo "<h3>Database Tables:</h3>";
    $tables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "<pre>" . print_r($tables, true) . "</pre>";

    // Check admin_users
    if (in_array('admin_users', $tables)) {
        $count = $conn->query("SELECT COUNT(*) FROM admin_users")->fetchColumn();
        echo "<p class='ok'>admin_users table exists with $count user(s)</p>";
    } else {
        echo "<p class='error'>admin_users table MISSING</p>";
    }

    // Check products
    if (in_array('products', $tables)) {
        $count = $conn->query("SELECT COUNT(*) FROM products")->fetchColumn();
        echo "<p class='ok'>products table exists with $count product(s)</p>";
    } else {
        echo "<p class='error'>products table MISSING</p>";
    }

    // Check categories
    if (in_array('categories', $tables)) {
        $count = $conn->query("SELECT COUNT(*) FROM categories")->fetchColumn();
        echo "<p class='ok'>categories table exists with $count category(ies)</p>";
    } else {
        echo "<p class='warn'>categories table MISSING - Run migrate.php</p>";
    }

} catch (Exception $e) {
    echo "<p class='error'>Database Connection: FAILED</p>";
    echo "<pre class='error'>" . $e->getMessage() . "</pre>";
}

// 5. Directory Permissions
echo "<h2>5. Directory Permissions</h2>";
$uploadDir = __DIR__ . '/uploads';
$productDir = __DIR__ . '/uploads/products';

if (is_dir($uploadDir)) {
    echo "<p class='ok'>uploads/ directory exists</p>";
    echo "<p>Writable: " . (is_writable($uploadDir) ? "<span class='ok'>YES</span>" : "<span class='error'>NO</span>") . "</p>";
} else {
    echo "<p class='error'>uploads/ directory MISSING</p>";
    // Try to create
    if (@mkdir($uploadDir, 0755, true)) {
        echo "<p class='ok'>Created uploads/ directory</p>";
    }
}

if (is_dir($productDir)) {
    echo "<p class='ok'>uploads/products/ directory exists</p>";
    echo "<p>Writable: " . (is_writable($productDir) ? "<span class='ok'>YES</span>" : "<span class='error'>NO</span>") . "</p>";
} else {
    echo "<p class='error'>uploads/products/ directory MISSING</p>";
    if (@mkdir($productDir, 0755, true)) {
        echo "<p class='ok'>Created uploads/products/ directory</p>";
    }
}

// 6. Session Test
echo "<h2>6. Session Test</h2>";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['test'] = 'working';
echo "<p>Session Status: " . (session_status() === PHP_SESSION_ACTIVE ? "<span class='ok'>ACTIVE</span>" : "<span class='error'>INACTIVE</span>") . "</p>";
echo "<p>Session ID: " . session_id() . "</p>";

// 7. File Structure Check
echo "<h2>7. Critical Files Check</h2>";
$criticalFiles = [
    'config/database.php',
    'products.php',
    'admin/login.php',
    'admin/auth.php',
    'admin/index.php',
    'admin/products.php',
    'admin/add-product.php'
];
foreach ($criticalFiles as $file) {
    $exists = file_exists(__DIR__ . '/' . $file);
    $status = $exists ? "<span class='ok'>EXISTS</span>" : "<span class='error'>MISSING</span>";
    echo "<p>$file: $status</p>";
}

echo "<hr>";
echo "<p style='color:red;font-weight:bold;'>DELETE THIS FILE (debug.php) AFTER DEBUGGING!</p>";
?>
