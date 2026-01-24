<?php
/**
 * Database Configuration
 * Sri Lakshmi Flex
 * Auto-detects local vs production environment
 */

// Detect environment based on server
$isProduction = (strpos($_SERVER['HTTP_HOST'] ?? '', 'upgradenow.in') !== false)
                || (strpos($_SERVER['HTTP_HOST'] ?? '', 'lakshmiflex') !== false);

if ($isProduction) {
    // ========== PRODUCTION CREDENTIALS ==========
    // Update these with your hosting provider's database details
    define('DB_HOST', 'localhost');           // Usually 'localhost' or check your hosting panel
    define('DB_NAME', 'your_db_name');        // Database name from hosting panel
    define('DB_USER', 'your_db_user');        // Database username from hosting panel
    define('DB_PASS', 'your_db_password');    // Database password from hosting panel
} else {
    // ========== LOCAL CREDENTIALS (XAMPP) ==========
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'proprint_db');
    define('DB_USER', 'root');
    define('DB_PASS', '');
}

// Create database connection
function getConnection() {
    try {
        $conn = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );
        return $conn;
    } catch (PDOException $e) {
        // In production, show generic error
        if (strpos($_SERVER['HTTP_HOST'] ?? '', 'upgradenow.in') !== false) {
            die("Database connection error. Please contact administrator.");
        }
        die("Connection failed: " . $e->getMessage());
    }
}
?>
