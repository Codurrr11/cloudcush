<?php
// admin/config/products-helper.php
// Shared product utility functions for CRUD operations

require_once __DIR__ . '/database.php';

/**
 * Generate URL-friendly slug from a string
 */
function generateSlug(string $title, int $excludeId = 0): string {
    $slug = strtolower(trim($title));
    $slug = preg_replace('/[^a-z0-9\s\-]/', '', $slug);
    $slug = preg_replace('/[\s\-]+/', '-', $slug);
    $slug = trim($slug, '-');

    // Ensure uniqueness
    $db = getDBConnection();
    $baseSlug = $slug;
    $i = 1;
    do {
        $checkSlug = ($i === 1) ? $baseSlug : $baseSlug . '-' . $i;
        $stmt = $db->prepare("SELECT id FROM products WHERE slug = :slug AND id != :id LIMIT 1");
        $stmt->execute(['slug' => $checkSlug, 'id' => $excludeId]);
        $exists = $stmt->fetch();
        if (!$exists) {
            $slug = $checkSlug;
            break;
        }
        $i++;
    } while (true);

    return $slug;
}

/**
 * Generate unique SKU from title and category
 */
function generateSKU(string $title, string $category, int $excludeId = 0): string {
    $prefix = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $category), 0, 3));
    $suffix = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $title), 0, 4));
    $number = strtoupper(substr(md5($title . $category . time()), 0, 4));
    $sku = $prefix . '-' . $suffix . '-' . $number;

    $db = getDBConnection();
    $stmt = $db->prepare("SELECT id FROM products WHERE sku = :sku AND id != :id LIMIT 1");
    $stmt->execute(['sku' => $sku, 'id' => $excludeId]);
    if ($stmt->fetch()) {
        $sku .= '-' . rand(10, 99);
    }
    return $sku;
}

/**
 * Handle image upload — returns relative URL or null
 */
function handleImageUpload(array $file, string $prefix = 'prod'): ?string {
    if (empty($file['name']) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB

    if (!in_array($file['type'], $allowedTypes)) {
        throw new \InvalidArgumentException('Invalid image format. Allowed: JPG, PNG, WebP.');
    }
    if ($file['size'] > $maxSize) {
        throw new \InvalidArgumentException('Image too large. Maximum size is 5MB.');
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filename = $prefix . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $destination = UPLOAD_DIR . $filename;

    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new \RuntimeException('Failed to save uploaded image.');
    }

    return UPLOAD_URL . $filename;
}

/**
 * Get all products with optional search/filter/pagination
 */
function getProducts(array $params = []): array {
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
        $conditions[] = '(p.title LIKE :search OR p.sku LIKE :search OR p.category LIKE :search)';
        $bindings['search'] = '%' . $search . '%';
    }
    if ($category) {
        $conditions[] = 'p.category = :category';
        $bindings['category'] = $category;
    }
    if ($status) {
        $conditions[] = 'p.status = :status';
        $bindings['status'] = $status;
    }

    $whereClause = implode(' AND ', $conditions);

    // Count query
    $countStmt = $db->prepare("SELECT COUNT(*) FROM products p WHERE $whereClause");
    $countStmt->execute($bindings);
    $totalCount = (int)$countStmt->fetchColumn();

    // Data query
    $stmt = $db->prepare("
        SELECT p.*, u.name AS creator_name
        FROM products p
        LEFT JOIN users u ON p.created_by = u.id
        WHERE $whereClause
        ORDER BY p.created_at DESC
        LIMIT :limit OFFSET :offset
    ");
    foreach ($bindings as $key => $val) {
        $stmt->bindValue($key, $val);
    }
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $products = $stmt->fetchAll();

    return [
        'data'        => $products,
        'total'       => $totalCount,
        'page'        => $page,
        'per_page'    => $perPage,
        'total_pages' => ceil($totalCount / $perPage),
    ];
}

/**
 * Get a single product by ID with its variants
 */
function getProductById(int $id): ?array {
    $db = getDBConnection();

    $stmt = $db->prepare("
        SELECT p.*, u.name AS creator_name
        FROM products p
        LEFT JOIN users u ON p.created_by = u.id
        WHERE p.id = :id LIMIT 1
    ");
    $stmt->execute(['id' => $id]);
    $product = $stmt->fetch();

    if (!$product) return null;

    // Fetch variants
    $varStmt = $db->prepare("SELECT * FROM product_variants WHERE product_id = :id ORDER BY is_default DESC, id ASC");
    $varStmt->execute(['id' => $id]);
    $product['variants'] = $varStmt->fetchAll();

    // Decode gallery images JSON
    $product['gallery_images'] = $product['gallery_images'] ? json_decode($product['gallery_images'], true) : [];
    $product['detail_images'] = !empty($product['detail_images']) ? json_decode($product['detail_images'], true) : [];

    return $product;
}

/**
 * Get a single product by slug with its variants
 */
function getProductBySlug(string $slug): ?array {
    $db = getDBConnection();

    $stmt = $db->prepare("
        SELECT p.*, u.name AS creator_name
        FROM products p
        LEFT JOIN users u ON p.created_by = u.id
        WHERE p.slug = :slug LIMIT 1
    ");
    $stmt->execute(['slug' => $slug]);
    $product = $stmt->fetch();

    if (!$product) return null;

    $id = $product['id'];

    // Fetch variants
    $varStmt = $db->prepare("SELECT * FROM product_variants WHERE product_id = :id ORDER BY is_default DESC, id ASC");
    $varStmt->execute(['id' => $id]);
    $product['variants'] = $varStmt->fetchAll();

    // Decode gallery images JSON
    $product['gallery_images'] = $product['gallery_images'] ? json_decode($product['gallery_images'], true) : [];
    $product['detail_images'] = !empty($product['detail_images']) ? json_decode($product['detail_images'], true) : [];

    return $product;
}

/**
 * Get distinct product categories
 */
function getProductCategories(): array {
    $db = getDBConnection();
    $stmt = $db->query("SELECT DISTINCT category FROM products WHERE category IS NOT NULL ORDER BY category ASC");
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

/**
 * Get product status label + CSS class
 */
function getStatusBadge(string $status): array {
    return match($status) {
        'active'       => ['label' => 'Active',       'class' => 'success'],
        'draft'        => ['label' => 'Draft',         'class' => 'warning'],
        'out_of_stock' => ['label' => 'Out of Stock',  'class' => 'danger'],
        'archived'     => ['label' => 'Archived',      'class' => 'secondary'],
        default        => ['label' => ucfirst($status), 'class' => 'secondary'],
    };
}
