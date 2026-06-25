<?php
// admin/config/home-helper.php
// Centralized helper functions for managing the Homepage sections

require_once __DIR__ . '/database.php';

// Only define UPLOAD_DIR / UPLOAD_URL if config.php hasn't defined them yet.
// config.php is the canonical source for these constants.
if (!defined('UPLOAD_DIR')) {
    define('UPLOAD_DIR', __DIR__ . '/../../admin/assets/uploads/');
}
if (!defined('UPLOAD_URL')) {
    // Fall back to a relative token if BASE_URL isn't available yet
    $baseUrl = defined('BASE_URL') ? BASE_URL : 'http://localhost/cloudcush/admin/';
    define('UPLOAD_URL', $baseUrl . 'assets/uploads/');
}

/**
 * Fetch consolidated Homepage JSON content from pages table.
 *
 * @param bool $refresh  Pass true to bypass the static cache (used after saves).
 */
function getHomePageData(bool $refresh = false): array {
    static $cache = null;
    if (!$refresh && $cache !== null) {
        return $cache;
    }
    if ($refresh) {
        $cache = null; // Explicitly clear
    }
    try {
        $db = getDBConnection();
        $stmt = $db->prepare("SELECT content FROM pages WHERE page_name = 'home' LIMIT 1");
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $cache = json_decode($row['content'], true) ?: [];
            return $cache;
        }
    } catch (Exception $e) {
        error_log("Failed to fetch homepage JSON: " . $e->getMessage());
    }
    $cache = [];
    return $cache;
}

/**
 * Save consolidated Homepage JSON content back to pages table.
 * Also clears the in-request static cache so subsequent reads reflect the update.
 */
function saveHomePageData(array $data): bool {
    try {
        $db = getDBConnection();
        $stmt = $db->prepare("UPDATE pages SET content = :content WHERE page_name = 'home'");
        $result = $stmt->execute([':content' => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)]);
        if ($result) {
            // Invalidate the static cache so the next getHomePageData() call re-reads from DB
            getHomePageData(true);
        }
        return $result;
    } catch (Exception $e) {
        error_log("Failed to save homepage JSON: " . $e->getMessage());
        return false;
    }
}

/**
 * Fetch all Homepage sections, indexed by their section_key.
 */
function getHomeSections(): array {
    $data = getHomePageData();
    return $data['sections'] ?? [];
}

/**
 * Fetch a single Homepage section by its unique section_key.
 */
function getHomeSection(string $key): ?array {
    $sections = getHomeSections();
    return $sections[$key] ?? null;
}

/**
 * Update an existing Homepage section by key.
 * All known field keys across every section are included so saves always work correctly.
 */
function updateHomeSection(string $key, array $sectionData): bool {
    $data = getHomePageData();
    if (!isset($data['sections']) || !is_array($data['sections'])) {
        $data['sections'] = [];
    }
    if (!isset($data['sections'][$key]) || !is_array($data['sections'][$key])) {
        $data['sections'][$key] = [];
    }

    // Master allow-list covering every section's field keys
    $allowed = [
        // Hero
        'section_title', 'section_subtitle',
        'left_text', 'right_text',
        'btn_text', 'btn_url',
        'image_url_1', 'image_url_2', 'image_url_3',
        // Showcase
        'badge_label', 'desc_1', 'desc_2', 'video_url',
        // Atelier header / Care plan header / Catnav header
        'content',
        // Care Plan
        'main_image_url',
        'panel_image_1', 'panel_image_2', 'panel_image_3', 'panel_image_4',
        // Philosophy
        'bg_image_url',
        'panel1_eyebrow', 'panel1_bold', 'panel1_text', 'panel1_btn_text', 'panel1_btn_url',
        'panel2_eyebrow', 'panel2_text', 'panel2_btn_text', 'panel2_btn_url',
    ];

    foreach ($allowed as $col) {
        if (array_key_exists($col, $sectionData)) {
            $data['sections'][$key][$col] = $sectionData[$col];
        }
    }
    return saveHomePageData($data);
}

/**
 * Fetch Sizing Atelier Variants.
 */
function getHomeAtelierVariants(): array {
    $data = getHomePageData();
    return $data['atelier_variants'] ?? [];
}

/**
 * Fetch a single Sizing Atelier Variant by ID.
 */
