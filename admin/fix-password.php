<?php
require_once __DIR__ . '/../config/database.php';

$conn = getConnection();

// Generate new password hash
$password = 'admin123';
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "New hash: " . $hash . "<br>";

// Update password
$stmt = $conn->prepare("UPDATE admin_users SET password = ? WHERE username = 'admin'");
$stmt->execute([$hash]);

echo "Password updated successfully!<br>";
echo "<br>Now try logging in with:<br>";
echo "Username: <strong>admin</strong><br>";
echo "Password: <strong>admin123</strong><br>";
echo "<br><a href='login.php'>Go to Login Page</a>";
?>
