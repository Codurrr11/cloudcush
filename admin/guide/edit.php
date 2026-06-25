<?php
// admin/guide/edit.php — Unified Edit & Add Form for Diaper Guide Sections and Items
require_once __DIR__ . '/../includes/auth-check.php';
require_once __DIR__ . '/../config/guide-helper.php';

$sectionKey = trim($_GET['section'] ?? '');
$itemType = trim($_GET['type'] ?? '');
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

$isSection = !empty($sectionKey);
$isItem = !empty($itemType);

if (!$isSection && !$isItem) {
    $_SESSION['flash_message'] = 'Missing section or item identifier.';
    $_SESSION['flash_type']    = 'error';
    header('Location: ' . BASE_URL . 'guide/');
    exit;
}

if ($isSection) {
    $metadata = getGuideSectionMetadata($sectionKey);
    if (empty($metadata)) {
        $_SESSION['flash_message'] = 'Invalid diaper guide section identifier.';
        $_SESSION['flash_type']    = 'error';
        header('Location: ' . BASE_URL . 'guide/');
        exit;
    }
    $section = getGuideSection($sectionKey);
    if (!$section) {
        $_SESSION['flash_message'] = 'Section data not found.';
        $_SESSION['flash_type']    = 'error';
        header('Location: ' . BASE_URL . 'guide/');
        exit;
    }
} elseif ($isItem) {
    if (!in_array($itemType, ['timeline', 'metric', 'layer'])) {
        $_SESSION['flash_message'] = 'Invalid item type.';
        $_SESSION['flash_type']    = 'error';
        header('Location: ' . BASE_URL . 'guide/');
        exit;
    }
    
    $isEdit = !empty($id);
    $item = null;
    if ($isEdit) {
        if ($itemType === 'timeline') {
            $item = getGuideTimelineItem($id);
        } elseif ($itemType === 'metric') {
            $item = getGuideMetric($id);
        } elseif ($itemType === 'layer') {
            $item = getGuideLayer($id);
        }
        if (!$item) {
            $_SESSION['flash_message'] = 'Requested item not found.';
            $_SESSION['flash_type']    = 'error';
            header('Location: ' . BASE_URL . 'guide/?tab=' . ($itemType === 'timeline' ? 'timeline' : ($itemType === 'metric' ? 'metrics' : 'layers')));
            exit;
        }
    }
}