function getHomeAtelierVariant(int $id): ?array {
    $variants = getHomeAtelierVariants();
    foreach ($variants as $v) {
        if (intval($v['id'] ?? 0) === $id) {
            return $v;
        }
    }
    return null;
}

/**
 * Update Sizing Atelier Variant by ID.
 */
function updateHomeAtelierVariant(int $id, array $variantData): bool {
    $data = getHomePageData();
    if (!isset($data['atelier_variants']) || !is_array($data['atelier_variants'])) {
        return false;
    }
    $updated = false;
    foreach ($data['atelier_variants'] as &$v) {
        if (intval($v['id'] ?? 0) === $id) {
            $v['variant_name']    = trim($variantData['variant_name']);
            $v['tag_top_title']   = trim($variantData['tag_top_title']);
            $v['tag_top_desc']    = trim($variantData['tag_top_desc']);
            $v['tag_bottom_title'] = trim($variantData['tag_bottom_title']);
            $v['tag_bottom_desc'] = trim($variantData['tag_bottom_desc']);
            $v['val_absorbency']  = intval($variantData['val_absorbency']);
            $v['val_stretch']     = intval($variantData['val_stretch']);
            $v['val_softness']    = intval($variantData['val_softness']);
            if (isset($variantData['image_url'])) {
                $v['image_url'] = $variantData['image_url'];
            }
            $updated = true;
            break;
        }
    }
    if (!$updated) return false;
    return saveHomePageData($data);
}

/**
 * Fetch Care Plan Perks.
 */
function getHomeCarePlanPerks(): array {
    $data = getHomePageData();
    return $data['care_plan_perks'] ?? [];
}

/**
 * Fetch a single Care Plan Perk by ID.
 */
function getHomeCarePlanPerk(int $id): ?array {
    $perks = getHomeCarePlanPerks();
    foreach ($perks as $p) {
        if (intval($p['id'] ?? 0) === $id) {
            return $p;
        }
    }
    return null;
}

/**
 * Update Care Plan Perk by ID.
 */
function updateHomeCarePlanPerk(int $id, array $perkData): bool {
    $data = getHomePageData();
    if (!isset($data['care_plan_perks']) || !is_array($data['care_plan_perks'])) {
        return false;
    }
    $updated = false;
    foreach ($data['care_plan_perks'] as &$p) {
        if (intval($p['id'] ?? 0) === $id) {
            $p['label']    = trim($perkData['label']);
            $p['icon_svg'] = trim($perkData['icon_svg']);
            $updated = true;
            break;
        }
    }
    if (!$updated) return false;
    return saveHomePageData($data);
}

/**
 * Fetch Category Nav Panels.
 * Sanitizes panel data to remove any malformed/duplicate keys that may have
 * crept in via an earlier save (e.g. the spurious "id: 3": 3 key).
 */
function getHomeCatnavPanels(): array {
    $data = getHomePageData();
    $panels = $data['catnav_panels'] ?? [];

    // Sanitize: ensure each panel has a clean integer 'id' and no stray keys
    foreach ($panels as &$panel) {
        // Remove any malformed key like "id: 3" left by an earlier JSON quirk
        foreach (array_keys($panel) as $k) {
            if (preg_match('/^id\s*:\s*\d+$/', $k)) {
                unset($panel[$k]);
            }
        }
        // Ensure 'id' is an integer
        if (isset($panel['id'])) {
            $panel['id'] = intval($panel['id']);
        }
    }
    unset($panel);

    return $panels;
}

/**
 * Fetch a single Category Nav Panel by ID.
 */
function getHomeCatnavPanel(int $id): ?array {
    $panels = getHomeCatnavPanels();
    foreach ($panels as $p) {
        if (intval($p['id'] ?? 0) === $id) {
            return $p;
        }
    }
    return null;
}

/**
 * Update Category Nav Panel by ID.
 */
function updateHomeCatnavPanel(int $id, array $panelData): bool {
    $data = getHomePageData();
    if (!isset($data['catnav_panels']) || !is_array($data['catnav_panels'])) {
        return false;
    }
    $updated = false;
    foreach ($data['catnav_panels'] as &$p) {
        if (intval($p['id'] ?? 0) === $id) {
            $p['eyebrow']     = trim($panelData['eyebrow']);
            $p['title']       = trim($panelData['title']);
            $p['description'] = trim($panelData['description']);
            $p['btn_text']    = trim($panelData['btn_text']);
            $p['btn_url']     = trim($panelData['btn_url']);
            if (isset($panelData['image_url'])) {
                $p['image_url'] = $panelData['image_url'];
            }
            $updated = true;
            break;
        }
    }
    if (!$updated) return false;
    return saveHomePageData($data);
}

