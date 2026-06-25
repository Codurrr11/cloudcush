<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/customers-init.php';

// Redirect if already logged in
if (!empty($_SESSION['customer_id'])) {
    header('Location: account.php');
    exit;
}

$errors  = [];
$oldEmail = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password']      ?? '';

    $oldEmail = $email;

    // Validation
    if ($email === '') {
        $errors[] = 'Email address is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    if ($password === '') {
        $errors[] = 'Password is required.';
    }

    // Verify against DB
    if (empty($errors)) {
        $pdo  = getFrontendDB();
        $stmt = $pdo->prepare('SELECT id, full_name, email, password, status FROM customers WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $customer = $stmt->fetch();

        if (!$customer || !password_verify($password, $customer['password'])) {
            $errors[] = 'Invalid email or password.';
        } elseif (($customer['status'] ?? 'active') === 'inactive') {
            $errors[] = 'Your account has been deactivated. Please contact support.';
        } else {
            // Update last_login timestamp
            $upd = $pdo->prepare('UPDATE customers SET last_login = NOW() WHERE id = ?');
            $upd->execute([$customer['id']]);

            // Create session
            $_SESSION['customer_id']    = $customer['id'];
            $_SESSION['customer_name']  = $customer['full_name'];
            $_SESSION['customer_email'] = $customer['email'];

            header('Location: account.php');
            exit;
        }
    }
}

$page_title = 'Sign In — CloudCush';
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $page_title; ?></title>
  <meta name="description" content="Sign in to your CloudCush account. Premium babycare crafted for tiny moments.">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&family=Lora:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500;1,600;1,700&family=Merriweather:ital,wght@0,300;0,400;0,700;0,900;1,300;1,400;1,700&family=Roboto+Slab:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/main.css">
  <link rel="stylesheet" href="assets/css/responsive.css">
  <link rel="stylesheet" href="assets/css/auth.css">
</head>

<body class="auth-standalone-body">

  <!-- Background decorative rings -->
  <div class="auth-background-overlay" aria-hidden="true"></div>
  <div class="auth-background-rings" aria-hidden="true"></div>

  <!-- Background ambient canvas -->
  <canvas id="authStandCanvas"></canvas>

  <div class="auth-viewport-wrap">

    <div class="auth-glass-card">

      <!-- Card Icon Box -->
      <div class="auth-card-icon-box" aria-hidden="true">
        <i class="ri-login-box-line"></i>
      </div>

      <h1 class="auth-card-title">Sign in with email</h1>
      <p class="auth-card-subtitle">Access your organic diapers, stage plans, and subscriptions. For free.</p>

      <?php if (!empty($errors)): ?>
        <div class="auth-alert auth-alert-error">
          <?php foreach ($errors as $e): ?>
            <p><?php echo htmlspecialchars($e); ?></p>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <form class="auth-fields-stack" action="login.php" method="POST" id="loginForm">

        <div class="auth-field-group">
          <div class="auth-field-wrap">
            <i class="ri-mail-line auth-field-icon"></i>
            <input
              type="email"
              id="login-email"
              name="email"
              class="auth-field-input"
              placeholder="Email"
              autocomplete="email"
              value="<?php echo htmlspecialchars($oldEmail); ?>"
              required>
          </div>
        </div>

        <div class="auth-field-group">
          <div class="auth-field-wrap">
            <i class="ri-lock-line auth-field-icon"></i>
            <input
              type="password"
              id="login-password"
              name="password"
              class="auth-field-input"
              placeholder="Password"
              autocomplete="current-password"
              required>
            <button type="button" class="auth-field-toggle" aria-label="Toggle password visibility">
              <i class="ri-eye-off-line"></i>
            </button>
          </div>
        </div>

        <a href="#" class="auth-forgot-link-right">Forgot password?</a>

        <button type="submit" class="auth-submit-btn-dark">Get Started</button>

      </form>

      <!-- Social divider -->
      <div class="auth-social-divider">
        <span class="auth-social-divider-line"></span>
        <span>Or sign in with</span>
        <span class="auth-social-divider-line"></span>
      </div>

      <!-- Social buttons -->
      <div class="auth-social-buttons-row">
        <a href="#" class="auth-social-btn-white" aria-label="Sign in with Google">
          <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4" />
            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853" />
            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#FBBC05" />
            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335" />
          </svg>
        </a>
        <a href="#" class="auth-social-btn-white" aria-label="Sign in with Facebook">
          <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" fill="#1877F2">
            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
          </svg>
        </a>
        <a href="#" class="auth-social-btn-white" aria-label="Sign in with Apple">
          <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" fill="#000000">
            <path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.8-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z" />
          </svg>
        </a>
      </div>

      <p class="auth-switch-link-bottom">
        Don't have an account? <a href="register.php">Create a free account</a>
      </p>

    </div>

  </div>

  <!-- JS libraries -->
  <script src="assets/js/auth.js"></script>

</body>

</html>
