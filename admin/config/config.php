<?php
// admin/config/config.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Timezone setup
date_default_timezone_set('Asia/Kolkata');

// Base URL configuration (absolute path mapping)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || ($_SERVER['SERVER_PORT'] ?? '') == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'www.cloudcush.in';
if ($host === 'localhost' || $host === '127.0.0.1') {
    define('BASE_URL', $protocol . $host . '/cloudcush/admin/');
} else {
    define('BASE_URL', $protocol . $host . '/admin/');
}

// Global definitions
define('SITE_NAME', 'CloudCush Admin Portal');
define('ADMIN_EMAIL', 'admin@cloudcush.com');

// Directories configuration
define('UPLOAD_DIR', dirname(__DIR__) . '/assets/uploads/');
define('UPLOAD_URL', BASE_URL . 'assets/uploads/');