// POST submission handler
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];
    if ($isSection) {
        $updateData = [];
        foreach ($metadata['fields'] as $fieldKey => $fieldMeta) {
            $val = $_POST[$fieldKey] ?? '';
            if ($fieldMeta['type'] === 'editor' || $fieldMeta['type'] === 'textarea') {
                $val = trim(strip_tags($val, '<p><a><strong><em><b><i><u><ul><ol><li><br><hr><h1><h2><h3><h4><h5><h6><div><span>'));
            } else {
                $val = trim(strip_tags($val));
            }
            if (empty($val) && !empty($fieldMeta['required'])) {
                $errors[] = htmlspecialchars($fieldMeta['label']) . ' is required.';
            }
            $updateData[$fieldKey] = $val;
        }
        
        if (empty($errors)) {
            if (updateGuideSection($sectionKey, $updateData)) {
                $_SESSION['flash_message'] = $metadata['name'] . ' updated successfully.';
                $_SESSION['flash_type']    = 'success';
                header('Location: ' . BASE_URL . 'guide/?tab=' . ($sectionKey === 'metrics_header' ? 'metrics' : $sectionKey));
                exit;
            } else {
                $_SESSION['flash_message'] = 'Failed to update section in database.';
                $_SESSION['flash_type']    = 'error';
            }
        } else {
            $_SESSION['flash_message'] = implode(' ', $errors);
            $_SESSION['flash_type']    = 'error';
        }
    } elseif ($isItem) {
        if ($itemType === 'timeline') {
            $title         = trim(strip_tags($_POST['title'] ?? ''));
            $subtitle      = trim(strip_tags($_POST['subtitle'] ?? ''));
            $title_heading = trim(strip_tags($_POST['title_heading'] ?? ''));
            $description   = trim(strip_tags($_POST['description'] ?? '', '<p><a><strong><em><b><i><u><ul><ol><li><br><hr><h1><h2><h3><h4><h5><h6><div><span>'));
            $sort_order    = filter_input(INPUT_POST, 'sort_order', FILTER_VALIDATE_INT) ?? 0;
            
            if (empty($title)) $errors[] = 'Milestone Title is required.';
            if (empty($title_heading)) $errors[] = 'Title Heading is required.';
            if (empty($description)) $errors[] = 'Description is required.';
            
            $imageUrl = $isEdit ? $item['image_url'] : '';
            if (!empty($_FILES['image_file']['name'])) {
                try {
                    $uploaded = handleGuideImageUpload($_FILES['image_file'], 'timeline');
                    if ($uploaded) {
                        $imageUrl = $uploaded;
                        if ($isEdit && $item['image_url'] && str_contains($item['image_url'], UPLOAD_URL)) {
                            $localPath = str_replace(UPLOAD_URL, UPLOAD_DIR, $item['image_url']);
                            if (file_exists($localPath)) @unlink($localPath);
                        }
                    }
                } catch (Exception $e) {
                    $errors[] = $e->getMessage();
                }
            }
            
            if (empty($errors)) {
                $payload = [
                    'title'         => $title,
                    'subtitle'      => $subtitle,
                    'title_heading' => $title_heading,
                    'description'   => $description,
                    'image_url'     => $imageUrl,
                    'sort_order'    => $sort_order
                ];
                $res = $isEdit ? updateGuideTimelineItem($id, $payload) : addGuideTimelineItem($payload);
                if ($res) {
                    $_SESSION['flash_message'] = 'Timeline milestone ' . ($isEdit ? 'updated' : 'added') . ' successfully.';
                    $_SESSION['flash_type']    = 'success';
                    header('Location: ' . BASE_URL . 'guide/?tab=timeline');
                    exit;
                } else {
                    $_SESSION['flash_message'] = 'Failed to save timeline milestone.';
                    $_SESSION['flash_type']    = 'error';
                }
            } else {
                $_SESSION['flash_message'] = implode(' ', $errors);
                $_SESSION['flash_type']    = 'error';
            }
            
        } elseif ($itemType === 'metric') {
            $icon_class   = trim(strip_tags($_POST['icon_class'] ?? ''));
            $target_value = trim(strip_tags($_POST['target_value'] ?? ''));
            $label        = trim(strip_tags($_POST['label'] ?? ''));
            $description  = trim(strip_tags($_POST['description'] ?? '', '<p><a><strong><em><b><i><u><ul><ol><li><br><hr><h1><h2><h3><h4><h5><h6><div><span>'));
            $suffix_type  = trim(strip_tags($_POST['suffix_type'] ?? 'none'));
            $decimals     = filter_input(INPUT_POST, 'decimals', FILTER_VALIDATE_INT) ?? 0;
            $sort_order   = filter_input(INPUT_POST, 'sort_order', FILTER_VALIDATE_INT) ?? 0;
            
            if (empty($label)) $errors[] = 'Metric Label is required.';
            if (empty($target_value)) $errors[] = 'Target Value is required.';
            if (empty($description)) $errors[] = 'Description is required.';
            
            if (empty($errors)) {
                $payload = [
                    'icon_class'   => $icon_class,
                    'target_value' => $target_value,
                    'label'        => $label,
                    'description'  => $description,
                    'suffix_type'  => $suffix_type,
                    'decimals'     => $decimals,
                    'sort_order'   => $sort_order
                ];
                $res = $isEdit ? updateGuideMetric($id, $payload) : addGuideMetric($payload);
                if ($res) {
                    $_SESSION['flash_message'] = 'Comfort metric ' . ($isEdit ? 'updated' : 'added') . ' successfully.';
                    $_SESSION['flash_type']    = 'success';
                    header('Location: ' . BASE_URL . 'guide/?tab=metrics');
                    exit;
                } else {
                    $_SESSION['flash_message'] = 'Failed to save comfort metric.';
                    $_SESSION['flash_type']    = 'error';
                }
            } else {
                $_SESSION['flash_message'] = implode(' ', $errors);
                $_SESSION['flash_type']    = 'error';
            }
            
        } elseif ($itemType === 'layer') {
            $badge       = trim(strip_tags($_POST['badge'] ?? ''));
            $title       = trim(strip_tags($_POST['title'] ?? ''));
            $caption     = trim(strip_tags($_POST['caption'] ?? ''));
            $specs       = trim(strip_tags($_POST['specs'] ?? ''));
            $description = trim(strip_tags($_POST['description'] ?? '', '<p><a><strong><em><b><i><u><ul><ol><li><br><hr><h1><h2><h3><h4><h5><h6><div><span>'));
            $sort_order  = filter_input(INPUT_POST, 'sort_order', FILTER_VALIDATE_INT) ?? 0;
            
            if (empty($title)) $errors[] = 'Layer Title is required.';
            if (empty($description)) $errors[] = 'Description is required.';
            
            $imageUrl = $isEdit ? $item['image_url'] : '';
            if (!empty($_FILES['image_file']['name'])) {
                try {
                    $uploaded = handleGuideImageUpload($_FILES['image_file'], 'layer');
                    if ($uploaded) {
                        $imageUrl = $uploaded;
                        if ($isEdit && $item['image_url'] && str_contains($item['image_url'], UPLOAD_URL)) {
                            $localPath = str_replace(UPLOAD_URL, UPLOAD_DIR, $item['image_url']);
                            if (file_exists($localPath)) @unlink($localPath);
                        }
                    }
                } catch (Exception $e) {
                    $errors[] = $e->getMessage();
                }
            }
            
            if (empty($errors)) {
                $payload = [
                    'badge'       => $badge,
                    'title'       => $title,
                    'caption'     => $caption,
                    'specs'       => $specs,
                    'description' => $description,
                    'image_url'   => $imageUrl,
                    'sort_order'  => $sort_order
                ];
                $res = $isEdit ? updateGuideLayer($id, $payload) : addGuideLayer($payload);
                if ($res) {
                    $_SESSION['flash_message'] = 'Visual story layer ' . ($isEdit ? 'updated' : 'added') . ' successfully.';
                    $_SESSION['flash_type']    = 'success';
                    header('Location: ' . BASE_URL . 'guide/?tab=layers');
                    exit;
                } else {
                    $_SESSION['flash_message'] = 'Failed to save visual story layer.';
                    $_SESSION['flash_type']    = 'error';
                }
            } else {
                $_SESSION['flash_message'] = implode(' ', $errors);
                $_SESSION['flash_type']    = 'error';
            }
        }
    }
}

