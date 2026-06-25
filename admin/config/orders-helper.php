<?php
// admin/config/orders-helper.php
// Complete order utility functions for admin order management

require_once __DIR__ . '/database.php';

/* ============================================================
   ORDER STATUS CONFIG
   ============================================================ */
function getOrderStatuses(): array {
    return [
        'pending'    => ['label' => 'Pending',    'class' => 'warning',   'icon' => 'clock',         'color' => '#d97706', 'bg' => '#fef3c7'],
        'confirmed'  => ['label' => 'Confirmed',  'class' => 'info',      'icon' => 'check-circle',  'color' => '#0369a1', 'bg' => '#e0f2fe'],
        'processing' => ['label' => 'Processing', 'class' => 'info',      'icon' => 'loader',        'color' => '#7c3aed', 'bg' => '#ede9fe'],
        'shipped'    => ['label' => 'Shipped',    'class' => 'primary',   'icon' => 'truck',         'color' => '#1d4ed8', 'bg' => '#dbeafe'],
        'delivered'  => ['label' => 'Delivered',  'class' => 'success',   'icon' => 'package-check', 'color' => '#065f46', 'bg' => '#d1fae5'],
        'cancelled'  => ['label' => 'Cancelled',  'class' => 'danger',    'icon' => 'x-circle',      'color' => '#991b1b', 'bg' => '#fee2e2'],
    ];
}

function getOrderStatusBadge(string $status): array {
    $map = getOrderStatuses();
    return $map[$status] ?? ['label' => ucfirst($status ?: 'unknown'), 'class' => 'secondary', 'icon' => 'circle', 'color' => '#64748b', 'bg' => '#f1f5f9'];
}

function getPaymentStatusBadge(string $status): array {
    return match ($status) {
        'paid'     => ['label' => 'Paid',     'class' => 'success'],
        'failed'   => ['label' => 'Failed',   'class' => 'danger'],
        'refunded' => ['label' => 'Refunded', 'class' => 'secondary'],
        default    => ['label' => 'Pending',  'class' => 'warning'],
    };
}

/* ============================================================
   LISTING — search, filter, paginate
   ============================================================ */
