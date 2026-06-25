<?php
// admin/config/guide-helper.php
// Centralized helper functions for managing the Diaper Guide page content in pages table

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/about-helper.php'; // For handleAboutImageUpload reuse

/**
 * Handle image upload for guide sections — returns absolute URL or null
 */
function handleGuideImageUpload(array $file, string $prefix = 'guide'): ?string {
    return handleAboutImageUpload($file, $prefix); // Reuse the existing about page uploader
}

/**
 * Fetch consolidated Diaper Guide JSON content from pages table.
 */
function getGuidePageData(): array {
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }
    try {
        $db = getDBConnection();
        $stmt = $db->prepare("SELECT content FROM pages WHERE page_name = 'guide' LIMIT 1");
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $cache = json_decode($row['content'], true) ?: [];
            return $cache;
        }
    } catch (Exception $e) {
        error_log("Failed to fetch guide page JSON: " . $e->getMessage());
    }
    return [];
}

/**
 * Save consolidated Diaper Guide JSON content back to pages table.
 */
function saveGuidePageData(array $data): bool {
    try {
        $db = getDBConnection();
        $stmt = $db->prepare("UPDATE pages SET content = :content WHERE page_name = 'guide'");
        return $stmt->execute([':content' => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)]);
    } catch (Exception $e) {
        error_log("Failed to save guide page JSON: " . $e->getMessage());
        return false;
    }
}

/**
 * Fetch all Diaper Guide sections, indexed by their section_key.
 */
function getGuideSections(): array {
    $data = getGuidePageData();
    return $data['sections'] ?? [];
}

/**
 * Fetch a single Diaper Guide section by its unique section_key.
 */
function getGuideSection(string $key): ?array {
    $sections = getGuideSections();
    return $sections[$key] ?? null;
}

/**
 * Update an existing Diaper Guide section by key.
 */
function updateGuideSection(string $key, array $sectionData): bool {
    $data = getGuidePageData();
    if (!isset($data['sections']) || !is_array($data['sections'])) {
        $data['sections'] = [];
    }
    if (!isset($data['sections'][$key]) || !is_array($data['sections'][$key])) {
        $data['sections'][$key] = [];
    }
    
    // Allowed keys to update
    $allowed = ['section_title', 'section_subtitle', 'content', 'btn_text_1', 'btn_url_1', 'btn_text_2', 'btn_url_2'];
    
    foreach ($allowed as $col) {
        if (array_key_exists($col, $sectionData)) {
            $data['sections'][$key][$col] = $sectionData[$col];
        }
    }
    return saveGuidePageData($data);
}

/**
 * Fetch all Diaper Guide timeline milestones
 */
function getGuideTimeline(): array {
    $data = getGuidePageData();
    $timeline = $data['timeline'] ?? [];
    
    usort($timeline, function($a, $b) {
        $orderDiff = intval($a['sort_order'] ?? 0) - intval($b['sort_order'] ?? 0);
        if ($orderDiff === 0) {
            return intval($a['id'] ?? 0) - intval($b['id'] ?? 0);
        }
        return $orderDiff;
    });
    return $timeline;
}

/**
 * Fetch a single timeline milestone by ID
 */
function getGuideTimelineItem(int $id): ?array {
    $data = getGuidePageData();
    $timeline = $data['timeline'] ?? [];
    foreach ($timeline as $t) {
        if (intval($t['id'] ?? 0) === $id) {
            return $t;
        }
    }
    return null;
}

/**
 * Add a new timeline milestone
 */
function addGuideTimelineItem(array $itemData): bool {
    $data = getGuidePageData();
    if (!isset($data['timeline']) || !is_array($data['timeline'])) {
        $data['timeline'] = [];
    }
    
    $maxId = 0;
    foreach ($data['timeline'] as $t) {
        if (intval($t['id'] ?? 0) > $maxId) {
            $maxId = intval($t['id']);
        }
    }
    $newId = $maxId + 1;
    
    $newItem = [
        'id'            => $newId,
        'title'         => trim($itemData['title']),
        'subtitle'      => trim($itemData['subtitle'] ?: 'Milestone 0' . $newId),
        'title_heading' => trim($itemData['title_heading']),
        'description'   => trim($itemData['description']),
        'image_url'     => trim($itemData['image_url'] ?? ''),
        'sort_order'    => intval($itemData['sort_order'] ?: 0)
    ];
    $data['timeline'][] = $newItem;
    return saveGuidePageData($data);
}

