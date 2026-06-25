<?php
require_once __DIR__ . '/../includes/auth-check.php';

$page_title = 'CloudCush Admin - Users';
$active_page = 'users';

include __DIR__ . '/../includes/header.php';
?>

<div id="wrapper">
    <?php include __DIR__ . '/../includes/sidebar.php'; ?>
    
    <div id="page-content-wrapper">
        <?php include __DIR__ . '/../includes/navbar.php'; ?>
        
        <div class="container-fluid px-0 py-2">
            <h1 class="h3 fw-bold mb-4">User Management</h1>
            <div class="card-premium py-5 text-center">
                <div class="stat-card-icon bg-primary-light mx-auto mb-3 d-flex align-items-center justify-content-center rounded-circle">
                    <i data-lucide="users" class="icon-xl"></i>
                </div>
                <h4 class="fw-bold text-dark">Staff & Permissions</h4>
                <p class="text-secondary mx-auto max-width-500">This section is currently under active development. You will soon be able to manage admin accounts, control role-based access, and configure editor options.</p>
                <button class="btn btn-premium-primary btn-sm mt-3"><i data-lucide="user-plus" class="me-2 icon-sm"></i>Add User</button>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
