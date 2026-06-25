<?php
// includes/db.php — Frontend DB connection (shared with admin config)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Asia/Kolkata');

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || ($_SERVER['SERVER_PORT'] ?? '') == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'www.cloudcush.in';
if ($host === 'localhost' || $host === '127.0.0.1') {
    define('FRONTEND_BASE_URL', $protocol . $host . '/cloudcush/');
} else {
    define('FRONTEND_BASE_URL', $protocol . $host . '/');
}

// Resolve asset URLs to relative path to handle domain configuration on deployment
if (!function_exists('resolveAssetUrl')) {
    function resolveAssetUrl($url) {
        if (is_array($url)) {
            return array_map('resolveAssetUrl', $url);
        }
        $url = trim((string)$url);
        if ($url === '') {
            return '';
        }
        $posAdmin = strpos($url, 'admin/assets/uploads/');
        if ($posAdmin !== false) {
            return substr($url, $posAdmin);
        }
        $posUploads = strpos($url, 'assets/uploads/');
        if ($posUploads !== false) {
            return 'admin/' . substr($url, $posUploads);
        }
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://') || str_starts_with($url, '//')) {
            return $url;
        }
        return $url;
    }
}

function getFrontendDB() {
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    $host    = 'localhost';
    $db      = 'cloudcush_db';
    $user    = 'root';
    $pass    = '';
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
        return $pdo;
    } catch (\PDOException $e) {
        error_log("Frontend DB error: " . $e->getMessage());
        die("Database connection failed. Please try again later.");
    }
}
