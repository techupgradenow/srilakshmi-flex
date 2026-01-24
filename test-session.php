<?php
/**
 * SESSION & LOGIN DEBUG
 * Tests session handling and admin authentication
 * DELETE AFTER DEBUGGING
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Session & Login Test</h1>";
echo "<pre>";

// Step 1: Session status before start
echo "=== STEP 1: Session Status ===\n";
echo "Session status before: " . session_status() . " (0=disabled, 1=none, 2=active)\n";

// Step 2: Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
echo "Session status after start: " . session_status() . "\n";
echo "Session ID: " . session_id() . "\n";
echo "Session save path: " . session_save_path() . "\n";

// Step 3: Test session write
echo "\n=== STEP 2: Session Write Test ===\n";
$_SESSION['test_time'] = time();
$_SESSION['test_value'] = 'session_works';
echo "Written to session: test_time=" . $_SESSION['test_time'] . "\n";
echo "Written to session: test_value=" . $_SESSION['test_value'] . "\n";

// Step 4: Check current session data
echo "\n=== STEP 3: Current Session Data ===\n";
print_r($_SESSION);

// Step 5: Test login function
echo "\n=== STEP 4: Login Function Test ===\n";
require_once __DIR__ . '/config/database.php';

// Get admin credentials
$conn = getConnection();
$admin = $conn->query("SELECT id, username, password FROM admin_users LIMIT 1")->fetch();

if ($admin) {
    echo "Admin found: {$admin['username']}\n";
    echo "Password hash: " . substr($admin['password'], 0, 20) . "...\n";
    echo "Hash length: " . strlen($admin['password']) . "\n";

    // Test password_verify with known password
    echo "\n=== STEP 5: Password Verification Test ===\n";
    $testPasswords = ['admin', 'admin123', 'password', '123456'];
    foreach ($testPasswords as $testPass) {
        $result = password_verify($testPass, $admin['password']);
        echo "Testing '$testPass': " . ($result ? "✓ MATCH" : "✗ No match") . "\n";
    }
} else {
    echo "✗ No admin user found!\n";
}

// Step 6: Test login simulation
echo "\n=== STEP 6: Login Simulation ===\n";
if (isset($_GET['test_login']) && isset($_GET['pass'])) {
    $testPassword = $_GET['pass'];
    echo "Testing login with password: $testPassword\n";

    if ($admin && password_verify($testPassword, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        echo "✓ Login SUCCESS!\n";
        echo "Session after login:\n";
        print_r($_SESSION);
    } else {
        echo "✗ Login FAILED - password incorrect\n";
    }
} else {
    echo "To test login, add: ?test_login=1&pass=YOUR_PASSWORD\n";
}

// Step 7: Check if already logged in
echo "\n=== STEP 7: Current Login Status ===\n";
if (isset($_SESSION['admin_id']) && isset($_SESSION['admin_username'])) {
    echo "✓ Currently logged in as: {$_SESSION['admin_username']} (ID: {$_SESSION['admin_id']})\n";
} else {
    echo "✗ Not currently logged in\n";
}

echo "\n=== SESSION TEST COMPLETE ===\n";
echo "</pre>";
echo "<p><a href='test-upload.php'>Next: Test Upload →</a></p>";
?>
