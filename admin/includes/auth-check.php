<?php
// admin/includes/auth-check.php
require_once __DIR__ . '/../config/config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // Save current URL for potential redirect after login
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    
    $_SESSION['flash_message'] = 'Please log in to access the admin portal.';
    $_SESSION['flash_type'] = 'warning';
    
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit;
}
