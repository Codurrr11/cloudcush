<?php
// admin/config/blogs-helper.php
// Shared blog utility functions for CRUD operations

require_once __DIR__ . '/database.php';

/**
 * Generate URL-friendly slug from a string for blogs
 */
function generateBlogSlug(string $title, int $excludeId = 0): string {
    $slug = strtolower(trim($title));
    $slug = preg_replace('/[^a-z0-9\s\-]/', '', $slug);
    $slug = preg_replace('/[\s\-]+/', '-', $slug);
    $slug = trim($slug, '-');

    // Default slug if empty
    if (empty($slug)) {
        $slug = 'post';
    }

    $db = getDBConnection();
    $baseSlug = $slug;
    $i = 1;
    do {
        $checkSlug = ($i === 1) ? $baseSlug : $baseSlug . '-' . $i;
        $stmt = $db->prepare("SELECT id FROM blogs WHERE slug = :slug AND id != :id LIMIT 1");
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
 * Handle image upload for blogs — returns relative URL or null
 */
function handleBlogImageUpload(array $file, string $prefix = 'blog'): ?string {
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
 * Get all blogs with optional search/filter/pagination
 */
function getBlogs(array $params = []): array {
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
        $conditions[] = '(b.title LIKE :search OR b.content LIKE :search OR b.category LIKE :search OR b.short_description LIKE :search)';
        $bindings['search'] = '%' . $search . '%';
    }
    if ($category) {
        $conditions[] = 'b.category = :category';
        $bindings['category'] = $category;
    }
    if ($status) {
        $conditions[] = 'b.status = :status';
        $bindings['status'] = $status;
    }

    $whereClause = implode(' AND ', $conditions);

    // Count query
    $countStmt = $db->prepare("SELECT COUNT(*) FROM blogs b WHERE $whereClause");
    $countStmt->execute($bindings);
    $totalCount = (int)$countStmt->fetchColumn();

    // Data query
    $stmt = $db->prepare("
        SELECT b.*, u.name AS author_name, u.role AS author_role
        FROM blogs b
        LEFT JOIN users u ON b.author_id = u.id
        WHERE $whereClause
        ORDER BY b.created_at DESC
        LIMIT :limit OFFSET :offset
    ");
    foreach ($bindings as $key => $val) {
        $stmt->bindValue($key, $val);
    }
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $blogs = $stmt->fetchAll();

    return [
        'data'        => $blogs,
        'total'       => $totalCount,
        'page'        => $page,
        'per_page'    => $perPage,
        'total_pages' => ceil($totalCount / $perPage),
    ];
}

/**
 * Get a single blog by ID or Slug
 */
function getBlogByIdOrSlug($idOrSlug): ?array {
    $db = getDBConnection();

    if (is_numeric($idOrSlug)) {
        $stmt = $db->prepare("
            SELECT b.*, u.name AS author_name, u.role AS author_role
            FROM blogs b
            LEFT JOIN users u ON b.author_id = u.id
            WHERE b.id = :id LIMIT 1
        ");
        $stmt->execute(['id' => (int)$idOrSlug]);
    } else {
        $stmt = $db->prepare("
            SELECT b.*, u.name AS author_name, u.role AS author_role
            FROM blogs b
            LEFT JOIN users u ON b.author_id = u.id
            WHERE b.slug = :slug LIMIT 1
        ");
        $stmt->execute(['slug' => $idOrSlug]);
    }
    
    $blog = $stmt->fetch();
    return $blog ?: null;
}

/**
 * Get distinct blog categories
 */
function getBlogCategories(): array {
    $db = getDBConnection();
    $stmt = $db->query("SELECT DISTINCT category FROM blogs WHERE category IS NOT NULL AND category != '' ORDER BY category ASC");
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

/**
 * Get blog status badge metadata
 */
function getBlogStatusBadge(string $status): array {
    return match($status) {
        'active' => ['label' => 'Published', 'class' => 'success'],
        'draft'  => ['label' => 'Draft',     'class' => 'warning'],
        default  => ['label' => ucfirst($status), 'class' => 'secondary'],
    };
}