/**
 * Handle image upload for homepage sections
 */
function handleHomeImageUpload(array $file, string $prefix = 'home'): ?string {
    if (empty($file['name']) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB

    if (!in_array($file['type'], $allowedTypes)) {
        throw new \InvalidArgumentException('Invalid image format. Allowed: JPG, PNG, WebP.');
    }

    if ($file['size'] > $maxSize) {
        throw new \InvalidArgumentException("Uploaded file is too large. Maximum size is 5MB.");
    }

    $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filename = $prefix . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;

    $destinationDir = UPLOAD_DIR;
    if (!is_dir($destinationDir)) {
        mkdir($destinationDir, 0755, true);
    }

    $destination = $destinationDir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new \RuntimeException('Failed to save uploaded file.');
    }

    return UPLOAD_URL . $filename;
}

/**
 * Handle video upload for homepage sections
 */
function handleHomeVideoUpload(array $file, string $prefix = 'home_video'): ?string {
    if (empty($file['name']) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $allowedTypes = ['video/mp4', 'video/webm', 'video/ogg'];
    $maxSize = 40 * 1024 * 1024; // 40MB limit for video upload to be safe

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowedExts = ['mp4', 'webm', 'ogg'];

    if (!in_array($file['type'], $allowedTypes) && !in_array($ext, $allowedExts)) {
        throw new \InvalidArgumentException('Invalid video format. Allowed: MP4, WebM, OGG.');
    }

    if ($file['size'] > $maxSize) {
        throw new \InvalidArgumentException("Uploaded file is too large. Maximum size is 40MB.");
    }

    $filename = $prefix . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;

    $destinationDir = UPLOAD_DIR;
    if (!is_dir($destinationDir)) {
        mkdir($destinationDir, 0755, true);
    }

    $destination = $destinationDir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new \RuntimeException('Failed to save uploaded file.');
    }

    return UPLOAD_URL . $filename;
}

/**
 * Get configuration metadata for form rendering on each section key.
 */
