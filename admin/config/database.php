<?php
// admin/config/database.php

require_once __DIR__ . '/config.php';

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

// Resolve asset URLs dynamically using current host inside the admin panel
if (!function_exists('resolveAdminAssetUrl')) {
    function resolveAdminAssetUrl($url) {
        if (is_array($url)) {
            return array_map('resolveAdminAssetUrl', $url);
        }
        $url = trim((string)$url);
        if ($url === '') {
            return '';
        }
        // If it's an external URL (not containing assets/uploads/), return it as is
        if (!str_contains($url, 'assets/uploads/')) {
            return $url;
        }
        // Extract filename and prepend dynamic UPLOAD_URL
        $filename = basename($url);
        return UPLOAD_URL . $filename;
    }
}

function getDBConnection() {
    $host = 'localhost';
    $db   = 'cloudcush_db';
    $user = 'root';
    $pass = '';
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        return new PDO($dsn, $user, $pass, $options);
    } catch (\PDOException $e) {
        // Log locally and raise error securely
        error_log("Database connection error: " . $e->getMessage());
        throw new \PDOException("Unable to connect to the database. Please check configuration settings.", (int)$e->getCode());
    }
}
