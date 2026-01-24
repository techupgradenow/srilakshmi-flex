<?php
/**
 * DATABASE CONNECTION DEBUG
 * This file tests ONLY the database connection
 * DELETE AFTER DEBUGGING
 */

// Enable ALL error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Database Connection Test</h1>";
echo "<pre>";

// Step 1: Check if config file exists
echo "=== STEP 1: Config File Check ===\n";
$configPath = __DIR__ . '/config/database.php';
if (file_exists($configPath)) {
    echo "✓ config/database.php EXISTS\n";
} else {
    die("✗ config/database.php NOT FOUND at: $configPath\n");
}

// Step 2: Load config
echo "\n=== STEP 2: Loading Config ===\n";
require_once $configPath;

echo "DB_HOST: " . DB_HOST . "\n";
echo "DB_NAME: " . DB_NAME . "\n";
echo "DB_USER: " . DB_USER . "\n";
echo "DB_PASS: " . (DB_PASS ? "[SET - " . strlen(DB_PASS) . " chars]" : "[EMPTY]") . "\n";

// Step 3: Test raw PDO connection
echo "\n=== STEP 3: Raw PDO Connection Test ===\n";
try {
    $testConn = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "✓ PDO Connection SUCCESS\n";

    // Test simple query
    $result = $testConn->query("SELECT 1 as test")->fetch();
    echo "✓ Simple query works: " . print_r($result, true) . "\n";

} catch (PDOException $e) {
    echo "✗ PDO Connection FAILED\n";
    echo "Error Code: " . $e->getCode() . "\n";
    echo "Error Message: " . $e->getMessage() . "\n";
    die("\nFix your database credentials in config/database.php\n");
}

// Step 4: Test getConnection() function
echo "\n=== STEP 4: getConnection() Function Test ===\n";
try {
    $conn = getConnection();
    echo "✓ getConnection() SUCCESS\n";
} catch (Exception $e) {
    echo "✗ getConnection() FAILED: " . $e->getMessage() . "\n";
    die();
}

// Step 5: Check tables exist
echo "\n=== STEP 5: Table Check ===\n";
$tables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
echo "Tables found: " . count($tables) . "\n";
foreach ($tables as $table) {
    echo "  - $table\n";
}

// Step 6: Check required tables
echo "\n=== STEP 6: Required Tables ===\n";
$requiredTables = ['admin_users', 'products', 'categories'];
foreach ($requiredTables as $table) {
    if (in_array($table, $tables)) {
        $count = $conn->query("SELECT COUNT(*) FROM $table")->fetchColumn();
        echo "✓ $table exists ($count rows)\n";
    } else {
        echo "✗ $table MISSING - Run migrate.php\n";
    }
}

// Step 7: Check admin user
echo "\n=== STEP 7: Admin User Check ===\n";
try {
    $admins = $conn->query("SELECT id, username, LENGTH(password) as pass_len FROM admin_users")->fetchAll();
    if (count($admins) > 0) {
        foreach ($admins as $admin) {
            echo "✓ Admin found: ID={$admin['id']}, Username={$admin['username']}, Password length={$admin['pass_len']}\n";
        }
    } else {
        echo "✗ No admin users found! Create one using setup.php\n";
    }
} catch (PDOException $e) {
    echo "✗ Error checking admin_users: " . $e->getMessage() . "\n";
}

// Step 8: Check products
echo "\n=== STEP 8: Products Check ===\n";
try {
    $products = $conn->query("SELECT id, name, status, image FROM products LIMIT 5")->fetchAll();
    echo "Products found: " . count($products) . "\n";
    foreach ($products as $p) {
        echo "  - ID:{$p['id']} | {$p['name']} | Status:{$p['status']} | Image:{$p['image']}\n";
    }
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

// Step 9: Test INSERT capability
echo "\n=== STEP 9: INSERT Test ===\n";
try {
    $testName = 'TEST_DELETE_ME_' . time();
    $stmt = $conn->prepare("INSERT INTO products (name, status) VALUES (?, 'inactive')");
    $stmt->execute([$testName]);
    $insertId = $conn->lastInsertId();
    echo "✓ INSERT works - Test ID: $insertId\n";

    // Clean up
    $conn->exec("DELETE FROM products WHERE id = $insertId");
    echo "✓ DELETE works - Cleaned up test record\n";
} catch (PDOException $e) {
    echo "✗ INSERT/DELETE failed: " . $e->getMessage() . "\n";
}

echo "\n=== DATABASE TEST COMPLETE ===\n";
echo "</pre>";
echo "<p><a href='test-session.php'>Next: Test Session →</a></p>";
?>
