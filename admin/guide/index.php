<?php
// admin/guide/index.php — Diaper Guide Settings Dashboard
require_once __DIR__ . '/../includes/auth-check.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/guide-helper.php';

// Check if dynamic delete request is being processed
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $action = trim($_GET['action'] ?? '');
    if ($id) {
        if ($action === 'delete_timeline') {
            $item = getGuideTimelineItem($id);
            if ($item && !empty($item['image_url']) && str_contains($item['image_url'], UPLOAD_URL)) {
                $localPath = str_replace(UPLOAD_URL, UPLOAD_DIR, $item['image_url']);
                if (file_exists($localPath)) @unlink($localPath);
            }
            if (deleteGuideTimelineItem($id)) {
                $_SESSION['flash_message'] = 'Timeline step deleted successfully.';
                $_SESSION['flash_type']    = 'success';
            } else {
                $_SESSION['flash_message'] = 'Failed to delete timeline step.';
                $_SESSION['flash_type']    = 'error';
            }
            header('Location: ' . BASE_URL . 'guide/?tab=timeline');
            exit;
        } elseif ($action === 'delete_metric') {
            if (deleteGuideMetric($id)) {
                $_SESSION['flash_message'] = 'Metric card deleted successfully.';
                $_SESSION['flash_type']    = 'success';
            } else {
                $_SESSION['flash_message'] = 'Failed to delete metric card.';
                $_SESSION['flash_type']    = 'error';
            }
            header('Location: ' . BASE_URL . 'guide/?tab=metrics');
            exit;
        } elseif ($action === 'delete_layer') {
            $item = getGuideLayer($id);
            if ($item && !empty($item['image_url']) && str_contains($item['image_url'], UPLOAD_URL)) {
                $localPath = str_replace(UPLOAD_URL, UPLOAD_DIR, $item['image_url']);
                if (file_exists($localPath)) @unlink($localPath);
            }
            if (deleteGuideLayer($id)) {
                $_SESSION['flash_message'] = 'Visual story layer deleted successfully.';
                $_SESSION['flash_type']    = 'success';
            } else {
                $_SESSION['flash_message'] = 'Failed to delete visual story layer.';
                $_SESSION['flash_type']    = 'error';
            }
            header('Location: ' . BASE_URL . 'guide/?tab=layers');
            exit;
        }
    }
}

$page_title  = 'CloudCush Admin — Diaper Guide';
$active_page = 'guide';

// Fetch current values
$sections = getGuideSections();
$metadata = getGuideSectionMetadata();
$timeline = getGuideTimeline();
$metrics  = getGuideMetrics();
$layers   = getGuideLayers();

