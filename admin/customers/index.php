<?php
// admin/customers/index.php — Customer Management Listing
require_once __DIR__ . '/../includes/auth-check.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/customers-helper.php';

/* ── POST: delete or status toggle ─────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    if ($id) {
        if (isset($_POST['status'])) {
            $newStatus = trim($_POST['status']);
            if (updateCustomerStatus($id, $newStatus)) {
                $_SESSION['flash_message'] = 'Customer status updated successfully.';
                $_SESSION['flash_type']    = 'success';
            } else {
                $_SESSION['flash_message'] = 'Failed to update customer status.';
                $_SESSION['flash_type']    = 'error';
            }
        } else {
            $customer = getCustomerById($id);
            if ($customer && deleteCustomer($id)) {
                $_SESSION['flash_message'] = "Customer \"{$customer['full_name']}\" deleted.";
                $_SESSION['flash_type']    = 'success';
            } else {
                $_SESSION['flash_message'] = 'Customer not found or could not be deleted.';
                $_SESSION['flash_type']    = 'error';
            }
        }
        header('Location: ' . BASE_URL . 'customers/');
        exit;
    }
}

$page_title  = 'CloudCush Admin — Customers';
$active_page = 'customers';

$search  = trim($_GET['search'] ?? '');
$status  = trim($_GET['status'] ?? '');
$curPage = max(1, (int)($_GET['page'] ?? 1));

$result     = getCustomers(['search' => $search, 'status' => $status, 'page' => $curPage, 'per_page' => 15]);
$customers  = $result['data'];
$totalPages = $result['total_pages'];
$totalCount = $result['total'];
$stats      = getCustomerStats();

include __DIR__ . '/../includes/header.php';
?>

<input type="hidden" id="custHandlerUrl" value="<?= BASE_URL ?>customers/index.php">

<div id="wrapper">
    <?php include __DIR__ . '/../includes/sidebar.php'; ?>

    <div id="page-content-wrapper">
        <?php include __DIR__ . '/../includes/navbar.php'; ?>

        <div class="container-fluid px-0 py-2">

            <?php include __DIR__ . '/../includes/alerts.php'; ?>

            <!-- ── Page Header ─────────────────────────────── -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
                <div>
                    <h1 class="h4 fw-bold mb-1 page-heading">Customer Management</h1>
                    <p class="text-secondary mb-0 fs-0-82">
                        View and manage all registered customers &mdash;
                        <span class="tech-data fw-semibold"><?= number_format($totalCount) ?></span>
                        customer<?= $totalCount !== 1 ? 's' : '' ?> total
                    </p>
                </div>
            </div>

            <!-- ── Stats ───────────────────────────────────── -->
            <div class="row g-3 mb-4">
                <div class="col-6 col-lg-3">
                    <div class="card-premium p-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="stat-card-icon bg-primary-light d-flex align-items-center justify-content-center rounded-circle">
                                <i data-lucide="users" class="icon-md text-primary"></i>
                            </div>
                            <div>
                                <div class="tech-data fw-bold fs-5 text-dark"><?= number_format($stats['total']) ?></div>
                                <div class="text-muted fs-0-75 fw-semibold">Total</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="card-premium p-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="stat-card-icon bg-success-light d-flex align-items-center justify-content-center rounded-circle">
                                <i data-lucide="user-check" class="icon-md text-success"></i>
                            </div>
                            <div>
                                <div class="tech-data fw-bold fs-5 text-dark"><?= number_format($stats['active']) ?></div>
                                <div class="text-muted fs-0-75 fw-semibold">Active</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="card-premium p-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="stat-card-icon d-flex align-items-center justify-content-center rounded-circle"
                                 style="background:rgba(100,116,139,.08);">
                                <i data-lucide="user-x" class="icon-md text-secondary"></i>
                            </div>
                            <div>
                                <div class="tech-data fw-bold fs-5 text-dark"><?= number_format($stats['inactive']) ?></div>
                                <div class="text-muted fs-0-75 fw-semibold">Inactive</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="card-premium p-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="stat-card-icon d-flex align-items-center justify-content-center rounded-circle"
                                 style="background:rgba(8,145,178,.08);">
                                <i data-lucide="user-plus" class="icon-md" style="color:#0891b2;"></i>
                            </div>
                            <div>
                                <div class="tech-data fw-bold fs-5 text-dark"><?= number_format($stats['newMonth']) ?></div>
                                <div class="text-muted fs-0-75 fw-semibold">New This Month</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Filter Bar ──────────────────────────────── -->
            <form method="GET" action="">
                <div class="products-filterbar">
                    <div class="search-input-wrap">
                        <i data-lucide="search" class="search-icon"></i>
                        <input type="text" name="search" class="form-control"
                               placeholder="Name, email, phone…"
                               value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="products-filterbar-selects">
                        <select name="status" class="form-select filter-autosubmit">
                            <option value="">All Statuses</option>
                            <option value="active"   <?= $status === 'active'   ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-premium-secondary btn-sm d-flex align-items-center gap-2">
                        <i data-lucide="search" class="icon-xs"></i>
                        <span>Search</span>
                    </button>
                    <?php if ($search || $status): ?>
                        <a href="<?= BASE_URL ?>customers/" class="btn btn-premium-secondary btn-sm d-flex align-items-center gap-2">
                            <i data-lucide="x" class="icon-xs"></i>
                            <span>Clear</span>
                        </a>
                    <?php endif; ?>
                </div>
            </form>

            <!-- ── Customers Table ─────────────────────────── -->
            <div class="card-premium p-0 overflow-hidden">

                <?php if (empty($customers)): ?>
                    <div class="products-empty-state">
                        <div class="products-empty-icon">
                            <i data-lucide="users" class="icon-xl"></i>
                        </div>
                        <h5 class="fw-bold mb-1 fs-0-95">No Customers Found</h5>
                        <p class="text-secondary mb-3 mx-auto max-width-360 fs-0-81 line-height-1-55">
                            <?= ($search || $status)
                                ? 'No customers match your filters. Try adjusting your search or clearing filters.'
                                : 'No registered customers yet. They will appear here once someone signs up.' ?>
                        </p>
                        <?php if ($search || $status): ?>
                            <a href="<?= BASE_URL ?>customers/" class="btn btn-premium-secondary btn-sm mx-auto w-fit-content">Clear Filters</a>
                        <?php endif; ?>
                    </div>

                <?php else: ?>
                    <div class="table-premium-container">
                        <table class="table table-premium align-middle">
                            <thead>
                                <tr>
                                    <th class="w-52-px">ID</th>
                                    <th>Customer</th>
                                    <th class="d-none d-md-table-cell">Contact</th>
                                    <th class="d-none d-lg-table-cell w-80-px text-center">Orders</th>
                                    <th class="d-none d-lg-table-cell w-110-px">Spent</th>
                                    <th class="d-none d-xl-table-cell">Latest Order</th>
                                    <th class="w-80-px">Status</th>
                                    <th class="d-none d-xl-table-cell w-100-px">Joined</th>
                                    <th class="w-108-px text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($customers as $cust):
                                    $badge = getCustomerStatusBadge($cust['status'] ?? 'active');
                                    $totalOrders = (int)($cust['total_orders'] ?? 0);
                                    $totalSpent  = (float)($cust['total_spent'] ?? 0);
                                    $latestOrder = !empty($cust['latest_order_date'])
                                        ? date('M j, Y', strtotime($cust['latest_order_date']))
                                        : '—';
                                ?>
                                <tr>
                                    <td>
                                        <span class="tech-data fw-semibold text-muted">#<?= $cust['id'] ?></span>
                                    </td>

                                    <td class="max-width-0">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="customer-avatar-sm flex-shrink-0">
                                                <?= strtoupper(mb_substr($cust['full_name'] ?: '?', 0, 1)) ?>
                                            </div>
                                            <div class="min-width-0">
                                                <div class="product-title-text" title="<?= htmlspecialchars($cust['full_name']) ?>">
                                                    <?= htmlspecialchars($cust['full_name']) ?>
                                                </div>
                                                <div class="tech-data fs-0-72 text-muted d-md-none text-ellipsis-100">
                                                    <?= htmlspecialchars($cust['email']) ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="d-none d-md-table-cell">
                                        <div class="tech-data fs-0-78 text-muted text-ellipsis-100"
                                             title="<?= htmlspecialchars($cust['email']) ?>">
                                            <?= htmlspecialchars($cust['email']) ?>
                                        </div>
                                        <?php if (!empty($cust['phone'])): ?>
                                            <div class="tech-data fs-0-72 text-muted mt-1">
                                                <?= htmlspecialchars($cust['phone']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>

                                    <td class="d-none d-lg-table-cell text-center">
                                        <?php if ($totalOrders > 0): ?>
                                            <a href="<?= BASE_URL ?>orders/?search=<?= urlencode($cust['email']) ?>"
                                               class="tech-data fw-bold text-primary text-decoration-none fs-0-88">
                                                <?= $totalOrders ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="tech-data text-muted fs-0-82">0</span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="d-none d-lg-table-cell">
                                        <?php if ($totalSpent > 0): ?>
                                            <span class="tech-data fw-bold fs-0-82 text-dark">
                                                ₹<?= number_format($totalSpent, 2) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="tech-data text-muted fs-0-82">—</span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="d-none d-xl-table-cell">
                                        <span class="tech-data fs-0-75 text-muted text-nowrap"><?= $latestOrder ?></span>
                                    </td>

                                    <td>
                                        <span class="badge-status <?= $badge['class'] ?>">
                                            <?= $badge['label'] ?>
                                        </span>
                                    </td>

                                    <td class="d-none d-xl-table-cell">
                                        <span class="tech-data fs-0-73 text-muted text-nowrap">
                                            <?= date('M j, Y', strtotime($cust['created_at'])) ?>
                                        </span>
                                    </td>

                                    <td>
                                        <div class="tbl-actions">
                                            <a href="<?= BASE_URL ?>customers/view.php?id=<?= $cust['id'] ?>"
                                               class="btn-action btn-action-view" title="View customer">
                                                <i data-lucide="eye" class="icon-sm"></i>
                                            </a>
                                            <?php if (($cust['status'] ?? 'active') === 'active'): ?>
                                                <button type="button"
                                                        class="btn-action btn-action-edit btn-toggle-cust"
                                                        data-id="<?= $cust['id'] ?>"
                                                        data-status="inactive"
                                                        data-name="<?= htmlspecialchars($cust['full_name']) ?>"
                                                        title="Deactivate">
                                                    <i data-lucide="user-x" class="icon-sm"></i>
                                                </button>
                                            <?php else: ?>
                                                <button type="button"
                                                        class="btn-action btn-action-edit btn-toggle-cust"
                                                        data-id="<?= $cust['id'] ?>"
                                                        data-status="active"
                                                        data-name="<?= htmlspecialchars($cust['full_name']) ?>"
                                                        title="Activate">
                                                    <i data-lucide="user-check" class="icon-sm"></i>
                                                </button>
                                            <?php endif; ?>
                                            <button type="button"
                                                    class="btn-action btn-action-delete btn-delete-cust"
                                                    data-id="<?= $cust['id'] ?>"
                                                    data-name="<?= htmlspecialchars($cust['full_name']) ?>"
                                                    title="Delete">
                                                <i data-lucide="trash-2" class="icon-sm"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($totalPages > 1):
                        $baseUrl = BASE_URL . 'customers/?search=' . urlencode($search) . '&status=' . urlencode($status);
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
    var handler = document.getElementById("custHandlerUrl")?.value || "";

    document.querySelectorAll(".filter-autosubmit").forEach(function(el) {
        el.addEventListener("change", function() { this.closest("form").submit(); });
    });

    // Delete
    document.querySelectorAll(".btn-delete-cust").forEach(function(btn) {
        btn.addEventListener("click", function() {
            var id = this.dataset.id, name = this.dataset.name || "this customer";
            Swal.fire({
                title: "Delete Customer?",
                html: '<p style="font-size:0.88rem;color:#64748b;margin:0 0 0.5rem;">Permanently delete</p>' +
                      '<p style="font-size:0.9rem;font-weight:700;color:#0f172a;margin:0;">' + name + '</p>' +
                      '<p style="font-size:0.78rem;color:#94a3b8;margin:0.6rem 0 0;">This cannot be undone.</p>',
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Yes, delete",
                cancelButtonText: "Cancel",
                confirmButtonColor: "#dc2626",
                cancelButtonColor: "#e2e8f0",
                reverseButtons: true,
                customClass: { popup: "swal2-premium-popup", confirmButton: "swal2-confirm-danger", cancelButton: "swal2-cancel-secondary" },
                buttonsStyling: false,
            }).then(function(res) {
                if (!res.isConfirmed) return;
                var f = document.createElement("form"); f.method = "POST"; f.action = handler;
                var i = document.createElement("input"); i.type = "hidden"; i.name = "id"; i.value = id;
                f.appendChild(i); document.body.appendChild(f); f.submit();
            });
        });
    });

    // Toggle status
    document.querySelectorAll(".btn-toggle-cust").forEach(function(btn) {
        btn.addEventListener("click", function() {
            var id = this.dataset.id, newStatus = this.dataset.status, name = this.dataset.name || "this customer";
            var action = newStatus === "active" ? "activate" : "deactivate";
            Swal.fire({
                title: (newStatus === "active" ? "Activate" : "Deactivate") + " Customer?",
                html: '<p style="font-size:0.88rem;color:#64748b;margin:0 0 0.5rem;">You are about to ' + action + '</p>' +
                      '<p style="font-size:0.9rem;font-weight:700;color:#0f172a;margin:0;">' + name + '</p>',
                icon: "question",
                showCancelButton: true,
                confirmButtonText: "Yes, " + action,
                cancelButtonText: "Cancel",
                confirmButtonColor: newStatus === "active" ? "#16a34a" : "#6b7280",
                cancelButtonColor: "#e2e8f0",
                reverseButtons: true,
                customClass: { popup: "swal2-premium-popup", cancelButton: "swal2-cancel-secondary" },
                buttonsStyling: false,
            }).then(function(res) {
                if (!res.isConfirmed) return;
                var f = document.createElement("form"); f.method = "POST"; f.action = handler;
                [["id",id],["status",newStatus]].forEach(function(p){
                    var i = document.createElement("input"); i.type="hidden"; i.name=p[0]; i.value=p[1]; f.appendChild(i);
                });
                document.body.appendChild(f); f.submit();
            });
        });
    });
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
