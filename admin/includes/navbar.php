<?php
// admin/includes/navbar.php
require_once __DIR__ . '/../config/config.php';

// Determine breadcrumbs based on active page
$breadcrumbs = ['CloudCush'];
if (isset($active_page)) {
    if ($active_page === 'dashboard') {
        $breadcrumbs[] = 'Overview';
    } else {
        $breadcrumbs[] = 'Management';
        $breadcrumbs[] = ucfirst($active_page);
    }
} else {
    $breadcrumbs[] = 'Dashboard';
}
?>
<!-- Premium Transparent Top Navbar -->
<nav class="navbar navbar-expand bg-transparent border-0 py-3 mb-3">
    <div class="container-fluid px-0">
        <!-- Left Section: Toggle Button & Breadcrumbs -->
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-white shadow-sm border-0 rounded-circle d-flex align-items-center justify-content-center navbar-btn-toggle" id="sidebarToggle" type="button" aria-label="Toggle Sidebar">
                <i data-lucide="panel-left-close" class="text-secondary icon-nav"></i>
            </button>
            
            <div class="breadcrumb-custom mb-0 d-none d-md-flex align-items-center">
                <?php foreach ($breadcrumbs as $index => $crumb): ?>
                    <?php if ($index > 0): ?>
                        <span class="mx-2 text-muted opacity-50 fs-0-75">/</span>
                    <?php endif; ?>
                    <a href="#" class="<?= $index === count($breadcrumbs) - 1 ? 'text-dark fw-bold' : 'text-muted fw-semibold' ?> fs-0-8">
                        <?= htmlspecialchars($crumb) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Right Section: Capsule Badges & Notifications -->
        <div class="d-flex align-items-center gap-2 ms-auto">
            <!-- Search bar (Premium Capsule Input) -->
            <form class="d-none d-lg-flex me-2" role="search">
                <div class="input-group input-group-sm w-220-px">
                    <span class="input-group-text bg-transparent border-0 rounded-start-pill shadow-sm ps-3 navbar-search-prefix">
                        <i data-lucide="search" class="text-muted icon-nav"></i>
                    </span>
                    <input class="form-control bg-transparent rounded-end-pill shadow-sm pe-3 py-2 navbar-search-input" type="search" placeholder="Search dashboard..." aria-label="Search">
                </div>
            </form>

            <div class="badge-pill-custom shadow-sm d-none d-sm-inline-flex h-34-px">
                <span class="text-secondary fw-semibold fs-0-78">Notes</span>
                <span class="badge-pill-number">7</span>
            </div>
            
            <div class="badge-pill-custom shadow-sm d-none d-sm-inline-flex h-34-px">
                <span class="text-secondary fw-semibold fs-0-78">Tasks</span>
                <span class="badge-pill-number">5</span>
            </div>
            
            <!-- Notifications Dropdown -->
            <div class="dropdown">
                <a class="btn btn-white shadow-sm border-0 rounded-circle d-flex align-items-center justify-content-center position-relative navbar-btn-alert" href="#" id="alertsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i data-lucide="bell" class="text-secondary icon-nav"></i>
                    <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle navbar-alert-dot"></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-3 p-2 rounded-4 dropdown-menu-premium" aria-labelledby="alertsDropdown">
                    <li class="dropdown-header text-start fw-bold text-dark py-2 fs-0-85">Notifications</li>
                    <li><hr class="dropdown-divider my-1"></li>
                    <li>
                        <a class="dropdown-item py-2 rounded-3" href="#">
                            <div class="d-flex align-items-center gap-2">
                                <div class="bg-primary-light p-2 rounded-circle text-primary d-flex align-items-center justify-content-center dropdown-icon-wrapper">
                                    <i data-lucide="package" class="icon-sm"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold text-dark fs-0-8">New order #ORD-9021</div>
                                    <div class="text-muted fs-0-7">Just now</div>
                                </div>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item py-2 rounded-3" href="#">
                            <div class="d-flex align-items-center gap-2">
                                <div class="bg-success-light p-2 rounded-circle text-success d-flex align-items-center justify-content-center dropdown-icon-wrapper">
                                    <i data-lucide="user-plus" class="icon-sm"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold text-dark fs-0-8">New user registered</div>
                                    <div class="text-muted fs-0-7">12 mins ago</div>
                                </div>
                            </div>
                        </a>
                    </li>
                    <li><hr class="dropdown-divider my-1"></li>
                    <li><a class="dropdown-item text-center text-primary py-2 fw-bold rounded-3 fs-0-8" href="#">View all alerts</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>

