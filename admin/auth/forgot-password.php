<?php
// admin/auth/forgot-password.php
require_once __DIR__ . '/../config/config.php';

$page_title = 'CloudCush Admin - Forgot Password';
$body_class = 'auth-premium-bg';
include __DIR__ . '/../includes/header.php';
?>

<div class="auth-premium-card shadow-lg">
    <div class="card-body p-4">
        <div class="text-center mb-4">
            <h2 class="fw-bold text-dark mb-1 ls-tight-3">Cloud<span class="text-primary">Cush</span></h2>
            <p class="text-muted small">Recover your account password</p>
        </div>
        
        <form action="#" method="POST" onsubmit="event.preventDefault(); Swal.fire('Check your inbox', 'If the email exists, we have sent a reset password link.', 'success').then(() => { window.location.href = 'login.php'; });">
            <div class="mb-3">
                <label class="form-label fw-medium text-secondary small">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 rounded-start-3"><i data-lucide="mail" class="text-muted icon-md"></i></span>
                    <input type="email" name="email" class="form-control border-start-0 bg-light rounded-end-3 fs-0-9" placeholder="admin@cloudcush.com" required autocomplete="email">
                </div>
                <div class="form-text text-muted small fs-0-75">We'll email you instructions to reset your password.</div>
            </div>
            
            <div class="d-grid mb-3">
                <button type="submit" class="btn btn-premium-primary py-2 fw-semibold">Send Reset Link</button>
            </div>
        </form>
        
        <div class="text-center mt-3">
            <a href="<?= BASE_URL ?>auth/login.php" class="small text-decoration-none fw-semibold text-primary"><i data-lucide="arrow-left" class="me-2 icon-md"></i>Back to Sign In</a>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

