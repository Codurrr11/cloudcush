<?php
// admin/handlers/auth/logout-handler.php
require_once __DIR__ . '/../../config/config.php';

// Unset session values and destroy the session
$_SESSION = [];

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

// Start fresh session to deliver logout flash message
session_start();
$_SESSION['flash_message'] = 'You have logged out successfully.';
$_SESSION['flash_type'] = 'success';

header('Location: ' . BASE_URL . 'auth/login.php');
exit;
