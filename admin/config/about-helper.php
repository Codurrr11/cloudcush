<?php
// admin/config/about-helper.php
// Centralized helper functions for managing the About page sections

require_once __DIR__ . '/database.php';

/**
 * Fetch consolidated About Page JSON content from pages table.
 */
function getAboutPageData(): array {
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }
    try {
        $db = getDBConnection();
        $stmt = $db->prepare("SELECT content FROM pages WHERE page_name = 'about' LIMIT 1");
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $cache = json_decode($row['content'], true) ?: [];
            return $cache;
        }
    } catch (Exception $e) {
        error_log("Failed to fetch about page JSON: " . $e->getMessage());
    }
    return [];
}

/**
 * Save consolidated About Page JSON content back to pages table.
 */
function saveAboutPageData(array $data): bool {
    try {
        $db = getDBConnection();
        $stmt = $db->prepare("UPDATE pages SET content = :content WHERE page_name = 'about'");
        return $stmt->execute([':content' => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)]);
    } catch (Exception $e) {
        error_log("Failed to save about page JSON: " . $e->getMessage());
        return false;
    }
}

/**
 * Fetch all About Page sections, indexed by their section_key.
 */
function getAboutSections(): array {
    $data = getAboutPageData();
    return $data['sections'] ?? [];
}

/**
 * Fetch a single About Page section by its unique section_key.
 */
function getAboutSection(string $key): ?array {
    $sections = getAboutSections();
    return $sections[$key] ?? null;
}

/**
 * Update an existing About Page section by key.
 */
function updateAboutSection(string $key, array $sectionData): bool {
    $data = getAboutPageData();
    if (!isset($data['sections']) || !is_array($data['sections'])) {
        $data['sections'] = [];
    }
    if (!isset($data['sections'][$key]) || !is_array($data['sections'][$key])) {
        $data['sections'][$key] = [];
    }
    
    // Allowed keys to update
    $allowed = ['section_title', 'section_subtitle', 'content', 'accent_text', 'image_url', 'btn_text_1', 'btn_url_1', 'btn_text_2', 'btn_url_2'];
    
    foreach ($allowed as $col) {
        if (array_key_exists($col, $sectionData)) {
            $data['sections'][$key][$col] = $sectionData[$col];
        }
    }
    return saveAboutPageData($data);
}

/**
 * Handle image upload for about sections — returns absolute URL or null
 */
