<?php
/**
 * COMPLETE SYSTEM TEST
 * Runs all tests in sequence and provides summary
 * DELETE AFTER DEBUGGING
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

$results = [];

echo "<!DOCTYPE html><html><head><title>Complete System Test</title>";
echo "<style>
body{font-family:Arial,sans-serif;max-width:1000px;margin:20px auto;padding:20px;}
.pass{color:green;font-weight:bold;}
.fail{color:red;font-weight:bold;}
.warn{color:orange;font-weight:bold;}
.test-section{background:#f9f9f9;padding:15px;margin:10px 0;border-radius:5px;border-left:4px solid #ccc;}
.test-section.pass-section{border-left-color:green;}
.test-section.fail-section{border-left-color:red;}
table{width:100%;border-collapse:collapse;margin:20px 0;}
th,td{padding:10px;border:1px solid #ddd;text-align:left;}
th{background:#0a2854;color:white;}
.summary{background:#0a2854;color:white;padding:20px;border-radius:5px;margin-top:20px;}
</style></head><body>";

echo "<h1>Sri Lakshmi Flex - Complete System Test</h1>";
echo "<p>Server: " . ($_SERVER['HTTP_HOST'] ?? 'CLI') . "</p>";
echo "<p>Time: " . date('Y-m-d H:i:s') . "</p>";

// ==================== TEST 1: PHP Environment ====================
echo "<div class='test-section'>";
echo "<h2>1. PHP Environment</h2>";

$phpVersion = phpversion();
$phpOk = version_compare($phpVersion, '7.4', '>=');
$results['PHP Version'] = $phpOk ? 'PASS' : 'FAIL';
echo "<p>PHP Version: $phpVersion " . ($phpOk ? "<span class='pass'>✓</span>" : "<span class='fail'>✗ Requires 7.4+</span>") . "</p>";

$extensions = ['pdo', 'pdo_mysql', 'session', 'fileinfo'];
foreach ($extensions as $ext) {
    $loaded = extension_loaded($ext);
    $results["Extension: $ext"] = $loaded ? 'PASS' : 'FAIL';
    echo "<p>$ext: " . ($loaded ? "<span class='pass'>✓ Loaded</span>" : "<span class='fail'>✗ Missing</span>") . "</p>";
}
echo "</div>";

// ==================== TEST 2: Database ====================
echo "<div class='test-section'>";
echo "<h2>2. Database Connection</h2>";

$dbOk = false;
$conn = null;

if (file_exists(__DIR__ . '/config/database.php')) {
    require_once __DIR__ . '/config/database.php';
    echo "<p>Config: DB_HOST=" . DB_HOST . ", DB_NAME=" . DB_NAME . ", DB_USER=" . DB_USER . "</p>";

    try {
        $conn = getConnection();
        $dbOk = true;
        $results['Database Connection'] = 'PASS';
        echo "<p><span class='pass'>✓ Connected successfully</span></p>";

        // Check tables
        $tables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        echo "<p>Tables found: " . implode(', ', $tables) . "</p>";

        $requiredTables = ['admin_users', 'products'];
        foreach ($requiredTables as $table) {
            $exists = in_array($table, $tables);
            $results["Table: $table"] = $exists ? 'PASS' : 'FAIL';
            if ($exists) {
                $count = $conn->query("SELECT COUNT(*) FROM $table")->fetchColumn();
                echo "<p>$table: <span class='pass'>✓ Exists ($count rows)</span></p>";
            } else {
                echo "<p>$table: <span class='fail'>✗ Missing</span></p>";
            }
        }
    } catch (PDOException $e) {
        $results['Database Connection'] = 'FAIL';
        echo "<p><span class='fail'>✗ Connection failed: " . $e->getMessage() . "</span></p>";
    }
} else {
    $results['Database Connection'] = 'FAIL';
    echo "<p><span class='fail'>✗ config/database.php not found</span></p>";
}
echo "</div>";

// ==================== TEST 3: Admin User ====================
echo "<div class='test-section'>";
echo "<h2>3. Admin User</h2>";

if ($conn) {
    try {
        $admin = $conn->query("SELECT id, username, LENGTH(password) as plen FROM admin_users LIMIT 1")->fetch();
        if ($admin) {
            $results['Admin User'] = 'PASS';
            echo "<p><span class='pass'>✓ Admin exists: {$admin['username']} (password length: {$admin['plen']})</span></p>";

            // Check password format
            $fullAdmin = $conn->query("SELECT password FROM admin_users LIMIT 1")->fetchColumn();
            if (strpos($fullAdmin, '$2y$') === 0) {
                echo "<p><span class='pass'>✓ Password is properly hashed (bcrypt)</span></p>";
            } else {
                echo "<p><span class='fail'>✗ Password may not be hashed correctly</span></p>";
            }
        } else {
            $results['Admin User'] = 'FAIL';
            echo "<p><span class='fail'>✗ No admin user found</span></p>";
        }
    } catch (PDOException $e) {
        $results['Admin User'] = 'FAIL';
        echo "<p><span class='fail'>✗ Error: " . $e->getMessage() . "</span></p>";
    }
} else {
    $results['Admin User'] = 'SKIP';
    echo "<p><span class='warn'>⚠ Skipped - no database connection</span></p>";
}
echo "</div>";

// ==================== TEST 4: Session ====================
echo "<div class='test-section'>";
echo "<h2>4. Session Handling</h2>";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$sessionOk = session_status() === PHP_SESSION_ACTIVE;
$results['Session'] = $sessionOk ? 'PASS' : 'FAIL';
echo "<p>Session status: " . ($sessionOk ? "<span class='pass'>✓ Active</span>" : "<span class='fail'>✗ Not active</span>") . "</p>";
echo "<p>Session ID: " . session_id() . "</p>";

$_SESSION['test'] = time();
$results['Session Write'] = isset($_SESSION['test']) ? 'PASS' : 'FAIL';
echo "<p>Session write: " . (isset($_SESSION['test']) ? "<span class='pass'>✓ Working</span>" : "<span class='fail'>✗ Failed</span>") . "</p>";
echo "</div>";

// ==================== TEST 5: Upload Directory ====================
echo "<div class='test-section'>";
echo "<h2>5. Upload Directory</h2>";

$uploadDir = __DIR__ . '/uploads/products';

if (!is_dir($uploadDir)) {
    @mkdir($uploadDir, 0755, true);
}

$dirExists = is_dir($uploadDir);
$dirWritable = is_writable($uploadDir);

$results['Upload Directory Exists'] = $dirExists ? 'PASS' : 'FAIL';
$results['Upload Directory Writable'] = $dirWritable ? 'PASS' : 'FAIL';

echo "<p>Directory exists: " . ($dirExists ? "<span class='pass'>✓ Yes</span>" : "<span class='fail'>✗ No</span>") . "</p>";
echo "<p>Directory writable: " . ($dirWritable ? "<span class='pass'>✓ Yes</span>" : "<span class='fail'>✗ No</span>") . "</p>";

if ($dirExists) {
    $perms = substr(sprintf('%o', fileperms($uploadDir)), -4);
    echo "<p>Permissions: $perms</p>";

    // Count files
    $files = array_diff(scandir($uploadDir), ['.', '..']);
    echo "<p>Files in directory: " . count($files) . "</p>";
}
echo "</div>";

// ==================== TEST 6: Products ====================
echo "<div class='test-section'>";
echo "<h2>6. Products</h2>";

if ($conn) {
    try {
        $total = $conn->query("SELECT COUNT(*) FROM products")->fetchColumn();
        $active = $conn->query("SELECT COUNT(*) FROM products WHERE status = 'active'")->fetchColumn();

        $results['Products Exist'] = ($total > 0) ? 'PASS' : 'WARN';
        $results['Active Products'] = ($active > 0) ? 'PASS' : 'WARN';

        echo "<p>Total products: $total</p>";
        echo "<p>Active products: " . ($active > 0 ? "<span class='pass'>$active</span>" : "<span class='warn'>$active (none will display!)</span>") . "</p>";

        // Check images
        $products = $conn->query("SELECT name, image FROM products WHERE status = 'active' LIMIT 5")->fetchAll();
        echo "<p>Image check for active products:</p><ul>";
        foreach ($products as $p) {
            $imgExists = false;
            if (!empty($p['image'])) {
                $path1 = __DIR__ . '/uploads/products/' . $p['image'];
                $path2 = __DIR__ . '/' . $p['image'];
                $imgExists = file_exists($path1) || file_exists($path2);
            }
            echo "<li>{$p['name']}: " . ($imgExists ? "<span class='pass'>✓ Image found</span>" : "<span class='warn'>⚠ Image missing</span>") . "</li>";
        }
        echo "</ul>";

    } catch (PDOException $e) {
        echo "<p><span class='fail'>✗ Error: " . $e->getMessage() . "</span></p>";
    }
} else {
    $results['Products'] = 'SKIP';
    echo "<p><span class='warn'>⚠ Skipped - no database connection</span></p>";
}
echo "</div>";

// ==================== TEST 7: Critical Files ====================
echo "<div class='test-section'>";
echo "<h2>7. Critical Files</h2>";

$files = [
    'index.html' => 'Homepage',
    'products.php' => 'Products page',
    'admin/login.php' => 'Admin login',
    'admin/index.php' => 'Admin dashboard',
    'admin/products.php' => 'Admin products',
    'admin/add-product.php' => 'Add product',
    'admin/auth.php' => 'Authentication',
    'config/database.php' => 'Database config'
];

foreach ($files as $file => $desc) {
    $exists = file_exists(__DIR__ . '/' . $file);
    $results["File: $file"] = $exists ? 'PASS' : 'FAIL';
    echo "<p>$desc ($file): " . ($exists ? "<span class='pass'>✓</span>" : "<span class='fail'>✗ Missing</span>") . "</p>";
}
echo "</div>";

// ==================== SUMMARY ====================
echo "<div class='summary'>";
echo "<h2>Summary</h2>";

$passed = count(array_filter($results, fn($v) => $v === 'PASS'));
$failed = count(array_filter($results, fn($v) => $v === 'FAIL'));
$warned = count(array_filter($results, fn($v) => $v === 'WARN'));
$total = count($results);

echo "<p style='font-size:24px;'>Passed: $passed / $total</p>";
if ($failed > 0) {
    echo "<p style='color:#ff6b6b;'>Failed: $failed</p>";
}
if ($warned > 0) {
    echo "<p style='color:#ffc107;'>Warnings: $warned</p>";
}

if ($failed === 0) {
    echo "<p style='color:#90EE90;font-size:18px;'>✓ All critical tests passed! Your site should work.</p>";
} else {
    echo "<p style='color:#ff6b6b;'>✗ Some tests failed. Fix the issues above.</p>";
}
echo "</div>";

// Results table
echo "<h2>Detailed Results</h2>";
echo "<table><tr><th>Test</th><th>Result</th></tr>";
foreach ($results as $test => $result) {
    $class = $result === 'PASS' ? 'pass' : ($result === 'FAIL' ? 'fail' : 'warn');
    echo "<tr><td>$test</td><td class='$class'>$result</td></tr>";
}
echo "</table>";

// Actions
echo "<h2>Quick Actions</h2>";
echo "<p><a href='admin/migrate.php' target='_blank'>Run Database Migration</a></p>";
echo "<p><a href='setup.php' target='_blank'>Run Setup Wizard</a></p>";
echo "<p><a href='admin/login.php' target='_blank'>Test Admin Login</a></p>";
echo "<p><a href='products.php' target='_blank'>Test Products Page</a></p>";

echo "<p style='color:red;margin-top:30px;'><strong>DELETE ALL TEST FILES AFTER DEBUGGING:</strong> test-db.php, test-session.php, test-upload.php, test-products.php, test-all.php, debug.php, setup.php</p>";

echo "</body></html>";
?>