// Layout parameters
if ($isSection) {
    $page_title  = 'CloudCush Admin — Edit ' . $metadata['name'];
    $backUrl     = BASE_URL . 'guide/?tab=' . ($sectionKey === 'metrics_header' ? 'metrics' : $sectionKey);
    $headerTitle = 'Edit ' . htmlspecialchars($metadata['name']);
    $headerDesc  = htmlspecialchars($metadata['description']);
    $hasImage    = false;
} else {
    $actionLabel = $isEdit ? 'Edit' : 'Add';
    $hasImage    = in_array($itemType, ['timeline', 'layer']);
    if ($itemType === 'timeline') {
        $page_title  = "CloudCush Admin — {$actionLabel} Guide Milestone";
        $backUrl     = BASE_URL . 'guide/?tab=timeline';
        $headerTitle = "{$actionLabel} Guide Milestone";
        $headerDesc  = $isEdit ? 'Modify milestone card settings for the Diaper Guide scrolling timeline.' : 'Create a new milestone card for the Diaper Guide scrolling timeline.';
    } elseif ($itemType === 'metric') {
        $page_title  = "CloudCush Admin — {$actionLabel} Comfort Metric";
        $backUrl     = BASE_URL . 'guide/?tab=metrics';
        $headerTitle = "{$actionLabel} Comfort Metric";
        $headerDesc  = $isEdit ? 'Modify comfort metric card settings for the Diaper Guide section.' : 'Create a new comfort metric card for the Diaper Guide section.';
    } else {
        $page_title  = "CloudCush Admin — {$actionLabel} Visual Layer";
        $backUrl     = BASE_URL . 'guide/?tab=layers';
        $headerTitle = "{$actionLabel} Visual Layer";
        $headerDesc  = $isEdit ? 'Modify diaper layering details card for the visual story block.' : 'Create a new diaper layering details card for the visual story block.';
    }
}