/**
 * Update an existing timeline milestone
 */
function updateGuideTimelineItem(int $id, array $itemData): bool {
    $data = getGuidePageData();
    if (!isset($data['timeline']) || !is_array($data['timeline'])) {
        return false;
    }
    $updated = false;
    foreach ($data['timeline'] as &$t) {
        if (intval($t['id'] ?? 0) === $id) {
            $t['title']         = trim($itemData['title']);
            $t['subtitle']      = trim($itemData['subtitle'] ?: $t['subtitle']);
            $t['title_heading'] = trim($itemData['title_heading']);
            $t['description']   = trim($itemData['description']);
            if (isset($itemData['image_url'])) {
                $t['image_url'] = trim($itemData['image_url']);
            }
            $t['sort_order']    = intval($itemData['sort_order'] ?: 0);
            $updated = true;
            break;
        }
    }
    if (!$updated) {
        return false;
    }
    return saveGuidePageData($data);
}

/**
 * Delete a timeline milestone by ID
 */
function deleteGuideTimelineItem(int $id): bool {
    $data = getGuidePageData();
    if (!isset($data['timeline']) || !is_array($data['timeline'])) {
        return false;
    }
    $initialCount = count($data['timeline']);
    $data['timeline'] = array_values(array_filter($data['timeline'], function($t) use ($id) {
        return intval($t['id'] ?? 0) !== $id;
    }));
    if (count($data['timeline']) === $initialCount) {
        return false;
    }
    return saveGuidePageData($data);
}

/**
 * Fetch all Diaper Guide metrics
 */
function getGuideMetrics(): array {
    $data = getGuidePageData();
    $metrics = $data['metrics'] ?? [];
    
    usort($metrics, function($a, $b) {
        $orderDiff = intval($a['sort_order'] ?? 0) - intval($b['sort_order'] ?? 0);
        if ($orderDiff === 0) {
            return intval($a['id'] ?? 0) - intval($b['id'] ?? 0);
        }
        return $orderDiff;
    });
    return $metrics;
}

/**
 * Fetch a single metric by ID
 */
function getGuideMetric(int $id): ?array {
    $data = getGuidePageData();
    $metrics = $data['metrics'] ?? [];
    foreach ($metrics as $m) {
        if (intval($m['id'] ?? 0) === $id) {
            return $m;
        }
    }
    return null;
}

/**
 * Add a new metric card
 */
function addGuideMetric(array $metricData): bool {
    $data = getGuidePageData();
    if (!isset($data['metrics']) || !is_array($data['metrics'])) {
        $data['metrics'] = [];
    }
    
    $maxId = 0;
    foreach ($data['metrics'] as $m) {
        if (intval($m['id'] ?? 0) > $maxId) {
            $maxId = intval($m['id']);
        }
    }
    $newId = $maxId + 1;
    
    $newMetric = [
        'id'           => $newId,
        'icon_class'   => trim($metricData['icon_class'] ?: 'ri-checkbox-circle-line'),
        'target_value' => trim($metricData['target_value']),
        'label'        => trim($metricData['label']),
        'description'  => trim($metricData['description']),
        'suffix_type'  => trim($metricData['suffix_type'] ?: 'none'),
        'decimals'     => intval($metricData['decimals'] ?? 0),
        'sort_order'   => intval($metricData['sort_order'] ?: 0)
    ];
    $data['metrics'][] = $newMetric;
    return saveGuidePageData($data);
}

/**
 * Update an existing metric card
 */
