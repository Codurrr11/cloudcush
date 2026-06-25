<?php
// admin/includes/sidebar.php
require_once __DIR__ . '/../config/config.php';
$active_menu = isset($active_page) ? $active_page : 'dashboard';
?>
<!-- Sidebar Navigation -->
<div id="sidebar-wrapper">
    <div class="sidebar-heading px-3 py-4 text-center">
        <a href="<?= BASE_URL ?>dashboard/index.php" class="text-decoration-none">
            <span class="fs-4 fw-bold text-dark sidebar-logo">Cloud<span class="text-primary">Cush</span></span>
            <div class="text-muted text-uppercase fw-bold fs-0-65 letter-spacing-pos-08 mt-2-px">Admin Panel</div>
        </a>
    </div>
    <div class="list-group list-group-flush sidebar-nav">
        <!-- Dashboard -->
        <a href="<?= BASE_URL ?>dashboard/index.php" class="list-group-item list-group-item-action <?= $active_menu === 'dashboard' ? 'active' : ''; ?>">
            <i data-lucide="layout-dashboard"></i>Dashboard
        </a>
        
        <!-- Products -->
        <a href="<?= BASE_URL ?>products/" class="list-group-item list-group-item-action <?= $active_menu === 'products' ? 'active' : ''; ?>">
            <i data-lucide="package"></i>Products
        </a>
        
        <!-- Orders -->
        <a href="<?= BASE_URL ?>orders/" class="list-group-item list-group-item-action <?= $active_menu === 'orders' ? 'active' : ''; ?>">
            <i data-lucide="credit-card"></i>Orders
        </a>

        <!-- Customers -->
        <a href="<?= BASE_URL ?>customers/" class="list-group-item list-group-item-action <?= $active_menu === 'customers' ? 'active' : ''; ?>">
            <i data-lucide="contact"></i>Customers
        </a>

        <!-- Blogs -->
        <a href="<?= BASE_URL ?>blogs/" class="list-group-item list-group-item-action <?= $active_menu === 'blogs' ? 'active' : ''; ?>">
            <i data-lucide="book-open"></i>Blogs
        </a>

        <!-- Reviews -->
        <a href="<?= BASE_URL ?>reviews/" class="list-group-item list-group-item-action <?= $active_menu === 'reviews' ? 'active' : ''; ?>">
            <i data-lucide="star"></i>Reviews
        </a>

        <!-- Users -->
        <a href="<?= BASE_URL ?>users/" class="list-group-item list-group-item-action <?= $active_menu === 'users' ? 'active' : ''; ?>">
            <i data-lucide="users"></i>Users
        </a>

        <!-- Front-end Pages Dropdown -->
        <?php $is_frontend_active = in_array($active_menu, ['faqs', 'about', 'guide', 'home', 'header', 'footer']); ?>
        <a href="#frontendPagesCollapse" 
           class="list-group-item list-group-item-action d-flex align-items-center justify-content-between <?= $is_frontend_active ? 'parent-active' : ''; ?>" 
           data-bs-toggle="collapse" 
           role="button" 
           aria-expanded="<?= $is_frontend_active ? 'true' : 'false'; ?>" 
           aria-controls="frontendPagesCollapse">
            <span class="d-flex align-items-center">
                <i data-lucide="layout"></i>Front-end Pages
            </span>
            <i data-lucide="chevron-down" class="menu-arrow ms-auto" style="width: 14px; height: 14px; transition: transform 0.2s;"></i>
        </a>
        <div class="collapse <?= $is_frontend_active ? 'show' : ''; ?>" id="frontendPagesCollapse">
            <div class="sidebar-submenu">
                <a href="<?= BASE_URL ?>home/" class="sidebar-sub-item <?= $active_menu === 'home' ? 'active' : ''; ?>">
                    <i data-lucide="home"></i>Homepage Settings
                </a>
                <a href="<?= BASE_URL ?>about/" class="sidebar-sub-item <?= $active_menu === 'about' ? 'active' : ''; ?>">
                    <i data-lucide="info"></i>About Page
                </a>
                <a href="<?= BASE_URL ?>guide/" class="sidebar-sub-item <?= $active_menu === 'guide' ? 'active' : ''; ?>">
                    <i data-lucide="layers"></i>Diaper Guide Page
                </a>
                <a href="<?= BASE_URL ?>faqs/" class="sidebar-sub-item <?= $active_menu === 'faqs' ? 'active' : ''; ?>">
                    <i data-lucide="help-circle"></i>FAQs
                </a>
                <a href="<?= BASE_URL ?>header/" class="sidebar-sub-item <?= $active_menu === 'header' ? 'active' : ''; ?>">
                    <i data-lucide="navigation"></i>Header Settings
                </a>
                <a href="<?= BASE_URL ?>footer/" class="sidebar-sub-item <?= $active_menu === 'footer' ? 'active' : ''; ?>">
                    <i data-lucide="panel-bottom"></i>Footer Settings
                </a>
            </div>
        </div>
        
        <!-- Settings -->
        <a href="<?= BASE_URL ?>settings/" class="list-group-item list-group-item-action <?= $active_menu === 'settings' ? 'active' : ''; ?>">
            <i data-lucide="settings"></i>Settings
        </a>

        <!-- Logout -->
        <a href="<?= BASE_URL ?>auth/logout.php" class="list-group-item list-group-item-action text-danger mt-2 border-top-dashed">
            <i data-lucide="log-out" class="text-danger"></i>Logout
        </a>
    </div>
</div>

