<?php
// logout.php — Destroys customer session and redirects to login
require_once __DIR__ . '/includes/db.php';

// Clear all customer session variables
unset(
    $_SESSION['customer_id'],
    $_SESSION['customer_name'],
    $_SESSION['customer_email']
);

// Destroy entire session if no admin session is active
if (empty($_SESSION['admin_logged_in'])) {
    session_destroy();
}

header('Location: login.php');
exit;
