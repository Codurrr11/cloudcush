<?php
// admin/orders/view.php — Order Detail
require_once __DIR__ . '/../includes/auth-check.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/orders-helper.php';

/* ── POST: status update ──────────────────────────────────── */
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
        header('Location: ' . BASE_URL . "orders/view.php?id=$id");
        exit;
    }
}

/* ── Fetch order ─────────────────────────────────────────── */
$id    = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$order = $id ? getOrderById($id) : null;

if (!$order) {
    $_SESSION['flash_message'] = 'Order not found.';
    $_SESSION['flash_type']    = 'error';
    header('Location: ' . BASE_URL . 'orders/');
    exit;
}

$items    = parseOrderItems($order);
$badge    = getOrderStatusBadge($order['status']         ?? 'pending');
$payBadge = getPaymentStatusBadge($order['payment_status'] ?? 'pending');
$statuses = getOrderStatuses();
$currentStatus = $order['status'] ?? 'pending';

// Status flow order (for the progress track)
$statusKeys = array_keys($statuses);
$currentIdx = array_search($currentStatus, $statusKeys);

// Pricing breakdown
$subtotal  = (float)($order['subtotal']         ?? 0);
$shipping  = (float)($order['shipping_amount']  ?? 0);
$discount  = (float)($order['discount_amount']  ?? 0);
$total     = (float)($order['total_amount']      ?? 0);

// Fallback: calculate subtotal from items if stored subtotal is 0
if ($subtotal <= 0 && !empty($items)) {
    foreach ($items as $itm) {
        $subtotal += (float)($itm['line_total'] ?? ((float)($itm['price'] ?? 0) * (int)($itm['quantity'] ?? 1)));
    }
}

// Address: prefer structured address from customer_addresses JOIN, else parse delivery_address text
$hasStructured = !empty($order['address_line_1']);
$hasText       = !empty($order['delivery_address']);

// Parse delivery_address text if structured not available
$parsedAddr = [];
if (!$hasStructured && $hasText) {
    $parts = array_map('trim', explode(',', $order['delivery_address']));
    $parsedAddr = $parts;
}

// Customer stats (enriched by orders-helper)
$custTotalOrders = (int)($order['cust_total_orders']  ?? 0);
$custTotalSpent  = (float)($order['cust_total_spent'] ?? 0);
$custLatestOrder = $order['cust_latest_order']        ?? '';
$custSince       = $order['customer_since']           ?? '';
$custLastLogin   = $order['customer_last_login']      ?? '';
$custDob         = $order['customer_dob']             ?? '';
$custGender      = $order['customer_gender']          ?? '';
$custStatus      = $order['customer_status']          ?? '';

$page_title  = "Order #$id — CloudCush Admin";
$active_page = 'orders';

include __DIR__ . '/../includes/header.php';
?>

<input type="hidden" id="orderStatusHandlerUrl" value="<?= BASE_URL ?>orders/view.php">

