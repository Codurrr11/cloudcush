<?php
// admin/about/index.php — About Page Sections Dashboard with Tabbed Premium Interface & Features CRUD
require_once __DIR__ . '/../includes/auth-check.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/about-helper.php';

// Check if dynamic feature or FAQ delete request is being processed
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $action = trim($_GET['action'] ?? '');
    if ($id) {
        if ($action === 'delete_faq') {
            if (deleteAboutFaq($id)) {
                $_SESSION['flash_message'] = 'About FAQ deleted successfully.';
                $_SESSION['flash_type']    = 'success';
            } else {
                $_SESSION['flash_message'] = 'Failed to delete About FAQ.';
                $_SESSION['flash_type']    = 'error';
            }
            header('Location: ' . BASE_URL . 'about/?tab=faq-cta');
            exit;
        } else {
            if (deleteAboutFeature($id)) {
                $_SESSION['flash_message'] = 'Feature Card deleted successfully.';
                $_SESSION['flash_type']    = 'success';
            } else {
                $_SESSION['flash_message'] = 'Failed to delete feature card.';
                $_SESSION['flash_type']    = 'error';
            }
            header('Location: ' . BASE_URL . 'about/?tab=features');
            exit;
        }
    }
}

$page_title  = 'CloudCush Admin — About Page';
$active_page = 'about';

// Fetch current values
$sections = getAboutSections();
$metadata = getSectionMetadata();
$features = getAboutFeatures();
$aboutFaqs = getAboutFaqs();

