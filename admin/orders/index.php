<?php
// admin/orders/index.php — Order Management Listing
require_once __DIR__ . '/../includes/auth-check.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/orders-helper.php';

/* ── POST: status quick-update ──────────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    if ($id && $_POST['action'] === 'update_status') {
        $newStatus = trim($_POST['status'] ?? '');
        if (updateOrderStatus($id, $newStatus)) {
            $_SESSION['flash_message'] = "Order #$id status updated to " . ucfirst($newStatus) . ".";
            $_SESSION['flash_type']    = 'success';
        } else {
            $_SESSION['flash_message'] = 'Invalid status or order not found.';
            $_SESSION['flash_type']    = 'error';
        }
        header('Location: ' . BASE_URL . 'orders/');
        exit;
    }
}

$page_title  = 'CloudCush Admin — Orders';
$active_page = 'orders';

/* ── Filter / pagination params ─────────────────────────────── */
$search  = trim($_GET['search'] ?? '');
$status  = trim($_GET['status'] ?? '');
$curPage = max(1, (int)($_GET['page'] ?? 1));

$result     = getOrders(['search' => $search, 'status' => $status, 'page' => $curPage, 'per_page' => 15]);
$orders     = $result['data'];
$totalPages = $result['total_pages'];
$totalCount = $result['total'];
$stats      = getOrderStats();
$statuses   = getOrderStatuses();

include __DIR__ . '/../includes/header.php';
?>

<input type="hidden" id="orderStatusHandlerUrl" value="<?= BASE_URL ?>orders/index.php">

