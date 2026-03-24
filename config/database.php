<?php
/**
 * Database Configuration
 * Sri Lakshmi Flex
 * Auto-detects local vs production environment
 */

// Auto-detect environment
$_host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$_isProd = ($_host === 'srilakshmiads.in' || $_host === 'www.srilakshmiads.in');

if ($_isProd) {
    // ========== PRODUCTION (Hostinger) ==========
    define('DB_HOST', '127.0.0.1');
    define('DB_NAME', 'u282002960_srilakshmiarts');
    define('DB_USER', 'u282002960_srilakshmiarts');
    define('DB_PASS', 'Srilakshmi@9092');
} else {
    // ========== LOCAL (XAMPP) ==========
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
        $h = $_SERVER['HTTP_HOST'] ?? '';
        if ($h === 'srilakshmiads.in' || $h === 'www.srilakshmiads.in') {
            die("Database connection error. Please contact administrator.");
        }
        die("Connection failed: " . $e->getMessage());
    }
}
?>
