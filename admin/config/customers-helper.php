<?php
// admin/config/customers-helper.php
// Complete customer utility functions for admin management

require_once __DIR__ . '/database.php';

/* ============================================================
   SCHEMA — ensure all columns exist
   ============================================================ */
function ensureCustomersTableAdmin(): void {
    $db = getDBConnection();

    $db->exec("
        CREATE TABLE IF NOT EXISTS `customers` (
            `id`            INT(11)      NOT NULL AUTO_INCREMENT,
            `full_name`     VARCHAR(150) NOT NULL,
            `email`         VARCHAR(150) NOT NULL,
            `phone`         VARCHAR(20)  DEFAULT NULL,
            `gender`        ENUM('male','female','other') DEFAULT NULL,
            `date_of_birth` DATE         DEFAULT NULL,
            `profile_photo` VARCHAR(255) DEFAULT NULL,
            `password`      VARCHAR(255) NOT NULL,
            `status`        ENUM('active','inactive') NOT NULL DEFAULT 'active',
            `address`       TEXT         DEFAULT NULL,
            `created_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `last_login`    TIMESTAMP    NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `email` (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // Idempotent column additions for older installs
    $existing = [];
    foreach ($db->query("SHOW COLUMNS FROM `customers`")->fetchAll() as $row) {
        $existing[] = $row['Field'];
    }

    $add = [
        'status'        => "ALTER TABLE `customers` ADD COLUMN `status` ENUM('active','inactive') NOT NULL DEFAULT 'active' AFTER `password`",
        'address'       => "ALTER TABLE `customers` ADD COLUMN `address` TEXT DEFAULT NULL AFTER `status`",
        'last_login'    => "ALTER TABLE `customers` ADD COLUMN `last_login` TIMESTAMP NULL DEFAULT NULL AFTER `created_at`",
        'gender'        => "ALTER TABLE `customers` ADD COLUMN `gender` ENUM('male','female','other') DEFAULT NULL AFTER `phone`",
        'date_of_birth' => "ALTER TABLE `customers` ADD COLUMN `date_of_birth` DATE DEFAULT NULL AFTER `gender`",
        'profile_photo' => "ALTER TABLE `customers` ADD COLUMN `profile_photo` VARCHAR(255) DEFAULT NULL AFTER `date_of_birth`",
    ];

    foreach ($add as $col => $sql) {
        if (!in_array($col, $existing)) {
            try { $db->exec($sql); } catch (\PDOException $e) { /* already exists */ }
        }
    }
}

ensureCustomersTableAdmin();

/* ============================================================
   LISTING — with order counts
   ============================================================ */
function getCustomers(array $params = []): array {
    $db = getDBConnection();

    $search  = trim($params['search'] ?? '');
    $status  = trim($params['status'] ?? '');
    $page    = max(1, (int)($params['page']     ?? 1));
    $perPage = max(1, (int)($params['per_page'] ?? 15));
    $offset  = ($page - 1) * $perPage;

    $conditions = ['1=1'];
    $bindings   = [];

    if ($search !== '') {
        $conditions[] = '(c.full_name LIKE :search OR c.email LIKE :search OR c.phone LIKE :search)';
        $bindings['search'] = '%' . $search . '%';
    }
    if ($status !== '') {
        $conditions[] = 'c.status = :status';
        $bindings['status'] = $status;
    }

    $where = implode(' AND ', $conditions);

    $countStmt = $db->prepare("SELECT COUNT(*) FROM `customers` c WHERE $where");
    $countStmt->execute($bindings);
    $total = (int)$countStmt->fetchColumn();

    $stmt = $db->prepare("
        SELECT
            c.*,
            COUNT(o.id)                         AS total_orders,
            COALESCE(SUM(o.total_amount), 0)    AS total_spent,
            MAX(o.created_at)                   AS latest_order_date
        FROM `customers` c
        LEFT JOIN `orders` o ON o.customer_id = c.id
        WHERE $where
        GROUP BY c.id
        ORDER BY c.created_at DESC
        LIMIT :limit OFFSET :offset
    ");

    foreach ($bindings as $k => $v) {
        $stmt->bindValue($k, $v, PDO::PARAM_STR);
    }
    $stmt->bindValue(':limit',  $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset,  PDO::PARAM_INT);
    $stmt->execute();
    $customers = $stmt->fetchAll();

    return [
        'data'        => $customers,
        'total'       => $total,
        'page'        => $page,
        'per_page'    => $perPage,
        'total_pages' => $total > 0 ? (int)ceil($total / $perPage) : 1,
    ];
}

/* ============================================================
   SINGLE CUSTOMER — with order stats
   ============================================================ */
function getCustomerById(int $id): ?array {
    $db   = getDBConnection();
    $stmt = $db->prepare("
        SELECT
            c.*,
            COUNT(o.id)                         AS total_orders,
            COALESCE(SUM(o.total_amount), 0)    AS total_spent,
            MAX(o.created_at)                   AS latest_order_date,
            MAX(o.id)                           AS latest_order_id
        FROM `customers` c
        LEFT JOIN `orders` o ON o.customer_id = c.id
        WHERE c.id = :id
        GROUP BY c.id
        LIMIT 1
    ");
    $stmt->execute([':id' => $id]);
    return $stmt->fetch() ?: null;
}

/* ============================================================
   CUSTOMER ORDERS — for detail page order history
   ============================================================ */
function getCustomerOrders(int $customerId, int $limit = 10): array {
    $db   = getDBConnection();
    $stmt = $db->prepare("
        SELECT id, item_count, total_amount, payment_method, payment_status, status, created_at
        FROM `orders`
        WHERE customer_id = :cid
        ORDER BY id DESC
        LIMIT :lim
    ");
    $stmt->bindValue(':cid', $customerId, PDO::PARAM_INT);
    $stmt->bindValue(':lim', $limit,      PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

/* ============================================================
   CUSTOMER ADDRESSES
   ============================================================ */
function getCustomerAddresses(int $customerId): array {
    $db   = getDBConnection();
    $stmt = $db->prepare("
        SELECT *
        FROM `customer_addresses`
        WHERE customer_id = :cid
        ORDER BY is_default DESC, id ASC
    ");
    $stmt->execute([':cid' => $customerId]);
    return $stmt->fetchAll();
}

/* ============================================================
   STATUS UPDATE
   ============================================================ */
function updateCustomerStatus(int $id, string $status): bool {
    if (!in_array($status, ['active', 'inactive'])) return false;
    $db   = getDBConnection();
    $stmt = $db->prepare("UPDATE `customers` SET status = :status WHERE id = :id");
    $stmt->execute([':status' => $status, ':id' => $id]);
    return $stmt->rowCount() > 0;
}

/* ============================================================
   UPDATE CUSTOMER
   ============================================================ */
function updateCustomer(int $id, array $data): bool {
    $db     = getDBConnection();
    $fields = [];
    $bind   = ['id' => $id];

    $allowed = ['full_name', 'email', 'phone', 'status', 'address', 'gender', 'date_of_birth'];
    foreach ($allowed as $field) {
        if (array_key_exists($field, $data)) {
            $fields[] = "`$field` = :$field";
            $bind[$field] = $data[$field] !== '' ? $data[$field] : null;
        }
    }

    if (empty($fields)) return false;

    $sql  = "UPDATE `customers` SET " . implode(', ', $fields) . " WHERE id = :id";
    $stmt = $db->prepare($sql);
    $stmt->execute($bind);
    return true;
}

/* ============================================================
   DELETE CUSTOMER
   ============================================================ */
function deleteCustomer(int $id): bool {
    $db   = getDBConnection();
    $stmt = $db->prepare("DELETE FROM `customers` WHERE id = :id");
    $stmt->execute([':id' => $id]);
    return $stmt->rowCount() > 0;
}

/* ============================================================
   BADGES
   ============================================================ */
function getCustomerStatusBadge(string $status): array {
    return match ($status) {
        'active'   => ['label' => 'Active',   'class' => 'success'],
        'inactive' => ['label' => 'Inactive', 'class' => 'secondary'],
        default    => ['label' => ucfirst($status ?: 'unknown'), 'class' => 'secondary'],
    };
}

/* ============================================================
   STATS
   ============================================================ */
function getCustomerStats(): array {
    $db       = getDBConnection();
    $total    = (int)$db->query("SELECT COUNT(*) FROM `customers`")->fetchColumn();
    $active   = (int)$db->query("SELECT COUNT(*) FROM `customers` WHERE status = 'active'")->fetchColumn();
    $inactive = (int)$db->query("SELECT COUNT(*) FROM `customers` WHERE status = 'inactive'")->fetchColumn();
    $newMonth = (int)$db->query("SELECT COUNT(*) FROM `customers` WHERE created_at >= DATE_FORMAT(NOW(),'%Y-%m-01')")->fetchColumn();

    return compact('total', 'active', 'inactive', 'newMonth');
}