<div id="wrapper">
    <?php include __DIR__ . '/../includes/sidebar.php'; ?>

    <div id="page-content-wrapper">
        <?php include __DIR__ . '/../includes/navbar.php'; ?>

        <div class="container-fluid px-0 py-2">

            <?php include __DIR__ . '/../includes/alerts.php'; ?>

            <!-- ── Page Header ──────────────────────────────── -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
                <div>
                    <h1 class="h4 fw-bold mb-1 page-heading">Order Management</h1>
                    <p class="text-secondary mb-0 fs-0-82">
                        Track, update and manage all customer orders &mdash;
                        <span class="tech-data fw-semibold"><?= number_format($totalCount) ?></span>
                        order<?= $totalCount !== 1 ? 's' : '' ?> total
                    </p>
                </div>
            </div>

            <!-- ── Stats Row ─────────────────────────────────── -->
            <div class="row g-3 mb-4">
                <div class="col-6 col-lg-3">
                    <div class="card-premium p-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="stat-card-icon bg-primary-light d-flex align-items-center justify-content-center rounded-circle">
                                <i data-lucide="shopping-bag" class="icon-md text-primary"></i>
                            </div>
                            <div>
                                <div class="tech-data fw-bold fs-5 text-dark"><?= number_format($stats['total']) ?></div>
                                <div class="text-muted fs-0-75 fw-semibold">Total Orders</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="card-premium p-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="stat-card-icon d-flex align-items-center justify-content-center rounded-circle" style="background:rgba(217,119,6,.1);">
                                <i data-lucide="clock" class="icon-md" style="color:#d97706;"></i>
                            </div>
                            <div>
                                <div class="tech-data fw-bold fs-5 text-dark"><?= number_format($stats['pending']) ?></div>
                                <div class="text-muted fs-0-75 fw-semibold">Pending</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="card-premium p-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="stat-card-icon bg-success-light d-flex align-items-center justify-content-center rounded-circle">
                                <i data-lucide="package-check" class="icon-md text-success"></i>
                            </div>
                            <div>
                                <div class="tech-data fw-bold fs-5 text-dark"><?= number_format($stats['delivered']) ?></div>
                                <div class="text-muted fs-0-75 fw-semibold">Delivered</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="card-premium p-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="stat-card-icon d-flex align-items-center justify-content-center rounded-circle" style="background:rgba(5,150,105,.1);">
                                <i data-lucide="indian-rupee" class="icon-md text-success"></i>
                            </div>
                            <div>
                                <div class="tech-data fw-bold fs-5 text-dark">₹<?= $stats['revenue'] ?></div>
                                <div class="text-muted fs-0-75 fw-semibold">Revenue</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Filter Bar ─────────────────────────────────── -->
            <form method="GET" action="" id="ordersFilterForm">
                <div class="products-filterbar">
                    <div class="search-input-wrap">
                        <i data-lucide="search" class="search-icon"></i>
                        <input type="text" name="search" class="form-control"
                               placeholder="Order ID, customer name or email…"
                               value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="products-filterbar-selects">
                        <select name="status" class="form-select filter-autosubmit">
                            <option value="">All Statuses</option>
                            <?php foreach ($statuses as $key => $cfg): ?>
                                <option value="<?= $key ?>" <?= $status === $key ? 'selected' : '' ?>>
                                    <?= $cfg['label'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-premium-secondary btn-sm d-flex align-items-center gap-2">
                        <i data-lucide="search" class="icon-xs"></i>
                        <span>Search</span>
                    </button>
                    <?php if ($search || $status): ?>
                        <a href="<?= BASE_URL ?>orders/" class="btn btn-premium-secondary btn-sm d-flex align-items-center gap-2">
                            <i data-lucide="x" class="icon-xs"></i>
                            <span>Clear</span>
                        </a>
                    <?php endif; ?>
                </div>
            </form>

            <!-- ── Orders Table ───────────────────────────────── -->
            <div class="card-premium p-0 overflow-hidden">

                <?php if (empty($orders)): ?>
                    <div class="products-empty-state">
                        <div class="products-empty-icon">
                            <i data-lucide="shopping-bag" class="icon-xl"></i>
                        </div>
                        <h5 class="fw-bold mb-1 fs-0-95">No Orders Found</h5>
                        <p class="text-secondary mb-3 mx-auto max-width-360 fs-0-81 line-height-1-55">
                            <?= ($search || $status)
                                ? 'No orders match your filters. Try adjusting your search or clearing filters.'
                                : 'No orders have been placed yet. They will appear here once customers check out.' ?>
                        </p>
                        <?php if ($search || $status): ?>
                            <a href="<?= BASE_URL ?>orders/" class="btn btn-premium-secondary btn-sm mx-auto w-fit-content">Clear Filters</a>
                        <?php endif; ?>
                    </div>

                <?php else: ?>
                    <div class="table-premium-container">
                        <table class="table table-premium align-middle">
                            <thead>
                                <tr>
                                    <th class="w-90-px">Order #</th>
                                    <th>Customer</th>
                                    <th class="d-none d-md-table-cell">Items</th>
                                    <th class="d-none d-lg-table-cell w-110-px">Amount</th>
                                    <th class="d-none d-xl-table-cell w-100-px">Payment</th>
                                    <th class="w-120-px">Status</th>
                                    <th class="d-none d-xl-table-cell w-100-px">Date</th>
                                    <th class="w-120-px text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order):
                                    $badge    = getOrderStatusBadge($order['status'] ?? 'pending');
                                    $payBadge = getPaymentStatusBadge($order['payment_status'] ?? 'pending');

                                    // Parse items for summary
                                    $itemsRaw  = !empty($order['order_items']) ? json_decode($order['order_items'], true) : [];
                                    $itemsRaw  = is_array($itemsRaw) ? $itemsRaw : [];
                                    $itemCount = !empty($itemsRaw)
                                        ? array_sum(array_column($itemsRaw, 'quantity'))
                                        : (int)($order['item_count'] ?? 1);

                                    $firstItem   = !empty($itemsRaw) ? $itemsRaw[0] : null;
                                    $itemSummary = $firstItem
                                        ? htmlspecialchars(mb_strimwidth($firstItem['name'] ?? 'Item', 0, 32, '…'))
                                          . (!empty($firstItem['size']) ? ' · ' . htmlspecialchars($firstItem['size']) : '')
                                        : '—';
                                    $extraCount  = count($itemsRaw) > 1 ? count($itemsRaw) - 1 : 0;
                                ?>
                                <tr>
                                    <!-- Order ID -->
                                    <td>
                                        <span class="tech-data fw-bold text-dark">#<?= $order['id'] ?></span>
                                        <?php if (!empty($order['created_at'])): ?>
                                            <div class="tech-data fs-0-68 text-muted d-xl-none mt-1">
                                                <?= date('M j', strtotime($order['created_at'])) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Customer -->
                                    <td class="max-width-0">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="customer-avatar-sm flex-shrink-0">
                                                <?= strtoupper(mb_substr($order['customer_name'] ?: '?', 0, 1)) ?>
                                            </div>
                                            <div class="min-width-0">
                                                <a href="<?= !empty($order['customer_id']) ? BASE_URL . 'customers/view.php?id=' . (int)$order['customer_id'] : '#' ?>"
                                                   class="product-title-text text-decoration-none text-dark"
                                                   title="<?= htmlspecialchars($order['customer_name']) ?>">
                                                    <?= htmlspecialchars($order['customer_name']) ?>
                                                </a>
                                                <div class="tech-data fs-0-73 text-muted text-ellipsis-100"
                                                     title="<?= htmlspecialchars($order['customer_email']) ?>">
                                                    <?= htmlspecialchars($order['customer_email']) ?>
                                                </div>
                                                <?php if (!empty($order['customer_phone'])): ?>
                                                    <div class="tech-data fs-0-68 text-muted">
                                                        <?= htmlspecialchars($order['customer_phone']) ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Items summary -->
                                    <td class="d-none d-md-table-cell">
                                        <div class="fs-0-81 text-dark fw-semibold">
                                            <?= $itemSummary ?>
                                            <?php if ($extraCount > 0): ?>
                                                <span class="badge-variant-count">+<?= $extraCount ?> more</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="tech-data fs-0-72 text-muted mt-1">
                                            <?= $itemCount ?> item<?= $itemCount !== 1 ? 's' : '' ?>
                                        </div>
                                    </td>

                                    <!-- Amount -->
                                    <td class="d-none d-lg-table-cell">
                                        <span class="tech-data fw-bold table-price-cell">
                                            ₹<?= number_format((float)$order['total_amount'], 2) ?>
                                        </span>
                                        <?php if (!empty($order['shipping_amount']) && (float)$order['shipping_amount'] > 0): ?>
                                            <div class="tech-data fs-0-68 text-muted mt-1">
                                                +₹<?= number_format((float)$order['shipping_amount'], 0) ?> ship
                                            </div>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Payment status -->
                                    <td class="d-none d-xl-table-cell">
                                        <span class="badge-status <?= $payBadge['class'] ?>">
                                            <?= $payBadge['label'] ?>
                                        </span>
                                        <?php if (!empty($order['payment_method'])): ?>
                                            <div class="tech-data fs-0-68 text-muted mt-1 text-uppercase">
                                                <?= htmlspecialchars($order['payment_method']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Order status -->
                                    <td>
                                        <span class="badge-status <?= $badge['class'] ?>">
                                            <?= $badge['label'] ?>
                                        </span>
                                    </td>

                                    <!-- Date -->
                                    <td class="d-none d-xl-table-cell">
                                        <span class="tech-data fs-0-73 text-muted text-nowrap">
                                            <?= date('M j, Y', strtotime($order['created_at'])) ?>
                                        </span>
                                    </td>

                                    <!-- Actions -->
                                    <td>
                                        <div class="tbl-actions">
                                            <a href="<?= BASE_URL ?>orders/view.php?id=<?= $order['id'] ?>"
                                               class="btn-action btn-action-view" title="View order detail">
                                                <i data-lucide="eye" class="icon-sm"></i>
                                            </a>
                                            <?php if (!empty($order['customer_id'])): ?>
                                                <a href="<?= BASE_URL ?>customers/view.php?id=<?= (int)$order['customer_id'] ?>"
                                                   class="btn-action btn-action-edit" title="View customer">
                                                    <i data-lucide="user" class="icon-sm"></i>
                                                </a>
                                            <?php endif; ?>
                                            <div class="dropdown">
                                                <button class="btn-action btn-action-edit"
                                                        type="button"
                                                        data-bs-toggle="dropdown"
                                                        aria-expanded="false"
                                                        title="Update status">
                                                    <i data-lucide="pencil" class="icon-sm"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 p-1 rounded-3"
                                                    style="min-width:160px;background:rgba(255,255,255,0.97);backdrop-filter:blur(12px);">
                                                    <?php foreach ($statuses as $sKey => $sCfg): ?>
                                                        <li>
                                                            <button type="button"
                                                                    class="dropdown-item rounded-2 fs-0-78 btn-update-order-status
                                                                           <?= ($order['status'] ?? '') === $sKey ? 'active fw-bold' : '' ?>"
                                                                    data-id="<?= $order['id'] ?>"
                                                                    data-status="<?= $sKey ?>"
                                                                    data-label="<?= htmlspecialchars($sCfg['label']) ?>">
                                                                <i data-lucide="<?= $sCfg['icon'] ?>" class="icon-xs me-1"></i>
                                                                <?= $sCfg['label'] ?>
                                                            </button>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- ── Pagination ─────────────────────────── -->
                    <?php if ($totalPages > 1):
                        $baseUrl = BASE_URL . 'orders/?search=' . urlencode($search) . '&status=' . urlencode($status);
                    ?>
                        <div class="pagination-footer">
                            <span class="pagination-info">
                                Page <span class="tech-data fw-bold text-dark"><?= $curPage ?></span>
                                of <span class="tech-data fw-bold text-dark"><?= $totalPages ?></span>
                                &middot; <?= number_format($totalCount) ?> total
                            </span>
                            <div class="pagination-premium">
                                <a href="<?= $baseUrl ?>&page=<?= max(1, $curPage - 1) ?>"
                                   class="page-btn <?= $curPage <= 1 ? 'disabled' : '' ?>" aria-label="Previous">
                                    <i data-lucide="chevron-left" class="icon-sm"></i>
                                </a>
                                <?php
                                $pStart = max(1, $curPage - 2);
                                $pEnd   = min($totalPages, $curPage + 2);
                                if ($pStart > 1): ?>
                                    <a href="<?= $baseUrl ?>&page=1" class="page-btn">1</a>
                                    <?php if ($pStart > 2): ?><span class="page-btn page-ellipsis">…</span><?php endif; ?>
                                <?php endif; ?>
                                <?php for ($pN = $pStart; $pN <= $pEnd; $pN++): ?>
                                    <a href="<?= $baseUrl ?>&page=<?= $pN ?>"
                                       class="page-btn <?= $pN === $curPage ? 'active' : '' ?>">
                                        <?= $pN ?>
                                    </a>
                                <?php endfor; ?>
                                <?php if ($pEnd < $totalPages): ?>
                                    <?php if ($pEnd < $totalPages - 1): ?><span class="page-btn page-ellipsis">…</span><?php endif; ?>
                                    <a href="<?= $baseUrl ?>&page=<?= $totalPages ?>" class="page-btn"><?= $totalPages ?></a>
                                <?php endif; ?>
                                <a href="<?= $baseUrl ?>&page=<?= min($totalPages, $curPage + 1) ?>"
                                   class="page-btn <?= $curPage >= $totalPages ? 'disabled' : '' ?>" aria-label="Next">
                                    <i data-lucide="chevron-right" class="icon-sm"></i>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>

                <?php endif; ?>
            </div>

        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    var handlerUrl = document.getElementById("orderStatusHandlerUrl")?.value || "";

    document.querySelectorAll(".filter-autosubmit").forEach(function (el) {
        el.addEventListener("change", function () { this.closest("form").submit(); });
    });

    document.querySelectorAll(".btn-update-order-status").forEach(function (btn) {
        btn.addEventListener("click", function () {
            var id     = this.dataset.id;
            var status = this.dataset.status;
            var label  = this.dataset.label;

            Swal.fire({
                title: "Update Order Status?",
                html:
                    '<p style="font-size:0.88rem;color:#64748b;margin:0 0 0.4rem;">Order <strong>#' + id + '</strong> will be marked as:</p>' +
                    '<p style="font-size:1rem;font-weight:700;color:#0f172a;margin:0;">' + label + '</p>',
                icon: "question",
                showCancelButton: true,
                confirmButtonText: "Yes, update",
                cancelButtonText: "Cancel",
                confirmButtonColor: "#4f46e5",
                cancelButtonColor: "#e2e8f0",
                reverseButtons: true,
                customClass: {
                    popup:         "swal2-premium-popup",
                    confirmButton: "swal2-confirm-primary",
                    cancelButton:  "swal2-cancel-secondary",
                },
                buttonsStyling: false,
            }).then(function (result) {
                if (!result.isConfirmed) return;
                var form = document.createElement("form");
                form.method = "POST";
                form.action = handlerUrl;
                [["action","update_status"],["id",id],["status",status]].forEach(function(p){
                    var i = document.createElement("input");
                    i.type = "hidden"; i.name = p[0]; i.value = p[1];
                    form.appendChild(i);
                });
                document.body.appendChild(form);
                form.submit();
            });
        });
    });
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
