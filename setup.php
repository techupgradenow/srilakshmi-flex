<?php
/**
 * Production Setup Script
 * Run this ONCE after uploading files to production
 * DELETE THIS FILE AFTER SETUP IS COMPLETE
 */

// Prevent caching
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');

$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'test_db') {
        // Test database connection
        $host = $_POST['db_host'] ?? 'localhost';
        $name = $_POST['db_name'] ?? '';
        $user = $_POST['db_user'] ?? '';
        $pass = $_POST['db_pass'] ?? '';

        try {
            $conn = new PDO(
                "mysql:host=$host;dbname=$name;charset=utf8mb4",
                $user,
                $pass,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            $message = "Database connection successful!";
        } catch (PDOException $e) {
            $error = "Connection failed: " . $e->getMessage();
        }
    }

    if ($action === 'create_dirs') {
        // Create directories
        $dirs = [
            __DIR__ . '/uploads',
            __DIR__ . '/uploads/products'
        ];
        foreach ($dirs as $dir) {
            if (!is_dir($dir)) {
                if (mkdir($dir, 0755, true)) {
                    $message .= "Created: $dir<br>";
                } else {
                    $error .= "Failed to create: $dir<br>";
                }
            } else {
                $message .= "Exists: $dir<br>";
            }
        }

        // Create .htaccess for uploads (security)
        $htaccess = __DIR__ . '/uploads/.htaccess';
        if (!file_exists($htaccess)) {
            file_put_contents($htaccess, "Options -Indexes\n");
            $message .= "Created .htaccess for uploads<br>";
        }
    }

    if ($action === 'create_admin') {
        // Create admin user
        require_once 'config/database.php';
        $username = $_POST['admin_user'] ?? 'admin';
        $password = $_POST['admin_pass'] ?? '';

        if (empty($password)) {
            $error = "Password is required";
        } else {
            try {
                $conn = getConnection();
                $hash = password_hash($password, PASSWORD_DEFAULT);

                // Check if user exists
                $stmt = $conn->prepare("SELECT id FROM admin_users WHERE username = ?");
                $stmt->execute([$username]);

                if ($stmt->fetch()) {
                    // Update password
                    $stmt = $conn->prepare("UPDATE admin_users SET password = ? WHERE username = ?");
                    $stmt->execute([$hash, $username]);
                    $message = "Admin password updated successfully!";
                } else {
                    // Create new user
                    $stmt = $conn->prepare("INSERT INTO admin_users (username, password) VALUES (?, ?)");
                    $stmt->execute([$username, $hash]);
                    $message = "Admin user created successfully!";
                }
            } catch (PDOException $e) {
                $error = "Error: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Production Setup</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
        .card { background: white; padding: 20px; margin-bottom: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        h1 { color: #0a2854; }
        h2 { color: #0a2854; border-bottom: 2px solid #ffc107; padding-bottom: 10px; }
        input[type="text"], input[type="password"] { width: 100%; padding: 10px; margin: 5px 0 15px; border: 1px solid #ddd; border-radius: 4px; }
        button { background: #ffc107; color: #0a2854; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; }
        button:hover { background: #e5ac00; }
        .success { background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
        .error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
        .warning { background: #fff3cd; color: #856404; padding: 15px; border-radius: 4px; margin-top: 20px; }
        code { background: #e9ecef; padding: 2px 6px; border-radius: 3px; }
    </style>
</head>
<body>
    <h1>Sri Lakshmi Flex - Production Setup</h1>

    <?php if ($message): ?>
        <div class="success"><?php echo $message; ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <!-- Step 1: Test Database -->
    <div class="card">
        <h2>Step 1: Test Database Connection</h2>
        <form method="POST">
            <input type="hidden" name="action" value="test_db">
            <label>Database Host:</label>
            <input type="text" name="db_host" value="localhost" required>

            <label>Database Name:</label>
            <input type="text" name="db_name" placeholder="Your database name" required>

            <label>Database Username:</label>
            <input type="text" name="db_user" placeholder="Your database username" required>

            <label>Database Password:</label>
            <input type="password" name="db_pass" placeholder="Your database password">

            <button type="submit">Test Connection</button>
        </form>
        <p style="margin-top:15px;font-size:14px;color:#666;">
            After testing, update <code>config/database.php</code> with these credentials.
        </p>
    </div>

    <!-- Step 2: Create Directories -->
    <div class="card">
        <h2>Step 2: Create Upload Directories</h2>
        <form method="POST">
            <input type="hidden" name="action" value="create_dirs">
            <p>This will create the required directories for file uploads.</p>
            <button type="submit">Create Directories</button>
        </form>
    </div>

    <!-- Step 3: Create/Update Admin -->
    <div class="card">
        <h2>Step 3: Create/Update Admin User</h2>
        <form method="POST">
            <input type="hidden" name="action" value="create_admin">
            <label>Admin Username:</label>
            <input type="text" name="admin_user" value="admin" required>

            <label>Admin Password:</label>
            <input type="password" name="admin_pass" placeholder="Enter new password" required>

            <button type="submit">Create/Update Admin</button>
        </form>
    </div>

    <!-- Step 4: Run Migration -->
    <div class="card">
        <h2>Step 4: Run Database Migration</h2>
        <p>After database connection is working, run the migration to create all required tables.</p>
        <a href="admin/migrate.php" target="_blank">
            <button type="button">Run Migration</button>
        </a>
    </div>

    <!-- Step 5: Test -->
    <div class="card">
        <h2>Step 5: Test Everything</h2>
        <p>Test these URLs after setup:</p>
        <ul>
            <li><a href="debug.php" target="_blank">Run Diagnostic (debug.php)</a></li>
            <li><a href="admin/login.php" target="_blank">Admin Login</a></li>
            <li><a href="products.php" target="_blank">Products Page</a></li>
        </ul>
    </div>

    <div class="warning">
        <strong>IMPORTANT:</strong> Delete both <code>setup.php</code> and <code>debug.php</code> after setup is complete for security!
    </div>
</body>
</html>
