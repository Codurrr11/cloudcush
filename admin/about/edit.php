<?php
// admin/about/edit.php — Unified Edit and Add Form for About Page Sections and Items
require_once __DIR__ . '/../includes/auth-check.php';
require_once __DIR__ . '/../config/about-helper.php';

$sectionKey = trim($_GET['section'] ?? '');
$itemType = trim($_GET['type'] ?? '');
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

$isSection = !empty($sectionKey);
$isItem = !empty($itemType);

if (!$isSection && !$isItem) {
    $_SESSION['flash_message'] = 'Missing section or item identifier.';
    $_SESSION['flash_type']    = 'error';
    header('Location: ' . BASE_URL . 'about/');
    exit;
}

if ($isSection) {
    $metadata = getSectionMetadata($sectionKey);
    if (empty($metadata)) {
        $_SESSION['flash_message'] = 'Invalid about section identifier.';
        $_SESSION['flash_type']    = 'error';
        header('Location: ' . BASE_URL . 'about/');
        exit;
    }
    $section = getAboutSection($sectionKey);
    if (!$section) {
        $_SESSION['flash_message'] = 'Section data not found.';
        $_SESSION['flash_type']    = 'error';
        header('Location: ' . BASE_URL . 'about/');
        exit;
    }
} elseif ($isItem) {
    if (!in_array($itemType, ['feature', 'faq'])) {
        $_SESSION['flash_message'] = 'Invalid item type.';
        $_SESSION['flash_type']    = 'error';
        header('Location: ' . BASE_URL . 'about/');
        exit;
    }
    
    $isEdit = !empty($id);
    $item = null;
    if ($isEdit) {
        if ($itemType === 'feature') {
            $item = getAboutFeature($id);
        } elseif ($itemType === 'faq') {
            $item = getAboutFaq($id);
        }
        if (!$item) {
            $_SESSION['flash_message'] = 'Requested item not found.';
            $_SESSION['flash_type']    = 'error';
            header('Location: ' . BASE_URL . 'about/?tab=' . ($itemType === 'feature' ? 'features' : 'faq-cta'));
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
            if ($fieldMeta['type'] === 'image') {
                continue;
            }
            $val = $_POST[$fieldKey] ?? '';
            if ($fieldMeta['type'] === 'editor' || $fieldMeta['type'] === 'textarea') {
                $val = trim(strip_tags($val, '<p><a><strong><em><b><i><u><ul><ol><li><br><hr><h1><h2><h3><h4><h5><h6><div><span>'));
            } else {
                if ($fieldKey === 'section_title') {
                    $val = trim(strip_tags($val, '<br>'));
                } else {
                    $val = trim(strip_tags($val));
                }
            }
            if (empty($val) && !empty($fieldMeta['required'])) {
                $errors[] = htmlspecialchars($fieldMeta['label']) . ' is required.';
            }
            $updateData[$fieldKey] = $val;
        }
        
        // Handle image upload if supported
        if (isset($metadata['fields']['image_url'])) {
            $imageUrl = $section['image_url'];
            if (!empty($_FILES['image_file']['name'])) {
                try {
                    $newImage = handleAboutImageUpload($_FILES['image_file'], 'about_' . $sectionKey);
                    if ($newImage) {
                        $imageUrl = $newImage;
                        if ($section['image_url'] && str_contains($section['image_url'], UPLOAD_URL)) {
                            $localPath = str_replace(UPLOAD_URL, UPLOAD_DIR, $section['image_url']);
                            if (file_exists($localPath)) @unlink($localPath);
                        }
                    }
                } catch (\InvalidArgumentException $e) {
                    $errors[] = $e->getMessage();
                } catch (Exception $e) {
                    $errors[] = 'Failed to upload image: ' . $e->getMessage();
                }
            }
            $updateData['image_url'] = $imageUrl;
        }
        
        if (empty($errors)) {
            if (updateAboutSection($sectionKey, $updateData)) {
                $_SESSION['flash_message'] = $metadata['name'] . ' updated successfully.';
                $_SESSION['flash_type']    = 'success';
                header('Location: ' . BASE_URL . 'about/?tab=' . ($sectionKey === 'about_faq_header' ? 'faq-cta' : $sectionKey));
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
        if ($itemType === 'feature') {
            $title       = trim(strip_tags($_POST['title'] ?? ''));
            $description = trim(strip_tags($_POST['description'] ?? '', '<p><a><strong><em><b><i><u><ul><ol><li><br><hr><h1><h2><h3><h4><h5><h6><div><span>'));
            $icon_class  = trim(strip_tags($_POST['icon_class'] ?? ''));
            $sort_order  = filter_input(INPUT_POST, 'sort_order', FILTER_VALIDATE_INT) ?? 0;
            
            if (empty($title)) $errors[] = 'Feature Title is required.';
            if (empty($description)) $errors[] = 'Feature Description is required.';
            if (empty($icon_class)) $errors[] = 'Remix Icon Class is required.';
            
            if (empty($errors)) {
                $payload = [
                    'title'       => $title,
                    'description' => $description,
                    'icon_class'  => $icon_class,
                    'sort_order'  => $sort_order
                ];
                $res = $isEdit ? updateAboutFeature($id, $payload) : addAboutFeature($payload);
                if ($res) {
                    $_SESSION['flash_message'] = 'Feature Card ' . ($isEdit ? 'updated' : 'added') . ' successfully.';
                    $_SESSION['flash_type']    = 'success';
                    header('Location: ' . BASE_URL . 'about/?tab=features');
                    exit;
                } else {
                    $_SESSION['flash_message'] = 'Failed to save feature card.';
                    $_SESSION['flash_type']    = 'error';
                }
            } else {
                $_SESSION['flash_message'] = implode(' ', $errors);
                $_SESSION['flash_type']    = 'error';
            }
            
        } elseif ($itemType === 'faq') {
            $question   = trim(strip_tags($_POST['question'] ?? ''));
            $answer     = trim($_POST['answer'] ?? '');
            $sort_order = filter_input(INPUT_POST, 'sort_order', FILTER_VALIDATE_INT) ?? 0;
            
            if (empty($question)) $errors[] = 'Question is required.';
            if (empty($answer)) $errors[] = 'Answer is required.';
            
            if (empty($errors)) {
                $payload = [
                    'question'   => $question,
                    'answer'     => $answer,
                    'sort_order' => $sort_order
                ];
                $res = $isEdit ? updateAboutFaq($id, $payload) : addAboutFaq($payload);
                if ($res) {
                    $_SESSION['flash_message'] = 'About FAQ ' . ($isEdit ? 'updated' : 'added') . ' successfully.';
                    $_SESSION['flash_type']    = 'success';
                    header('Location: ' . BASE_URL . 'about/?tab=faq-cta');
                    exit;
                } else {
                    $_SESSION['flash_message'] = 'Failed to save About FAQ.';
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
    $backUrl     = BASE_URL . 'about/?tab=' . ($sectionKey === 'about_faq_header' ? 'faq-cta' : $sectionKey);
    $headerTitle = 'Edit ' . htmlspecialchars($metadata['name']);
    $headerDesc  = htmlspecialchars($metadata['description']);
    $hasImage    = isset($metadata['fields']['image_url']);
} else {
    $actionLabel = $isEdit ? 'Edit' : 'Add';
    $hasImage    = false;
    if ($itemType === 'feature') {
        $page_title  = "CloudCush Admin — {$actionLabel} Feature Card";
        $backUrl     = BASE_URL . 'about/?tab=features';
        $headerTitle = "{$actionLabel} Feature Card";
        $headerDesc  = $isEdit ? 'Modify dynamic card settings in the Why Choose CloudCush grid section.' : 'Add a new dynamic feature card to the Why Choose CloudCush grid section.';
    } else {
        $page_title  = "CloudCush Admin — {$actionLabel} About FAQ";
        $backUrl     = BASE_URL . 'about/?tab=faq-cta';
        $headerTitle = "{$actionLabel} About FAQ";
        $headerDesc  = $isEdit ? 'Modify dynamic question card settings for the About Page interactive accordion.' : 'Add a new dedicated FAQ for the About Page accordion.';
    }
}

$active_page = 'about';
$fieldIcons = [
    'section_subtitle' => 'tag',
    'section_title'    => 'heading',
    'content'          => 'align-left',
    'accent_text'      => 'sparkles',
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
                                        if ($fieldMeta['type'] === 'image') continue;
                                        $requiredStar = !empty($fieldMeta['required']) ? ' <span class="req">*</span>' : '';
                                        $val = $_POST[$fieldKey] ?? ($section[$fieldKey] ?? '');
                                        $iconName = $fieldIcons[$fieldKey] ?? 'edit-3';
                                    ?>
                                        <div class="form-group-wrap">
                                            <div class="d-flex align-items-center gap-2 mb-2">
                                                <i data-lucide="<?= $iconName ?>" class="text-muted" style="width: 14px; height: 14px;"></i>
                                                <label class="form-label-premium mb-0" for="about_<?= $fieldKey ?>">
                                                    <?= htmlspecialchars($fieldMeta['label']) ?><?= $requiredStar ?>
                                                </label>
                                            </div>
                                            
                                            <?php if ($fieldMeta['type'] === 'text'): ?>
                                                <input type="text" id="about_<?= $fieldKey ?>" name="<?= $fieldKey ?>"
                                                       class="form-control-premium" <?= !empty($fieldMeta['required']) ? 'required' : '' ?>
                                                       placeholder="<?= htmlspecialchars($fieldMeta['placeholder'] ?? '') ?>"
                                                       value="<?= htmlspecialchars($val) ?>">
                                            <?php elseif ($fieldMeta['type'] === 'textarea'): ?>
                                                <div class="tinymce-wrap">
                                                    <textarea id="about_<?= $fieldKey ?>" name="<?= $fieldKey ?>"
                                                              class="tinymce-editor" rows="6" <?= !empty($fieldMeta['required']) ? 'required' : '' ?>
                                                              placeholder="<?= htmlspecialchars($fieldMeta['placeholder'] ?? '') ?>"><?= htmlspecialchars($val) ?></textarea>
                                                </div>
                                            <?php elseif ($fieldMeta['type'] === 'editor'): ?>
                                                <div class="tinymce-wrap">
                                                    <textarea id="about_<?= $fieldKey ?>" name="<?= $fieldKey ?>"
                                                              class="tinymce-editor" rows="10"><?= htmlspecialchars($val) ?></textarea>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>

                                <?php elseif ($isItem && $itemType === 'feature'): ?>
                                    <!-- Feature Card Fields -->
                                    <!-- Title -->
                                    <div class="form-group-wrap">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <i data-lucide="heading" class="text-muted" style="width: 14px; height: 14px;"></i>
                                            <label class="form-label-premium mb-0" for="feat_title">
                                                Feature Title <span class="req">*</span>
                                            </label>
                                        </div>
                                        <input type="text" id="feat_title" name="title"
                                               class="form-control-premium" required
                                               placeholder="e.g. Rash-Free Comfort"
                                               value="<?= htmlspecialchars($_POST['title'] ?? ($item['title'] ?? '')) ?>">
                                    </div>

                                    <!-- Description -->
                                    <div class="form-group-wrap">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <i data-lucide="align-left" class="text-muted" style="width: 14px; height: 14px;"></i>
                                            <label class="form-label-premium mb-0" for="feat_description">
                                                Feature Description <span class="req">*</span>
                                            </label>
                                        </div>
                                        <div class="tinymce-wrap">
                                            <textarea id="feat_description" name="description"
                                                      class="tinymce-editor" rows="4" required
                                                      placeholder="Write a short feature description…"><?= htmlspecialchars($_POST['description'] ?? ($item['description'] ?? '')) ?></textarea>
                                        </div>
                                    </div>

                                    <!-- Icon Class -->
                                    <div class="form-group-wrap">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <i data-lucide="sparkles" class="text-muted" style="width: 14px; height: 14px;"></i>
                                            <label class="form-label-premium mb-0" for="feat_icon_class">
                                                Remix Icon Class <span class="req">*</span>
                                            </label>
                                        </div>
                                        <input type="text" id="feat_icon_class" name="icon_class"
                                               class="form-control-premium" required
                                               placeholder="e.g. ri-shield-cross-line"
                                               value="<?= htmlspecialchars($_POST['icon_class'] ?? ($item['icon_class'] ?? 'ri-checkbox-circle-line')) ?>">
                                        <div class="fs-0-72 text-secondary mt-1">
                                            Use any class from <a href="https://remixicon.com/" target="_blank" class="text-primary decoration-underline">Remix Icon library</a> (e.g. <code>ri-shield-cross-line</code>, <code>ri-moon-line</code>, <code>ri-leaf-line</code>).
                                        </div>
                                    </div>

                                    <!-- Sort Order -->
                                    <div class="form-group-wrap">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <i data-lucide="hash" class="text-muted" style="width: 14px; height: 14px;"></i>
                                            <label class="form-label-premium mb-0" for="feat_sort_order">
                                                Sort Order
                                            </label>
                                        </div>
                                        <input type="number" id="feat_sort_order" name="sort_order"
                                               class="form-control-premium"
                                               placeholder="e.g. 10"
                                               value="<?= htmlspecialchars($_POST['sort_order'] ?? ($item['sort_order'] ?? '0')) ?>">
                                    </div>

                                <?php elseif ($isItem && $itemType === 'faq'): ?>
                                    <!-- FAQ Fields -->
                                    <!-- Question -->
                                    <div class="form-group-wrap">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <i data-lucide="help-circle" class="text-muted" style="width: 14px; height: 14px;"></i>
                                            <label class="form-label-premium mb-0" for="faq_question">
                                                Question <span class="req">*</span>
                                            </label>
                                        </div>
                                        <input type="text" id="faq_question" name="question"
                                               class="form-control-premium" required
                                               placeholder="e.g. Are CloudCush diapers safe for sensitive skin?"
                                               value="<?= htmlspecialchars($_POST['question'] ?? ($item['question'] ?? '')) ?>">
                                    </div>

                                    <!-- Answer (TinyMCE) -->
                                    <div class="form-group-wrap">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <i data-lucide="align-left" class="text-muted" style="width: 14px; height: 14px;"></i>
                                            <label class="form-label-premium mb-0" for="faq_answer">
                                                Answer Content <span class="req">*</span>
                                            </label>
                                        </div>
                                        <div class="tinymce-wrap">
                                            <textarea id="faq_answer" name="answer"
                                                      class="tinymce-editor" rows="10"><?= htmlspecialchars($_POST['answer'] ?? ($item['answer'] ?? '')) ?></textarea>
                                        </div>
                                    </div>

                                    <!-- Sort Order -->
                                    <div class="form-group-wrap">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <i data-lucide="hash" class="text-muted" style="width: 14px; height: 14px;"></i>
                                            <label class="form-label-premium mb-0" for="faq_sort_order">
                                                Sort Order
                                            </label>
                                        </div>
                                        <input type="number" id="faq_sort_order" name="sort_order"
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
                                        
                                        <?php if (!empty($section['image_url'])): ?>
                                            <div id="uploadZoneBody" class="d-none-init">
                                                <div class="upload-zone-icon">
                                                    <i data-lucide="image-plus" class="icon-3d"></i>
                                                </div>
                                                <div class="upload-zone-title">Drop image or click to browse</div>
                                                <div class="upload-zone-sub">JPG, PNG, WebP &bull; Max 5MB</div>
                                            </div>
                                            <div class="upload-zone-preview show shadow-sm" id="uploadPreviewWrap">
                                                <img src="<?= htmlspecialchars($section['image_url']) ?>" id="uploadPreviewImg" alt="Preview">
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