function getOrders(array $params = []): array {
    $db = getDBConnection();

    $search  = trim($params['search']   ?? '');
    $status  = trim($params['status']   ?? '');
    $page    = max(1, (int)($params['page']     ?? 1));
    $perPage = max(1, (int)($params['per_page'] ?? 15));
    $offset  = ($page - 1) * $perPage;

    $conditions = ['1=1'];
    $bindings   = [];

    if ($search !== '') {
        if (ctype_digit($search)) {
            $conditions[] = 'o.id = :search_id';
            $bindings['search_id'] = (int)$search;
        } else {
            $conditions[] = '(o.customer_name LIKE :search OR o.customer_email LIKE :search)';
            $bindings['search'] = '%' . $search . '%';
        }
    }

    if ($status !== '') {
        $conditions[] = 'o.status = :status';
        $bindings['status'] = $status;
    }

    $where = implode(' AND ', $conditions);

    $countStmt = $db->prepare("SELECT COUNT(*) FROM `orders` o WHERE $where");
    $countStmt->execute($bindings);
    $total = (int)$countStmt->fetchColumn();

    $stmt = $db->prepare("
        SELECT
            o.id,
            o.customer_id,
            o.customer_name,
            o.customer_email,
            o.order_items,
            o.item_count,
            o.payment_method,
            o.payment_status,
            o.subtotal,
            o.shipping_amount,
            o.discount_amount,
            o.promo_code,
            o.total_amount,
            o.status,
            o.created_at,
            o.updated_at,
            COALESCE(c.phone, '') AS customer_phone
        FROM `orders` o
        LEFT JOIN `customers` c ON c.id = o.customer_id
        WHERE $where
        ORDER BY o.id DESC
        LIMIT :limit OFFSET :offset
    ");

    foreach ($bindings as $k => $v) {
        $stmt->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    $stmt->bindValue(':limit',  $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset,  PDO::PARAM_INT);
    $stmt->execute();
    $orders = $stmt->fetchAll();

    return [
        'data'        => $orders,
        'total'       => $total,
        'page'        => $page,
        'per_page'    => $perPage,
        'total_pages' => $total > 0 ? (int)ceil($total / $perPage) : 1,
    ];
}

/* ============================================================
   SINGLE ORDER — with full address + enriched customer details
   ============================================================ */
function getOrderById(int $id): ?array {
    $db = getDBConnection();

    // Main order + customer + address JOIN
    $stmt = $db->prepare("
        SELECT
            o.*,
            -- Customer profile fields
            COALESCE(c.full_name,     '')    AS cust_full_name,
            COALESCE(c.phone,         '')    AS customer_phone,
            COALESCE(c.status,        '')    AS customer_status,
            COALESCE(c.gender,        '')    AS customer_gender,
            COALESCE(c.date_of_birth, '')    AS customer_dob,
            COALESCE(c.created_at,    '')    AS customer_since,
            COALESCE(c.last_login,    '')    AS customer_last_login,
            COALESCE(c.address,       '')    AS customer_address_text,
            -- Address record fields
            ca.label                         AS addr_label,
            ca.full_name                     AS addr_full_name,
            ca.phone                         AS addr_phone,
            ca.address_line_1,
            ca.address_line_2,
            ca.city,
            ca.state,
            ca.country,
            ca.zip_code
        FROM `orders` o
        LEFT JOIN `customers`          c  ON c.id  = o.customer_id
        LEFT JOIN `customer_addresses` ca ON ca.id = o.address_id
        WHERE o.id = :id
        LIMIT 1
    ");
    $stmt->execute([':id' => $id]);
    $order = $stmt->fetch();
    if (!$order) return null;

    // Merge cust_full_name into customer_name if empty
    if (empty($order['customer_name']) && !empty($order['cust_full_name'])) {
        $order['customer_name'] = $order['cust_full_name'];
    }

    // Fetch customer lifetime stats (total orders + total spent) if customer_id exists
    if (!empty($order['customer_id'])) {
        $statsStmt = $db->prepare("
            SELECT
                COUNT(*)                          AS total_orders,
                COALESCE(SUM(total_amount), 0)    AS total_spent,
                MAX(created_at)                   AS latest_order_date
            FROM `orders`
            WHERE customer_id = :cid
        ");
        $statsStmt->execute([':cid' => (int)$order['customer_id']]);
        $stats = $statsStmt->fetch();
        $order['cust_total_orders']    = (int)($stats['total_orders']   ?? 0);
        $order['cust_total_spent']     = (float)($stats['total_spent']  ?? 0);
        $order['cust_latest_order']    = $stats['latest_order_date']    ?? '';

        // Also fetch default/primary saved address for customer if not already loaded
        if (empty($order['address_line_1'])) {
            $addrStmt = $db->prepare("
                SELECT full_name, phone, address_line_1, address_line_2,
                       city, state, country, zip_code, label
                FROM `customer_addresses`
                WHERE customer_id = :cid
                ORDER BY is_default DESC, id DESC
                LIMIT 1
            ");
            $addrStmt->execute([':cid' => (int)$order['customer_id']]);
            $primaryAddr = $addrStmt->fetch();
            if ($primaryAddr) {
                // Only fill in if the order didn't have a linked address already
                foreach (['address_line_1','address_line_2','city','state','country','zip_code','label','full_name','phone'] as $field) {
                    if (empty($order[$field]) && !empty($primaryAddr[$field])) {
                        $order['addr_' . $field] = $primaryAddr[$field];
                        if (in_array($field, ['address_line_1','address_line_2','city','state','country','zip_code'])) {
                            $order[$field] = $primaryAddr[$field];
                        }
                    }
                }
                if (empty($order['addr_label']))     $order['addr_label']     = $primaryAddr['label']     ?? '';
                if (empty($order['addr_full_name'])) $order['addr_full_name'] = $primaryAddr['full_name'] ?? '';
                if (empty($order['addr_phone']))     $order['addr_phone']     = $primaryAddr['phone']     ?? '';
            }
        }
    } else {
        $order['cust_total_orders'] = 0;
        $order['cust_total_spent']  = 0;
        $order['cust_latest_order'] = '';
    }

    // Decode JSON order_items
    $decoded = [];
    if (!empty($order['order_items'])) {
        $try = json_decode($order['order_items'], true);
        $decoded = is_array($try) ? $try : [];
    }
    $order['items_decoded'] = $decoded;

    return $order;
}

/* ============================================================
   STATUS UPDATE
   ============================================================ */
function updateOrderStatus(int $id, string $status): bool {
    $allowed = array_keys(getOrderStatuses());
    if (!in_array($status, $allowed, true)) return false;

    $db   = getDBConnection();
    $stmt = $db->prepare("UPDATE `orders` SET status = :status WHERE id = :id");
    $stmt->execute([':status' => $status, ':id' => $id]);
    return $stmt->rowCount() > 0;
}

/* ============================================================
   STATS
   ============================================================ */
function getOrderStats(): array {
    $db = getDBConnection();

    $total     = (int)$db->query("SELECT COUNT(*) FROM `orders`")->fetchColumn();
    $pending   = (int)$db->query("SELECT COUNT(*) FROM `orders` WHERE status = 'pending'")->fetchColumn();
    $delivered = (int)$db->query("SELECT COUNT(*) FROM `orders` WHERE status = 'delivered'")->fetchColumn();
    $newMonth  = (int)$db->query("SELECT COUNT(*) FROM `orders` WHERE created_at >= DATE_FORMAT(NOW(),'%Y-%m-01')")->fetchColumn();

    $rev     = $db->query("SELECT COALESCE(SUM(total_amount),0) FROM `orders` WHERE status = 'delivered'")->fetchColumn();
    $revenue = number_format((float)$rev, 2);

    return compact('total', 'pending', 'delivered', 'revenue', 'newMonth');
}

/* ============================================================
   PARSE PRODUCT ITEMS FROM ORDER
   ============================================================ */
function parseOrderItems(array $order): array {
    if (!empty($order['items_decoded']) && is_array($order['items_decoded'])) {
        return $order['items_decoded'];
    }

    // Fallback: try re-decoding
    if (!empty($order['order_items'])) {
        $try = json_decode($order['order_items'], true);
        if (is_array($try) && !empty($try)) return $try;
    }

    return [];
}