$active_page = 'guide';
$fieldIcons = [
    'section_subtitle' => 'tag',
    'section_title'    => 'heading',
    'content'          => 'align-left',
    'btn_text_1'       => 'square-play',
    'btn_url_1'        => 'link',
    'btn_text_2'       => 'square-play',
    'btn_url_2'        => 'link'
];

include __DIR__ . '/../includes/header.php';
?>

<div id="wrapper">
    <?php include __DIR__ . '/../includes/sidebar.php'; ?>

    <div id="page-content-wrapper">
        <?php include __DIR__ . '/../includes/navbar.php'; ?>

        <div class="container-fluid px-0 py-2">

            <!-- Page Header -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <a href="<?= $backUrl ?>" class="btn-action btn-back-square" title="Back to Settings">
                            <i data-lucide="arrow-left" class="icon-sm"></i>
                        </a>
                        <h1 class="h4 fw-bold mb-0 page-heading"><?= $headerTitle ?></h1>
                    </div>
                    <p class="text-secondary mb-0 fs-0-82 pl-2-25">
                        <?= $headerDesc ?>
                    </p>
                </div>
            </div>

            <form action="" method="POST" enctype="multipart/form-data" novalidate>
                <div class="row g-4">
                    
                    <!-- Form Column -->
                    <div class="<?= $hasImage ? 'col-12 col-xl-8' : 'col-12 max-width-800-px' ?>">
                        <div class="card-premium p-4">
                            <div class="d-flex align-items-center gap-2 mb-4 border-bottom pb-2">
                                <i data-lucide="file-edit" class="text-primary" style="width: 18px; height: 18px;"></i>
                                <span class="form-section-label mb-0" style="font-size:0.88rem;">Details</span>
                            </div>

                            <div class="d-flex flex-column gap-3">
                                <?php if ($isSection): ?>
                                    <!-- Editorial Sections Form fields -->
                                    <?php 
                                    foreach ($metadata['fields'] as $fieldKey => $fieldMeta):
                                        $requiredStar = !empty($fieldMeta['required']) ? ' <span class="req">*</span>' : '';
                                        $val = $_POST[$fieldKey] ?? ($section[$fieldKey] ?? '');
                                        $iconName = $fieldIcons[$fieldKey] ?? 'edit-3';
                                    ?>
                                        <div class="form-group-wrap">
                                            <div class="d-flex align-items-center gap-2 mb-2">
                                                <i data-lucide="<?= $iconName ?>" class="text-muted" style="width: 14px; height: 14px;"></i>
                                                <label class="form-label-premium mb-0" for="guide_<?= $fieldKey ?>">
                                                    <?= htmlspecialchars($fieldMeta['label']) ?><?= $requiredStar ?>
                                                </label>
                                            </div>
                                            
                                            <?php if ($fieldMeta['type'] === 'text'): ?>
                                                <input type="text" id="guide_<?= $fieldKey ?>" name="<?= $fieldKey ?>"
                                                       class="form-control-premium" <?= !empty($fieldMeta['required']) ? 'required' : '' ?>
                                                       placeholder="<?= htmlspecialchars($fieldMeta['placeholder'] ?? '') ?>"
                                                       value="<?= htmlspecialchars($val) ?>">
                                            <?php elseif ($fieldMeta['type'] === 'textarea'): ?>
                                                <div class="tinymce-wrap">
                                                    <textarea id="guide_<?= $fieldKey ?>" name="<?= $fieldKey ?>"
                                                              class="tinymce-editor" rows="6" <?= !empty($fieldMeta['required']) ? 'required' : '' ?>
                                                              placeholder="<?= htmlspecialchars($fieldMeta['placeholder'] ?? '') ?>"><?= htmlspecialchars($val) ?></textarea>
                                                </div>
                                            <?php elseif ($fieldMeta['type'] === 'editor'): ?>
                                                <div class="tinymce-wrap">
                                                    <textarea id="guide_<?= $fieldKey ?>" name="<?= $fieldKey ?>"
                                                              class="tinymce-editor" rows="10"><?= htmlspecialchars($val) ?></textarea>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>

                                <?php elseif ($isItem && $itemType === 'timeline'): ?>
                                    <!-- Timeline Milestone Fields -->
                                    <!-- Title -->
                                    <div class="form-group-wrap">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <i data-lucide="tag" class="text-muted" style="width: 14px; height: 14px;"></i>
                                            <label class="form-label-premium mb-0" for="t_title">
                                                Timeline Title <span class="req">*</span>
                                            </label>
                                        </div>
                                        <input type="text" id="t_title" name="title"
                                               class="form-control-premium" required
                                               placeholder="e.g. Gentle Materials"
                                               value="<?= htmlspecialchars($_POST['title'] ?? ($item['title'] ?? '')) ?>">
                                    </div>

                                    <!-- Subtitle / Milestone Label -->
                                    <div class="form-group-wrap">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <i data-lucide="hash" class="text-muted" style="width: 14px; height: 14px;"></i>
                                            <label class="form-label-premium mb-0" for="t_subtitle">
                                                Milestone Label
                                            </label>
                                        </div>
                                        <input type="text" id="t_subtitle" name="subtitle"
                                               class="form-control-premium"
                                               placeholder="e.g. Milestone 01"
                                               value="<?= htmlspecialchars($_POST['subtitle'] ?? ($item['subtitle'] ?? '')) ?>">
                                    </div>

                                    <!-- Title Heading -->
                                    <div class="form-group-wrap">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <i data-lucide="heading" class="text-muted" style="width: 14px; height: 14px;"></i>
                                            <label class="form-label-premium mb-0" for="t_title_heading">
                                                Title Heading <span class="req">*</span>
                                            </label>
                                        </div>
                                        <input type="text" id="t_title_heading" name="title_heading"
                                               class="form-control-premium" required
                                               placeholder="e.g. Fiber-Level Pureness"
                                               value="<?= htmlspecialchars($_POST['title_heading'] ?? ($item['title_heading'] ?? '')) ?>">
                                    </div>

                                    <!-- Description -->
                                    <div class="form-group-wrap">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <i data-lucide="align-left" class="text-muted" style="width: 14px; height: 14px;"></i>
                                            <label class="form-label-premium mb-0" for="t_description">
                                                Description text <span class="req">*</span>
                                            </label>
                                        </div>
                                        <div class="tinymce-wrap">
                                            <textarea id="t_description" name="description"
                                                      class="tinymce-editor" rows="5" required
                                                      placeholder="Provide details about this milestone…"><?= $_POST['description'] ?? ($item['description'] ?? '') ?></textarea>
                                        </div>
                                    </div>

                                    <!-- Sort Order -->
                                    <div class="form-group-wrap">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <i data-lucide="hash" class="text-muted" style="width: 14px; height: 14px;"></i>
                                            <label class="form-label-premium mb-0" for="t_sort_order">
                                                Sort Order
                                            </label>
                                        </div>
                                        <input type="number" id="t_sort_order" name="sort_order"
                                               class="form-control-premium"
                                               placeholder="e.g. 10"
                                               value="<?= htmlspecialchars($_POST['sort_order'] ?? ($item['sort_order'] ?? '0')) ?>">
                                    </div>

                                <?php elseif ($isItem && $itemType === 'metric'): ?>
                                    <!-- Comfort Metric Fields -->
                                    <!-- Metric Label -->
                                    <div class="form-group-wrap">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <i data-lucide="tag" class="text-muted" style="width: 14px; height: 14px;"></i>
                                            <label class="form-label-premium mb-0" for="m_label">
                                                Metric Label <span class="req">*</span>
                                            </label>
                                        </div>
                                        <input type="text" id="m_label" name="label"
                                               class="form-control-premium" required
                                               placeholder="e.g. All-Night Dryness"
                                               value="<?= htmlspecialchars($_POST['label'] ?? ($item['label'] ?? '')) ?>">
                                    </div>

                                    <!-- Display Value -->
                                    <div class="form-group-wrap">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <i data-lucide="heading" class="text-muted" style="width: 14px; height: 14px;"></i>
                                            <label class="form-label-premium mb-0" for="m_target_value">
                                                Display Value <span class="req">*</span>
                                            </label>
                                        </div>
                                        <input type="text" id="m_target_value" name="target_value"
                                               class="form-control-premium" required
                                               placeholder="e.g. 12"
                                               value="<?= htmlspecialchars($_POST['target_value'] ?? ($item['target_value'] ?? '')) ?>">
                                    </div>

                                    <!-- Icon Class -->
                                    <div class="form-group-wrap">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <i data-lucide="sparkles" class="text-muted" style="width: 14px; height: 14px;"></i>
                                            <label class="form-label-premium mb-0" for="m_icon_class">
                                                Remix Icon Class <span class="req">*</span>
                                            </label>
                                        </div>
                                        <input type="text" id="m_icon_class" name="icon_class"
                                               class="form-control-premium" required
                                               placeholder="e.g. ri-time-line"
                                               value="<?= htmlspecialchars($_POST['icon_class'] ?? ($item['icon_class'] ?? 'ri-checkbox-circle-line')) ?>">
                                        <div class="fs-0-72 text-secondary mt-1">
                                            Use any class from <a href="https://remixicon.com/" target="_blank" class="text-primary decoration-underline">Remix Icon library</a> (e.g. <code>ri-time-line</code>, <code>ri-shield-check-line</code>, <code>ri-heart-line</code>, <code>ri-star-line</code>).
                                        </div>
                                    </div>

                                    <!-- Description -->
                                    <div class="form-group-wrap">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <i data-lucide="align-left" class="text-muted" style="width: 14px; height: 14px;"></i>
                                            <label class="form-label-premium mb-0" for="m_description">
                                                Description <span class="req">*</span>
                                            </label>
                                        </div>
                                        <div class="tinymce-wrap">
                                            <textarea id="m_description" name="description"
                                                      class="tinymce-editor" rows="4" required
                                                      placeholder="Provide details about this metric…"><?= $_POST['description'] ?? ($item['description'] ?? '') ?></textarea>
                                        </div>
                                    </div>

                                    <!-- Suffix Format -->
                                    <div class="form-group-wrap">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <i data-lucide="percent" class="text-muted" style="width: 14px; height: 14px;"></i>
                                            <label class="form-label-premium mb-0" for="m_suffix_type">
                                                Format Suffix Type
                                            </label>
                                        </div>
                                        <select id="m_suffix_type" name="suffix_type" class="form-select-premium">
                                            <?php 
                                            $currSuffix = $_POST['suffix_type'] ?? ($item['suffix_type'] ?? 'none');
                                            $opts = ['none' => 'No Suffix', 'hours' => 'Hours (h)', 'percent' => 'Percent (%)', 'plus' => 'Plus (+)', 'star' => 'Star (Rating)'];
                                            foreach ($opts as $optVal => $optLabel):
                                            ?>
                                                <option value="<?= $optVal ?>" <?= $currSuffix === $optVal ? 'selected' : '' ?>><?= $optLabel ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <!-- Decimals -->
                                    <div class="form-group-wrap">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <i data-lucide="hash" class="text-muted" style="width: 14px; height: 14px;"></i>
                                            <label class="form-label-premium mb-0" for="m_decimals">
                                                Decimal Places
                                            </label>
                                        </div>
                                        <input type="number" id="m_decimals" name="decimals"
                                               class="form-control-premium" min="0" max="4"
                                               placeholder="e.g. 0"
                                               value="<?= htmlspecialchars($_POST['decimals'] ?? ($item['decimals'] ?? '0')) ?>">
                                    </div>

                                    <!-- Sort Order -->
                                    <div class="form-group-wrap">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <i data-lucide="hash" class="text-muted" style="width: 14px; height: 14px;"></i>
                                            <label class="form-label-premium mb-0" for="m_sort_order">
                                                Sort Order
                                            </label>
                                        </div>
                                        <input type="number" id="m_sort_order" name="sort_order"
                                               class="form-control-premium"
                                               placeholder="e.g. 10"
                                               value="<?= htmlspecialchars($_POST['sort_order'] ?? ($item['sort_order'] ?? '0')) ?>">
                                    </div>

                                <?php elseif ($isItem && $itemType === 'layer'): ?>
                                    <!-- Story Layer Fields -->
                                    <!-- Index Badge Label -->
                                    <div class="form-group-wrap">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <i data-lucide="hash" class="text-muted" style="width: 14px; height: 14px;"></i>
                                            <label class="form-label-premium mb-0" for="l_badge">
                                                Layer Index Label
                                            </label>
                                        </div>
                                        <input type="text" id="l_badge" name="badge"
                                               class="form-control-premium"
                                               placeholder="e.g. Layer One (leave blank to auto-generate)"
                                               value="<?= htmlspecialchars($_POST['badge'] ?? ($item['badge'] ?? '')) ?>">
                                    </div>

                                    <!-- Title -->
                                    <div class="form-group-wrap">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <i data-lucide="tag" class="text-muted" style="width: 14px; height: 14px;"></i>
                                            <label class="form-label-premium mb-0" for="l_title">
                                                Layer Title <span class="req">*</span>
                                            </label>
                                        </div>
                                        <input type="text" id="l_title" name="title"
                                               class="form-control-premium" required
                                               placeholder="e.g. Topsheet Comfort"
                                               value="<?= htmlspecialchars($_POST['title'] ?? ($item['title'] ?? '')) ?>">
                                    </div>

                                    <!-- Caption -->
                                    <div class="form-group-wrap">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <i data-lucide="heading" class="text-muted" style="width: 14px; height: 14px;"></i>
                                            <label class="form-label-premium mb-0" for="l_caption">
                                                Image Caption
                                            </label>
                                        </div>
                                        <input type="text" id="l_caption" name="caption"
                                               class="form-control-premium"
                                               placeholder="e.g. 01 / Cottony Cloud Topsheet"
                                               value="<?= htmlspecialchars($_POST['caption'] ?? ($item['caption'] ?? '')) ?>">
                                    </div>

                                    <!-- Specs Tags -->
                                    <div class="form-group-wrap">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <i data-lucide="sparkles" class="text-muted" style="width: 14px; height: 14px;"></i>
                                            <label class="form-label-premium mb-0" for="l_specs">
                                                Specs Tags (comma separated)
                                            </label>
                                        </div>
                                        <input type="text" id="l_specs" name="specs"
                                               class="form-control-premium"
                                               placeholder="e.g. Hypoallergenic, Zero fragrances, Anti-friction"
                                               value="<?= htmlspecialchars($_POST['specs'] ?? ($item['specs'] ?? '')) ?>">
                                    </div>

                                    <!-- Description -->
                                    <div class="form-group-wrap">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <i data-lucide="align-left" class="text-muted" style="width: 14px; height: 14px;"></i>
                                            <label class="form-label-premium mb-0" for="l_description">
                                                Description Text <span class="req">*</span>
                                            </label>
                                        </div>
                                        <div class="tinymce-wrap">
                                            <textarea id="l_description" name="description"
                                                      class="tinymce-editor" rows="5" required
                                                      placeholder="Provide details about this diaper layer…"><?= $_POST['description'] ?? ($item['description'] ?? '') ?></textarea>
                                        </div>
                                    </div>

                                    <!-- Sort Order -->
                                    <div class="form-group-wrap">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <i data-lucide="hash" class="text-muted" style="width: 14px; height: 14px;"></i>
                                            <label class="form-label-premium mb-0" for="l_sort_order">
                                                Sort Order
                                            </label>
                                        </div>
                                        <input type="number" id="l_sort_order" name="sort_order"
                                               class="form-control-premium"
                                               placeholder="e.g. 10"
                                               value="<?= htmlspecialchars($_POST['sort_order'] ?? ($item['sort_order'] ?? '0')) ?>">
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="card-premium mt-3 p-3">
                            <div class="d-flex gap-2 max-width-320-px">
                                <button type="submit" class="btn btn-premium-primary flex-grow-1 d-flex align-items-center justify-content-center gap-2 py-2">
                                    <i data-lucide="check" class="icon-lg"></i>
                                    <span><?= ($isItem && !$isEdit) ? 'Save Item' : 'Save Changes' ?></span>
                                </button>
                                <a href="<?= $backUrl ?>" class="btn btn-premium-secondary d-flex align-items-center justify-content-center gap-2 px-3 py-2 text-decoration-none">
                                    <i data-lucide="x" class="icon-lg"></i>
                                    <span>Cancel</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Sidebar Uploader (Only if image supported) -->
                    <?php if ($hasImage): ?>
                        <div class="col-12 col-xl-4">
                            <div class="form-sidebar-sticky-wrapper d-flex flex-column gap-3">
                                <div class="card-premium p-4">
                                    <div class="d-flex align-items-center gap-2 mb-3 border-bottom pb-2">
                                        <i data-lucide="image" class="text-primary" style="width: 16px; height: 16px;"></i>
                                        <span class="form-section-label mb-0" style="font-size:0.82rem;">Upload Image</span>
                                    </div>
                                    
                                    <div class="upload-zone" id="uploadZone">
                                        <input type="file" id="aboutImageInput" name="image_file"
                                               accept="image/jpeg,image/png,image/webp"
                                               aria-label="Upload item image">
                                        
                                        <?php if (!empty($item['image_url'])): ?>
                                            <div id="uploadZoneBody" class="d-none-init">
                                                <div class="upload-zone-icon">
                                                    <i data-lucide="image-plus" class="icon-3d"></i>
                                                </div>
                                                <div class="upload-zone-title">Drop image or click to browse</div>
                                                <div class="upload-zone-sub">JPG, PNG, WebP &bull; Max 5MB</div>
                                            </div>
                                            <div class="upload-zone-preview show shadow-sm" id="uploadPreviewWrap">
                                                <img src="<?= htmlspecialchars($item['image_url']) ?>" id="uploadPreviewImg" alt="Preview">
                                                <button type="button" class="preview-remove" id="previewRemoveBtn" title="Remove image">
                                                    <i data-lucide="x" class="icon-12"></i>
                                                </button>
                                            </div>
                                        <?php else: ?>
                                            <div id="uploadZoneBody">
                                                <div class="upload-zone-icon">
                                                    <i data-lucide="image-plus" class="icon-3d"></i>
                                                </div>
                                                <div class="upload-zone-title">Drop image or click to browse</div>
                                                <div class="upload-zone-sub">JPG, PNG, WebP &bull; Max 5MB</div>
                                            </div>
                                            <div class="upload-zone-preview shadow-sm" id="uploadPreviewWrap">
                                                <img src="" id="uploadPreviewImg" alt="Preview">
                                                <button type="button" class="preview-remove" id="previewRemoveBtn" title="Remove image">
                                                    <i data-lucide="x" class="icon-12"></i>
                                                </button>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>
            </form>

        </div><!-- /container-fluid -->
    </div><!-- /page-content-wrapper -->
</div><!-- /wrapper -->

<?php include __DIR__ . '/../includes/footer.php'; ?>