function handleAboutImageUpload(array $file, string $prefix = 'about'): ?string {
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
 * Get configuration metadata for form rendering constraints on each section key.
 */
function getSectionMetadata(string $key = null): array {
    $meta = [
        'hero' => [
            'name'        => 'Hero Banner Section',
            'description' => 'The top-most header section on the About Page with main heading, description and action link hooks.',
            'fields'      => [
                'section_subtitle' => ['type' => 'text', 'label' => 'Sub-Label', 'required' => true, 'placeholder' => 'e.g. About CloudCush'],
                'section_title'    => ['type' => 'text', 'label' => 'Hero Main Title', 'required' => true, 'placeholder' => 'HTML allowed, e.g. Design for Parents.'],
                'content'          => ['type' => 'textarea', 'label' => 'Hero Description text', 'required' => true, 'placeholder' => 'Write a short intro paragraph…']
            ]
        ],
        'story_1' => [
            'name'        => 'Story Card 1 — The Origin',
            'description' => 'First block inside the horizontal scrolling storytelling section.',
            'fields'      => [
                'section_subtitle' => ['type' => 'text', 'label' => 'Card Label', 'required' => true, 'placeholder' => 'e.g. 01 / THE ORIGIN'],
                'section_title'    => ['type' => 'text', 'label' => 'Card Title', 'required' => true, 'placeholder' => 'e.g. Crafted in Kota.'],
                'content'          => ['type' => 'editor', 'label' => 'Card Story Content', 'required' => true, 'placeholder' => 'Details about the origin…'],
                'accent_text'      => ['type' => 'text', 'label' => 'Accent Bottom text', 'required' => false, 'placeholder' => 'A short tagline at the bottom of the card.'],
                'image_url'        => ['type' => 'image', 'label' => 'Card Feature Image', 'required' => false]
            ]
        ],
        'story_2' => [
            'name'        => 'Story Card 2 — The Pledge',
            'description' => 'Second block inside the horizontal scrolling storytelling section.',
            'fields'      => [
                'section_subtitle' => ['type' => 'text', 'label' => 'Card Label', 'required' => true, 'placeholder' => 'e.g. 02 / THE PLEDGE'],
                'section_title'    => ['type' => 'text', 'label' => 'Card Title', 'required' => true, 'placeholder' => 'e.g. Chlorine-Free Safety.'],
                'content'          => ['type' => 'editor', 'label' => 'Card Story Content', 'required' => true, 'placeholder' => 'Details about the materials/pledge…'],
                'accent_text'      => ['type' => 'text', 'label' => 'Accent Bottom text', 'required' => false, 'placeholder' => 'A short tagline at the bottom of the card.'],
                'image_url'        => ['type' => 'image', 'label' => 'Card Feature Image', 'required' => false]
            ]
        ],
        'story_3' => [
            'name'        => 'Story Card 3 — The Future',
            'description' => 'Third block inside the horizontal scrolling storytelling section.',
            'fields'      => [
                'section_subtitle' => ['type' => 'text', 'label' => 'Card Label', 'required' => true, 'placeholder' => 'e.g. 03 / THE FUTURE'],
                'section_title'    => ['type' => 'text', 'label' => 'Card Title', 'required' => true, 'placeholder' => 'e.g. For Modern Indian Families.'],
                'content'          => ['type' => 'editor', 'label' => 'Card Story Content', 'required' => true, 'placeholder' => 'Details about the family vision…'],
                'accent_text'      => ['type' => 'text', 'label' => 'Accent Bottom text', 'required' => false, 'placeholder' => 'A short tagline at the bottom of the card.'],
                'image_url'        => ['type' => 'image', 'label' => 'Card Feature Image', 'required' => false]
            ]
        ],
        'philosophy' => [
            'name'        => 'Philosophy Section',
            'description' => 'The large parallax quote and lifestyle image section detailing brand vision.',
            'fields'      => [
                'section_subtitle' => ['type' => 'text', 'label' => 'Section Label', 'required' => true, 'placeholder' => 'e.g. Our Philosophy'],
                'section_title'    => ['type' => 'textarea', 'label' => 'Philosophy Quote', 'required' => true, 'placeholder' => 'Double quotes quote text…'],
                'image_url'        => ['type' => 'image', 'label' => 'Right Side Parallax Image', 'required' => false]
            ]
        ],
        'features_header' => [
            'name'        => 'Features Section Header',
            'description' => 'The title and label for the "Why Choose CloudCush" grid section.',
            'fields'      => [
                'section_subtitle' => ['type' => 'text', 'label' => 'Features Label', 'required' => true, 'placeholder' => 'e.g. Features'],
                'section_title'    => ['type' => 'text', 'label' => 'Features Title', 'required' => true, 'placeholder' => 'e.g. Thoughtful Protection, Reimagined.']
            ]
        ],
        'about_faq_header' => [
            'name'        => 'FAQ Section Header',
            'description' => 'The title and label for the dynamic FAQ section on the About Page.',
            'fields'      => [
                'section_subtitle' => ['type' => 'text', 'label' => 'FAQ Section Label', 'required' => true, 'placeholder' => 'e.g. Got Questions?'],
                'section_title'    => ['type' => 'text', 'label' => 'FAQ Section Title', 'required' => true, 'placeholder' => 'e.g. Frequently Asked Questions']
            ]
        ],
        'cta' => [
            'name'        => 'Final CTA Section',
            'description' => 'The action prompt block at the bottom of the page before the footer.',
            'fields'      => [
                'section_title' => ['type' => 'text', 'label' => 'CTA Heading', 'required' => true, 'placeholder' => 'e.g. Made for Better Baby Days.'],
                'content'       => ['type' => 'textarea', 'label' => 'CTA Description text', 'required' => true, 'placeholder' => 'Write a short description to invite actions…'],
                'btn_text_1'    => ['type' => 'text', 'label' => 'Button 1 Text', 'required' => false, 'placeholder' => 'e.g. Shop Collection'],
                'btn_url_1'     => ['type' => 'text', 'label' => 'Button 1 URL', 'required' => false, 'placeholder' => 'e.g. products.php'],
                'btn_text_2'    => ['type' => 'text', 'label' => 'Button 2 Text', 'required' => false, 'placeholder' => 'e.g. Explore Diaper Guide'],
                'btn_url_2'     => ['type' => 'text', 'label' => 'Button 2 URL', 'required' => false, 'placeholder' => 'e.g. diaper-guide.php']
            ]
        ]
    ];

    if ($key !== null) {
        return $meta[$key] ?? [];
    }
    return $meta;
}

/**
 * Fetch all About Page dynamic features
 */
function getAboutFeatures(): array {
    $data = getAboutPageData();
    $features = $data['features'] ?? [];
    
    usort($features, function($a, $b) {
        $orderDiff = intval($a['sort_order'] ?? 0) - intval($b['sort_order'] ?? 0);
        if ($orderDiff === 0) {
            return intval($a['id'] ?? 0) - intval($b['id'] ?? 0);
        }
        return $orderDiff;
    });
    return $features;
}

/**
 * Fetch a single About Page feature by ID
 */
function getAboutFeature(int $id): ?array {
    $data = getAboutPageData();
    $features = $data['features'] ?? [];
    foreach ($features as $f) {
        if (intval($f['id'] ?? 0) === $id) {
            return $f;
        }
    }
    return null;
}

/**
 * Add a new About Page feature
 */
function addAboutFeature(array $featureData): bool {
    $data = getAboutPageData();
    if (!isset($data['features']) || !is_array($data['features'])) {
        $data['features'] = [];
    }
    
    $maxId = 0;
    foreach ($data['features'] as $f) {
        if (intval($f['id'] ?? 0) > $maxId) {
            $maxId = intval($f['id']);
        }
    }
    $newId = $maxId + 1;
    
    $newFeature = [
        'id'          => $newId,
        'title'       => trim($featureData['title']),
        'description' => trim($featureData['description']),
        'icon_class'  => trim($featureData['icon_class'] ?: 'ri-checkbox-circle-line'),
        'sort_order'  => intval($featureData['sort_order'] ?: 0)
    ];
    $data['features'][] = $newFeature;
    return saveAboutPageData($data);
}

/**
 * Update an existing About Page feature
 */
function updateAboutFeature(int $id, array $featureData): bool {
    $data = getAboutPageData();
    if (!isset($data['features']) || !is_array($data['features'])) {
        return false;
    }
    $updated = false;
    foreach ($data['features'] as &$f) {
        if (intval($f['id'] ?? 0) === $id) {
            $f['title']       = trim($featureData['title']);
            $f['description'] = trim($featureData['description']);
            $f['icon_class']  = trim($featureData['icon_class'] ?: 'ri-checkbox-circle-line');
            $f['sort_order']  = intval($featureData['sort_order'] ?: 0);
            $updated = true;
            break;
        }
    }
    if (!$updated) {
        return false;
    }
    return saveAboutPageData($data);
}

/**
 * Delete an About Page feature by ID
 */
function deleteAboutFeature(int $id): bool {
    $data = getAboutPageData();
    if (!isset($data['features']) || !is_array($data['features'])) {
        return false;
    }
    $initialCount = count($data['features']);
    $data['features'] = array_values(array_filter($data['features'], function($f) use ($id) {
        return intval($f['id'] ?? 0) !== $id;
    }));
    if (count($data['features']) === $initialCount) {
        return false;
    }
    return saveAboutPageData($data);
}

/**
 * Fetch all About Page dynamic FAQs
 */
function getAboutFaqs(): array {
    $data = getAboutPageData();
    $faqs = $data['faqs'] ?? [];
    
    usort($faqs, function($a, $b) {
        $orderDiff = intval($a['sort_order'] ?? 0) - intval($b['sort_order'] ?? 0);
        if ($orderDiff === 0) {
            return intval($a['id'] ?? 0) - intval($b['id'] ?? 0);
        }
        return $orderDiff;
    });
    return $faqs;
}

/**
 * Fetch a single About Page FAQ by ID
 */
function getAboutFaq(int $id): ?array {
    $data = getAboutPageData();
    $faqs = $data['faqs'] ?? [];
    foreach ($faqs as $faq) {
        if (intval($faq['id'] ?? 0) === $id) {
            return $faq;
        }
    }
    return null;
}

/**
 * Add a new About Page FAQ
 */
function addAboutFaq(array $faqData): bool {
    $data = getAboutPageData();
    if (!isset($data['faqs']) || !is_array($data['faqs'])) {
        $data['faqs'] = [];
    }
    $maxId = 0;
    foreach ($data['faqs'] as $faq) {
        if (intval($faq['id'] ?? 0) > $maxId) {
            $maxId = intval($faq['id']);
        }
    }
    $newId = $maxId + 1;
    
    $newFaq = [
        'id'         => $newId,
        'question'   => trim($faqData['question']),
        'answer'     => trim($faqData['answer']),
        'sort_order' => intval($faqData['sort_order'] ?? 0)
    ];
    $data['faqs'][] = $newFaq;
    return saveAboutPageData($data);
}

/**
 * Update an existing About Page FAQ
 */
function updateAboutFaq(int $id, array $faqData): bool {
    $data = getAboutPageData();
    if (!isset($data['faqs']) || !is_array($data['faqs'])) {
        return false;
    }
    $updated = false;
    foreach ($data['faqs'] as &$faq) {
        if (intval($faq['id'] ?? 0) === $id) {
            $faq['question']   = trim($faqData['question']);
            $faq['answer']     = trim($faqData['answer']);
            $faq['sort_order'] = intval($faqData['sort_order'] ?? 0);
            $updated = true;
            break;
        }
    }
    if (!$updated) {
        return false;
    }
    return saveAboutPageData($data);
}

/**
 * Delete an About Page FAQ by ID
 */
function deleteAboutFaq(int $id): bool {
    $data = getAboutPageData();
    if (!isset($data['faqs']) || !is_array($data['faqs'])) {
        return false;
    }
    $initialCount = count($data['faqs']);
    $data['faqs'] = array_values(array_filter($data['faqs'], function($faq) use ($id) {
        return intval($faq['id'] ?? 0) !== $id;
    }));
    if (count($data['faqs']) === $initialCount) {
        return false;
    }
    return saveAboutPageData($data);
}


