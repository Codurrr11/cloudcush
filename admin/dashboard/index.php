<?php
// admin/dashboard/index.php
require_once __DIR__ . '/../includes/auth-check.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/dashboard-helper.php';
require_once __DIR__ . '/../config/orders-helper.php';

$page_title  = 'CloudCush Admin - Dashboard';
$active_page = 'dashboard';

// ── Fetch all live data ──────────────────────────────────────
$overview      = getDashboardOverview();
$productStats  = getDashboardProductStats();
$orderStats    = getDashboardOrderStats();
$customerStats = getDashboardCustomerStats();
$categoryStats = getDashboardCategoryStats();
$chartData     = getDashboardChartData();
$recentOrders  = getDashboardRecentOrders(7);
$topProducts   = getDashboardTopProducts(5);

// ── Helpers ──────────────────────────────────────────────────
function initials(string $name): string {
    $parts = explode(' ', trim($name));
    $ini   = strtoupper(substr($parts[0] ?? '', 0, 1));
    if (count($parts) > 1) $ini .= strtoupper(substr($parts[count($parts) - 1], 0, 1));
    return $ini ?: '?';
}

function statusBadgeClass(string $status): string {
    return match(strtolower($status)) {
        'pending'    => 'warning',
        'processing' => 'info',
        'confirmed'  => 'info',
        'shipped'    => 'primary',
        'delivered','completed' => 'success',
        'cancelled'  => 'danger',
        default      => 'secondary',
    };
}

function avatarBg(string $name): string {
    $colors = ['bg-primary', 'bg-warning', 'bg-info', 'bg-success', 'bg-danger', 'bg-secondary'];
    return $colors[abs(crc32($name)) % count($colors)];
}

function textContrast(string $bgClass): string {
    return in_array($bgClass, ['bg-warning']) ? 'text-dark' : 'text-white';
}

// ── Prepare JSON for charts ──────────────────────────────────
// Orders per month
$ordersMonthLabels = array_column($chartData['ordersPerMonth'], 'month_label');
$ordersMonthData   = array_column($chartData['ordersPerMonth'], 'count');

// Customers per month
$custMonthLabels   = array_column($chartData['customersPerMonth'], 'month_label');
$custMonthData     = array_column($chartData['customersPerMonth'], 'count');

// Products per month
$prodMonthLabels   = array_column($chartData['productsPerMonth'], 'month_label');
$prodMonthData     = array_column($chartData['productsPerMonth'], 'count');

// Order status distribution
$statusLabels = array_column($chartData['orderStatusDist'], 'status');
$statusCounts = array_column($chartData['orderStatusDist'], 'count');
$statusColors = array_map(fn($s) => match(strtolower($s)) {
    'pending'    => '#f59e0b',
    'processing' => '#8b5cf6',
    'confirmed'  => '#0ea5e9',
    'shipped'    => '#3b82f6',
    'delivered','completed' => '#10b981',
    'cancelled'  => '#ef4444',
    default      => '#94a3b8',
}, $statusLabels);

// Top selling products chart
$topProdLabels = array_map(fn($p) => mb_substr($p['title'], 0, 22) . (mb_strlen($p['title']) > 22 ? '…' : ''), $chartData['topProducts']);
$topProdCounts = array_column($chartData['topProducts'], 'order_count');

// Combined activity chart (orders + customers) for the main wave chart
// Merge months from both
$allMonths = array_unique(array_merge($ordersMonthLabels, $custMonthLabels));
sort($allMonths);
$activityLabels   = $allMonths;
$activityOrders   = [];
$activityCustomers = [];
$oMap = array_combine($ordersMonthLabels, $ordersMonthData);
$cMap = array_combine($custMonthLabels,   $custMonthData);
foreach ($allMonths as $m) {
    $activityOrders[]    = $oMap[$m] ?? 0;
    $activityCustomers[] = $cMap[$m] ?? 0;
}

include __DIR__ . '/../includes/header.php';
?>

