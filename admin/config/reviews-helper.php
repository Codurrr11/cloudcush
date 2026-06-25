<?php
// admin/config/reviews-helper.php
// Shared review utility functions for CRUD operations

require_once __DIR__ . '/database.php';

/**
 * Handle image or video upload for reviews — returns absolute URL or null
 */
function handleReviewMediaUpload(array $file, string $mediaType, string $prefix = 'review'): ?string {
    if (empty($file['name']) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    if ($mediaType === 'image') {
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        if (!in_array($file['type'], $allowedTypes)) {
            throw new \InvalidArgumentException('Invalid image format. Allowed: JPG, PNG, WebP.');
        }
    } elseif ($mediaType === 'video') {
        $maxSize = 25 * 1024 * 1024; // 25MB
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $videoExtensions = ['mp4', 'webm', 'ogg', 'mov'];
        if (!in_array($ext, $videoExtensions)) {
            throw new \InvalidArgumentException('Invalid video format. Allowed: MP4, WebM, OGG, MOV.');
        }
    } else {
        return null;
    }

    if ($file['size'] > $maxSize) {
        $maxLabel = $mediaType === 'video' ? '25MB' : '5MB';
        throw new \InvalidArgumentException("Uploaded file is too large. Maximum size is $maxLabel.");
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filename = $prefix . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $destination = UPLOAD_DIR . $filename;

    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new \RuntimeException('Failed to save uploaded file.');
    }

    return UPLOAD_URL . $filename;
}

/**
 * Get reviews list with pagination, search and filters
 */
function getReviews(array $params = []): array {
    $db = getDBConnection();

    $search = $params['search'] ?? '';
    $status = $params['status'] ?? '';
    $page = max(1, (int)($params['page'] ?? 1));
    $perPage = (int)($params['per_page'] ?? 15);
    $offset = ($page - 1) * $perPage;

    $conditions = ['1=1'];
    $bindings = [];

    if ($search) {
        $conditions[] = '(name LIKE :search OR role LIKE :search OR quote LIKE :search)';
        $bindings['search'] = '%' . $search . '%';
    }
    if ($status) {
        $conditions[] = 'status = :status';
        $bindings['status'] = $status;
    }

    $whereClause = implode(' AND ', $conditions);

    // Count query
    $countStmt = $db->prepare("SELECT COUNT(*) FROM reviews WHERE $whereClause");
    $countStmt->execute($bindings);
    $totalCount = (int)$countStmt->fetchColumn();

    // Data query
    $stmt = $db->prepare("
        SELECT * FROM reviews
        WHERE $whereClause
        ORDER BY created_at DESC
        LIMIT :limit OFFSET :offset
    ");
    foreach ($bindings as $key => $val) {
        $stmt->bindValue($key, $val);
    }
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $reviews = $stmt->fetchAll();

    return [
        'data'        => $reviews,
        'total'       => $totalCount,
        'page'        => $page,
        'per_page'    => $perPage,
        'total_pages' => ceil($totalCount / $perPage),
    ];
}

/**
 * Get distinct review status badge formatting
 */
function getReviewStatusBadge(string $status): array {
    return match($status) {
        'active' => ['label' => 'Approved', 'class' => 'success'],
        'draft'  => ['label' => 'Pending',     'class' => 'warning'],
        default  => ['label' => ucfirst($status), 'class' => 'secondary'],
    };
}

/**
 * Get a single review record by ID
 */
function getReviewById(int $id): ?array {
    $db = getDBConnection();
    $stmt = $db->prepare("SELECT * FROM reviews WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => $id]);
    $review = $stmt->fetch();
    return $review ?: null;
}
