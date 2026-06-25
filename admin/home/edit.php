<?php
// admin/home/edit.php — Unified Edit Page for Homepage Sections and Items
require_once __DIR__ . '/../includes/auth-check.php';
require_once __DIR__ . '/../config/home-helper.php';

$sectionKey = trim($_GET['section'] ?? '');
$itemType = trim($_GET['type'] ?? '');
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

$isSection = !empty($sectionKey);
$isItem = !empty($itemType);

if (!$isSection && !$isItem) {
    $_SESSION['flash_message'] = 'Missing section or item identifier.';
    $_SESSION['flash_type']    = 'error';
    header('Location: ' . BASE_URL . 'home/');
    exit;
}

if ($isSection) {
    $metadata = getHomeSectionMetadata($sectionKey);
    if (empty($metadata)) {
        $_SESSION['flash_message'] = 'Invalid homepage section identifier.';
        $_SESSION['flash_type']    = 'error';
        header('Location: ' . BASE_URL . 'home/');
        exit;
    }
    $section = getHomeSection($sectionKey);
    if (!$section) {
        $_SESSION['flash_message'] = 'Section data not found.';
        $_SESSION['flash_type']    = 'error';
        header('Location: ' . BASE_URL . 'home/');
        exit;
    }
} elseif ($isItem) {
    if (!in_array($itemType, ['atelier_variant', 'care_plan_perk', 'catnav_panel'])) {
        $_SESSION['flash_message'] = 'Invalid item type.';
        $_SESSION['flash_type']    = 'error';
        header('Location: ' . BASE_URL . 'home/');
        exit;
    }

    if (empty($id)) {
        $_SESSION['flash_message'] = 'Missing item identifier.';
        $_SESSION['flash_type']    = 'error';
        header('Location: ' . BASE_URL . 'home/');
        exit;
    }

    if ($itemType === 'atelier_variant') {
        $item = getHomeAtelierVariant($id);
        $tabName = 'atelier';
    } elseif ($itemType === 'care_plan_perk') {
        $item = getHomeCarePlanPerk($id);
        $tabName = 'perks';
    } elseif ($itemType === 'catnav_panel') {
        $item = getHomeCatnavPanel($id);
        $tabName = 'experience';
    }

    if (!$item) {
        $_SESSION['flash_message'] = 'Requested item not found.';
        $_SESSION['flash_type']    = 'error';
        header('Location: ' . BASE_URL . 'home/?tab=' . $tabName);
        exit;
    }
}

