<?php
// admin/config/header-helper.php
require_once __DIR__ . '/database.php';

if (!defined('UPLOAD_DIR')) {
    define('UPLOAD_DIR', __DIR__ . '/../../admin/assets/uploads/');
}
if (!defined('UPLOAD_URL')) {
    $baseUrl = defined('BASE_URL') ? BASE_URL : 'http://localhost/cloudcush/admin/';
    define('UPLOAD_URL', $baseUrl . 'assets/uploads/');
}

function getHeaderData(bool $refresh = false): array {
    static $cache = null;
    if (!$refresh && $cache !== null) {
        return $cache;
    }
    try {
        $db = getDBConnection();
        $stmt = $db->prepare("SELECT content FROM pages WHERE page_name = 'header' LIMIT 1");
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $cache = json_decode($row['content'], true) ?: [];
            return $cache;
        }
    } catch (Exception $e) {
        error_log("Failed to fetch header JSON: " . $e->getMessage());
    }
    
    // Default fallback
    $cache = [
        'logo_img' => 'assets/images/logo.png',
        'logo_text' => 'CloudCush',
        'tabs' => [
            ['title' => 'Shop', 'url' => 'products.php', 'position' => 'left'],
            ['title' => 'Why CloudCush', 'url' => 'about.php', 'position' => 'left'],
            ['title' => 'Care Guide', 'url' => 'diaper-guide.php', 'position' => 'left'],
            ['title' => 'Journal', 'url' => 'blog.php', 'position' => 'right'],
            ['title' => 'FAQ', 'url' => 'faq.php', 'position' => 'right']
        ]
    ];
    return $cache;
}

function saveHeaderData(array $data): bool {
    try {
        $db = getDBConnection();
        $check = $db->prepare("SELECT id FROM pages WHERE page_name = 'header' LIMIT 1");
        $check->execute();
        if (!$check->fetch()) {
            $ins = $db->prepare("INSERT INTO pages (page_name, content) VALUES ('header', :content)");
            $ins->execute([':content' => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)]);
        } else {
            $stmt = $db->prepare("UPDATE pages SET content = :content WHERE page_name = 'header'");
            $stmt->execute([':content' => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)]);
        }
        getHeaderData(true); // invalidate cache
        return true;
    } catch (Exception $e) {
        error_log("Failed to save header JSON: " . $e->getMessage());
        return false;
    }
}

function handleHeaderImageUpload(array $file, string $prefix = 'logo'): ?string {
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