function updateGuideMetric(int $id, array $metricData): bool {
    $data = getGuidePageData();
    if (!isset($data['metrics']) || !is_array($data['metrics'])) {
        return false;
    }
    $updated = false;
    foreach ($data['metrics'] as &$m) {
        if (intval($m['id'] ?? 0) === $id) {
            $m['icon_class']   = trim($metricData['icon_class'] ?: 'ri-checkbox-circle-line');
            $m['target_value'] = trim($metricData['target_value']);
            $m['label']        = trim($metricData['label']);
            $m['description']  = trim($metricData['description']);
            $m['suffix_type']  = trim($metricData['suffix_type'] ?: 'none');
            $m['decimals']     = intval($metricData['decimals'] ?? 0);
            $m['sort_order']   = intval($metricData['sort_order'] ?: 0);
            $updated = true;
            break;
        }
    }
    if (!$updated) {
        return false;
    }
    return saveGuidePageData($data);
}

/**
 * Delete a metric card by ID
 */
function deleteGuideMetric(int $id): bool {
    $data = getGuidePageData();
    if (!isset($data['metrics']) || !is_array($data['metrics'])) {
        return false;
    }
    $initialCount = count($data['metrics']);
    $data['metrics'] = array_values(array_filter($data['metrics'], function($m) use ($id) {
        return intval($m['id'] ?? 0) !== $id;
    }));
    if (count($data['metrics']) === $initialCount) {
        return false;
    }
    return saveGuidePageData($data);
}

/**
 * Fetch all visual story layers
 */
function getGuideLayers(): array {
    $data = getGuidePageData();
    $layers = $data['layers'] ?? [];
    
    usort($layers, function($a, $b) {
        $orderDiff = intval($a['sort_order'] ?? 0) - intval($b['sort_order'] ?? 0);
        if ($orderDiff === 0) {
            return intval($a['id'] ?? 0) - intval($b['id'] ?? 0);
        }
        return $orderDiff;
    });
    return $layers;
}

/**
 * Fetch a single visual story layer by ID
 */
function getGuideLayer(int $id): ?array {
    $data = getGuidePageData();
    $layers = $data['layers'] ?? [];
    foreach ($layers as $l) {
        if (intval($l['id'] ?? 0) === $id) {
            return $l;
        }
    }
    return null;
}

/**
 * Add a new visual story layer
 */
function addGuideLayer(array $layerData): bool {
    $data = getGuidePageData();
    if (!isset($data['layers']) || !is_array($data['layers'])) {
        $data['layers'] = [];
    }
    
    $maxId = 0;
    foreach ($data['layers'] as $l) {
        if (intval($l['id'] ?? 0) > $maxId) {
            $maxId = intval($l['id']);
        }
    }
    $newId = $maxId + 1;
    
    $newLayer = [
        'id'          => $newId,
        'badge'       => trim($layerData['badge'] ?: 'Layer ' . $newId),
        'title'       => trim($layerData['title']),
        'description' => trim($layerData['description']),
        'image_url'   => trim($layerData['image_url'] ?? ''),
        'caption'     => trim($layerData['caption']),
        'specs'       => trim($layerData['specs']),
        'sort_order'  => intval($layerData['sort_order'] ?: 0)
    ];
    $data['layers'][] = $newLayer;
    return saveGuidePageData($data);
}

/**
 * Update an existing visual story layer
 */
function updateGuideLayer(int $id, array $layerData): bool {
    $data = getGuidePageData();
    if (!isset($data['layers']) || !is_array($data['layers'])) {
        return false;
    }
    $updated = false;
    foreach ($data['layers'] as &$l) {
        if (intval($l['id'] ?? 0) === $id) {
            $l['badge']       = trim($layerData['badge'] ?: $l['badge']);
            $l['title']       = trim($layerData['title']);
            $l['description'] = trim($layerData['description']);
            if (isset($layerData['image_url'])) {
                $l['image_url'] = trim($layerData['image_url']);
            }
            $l['caption']     = trim($layerData['caption']);
            $l['specs']       = trim($layerData['specs']);
            $l['sort_order']  = intval($layerData['sort_order'] ?: 0);
            $updated = true;
            break;
        }
    }
    if (!$updated) {
        return false;
    }
    return saveGuidePageData($data);
}

