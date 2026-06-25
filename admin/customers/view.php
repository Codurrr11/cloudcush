<?php
// admin/customers/view.php — Customer Detail + Edit + Order History
require_once __DIR__ . '/../includes/auth-check.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/customers-helper.php';
require_once __DIR__ . '/../config/orders-helper.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    $_SESSION['flash_message'] = 'Invalid customer ID.';
    $_SESSION['flash_type']    = 'error';
    header('Location: ' . BASE_URL . 'customers/');
    exit;
}

/* ── POST handler ──────────────────────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = trim($_POST['action'] ?? '');

    if ($action === 'update') {
        $data = [
            'full_name'     => trim($_POST['full_name']     ?? ''),
            'email'         => trim($_POST['email']         ?? ''),
            'phone'         => trim($_POST['phone']         ?? ''),
            'status'        => trim($_POST['status']        ?? 'active'),
            'gender'        => trim($_POST['gender']        ?? ''),
            'date_of_birth' => trim($_POST['date_of_birth'] ?? ''),
            'address'       => trim($_POST['address']       ?? ''),
        ];

        $errors = [];
        if ($data['full_name'] === '') {
            $errors[] = 'Full name is required.';
        }
        if ($data['email'] === '') {
            $errors[] = 'Email is required.';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email address.';
        } else {
            $db  = getDBConnection();
            $chk = $db->prepare("SELECT id FROM `customers` WHERE email = :email AND id != :id LIMIT 1");
            $chk->execute(['email' => $data['email'], 'id' => $id]);
            if ($chk->fetch()) {
                $errors[] = 'Another customer already uses this email.';
            }
        }

        // Sanitise optional fields
        if ($data['gender'] === '')        $data['gender']        = null;
        if ($data['date_of_birth'] === '') $data['date_of_birth'] = null;
        if ($data['address'] === '')       $data['address']       = null;
        if ($data['phone'] === '')         $data['phone']         = null;

        if (empty($errors)) {
            updateCustomer($id, $data);
            $_SESSION['flash_message'] = 'Customer updated successfully.';
            $_SESSION['flash_type']    = 'success';
            header('Location: ' . BASE_URL . "customers/view.php?id=$id");
            exit;
        } else {
            $_SESSION['flash_message'] = implode(' ', $errors);
            $_SESSION['flash_type']    = 'error';
        }

    } elseif ($action === 'toggle_status') {
        $newStatus = trim($_POST['status'] ?? '');
        updateCustomerStatus($id, $newStatus);
        $_SESSION['flash_message'] = 'Customer status updated.';
        $_SESSION['flash_type']    = 'success';
        header('Location: ' . BASE_URL . "customers/view.php?id=$id");
        exit;

    } elseif ($action === 'delete') {
        $cust = getCustomerById($id);
        if ($cust && deleteCustomer($id)) {
            $_SESSION['flash_message'] = "Customer \"{$cust['full_name']}\" deleted.";
            $_SESSION['flash_type']    = 'success';
            header('Location: ' . BASE_URL . 'customers/');
            exit;
        }
        $_SESSION['flash_message'] = 'Could not delete customer.';
        $_SESSION['flash_type']    = 'error';
    }
}

/* ── Fetch customer with order stats ─────────────────────── */
$customer = getCustomerById($id);
if (!$customer) {
    $_SESSION['flash_message'] = 'Customer not found.';
    $_SESSION['flash_type']    = 'error';
    header('Location: ' . BASE_URL . 'customers/');
    exit;
}

$orders    = getCustomerOrders($id, 8);
$addresses = getCustomerAddresses($id);
$badge     = getCustomerStatusBadge($customer['status'] ?? 'active');
$initial   = strtoupper(mb_substr($customer['full_name'] ?: '?', 0, 1));

$page_title  = 'Customer: ' . htmlspecialchars($customer['full_name']) . ' — CloudCush Admin';
$active_page = 'customers';

include __DIR__ . '/../includes/header.php';
?>

