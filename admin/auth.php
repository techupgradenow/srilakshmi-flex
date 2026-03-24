<?php
/**
 * Authentication Helper
 * Handles session management and login verification
 */

// Ensure session is started properly (production-safe)
if (session_status() === PHP_SESSION_NONE) {
    // Set secure session settings for production
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        ini_set('session.cookie_secure', 1);
    }
    session_start();
}

require_once __DIR__ . '/../config/database.php';

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['admin_id']) && isset($_SESSION['admin_username']);
}

// Require login - redirect if not authenticated
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /admin/login.php');
        exit;
    }
}

// Login user
function loginUser($username, $password) {
    $conn = getConnection();

    $stmt = $conn->prepare("SELECT id, username, password FROM admin_users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['admin_id'] = $user['id'];
        $_SESSION['admin_username'] = $user['username'];
        return true;
    }

    return false;
}

// Logout user
function logoutUser() {
    session_unset();
    session_destroy();
    header('Location: /admin/login.php');
    exit;
}
?>