<div id="wrapper">
    <?php include __DIR__ . '/../includes/sidebar.php'; ?>

    <div id="page-content-wrapper">
        <?php include __DIR__ . '/../includes/navbar.php'; ?>

        <div class="container-fluid px-0 py-2">

            <?php include __DIR__ . '/../includes/alerts.php'; ?>

            <!-- ── Page Header ──────────────────────────────── -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
                <div class="d-flex align-items-center gap-3">
                    <a href="<?= BASE_URL ?>orders/"
                       class="btn-action btn-action-view btn-back-square" title="Back to orders">
                        <i data-lucide="arrow-left" class="icon-sm"></i>
                    </a>
                    <div>
                        <h1 class="h4 fw-bold mb-1 page-heading">Order #<?= $order['id'] ?></h1>
                        <p class="text-secondary mb-0 fs-0-82">
                            Placed on <?= date('F j, Y \a\t g:i A', strtotime($order['created_at'])) ?>
                        </p>
                    </div>
                </div>
                <span class="badge-status <?= $badge['class'] ?>" style="font-size:0.78rem;padding:.35rem .75rem;">
                    <i data-lucide="<?= $badge['icon'] ?>" class="icon-xs"></i>
                    <?= $badge['label'] ?>
                </span>
            </div>

            <!-- ── Two-column layout ─────────────────────────── -->
            <div class="row g-3 align-items-start">

                <!-- LEFT ────────────────────────────────────── -->
                <div class="col-12 col-xl-8">

                    <!-- Ordered Items -->
                    <div class="card-premium mb-3">
                        <div class="card-premium-header mb-3">
                            <span class="card-premium-title d-flex align-items-center gap-2">
                                <i data-lucide="package" class="icon-md text-primary"></i>
                                Ordered Items
                            </span>
                            <span class="tech-data fs-0-75 text-muted">
                                <?= count($items) ?> product<?= count($items) !== 1 ? 's' : '' ?>
                            </span>
                        </div>

                        <?php if (empty($items)): ?>
                            <div class="text-center py-3">
                                <i data-lucide="package-x" class="icon-xxl text-muted mb-2" style="width:32px;height:32px;opacity:.4;"></i>
                                <p class="text-muted fs-0-82 mb-0">No item data recorded for this order.</p>
                            </div>
                        <?php else: ?>
                            <div class="order-items-list">
                                <?php foreach ($items as $item):
                                    $img       = !empty($item['image'])          ? $item['image']         : '';
                                    $name      = !empty($item['name'])           ? $item['name']          : 'Product';
                                    $size      = !empty($item['size'])           ? $item['size']          : '';
                                    $qty       = (int)($item['quantity']         ?? 1);
                                    $price     = (float)($item['price']          ?? 0);
                                    $lineTotal = (float)($item['line_total']     ?? ($price * $qty));
                                    $origPrice = !empty($item['original_price']) ? (float)$item['original_price'] : null;
                                ?>
                                <div class="order-item-row">
                                    <div class="tbl-thumb flex-shrink-0" style="width:52px;height:52px;">
                                        <?php if ($img): ?>
                                            <img src="<?= htmlspecialchars($img) ?>"
                                                 alt="<?= htmlspecialchars($name) ?>" loading="lazy">
                                        <?php else: ?>
                                            <div class="d-flex align-items-center justify-content-center w-100 h-100 tbl-thumb-placeholder">
                                                <i data-lucide="image" class="icon-md"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="order-item-info flex-grow-1 min-width-0">
                                        <div class="fw-semibold fs-0-88 text-dark text-ellipsis-100">
                                            <?= htmlspecialchars($name) ?>
                                        </div>
                                        <?php if ($size): ?>
                                            <div class="tech-data fs-0-74 text-muted">
                                                Size: <strong><?= htmlspecialchars($size) ?></strong>
                                            </div>
                                        <?php endif; ?>
                                        <div class="tech-data fs-0-74 text-muted">
                                            Qty: <?= $qty ?>
                                            &nbsp;&middot;&nbsp;
                                            <?php if ($origPrice && $origPrice > $price): ?>
                                                <span class="text-decoration-line-through">₹<?= number_format($origPrice, 2) ?></span>&nbsp;
                                            <?php endif; ?>
                                            ₹<?= number_format($price, 2) ?> each
                                        </div>
                                    </div>
                                    <div class="tech-data fw-bold text-dark flex-shrink-0 fs-0-88">
                                        ₹<?= number_format($lineTotal, 2) ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- Pricing summary -->
                            <div class="order-price-summary mt-3 pt-3">
                                <?php if ($subtotal > 0): ?>
                                <div class="order-price-row">
                                    <span class="fs-0-82 text-secondary">Subtotal</span>
                                    <span class="tech-data fs-0-82 text-dark">₹<?= number_format($subtotal, 2) ?></span>
                                </div>
                                <?php endif; ?>
                                <div class="order-price-row">
                                    <span class="fs-0-82 text-secondary">Shipping</span>
                                    <span class="tech-data fs-0-82 <?= $shipping > 0 ? 'text-dark' : 'text-success' ?>">
                                        <?= $shipping > 0 ? '₹' . number_format($shipping, 2) : 'Free' ?>
                                    </span>
                                </div>
                                <?php if ($discount > 0): ?>
                                <div class="order-price-row">
                                    <span class="fs-0-82 text-secondary">
                                        Discount<?= !empty($order['promo_code']) ? ' (' . htmlspecialchars($order['promo_code']) . ')' : '' ?>
                                    </span>
                                    <span class="tech-data fs-0-82 text-success">−₹<?= number_format($discount, 2) ?></span>
                                </div>
                                <?php endif; ?>
                                <div class="order-price-row order-price-total">
                                    <span class="fw-bold">Total</span>
                                    <span class="tech-data fw-bold fs-5">₹<?= number_format($total, 2) ?></span>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Delivery Address -->
                    <div class="card-premium">
                        <div class="card-premium-header mb-3">
                            <span class="card-premium-title d-flex align-items-center gap-2">
                                <i data-lucide="map-pin" class="icon-md text-primary"></i>
                                Delivery Address
                            </span>
                        </div>

                        <?php if ($hasStructured): ?>
                            <div class="address-detail-block">
                                <?php if (!empty($order['addr_label'])): ?>
                                    <div class="tech-data fs-0-72 text-muted text-uppercase mb-1 fw-bold">
                                        <?= htmlspecialchars($order['addr_label']) ?>
                                    </div>
                                <?php endif; ?>
                                <div class="fw-semibold fs-0-88 text-dark mb-1">
                                    <?= htmlspecialchars($order['addr_full_name'] ?: $order['customer_name']) ?>
                                    <?php $addrPhone = $order['addr_phone'] ?: $order['customer_phone']; ?>
                                    <?php if ($addrPhone): ?>
                                        <span class="text-muted fw-normal">&middot; <?= htmlspecialchars($addrPhone) ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="fs-0-82 text-secondary lh-1-7">
                                    <?= htmlspecialchars($order['address_line_1']) ?>
                                    <?php if (!empty($order['address_line_2'])): ?>
                                        <br><?= htmlspecialchars($order['address_line_2']) ?>
                                    <?php endif; ?>
                                    <br><?= htmlspecialchars($order['city'] . ', ' . $order['state']) ?>
                                    <br><?= htmlspecialchars($order['country'] ?? 'India') ?>
                                    <?php if (!empty($order['zip_code'])): ?>
                                        &mdash; <?= htmlspecialchars($order['zip_code']) ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php elseif ($hasText): ?>
                            <p class="fs-0-82 text-secondary lh-1-7 mb-0">
                                <?= nl2br(htmlspecialchars($order['delivery_address'])) ?>
                            </p>
                        <?php else: ?>
                            <div class="text-center py-2">
                                <p class="text-muted fs-0-82 mb-0">No delivery address recorded for this order.</p>
                            </div>
                        <?php endif; ?>
                    </div>

                </div><!-- /col left -->

                <!-- RIGHT ───────────────────────────────────── -->
                <div class="col-12 col-xl-4">

                    <!-- ════════════════════════════════════════
                         CUSTOMER INFO CARD (ENRICHED)
                         ════════════════════════════════════════ -->
                    <div class="card-premium mb-3">
                        <div class="card-premium-header mb-3">
                            <span class="card-premium-title d-flex align-items-center gap-2">
                                <i data-lucide="user" class="icon-md text-primary"></i>
                                Customer
                            </span>
                            <?php if (!empty($order['customer_id'])): ?>
                                <a href="<?= BASE_URL ?>customers/view.php?id=<?= (int)$order['customer_id'] ?>"
                                   class="edit-link-custom">View Profile →</a>
                            <?php endif; ?>
                        </div>

                        <!-- Avatar + name + status -->
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="customer-avatar-sm" style="width:44px;height:44px;font-size:1rem;flex-shrink:0;">
                                <?= strtoupper(mb_substr($order['customer_name'] ?: '?', 0, 1)) ?>
                            </div>
                            <div class="min-width-0">
                                <div class="fw-semibold fs-0-88 text-dark text-ellipsis-100">
                                    <?= htmlspecialchars($order['customer_name'] ?: '—') ?>
                                </div>
                                <div class="d-flex align-items-center gap-2 mt-1 flex-wrap">
                                    <?php if ($custStatus): ?>
                                        <span class="badge-status <?= $custStatus === 'active' ? 'success' : 'secondary' ?>" style="font-size:0.62rem;">
                                            <?= ucfirst($custStatus) ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if ($custGender): ?>
                                        <span class="tech-data fs-0-7 text-muted text-capitalize"><?= htmlspecialchars($custGender) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Lifetime Stats mini-row -->
                        <?php if ($custTotalOrders > 0): ?>
                        <div class="cust-stats-row mb-3">
                            <div class="cust-stat-item">
                                <div class="cust-stat-value"><?= $custTotalOrders ?></div>
                                <div class="cust-stat-label">Orders</div>
                            </div>
                            <div class="cust-stat-divider"></div>
                            <div class="cust-stat-item">
                                <div class="cust-stat-value">₹<?= number_format($custTotalSpent, 0) ?></div>
                                <div class="cust-stat-label">Lifetime Spent</div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Info rows -->
                        <div class="customer-info-list">
                            <div class="customer-info-row">
                                <div class="customer-info-label"><i data-lucide="mail" class="icon-xs"></i> Email</div>
                                <div class="customer-info-value" style="font-size:0.76rem;word-break:break-all;">
                                    <?= htmlspecialchars($order['customer_email'] ?: '—') ?>
                                </div>
                            </div>

                            <?php if (!empty($order['customer_phone'])): ?>
                            <div class="customer-info-row">
                                <div class="customer-info-label"><i data-lucide="phone" class="icon-xs"></i> Phone</div>
                                <div class="customer-info-value"><?= htmlspecialchars($order['customer_phone']) ?></div>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($custDob)): ?>
                            <div class="customer-info-row">
                                <div class="customer-info-label"><i data-lucide="cake" class="icon-xs"></i> Birthday</div>
                                <div class="customer-info-value tech-data fs-0-75">
                                    <?= date('M j, Y', strtotime($custDob)) ?>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($custSince)): ?>
                            <div class="customer-info-row">
                                <div class="customer-info-label"><i data-lucide="calendar" class="icon-xs"></i> Joined</div>
                                <div class="customer-info-value tech-data fs-0-75">
                                    <?= date('M j, Y', strtotime($custSince)) ?>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($custLastLogin)): ?>
                            <div class="customer-info-row">
                                <div class="customer-info-label"><i data-lucide="log-in" class="icon-xs"></i> Last Login</div>
                                <div class="customer-info-value tech-data fs-0-74">
                                    <?= date('M j, Y g:i A', strtotime($custLastLogin)) ?>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($custLatestOrder)): ?>
                            <div class="customer-info-row">
                                <div class="customer-info-label"><i data-lucide="shopping-bag" class="icon-xs"></i> Latest Order</div>
                                <div class="customer-info-value tech-data fs-0-74">
                                    <?= date('M j, Y', strtotime($custLatestOrder)) ?>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php
                            // Show primary saved address (city/state/pincode) if available
                            $addrCity  = $order['city']     ?? '';
                            $addrState = $order['state']    ?? '';
                            $addrZip   = $order['zip_code'] ?? '';
                            if ($addrCity || $addrState || $addrZip):
                            ?>
                            <div class="customer-info-row">
                                <div class="customer-info-label"><i data-lucide="map-pin" class="icon-xs"></i> Location</div>
                                <div class="customer-info-value fs-0-76">
                                    <?php
                                    $parts = array_filter([$addrCity, $addrState, $addrZip]);
                                    echo htmlspecialchars(implode(', ', $parts));
                                    ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Order Summary -->
                    <div class="card-premium mb-3">
                        <div class="card-premium-header mb-3">
                            <span class="card-premium-title d-flex align-items-center gap-2">
                                <i data-lucide="receipt" class="icon-md text-primary"></i>
                                Order Summary
                            </span>
                        </div>
                        <div class="customer-info-list">
                            <div class="customer-info-row">
                                <div class="customer-info-label"><i data-lucide="hash" class="icon-xs"></i> Order ID</div>
                                <div class="customer-info-value tech-data fw-bold">#<?= $order['id'] ?></div>
                            </div>
                            <div class="customer-info-row">
                                <div class="customer-info-label"><i data-lucide="calendar" class="icon-xs"></i> Date</div>
                                <div class="customer-info-value tech-data fs-0-75">
                                    <?= date('M j, Y', strtotime($order['created_at'])) ?>
                                </div>
                            </div>
                            <div class="customer-info-row">
                                <div class="customer-info-label"><i data-lucide="package" class="icon-xs"></i> Items</div>
                                <div class="customer-info-value tech-data">
                                    <?= count($items) ?: ($order['item_count'] ?? '—') ?>
                                </div>
                            </div>
                            <div class="customer-info-row">
                                <div class="customer-info-label"><i data-lucide="indian-rupee" class="icon-xs"></i> Total</div>
                                <div class="customer-info-value tech-data fw-bold">
                                    ₹<?= number_format($total, 2) ?>
                                </div>
                            </div>
                            <div class="customer-info-row">
                                <div class="customer-info-label"><i data-lucide="credit-card" class="icon-xs"></i> Payment</div>
                                <div class="customer-info-value">
                                    <span class="badge-status <?= $payBadge['class'] ?>"><?= $payBadge['label'] ?></span>
                                    <?php if (!empty($order['payment_method'])): ?>
                                        <span class="tech-data fs-0-68 text-muted ms-1 text-uppercase">
                                            <?= htmlspecialchars($order['payment_method']) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php if ($discount > 0): ?>
                            <div class="customer-info-row">
                                <div class="customer-info-label"><i data-lucide="tag" class="icon-xs"></i> Discount</div>
                                <div class="customer-info-value tech-data text-success">
                                    −₹<?= number_format($discount, 2) ?>
                                    <?php if (!empty($order['promo_code'])): ?>
                                        <span class="badge-variant-count ms-1"><?= htmlspecialchars($order['promo_code']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($order['updated_at'])): ?>
                            <div class="customer-info-row">
                                <div class="customer-info-label"><i data-lucide="refresh-cw" class="icon-xs"></i> Updated</div>
                                <div class="customer-info-value tech-data fs-0-74">
                                    <?= date('M j, Y g:i A', strtotime($order['updated_at'])) ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- ════════════════════════════════════════
                         UPDATE STATUS — Redesigned
                         ════════════════════════════════════════ -->
                    <div class="card-premium">
                        <div class="card-premium-header mb-1">
                            <span class="card-premium-title d-flex align-items-center gap-2">
                                <i data-lucide="activity" class="icon-md text-primary"></i>
                                Update Status
                            </span>
                            <span class="badge-status <?= $badge['class'] ?>" style="font-size:0.62rem;padding:.2rem .55rem;">
                                <?= $badge['label'] ?>
                            </span>
                        </div>

                        <!-- Current status hint -->
                        <p class="fs-0-75 text-muted mb-3" style="line-height:1.45;">
                            Select a status below to update this order immediately.
                        </p>

                        <!-- Status button grid -->
                        <div class="order-status-grid">
                            <?php foreach ($statuses as $sKey => $sCfg):
                                $isCurrent = $currentStatus === $sKey;
                                $isCancelled = $sKey === 'cancelled';
                            ?>
                            <form method="POST" action="" class="order-status-form-item">
                                <input type="hidden" name="action" value="update_status">
                                <input type="hidden" name="id"     value="<?= $order['id'] ?>">
                                <input type="hidden" name="status" value="<?= $sKey ?>">
                                <button
                                    type="<?= $isCurrent ? 'button' : 'submit' ?>"
                                    class="order-status-chip <?= $isCurrent ? 'is-current' : '' ?> <?= $isCancelled ? 'is-cancel' : '' ?>"
                                    style="--chip-color:<?= $sCfg['color'] ?>;--chip-bg:<?= $sCfg['bg'] ?>;"
                                    <?= $isCurrent ? 'disabled aria-disabled="true"' : '' ?>
                                    title="<?= $isCurrent ? 'Current status' : 'Set to ' . $sCfg['label'] ?>">
                                    <span class="order-status-chip-icon">
                                        <i data-lucide="<?= $sCfg['icon'] ?>"></i>
                                    </span>
                                    <span class="order-status-chip-label"><?= $sCfg['label'] ?></span>
                                    <?php if ($isCurrent): ?>
                                        <span class="order-status-chip-check">
                                            <i data-lucide="check"></i>
                                        </span>
                                    <?php endif; ?>
                                </button>
                            </form>
                            <?php endforeach; ?>
                        </div>

                        <!-- Caution for destructive action -->
                        <?php if ($currentStatus !== 'cancelled' && $currentStatus !== 'delivered'): ?>
                        <p class="fs-0-72 text-muted mt-3 mb-0" style="line-height:1.5;opacity:0.7;">
                            <i data-lucide="info" style="width:10px;height:10px;vertical-align:middle;margin-right:3px;"></i>
                            Cancelling an order cannot be undone. Confirm before proceeding.
                        </p>
                        <?php endif; ?>
                    </div>

                </div><!-- /col right -->
            </div><!-- /row -->

        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
