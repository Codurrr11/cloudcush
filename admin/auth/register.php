<?php
// admin/auth/register.php
require_once __DIR__ . '/../config/config.php';

// If already logged in, redirect to dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: ' . BASE_URL . 'dashboard/index.php');
    exit;
}

$page_title = 'CloudCush Admin - Register';
$body_class = 'auth-premium-bg';
include __DIR__ . '/../includes/header.php';
?>

<div class="auth-premium-card shadow-lg">
    <div class="card-body p-4">
        <div class="text-center mb-4">
            <h2 class="fw-bold text-dark mb-1 ls-tight-3">Cloud<span class="text-primary">Cush</span></h2>
            <p class="text-muted small">Create a new administration account</p>
        </div>
        
        <form action="<?= BASE_URL ?>handlers/auth/register-handler.php" method="POST" novalidate>
            <div class="mb-3">
                <label class="form-label fw-medium text-secondary small">Full Name</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 rounded-start-3"><i data-lucide="user" class="text-muted icon-md"></i></span>
                    <input type="text" name="name" class="form-control border-start-0 bg-light rounded-end-3 fs-0-9" placeholder="John Doe" required autocomplete="name">
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label fw-medium text-secondary small">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 rounded-start-3"><i data-lucide="mail" class="text-muted icon-md"></i></span>
                    <input type="email" name="email" class="form-control border-start-0 bg-light rounded-end-3 fs-0-9" placeholder="admin@cloudcush.com" required autocomplete="email">
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label fw-medium text-secondary small">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 rounded-start-3"><i data-lucide="lock" class="text-muted icon-md"></i></span>
                    <input type="password" name="password" class="form-control border-start-0 bg-light rounded-end-3 fs-0-9" placeholder="••••••••" required autocomplete="new-password">
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label fw-medium text-secondary small">Confirm Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 rounded-start-3"><i data-lucide="shield" class="text-muted icon-md"></i></span>
                    <input type="password" name="confirm_password" class="form-control border-start-0 bg-light rounded-end-3 fs-0-9" placeholder="••••••••" required autocomplete="new-password">
                </div>
            </div>
            
            <div class="mb-3 form-check d-flex align-items-center gap-2">
                <input type="checkbox" name="agree_terms" class="form-check-input mt-0" id="agreeTerms" required checked>
                <label class="form-check-label text-muted small fs-0-8" for="agreeTerms">I agree to the terms and privacy policy</label>
            </div>
            
            <div class="d-grid mb-3">
                <button type="submit" class="btn btn-premium-primary py-2 fw-semibold">Register Account</button>
            </div>
        </form>
        
        <div class="text-center mt-3">
            <span class="text-muted small fs-0-8">Already have an account?</span>
            <a href="<?= BASE_URL ?>auth/login.php" class="small text-decoration-none fw-semibold text-primary">Sign In</a>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