<div id="wrapper">
    <!-- Sidebar -->
    <?php include __DIR__ . '/../includes/sidebar.php'; ?>

    <!-- Page Content -->
    <div id="page-content-wrapper">
        <!-- Top Navbar -->
        <?php include __DIR__ . '/../includes/navbar.php'; ?>

        <!-- Dashboard Body Content -->
        <div class="container-fluid px-0 py-2 overflow-x-hidden">

            <!-- Welcome Header -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
                <div>
                    <h1 class="h4 fw-bold mb-1">Dashboard Overview</h1>
                    <p class="text-secondary mb-0 fs-0-82">Live e-commerce analytics — products, orders, customers and categories</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-premium-secondary btn-sm d-flex align-items-center gap-2" type="button">
                        <i data-lucide="calendar" class="icon-sm"></i>
                        <span><?= date('d M Y') ?></span>
                    </button>
                    <a href="<?= BASE_URL ?>orders/" class="btn btn-premium-primary btn-sm d-flex align-items-center gap-2">
                        <i data-lucide="package" class="icon-sm"></i><span>View Orders</span>
                    </a>
                </div>
            </div>

            <!-- Capsule Filters -->
            <div class="capsule-tabs">
                <a href="#overview-section"   class="capsule-tab-item active">Overview</a>
                <a href="#orders-section"     class="capsule-tab-item">Orders</a>
                <a href="#products-section"   class="capsule-tab-item">Products</a>
                <a href="#customers-section"  class="capsule-tab-item">Customers</a>
                <a href="#categories-section" class="capsule-tab-item">Categories</a>
            </div>

            <!-- ═══════════════════════════════════════════════════
                 OVERVIEW CARDS
            ═══════════════════════════════════════════════════ -->
            <div id="overview-section" class="row g-3 mb-4">

                <!-- Total Products -->
                <div class="col-6 col-sm-6 col-xl-3">
                    <div class="card-premium h-100 d-flex flex-column justify-content-between">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <span class="metric-card-title">Total Products</span>
                                <h3 class="metric-card-value"><?= number_format($overview['totalProducts']) ?></h3>
                            </div>
                            <div class="bg-primary-light p-2 rounded-3 text-primary d-flex align-items-center justify-content-center metric-card-icon-container">
                                <i data-lucide="box" class="icon-md"></i>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2 mt-2">
                            <span class="badge bg-success-subtle text-success small fw-bold tech-data fs-0-7"><?= $productStats['active'] ?> active</span>
                            <span class="text-muted small fs-0-72">in catalogue</span>
                        </div>
                    </div>
                </div>

                <!-- Total Orders -->
                <div class="col-6 col-sm-6 col-xl-3">
                    <div class="card-premium card-gradient-metric h-100 d-flex flex-column justify-content-between">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <span class="metric-card-title">Total Orders</span>
                                <h3 class="metric-card-value"><?= number_format($overview['totalOrders']) ?></h3>
                            </div>
                            <div class="p-2 rounded-3 d-flex align-items-center justify-content-center metric-card-icon-container bg-white-15">
                                <i data-lucide="package" class="text-white icon-md"></i>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2 mt-2">
                            <span class="badge bg-white bg-opacity-25 text-white small fw-bold tech-data fs-0-7"><?= $overview['pendingOrders'] ?> pending</span>
                            <span class="text-white-50 small fs-0-72">awaiting action</span>
                        </div>
                    </div>
                </div>

                <!-- Total Customers -->
                <div class="col-6 col-sm-6 col-xl-3">
                    <div class="card-premium h-100 d-flex flex-column justify-content-between">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <span class="metric-card-title">Total Customers</span>
                                <h3 class="metric-card-value"><?= number_format($overview['totalCustomers']) ?></h3>
                            </div>
                            <div class="bg-primary-light p-2 rounded-3 text-primary d-flex align-items-center justify-content-center metric-card-icon-container">
                                <i data-lucide="users" class="icon-md"></i>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2 mt-2">
                            <span class="badge bg-success-subtle text-success small fw-bold tech-data fs-0-7">registered</span>
                            <span class="text-muted small fs-0-72">all-time accounts</span>
                        </div>
                    </div>
                </div>

                <!-- Total Categories -->
                <div class="col-6 col-sm-6 col-xl-3">
                    <div class="card-premium h-100 d-flex flex-column justify-content-between">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <span class="metric-card-title">Product Categories</span>
                                <h3 class="metric-card-value"><?= number_format($overview['totalCategories']) ?></h3>
                            </div>
                            <div class="bg-primary-light p-2 rounded-3 text-primary d-flex align-items-center justify-content-center metric-card-icon-container">
                                <i data-lucide="tag" class="icon-md"></i>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2 mt-2">
                            <span class="badge bg-primary-subtle text-primary small fw-bold tech-data fs-0-7"><?= $overview['totalProducts'] ?> products</span>
                            <span class="text-muted small fs-0-72">across all categories</span>
                        </div>
                    </div>
                </div>

                <!-- Pending Orders -->
                <div class="col-6 col-sm-6 col-xl-3">
                    <div class="card-premium h-100 d-flex flex-column justify-content-between">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <span class="metric-card-title">Pending Orders</span>
                                <h3 class="metric-card-value text-warning"><?= number_format($overview['pendingOrders']) ?></h3>
                            </div>
                            <div class="bg-warning-subtle p-2 rounded-3 text-warning d-flex align-items-center justify-content-center metric-card-icon-container">
                                <i data-lucide="clock" class="icon-md"></i>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2 mt-2">
                            <a href="<?= BASE_URL ?>orders/?status=pending" class="badge bg-warning-subtle text-warning small fw-bold tech-data fs-0-7 text-decoration-none">View all</a>
                            <span class="text-muted small fs-0-72">need processing</span>
                        </div>
                    </div>
                </div>

                <!-- Processing Orders -->
                <div class="col-6 col-sm-6 col-xl-3">
                    <div class="card-premium h-100 d-flex flex-column justify-content-between">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <span class="metric-card-title">Processing</span>
                                <h3 class="metric-card-value text-info"><?= number_format($overview['processingOrders']) ?></h3>
                            </div>
                            <div class="bg-info-subtle p-2 rounded-3 text-info d-flex align-items-center justify-content-center metric-card-icon-container">
                                <i data-lucide="loader" class="icon-md"></i>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2 mt-2">
                            <a href="<?= BASE_URL ?>orders/?status=processing" class="badge bg-info-subtle text-info small fw-bold tech-data fs-0-7 text-decoration-none">View all</a>
                            <span class="text-muted small fs-0-72">in progress</span>
                        </div>
                    </div>
                </div>

                <!-- Completed Orders -->
                <div class="col-6 col-sm-6 col-xl-3">
                    <div class="card-premium h-100 d-flex flex-column justify-content-between">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <span class="metric-card-title">Completed</span>
                                <h3 class="metric-card-value text-success"><?= number_format($overview['completedOrders']) ?></h3>
                            </div>
                            <div class="bg-success-subtle p-2 rounded-3 text-success d-flex align-items-center justify-content-center metric-card-icon-container">
                                <i data-lucide="check-circle" class="icon-md"></i>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2 mt-2">
                            <a href="<?= BASE_URL ?>orders/?status=delivered" class="badge bg-success-subtle text-success small fw-bold tech-data fs-0-7 text-decoration-none">View all</a>
                            <span class="text-muted small fs-0-72">delivered</span>
                        </div>
                    </div>
                </div>

                <!-- Cancelled Orders -->
                <div class="col-6 col-sm-6 col-xl-3">
                    <div class="card-premium h-100 d-flex flex-column justify-content-between">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <span class="metric-card-title">Cancelled</span>
                                <h3 class="metric-card-value text-danger"><?= number_format($overview['cancelledOrders']) ?></h3>
                            </div>
                            <div class="bg-danger-subtle p-2 rounded-3 text-danger d-flex align-items-center justify-content-center metric-card-icon-container">
                                <i data-lucide="x-circle" class="icon-md"></i>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2 mt-2">
                            <a href="<?= BASE_URL ?>orders/?status=cancelled" class="badge bg-danger-subtle text-danger small fw-bold tech-data fs-0-7 text-decoration-none">View all</a>
                            <span class="text-muted small fs-0-72">not fulfilled</span>
                        </div>
                    </div>
                </div>

            </div><!-- /overview cards -->

            <!-- ═══════════════════════════════════════════════════
                 MAIN CHARTS ROW
            ═══════════════════════════════════════════════════ -->
            <div class="row g-4 mb-4">

                <!-- Store Activity Chart -->
                <div class="col-12 col-lg-8 overflow-hidden">
                    <div class="card-premium h-100 d-flex flex-column overflow-hidden">
                        <div class="card-premium-header">
                            <div>
                                <h5 class="card-premium-title">Store Activity & Performance</h5>
                                <span class="small text-muted fs-0-75">Orders & new customers per month</span>
                            </div>
                            <div class="d-flex gap-2 align-items-center">
                                <span class="d-flex align-items-center gap-1 fs-0-75 text-muted"><span class="d-inline-block rounded-circle bg-primary" style="width:8px;height:8px;"></span> Orders</span>
                                <span class="d-flex align-items-center gap-1 fs-0-75 text-muted"><span class="d-inline-block rounded-circle bg-success" style="width:8px;height:8px;"></span> Customers</span>
                            </div>
                        </div>
                        <div class="chart-container-premium">
                            <canvas id="activityChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Order Status Distribution -->
                <div class="col-12 col-lg-4">
                    <div class="card-premium h-100 d-flex flex-column">
                        <div class="card-premium-header mb-3">
                            <h5 class="card-premium-title">Order Status Distribution</h5>
                            <span class="badge-status success">Live</span>
                        </div>
                        <div style="max-height:200px;" class="d-flex align-items-center justify-content-center">
                            <canvas id="statusChart"></canvas>
                        </div>
                        <!-- Legend -->
                        <div class="mt-3">
                            <?php foreach ($chartData['orderStatusDist'] as $sd): ?>
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="d-inline-block rounded-circle flex-shrink-0" style="width:8px;height:8px;background:<?= htmlspecialchars(match(strtolower($sd['status'])) {
                                        'pending'    => '#f59e0b',
                                        'processing' => '#8b5cf6',
                                        'confirmed'  => '#0ea5e9',
                                        'shipped'    => '#3b82f6',
                                        'delivered','completed' => '#10b981',
                                        'cancelled'  => '#ef4444',
                                        default      => '#94a3b8',
                                    }) ?>"></span>
                                    <span class="fs-0-78 text-capitalize"><?= htmlspecialchars($sd['status']) ?></span>
                                </div>
                                <span class="badge-status <?= statusBadgeClass($sd['status']) ?> tech-data"><?= $sd['count'] ?></span>
                            </div>
                            <?php endforeach; ?>
                            <?php if (empty($chartData['orderStatusDist'])): ?>
                            <p class="text-muted fs-0-78 text-center mt-3">No orders yet.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ═══════════════════════════════════════════════════
                 RECENT ORDERS TABLE
            ═══════════════════════════════════════════════════ -->
            <div id="orders-section" class="row g-4 mb-4">
                <div class="col-12">
                    <div class="card-premium overflow-hidden">
                        <div class="card-premium-header">
                            <div>
                                <h5 class="card-premium-title">Recent Orders</h5>
                                <span class="small text-muted fs-0-75">Latest <?= count($recentOrders) ?> of <?= $orderStats['total'] ?> total orders</span>
                            </div>
                            <a href="<?= BASE_URL ?>orders/" class="small text-decoration-none fw-bold text-primary fs-0-8">View all</a>
                        </div>
                        <div class="table-premium-container">
                            <table class="table table-premium align-middle">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Customer</th>
                                        <th>Email</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Items</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($recentOrders)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4 fs-0-85">No orders found.</td>
                                    </tr>
                                    <?php else: ?>
                                    <?php foreach ($recentOrders as $order): ?>
                                    <tr>
                                        <td>
                                            <a href="<?= BASE_URL ?>orders/?action=view&id=<?= $order['id'] ?>" class="fw-semibold tech-data text-decoration-none text-primary">
                                                #ORD-<?= str_pad($order['id'], 4, '0', STR_PAD_LEFT) ?>
                                            </a>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <?php $bg = avatarBg($order['customer_name']); $tc = textContrast($bg); ?>
                                                <span class="avatar avatar-xs rounded-circle <?= $bg ?> <?= $tc ?> d-flex align-items-center justify-content-center fw-bold avatar-24 flex-shrink-0">
                                                    <?= htmlspecialchars(initials($order['customer_name'])) ?>
                                                </span>
                                                <span class="fw-semibold text-dark"><?= htmlspecialchars($order['customer_name']) ?></span>
                                            </div>
                                        </td>
                                        <td class="text-muted fs-0-82"><?= htmlspecialchars($order['customer_email']) ?></td>
                                        <td class="tech-data fs-0-82"><?= date('d M Y', strtotime($order['created_at'])) ?></td>
                                        <td>
                                            <span class="badge-status <?= statusBadgeClass($order['status']) ?> text-capitalize">
                                                <?= htmlspecialchars($order['status']) ?>
                                            </span>
                                        </td>
                                        <td class="tech-data text-center"><?= (int)$order['item_count'] ?></td>
                                        <td class="fw-bold text-dark tech-data">₹<?= number_format((float)$order['total_amount'], 2) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ═══════════════════════════════════════════════════
                 PRODUCTS SECTION
            ═══════════════════════════════════════════════════ -->
            <div id="products-section" class="row g-4 mb-4">

                <!-- Products Stats Cards -->
                <div class="col-12">
                    <div class="card-premium-header mb-3 px-0">
                        <h5 class="card-premium-title">Products</h5>
                        <a href="<?= BASE_URL ?>products/" class="small text-decoration-none fw-bold text-primary fs-0-8">Manage Products</a>
                    </div>
                </div>

                <!-- Product stat mini-cards -->
                <div class="col-12 col-md-4">
                    <div class="card-premium h-100">
                        <div class="card-premium-header mb-3">
                            <h6 class="card-premium-title mb-0">Product Summary</h6>
                        </div>
                        <div class="d-flex flex-column gap-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted fs-0-85">Total Products</span>
                                <span class="fw-bold tech-data"><?= $productStats['total'] ?></span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted fs-0-85">Active</span>
                                <span class="badge-status success"><?= $productStats['active'] ?></span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted fs-0-85">Inactive / Draft / Archived</span>
                                <span class="badge-status secondary"><?= $productStats['inactive'] ?></span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted fs-0-85">Never Ordered</span>
                                <span class="badge-status warning"><?= count($productStats['neverOrdered']) ?></span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted fs-0-85">Low Stock</span>
                                <span class="badge-status danger"><?= count($productStats['lowStock']) ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Selling Products table -->
                <div class="col-12 col-md-8">
                    <div class="card-premium h-100 overflow-hidden">
                        <div class="card-premium-header">
                            <h6 class="card-premium-title mb-0">Top Selling Products</h6>
                        </div>
                        <div class="table-premium-container">
                            <table class="table table-premium align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Category</th>
                                        <th>Orders</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($topProducts)): ?>
                                    <tr><td colspan="4" class="text-center text-muted py-3 fs-0-85">No data yet.</td></tr>
                                    <?php else: ?>
                                    <?php foreach ($topProducts as $p): ?>
                                    <tr>
                                        <td class="fw-semibold text-dark"><?= htmlspecialchars($p['title']) ?></td>
                                        <td class="text-muted fs-0-82"><?= htmlspecialchars($p['category']) ?></td>
                                        <td class="tech-data fw-semibold"><?= (int)$p['order_count'] ?></td>
                                        <td><span class="badge-status <?= $p['status'] === 'active' ? 'success' : 'secondary' ?>"><?= htmlspecialchars(ucfirst($p['status'])) ?></span></td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Low Stock Products -->
                <?php if (!empty($productStats['lowStock'])): ?>
                <div class="col-12 col-md-6">
                    <div class="card-premium overflow-hidden">
                        <div class="card-premium-header">
                            <h6 class="card-premium-title mb-0">
                                <i data-lucide="alert-triangle" class="icon-sm text-warning me-1"></i>Low Stock Products
                            </h6>
                        </div>
                        <div class="table-premium-container">
                            <table class="table table-premium align-middle mb-0">
                                <thead><tr><th>Product</th><th>Stock</th><th>Threshold</th></tr></thead>
                                <tbody>
                                    <?php foreach ($productStats['lowStock'] as $lp): ?>
                                    <tr>
                                        <td class="fw-semibold"><?= htmlspecialchars($lp['title']) ?></td>
                                        <td><span class="badge-status danger tech-data"><?= (int)$lp['stock'] ?></span></td>
                                        <td class="text-muted tech-data"><?= (int)$lp['low_stock_threshold'] ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Recently Added Products -->
                <div class="col-12 col-md-<?= !empty($productStats['lowStock']) ? '6' : '12' ?>">
                    <div class="card-premium overflow-hidden">
                        <div class="card-premium-header">
                            <h6 class="card-premium-title mb-0">Recently Added Products</h6>
                            <a href="<?= BASE_URL ?>products/" class="small text-decoration-none fw-bold text-primary fs-0-8">All products</a>
                        </div>
                        <div class="table-premium-container">
                            <table class="table table-premium align-middle mb-0">
                                <thead><tr><th>Product</th><th>Category</th><th>Added</th><th>Status</th></tr></thead>
                                <tbody>
                                    <?php if (empty($productStats['recentProducts'])): ?>
                                    <tr><td colspan="4" class="text-center text-muted py-3 fs-0-85">No products yet.</td></tr>
                                    <?php else: ?>
                                    <?php foreach ($productStats['recentProducts'] as $rp): ?>
                                    <tr>
                                        <td class="fw-semibold"><?= htmlspecialchars($rp['title']) ?></td>
                                        <td class="text-muted fs-0-82"><?= htmlspecialchars($rp['category']) ?></td>
                                        <td class="tech-data fs-0-82"><?= date('d M Y', strtotime($rp['created_at'])) ?></td>
                                        <td><span class="badge-status <?= $rp['status'] === 'active' ? 'success' : 'secondary' ?>"><?= ucfirst($rp['status']) ?></span></td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Products Never Ordered -->
                <?php if (!empty($productStats['neverOrdered'])): ?>
                <div class="col-12">
                    <div class="card-premium overflow-hidden">
                        <div class="card-premium-header">
                            <h6 class="card-premium-title mb-0">
                                <i data-lucide="inbox" class="icon-sm text-muted me-1"></i>Products Never Ordered
                            </h6>
                        </div>
                        <div class="table-premium-container">
                            <table class="table table-premium align-middle mb-0">
                                <thead><tr><th>Product</th><th>Category</th><th>Status</th></tr></thead>
                                <tbody>
                                    <?php foreach ($productStats['neverOrdered'] as $np): ?>
                                    <tr>
                                        <td class="fw-semibold"><?= htmlspecialchars($np['title']) ?></td>
                                        <td class="text-muted fs-0-82"><?= htmlspecialchars($np['category']) ?></td>
                                        <td><span class="badge-status <?= $np['status'] === 'active' ? 'success' : 'secondary' ?>"><?= ucfirst($np['status']) ?></span></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

            </div><!-- /products section -->

            <!-- ═══════════════════════════════════════════════════
                 ORDERS DETAILED SECTION
            ═══════════════════════════════════════════════════ -->
            <div class="row g-4 mb-4">

                <!-- Orders chart per month -->
                <div class="col-12 col-lg-8">
                    <div class="card-premium h-100 overflow-hidden">
                        <div class="card-premium-header">
                            <div>
                                <h5 class="card-premium-title">Orders Per Month</h5>
                                <span class="small text-muted fs-0-75">Last 12 months</span>
                            </div>
                        </div>
                        <div class="chart-container-premium">
                            <canvas id="ordersMonthChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Orders status quick-view -->
                <div class="col-12 col-lg-4">
                    <div class="card-premium h-100 d-flex flex-column justify-content-between">
                        <div>
                            <div class="card-premium-header mb-3">
                                <h5 class="card-premium-title">Order Breakdown</h5>
                            </div>
                            <div class="d-flex flex-column gap-3">
                                <?php
                                $orderBreakdown = [
                                    ['label' => 'Total Orders',      'value' => $orderStats['total'],      'class' => 'primary'],
                                    ['label' => 'Pending',           'value' => $orderStats['pending'],    'class' => 'warning'],
                                    ['label' => 'Processing',        'value' => $orderStats['processing'], 'class' => 'info'],
                                    ['label' => 'Completed',         'value' => $orderStats['completed'],  'class' => 'success'],
                                    ['label' => 'Cancelled',         'value' => $orderStats['cancelled'],  'class' => 'danger'],
                                ];
                                foreach ($orderBreakdown as $ob): ?>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted fs-0-85"><?= $ob['label'] ?></span>
                                    <span class="badge-status <?= $ob['class'] ?> tech-data fw-bold"><?= $ob['value'] ?></span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <a href="<?= BASE_URL ?>orders/" class="btn btn-premium-primary w-100 btn-sm py-2 mt-4">
                            <i data-lucide="package" class="me-2 icon-sm"></i>Manage All Orders
                        </a>
                    </div>
                </div>
            </div>

            <!-- ═══════════════════════════════════════════════════
                 CUSTOMERS SECTION
            ═══════════════════════════════════════════════════ -->
            <div id="customers-section" class="row g-4 mb-4">
                <div class="col-12">
                    <div class="card-premium-header mb-2 px-0">
                        <h5 class="card-premium-title">Customers</h5>
                        <a href="<?= BASE_URL ?>customers/" class="small text-decoration-none fw-bold text-primary fs-0-8">Manage Customers</a>
                    </div>
                </div>

                <!-- Customer summary + chart row -->
                <div class="col-12 col-lg-4">
                    <div class="card-premium h-100 d-flex flex-column justify-content-between">
                        <div>
                            <div class="card-premium-header mb-3">
                                <h6 class="card-premium-title">Customer Summary</h6>
                            </div>
                            <div class="d-flex flex-column gap-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted fs-0-85">Total Customers</span>
                                    <span class="fw-bold tech-data"><?= $customerStats['total'] ?></span>
                                </div>
                                <?php
                                $newThisMonth = 0;
                                foreach ($customerStats['recentCustomers'] as $rc) {
                                    if (date('Y-m', strtotime($rc['created_at'])) === date('Y-m')) $newThisMonth++;
                                }
                                ?>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted fs-0-85">New This Month</span>
                                    <span class="badge-status success tech-data"><?= $newThisMonth ?></span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted fs-0-85">With Orders</span>
                                    <?php
                                    $withOrders = count(array_filter($customerStats['mostOrders'], fn($c) => $c['total_orders'] > 0));
                                    ?>
                                    <span class="badge-status primary tech-data"><?= $withOrders ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4">
                            <div style="max-height:140px;" class="d-flex align-items-center justify-content-center">
                                <canvas id="custMonthChart"></canvas>
                            </div>
                            <p class="text-muted fs-0-78 text-center mt-2">Customers registered per month</p>
                        </div>
                    </div>
                </div>

                <!-- Recent Customers table -->
                <div class="col-12 col-lg-8">
                    <div class="card-premium overflow-hidden">
                        <div class="card-premium-header">
                            <h6 class="card-premium-title">Latest Customer Registrations</h6>
                        </div>
                        <div class="table-premium-container">
                            <table class="table table-premium align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Customer</th>
                                        <th>Email</th>
                                        <th>Registered</th>
                                        <th>Orders</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($customerStats['recentCustomers'])): ?>
                                    <tr><td colspan="4" class="text-center text-muted py-3 fs-0-85">No customers yet.</td></tr>
                                    <?php else: ?>
                                    <?php foreach ($customerStats['recentCustomers'] as $rc): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <?php $bg = avatarBg($rc['full_name']); $tc = textContrast($bg); ?>
                                                <span class="avatar avatar-xs rounded-circle <?= $bg ?> <?= $tc ?> d-flex align-items-center justify-content-center fw-bold avatar-24 flex-shrink-0">
                                                    <?= htmlspecialchars(initials($rc['full_name'])) ?>
                                                </span>
                                                <a href="<?= BASE_URL ?>customers/?action=view&id=<?= $rc['id'] ?>" class="fw-semibold text-dark text-decoration-none">
                                                    <?= htmlspecialchars($rc['full_name']) ?>
                                                </a>
                                            </div>
                                        </td>
                                        <td class="text-muted fs-0-82"><?= htmlspecialchars($rc['email']) ?></td>
                                        <td class="tech-data fs-0-82"><?= date('d M Y', strtotime($rc['created_at'])) ?></td>
                                        <td><span class="badge-status <?= $rc['total_orders'] > 0 ? 'success' : 'secondary' ?> tech-data"><?= (int)$rc['total_orders'] ?></span></td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Customers Most Orders -->
                <div class="col-12 col-md-6">
                    <div class="card-premium overflow-hidden">
                        <div class="card-premium-header">
                            <h6 class="card-premium-title">Customers With Most Orders</h6>
                        </div>
                        <div class="table-premium-container">
                            <table class="table table-premium align-middle mb-0">
                                <thead><tr><th>Customer</th><th>Email</th><th>Orders</th></tr></thead>
                                <tbody>
                                    <?php if (empty($customerStats['mostOrders'])): ?>
                                    <tr><td colspan="3" class="text-center text-muted py-3 fs-0-85">No data yet.</td></tr>
                                    <?php else: ?>
                                    <?php foreach ($customerStats['mostOrders'] as $mo): ?>
                                    <tr>
                                        <td class="fw-semibold"><?= htmlspecialchars($mo['full_name']) ?></td>
                                        <td class="text-muted fs-0-82"><?= htmlspecialchars($mo['email']) ?></td>
                                        <td><span class="badge-status primary tech-data"><?= (int)$mo['total_orders'] ?></span></td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Customers Highest Spending -->
                <div class="col-12 col-md-6">
                    <div class="card-premium overflow-hidden">
                        <div class="card-premium-header">
                            <h6 class="card-premium-title">Customers With Highest Spending</h6>
                        </div>
                        <div class="table-premium-container">
                            <table class="table table-premium align-middle mb-0">
                                <thead><tr><th>Customer</th><th>Orders</th><th>Total Spent</th></tr></thead>
                                <tbody>
                                    <?php if (empty($customerStats['highestSpending'])): ?>
                                    <tr><td colspan="3" class="text-center text-muted py-3 fs-0-85">No data yet.</td></tr>
                                    <?php else: ?>
                                    <?php foreach ($customerStats['highestSpending'] as $hs): ?>
                                    <tr>
                                        <td class="fw-semibold"><?= htmlspecialchars($hs['full_name']) ?></td>
                                        <td class="tech-data"><?= (int)$hs['total_orders'] ?></td>
                                        <td class="fw-bold tech-data text-dark">₹<?= number_format((float)$hs['total_spent'], 2) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div><!-- /customers section -->

            <!-- ═══════════════════════════════════════════════════
                 CATEGORIES SECTION
            ═══════════════════════════════════════════════════ -->
            <div id="categories-section" class="row g-4 mb-4">
                <div class="col-12">
                    <div class="card-premium-header mb-2 px-0">
                        <h5 class="card-premium-title">Categories</h5>
                        <a href="<?= BASE_URL ?>products/" class="small text-decoration-none fw-bold text-primary fs-0-8">Manage Products</a>
                    </div>
                </div>

                <!-- Category summary cards -->
                <?php foreach ($categoryStats['perCategory'] as $cat): ?>
                <div class="col-6 col-sm-4 col-xl-3">
                    <div class="card-premium h-100 d-flex flex-column justify-content-between">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <span class="metric-card-title"><?= htmlspecialchars($cat['category']) ?></span>
                                <h3 class="metric-card-value"><?= (int)$cat['product_count'] ?></h3>
                            </div>
                            <div class="bg-primary-light p-2 rounded-3 text-primary d-flex align-items-center justify-content-center metric-card-icon-container">
                                <i data-lucide="tag" class="icon-md"></i>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2 mt-2">
                            <span class="badge-status success"><?= (int)$cat['active_count'] ?> active</span>
                            <span class="text-muted small fs-0-72">products</span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>

                <?php if (empty($categoryStats['perCategory'])): ?>
                <div class="col-12">
                    <div class="card-premium text-center py-4">
                        <p class="text-muted fs-0-85 mb-0">No categories found. Add products to populate this section.</p>
                    </div>
                </div>
                <?php endif; ?>
            </div><!-- /categories section -->

            <!-- ═══════════════════════════════════════════════════
                 BOTTOM CHARTS ROW
            ═══════════════════════════════════════════════════ -->
            <div class="row g-4 mb-4">
                <!-- Top Selling Products Chart -->
                <div class="col-12 col-lg-6">
                    <div class="card-premium overflow-hidden">
                        <div class="card-premium-header">
                            <div>
                                <h5 class="card-premium-title">Top Selling Products</h5>
                                <span class="small text-muted fs-0-75">By order count</span>
                            </div>
                        </div>
                        <div class="chart-container-premium">
                            <canvas id="topProdChart"></canvas>
                        </div>
                    </div>
                </div>
                <!-- Products Added Per Month Chart -->
                <div class="col-12 col-lg-6">
                    <div class="card-premium overflow-hidden">
                        <div class="card-premium-header">
                            <div>
                                <h5 class="card-premium-title">Products Added Per Month</h5>
                                <span class="small text-muted fs-0-75">Last 12 months</span>
                            </div>
                        </div>
                        <div class="chart-container-premium">
                            <canvas id="prodMonthChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

        </div><!-- /container-fluid -->
    </div><!-- /page-content-wrapper -->