// Determine active tab from URL parameter
$activeTab = trim($_GET['tab'] ?? 'hero');
$allowedTabs = ['hero', 'timeline', 'metrics', 'layers', 'quote', 'cta'];
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
                    <h1 class="h4 fw-bold mb-1 page-heading">Diaper Guide Settings</h1>
                    <p class="text-secondary mb-0 fs-0-82">
                        Manage all the text, timelines, interactive layers, and comfort metrics on the public Diaper Guide page.
                    </p>
                </div>
                <div>
                    <a href="../../diaper-guide.php" target="_blank" class="btn btn-premium-secondary btn-sm d-flex align-items-center gap-2">
                        <i data-lucide="external-link" class="icon-xs"></i>
                        <span>View Live Guide Page</span>
                    </a>
                </div>
            </div>

            <!-- Tabbed Navigation Links (Premium HSL Pills) -->
            <div class="mb-4">
                <ul class="nav nav-pills nav-pills-premium gap-2" id="guidePageTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a href="?tab=hero" class="nav-link <?= $activeTab === 'hero' ? 'active' : '' ?>">
                            <i data-lucide="layout-template" class="icon-sm"></i>
                            <span>Hero Banner</span>
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a href="?tab=timeline" class="nav-link <?= $activeTab === 'timeline' ? 'active' : '' ?>">
                            <i data-lucide="milestone" class="icon-sm"></i>
                            <span>Timeline Steps</span>
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a href="?tab=metrics" class="nav-link <?= $activeTab === 'metrics' ? 'active' : '' ?>">
                            <i data-lucide="bar-chart-2" class="icon-sm"></i>
                            <span>Comfort Metrics</span>
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a href="?tab=layers" class="nav-link <?= $activeTab === 'layers' ? 'active' : '' ?>">
                            <i data-lucide="layers" class="icon-sm"></i>
                            <span>Story Layers</span>
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a href="?tab=quote" class="nav-link <?= $activeTab === 'quote' ? 'active' : '' ?>">
                            <i data-lucide="quote" class="icon-sm"></i>
                            <span>Pediatrician Quote</span>
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a href="?tab=cta" class="nav-link <?= $activeTab === 'cta' ? 'active' : '' ?>">
                            <i data-lucide="help-circle" class="icon-sm"></i>
                            <span>CTA Block</span>
                        </a>
                    </li>
                </ul>
            </div>

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
                            <a href="<?= BASE_URL ?>guide/edit.php?section=hero" class="btn btn-premium-primary btn-sm d-flex align-items-center gap-2 px-3">
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
                                    <?= htmlspecialchars($data['section_title'] ?? '—') ?>
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

                <!-- ── TAB 2: TIMELINE STEPS ── -->
                <?php if ($activeTab === 'timeline'): ?>
                    <input type="hidden" id="deleteTimelineHandlerUrl" value="<?= BASE_URL ?>guide/index.php?action=delete_timeline">
                    <div class="card-premium p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom border-light-subtle">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-premium-soft-primary tech-data px-2 py-1 fs-0-75">Milestones</span>
                                <h3 class="h6 fw-bold mb-0 text-dark">Timeline Experience steps</h3>
                            </div>
                            <a href="<?= BASE_URL ?>guide/edit.php?type=timeline" class="btn btn-premium-primary btn-sm d-flex align-items-center gap-2 px-3">
                                <i data-lucide="plus" class="icon-xs"></i>
                                <span>Add Milestone</span>
                            </a>
                        </div>
                        
                        <?php if (empty($timeline)): ?>
                            <div class="text-center py-5">
                                <div class="mb-3 text-muted">
                                    <i data-lucide="milestone" style="width: 48px; height: 48px; stroke-width: 1;"></i>
                                </div>
                                <h4 class="h6 fw-semibold text-dark mb-1">No steps added yet</h4>
                                <p class="text-secondary fs-0-78 max-width-320-px mx-auto mb-0">
                                    Add interactive timeline cards to populate the scroll guide journey.
                                </p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-premium align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Image</th>
                                            <th>Milestone Label</th>
                                            <th>Title & Heading</th>
                                            <th style="width: 100px;">Sort Order</th>
                                            <th class="text-end" style="width: 120px;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($timeline as $t): ?>
                                            <tr>
                                                <td>
                                                    <?php if (!empty($t['image_url'])): ?>
                                                        <img src="<?= htmlspecialchars($t['image_url']) ?>" alt="Milestone Thumbnail" 
                                                             style="width: 50px; height: 40px; object-fit: cover;" class="rounded border">
                                                    <?php else: ?>
                                                        <div class="rounded border bg-light d-flex align-items-center justify-content-center text-muted" 
                                                             style="width: 50px; height: 40px;">
                                                            <i data-lucide="image" class="icon-xs text-secondary"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-premium-soft-primary px-2 py-1 fs-0-75"><?= htmlspecialchars($t['subtitle']) ?></span>
                                                </td>
                                                <td>
                                                    <div class="fw-semibold text-dark fs-0-82"><?= htmlspecialchars($t['title']) ?></div>
                                                    <small class="text-secondary"><?= htmlspecialchars($t['title_heading']) ?></small>
                                                </td>
                                                <td class="tech-data text-center">
                                                    <span class="badge bg-light text-secondary border px-2 py-1"><?= intval($t['sort_order']) ?></span>
                                                </td>
                                                <td class="text-end">
                                                    <div class="d-inline-flex gap-1">
                                                        <a href="<?= BASE_URL ?>guide/edit.php?type=timeline&id=<?= $t['id'] ?>" class="btn-action btn-edit-square" title="Edit Milestone">
                                                            <i data-lucide="pencil" class="icon-sm"></i>
                                                        </a>
                                                        <button type="button" class="btn-action btn-delete-square btn-delete-timeline" data-id="<?= $t['id'] ?>" data-name="<?= htmlspecialchars($t['title']) ?>" title="Delete Milestone">
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
                <?php endif; ?>

                <!-- ── TAB 3: COMFORT METRICS ── -->
                <?php if ($activeTab === 'metrics'): ?>
                    <input type="hidden" id="deleteMetricHandlerUrl" value="<?= BASE_URL ?>guide/index.php?action=delete_metric">
                    
                    <!-- Metrics Header -->
                    <?php $meta = $metadata['metrics_header']; $data = $sections['metrics_header'] ?? []; ?>
                    <div class="card-premium p-4 mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom border-light-subtle">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-premium-soft-primary tech-data px-2 py-1 fs-0-75">Header</span>
                                <h3 class="h6 fw-bold mb-0 text-dark"><?= htmlspecialchars($meta['name']) ?></h3>
                            </div>
                            <a href="<?= BASE_URL ?>guide/edit.php?section=metrics_header" class="btn btn-premium-primary btn-sm d-flex align-items-center gap-2 px-3">
                                <i data-lucide="pencil" class="icon-xs"></i>
                                <span>Edit Header</span>
                            </a>
                        </div>
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
                                    <?= htmlspecialchars($data['section_title'] ?? '—') ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Metrics Cards Grid List -->
                    <div class="card-premium p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom border-light-subtle">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-premium-soft-primary tech-data px-2 py-1 fs-0-75">Infographics</span>
                                <h3 class="h6 fw-bold mb-0 text-dark">Comfort Metrics Cards</h3>
                            </div>
                            <a href="<?= BASE_URL ?>guide/edit.php?type=metric" class="btn btn-premium-primary btn-sm d-flex align-items-center gap-2 px-3">
                                <i data-lucide="plus" class="icon-xs"></i>
                                <span>Add Metric</span>
                            </a>
                        </div>
                        
                        <?php if (empty($metrics)): ?>
                            <div class="text-center py-5">
                                <div class="mb-3 text-muted">
                                    <i data-lucide="bar-chart-2" style="width: 48px; height: 48px; stroke-width: 1;"></i>
                                </div>
                                <h4 class="h6 fw-semibold text-dark mb-1">No metrics added yet</h4>
                                <p class="text-secondary fs-0-78 max-width-320-px mx-auto mb-0">
                                    Add metric cards to display scientific evidence and trust scores.
                                </p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-premium align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Icon</th>
                                            <th>Metric Label</th>
                                            <th>Display Value</th>
                                            <th>Format Option</th>
                                            <th style="width: 100px;">Sort Order</th>
                                            <th class="text-end" style="width: 120px;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($metrics as $m): ?>
                                            <tr>
                                                <td>
                                                    <span class="avatar avatar-xs bg-premium-soft-primary rounded text-primary fs-5 d-flex align-items-center justify-content-center p-1" style="width:32px;height:32px;">
                                                        <i class="<?= htmlspecialchars($m['icon_class']) ?>"></i>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="fw-semibold text-dark fs-0-82"><?= htmlspecialchars($m['label']) ?></span>
                                                    <div class="fs-0-7 text-secondary text-truncate max-width-280-px" title="<?= htmlspecialchars($m['description']) ?>">
                                                        <?= htmlspecialchars($m['description']) ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="fw-bold text-dark"><?= htmlspecialchars($m['target_value']) ?></span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-light border text-dark px-2 py-1 text-capitalize"><?= htmlspecialchars($m['suffix_type']) ?></span>
                                                </td>
                                                <td class="tech-data text-center">
                                                    <span class="badge bg-light text-secondary border px-2 py-1"><?= intval($m['sort_order']) ?></span>
                                                </td>
                                                <td class="text-end">
                                                    <div class="d-inline-flex gap-1">
                                                        <a href="<?= BASE_URL ?>guide/edit.php?type=metric&id=<?= $m['id'] ?>" class="btn-action btn-edit-square" title="Edit Metric">
                                                            <i data-lucide="pencil" class="icon-sm"></i>
                                                        </a>
                                                        <button type="button" class="btn-action btn-delete-square btn-delete-metric" data-id="<?= $m['id'] ?>" data-name="<?= htmlspecialchars($m['label']) ?>" title="Delete Metric">
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
                <?php endif; ?>

                <!-- ── TAB 4: STORY LAYERS ── -->
                <?php if ($activeTab === 'layers'): ?>
                    <input type="hidden" id="deleteLayerHandlerUrl" value="<?= BASE_URL ?>guide/index.php?action=delete_layer">
                    <div class="card-premium p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom border-light-subtle">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-premium-soft-primary tech-data px-2 py-1 fs-0-75">Layers</span>
                                <h3 class="h6 fw-bold mb-0 text-dark">Visual Story diaper layers</h3>
                            </div>
                            <a href="<?= BASE_URL ?>guide/edit.php?type=layer" class="btn btn-premium-primary btn-sm d-flex align-items-center gap-2 px-3">
                                <i data-lucide="plus" class="icon-xs"></i>
                                <span>Add Layer</span>
                            </a>
                        </div>
                        
                        <?php if (empty($layers)): ?>
                            <div class="text-center py-5">
                                <div class="mb-3 text-muted">
                                    <i data-lucide="layers" style="width: 48px; height: 48px; stroke-width: 1;"></i>
                                </div>
                                <h4 class="h6 fw-semibold text-dark mb-1">No layers added yet</h4>
                                <p class="text-secondary fs-0-78 max-width-320-px mx-auto mb-0">
                                    Add diaper structure layer cards to explain materials.
                                </p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-premium align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Image</th>
                                            <th>Layer Index</th>
                                            <th>Layer Title</th>
                                            <th>Specs Tags</th>
                                            <th style="width: 100px;">Sort Order</th>
                                            <th class="text-end" style="width: 120px;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($layers as $l): ?>
                                            <tr>
                                                <td>
                                                    <?php if (!empty($l['image_url'])): ?>
                                                        <img src="<?= htmlspecialchars($l['image_url']) ?>" alt="Layer Thumbnail" 
                                                             style="width: 50px; height: 40px; object-fit: cover;" class="rounded border">
                                                    <?php else: ?>
                                                        <div class="rounded border bg-light d-flex align-items-center justify-content-center text-muted" 
                                                             style="width: 50px; height: 40px;">
                                                            <i data-lucide="image" class="icon-xs text-secondary"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-premium-soft-primary px-2 py-1 fs-0-75"><?= htmlspecialchars($l['badge']) ?></span>
                                                </td>
                                                <td>
                                                    <div class="fw-semibold text-dark fs-0-82"><?= htmlspecialchars($l['title']) ?></div>
                                                    <small class="text-secondary text-truncate max-width-280-px d-block" title="<?= htmlspecialchars($l['caption']) ?>">
                                                        <?= htmlspecialchars($l['caption']) ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <div class="fs-0-75 text-secondary text-truncate max-width-280-px" title="<?= htmlspecialchars($l['specs']) ?>">
                                                        <?= htmlspecialchars($l['specs']) ?>
                                                    </div>
                                                </td>
                                                <td class="tech-data text-center">
                                                    <span class="badge bg-light text-secondary border px-2 py-1"><?= intval($l['sort_order']) ?></span>
                                                </td>
                                                <td class="text-end">
                                                    <div class="d-inline-flex gap-1">
                                                        <a href="<?= BASE_URL ?>guide/edit.php?type=layer&id=<?= $l['id'] ?>" class="btn-action btn-edit-square" title="Edit Layer">
                                                            <i data-lucide="pencil" class="icon-sm"></i>
                                                        </a>
                                                        <button type="button" class="btn-action btn-delete-square btn-delete-layer" data-id="<?= $l['id'] ?>" data-name="<?= htmlspecialchars($l['title']) ?>" title="Delete Layer">
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
                <?php endif; ?>

                <!-- ── TAB 5: PEDIATRICIAN QUOTE ── -->
                <?php if ($activeTab === 'quote'): $meta = $metadata['quote']; $data = $sections['quote'] ?? []; ?>
                    <div class="card-premium p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom border-light-subtle">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-premium-soft-primary tech-data px-2 py-1 fs-0-75">Quote</span>
                                <h3 class="h6 fw-bold mb-0 text-dark"><?= htmlspecialchars($meta['name']) ?></h3>
                            </div>
                            <a href="<?= BASE_URL ?>guide/edit.php?section=quote" class="btn btn-premium-primary btn-sm d-flex align-items-center gap-2 px-3">
                                <i data-lucide="pencil" class="icon-xs"></i>
                                <span>Edit Quote</span>
                            </a>
                        </div>
                        <p class="text-secondary fs-0-78 mb-4"><?= htmlspecialchars($meta['description']) ?></p>
                        
                        <div class="d-flex flex-column gap-3">
                            <div class="row align-items-start py-1">
                                <div class="col-4 col-sm-3 text-secondary fw-semibold fs-0-8 d-flex align-items-center gap-2">
                                    <i data-lucide="quote" class="text-primary" style="width: 14px; height: 14px;"></i>
                                    <span>Pediatrician Quote</span>
                                </div>
                                <div class="col-8 col-sm-9 text-dark fs-0-8 font-medium line-height-1-5">
                                    <?= htmlspecialchars($data['content'] ?? '—') ?>
                                </div>
                            </div>
                            <div class="row align-items-start py-1">
                                <div class="col-4 col-sm-3 text-secondary fw-semibold fs-0-8 d-flex align-items-center gap-2">
                                    <i data-lucide="user" class="text-primary" style="width: 14px; height: 14px;"></i>
                                    <span>Author Name</span>
                                </div>
                                <div class="col-8 col-sm-9 text-dark fs-0-8 font-medium">
                                    <?= htmlspecialchars($data['section_title'] ?? '—') ?>
                                </div>
                            </div>
                            <div class="row align-items-start py-1">
                                <div class="col-4 col-sm-3 text-secondary fw-semibold fs-0-8 d-flex align-items-center gap-2">
                                    <i data-lucide="tag" class="text-primary" style="width: 14px; height: 14px;"></i>
                                    <span>Author Credentials</span>
                                </div>
                                <div class="col-8 col-sm-9 text-dark fs-0-8 font-medium">
                                    <?= htmlspecialchars($data['section_subtitle'] ?? '—') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- ── TAB 6: CTA BLOCK ── -->
                <?php if ($activeTab === 'cta'): $meta = $metadata['cta']; $data = $sections['cta'] ?? []; ?>
                    <div class="card-premium p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom border-light-subtle">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-premium-soft-primary tech-data px-2 py-1 fs-0-75">Final CTA</span>
                                <h3 class="h6 fw-bold mb-0 text-dark"><?= htmlspecialchars($meta['name']) ?></h3>
                            </div>
                            <a href="<?= BASE_URL ?>guide/edit.php?section=cta" class="btn btn-premium-primary btn-sm d-flex align-items-center gap-2 px-3">
                                <i data-lucide="pencil" class="icon-xs"></i>
                                <span>Edit CTA Section</span>
                            </a>
                        </div>
                        <p class="text-secondary fs-0-78 mb-4"><?= htmlspecialchars($meta['description']) ?></p>
                        
                        <div class="d-flex flex-column gap-3">
                            <div class="row align-items-start py-1">
                                <div class="col-4 col-sm-3 text-secondary fw-semibold fs-0-8 d-flex align-items-center gap-2">
                                    <i data-lucide="tag" class="text-primary" style="width: 14px; height: 14px;"></i>
                                    <span>CTA Eyebrow</span>
                                </div>
                                <div class="col-8 col-sm-9 text-dark fs-0-8 font-medium">
                                    <?= htmlspecialchars($data['section_subtitle'] ?? '—') ?>
                                </div>
                            </div>
                            <div class="row align-items-start py-1">
                                <div class="col-4 col-sm-3 text-secondary fw-semibold fs-0-8 d-flex align-items-center gap-2">
                                    <i data-lucide="heading" class="text-primary" style="width: 14px; height: 14px;"></i>
                                    <span>CTA Title Heading</span>
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
                <?php endif; ?>

            </div>

        </div><!-- /container-fluid -->
    </div><!-- /page-content-wrapper -->
</div><!-- /wrapper -->

<?php include __DIR__ . '/../includes/footer.php'; ?>