// Determine active tab from URL parameter
$activeTab = trim($_GET['tab'] ?? 'hero');
$allowedTabs = ['hero', 'stories', 'features', 'philosophy', 'faq-cta'];
if (!in_array($activeTab, $allowedTabs)) {
    $activeTab = 'hero';
}

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
                    <h1 class="h4 fw-bold mb-1 page-heading">About Page Settings</h1>
                    <p class="text-secondary mb-0 fs-0-82">
                        Manage all the text, headings, images, and feature cards on the public About Us page.
                    </p>
                </div>
                <div>
                    <a href="../../about.php" target="_blank" class="btn btn-premium-secondary btn-sm d-flex align-items-center gap-2">
                        <i data-lucide="external-link" class="icon-xs"></i>
                        <span>View Live About Page</span>
                    </a>
                </div>
            </div>

            <!-- Tabbed Navigation Links (Premium HSL Pills) -->
            <div class="mb-4">
                <ul class="nav nav-pills nav-pills-premium gap-2" id="aboutPageTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a href="?tab=hero" class="nav-link <?= $activeTab === 'hero' ? 'active' : '' ?>">
                            <i data-lucide="layout-template" class="icon-sm"></i>
                            <span>Hero Banner</span>
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a href="?tab=stories" class="nav-link <?= $activeTab === 'stories' ? 'active' : '' ?>">
                            <i data-lucide="book-open" class="icon-sm"></i>
                            <span>Storytelling Strip</span>
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a href="?tab=features" class="nav-link <?= $activeTab === 'features' ? 'active' : '' ?>">
                            <i data-lucide="grid" class="icon-sm"></i>
                            <span>Features Grid</span>
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a href="?tab=philosophy" class="nav-link <?= $activeTab === 'philosophy' ? 'active' : '' ?>">
                            <i data-lucide="sparkles" class="icon-sm"></i>
                            <span>Philosophy</span>
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a href="?tab=faq-cta" class="nav-link <?= $activeTab === 'faq-cta' ? 'active' : '' ?>">
                            <i data-lucide="help-circle" class="icon-sm"></i>
                            <span>FAQ & CTA</span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Hidden Delete Handler URL for features -->
            <input type="hidden" id="deleteFeatureHandlerUrl" value="<?= BASE_URL ?>about/index.php">

            <!-- Tab Content Pane Grid -->
            <div>

                <!-- ── TAB 1: HERO BANNER ── -->
                <?php if ($activeTab === 'hero'): $meta = $metadata['hero']; $data = $sections['hero'] ?? []; ?>
                    <div class="card-premium p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom border-light-subtle">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-premium-soft-primary tech-data px-2 py-1 fs-0-75">Header</span>
                                <h3 class="h6 fw-bold mb-0 text-dark"><?= htmlspecialchars($meta['name']) ?></h3>
                            </div>
                            <a href="<?= BASE_URL ?>about/edit.php?section=hero" class="btn btn-premium-primary btn-sm d-flex align-items-center gap-2 px-3">
                                <i data-lucide="pencil" class="icon-xs"></i>
                                <span>Edit Section</span>
                            </a>
                        </div>
                        <p class="text-secondary fs-0-78 mb-4"><?= htmlspecialchars($meta['description']) ?></p>
                        
                        <div class="d-flex flex-column gap-3">
                            <div class="row align-items-start py-1">
                                <div class="col-4 col-sm-3 text-secondary fw-semibold fs-0-8 d-flex align-items-center gap-2">
                                    <i data-lucide="tag" class="text-primary" style="width: 14px; height: 14px;"></i>
                                    <span>Sub-Label</span>
                                </div>
                                <div class="col-8 col-sm-9 text-dark fs-0-8 font-medium">
                                    <?= htmlspecialchars($data['section_subtitle'] ?? '—') ?>
                                </div>
                            </div>
                            <div class="row align-items-start py-1">
                                <div class="col-4 col-sm-3 text-secondary fw-semibold fs-0-8 d-flex align-items-center gap-2">
                                    <i data-lucide="heading" class="text-primary" style="width: 14px; height: 14px;"></i>
                                    <span>Heading</span>
                                </div>
                                <div class="col-8 col-sm-9 text-dark fs-0-8 font-medium">
                                    <?= htmlspecialchars(strip_tags($data['section_title'] ?? '—')) ?>
                                </div>
                            </div>
                            <div class="row align-items-start py-1">
                                <div class="col-4 col-sm-3 text-secondary fw-semibold fs-0-8 d-flex align-items-center gap-2">
                                    <i data-lucide="align-left" class="text-primary" style="width: 14px; height: 14px;"></i>
                                    <span>Content Text</span>
                                </div>
                                <div class="col-8 col-sm-9 text-dark fs-0-8 font-medium line-height-1-5">
                                    <?= htmlspecialchars($data['content'] ?? '—') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- ── TAB 2: STORYTELLING STRIP ── -->
                <?php if ($activeTab === 'stories'): ?>
                    <div class="d-flex flex-column gap-4">
                        <?php 
                        for ($i = 1; $i <= 3; $i++): 
                            $key = 'story_' . $i;
                            $meta = $metadata[$key];
                            $data = $sections[$key] ?? [];
                        ?>
                            <div class="card-premium p-4">
                                <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom border-light-subtle">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-premium-soft-primary tech-data px-2 py-1 fs-0-75">Card 0<?= $i ?></span>
                                        <h3 class="h6 fw-bold mb-0 text-dark"><?= htmlspecialchars($meta['name']) ?></h3>
                                    </div>
                                    <a href="<?= BASE_URL ?>about/edit.php?section=<?= $key ?>" class="btn btn-premium-primary btn-sm d-flex align-items-center gap-2 px-3">
                                        <i data-lucide="pencil" class="icon-xs"></i>
                                        <span>Edit Card</span>
                                    </a>
                                </div>
                                <p class="text-secondary fs-0-78 mb-4"><?= htmlspecialchars($meta['description']) ?></p>
                                
                                <div class="row g-4">
                                    <div class="col-12 col-md-8">
                                        <div class="d-flex flex-column gap-3">
                                            <div class="row align-items-start py-1">
                                                <div class="col-4 col-sm-3 text-secondary fw-semibold fs-0-8 d-flex align-items-center gap-2">
                                                    <i data-lucide="tag" class="text-primary" style="width: 14px; height: 14px;"></i>
                                                    <span>Sub-Label</span>
                                                </div>
                                                <div class="col-8 col-sm-9 text-dark fs-0-8 font-medium">
                                                    <?= htmlspecialchars($data['section_subtitle'] ?? '—') ?>
                                                </div>
                                            </div>
                                            <div class="row align-items-start py-1">
                                                <div class="col-4 col-sm-3 text-secondary fw-semibold fs-0-8 d-flex align-items-center gap-2">
                                                    <i data-lucide="heading" class="text-primary" style="width: 14px; height: 14px;"></i>
                                                    <span>Heading</span>
                                                </div>
                                                <div class="col-8 col-sm-9 text-dark fs-0-8 font-medium">
                                                    <?= htmlspecialchars(strip_tags($data['section_title'] ?? '—')) ?>
                                                </div>
                                            </div>
                                            <div class="row align-items-start py-1">
                                                <div class="col-4 col-sm-3 text-secondary fw-semibold fs-0-8 d-flex align-items-center gap-2">
                                                    <i data-lucide="align-left" class="text-primary" style="width: 14px; height: 14px;"></i>
                                                    <span>Story Content</span>
                                                </div>
                                                <div class="col-8 col-sm-9 text-dark fs-0-8 font-medium line-height-1-5">
                                                    <?php 
                                                    $plainContent = strip_tags($data['content'] ?? '');
                                                    $excerpt = (mb_strlen($plainContent) > 200) ? mb_substr($plainContent, 0, 200) . '...' : $plainContent;
                                                    echo htmlspecialchars($excerpt ?: '—');
                                                    ?>
                                                </div>
                                            </div>
                                            <div class="row align-items-start py-1">
                                                <div class="col-4 col-sm-3 text-secondary fw-semibold fs-0-8 d-flex align-items-center gap-2">
                                                    <i data-lucide="sparkles" class="text-primary" style="width: 14px; height: 14px;"></i>
                                                    <span>Accent Text</span>
                                                </div>
                                                <div class="col-8 col-sm-9 text-dark fs-0-8 font-medium">
                                                    <?= htmlspecialchars($data['accent_text'] ?? '—') ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <div class="card bg-light border-0 p-3 rounded h-100">
                                            <span class="d-block text-secondary fw-semibold fs-0-75 text-uppercase letter-spacing-pos-05 mb-2">Section Image</span>
                                            <?php if (!empty($data['image_url'])): ?>
                                                <div class="rounded overflow-hidden shadow-sm border bg-white" style="width: 100%; height: 130px;">
                                                    <img src="<?= htmlspecialchars($data['image_url']) ?>" alt="Story image preview" 
                                                         style="width: 100%; height: 100%; object-fit: cover;" loading="lazy">
                                                </div>
                                            <?php else: ?>
                                                <div class="rounded border border-dashed bg-white d-flex flex-column align-items-center justify-content-center text-muted" 
                                                     style="width: 100%; height: 130px;">
                                                    <i data-lucide="image" class="text-secondary mb-1" style="width: 20px; height: 20px;"></i>
                                                    <span style="font-size: 0.75rem;">No Image Selected</span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>

                <!-- ── TAB 3: FEATURES GRID ── -->
                <?php if ($activeTab === 'features'): ?>
                    <div class="d-flex flex-column gap-4">
                        
                        <!-- A. Features Section Header Card -->
                        <?php $meta = $metadata['features_header']; $data = $sections['features_header'] ?? []; ?>
                        <div class="card-premium p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom border-light-subtle">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-premium-soft-primary tech-data px-2 py-1 fs-0-75">Header</span>
                                    <h3 class="h6 fw-bold mb-0 text-dark"><?= htmlspecialchars($meta['name']) ?></h3>
                                </div>
                                <a href="<?= BASE_URL ?>about/edit.php?section=features_header" class="btn btn-premium-primary btn-sm d-flex align-items-center gap-2 px-3">
                                    <i data-lucide="pencil" class="icon-xs"></i>
                                    <span>Edit Header</span>
                                </a>
                            </div>
                            <p class="text-secondary fs-0-78 mb-4"><?= htmlspecialchars($meta['description']) ?></p>
                            
                            <div class="d-flex flex-column gap-3">
                                <div class="row align-items-start py-1">
                                    <div class="col-4 col-sm-3 text-secondary fw-semibold fs-0-8 d-flex align-items-center gap-2">
                                        <i data-lucide="tag" class="text-primary" style="width: 14px; height: 14px;"></i>
                                        <span>Features Sub-Label</span>
                                    </div>
                                    <div class="col-8 col-sm-9 text-dark fs-0-8 font-medium">
                                        <?= htmlspecialchars($data['section_subtitle'] ?? '—') ?>
                                    </div>
                                </div>
                                <div class="row align-items-start py-1">
                                    <div class="col-4 col-sm-3 text-secondary fw-semibold fs-0-8 d-flex align-items-center gap-2">
                                        <i data-lucide="heading" class="text-primary" style="width: 14px; height: 14px;"></i>
                                        <span>Features Title</span>
                                    </div>
                                    <div class="col-8 col-sm-9 text-dark fs-0-8 font-medium">
                                        <?= htmlspecialchars($data['section_title'] ?? '—') ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- B. Dynamic Feature Cards List (CRUD) -->
                        <div class="card-premium p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom border-light-subtle">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-premium-soft-primary tech-data px-2 py-1 fs-0-75">Features Grid</span>
                                    <h3 class="h6 fw-bold mb-0 text-dark">Why Choose CloudCush Cards</h3>
                                </div>
                                <a href="<?= BASE_URL ?>about/edit.php?type=feature" class="btn btn-premium-primary btn-sm d-flex align-items-center gap-2 px-3">
                                    <i data-lucide="plus" class="icon-xs"></i>
                                    <span>Add Feature Card</span>
                                </a>
                            </div>
                            
                            <?php if (empty($features)): ?>
                                <div class="text-center py-5">
                                    <div class="mb-3 text-muted">
                                        <i data-lucide="grid" style="width: 48px; height: 48px; stroke-width: 1;"></i>
                                    </div>
                                    <h4 class="h6 fw-semibold text-dark mb-1">No feature cards added yet</h4>
                                    <p class="text-secondary fs-0-78 max-width-320-px mx-auto mb-0">
                                        Add dynamic cards to explain why parents should choose CloudCush.
                                    </p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-premium align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th style="width: 80px;">Icon</th>
                                                <th>Title</th>
                                                <th>Description</th>
                                                <th style="width: 100px;">Sort Order</th>
                                                <th class="text-end" style="width: 120px;">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($features as $feat): ?>
                                                <tr>
                                                    <td class="tech-data">
                                                        <div class="d-flex align-items-center justify-content-center bg-light text-primary rounded-circle" style="width: 38px; height: 38px;">
                                                            <i class="<?= htmlspecialchars($feat['icon_class'] ?: 'ri-checkbox-circle-line') ?> fs-5"></i>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="fw-semibold text-dark fs-0-82"><?= htmlspecialchars($feat['title']) ?></span>
                                                    </td>
                                                    <td>
                                                        <div class="fs-0-78 text-secondary max-width-320-px text-truncate" title="<?= htmlspecialchars($feat['description']) ?>">
                                                            <?= htmlspecialchars($feat['description']) ?>
                                                        </div>
                                                    </td>
                                                    <td class="tech-data text-center">
                                                        <span class="badge bg-light text-secondary border px-2 py-1"><?= intval($feat['sort_order']) ?></span>
                                                    </td>
                                                    <td class="text-end">
                                                        <div class="d-inline-flex gap-1">
                                                            <a href="<?= BASE_URL ?>about/edit.php?type=feature&id=<?= $feat['id'] ?>" class="btn-action btn-edit-square" title="Edit Card">
                                                                <i data-lucide="pencil" class="icon-sm"></i>
                                                            </a>
                                                            <button type="button" class="btn-action btn-delete-square btn-delete-feature" data-id="<?= $feat['id'] ?>" data-name="<?= htmlspecialchars($feat['title']) ?>" title="Delete Card">
                                                                <i data-lucide="trash-2" class="icon-sm"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>

                        </div>

                    </div>
                <?php endif; ?>

                <!-- ── TAB 4: PHILOSOPHY ── -->
                <?php if ($activeTab === 'philosophy'): $meta = $metadata['philosophy']; $data = $sections['philosophy'] ?? []; ?>
                    <div class="card-premium p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom border-light-subtle">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-premium-soft-primary tech-data px-2 py-1 fs-0-75">Section</span>
                                <h3 class="h6 fw-bold mb-0 text-dark"><?= htmlspecialchars($meta['name']) ?></h3>
                            </div>
                            <a href="<?= BASE_URL ?>about/edit.php?section=philosophy" class="btn btn-premium-primary btn-sm d-flex align-items-center gap-2 px-3">
                                <i data-lucide="pencil" class="icon-xs"></i>
                                <span>Edit Section</span>
                            </a>
                        </div>
                        <p class="text-secondary fs-0-78 mb-4"><?= htmlspecialchars($meta['description']) ?></p>
                        
                        <div class="row g-4">
                            <div class="col-12 col-md-8">
                                <div class="d-flex flex-column gap-3">
                                    <div class="row align-items-start py-1">
                                        <div class="col-4 col-sm-3 text-secondary fw-semibold fs-0-8 d-flex align-items-center gap-2">
                                            <i data-lucide="tag" class="text-primary" style="width: 14px; height: 14px;"></i>
                                            <span>Sub-Label</span>
                                        </div>
                                        <div class="col-8 col-sm-9 text-dark fs-0-8 font-medium">
                                            <?= htmlspecialchars($data['section_subtitle'] ?? '—') ?>
                                        </div>
                                    </div>
                                    <div class="row align-items-start py-1">
                                        <div class="col-4 col-sm-3 text-secondary fw-semibold fs-0-8 d-flex align-items-center gap-2">
                                            <i data-lucide="quote" class="text-primary" style="width: 14px; height: 14px;"></i>
                                            <span>Quote Text</span>
                                        </div>
                                        <div class="col-8 col-sm-9 text-dark fs-0-8 font-medium line-height-1-5">
                                            <?= htmlspecialchars($data['section_title'] ?? '—') ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="card bg-light border-0 p-3 rounded h-100">
                                    <span class="d-block text-secondary fw-semibold fs-0-75 text-uppercase letter-spacing-pos-05 mb-2">Parallax Image</span>
                                    <?php if (!empty($data['image_url'])): ?>
                                        <div class="rounded overflow-hidden shadow-sm border bg-white" style="width: 100%; height: 130px;">
                                            <img src="<?= htmlspecialchars($data['image_url']) ?>" alt="Philosophy image preview" 
                                                 style="width: 100%; height: 100%; object-fit: cover;" loading="lazy">
                                        </div>
                                    <?php else: ?>
                                        <div class="rounded border border-dashed bg-white d-flex flex-column align-items-center justify-content-center text-muted" 
                                             style="width: 100%; height: 130px;">
                                            <i data-lucide="image" class="text-secondary mb-1" style="width: 20px; height: 20px;"></i>
                                            <span style="font-size: 0.75rem;">No Image Selected</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- ── TAB 5: FAQ & CTA ── -->
                <?php if ($activeTab === 'faq-cta'): ?>
                    <div class="d-flex flex-column gap-4">
                        
                        <!-- FAQ Header Card -->
                        <?php $meta = $metadata['about_faq_header']; $data = $sections['about_faq_header'] ?? []; ?>
                        <div class="card-premium p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom border-light-subtle">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-premium-soft-primary tech-data px-2 py-1 fs-0-75">FAQ Header</span>
                                    <h3 class="h6 fw-bold mb-0 text-dark"><?= htmlspecialchars($meta['name']) ?></h3>
                                </div>
                                <a href="<?= BASE_URL ?>about/edit.php?section=about_faq_header" class="btn btn-premium-primary btn-sm d-flex align-items-center gap-2 px-3">
                                    <i data-lucide="pencil" class="icon-xs"></i>
                                    <span>Edit FAQ Header</span>
                                </a>
                            </div>
                            <p class="text-secondary fs-0-78 mb-4"><?= htmlspecialchars($meta['description']) ?></p>
                            
                            <div class="d-flex flex-column gap-3">
                                <div class="row align-items-start py-1">
                                    <div class="col-4 col-sm-3 text-secondary fw-semibold fs-0-8 d-flex align-items-center gap-2">
                                        <i data-lucide="tag" class="text-primary" style="width: 14px; height: 14px;"></i>
                                        <span>Sub-Label</span>
                                    </div>
                                    <div class="col-8 col-sm-9 text-dark fs-0-8 font-medium">
                                        <?= htmlspecialchars($data['section_subtitle'] ?? '—') ?>
                                    </div>
                                </div>
                                <div class="row align-items-start py-1">
                                    <div class="col-4 col-sm-3 text-secondary fw-semibold fs-0-8 d-flex align-items-center gap-2">
                                        <i data-lucide="heading" class="text-primary" style="width: 14px; height: 14px;"></i>
                                        <span>Title Heading</span>
                                    </div>
                                    <div class="col-8 col-sm-9 text-dark fs-0-8 font-medium">
                                        <?= htmlspecialchars($data['section_title'] ?? '—') ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Dynamic FAQs Accordion Items List (CRUD) -->
                        <input type="hidden" id="deleteFaqHandlerUrl" value="<?= BASE_URL ?>about/index.php?action=delete_faq">
                        <div class="card-premium p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom border-light-subtle">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-premium-soft-primary tech-data px-2 py-1 fs-0-75">Accordion</span>
                                    <h3 class="h6 fw-bold mb-0 text-dark">About Page Accordion FAQs</h3>
                                </div>
                                <a href="<?= BASE_URL ?>about/edit.php?type=faq" class="btn btn-premium-primary btn-sm d-flex align-items-center gap-2 px-3">
                                    <i data-lucide="plus" class="icon-xs"></i>
                                    <span>Add FAQ Card</span>
                                </a>
                            </div>
                            
                            <?php if (empty($aboutFaqs)): ?>
                                <div class="text-center py-5">
                                    <div class="mb-3 text-muted">
                                        <i data-lucide="help-circle" style="width: 48px; height: 48px; stroke-width: 1;"></i>
                                    </div>
                                    <h4 class="h6 fw-semibold text-dark mb-1">No FAQs added yet</h4>
                                    <p class="text-secondary fs-0-78 max-width-320-px mx-auto mb-0">
                                        Add dynamic question cards to build the About Page interactive accordion.
                                    </p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-premium align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>Question</th>
                                                <th>Answer Preview</th>
                                                <th style="width: 100px;">Sort Order</th>
                                                <th class="text-end" style="width: 120px;">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($aboutFaqs as $afaq): ?>
                                                <tr>
                                                    <td>
                                                        <span class="fw-semibold text-dark fs-0-82"><?= htmlspecialchars($afaq['question']) ?></span>
                                                    </td>
                                                    <td>
                                                        <div class="fs-0-78 text-secondary max-width-320-px text-truncate" title="<?= htmlspecialchars(strip_tags($afaq['answer'])) ?>">
                                                            <?= htmlspecialchars(strip_tags($afaq['answer'])) ?>
                                                        </div>
                                                    </td>
                                                    <td class="tech-data text-center">
                                                        <span class="badge bg-light text-secondary border px-2 py-1"><?= intval($afaq['sort_order']) ?></span>
                                                    </td>
                                                    <td class="text-end">
                                                        <div class="d-inline-flex gap-1">
                                                            <a href="<?= BASE_URL ?>about/edit.php?type=faq&id=<?= $afaq['id'] ?>" class="btn-action btn-edit-square" title="Edit FAQ">
                                                                <i data-lucide="pencil" class="icon-sm"></i>
                                                            </a>
                                                            <button type="button" class="btn-action btn-delete-square btn-delete-faq" data-id="<?= $afaq['id'] ?>" data-name="<?= htmlspecialchars($afaq['question']) ?>" title="Delete FAQ">
                                                                <i data-lucide="trash-2" class="icon-sm"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- CTA Section Card -->
                        <?php $meta = $metadata['cta']; $data = $sections['cta'] ?? []; ?>
                        <div class="card-premium p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom border-light-subtle">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-premium-soft-primary tech-data px-2 py-1 fs-0-75">Final CTA</span>
                                    <h3 class="h6 fw-bold mb-0 text-dark"><?= htmlspecialchars($meta['name']) ?></h3>
                                </div>
                                <a href="<?= BASE_URL ?>about/edit.php?section=cta" class="btn btn-premium-primary btn-sm d-flex align-items-center gap-2 px-3">
                                    <i data-lucide="pencil" class="icon-xs"></i>
                                    <span>Edit CTA Section</span>
                                </a>
                            </div>
                            <p class="text-secondary fs-0-78 mb-4"><?= htmlspecialchars($meta['description']) ?></p>
                            
                            <div class="d-flex flex-column gap-3">
                                <div class="row align-items-start py-1">
                                    <div class="col-4 col-sm-3 text-secondary fw-semibold fs-0-8 d-flex align-items-center gap-2">
                                        <i data-lucide="heading" class="text-primary" style="width: 14px; height: 14px;"></i>
                                        <span>CTA Heading</span>
                                    </div>
                                    <div class="col-8 col-sm-9 text-dark fs-0-8 font-medium">
                                        <?= htmlspecialchars($data['section_title'] ?? '—') ?>
                                    </div>
                                </div>
                                <div class="row align-items-start py-1">
                                    <div class="col-4 col-sm-3 text-secondary fw-semibold fs-0-8 d-flex align-items-center gap-2">
                                        <i data-lucide="align-left" class="text-primary" style="width: 14px; height: 14px;"></i>
                                        <span>CTA Description</span>
                                    </div>
                                    <div class="col-8 col-sm-9 text-dark fs-0-8 font-medium line-height-1-5">
                                        <?= htmlspecialchars($data['content'] ?? '—') ?>
                                    </div>
                                </div>
                                <!-- Button 1 Details -->
                                <div class="row align-items-start py-1 border-top pt-2 mt-1">
                                    <div class="col-4 col-sm-3 text-secondary fw-semibold fs-0-8 d-flex align-items-center gap-2">
                                        <i data-lucide="square-play" class="text-primary" style="width: 14px; height: 14px;"></i>
                                        <span>Button 1 Action</span>
                                    </div>
                                    <div class="col-8 col-sm-9 text-dark fs-0-8 font-medium">
                                        <span class="badge bg-light text-dark border me-2 py-1 px-2"><?= htmlspecialchars($data['btn_text_1'] ?? '—') ?></span>
                                        <code class="text-secondary" style="font-size:0.75rem;"><?= htmlspecialchars($data['btn_url_1'] ?? '—') ?></code>
                                    </div>
                                </div>
                                <!-- Button 2 Details -->
                                <div class="row align-items-start py-1 border-top pt-2">
                                    <div class="col-4 col-sm-3 text-secondary fw-semibold fs-0-8 d-flex align-items-center gap-2">
                                        <i data-lucide="square-play" class="text-primary" style="width: 14px; height: 14px;"></i>
                                        <span>Button 2 Action</span>
                                    </div>
                                    <div class="col-8 col-sm-9 text-dark fs-0-8 font-medium">
                                        <span class="badge bg-light text-dark border me-2 py-1 px-2"><?= htmlspecialchars($data['btn_text_2'] ?? '—') ?></span>
                                        <code class="text-secondary" style="font-size:0.75rem;"><?= htmlspecialchars($data['btn_url_2'] ?? '—') ?></code>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                <?php endif; ?>



            </div>

        </div><!-- /container-fluid -->
    </div><!-- /page-content-wrapper -->
</div><!-- /wrapper -->

<?php include __DIR__ . '/../includes/footer.php'; ?>