function getHomeSectionMetadata(string $key = null): array {
    $meta = [
        'hero' => [
            'name'        => 'Hero Banner Section',
            'description' => 'The top-most header section on the home page with main heading, left description, right description, CTA link, and baby image layers.',
            'fields'      => [
                'section_title' => ['type' => 'text',     'label' => 'Main Editorial Title',          'required' => true,  'placeholder' => 'e.g. Softness That Breathes.'],
                'left_text'     => ['type' => 'textarea', 'label' => 'Left Column Description',        'required' => true,  'placeholder' => 'HTML allowed, e.g. Soft. Dry. Clean.<br>Pure Comfort.'],
                'right_text'    => ['type' => 'textarea', 'label' => 'Right Column Description',       'required' => true,  'placeholder' => 'HTML allowed, e.g. Pediatrician-approved TCF.<br>Certified safe for newborn skin.'],
                'btn_text'      => ['type' => 'text',     'label' => 'CTA Button Text',                'required' => false],
                'btn_url'       => ['type' => 'text',     'label' => 'CTA Button URL',                 'required' => false],
                'image_url_1'   => ['type' => 'image',    'label' => 'Baby Main Image 1',              'required' => false],
                'image_url_2'   => ['type' => 'image',    'label' => 'Baby Main Image 2',              'required' => false],
                'image_url_3'   => ['type' => 'image',    'label' => 'Baby Main Image 3',              'required' => false],
            ]
        ],
        'showcase' => [
            'name'        => 'Showcase Section',
            'description' => 'The rotating diaper showcase block with editorial content and video placeholder.',
            'fields'      => [
                'section_title' => ['type' => 'text',     'label' => 'Showcase Title',                 'required' => true,  'placeholder' => 'HTML allowed, e.g. The Softest<br>Diaper Ever.'],
                'badge_label'   => ['type' => 'text',     'label' => 'Cloud Badge Label',              'required' => true,  'placeholder' => 'e.g. Like Soft Clouds'],
                'desc_1'        => ['type' => 'textarea', 'label' => 'Description Line 1',             'required' => true],
                'desc_2'        => ['type' => 'textarea', 'label' => 'Description Line 2',             'required' => true],
                'btn_text'      => ['type' => 'text',     'label' => 'Discover Button Text',           'required' => false],
                'btn_url'       => ['type' => 'text',     'label' => 'Discover Button URL',            'required' => false],
                'video_url'     => ['type' => 'video',    'label' => 'Rotating Diaper Video URL/Path', 'required' => true],
            ]
        ],
        'atelier_header' => [
            'name'        => 'Sizing Atelier Header',
            'description' => 'The descriptive header of the sensation sizing atelier.',
            'fields'      => [
                'section_subtitle' => ['type' => 'text',     'label' => 'Section Sub-Label',           'required' => true],
                'section_title'    => ['type' => 'text',     'label' => 'Section Title',               'required' => true,  'placeholder' => 'HTML allowed'],
                'content'          => ['type' => 'textarea', 'label' => 'Section Description Text',    'required' => true],
            ]
        ],
        'care_plan' => [
            'name'        => 'BabyCare+ Plan Section',
            'description' => 'The subscription and milestone benefits block.',
            'fields'      => [
                'section_title'  => ['type' => 'text',     'label' => 'Plan Main Title',               'required' => true,  'placeholder' => 'HTML allowed'],
                'content'        => ['type' => 'textarea', 'label' => 'Plan Description Text',         'required' => true],
                'btn_text'       => ['type' => 'text',     'label' => 'CTA Button Text',               'required' => false],
                'btn_url'        => ['type' => 'text',     'label' => 'CTA Button URL',                'required' => false],
                'main_image_url' => ['type' => 'image',    'label' => 'Main Care Plan Diagram Image',  'required' => false],
                'panel_image_1'  => ['type' => 'image',    'label' => 'Perk Panel Image 1',            'required' => false],
                'panel_image_2'  => ['type' => 'image',    'label' => 'Perk Panel Image 2',            'required' => false],
                'panel_image_3'  => ['type' => 'image',    'label' => 'Perk Panel Image 3',            'required' => false],
                'panel_image_4'  => ['type' => 'image',    'label' => 'Perk Panel Image 4',            'required' => false],
            ]
        ],
        'catnav_header' => [
            'name'        => 'Variant Experience Header',
            'description' => 'The titles of the 5-tab scroll experience block.',
            'fields'      => [
                'section_subtitle' => ['type' => 'text', 'label' => 'Section Sub-Label', 'required' => true],
                'section_title'    => ['type' => 'text', 'label' => 'Section Title',     'required' => true],
            ]
        ],
        'philosophy' => [
            'name'        => 'Philosophy Section',
            'description' => 'Full-width cinematic section with brand story, background image, two content panels, and CTA links.',
            'fields'      => [
                'bg_image_url'    => ['type' => 'image',    'label' => 'Background Image',          'required' => false],
                'panel1_eyebrow'  => ['type' => 'text',     'label' => 'Panel 1 Eyebrow Label',     'required' => false, 'placeholder' => 'e.g. THE PHILOSOPHY'],
                'panel1_bold'     => ['type' => 'text',     'label' => 'Panel 1 Bold Intro Phrase', 'required' => false, 'placeholder' => 'e.g. Comfort, Made Simple.'],
                'panel1_text'     => ['type' => 'textarea', 'label' => 'Panel 1 Body Text',          'required' => false, 'placeholder' => 'Full paragraph. HTML entities allowed.'],
                'panel1_btn_text' => ['type' => 'text',     'label' => 'Panel 1 CTA Button Text',   'required' => false],
                'panel1_btn_url'  => ['type' => 'text',     'label' => 'Panel 1 CTA Button URL',    'required' => false],
                'panel2_eyebrow'  => ['type' => 'text',     'label' => 'Panel 2 Eyebrow Label',     'required' => false, 'placeholder' => 'e.g. THE STANDARDS'],
                'panel2_text'     => ['type' => 'textarea', 'label' => 'Panel 2 Body Text',          'required' => false],
                'panel2_btn_text' => ['type' => 'text',     'label' => 'Panel 2 CTA Button Text',   'required' => false],
                'panel2_btn_url'  => ['type' => 'text',     'label' => 'Panel 2 CTA Button URL',    'required' => false],
            ]
        ],
    ];

    if ($key !== null) {
        return $meta[$key] ?? [];
    }
    return $meta;
}