</div><!-- /wrapper -->

<!-- ═══════════════════════════════════════════════════
     CHART.JS INITIALISATION (all live data from PHP)
═══════════════════════════════════════════════════ -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Shared chart defaults
    Chart.defaults.font.family = "'Familjen Grotesk', 'Inter', sans-serif";
    Chart.defaults.font.size   = 12;
    Chart.defaults.color       = '#64748b';

    const gridColor  = 'rgba(148,163,184,0.15)';
    const primaryCol = '#6366f1';
    const successCol = '#10b981';

    /* ─── 1. Store Activity Chart (Orders + Customers per month) ─── */
    const actCtx = document.getElementById('activityChart');
    if (actCtx) {
        new Chart(actCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode($activityLabels) ?>,
                datasets: [
                    {
                        label: 'Orders',
                        data: <?= json_encode($activityOrders) ?>,
                        borderColor: primaryCol,
                        backgroundColor: 'rgba(99,102,241,0.12)',
                        tension: 0.42,
                        fill: true,
                        pointRadius: 4,
                        pointBackgroundColor: primaryCol,
                    },
                    {
                        label: 'New Customers',
                        data: <?= json_encode($activityCustomers) ?>,
                        borderColor: successCol,
                        backgroundColor: 'rgba(16,185,129,0.08)',
                        tension: 0.42,
                        fill: true,
                        pointRadius: 4,
                        pointBackgroundColor: successCol,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { color: gridColor }, ticks: { maxTicksLimit: 8 } },
                    y: { beginAtZero: true, grid: { color: gridColor }, ticks: { stepSize: 1, precision: 0 } }
                }
            }
        });
    }

    /* ─── 2. Order Status Donut ─── */
    const statusCtx = document.getElementById('statusChart');
    if (statusCtx) {
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode(array_map('ucfirst', $statusLabels)) ?>,
                datasets: [{
                    data: <?= json_encode(array_values($statusCounts)) ?>,
                    backgroundColor: <?= json_encode(array_values($statusColors)) ?>,
                    borderWidth: 2,
                    borderColor: '#fff',
                    hoverBorderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '68%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => ` ${ctx.label}: ${ctx.parsed}`
                        }
                    }
                }
            }
        });
    }

    /* ─── 3. Orders Per Month Bar ─── */
    const ordMonCtx = document.getElementById('ordersMonthChart');
    if (ordMonCtx) {
        new Chart(ordMonCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($ordersMonthLabels) ?>,
                datasets: [{
                    label: 'Orders',
                    data: <?= json_encode($ordersMonthData) ?>,
                    backgroundColor: 'rgba(99,102,241,0.75)',
                    borderRadius: 5,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false } },
                    y: { beginAtZero: true, grid: { color: gridColor }, ticks: { precision: 0 } }
                }
            }
        });
    }

    /* ─── 4. Customers Per Month Line (small) ─── */
    const custMonCtx = document.getElementById('custMonthChart');
    if (custMonCtx) {
        new Chart(custMonCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode($custMonthLabels) ?>,
                datasets: [{
                    label: 'Customers',
                    data: <?= json_encode($custMonthData) ?>,
                    borderColor: successCol,
                    backgroundColor: 'rgba(16,185,129,0.12)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 3,
                    pointBackgroundColor: successCol,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false }, ticks: { maxTicksLimit: 4, font: { size: 10 } } },
                    y: { display: false, beginAtZero: true }
                }
            }
        });
    }

    /* ─── 5. Top Products Horizontal Bar ─── */
    const topPCtx = document.getElementById('topProdChart');
    if (topPCtx) {
        new Chart(topPCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($topProdLabels) ?>,
                datasets: [{
                    label: 'Orders',
                    data: <?= json_encode($topProdCounts) ?>,
                    backgroundColor: [
                        'rgba(99,102,241,0.8)',
                        'rgba(16,185,129,0.8)',
                        'rgba(245,158,11,0.8)',
                        'rgba(239,68,68,0.8)',
                        'rgba(14,165,233,0.8)',
                        'rgba(139,92,246,0.8)',
                    ],
                    borderRadius: 4,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { beginAtZero: true, grid: { color: gridColor }, ticks: { precision: 0 } },
                    y: { grid: { display: false } }
                }
            }
        });
    }

    /* ─── 6. Products Added Per Month ─── */
    const prodMonCtx = document.getElementById('prodMonthChart');
    if (prodMonCtx) {
        new Chart(prodMonCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode($prodMonthLabels) ?>,
                datasets: [{
                    label: 'Products Added',
                    data: <?= json_encode($prodMonthData) ?>,
                    borderColor: '#f59e0b',
                    backgroundColor: 'rgba(245,158,11,0.1)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 4,
                    pointBackgroundColor: '#f59e0b',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { color: gridColor }, ticks: { maxTicksLimit: 8 } },
                    y: { beginAtZero: true, grid: { color: gridColor }, ticks: { precision: 0 } }
                }
            }
        });
    }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
