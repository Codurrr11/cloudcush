<?php
// admin/index.php
require_once __DIR__ . '/config/config.php';

// If session shows user is authenticated, redirect to dashboard. Otherwise, redirect to login page.
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: ' . BASE_URL . 'dashboard/index.php');
} else {
    header('Location: ' . BASE_URL . 'auth/login.php');
}
exit;
    