<?php
/**
 * Database Configuration
 * ProPrint Solutions Admin Panel
 */

// Auto-detect environment
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$isProduction = ($host === 'srilakshmiads.in' || $host === 'www.srilakshmiads.in');

if ($isProduction) {
    // ========== PRODUCTION (Hostinger) ==========
    error_reporting(0);
    ini_set('display_errors', 0);

    define('DB_HOST', '127.0.0.1');
    define('DB_NAME', 'u282002960_srilakshmiarts');
    define('DB_USER', 'u282002960_srilakshmiarts');
    define('DB_PASS', 'Srilakshmi@9092');

    define('SITE_NAME', 'Sri Lakshmi Ads');
    define('SITE_URL', 'https://srilakshmiads.in');
} else {
    // ========== LOCAL (XAMPP) ==========
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    define('DB_HOST', 'localhost');
    define('DB_NAME', 'proprint_db');
    define('DB_USER', 'root');
    define('DB_PASS', '');

    define('SITE_NAME', 'Sri Lakshmi Ads');
    define('SITE_URL', 'http://localhost/srilakshmi-flex');
}

define('ADMIN_URL', SITE_URL . '/admin');

// Upload configuration
define('UPLOAD_DIR', dirname(__DIR__) . '/uploads/products/');
define('UPLOAD_URL', SITE_URL . '/uploads/products/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// Session configuration
define('SESSION_LIFETIME', 3600); // 1 hour

// Create upload directory if not exists
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

// Database connection
function getDBConnection() {
    static $conn = null;

    if ($conn === null) {
        try {
            $conn = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    return $conn;
}

// Helper function to sanitize input
function sanitize($input) {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Helper function to generate slug
function generateSlug($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return $text ?: 'product-' . time();
}

// Helper function to format price
function formatPrice($price) {
    if ($price === null || $price === '') {
        return 'Contact for Price';
    }
    return '₹' . number_format($price, 2);
}
?>
