<?php
// admin/config/dashboard-helper.php
// All live database queries for the admin dashboard.

require_once __DIR__ . '/database.php';

/* ============================================================
   OVERVIEW CARDS
   ============================================================ */
function getDashboardOverview(): array {
    $db = getDBConnection();

    $totalProducts    = (int) $db->query("SELECT COUNT(*) FROM `products`")->fetchColumn();
    $totalOrders      = (int) $db->query("SELECT COUNT(*) FROM `orders`")->fetchColumn();
    $totalCustomers   = (int) $db->query("SELECT COUNT(*) FROM `customers`")->fetchColumn();
    $totalCategories  = (int) $db->query("SELECT COUNT(DISTINCT category) FROM `products` WHERE category IS NOT NULL AND category != ''")->fetchColumn();

    $pendingOrders    = (int) $db->query("SELECT COUNT(*) FROM `orders` WHERE status = 'pending'")->fetchColumn();
    $processingOrders = (int) $db->query("SELECT COUNT(*) FROM `orders` WHERE status = 'processing'")->fetchColumn();
    $completedOrders  = (int) $db->query("SELECT COUNT(*) FROM `orders` WHERE status IN ('delivered','completed')")->fetchColumn();
    $cancelledOrders  = (int) $db->query("SELECT COUNT(*) FROM `orders` WHERE status = 'cancelled'")->fetchColumn();

    return compact(
        'totalProducts', 'totalOrders', 'totalCustomers', 'totalCategories',
        'pendingOrders', 'processingOrders', 'completedOrders', 'cancelledOrders'
    );
}

/* ============================================================
   PRODUCTS SECTION
   ============================================================ */
