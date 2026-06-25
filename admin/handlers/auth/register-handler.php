<?php
// admin/handlers/auth/register-handler.php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . 'auth/register.php');
    exit;
}

$name = trim($_POST['name'] ?? '');
$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
    $_SESSION['flash_message'] = 'Please fill in all fields.';
    $_SESSION['flash_type'] = 'error';
    header('Location: ' . BASE_URL . 'auth/register.php');
    exit;
}

if ($password !== $confirm_password) {
    $_SESSION['flash_message'] = 'Passwords do not match.';
    $_SESSION['flash_type'] = 'error';
    header('Location: ' . BASE_URL . 'auth/register.php');
    exit;
}

try {
    $db = getDBConnection();
    
    // Check if email already exists
    $stmt = $db->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
    $stmt->execute(['email' => $email]);
    if ($stmt->fetch()) {
        $_SESSION['flash_message'] = 'Email address is already registered.';
        $_SESSION['flash_type'] = 'error';
        header('Location: ' . BASE_URL . 'auth/register.php');
        exit;
    }
    
    // Hash password and insert user
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $db->prepare("INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, 'editor')");
    $stmt->execute([
        'name' => $name,
        'email' => $email,
        'password' => $hashed_password
    ]);
    
    $_SESSION['flash_message'] = 'Registration successful! Please sign in.';
    $_SESSION['flash_type'] = 'success';
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit;
} catch (\PDOException $e) {
    $_SESSION['flash_message'] = 'Database error encountered during registration.';
    $_SESSION['flash_type'] = 'error';
    error_log("Registration error: " . $e->getMessage());
    header('Location: ' . BASE_URL . 'auth/register.php');
    exit;
}
