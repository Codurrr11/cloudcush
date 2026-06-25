<?php
// admin/home/index.php — Homepage Settings Dashboard
require_once __DIR__ . '/../includes/auth-check.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/home-helper.php';

$page_title  = 'CloudCush Admin — Homepage Settings';
$active_page = 'home';

// Fetch current values
$sections = getHomeSections();
$metadata = getHomeSectionMetadata();
$variants = getHomeAtelierVariants();
$perks    = getHomeCarePlanPerks();
$panels   = getHomeCatnavPanels();

// Determine active tab from URL parameter
$activeTab   = trim($_GET['tab'] ?? 'hero');
$allowedTabs = ['hero', 'atelier', 'perks', 'experience', 'philosophy'];
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
                    <h1 class="h4 fw-bold mb-1 page-heading">Homepage Settings</h1>
                    <p class="text-secondary mb-0 fs-0-82">
                        Manage all the text, headings, images, and videos on the frontend Homepage.
                    </p>
                </div>
                <div>
                    <a href="../../index.php" target="_blank" class="btn btn-premium-secondary btn-sm d-flex align-items-center gap-2">
                        <i data-lucide="external-link" class="icon-xs"></i>
                        <span>View Live Homepage</span>
                    </a>
                </div>
            </div>

            <!-- Tabbed Navigation Links (Premium HSL Pills) -->
            <div class="mb-4">
                <ul class="nav nav-pills nav-pills-premium gap-2" id="homePageTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a href="?tab=hero" class="nav-link <?= $activeTab === 'hero' ? 'active' : '' ?>">
                            <i data-lucide="layout-template" class="icon-sm"></i>
                            <span>Hero &amp; Showcase</span>
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a href="?tab=atelier" class="nav-link <?= $activeTab === 'atelier' ? 'active' : '' ?>">
                            <i data-lucide="layers" class="icon-sm"></i>
                            <span>Sizing Atelier</span>
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a href="?tab=perks" class="nav-link <?= $activeTab === 'perks' ? 'active' : '' ?>">
                            <i data-lucide="sparkles" class="icon-sm"></i>
                            <span>Care Plan Perks</span>
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a href="?tab=experience" class="nav-link <?= $activeTab === 'experience' ? 'active' : '' ?>">
                            <i data-lucide="grid" class="icon-sm"></i>
                            <span>Diaper Experience</span>
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a href="?tab=philosophy" class="nav-link <?= $activeTab === 'philosophy' ? 'active' : '' ?>">
                            <i data-lucide="book-open" class="icon-sm"></i>
                            <span>Philosophy</span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Tab Content Pane Grid -->
            <div>

                <!-- ── TAB 1: HERO & SHOWCASE ── -->
                <?php if ($activeTab === 'hero'): ?>
                    <div class="d-flex flex-column gap-4">

                        <!-- Hero Section Block -->
                        <?php $meta = $metadata['hero']; $data = $sections['hero'] ?? []; ?>
                        <div class="card-premium p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom border-light-subtle">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-premium-soft-primary tech-data px-2 py-1 fs-0-75">Section</span>
                                    <h3 class="h6 fw-bold mb-0 text-dark"><?= htmlspecialchars($meta['name']) ?></h3>
                                </div>
                                <a href="<?= BASE_URL ?>home/edit.php?section=hero" class="btn btn-premium-primary btn-sm d-flex align-items-center gap-2 px-3">
                                    <i data-lucide="pencil" class="icon-xs"></i>
                                    <span>Edit Section</span>
                                </a>
                            </div>
                            <p class="text-secondary fs-0-78 mb-4"><?= htmlspecialchars($meta['description']) ?></p>

                            <div class="row g-4">
                                <div class="col-12 col-lg-8">
                                    <div class="d-flex flex-column gap-3">
                                        <div class="row align-items-start py-1">
                                            <div class="col-4 col-sm-3 text-secondary fw-semibold fs-0-8 d-flex align-items-center gap-2">
                                                <i data-lucide="heading" class="text-primary" style="width: 14px; height: 14px;"></i>
                                                <span>Title Heading</span>
                                            </div>
                                            <div class="col-8 col-sm-9 text-dark fs-0-8 font-medium">
                                                <?= htmlspecialchars($data['section_title'] ?? '—') ?>
                                            </div>
                                        </div>
                                        <div class="row align-items-start py-1">
                                            <div class="col-4 col-sm-3 text-secondary fw-semibold fs-0-8 d-flex align-items-center gap-2">
                                                <i data-lucide="align-left" class="text-primary" style="width: 14px; height: 14px;"></i>
                                                <span>Left Text</span>
                                            </div>
                                            <div class="col-8 col-sm-9 text-dark fs-0-8 font-medium">
                                                <?= htmlspecialchars(strip_tags($data['left_text'] ?? '—')) ?>
                                            </div>
                                        </div>
                                        <div class="row align-items-start py-1">
                                            <div class="col-4 col-sm-3 text-secondary fw-semibold fs-0-8 d-flex align-items-center gap-2">
                                                <i data-lucide="align-right" class="text-primary" style="width: 14px; height: 14px;"></i>
                                                <span>Right Text</span>
                                            </div>
                                            <div class="col-8 col-sm-9 text-dark fs-0-8 font-medium">
                                                <?= htmlspecialchars(strip_tags($data['right_text'] ?? '—')) ?>
                                            </div>
                                        </div>
                                        <div class="row align-items-start py-1">
                                            <div class="col-4 col-sm-3 text-secondary fw-semibold fs-0-8 d-flex align-items-center gap-2">
                                                <i data-lucide="link" class="text-primary" style="width: 14px; height: 14px;"></i>
                                                <span>CTA Target</span>
                                            </div>
                                            <div class="col-8 col-sm-9 text-dark fs-0-8 font-medium">
                                                <span class="badge bg-light text-secondary border px-2 py-1"><?= htmlspecialchars($data['btn_text'] ?? '—') ?></span>
                                                <code class="ms-2 fs-0-75"><?= htmlspecialchars($data['btn_url'] ?? '—') ?></code>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-lg-4">
                                    <div class="card bg-light border-0 p-3 rounded h-100">
                                        <span class="d-block text-secondary fw-semibold fs-0-75 text-uppercase letter-spacing-pos-05 mb-3">Hero Image Layers</span>
                                        <div class="d-flex flex-column gap-2">
                                            <?php for($i = 1; $i <= 3; $i++): $imgKey = "image_url_" . $i; ?>
                                                <div class="d-flex align-items-center gap-2 bg-white p-2 rounded border border-light shadow-xs">
                                                    <?php if (!empty($data[$imgKey])): ?>
                                                        <img src="<?= htmlspecialchars(str_starts_with($data[$imgKey], 'assets/') ? '../../' . $data[$imgKey] : $data[$imgKey]) ?>"
                                                             style="width: 48px; height: 48px; object-fit: cover;" class="rounded border bg-light">
                                                    <?php else: ?>
                                                        <div class="d-flex align-items-center justify-content-center bg-light text-muted rounded border" style="width: 48px; height: 48px;">
                                                            <i data-lucide="image" style="width: 18px; height: 18px;"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div class="overflow-hidden">
                                                        <span class="d-block fw-semibold fs-0-7" style="line-height: 1.2;">Layer 0<?= $i ?></span>
                                                        <span class="d-block text-truncate text-secondary fs-0-6" style="max-width: 180px;" title="<?= htmlspecialchars($data[$imgKey] ?? '') ?>">
                                                            <?= htmlspecialchars($data[$imgKey] ?? 'None') ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Showcase Section Block -->
                        <?php $meta = $metadata['showcase']; $data = $sections['showcase'] ?? []; ?>
                        <div class="card-premium p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom border-light-subtle">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-premium-soft-primary tech-data px-2 py-1 fs-0-75">Section</span>
                                    <h3 class="h6 fw-bold mb-0 text-dark"><?= htmlspecialchars($meta['name']) ?></h3>
                                </div>
                                <a href="<?= BASE_URL ?>home/edit.php?section=showcase" class="btn btn-premium-primary btn-sm d-flex align-items-center gap-2 px-3">
                                    <i data-lucide="pencil" class="icon-xs"></i>
                                    <span>Edit Section</span>
                                </a>
                            </div>
                            <p class="text-secondary fs-0-78 mb-4"><?= htmlspecialchars($meta['description']) ?></p>

                            <div class="row g-4">
                                <div class="col-12 col-lg-8">
                                    <div class="d-flex flex-column gap-3">
                                        <div class="row align-items-start py-1">
                                            <div class="col-4 col-sm-3 text-secondary fw-semibold fs-0-8 d-flex align-items-center gap-2">
                                                <i data-lucide="heading" class="text-primary" style="width: 14px; height: 14px;"></i>
                                                <span>Title</span>
                                            </div>
                                            <div class="col-8 col-sm-9 text-dark fs-0-8 font-medium">
                                                <?= htmlspecialchars(strip_tags($data['section_title'] ?? '—')) ?>
                                            </div>
                                        </div>
                                        <div class="row align-items-start py-1">
                                            <div class="col-4 col-sm-3 text-secondary fw-semibold fs-0-8 d-flex align-items-center gap-2">
                                                <i data-lucide="cloud" class="text-primary" style="width: 14px; height: 14px;"></i>
                                                <span>Cloud Badge</span>
                                            </div>
                                            <div class="col-8 col-sm-9 text-dark fs-0-8 font-medium">
                                                <?= htmlspecialchars($data['badge_label'] ?? '—') ?>
                                            </div>
                                        </div>
                                        <div class="row align-items-start py-1">
                                            <div class="col-4 col-sm-3 text-secondary fw-semibold fs-0-8 d-flex align-items-center gap-2">
                                                <i data-lucide="align-left" class="text-primary" style="width: 14px; height: 14px;"></i>
                                                <span>Description 1</span>
                                            </div>
                                            <div class="col-8 col-sm-9 text-dark fs-0-8 font-medium">
                                                <?= htmlspecialchars($data['desc_1'] ?? '—') ?>
                                            </div>
                                        </div>
                                        <div class="row align-items-start py-1">
                                            <div class="col-4 col-sm-3 text-secondary fw-semibold fs-0-8 d-flex align-items-center gap-2">
                                                <i data-lucide="align-left" class="text-primary" style="width: 14px; height: 14px;"></i>
                                                <span>Description 2</span>
                                            </div>
                                            <div class="col-8 col-sm-9 text-dark fs-0-8 font-medium">
                                                <?= htmlspecialchars($data['desc_2'] ?? '—') ?>
                                            </div>
                                        </div>
                                        <div class="row align-items-start py-1">
                                            <div class="col-4 col-sm-3 text-secondary fw-semibold fs-0-8 d-flex align-items-center gap-2">
                                                <i data-lucide="link" class="text-primary" style="width: 14px; height: 14px;"></i>
                                                <span>CTA Target</span>
                                            </div>
                                            <div class="col-8 col-sm-9 text-dark fs-0-8 font-medium">
                                                <span class="badge bg-light text-secondary border px-2 py-1"><?= htmlspecialchars($data['btn_text'] ?? '—') ?></span>
                                                <code class="ms-2 fs-0-75"><?= htmlspecialchars($data['btn_url'] ?? '—') ?></code>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-lg-4">
                                    <div class="card bg-light border-0 p-3 rounded h-100">
                                        <span class="d-block text-secondary fw-semibold fs-0-75 text-uppercase letter-spacing-pos-05 mb-2">Showcase Video</span>
                                        <div class="bg-white p-2 rounded border border-light shadow-xs d-flex align-items-center gap-2">
                                            <div class="d-flex align-items-center justify-content-center bg-light text-primary rounded border" style="width: 48px; height: 48px;">
                                                <i data-lucide="play" style="width: 18px; height: 18px;"></i>
                                            </div>
                                            <div class="overflow-hidden">
                                                <span class="d-block fw-semibold fs-0-7" style="line-height: 1.2;">Video Path</span>
                                                <span class="d-block text-truncate text-secondary fs-0-6" style="max-width: 180px;" title="<?= htmlspecialchars($data['video_url'] ?? '') ?>">
                                                    <?= htmlspecialchars($data['video_url'] ?? 'None') ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                <?php endif; ?>


                <!-- ── TAB 2: SIZING ATELIER ── -->
                <?php if ($activeTab === 'atelier'): ?>
                    <div class="d-flex flex-column gap-4">

                        <!-- Atelier Header Block -->
                        <?php $meta = $metadata['atelier_header']; $data = $sections['atelier_header'] ?? []; ?>
                        <div class="card-premium p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom border-light-subtle">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-premium-soft-primary tech-data px-2 py-1 fs-0-75">Header</span>
                                    <h3 class="h6 fw-bold mb-0 text-dark"><?= htmlspecialchars($meta['name']) ?></h3>
                                </div>
                                <a href="<?= BASE_URL ?>home/edit.php?section=atelier_header" class="btn btn-premium-primary btn-sm d-flex align-items-center gap-2 px-3">
                                    <i data-lucide="pencil" class="icon-xs"></i>
                                    <span>Edit Header</span>
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
                                        <span>Title</span>
                                    </div>
                                    <div class="col-8 col-sm-9 text-dark fs-0-8 font-medium">
                                        <?= htmlspecialchars(strip_tags($data['section_title'] ?? '—')) ?>
                                    </div>
                                </div>
                                <div class="row align-items-start py-1">
                                    <div class="col-4 col-sm-3 text-secondary fw-semibold fs-0-8 d-flex align-items-center gap-2">
                                        <i data-lucide="align-left" class="text-primary" style="width: 14px; height: 14px;"></i>
                                        <span>Description</span>
                                    </div>
                                    <div class="col-8 col-sm-9 text-dark fs-0-8 font-medium">
                                        <?= htmlspecialchars($data['content'] ?? '—') ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Atelier Milestone Variants List (3 Items) -->
                        <div class="card-premium p-4">
                            <h3 class="h6 fw-bold mb-3 pb-3 border-bottom border-light-subtle text-dark">Sizing Sensation Variants</h3>
                            <div class="table-responsive">
                                <table class="table table-premium align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Image</th>
                                            <th>Milestone Label</th>
                                            <th>Product Name</th>
                                            <th>Metrics (Abs / Str / Soft)</th>
                                            <th class="text-end" style="width: 120px;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($variants as $v): ?>
                                            <tr>
                                                <td class="tech-data">
                                                    <?php if (!empty($v['image_url'])): ?>
                                                        <img src="<?= htmlspecialchars(str_starts_with($v['image_url'], 'assets/') ? '../../' . $v['image_url'] : $v['image_url']) ?>"
                                                             style="width: 44px; height: 44px; object-fit: cover;" class="rounded border bg-light">
                                                    <?php else: ?>
                                                        <div class="d-flex align-items-center justify-content-center bg-light text-muted rounded border" style="width: 44px; height: 44px;">
                                                            <i data-lucide="image" style="width: 18px; height: 18px;"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="fw-semibold text-dark fs-0-82"><?= htmlspecialchars($v['label']) ?></span>
                                                </td>
                                                <td>
                                                    <span class="fs-0-82 text-secondary"><?= htmlspecialchars($v['variant_name']) ?></span>
                                                </td>
                                                <td class="tech-data">
                                                    <span class="badge bg-light text-secondary border font-medium fs-0-7">
                                                        Absorb: <?= intval($v['val_absorbency']) ?>% | Stretch: <?= intval($v['val_stretch']) ?>% | Softness: <?= intval($v['val_softness']) ?>%
                                                    </span>
                                                </td>
                                                <td class="text-end">
                                                    <a href="<?= BASE_URL ?>home/edit.php?type=atelier_variant&id=<?= $v['id'] ?>" class="btn btn-premium-secondary btn-sm px-3 d-inline-flex align-items-center gap-1">
                                                        <i data-lucide="pencil" class="icon-xs"></i>
                                                        <span>Edit</span>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                <?php endif; ?>


                <!-- ── TAB 3: CARE PLAN PERKS ── -->
                <?php if ($activeTab === 'perks'): ?>
                    <div class="d-flex flex-column gap-4">

                        <!-- Care Plan General Settings -->
                        <?php $meta = $metadata['care_plan']; $data = $sections['care_plan'] ?? []; ?>
                        <div class="card-premium p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom border-light-subtle">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-premium-soft-primary tech-data px-2 py-1 fs-0-75">Section</span>
                                    <h3 class="h6 fw-bold mb-0 text-dark"><?= htmlspecialchars($meta['name']) ?></h3>
                                </div>
                                <a href="<?= BASE_URL ?>home/edit.php?section=care_plan" class="btn btn-premium-primary btn-sm d-flex align-items-center gap-2 px-3">
                                    <i data-lucide="pencil" class="icon-xs"></i>
                                    <span>Edit Section</span>
                                </a>
                            </div>
                            <p class="text-secondary fs-0-78 mb-4"><?= htmlspecialchars($meta['description']) ?></p>

                            <div class="row g-4">
                                <div class="col-12 col-lg-8">
                                    <div class="d-flex flex-column gap-3">
                                        <div class="row align-items-start py-1">
                                            <div class="col-4 col-sm-3 text-secondary fw-semibold fs-0-8 d-flex align-items-center gap-2">
                                                <i data-lucide="heading" class="text-primary" style="width: 14px; height: 14px;"></i>
                                                <span>Title</span>
                                            </div>
                                            <div class="col-8 col-sm-9 text-dark fs-0-8 font-medium">
                                                <?= htmlspecialchars(strip_tags($data['section_title'] ?? '—')) ?>
                                            </div>
                                        </div>
                                        <div class="row align-items-start py-1">
                                            <div class="col-4 col-sm-3 text-secondary fw-semibold fs-0-8 d-flex align-items-center gap-2">
                                                <i data-lucide="align-left" class="text-primary" style="width: 14px; height: 14px;"></i>
                                                <span>Description</span>
                                            </div>
                                            <div class="col-8 col-sm-9 text-dark fs-0-8 font-medium">
                                                <?= htmlspecialchars($data['content'] ?? '—') ?>
                                            </div>
                                        </div>
                                        <div class="row align-items-start py-1">
                                            <div class="col-4 col-sm-3 text-secondary fw-semibold fs-0-8 d-flex align-items-center gap-2">
                                                <i data-lucide="link" class="text-primary" style="width: 14px; height: 14px;"></i>
                                                <span>CTA Target</span>
                                            </div>
                                            <div class="col-8 col-sm-9 text-dark fs-0-8 font-medium">
                                                <span class="badge bg-light text-secondary border px-2 py-1"><?= htmlspecialchars($data['btn_text'] ?? '—') ?></span>
                                                <code class="ms-2 fs-0-75"><?= htmlspecialchars($data['btn_url'] ?? '—') ?></code>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-lg-4">
                                    <div class="card bg-light border-0 p-3 rounded h-100">
                                        <span class="d-block text-secondary fw-semibold fs-0-75 text-uppercase letter-spacing-pos-05 mb-2">Main Plan Image</span>
                                        <?php if (!empty($data['main_image_url'])): ?>
                                            <div class="rounded overflow-hidden shadow-sm border bg-white" style="width: 100%; height: 120px;">
                                                <img src="<?= htmlspecialchars(str_starts_with($data['main_image_url'], 'assets/') ? '../../' . $data['main_image_url'] : $data['main_image_url']) ?>" alt="Care plan preview"
                                                     style="width: 100%; height: 100%; object-fit: cover;" loading="lazy">
                                            </div>
                                        <?php else: ?>
                                            <div class="rounded border border-dashed bg-white d-flex flex-column align-items-center justify-content-center text-muted"
                                                 style="width: 100%; height: 120px;">
                                                <i data-lucide="image" class="text-secondary mb-1" style="width: 20px; height: 20px;"></i>
                                                <span style="font-size: 0.75rem;">No Image</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Subscription Perks List (4 items) -->
                        <div class="card-premium p-4">
                            <h3 class="h6 fw-bold mb-3 pb-3 border-bottom border-light-subtle text-dark">Subscription Perks</h3>
                            <div class="table-responsive">
                                <table class="table table-premium align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th style="width: 100px;">Icon / SVG</th>
                                            <th>Perk Label</th>
                                            <th class="text-end" style="width: 120px;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($perks as $p): ?>
                                            <tr>
                                                <td class="tech-data">
                                                    <div class="bg-light p-2 rounded text-primary d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                        <div style="width: 20px; height: 20px; color: currentColor;">
                                                            <?= $p['icon_svg'] ?>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="fw-semibold text-dark fs-0-82"><?= htmlspecialchars($p['label']) ?></span>
                                                </td>
                                                <td class="text-end">
                                                    <a href="<?= BASE_URL ?>home/edit.php?type=care_plan_perk&id=<?= $p['id'] ?>" class="btn btn-premium-secondary btn-sm px-3 d-inline-flex align-items-center gap-1">
                                                        <i data-lucide="pencil" class="icon-xs"></i>
                                                        <span>Edit</span>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                <?php endif; ?>


                <!-- ── TAB 4: DIAPER EXPERIENCE ── -->
                <?php if ($activeTab === 'experience'): ?>
                    <div class="d-flex flex-column gap-4">

                        <!-- Variant Experience Header Block -->
                        <?php $meta = $metadata['catnav_header']; $data = $sections['catnav_header'] ?? []; ?>
                        <div class="card-premium p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom border-light-subtle">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-premium-soft-primary tech-data px-2 py-1 fs-0-75">Header</span>
                                    <h3 class="h6 fw-bold mb-0 text-dark"><?= htmlspecialchars($meta['name']) ?></h3>
                                </div>
                                <a href="<?= BASE_URL ?>home/edit.php?section=catnav_header" class="btn btn-premium-primary btn-sm d-flex align-items-center gap-2 px-3">
                                    <i data-lucide="pencil" class="icon-xs"></i>
                                    <span>Edit Header</span>
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
                                        <span>Title</span>
                                    </div>
                                    <div class="col-8 col-sm-9 text-dark fs-0-8 font-medium">
                                        <?= htmlspecialchars($data['section_title'] ?? '—') ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 5 Tabbed Category Panels List -->
                        <div class="card-premium p-4">
                            <h3 class="h6 fw-bold mb-3 pb-3 border-bottom border-light-subtle text-dark">Diaper Experience Categories</h3>
                            <div class="table-responsive">
                                <table class="table table-premium align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Media</th>
                                            <th>Eyebrow &amp; Label</th>
                                            <th>Headline Title</th>
                                            <th>Description</th>
                                            <th>CTA Button</th>
                                            <th class="text-end" style="width: 120px;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($panels as $p): ?>
                                            <tr>
                                                <td class="tech-data">
                                                    <?php if (!empty($p['image_url'])): ?>
                                                        <img src="<?= htmlspecialchars(str_starts_with($p['image_url'], 'assets/') ? '../../' . $p['image_url'] : $p['image_url']) ?>"
                                                             style="width: 44px; height: 44px; object-fit: cover;" class="rounded border bg-light">
                                                    <?php else: ?>
                                                        <div class="d-flex align-items-center justify-content-center bg-light text-muted rounded border" style="width: 44px; height: 44px;">
                                                            <i data-lucide="image" style="width: 18px; height: 18px;"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="d-block fw-semibold text-dark fs-0-8"><?= htmlspecialchars($p['label']) ?></span>
                                                    <span class="d-block text-secondary fs-0-65"><?= htmlspecialchars($p['eyebrow']) ?></span>
                                                </td>
                                                <td>
                                                    <span class="fs-0-82 text-secondary fw-semibold"><?= htmlspecialchars($p['title']) ?></span>
                                                </td>
                                                <td>
                                                    <div class="fs-0-78 text-secondary max-width-280-px text-truncate" title="<?= htmlspecialchars($p['description']) ?>">
                                                        <?= htmlspecialchars($p['description']) ?>
                                                    </div>
                                                </td>
                                                <td class="tech-data">
                                                    <span class="badge bg-light text-secondary border font-medium fs-0-65">
                                                        <?= htmlspecialchars($p['btn_text']) ?>
                                                    </span>
                                                </td>
                                                <td class="text-end">
                                                    <a href="<?= BASE_URL ?>home/edit.php?type=catnav_panel&id=<?= $p['id'] ?>" class="btn btn-premium-secondary btn-sm px-3 d-inline-flex align-items-center gap-1">
                                                        <i data-lucide="pencil" class="icon-xs"></i>
                                                        <span>Edit</span>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                <?php endif; ?>


                <!-- ── TAB 5: PHILOSOPHY ── -->
                <?php if ($activeTab === 'philosophy'): ?>
                    <div class="d-flex flex-column gap-4">

                        <?php $meta = $metadata['philosophy']; $data = $sections['philosophy'] ?? []; ?>
                        <div class="card-premium p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom border-light-subtle">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-premium-soft-primary tech-data px-2 py-1 fs-0-75">Section</span>
                                    <h3 class="h6 fw-bold mb-0 text-dark"><?= htmlspecialchars($meta['name']) ?></h3>
                                </div>
                                <a href="<?= BASE_URL ?>home/edit.php?section=philosophy" class="btn btn-premium-primary btn-sm d-flex align-items-center gap-2 px-3">
                                    <i data-lucide="pencil" class="icon-xs"></i>
                                    <span>Edit Section</span>
                                </a>
                            </div>
                            <p class="text-secondary fs-0-78 mb-4"><?= htmlspecialchars($meta['description']) ?></p>

                            <div class="row g-4">
                                <!-- Left: text fields -->
                                <div class="col-12 col-lg-8">
                                    <div class="d-flex flex-column gap-3">

                                        <!-- Panel 1 -->
                                        <div class="p-3 rounded border border-light-subtle bg-light-subtle">
                                            <span class="d-block fw-semibold fs-0-78 text-secondary mb-2 text-uppercase letter-spacing-pos-05">Panel 1 — The Philosophy</span>
                                            <div class="row align-items-start py-1">
                                                <div class="col-4 col-sm-3 text-secondary fw-semibold fs-0-8">Eyebrow</div>
                                                <div class="col-8 col-sm-9 text-dark fs-0-8"><?= htmlspecialchars($data['panel1_eyebrow'] ?? '—') ?></div>
                                            </div>
                                            <div class="row align-items-start py-1">
                                                <div class="col-4 col-sm-3 text-secondary fw-semibold fs-0-8">Bold Phrase</div>
                                                <div class="col-8 col-sm-9 text-dark fs-0-8"><?= htmlspecialchars($data['panel1_bold'] ?? '—') ?></div>
                                            </div>
                                            <div class="row align-items-start py-1">
                                                <div class="col-4 col-sm-3 text-secondary fw-semibold fs-0-8">Body Text</div>
                                                <div class="col-8 col-sm-9 text-dark fs-0-8 text-truncate" style="max-width: 480px;" title="<?= htmlspecialchars(strip_tags($data['panel1_text'] ?? '')) ?>"><?= htmlspecialchars(strip_tags($data['panel1_text'] ?? '—')) ?></div>
                                            </div>
                                            <div class="row align-items-start py-1">
                                                <div class="col-4 col-sm-3 text-secondary fw-semibold fs-0-8">CTA</div>
                                                <div class="col-8 col-sm-9 text-dark fs-0-8">
                                                    <span class="badge bg-light text-secondary border px-2 py-1"><?= htmlspecialchars($data['panel1_btn_text'] ?? '—') ?></span>
                                                    <code class="ms-2 fs-0-75"><?= htmlspecialchars($data['panel1_btn_url'] ?? '—') ?></code>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Panel 2 -->
                                        <div class="p-3 rounded border border-light-subtle bg-light-subtle">
                                            <span class="d-block fw-semibold fs-0-78 text-secondary mb-2 text-uppercase letter-spacing-pos-05">Panel 2 — The Standards</span>
                                            <div class="row align-items-start py-1">
                                                <div class="col-4 col-sm-3 text-secondary fw-semibold fs-0-8">Eyebrow</div>
                                                <div class="col-8 col-sm-9 text-dark fs-0-8"><?= htmlspecialchars($data['panel2_eyebrow'] ?? '—') ?></div>
                                            </div>
                                            <div class="row align-items-start py-1">
                                                <div class="col-4 col-sm-3 text-secondary fw-semibold fs-0-8">Body Text</div>
                                                <div class="col-8 col-sm-9 text-dark fs-0-8 text-truncate" style="max-width: 480px;" title="<?= htmlspecialchars(strip_tags($data['panel2_text'] ?? '')) ?>"><?= htmlspecialchars(strip_tags($data['panel2_text'] ?? '—')) ?></div>
                                            </div>
                                            <div class="row align-items-start py-1">
                                                <div class="col-4 col-sm-3 text-secondary fw-semibold fs-0-8">CTA</div>
                                                <div class="col-8 col-sm-9 text-dark fs-0-8">
                                                    <span class="badge bg-light text-secondary border px-2 py-1"><?= htmlspecialchars($data['panel2_btn_text'] ?? '—') ?></span>
                                                    <code class="ms-2 fs-0-75"><?= htmlspecialchars($data['panel2_btn_url'] ?? '—') ?></code>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                                <!-- Right: background image preview -->
                                <div class="col-12 col-lg-4">
                                    <div class="card bg-light border-0 p-3 rounded h-100">
                                        <span class="d-block text-secondary fw-semibold fs-0-75 text-uppercase letter-spacing-pos-05 mb-2">Background Image</span>
                                        <?php $bgImg = $data['bg_image_url'] ?? ''; ?>
                                        <?php if (!empty($bgImg)): ?>
                                            <div class="rounded overflow-hidden shadow-sm border bg-white" style="width: 100%; height: 140px;">
                                                <img src="<?= htmlspecialchars(str_starts_with($bgImg, 'assets/') ? '../../' . $bgImg : $bgImg) ?>" alt="Philosophy background"
                                                     style="width: 100%; height: 100%; object-fit: cover;" loading="lazy">
                                            </div>
                                        <?php else: ?>
                                            <div class="rounded border border-dashed bg-white d-flex flex-column align-items-center justify-content-center text-muted"
                                                 style="width: 100%; height: 140px;">
                                                <i data-lucide="image" class="text-secondary mb-1" style="width: 20px; height: 20px;"></i>
                                                <span style="font-size: 0.75rem;">No Image Set</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                <?php endif; ?>

            </div>

        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
