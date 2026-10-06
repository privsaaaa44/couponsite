<?php
// connection.php - Database connection & global helper functions
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = 'localhost';
$dbname = 'coupon';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host={$host};dbname={$dbname};charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    die("Database Connection Error. Please verify database setup: " . htmlspecialchars($e->getMessage()));
}

// Generate or retrieve CSRF token
function get_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Render CSRF hidden input field
function csrf_field() {
    $token = get_csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

// Validate CSRF token
function verify_csrf_token($token) {
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

// Base URL helper
function base_url($path = '') {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    // Automatically detect folder
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    // Strip /admin or /includes if inside subfolders
    $baseDir = preg_replace('/(\/admin|\/includes)$/', '', $scriptDir);
    $baseDir = rtrim($baseDir, '/');
    
    $path = ltrim($path, '/');
    return $protocol . '://' . $host . $baseDir . ($path ? '/' . $path : '');
}

// Clean escape helper
function e($str) {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}
?>