<div id="wrapper">
    <?php include __DIR__ . '/../includes/sidebar.php'; ?>

    <div id="page-content-wrapper">
        <?php include __DIR__ . '/../includes/navbar.php'; ?>

        <div class="container-fluid px-0 py-2">

            <?php include __DIR__ . '/../includes/alerts.php'; ?>

            <!-- ── Page Header ─────────────────────────────── -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
                <div class="d-flex align-items-center gap-3">
                    <a href="<?= BASE_URL ?>customers/"
                       class="btn-action btn-action-view btn-back-square" title="Back to customers">
                        <i data-lucide="arrow-left" class="icon-sm"></i>
                    </a>
                    <div>
                        <h1 class="h4 fw-bold mb-0 page-heading">Customer Details</h1>
                    </div>
                </div>
                <div class="d-flex gap-2 flex-shrink-0 flex-wrap">
                    <?php if (($customer['status'] ?? 'active') === 'active'): ?>
                        <form method="POST">
                            <input type="hidden" name="action" value="toggle_status">
                            <input type="hidden" name="status" value="inactive">
                            <button type="submit" class="btn btn-premium-secondary btn-sm d-flex align-items-center gap-2">
                                <i data-lucide="user-x" class="icon-sm"></i>
                                <span>Deactivate</span>
                            </button>
                        </form>
                    <?php else: ?>
                        <form method="POST">
                            <input type="hidden" name="action" value="toggle_status">
                            <input type="hidden" name="status" value="active">
                            <button type="submit" class="btn btn-premium-secondary btn-sm d-flex align-items-center gap-2">
                                <i data-lucide="user-check" class="icon-sm"></i>
                                <span>Activate</span>
                            </button>
                        </form>
                    <?php endif; ?>
                    <button type="button" id="btnDeleteCust"
                            class="btn btn-outline-danger btn-sm d-flex align-items-center gap-2"
                            data-id="<?= $customer['id'] ?>"
                            data-name="<?= htmlspecialchars($customer['full_name']) ?>">
                        <i data-lucide="trash-2" class="icon-sm"></i>
                        <span>Delete</span>
                    </button>
                </div>
            </div>

            <!-- ── Main Grid ──────────────────────────────── -->
            <div class="row g-3 align-items-start">

                <!-- LEFT: Profile summary + addresses ──────── -->
                <div class="col-12 col-lg-4">

                    <!-- Profile Card -->
                    <div class="card-premium mb-3">
                        <div class="text-center mb-4">
                            <div class="customer-avatar-lg mx-auto mb-3"><?= $initial ?></div>
                            <h5 class="fw-bold mb-1 fs-0-95"><?= htmlspecialchars($customer['full_name']) ?></h5>
                            <p class="text-muted fs-0-78 mb-2" style="word-break:break-all;">
                                <?= htmlspecialchars($customer['email']) ?>
                            </p>
                            <span class="badge-status <?= $badge['class'] ?>"><?= $badge['label'] ?></span>
                        </div>

                        <hr class="my-3" style="border-color:rgba(0,0,0,0.05);">

                        <div class="customer-info-list">
                            <div class="customer-info-row">
                                <div class="customer-info-label">
                                    <i data-lucide="hash" class="icon-xs"></i> ID
                                </div>
                                <div class="customer-info-value tech-data">#<?= $customer['id'] ?></div>
                            </div>
                            <div class="customer-info-row">
                                <div class="customer-info-label">
                                    <i data-lucide="phone" class="icon-xs"></i> Phone
                                </div>
                                <div class="customer-info-value">
                                    <?= !empty($customer['phone'])
                                        ? htmlspecialchars($customer['phone'])
                                        : '<span class="text-muted">Not provided</span>' ?>
                                </div>
                            </div>
                            <?php if (!empty($customer['gender'])): ?>
                            <div class="customer-info-row">
                                <div class="customer-info-label">
                                    <i data-lucide="user-circle" class="icon-xs"></i> Gender
                                </div>
                                <div class="customer-info-value"><?= ucfirst($customer['gender']) ?></div>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($customer['date_of_birth'])): ?>
                            <div class="customer-info-row">
                                <div class="customer-info-label">
                                    <i data-lucide="cake" class="icon-xs"></i> Birthday
                                </div>
                                <div class="customer-info-value tech-data fs-0-78">
                                    <?= date('M j, Y', strtotime($customer['date_of_birth'])) ?>
                                </div>
                            </div>
                            <?php endif; ?>
                            <div class="customer-info-row">
                                <div class="customer-info-label">
                                    <i data-lucide="calendar" class="icon-xs"></i> Joined
                                </div>
                                <div class="customer-info-value tech-data fs-0-78">
                                    <?= date('M j, Y', strtotime($customer['created_at'])) ?>
                                </div>
                            </div>
                            <?php if (!empty($customer['last_login'])): ?>
                            <div class="customer-info-row">
                                <div class="customer-info-label">
                                    <i data-lucide="log-in" class="icon-xs"></i> Last Login
                                </div>
                                <div class="customer-info-value tech-data fs-0-78">
                                    <?= date('M j, Y g:i A', strtotime($customer['last_login'])) ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Order Stats Card -->
                    <div class="card-premium mb-3">
                        <div class="card-premium-header mb-3">
                            <span class="card-premium-title d-flex align-items-center gap-2">
                                <i data-lucide="bar-chart-2" class="icon-md text-primary"></i>
                                Order Summary
                            </span>
                        </div>
                        <div class="row g-2">
                            <div class="col-6">
                                <div class="p-3 rounded-3 text-center" style="background:rgba(79,70,229,0.05);">
                                    <div class="tech-data fw-bold fs-4 text-dark">
                                        <?= (int)($customer['total_orders'] ?? 0) ?>
                                    </div>
                                    <div class="text-muted fs-0-72 fw-semibold">Total Orders</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 rounded-3 text-center" style="background:rgba(16,185,129,0.05);">
                                    <div class="tech-data fw-bold text-dark" style="font-size:1.1rem;">
                                        ₹<?= number_format((float)($customer['total_spent'] ?? 0), 0) ?>
                                    </div>
                                    <div class="text-muted fs-0-72 fw-semibold">Total Spent</div>
                                </div>
                            </div>
                        </div>
                        <?php if (!empty($customer['latest_order_date'])): ?>
                            <div class="mt-3 pt-3" style="border-top:1px solid rgba(0,0,0,0.04);">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fs-0-78 text-secondary">Latest Order</span>
                                    <span class="tech-data fs-0-75 text-muted">
                                        <?= date('M j, Y', strtotime($customer['latest_order_date'])) ?>
                                    </span>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if ((int)($customer['total_orders'] ?? 0) > 0): ?>
                            <div class="mt-3">
                                <a href="<?= BASE_URL ?>orders/?search=<?= urlencode($customer['email']) ?>"
                                   class="btn btn-premium-secondary btn-sm w-100 d-flex align-items-center justify-content-center gap-2">
                                    <i data-lucide="shopping-bag" class="icon-xs"></i>
                                    <span>View All Orders</span>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Saved Addresses -->
                    <?php if (!empty($addresses)): ?>
                    <div class="card-premium">
                        <div class="card-premium-header mb-3">
                            <span class="card-premium-title d-flex align-items-center gap-2">
                                <i data-lucide="map-pin" class="icon-md text-primary"></i>
                                Saved Addresses
                            </span>
                            <span class="tech-data fs-0-72 text-muted"><?= count($addresses) ?></span>
                        </div>
                        <?php foreach ($addresses as $addr): ?>
                        <div class="mb-3 p-3 rounded-3" style="background:rgba(255,255,255,0.5);border:1px solid rgba(226,232,240,0.6);">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="tech-data fs-0-68 fw-bold text-muted text-uppercase">
                                    <?= htmlspecialchars($addr['label'] ?: ($addr['address_type'] === 'shipping' ? 'Shipping' : 'Billing')) ?>
                                </span>
                                <?php if ($addr['is_default']): ?>
                                    <span class="badge-status success" style="font-size:0.6rem;">Default</span>
                                <?php endif; ?>
                            </div>
                            <div class="fw-semibold fs-0-82 text-dark mb-1">
                                <?= htmlspecialchars($addr['full_name']) ?>
                                <?php if (!empty($addr['phone'])): ?>
                                    <span class="text-muted fw-normal">&middot; <?= htmlspecialchars($addr['phone']) ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="fs-0-78 text-secondary lh-1-7">
                                <?= htmlspecialchars($addr['address_line_1']) ?>
                                <?php if (!empty($addr['address_line_2'])): ?>, <?= htmlspecialchars($addr['address_line_2']) ?><?php endif; ?><br>
                                <?= htmlspecialchars($addr['city'] . ', ' . $addr['state']) ?><br>
                                <?= htmlspecialchars($addr['country']) ?> &mdash; <?= htmlspecialchars($addr['zip_code']) ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                </div><!-- /col left -->

                <!-- RIGHT: Edit + Order History ────────────── -->
                <div class="col-12 col-lg-8">

                    <!-- Edit Form -->
                    <div class="card-premium mb-3">
                        <div class="card-premium-header mb-3">
                            <span class="card-premium-title d-flex align-items-center gap-2">
                                <i data-lucide="pencil" class="icon-md text-primary"></i>
                                Edit Customer
                            </span>
                        </div>
                        <form method="POST">
                            <input type="hidden" name="action" value="update">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold fs-0-82">Full Name <span style="color:#ef4444;">*</span></label>
                                    <input type="text" class="form-control-premium" name="full_name"
                                           value="<?= htmlspecialchars($customer['full_name']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold fs-0-82">Email <span style="color:#ef4444;">*</span></label>
                                    <input type="email" class="form-control-premium" name="email"
                                           value="<?= htmlspecialchars($customer['email']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold fs-0-82">Phone</label>
                                    <input type="tel" class="form-control-premium" name="phone"
                                           value="<?= htmlspecialchars($customer['phone'] ?? '') ?>"
                                           placeholder="+91 …">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold fs-0-82">Status</label>
                                    <select class="form-select-premium" name="status">
                                        <option value="active"   <?= ($customer['status'] ?? 'active') === 'active'   ? 'selected' : '' ?>>Active</option>
                                        <option value="inactive" <?= ($customer['status'] ?? 'active') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold fs-0-82">Gender</label>
                                    <select class="form-select-premium" name="gender">
                                        <option value="">Not specified</option>
                                        <option value="male"   <?= ($customer['gender'] ?? '') === 'male'   ? 'selected' : '' ?>>Male</option>
                                        <option value="female" <?= ($customer['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
                                        <option value="other"  <?= ($customer['gender'] ?? '') === 'other'  ? 'selected' : '' ?>>Other</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold fs-0-82">Date of Birth</label>
                                    <input type="date" class="form-control-premium" name="date_of_birth"
                                           value="<?= htmlspecialchars($customer['date_of_birth'] ?? '') ?>">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold fs-0-82">Address</label>
                                    <textarea class="form-control-premium" name="address" rows="2"
                                              placeholder="General address (optional)"><?= htmlspecialchars($customer['address'] ?? '') ?></textarea>
                                </div>
                            </div>
                            <div class="mt-4 d-flex gap-2">
                                <button type="submit" class="btn btn-premium-primary btn-sm d-flex align-items-center gap-2">
                                    <i data-lucide="check" class="icon-sm"></i>
                                    <span>Save Changes</span>
                                </button>
                                <a href="<?= BASE_URL ?>customers/view.php?id=<?= $id ?>"
                                   class="btn btn-premium-secondary btn-sm">Cancel</a>
                            </div>
                        </form>
                    </div>

                    <!-- Order History -->
                    <?php if (!empty($orders)): ?>
                    <div class="card-premium p-0 overflow-hidden">
                        <div class="card-premium-header px-4 pt-4 pb-0 mb-3">
                            <span class="card-premium-title d-flex align-items-center gap-2">
                                <i data-lucide="shopping-bag" class="icon-md text-primary"></i>
                                Order History
                            </span>
                            <a href="<?= BASE_URL ?>orders/?search=<?= urlencode($customer['email']) ?>"
                               class="edit-link-custom">View all →</a>
                        </div>
                        <div class="table-premium-container">
                            <table class="table table-premium align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th class="w-90-px">Order #</th>
                                        <th class="d-none d-md-table-cell">Items</th>
                                        <th class="w-110-px">Amount</th>
                                        <th class="d-none d-md-table-cell w-100-px">Payment</th>
                                        <th class="w-110-px">Status</th>
                                        <th class="d-none d-lg-table-cell w-100-px">Date</th>
                                        <th class="w-60-px text-end">View</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $ord):
                                        $oBadge   = getOrderStatusBadge($ord['status']         ?? 'pending');
                                        $oPay     = getPaymentStatusBadge($ord['payment_status'] ?? 'pending');
                                    ?>
                                    <tr>
                                        <td>
                                            <span class="tech-data fw-bold text-dark">#<?= $ord['id'] ?></span>
                                        </td>
                                        <td class="d-none d-md-table-cell">
                                            <span class="tech-data fs-0-78 text-muted">
                                                <?= (int)($ord['item_count'] ?? 0) ?> item<?= (int)($ord['item_count'] ?? 0) !== 1 ? 's' : '' ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="tech-data fw-bold fs-0-82 text-dark">
                                                ₹<?= number_format((float)$ord['total_amount'], 2) ?>
                                            </span>
                                        </td>
                                        <td class="d-none d-md-table-cell">
                                            <span class="badge-status <?= $oPay['class'] ?>">
                                                <?= $oPay['label'] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge-status <?= $oBadge['class'] ?>">
                                                <?= $oBadge['label'] ?>
                                            </span>
                                        </td>
                                        <td class="d-none d-lg-table-cell">
                                            <span class="tech-data fs-0-73 text-muted text-nowrap">
                                                <?= date('M j, Y', strtotime($ord['created_at'])) ?>
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <a href="<?= BASE_URL ?>orders/view.php?id=<?= $ord['id'] ?>"
                                               class="btn-action btn-action-view" title="View order">
                                                <i data-lucide="eye" class="icon-sm"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="card-premium">
                        <div class="card-premium-header mb-3">
                            <span class="card-premium-title d-flex align-items-center gap-2">
                                <i data-lucide="shopping-bag" class="icon-md text-primary"></i>
                                Order History
                            </span>
                        </div>
                        <div class="text-center py-4">
                            <div class="products-empty-icon mx-auto mb-2" style="width:48px;height:48px;">
                                <i data-lucide="shopping-bag" class="icon-xl"></i>
                            </div>
                            <p class="text-muted fs-0-82 mb-0">No orders placed by this customer yet.</p>
                        </div>
                    </div>
                    <?php endif; ?>

                </div><!-- /col right -->
            </div><!-- /row -->

        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    var deleteBtn = document.getElementById("btnDeleteCust");
    if (deleteBtn) {
        deleteBtn.addEventListener("click", function () {
            var name = this.dataset.name || "this customer";
            Swal.fire({
                title: "Delete Customer?",
                html: '<p style="font-size:0.88rem;color:#64748b;margin:0 0 0.5rem;">Permanently delete</p>' +
                      '<p style="font-size:0.9rem;font-weight:700;color:#0f172a;margin:0;">' + name + '</p>' +
                      '<p style="font-size:0.78rem;color:#94a3b8;margin:0.65rem 0 0;">This cannot be undone.</p>',
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Yes, delete",
                cancelButtonText: "Cancel",
                confirmButtonColor: "#dc2626",
                cancelButtonColor: "#e2e8f0",
                reverseButtons: true,
                customClass: {
                    popup: "swal2-premium-popup",
                    confirmButton: "swal2-confirm-danger",
                    cancelButton: "swal2-cancel-secondary",
                },
                buttonsStyling: false,
            }).then(function (res) {
                if (!res.isConfirmed) return;
                var f = document.createElement("form");
                f.method = "POST"; f.action = "";
                var a = document.createElement("input");
                a.type = "hidden"; a.name = "action"; a.value = "delete";
                f.appendChild(a); document.body.appendChild(f); f.submit();
            });
        });
    }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
