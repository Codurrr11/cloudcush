<?php
// admin/config/faqs-helper.php
// Centralized FAQs helper functions

require_once __DIR__ . '/database.php';

/**
 * Fetch FAQs with search, category filtering, status filtering, and pagination.
 */
function getFaqs(array $params = []): array {
    $db = getDBConnection();

    $search = $params['search'] ?? '';
    $category = $params['category'] ?? '';
    $status = $params['status'] ?? '';
    $page = max(1, (int)($params['page'] ?? 1));
    $perPage = (int)($params['per_page'] ?? 15);
    $offset = ($page - 1) * $perPage;

    $conditions = ['1=1'];
    $bindings = [];

    if ($search) {
        $conditions[] = '(question LIKE :search OR answer LIKE :search)';
        $bindings['search'] = '%' . $search . '%';
    }
    if ($category) {
        $conditions[] = 'category = :category';
        $bindings['category'] = $category;
    }
    if ($status) {
        $conditions[] = 'status = :status';
        $bindings['status'] = $status;
    }

    $whereClause = implode(' AND ', $conditions);

    // Count total FAQs
    $countStmt = $db->prepare("SELECT COUNT(*) FROM faqs WHERE $whereClause");
    $countStmt->execute($bindings);
    $totalCount = (int)$countStmt->fetchColumn();

    // Query FAQs with sorting: first by category order, then by sort_order, then by created date
    $stmt = $db->prepare("
        SELECT * FROM faqs
        WHERE $whereClause
        ORDER BY category ASC, sort_order ASC, created_at DESC
        LIMIT :limit OFFSET :offset
    ");

    foreach ($bindings as $key => $val) {
        $stmt->bindValue($key, $val);
    }
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return [
        'data'        => $data,
        'total'       => $totalCount,
        'page'        => $page,
        'per_page'    => $perPage,
        'total_pages' => ceil($totalCount / $perPage)
    ];
}

/**
 * Fetch a single FAQ by its ID.
 */
function getFaqById(int $id): ?array {
    $db = getDBConnection();
    $stmt = $db->prepare("SELECT * FROM faqs WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => $id]);
    $faq = $stmt->fetch();
    return $faq ?: null;
}

/**
 * Get display names for valid FAQ categories.
 */
function getFaqCategories(): array {
    return [
        'product'   => 'Product & Sizing',
        'materials' => 'Materials & Safety',
        'orders'    => 'Orders & Shipping',
        'returns'   => 'Returns & Support',
        'parenting' => 'Parenting & Care'
    ];
}

/**
 * Get badge configuration mapping based on FAQ status.
 */
function getFaqStatusBadge(string $status): array {
    return match($status) {
        'active' => ['label' => 'Live',   'class' => 'success'],
        'draft'  => ['label' => 'Draft',  'class' => 'warning'],
        default  => ['label' => ucfirst($status), 'class' => 'secondary']
    };
}