function getDashboardProductStats(): array {
    $db = getDBConnection();

    $total    = (int) $db->query("SELECT COUNT(*) FROM `products`")->fetchColumn();
    $active   = (int) $db->query("SELECT COUNT(*) FROM `products` WHERE status = 'active'")->fetchColumn();
    $inactive = (int) $db->query("SELECT COUNT(*) FROM `products` WHERE status != 'active'")->fetchColumn();

    // Recently added (last 30 days)
    $recentStmt = $db->query("
        SELECT id, title, category, status, created_at
        FROM `products`
        ORDER BY created_at DESC
        LIMIT 5
    ");
    $recentProducts = $recentStmt->fetchAll();

    // Best selling: products most referenced in orders JSON
    // We parse from order_items JSON — count by product id appearance
    $bestStmt = $db->query("
        SELECT
            p.id,
            p.title,
            p.category,
            p.status,
            COUNT(o.id) AS order_count
        FROM `products` p
        LEFT JOIN `orders` o ON JSON_SEARCH(o.order_items, 'one', CAST(p.id AS CHAR), NULL, '$[*].id') IS NOT NULL
        GROUP BY p.id
        ORDER BY order_count DESC
        LIMIT 5
    ");
    $bestSelling = $bestStmt->fetchAll();

    // Products never ordered
    $neverStmt = $db->query("
        SELECT
            p.id,
            p.title,
            p.category,
            p.status
        FROM `products` p
        WHERE NOT EXISTS (
            SELECT 1 FROM `orders` o
            WHERE JSON_SEARCH(o.order_items, 'one', CAST(p.id AS CHAR), NULL, '$[*].id') IS NOT NULL
        )
        LIMIT 10
    ");
    $neverOrdered = $neverStmt->fetchAll();

    // Low stock products (stock <= low_stock_threshold)
    $lowStockStmt = $db->query("
        SELECT id, title, category, stock, low_stock_threshold, status
        FROM `products`
        WHERE stock <= low_stock_threshold
        ORDER BY stock ASC
        LIMIT 5
    ");
    $lowStock = $lowStockStmt->fetchAll();

    return compact('total', 'active', 'inactive', 'recentProducts', 'bestSelling', 'neverOrdered', 'lowStock');
}

/* ============================================================
   ORDERS SECTION
   ============================================================ */
function getDashboardOrderStats(): array {
    $db = getDBConnection();

    $total      = (int) $db->query("SELECT COUNT(*) FROM `orders`")->fetchColumn();
    $pending    = (int) $db->query("SELECT COUNT(*) FROM `orders` WHERE status = 'pending'")->fetchColumn();
    $processing = (int) $db->query("SELECT COUNT(*) FROM `orders` WHERE status = 'processing'")->fetchColumn();
    $completed  = (int) $db->query("SELECT COUNT(*) FROM `orders` WHERE status IN ('delivered','completed')")->fetchColumn();
    $cancelled  = (int) $db->query("SELECT COUNT(*) FROM `orders` WHERE status = 'cancelled'")->fetchColumn();

    // Latest orders
    $latestStmt = $db->query("
        SELECT
            o.id,
            o.customer_name,
            o.customer_email,
            o.item_count,
            o.total_amount,
            o.status,
            o.created_at
        FROM `orders` o
        ORDER BY o.id DESC
        LIMIT 8
    ");
    $latestOrders = $latestStmt->fetchAll();

    return compact('total', 'pending', 'processing', 'completed', 'cancelled', 'latestOrders');
}

/* ============================================================
   CUSTOMERS SECTION
   ============================================================ */
function getDashboardCustomerStats(): array {
    $db = getDBConnection();

    $total = (int) $db->query("SELECT COUNT(*) FROM `customers`")->fetchColumn();

    // Recently registered
    $recentStmt = $db->query("
        SELECT
            c.id,
            c.full_name,
            c.email,
            c.created_at,
            COUNT(o.id) AS total_orders
        FROM `customers` c
        LEFT JOIN `orders` o ON o.customer_id = c.id
        GROUP BY c.id
        ORDER BY c.created_at DESC
        LIMIT 5
    ");
    $recentCustomers = $recentStmt->fetchAll();

    // Most orders
    $mostOrdersStmt = $db->query("
        SELECT
            c.id,
            c.full_name,
            c.email,
            COUNT(o.id) AS total_orders
        FROM `customers` c
        LEFT JOIN `orders` o ON o.customer_id = c.id
        GROUP BY c.id
        ORDER BY total_orders DESC
        LIMIT 5
    ");
    $mostOrders = $mostOrdersStmt->fetchAll();

    // Highest spending
    $highestSpendStmt = $db->query("
        SELECT
            c.id,
            c.full_name,
            c.email,
            COALESCE(SUM(o.total_amount), 0) AS total_spent,
            COUNT(o.id) AS total_orders
        FROM `customers` c
        LEFT JOIN `orders` o ON o.customer_id = c.id
        GROUP BY c.id
        ORDER BY total_spent DESC
        LIMIT 5
    ");
    $highestSpending = $highestSpendStmt->fetchAll();

    return compact('total', 'recentCustomers', 'mostOrders', 'highestSpending');
}

/* ============================================================
   CATEGORIES SECTION
   ============================================================ */
function getDashboardCategoryStats(): array {
    $db = getDBConnection();

    $totalCategories = (int) $db->query("SELECT COUNT(DISTINCT category) FROM `products` WHERE category IS NOT NULL AND category != ''")->fetchColumn();

    // Products per category
    $perCatStmt = $db->query("
        SELECT
            category,
            COUNT(*) AS product_count,
            SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) AS active_count
        FROM `products`
        WHERE category IS NOT NULL AND category != ''
        GROUP BY category
        ORDER BY product_count DESC
    ");
    $perCategory = $perCatStmt->fetchAll();

    // Categories with no products (can't happen by design since categories come from products,
    // but check for empty/null category products)
    $noProductsCats = [];

    return compact('totalCategories', 'perCategory', 'noProductsCats');
}

/* ============================================================
   CHART DATA
   ============================================================ */
function getDashboardChartData(): array {
    $db = getDBConnection();

    // Orders per month (last 12 months)
    $ordersMonthStmt = $db->query("
        SELECT
            DATE_FORMAT(created_at, '%Y-%m') AS month_key,
            DATE_FORMAT(created_at, '%b %Y')  AS month_label,
            COUNT(*) AS count
        FROM `orders`
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
        GROUP BY month_key, month_label
        ORDER BY month_key ASC
    ");
    $ordersPerMonth = $ordersMonthStmt->fetchAll();

    // Customers per month (last 12 months)
    $custMonthStmt = $db->query("
        SELECT
            DATE_FORMAT(created_at, '%Y-%m') AS month_key,
            DATE_FORMAT(created_at, '%b %Y')  AS month_label,
            COUNT(*) AS count
        FROM `customers`
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
        GROUP BY month_key, month_label
        ORDER BY month_key ASC
    ");
    $customersPerMonth = $custMonthStmt->fetchAll();

    // Products added per month (last 12 months)
    $prodMonthStmt = $db->query("
        SELECT
            DATE_FORMAT(created_at, '%Y-%m') AS month_key,
            DATE_FORMAT(created_at, '%b %Y')  AS month_label,
            COUNT(*) AS count
        FROM `products`
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
        GROUP BY month_key, month_label
        ORDER BY month_key ASC
    ");
    $productsPerMonth = $prodMonthStmt->fetchAll();

    // Order status distribution
    $statusDistStmt = $db->query("
        SELECT status, COUNT(*) AS count
        FROM `orders`
        GROUP BY status
        ORDER BY count DESC
    ");
    $orderStatusDist = $statusDistStmt->fetchAll();

    // Top selling products (from orders JSON parsing)
    $topProductsStmt = $db->query("
        SELECT
            p.id,
            p.title,
            COUNT(o.id) AS order_count
        FROM `products` p
        LEFT JOIN `orders` o ON JSON_SEARCH(o.order_items, 'one', CAST(p.id AS CHAR), NULL, '$[*].id') IS NOT NULL
        GROUP BY p.id, p.title
        ORDER BY order_count DESC
        LIMIT 6
    ");
    $topProducts = $topProductsStmt->fetchAll();

    return compact('ordersPerMonth', 'customersPerMonth', 'productsPerMonth', 'orderStatusDist', 'topProducts');
}

/* ============================================================
   RECENT ORDERS TABLE (dashboard widget)
   ============================================================ */
function getDashboardRecentOrders(int $limit = 7): array {
    $db = getDBConnection();
    $stmt = $db->prepare("
        SELECT
            o.id,
            o.customer_name,
            o.customer_email,
            o.item_count,
            o.total_amount,
            o.status,
            o.created_at
        FROM `orders` o
        ORDER BY o.id DESC
        LIMIT :lim
    ");
    $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

/* ============================================================
   TOP PRODUCTS TABLE (dashboard widget)
   ============================================================ */
function getDashboardTopProducts(int $limit = 5): array {
    $db = getDBConnection();
    $stmt = $db->query("
        SELECT
            p.id,
            p.title,
            p.category,
            p.status,
            COUNT(o.id) AS order_count,
            COALESCE(SUM(o.item_count), 0) AS total_qty_sold
        FROM `products` p
        LEFT JOIN `orders` o ON JSON_SEARCH(o.order_items, 'one', CAST(p.id AS CHAR), NULL, '$[*].id') IS NOT NULL
        GROUP BY p.id, p.title, p.category, p.status
        ORDER BY order_count DESC
        LIMIT $limit
    ");
    return $stmt->fetchAll();
}
