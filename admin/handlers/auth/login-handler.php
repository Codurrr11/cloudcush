<?php
// admin/handlers/auth/login-handler.php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit;
}

$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    $_SESSION['flash_message'] = 'Please fill in all fields.';
    $_SESSION['flash_type'] = 'error';
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit;
}

try {
    $db = getDBConnection();
    $stmt = $db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Log in successfully, establish session variables
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];

        $_SESSION['flash_message'] = 'Welcome back, ' . $user['name'] . '!';
        $_SESSION['flash_type'] = 'success';

        // Redirect to saved redirect_url or default dashboard
        $redirect = isset($_SESSION['redirect_url']) ? $_SESSION['redirect_url'] : BASE_URL . 'dashboard/index.php';
        unset($_SESSION['redirect_url']);
        header('Location: ' . $redirect);
        exit;
    } else {
        $_SESSION['flash_message'] = 'Invalid email or password.';
        $_SESSION['flash_type'] = 'error';
        header('Location: ' . BASE_URL . 'auth/login.php');
        exit;
    }
} catch (\PDOException $e) {
    $_SESSION['flash_message'] = 'Database error encountered during authentication.';
    $_SESSION['flash_type'] = 'error';
    error_log("Login error: " . $e->getMessage());
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit;
}