/**
 * Delete a visual story layer by ID
 */
function deleteGuideLayer(int $id): bool {
    $data = getGuidePageData();
    if (!isset($data['layers']) || !is_array($data['layers'])) {
        return false;
    }
    $initialCount = count($data['layers']);
    $data['layers'] = array_values(array_filter($data['layers'], function($l) use ($id) {
        return intval($l['id'] ?? 0) !== $id;
    }));
    if (count($data['layers']) === $initialCount) {
        return false;
    }
    return saveGuidePageData($data);
}

/**
 * Get configuration metadata for form rendering constraints on each section key.
 */
function getGuideSectionMetadata(string $key = null): array {
    $meta = [
        'hero' => [
            'name'        => 'Hero Banner Section',
            'description' => 'The top-most header section on the Diaper Guide page.',
            'fields'      => [
                'section_subtitle' => ['type' => 'text', 'label' => 'Sub-Label', 'required' => true, 'placeholder' => 'e.g. Interactive Experience'],
                'section_title'    => ['type' => 'text', 'label' => 'Hero Main Title', 'required' => true, 'placeholder' => 'e.g. A Journey of Touch, Safety, and Softness.'],
                'content'          => ['type' => 'textarea', 'label' => 'Hero Description text', 'required' => true, 'placeholder' => 'Write a short intro paragraph…']
            ]
        ],
        'quote' => [
            'name'        => 'Pediatrician Quote Section',
            'description' => 'The editorial block highlighting recommendations from pediatricians.',
            'fields'      => [
                'content'          => ['type' => 'textarea', 'label' => 'Quote Text', 'required' => true, 'placeholder' => 'Write quote here…'],
                'section_title'    => ['type' => 'text', 'label' => 'Author Name', 'required' => true, 'placeholder' => 'e.g. Dr. Anjali Sen, MD'],
                'section_subtitle' => ['type' => 'text', 'label' => 'Author Subtitle/Role', 'required' => true, 'placeholder' => 'e.g. Consultant Pediatric Dermatologist']
            ]
        ],
        'cta' => [
            'name'        => 'Final CTA Section',
            'description' => 'The call-to-action block at the bottom of the page before the footer.',
            'fields'      => [
                'section_subtitle' => ['type' => 'text', 'label' => 'CTA Eyebrow', 'required' => true, 'placeholder' => 'e.g. Designed for peaceful nights'],
                'section_title'    => ['type' => 'text', 'label' => 'CTA Heading', 'required' => true, 'placeholder' => 'e.g. Softness that lasts, safety you can trust.'],
                'content'          => ['type' => 'textarea', 'label' => 'CTA Description text', 'required' => true, 'placeholder' => 'Write a short description…'],
                'btn_text_1'       => ['type' => 'text', 'label' => 'Button 1 Text', 'required' => false, 'placeholder' => 'e.g. Explore Diapers'],
                'btn_url_1'        => ['type' => 'text', 'label' => 'Button 1 URL', 'required' => false, 'placeholder' => 'e.g. products.php'],
                'btn_text_2'       => ['type' => 'text', 'label' => 'Button 2 Text', 'required' => false, 'placeholder' => 'e.g. Why CloudCush'],
                'btn_url_2'        => ['type' => 'text', 'label' => 'Button 2 URL', 'required' => false, 'placeholder' => 'e.g. about.php']
            ]
        ],
        'metrics_header' => [
            'name'        => 'Metrics Section Header',
            'description' => 'The headings for the Dermatological standards metrics grid.',
            'fields'      => [
                'section_subtitle' => ['type' => 'text', 'label' => 'Metrics Label', 'required' => true, 'placeholder' => 'e.g. The Proof in Comfort'],
                'section_title'    => ['type' => 'text', 'label' => 'Metrics Title', 'required' => true, 'placeholder' => 'e.g. Dermatological Standards. Proven Results.']
            ]
        ]
    ];

    if ($key !== null) {
        return $meta[$key] ?? [];
    }
    return $meta;
}