// POST submission handler
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];
    if ($isSection) {
        $updateData = [];
        // Fields that are allowed to contain raw HTML tags or HTML entities
        $htmlAllowedFields = [
            'section_title',
            'left_text',
            'right_text',
            'panel1_text',
            'panel2_text',
        ];
        // Fields that allow a small safe subset of inline HTML tags
        $htmlTagAllowedFields = [
            'section_title',
            'left_text',
            'right_text',
        ];
        // Fields that are edited using TinyMCE rich text editor
        $tinymceFields = [
            'left_text',
            'right_text',
            'desc_1',
            'desc_2',
            'content',
            'panel1_text',
            'panel2_text'
        ];

        foreach ($metadata['fields'] as $fieldKey => $fieldMeta) {
            if ($fieldMeta['type'] === 'image' || $fieldMeta['type'] === 'video') {
                continue;
            }
            $val = $_POST[$fieldKey] ?? '';

            if (in_array($fieldKey, $tinymceFields, true)) {
                // Allow safe rich-text formatting tags from TinyMCE
                $val = trim(strip_tags($val, '<p><a><strong><em><b><i><u><ul><ol><li><br><hr><h1><h2><h3><h4><h5><h6><div><span>'));
            } elseif (in_array($fieldKey, $htmlTagAllowedFields, true)) {
                // Allow safe inline HTML tags (br, em, strong, etc.) — used for editorial line-breaks
                $val = trim(strip_tags($val, '<em><strong><b><i><u><br>'));
            } elseif (in_array($fieldKey, $htmlAllowedFields, true)) {
                // Allow HTML entities but no tags — text is echoed raw on frontend
                // We do NOT strip_tags here because the value may contain &nbsp; &#x2019; etc.
                $val = trim($val);
            } else {
                // Plain text fields — strip everything
                $val = trim(strip_tags($val));
            }

            if (($val === '' || $val === null) && !empty($fieldMeta['required'])) {
                $errors[] = htmlspecialchars($fieldMeta['label']) . ' is required.';
            }
            $updateData[$fieldKey] = $val;
        }

        // Handle image and video uploads
        foreach ($metadata['fields'] as $fieldKey => $fieldMeta) {
            if ($fieldMeta['type'] === 'image') {
                $imageUrl = $section[$fieldKey] ?? '';
                $fileInputKey = $fieldKey . '_file';
                if (!empty($_FILES[$fileInputKey]['name']) && $_FILES[$fileInputKey]['error'] === UPLOAD_ERR_OK) {
                    try {
                        $newImage = handleHomeImageUpload($_FILES[$fileInputKey], 'home_' . $sectionKey . '_' . $fieldKey);
                        if ($newImage) {
                            $imageUrl = $newImage;
                            // Attempt to unlink old image if local
                            $oldUrl = $section[$fieldKey] ?? '';
                            if ($oldUrl && str_contains($oldUrl, UPLOAD_URL)) {
                                $localPath = str_replace(UPLOAD_URL, UPLOAD_DIR, $oldUrl);
                                if (file_exists($localPath)) @unlink($localPath);
                            }
                        }
                    } catch (Exception $e) {
                        $errors[] = $e->getMessage();
                    }
                }
                $updateData[$fieldKey] = $imageUrl;
            } elseif ($fieldMeta['type'] === 'video') {
                $videoUrl = $section[$fieldKey] ?? '';
                $fileInputKey = $fieldKey . '_file';
                if (!empty($_FILES[$fileInputKey]['name']) && $_FILES[$fileInputKey]['error'] === UPLOAD_ERR_OK) {
                    try {
                        $newVideo = handleHomeVideoUpload($_FILES[$fileInputKey], 'home_' . $sectionKey . '_' . $fieldKey);
                        if ($newVideo) {
                            $videoUrl = $newVideo;
                            // Attempt to unlink old video if local
                            $oldUrl = $section[$fieldKey] ?? '';
                            if ($oldUrl && str_contains($oldUrl, UPLOAD_URL)) {
                                $localPath = str_replace(UPLOAD_URL, UPLOAD_DIR, $oldUrl);
                                if (file_exists($localPath)) @unlink($localPath);
                            }
                        }
                    } catch (Exception $e) {
                        $errors[] = $e->getMessage();
                    }
                }
                if (empty($videoUrl) && !empty($fieldMeta['required'])) {
                    $errors[] = htmlspecialchars($fieldMeta['label']) . ' is required.';
                }
                $updateData[$fieldKey] = $videoUrl;
            }
        }

        if (empty($errors)) {
            if (updateHomeSection($sectionKey, $updateData)) {
                $_SESSION['flash_message'] = $metadata['name'] . ' updated successfully.';
                $_SESSION['flash_type']    = 'success';
                header('Location: ' . BASE_URL . 'home/?tab=' . ($sectionKey === 'atelier_header' ? 'atelier' : ($sectionKey === 'catnav_header' ? 'experience' : $sectionKey)));
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
        if ($itemType === 'atelier_variant') {
            $variant_name      = trim(strip_tags($_POST['variant_name'] ?? ''));
            $tag_top_title     = trim(strip_tags($_POST['tag_top_title'] ?? ''));
            $tag_top_desc      = trim(strip_tags($_POST['tag_top_desc'] ?? ''));
            $tag_bottom_title  = trim(strip_tags($_POST['tag_bottom_title'] ?? ''));
            $tag_bottom_desc   = trim(strip_tags($_POST['tag_bottom_desc'] ?? ''));
            $val_absorbency    = filter_input(INPUT_POST, 'val_absorbency', FILTER_VALIDATE_INT) ?? 0;
            $val_stretch       = filter_input(INPUT_POST, 'val_stretch', FILTER_VALIDATE_INT) ?? 0;
            $val_softness      = filter_input(INPUT_POST, 'val_softness', FILTER_VALIDATE_INT) ?? 0;

            if (empty($variant_name)) $errors[] = 'Variant Name is required.';
            if (empty($tag_top_title)) $errors[] = 'Top Tag Title is required.';
            if (empty($tag_top_desc)) $errors[] = 'Top Tag Description is required.';

            $imageUrl = $item['image_url'];
            if (!empty($_FILES['image_file']['name'])) {
                try {
                    $newImage = handleHomeImageUpload($_FILES['image_file'], 'home_atelier_' . $id);
                    if ($newImage) {
                        $imageUrl = $newImage;
                        if ($item['image_url'] && str_contains($item['image_url'], UPLOAD_URL)) {
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
                    'variant_name' => $variant_name,
                    'tag_top_title' => $tag_top_title,
                    'tag_top_desc' => $tag_top_desc,
                    'tag_bottom_title' => $tag_bottom_title,
                    'tag_bottom_desc' => $tag_bottom_desc,
                    'val_absorbency' => $val_absorbency,
                    'val_stretch' => $val_stretch,
                    'val_softness' => $val_softness,
                    'image_url' => $imageUrl
                ];
                if (updateHomeAtelierVariant($id, $payload)) {
                    $_SESSION['flash_message'] = 'Atelier variant updated successfully.';
                    $_SESSION['flash_type']    = 'success';
                    header('Location: ' . BASE_URL . 'home/?tab=atelier');
                    exit;
                } else {
                    $_SESSION['flash_message'] = 'Failed to update variant in database.';
                    $_SESSION['flash_type']    = 'error';
                }
            } else {
                $_SESSION['flash_message'] = implode(' ', $errors);
                $_SESSION['flash_type']    = 'error';
            }
        } elseif ($itemType === 'care_plan_perk') {
            $label    = trim(strip_tags($_POST['label'] ?? ''));
            $icon_svg = trim($_POST['icon_svg'] ?? '');

            if (empty($label)) $errors[] = 'Perk Label is required.';
            if (empty($icon_svg)) $errors[] = 'Icon SVG is required.';

            if (empty($errors)) {
                $payload = [
                    'label' => $label,
                    'icon_svg' => $icon_svg
                ];
                if (updateHomeCarePlanPerk($id, $payload)) {
                    $_SESSION['flash_message'] = 'Care plan perk updated successfully.';
                    $_SESSION['flash_type']    = 'success';
                    header('Location: ' . BASE_URL . 'home/?tab=perks');
                    exit;
                } else {
                    $_SESSION['flash_message'] = 'Failed to update perk in database.';
                    $_SESSION['flash_type']    = 'error';
                }
            } else {
                $_SESSION['flash_message'] = implode(' ', $errors);
                $_SESSION['flash_type']    = 'error';
            }
        } elseif ($itemType === 'catnav_panel') {
            $eyebrow     = trim(strip_tags($_POST['eyebrow'] ?? ''));
            $title       = trim(strip_tags($_POST['title'] ?? ''));
            $description = trim(strip_tags($_POST['description'] ?? '', '<p><a><strong><em><b><i><u><ul><ol><li><br><hr><h1><h2><h3><h4><h5><h6><div><span>'));
            $btn_text    = trim(strip_tags($_POST['btn_text'] ?? ''));
            $btn_url     = trim(strip_tags($_POST['btn_url'] ?? ''));

            if (empty($title)) $errors[] = 'Panel Title is required.';
            if (empty($description)) $errors[] = 'Description is required.';

            $imageUrl = $item['image_url'];
            if (!empty($_FILES['image_file']['name'])) {
                try {
                    $newImage = handleHomeImageUpload($_FILES['image_file'], 'home_experience_' . $id);
                    if ($newImage) {
                        $imageUrl = $newImage;
                        if ($item['image_url'] && str_contains($item['image_url'], UPLOAD_URL)) {
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
                    'eyebrow' => $eyebrow,
                    'title' => $title,
                    'description' => $description,
                    'btn_text' => $btn_text,
                    'btn_url' => $btn_url,
                    'image_url' => $imageUrl
                ];
                if (updateHomeCatnavPanel($id, $payload)) {
                    $_SESSION['flash_message'] = 'Diaper experience category panel updated successfully.';
                    $_SESSION['flash_type']    = 'success';
                    header('Location: ' . BASE_URL . 'home/?tab=experience');
                    exit;
                } else {
                    $_SESSION['flash_message'] = 'Failed to update panel in database.';
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
    $backUrl     = BASE_URL . 'home/?tab=' . ($sectionKey === 'atelier_header' ? 'atelier' : ($sectionKey === 'catnav_header' ? 'experience' : $sectionKey));
    $headerTitle = 'Edit ' . htmlspecialchars($metadata['name']);
    $headerDesc  = htmlspecialchars($metadata['description']);
    $hasImage    = false;
    foreach ($metadata['fields'] as $fk => $fm) {
        if ($fm['type'] === 'image' || $fm['type'] === 'video') $hasImage = true;
    }
} else {
    $hasImage = ($itemType !== 'care_plan_perk');
    if ($itemType === 'atelier_variant') {
        $page_title  = "CloudCush Admin — Edit Atelier Variant";
        $backUrl     = BASE_URL . 'home/?tab=atelier';
        $headerTitle = "Edit Atelier Variant (" . htmlspecialchars($item['label']) . ")";
        $headerDesc  = 'Modify descriptions, callout badges, and sensation ratings for the sizing engine variant.';
    } elseif ($itemType === 'care_plan_perk') {
        $page_title  = "CloudCush Admin — Edit Care Plan Perk";
        $backUrl     = BASE_URL . 'home/?tab=perks';
        $headerTitle = "Edit Care Plan Perk";
        $headerDesc  = 'Modify the display label and vector SVG icon for this subscription perk.';
    } elseif ($itemType === 'catnav_panel') {
        $page_title  = "CloudCush Admin — Edit Diaper Experience Panel";
        $backUrl     = BASE_URL . 'home/?tab=experience';
        $headerTitle = "Edit Experience Panel (" . htmlspecialchars($item['label']) . ")";
        $headerDesc  = 'Modify description narrative, featured image, and CTA buttons for this category slide.';
    }
}

$active_page = 'home';
$fieldIcons = [
    'section_subtitle' => 'tag',
    'section_title'    => 'heading',
    'content'          => 'align-left',
    'left_text'        => 'align-left',
    'right_text'       => 'align-right',
    'btn_text'         => 'square-play',
    'btn_url'          => 'link',
    'badge_label'      => 'cloud',
    'desc_1'           => 'align-left',
    'desc_2'           => 'align-left',
    'video_url'        => 'play-circle',

    // Philosophy fields
    'panel1_eyebrow'   => 'tag',
    'panel1_bold'      => 'type',
    'panel1_text'      => 'align-left',
    'panel1_btn_text'  => 'square-play',
    'panel1_btn_url'   => 'link',

    'panel2_eyebrow'   => 'tag',
    'panel2_text'      => 'align-left',
    'panel2_btn_text'  => 'square-play',
    'panel2_btn_url'   => 'link'
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
                                    <!-- Dynamic fields for editorial sections -->
                                    <?php
                                    foreach ($metadata['fields'] as $fieldKey => $fieldMeta):
                                        if ($fieldMeta['type'] === 'image' || $fieldMeta['type'] === 'video') continue;
                                        $requiredStar = !empty($fieldMeta['required']) ? ' <span class="req">*</span>' : '';
                                        $val = $_POST[$fieldKey] ?? ($section[$fieldKey] ?? '');
                                        $iconName = $fieldIcons[$fieldKey] ?? 'edit-3';
                                    ?>
                                        <div class="form-group-wrap">
                                            <div class="d-flex align-items-center gap-2 mb-2">
                                                <i data-lucide="<?= $iconName ?>" class="text-muted" style="width: 14px; height: 14px;"></i>
                                                <label class="form-label-premium mb-0" for="home_<?= $fieldKey ?>">
                                                    <?= htmlspecialchars($fieldMeta['label']) ?><?= $requiredStar ?>
                                                </label>
                                            </div>

                                            <?php if ($fieldMeta['type'] === 'text'): ?>
                                                <input type="text" id="home_<?= $fieldKey ?>" name="<?= $fieldKey ?>"
                                                    class="form-control-premium" <?= !empty($fieldMeta['required']) ? 'required' : '' ?>
                                                    placeholder="<?= htmlspecialchars($fieldMeta['placeholder'] ?? '') ?>"
                                                    value="<?= htmlspecialchars($val) ?>">
                                            <?php elseif ($fieldMeta['type'] === 'textarea'): ?>
                                                <div class="tinymce-wrap">
                                                    <textarea id="home_<?= $fieldKey ?>" name="<?= $fieldKey ?>"
                                                        class="tinymce-editor" rows="6" <?= !empty($fieldMeta['required']) ? 'required' : '' ?>
                                                        placeholder="<?= htmlspecialchars($fieldMeta['placeholder'] ?? '') ?>"><?= htmlspecialchars($val) ?></textarea>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>

                                <?php elseif ($isItem && $itemType === 'atelier_variant'): ?>
                                    <!-- Atelier Variant Fields -->
                                    <div class="form-group-wrap">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <i data-lucide="tag" class="text-muted" style="width: 14px; height: 14px;"></i>
                                            <label class="form-label-premium mb-0" for="variant_name">
                                                Product Name <span class="req">*</span>
                                            </label>
                                        </div>
                                        <input type="text" id="variant_name" name="variant_name" class="form-control-premium" required
                                            placeholder="e.g. CloudCush TinyHug"
                                            value="<?= htmlspecialchars($_POST['variant_name'] ?? ($item['variant_name'] ?? '')) ?>">
                                    </div>

                                    <div class="row g-3">
                                        <div class="col-12 col-md-6">
                                            <div class="form-group-wrap">
                                                <div class="d-flex align-items-center gap-2 mb-2">
                                                    <i data-lucide="heading" class="text-muted" style="width: 14px; height: 14px;"></i>
                                                    <label class="form-label-premium mb-0" for="tag_top_title">
                                                        Top Callout Badge Title <span class="req">*</span>
                                                    </label>
                                                </div>
                                                <input type="text" id="tag_top_title" name="tag_top_title" class="form-control-premium" required
                                                    placeholder="e.g. Umbilical Care Cutout"
                                                    value="<?= htmlspecialchars($_POST['tag_top_title'] ?? ($item['tag_top_title'] ?? '')) ?>">
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <div class="form-group-wrap">
                                                <div class="d-flex align-items-center gap-2 mb-2">
                                                    <i data-lucide="align-left" class="text-muted" style="width: 14px; height: 14px;"></i>
                                                    <label class="form-label-premium mb-0" for="tag_top_desc">
                                                        Top Callout Description <span class="req">*</span>
                                                    </label>
                                                </div>
                                                <input type="text" id="tag_top_desc" name="tag_top_desc" class="form-control-premium" required
                                                    placeholder="Describe the top feature..."
                                                    value="<?= htmlspecialchars($_POST['tag_top_desc'] ?? ($item['tag_top_desc'] ?? '')) ?>">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row g-3">
                                        <div class="col-12 col-md-6">
                                            <div class="form-group-wrap">
                                                <div class="d-flex align-items-center gap-2 mb-2">
                                                    <i data-lucide="heading" class="text-muted" style="width: 14px; height: 14px;"></i>
                                                    <label class="form-label-premium mb-0" for="tag_bottom_title">
                                                        Bottom Callout Badge Title
                                                    </label>
                                                </div>
                                                <input type="text" id="tag_bottom_title" name="tag_bottom_title" class="form-control-premium"
                                                    placeholder="e.g. Organic Topsheet"
                                                    value="<?= htmlspecialchars($_POST['tag_bottom_title'] ?? ($item['tag_bottom_title'] ?? '')) ?>">
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <div class="form-group-wrap">
                                                <div class="d-flex align-items-center gap-2 mb-2">
                                                    <i data-lucide="align-left" class="text-muted" style="width: 14px; height: 14px;"></i>
                                                    <label class="form-label-premium mb-0" for="tag_bottom_desc">
                                                        Bottom Callout Description
                                                    </label>
                                                </div>
                                                <input type="text" id="tag_bottom_desc" name="tag_bottom_desc" class="form-control-premium"
                                                    placeholder="Describe the bottom feature..."
                                                    value="<?= htmlspecialchars($_POST['tag_bottom_desc'] ?? ($item['tag_bottom_desc'] ?? '')) ?>">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row g-3">
                                        <div class="col-4">
                                            <div class="form-group-wrap">
                                                <div class="d-flex align-items-center gap-2 mb-2">
                                                    <i data-lucide="hash" class="text-muted" style="width: 14px; height: 14px;"></i>
                                                    <label class="form-label-premium mb-0" for="val_absorbency">
                                                        Absorbency %
                                                    </label>
                                                </div>
                                                <input type="number" id="val_absorbency" name="val_absorbency" class="form-control-premium" min="0" max="100"
                                                    value="<?= intval($_POST['val_absorbency'] ?? ($item['val_absorbency'] ?? 0)) ?>">
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="form-group-wrap">
                                                <div class="d-flex align-items-center gap-2 mb-2">
                                                    <i data-lucide="hash" class="text-muted" style="width: 14px; height: 14px;"></i>
                                                    <label class="form-label-premium mb-0" for="val_stretch">
                                                        Stretch %
                                                    </label>
                                                </div>
                                                <input type="number" id="val_stretch" name="val_stretch" class="form-control-premium" min="0" max="100"
                                                    value="<?= intval($_POST['val_stretch'] ?? ($item['val_stretch'] ?? 0)) ?>">
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="form-group-wrap">
                                                <div class="d-flex align-items-center gap-2 mb-2">
                                                    <i data-lucide="hash" class="text-muted" style="width: 14px; height: 14px;"></i>
                                                    <label class="form-label-premium mb-0" for="val_softness">
                                                        Softness %
                                                    </label>
                                                </div>
                                                <input type="number" id="val_softness" name="val_softness" class="form-control-premium" min="0" max="100"
                                                    value="<?= intval($_POST['val_softness'] ?? ($item['val_softness'] ?? 0)) ?>">
                                            </div>
                                        </div>
                                    </div>

                                <?php elseif ($isItem && $itemType === 'care_plan_perk'): ?>
                                    <!-- Care Plan Perk Fields -->
                                    <div class="form-group-wrap">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <i data-lucide="tag" class="text-muted" style="width: 14px; height: 14px;"></i>
                                            <label class="form-label-premium mb-0" for="perk_label">
                                                Perk Display Text <span class="req">*</span>
                                            </label>
                                        </div>
                                        <input type="text" id="perk_label" name="label" class="form-control-premium" required
                                            placeholder="e.g. 15% Subscription Savings"
                                            value="<?= htmlspecialchars($_POST['label'] ?? ($item['label'] ?? '')) ?>">
                                    </div>
                                    <div class="form-group-wrap">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <i data-lucide="align-left" class="text-muted" style="width: 14px; height: 14px;"></i>
                                            <label class="form-label-premium mb-0" for="perk_icon_svg">
                                                Perk Icon SVG Code <span class="req">*</span>
                                            </label>
                                        </div>
                                        <textarea id="perk_icon_svg" name="icon_svg" class="form-control-premium" rows="5" required
                                            placeholder="e.g. &lt;svg viewBox='0 0 24 24'&gt;...&lt;/svg&gt;"><?= htmlspecialchars($_POST['icon_svg'] ?? ($item['icon_svg'] ?? '')) ?></textarea>
                                        <div class="fs-0-72 text-secondary mt-1">Raw XML vector markup code for custom icon drawings. Must include standard SVG boundaries.</div>
                                    </div>

                                <?php elseif ($isItem && $itemType === 'catnav_panel'): ?>
                                    <!-- Diaper Experience panel Fields -->
                                    <div class="row g-3">
                                        <div class="col-12 col-md-6">
                                            <div class="form-group-wrap">
                                                <div class="d-flex align-items-center gap-2 mb-2">
                                                    <i data-lucide="tag" class="text-muted" style="width: 14px; height: 14px;"></i>
                                                    <label class="form-label-premium mb-0" for="panel_eyebrow">
                                                        Panel Prefix Eyebrow
                                                    </label>
                                                </div>
                                                <input type="text" id="panel_eyebrow" name="eyebrow" class="form-control-premium"
                                                    placeholder="e.g. 01 — First Touch"
                                                    value="<?= htmlspecialchars($_POST['eyebrow'] ?? ($item['eyebrow'] ?? '')) ?>">
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <div class="form-group-wrap">
                                                <div class="d-flex align-items-center gap-2 mb-2">
                                                    <i data-lucide="heading" class="text-muted" style="width: 14px; height: 14px;"></i>
                                                    <label class="form-label-premium mb-0" for="panel_title">
                                                        Panel Product Title <span class="req">*</span>
                                                    </label>
                                                </div>
                                                <input type="text" id="panel_title" name="title" class="form-control-premium" required
                                                    placeholder="e.g. CloudCush TinyHug"
                                                    value="<?= htmlspecialchars($_POST['title'] ?? ($item['title'] ?? '')) ?>">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group-wrap">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <i data-lucide="align-left" class="text-muted" style="width: 14px; height: 14px;"></i>
                                            <label class="form-label-premium mb-0" for="panel_desc">
                                                Description Narrative <span class="req">*</span>
                                            </label>
                                        </div>
                                        <div class="tinymce-wrap">
                                            <textarea id="panel_desc" name="description" class="tinymce-editor" rows="6" required
                                                placeholder="Provide a detailed description of the category panel..."><?= htmlspecialchars($_POST['description'] ?? ($item['description'] ?? '')) ?></textarea>
                                        </div>
                                    </div>

                                    <div class="row g-3">
                                        <div class="col-12 col-md-6">
                                            <div class="form-group-wrap">
                                                <div class="d-flex align-items-center gap-2 mb-2">
                                                    <i data-lucide="type" class="text-muted" style="width: 14px; height: 14px;"></i>
                                                    <label class="form-label-premium mb-0" for="panel_btn_text">
                                                        Button Label
                                                    </label>
                                                </div>
                                                <input type="text" id="panel_btn_text" name="btn_text" class="form-control-premium"
                                                    placeholder="e.g. Shop TinyHug"
                                                    value="<?= htmlspecialchars($_POST['btn_text'] ?? ($item['btn_text'] ?? '')) ?>">
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <div class="form-group-wrap">
                                                <div class="d-flex align-items-center gap-2 mb-2">
                                                    <i data-lucide="link" class="text-muted" style="width: 14px; height: 14px;"></i>
                                                    <label class="form-label-premium mb-0" for="panel_btn_url">
                                                        Button Target URL
                                                    </label>
                                                </div>
                                                <input type="text" id="panel_btn_url" name="btn_url" class="form-control-premium"
                                                    placeholder="e.g. product-details.php"
                                                    value="<?= htmlspecialchars($_POST['btn_url'] ?? ($item['btn_url'] ?? '')) ?>">
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="card-premium mt-3 p-3">
                            <div class="d-flex gap-2 max-width-320-px">
                                <button type="submit" class="btn btn-premium-primary flex-grow-1 d-flex align-items-center justify-content-center gap-2 py-2">
                                    <i data-lucide="check" class="icon-lg"></i>
                                    <span>Save Changes</span>
                                </button>
                                <a href="<?= $backUrl ?>" class="btn btn-premium-secondary d-flex align-items-center justify-content-center gap-2 px-3 py-2 text-decoration-none">
                                    <i data-lucide="x" class="icon-lg"></i>
                                    <span>Cancel</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Image Column (if applicable) -->
                    <?php if ($hasImage): ?>
                        <div class="col-12 col-xl-4">
                            <div class="card-premium p-4">
                                <div class="d-flex align-items-center gap-2 mb-4 border-bottom pb-2">
                                    <i data-lucide="image" class="text-primary" style="width: 18px; height: 18px;"></i>
                                    <span class="form-section-label mb-0" style="font-size:0.88rem;">Media Attachments</span>
                                </div>

                                <div class="d-flex flex-column gap-4">
                                    <?php if ($isSection): ?>
                                        <!-- Show image and video fields inside Section fields -->
                                        <?php
                                        foreach ($metadata['fields'] as $fieldKey => $fieldMeta):
                                            if ($fieldMeta['type'] !== 'image' && $fieldMeta['type'] !== 'video') continue;
                                            $currentFile = $section[$fieldKey] ?? '';
                                        ?>
                                            <div class="form-group-wrap p-2 border rounded bg-light-subtle">
                                                <label class="form-label-premium mb-2 d-block fs-0-8"><?= htmlspecialchars($fieldMeta['label']) ?></label>
                                                <?php if ($fieldMeta['type'] === 'image'): ?>
                                                    <?php if (!empty($currentFile)): ?>
                                                        <div class="mb-3 rounded overflow-hidden border bg-white shadow-xs" style="width:100%; max-height: 120px;">
                                                            <img src="<?= htmlspecialchars(str_starts_with($currentFile, 'assets/') ? '../../' . $currentFile : $currentFile) ?>"
                                                                alt="Preview" style="width:100%; height:120px; object-fit: cover;" class="img-preview-target">
                                                        </div>
                                                    <?php endif; ?>
                                                    <input type="file" name="<?= $fieldKey ?>_file" class="form-control form-control-sm border-0 bg-light p-2 rounded shadow-xs w-100 fs-0-75 home-image-input" accept="image/*">
                                                <?php elseif ($fieldMeta['type'] === 'video'): ?>
                                                    <?php if (!empty($currentFile)): ?>
                                                        <div class="mb-3 rounded overflow-hidden border bg-white shadow-xs" style="width:100%; max-height: 180px;">
                                                            <video src="<?= htmlspecialchars(str_starts_with($currentFile, 'assets/') ? '../../' . $currentFile : $currentFile) ?>"
                                                                controls style="width:100%; height:180px; object-fit: cover;" class="video-preview-target"></video>
                                                        </div>
                                                    <?php endif; ?>
                                                    <input type="file" name="<?= $fieldKey ?>_file" class="form-control form-control-sm border-0 bg-light p-2 rounded shadow-xs w-100 fs-0-75 home-image-input" accept="video/mp4,video/webm,video/ogg">
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>

                                    <?php else: ?>
                                        <!-- Show item image field -->
                                        <div class="form-group-wrap">
                                            <label class="form-label-premium mb-2 d-block fs-0-8">Featured Attachment Image</label>
                                            <?php if (!empty($item['image_url'])): ?>
                                                <div class="mb-3 rounded overflow-hidden border bg-white shadow-xs" style="width:100%; max-height: 180px;">
                                                    <img src="<?= htmlspecialchars(str_starts_with($item['image_url'], 'assets/') ? '../../' . $item['image_url'] : $item['image_url']) ?>"
                                                        alt="Preview" style="width:100%; height:180px; object-fit: cover;" class="img-preview-target">
                                                </div>
                                            <?php endif; ?>
                                            <input type="file" name="image_file" class="form-control form-control-sm border-0 bg-light p-2 rounded shadow-xs w-100 fs-0-75 home-image-input" accept="image/*">
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>
            </form>

        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
