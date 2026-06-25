<?php
// admin/config/footer-helper.php
require_once __DIR__ . '/database.php';

if (!defined('UPLOAD_DIR')) {
    define('UPLOAD_DIR', __DIR__ . '/../../admin/assets/uploads/');
}
if (!defined('UPLOAD_URL')) {
    $baseUrl = defined('BASE_URL') ? BASE_URL : 'http://localhost/cloudcush/admin/';
    define('UPLOAD_URL', $baseUrl . 'assets/uploads/');
}

function getFooterData(bool $refresh = false): array {
    static $cache = null;
    if (!$refresh && $cache !== null) {
        return $cache;
    }
    try {
        $db = getDBConnection();
        $stmt = $db->prepare("SELECT content FROM pages WHERE page_name = 'footer' LIMIT 1");
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $cache = json_decode($row['content'], true) ?: [];
            return $cache;
        }
    } catch (Exception $e) {
        error_log("Failed to fetch footer JSON: " . $e->getMessage());
    }

    // Default fallback
    $cache = [
        'logo_img'       => 'assets/images/logo.png',
        'story_text'     => "CloudCush is a luxury baby-care brand redefining newborn routine comfort. We believe in pure, organic, and dermatologically certified materials designed to protect your baby's delicate skin barrier from day one.",
        'bg_image'       => 'assets/images/footer-bg.png',
        'typing_text_1'  => 'Cloudcush',
        'typing_text_2'  => 'comfort designed for tiny humans.',
        'copyright_text' => '© 2026, CloudCush. Crafted for softer beginnings.',
        'social_links'   => [
            'instagram' => 'javascript:void(0);',
            'youtube'   => 'javascript:void(0);',
            'facebook'  => 'javascript:void(0);',
            'twitter'   => '',
        ],
        'legal_links'    => [
            ['title' => 'Shipping & Delivery',  'url' => 'javascript:void(0);'],
            ['title' => 'Return & Refund',       'url' => 'javascript:void(0);'],
            ['title' => 'Warranty',              'url' => 'javascript:void(0);'],
            ['title' => 'Terms & Conditions',    'url' => 'javascript:void(0);'],
            ['title' => 'Privacy Policy',        'url' => 'javascript:void(0);'],
        ],
        'columns' => [
            [
                'title' => 'Shop',
                'links' => [
                    ['title' => 'Newborn Diapers',       'url' => 'products.php'],
                    ['title' => 'Active Baby Diapers',   'url' => 'products.php'],
                    ['title' => 'Overnight Protection',  'url' => 'products.php'],
                    ['title' => 'Sensitive Skin Diapers','url' => 'products.php'],
                    ['title' => 'Single Packs',          'url' => 'products.php']
                ]
            ],
            [
                'title' => 'Explore',
                'links' => [
                    ['title' => 'Why CloudCush',  'url' => 'about.php'],
                    ['title' => 'Our Philosophy', 'url' => 'about.php'],
                    ['title' => 'The Journal',    'url' => 'blog.php'],
                    ['title' => 'Care Plan',      'url' => 'diaper-guide.php']
                ]
            ],
            [
                'title' => 'Support',
                'links' => [
                    ['title' => 'FAQs',                  'url' => 'faq.php'],
                    ['title' => 'Diaper Guide',          'url' => 'diaper-guide.php'],
                    ['title' => 'Logistics & Tracking',  'url' => 'faq.php'],
                    ['title' => 'Contact Care',          'url' => 'faq.php']
                ]
            ]
        ]
    ];
    return $cache;
}

function saveFooterData(array $data): bool {
    try {
        $db = getDBConnection();
        $check = $db->prepare("SELECT id FROM pages WHERE page_name = 'footer' LIMIT 1");
        $check->execute();
        if (!$check->fetch()) {
            $ins = $db->prepare("INSERT INTO pages (page_name, content) VALUES ('footer', :content)");
            $ins->execute([':content' => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)]);
        } else {
            $stmt = $db->prepare("UPDATE pages SET content = :content WHERE page_name = 'footer'");
            $stmt->execute([':content' => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)]);
        }
        getFooterData(true); // invalidate cache
        return true;
    } catch (Exception $e) {
        error_log("Failed to save footer JSON: " . $e->getMessage());
        return false;
    }
}

function handleFooterImageUpload(array $file, string $prefix = 'foot'): ?string {
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
