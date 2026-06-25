<?php
// Suppress PHP notices/warnings from affecting front-end HTML output
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

require_once __DIR__ . '/admin/config/database.php';
require_once __DIR__ . '/admin/config/reviews-helper.php';
require_once __DIR__ . '/admin/config/home-helper.php';
require_once __DIR__ . '/admin/config/products-helper.php';
require_once __DIR__ . '/admin/config/blogs-helper.php';

try {
    $db   = getDBConnection();
    $stmt = $db->prepare("SELECT * FROM reviews WHERE status = 'active' ORDER BY created_at DESC");
    $stmt->execute();
    $activeReviews = $stmt->fetchAll();
} catch (Exception $e) {
    error_log("Failed to fetch reviews: " . $e->getMessage());
    $activeReviews = [];
}

try {
    $resProducts    = getProducts(['status' => 'active', 'per_page' => 12]);
    $activeProducts = $resProducts['data'] ?? [];
} catch (Exception $e) {
    error_log("Failed to fetch products: " . $e->getMessage());
    $activeProducts = [];
}

try {
    $resBlogs    = getBlogs(['status' => 'active', 'per_page' => 10]);
    $activeBlogs = $resBlogs['data'] ?? [];
} catch (Exception $e) {
    error_log("Failed to fetch blogs: " . $e->getMessage());
    $activeBlogs = [];
}

$homeSections    = getHomeSections();
$atelierVariants = getHomeAtelierVariants();
$carePlanPerks   = getHomeCarePlanPerks();
$catnavPanels    = getHomeCatnavPanels();

// ─── Fallback content ────────────────────────────────────────────────────────
// Used only when admin has not yet saved a value for that field.
$fallbackHome = [
    'hero'           => [
        'section_title' => 'Softness That Breathes.',
        'left_text'     => 'Soft. Dry. Clean.<br>Pure Comfort.',
        'right_text'    => 'Pediatrician-approved TCF.<br>Certified safe for newborn skin.',
        'btn_text'      => '',
        'btn_url'       => '',
        'image_url_1'   => '',
        'image_url_2'   => '',
        'image_url_3'   => '',
    ],
    'showcase'       => [
        'section_title' => 'The Softest<br>Diaper Ever.',
        'badge_label'   => 'Like Soft Clouds',
        'desc_1'        => '',
        'desc_2'        => '',
        'btn_text'      => '',
        'btn_url'       => '',
        'video_url'     => '',
    ],
    'atelier_header' => [
        'section_subtitle' => '02 / SIZING & SENSATION',
        'section_title'    => 'Tailored to their stage, <br><em>crafted for their skin.</em>',
        'content'          => '',
    ],
    'care_plan'      => [
        'section_title'  => 'CloudCush Care Plan<br>for Diapers',
        'content'        => '',
        'btn_text'       => '',
        'btn_url'        => '',
        'main_image_url' => '',
        'panel_image_1'  => '',
        'panel_image_2'  => '',
        'panel_image_3'  => '',
        'panel_image_4'  => '',
    ],
    'catnav_header'  => [
        'section_subtitle' => 'Variant Showcase',
        'section_title'    => 'The Diaper Experience',
    ],
    'philosophy'     => [
        'bg_image_url'    => '',
        'panel1_eyebrow'  => 'THE PHILOSOPHY',
        'panel1_bold'     => 'Comfort, Made Simple.',
        'panel1_text'     => 'Parenthood is made of little moments. At CloudCush, we believe comfort should never be complicated. That&#x2019;s why every diaper is thoughtfully designed for softness, protection, and everyday peace of mind. Crafted in Kota, Rajasthan, we bring premium, skin-friendly protection to modern Indian families.',
        'panel1_btn_text' => 'View Philosophy',
        'panel1_btn_url'  => 'about.php',
        'panel2_eyebrow'  => 'THE STANDARDS',
        'panel2_text'     => 'We prioritize absolute diaper integrity. Every CloudCush diaper is crafted using certified non-toxic, hypoallergenic, and cotton-soft materials. Our commitment ensures 12-hour dryness and rash-free protection for your baby&#x2019;s delicate skin.',
        'panel2_btn_text' => 'View Standards',
        'panel2_btn_url'  => 'about.php',
    ],
];

/**
 * Merge a single homepage section's DB data with its fallback values.
 * Fallbacks are only applied when the DB value is null / empty string.
 */
function getHomeSectionData(string $key, array $sections, array $fallbacks): array
{
    $sec      = $sections[$key] ?? [];
    $fallback = $fallbacks[$key] ?? [];

    // Collect all expected keys from both sources
    $allKeys = array_unique(array_merge(array_keys($fallback), array_keys($sec)));
    $res     = [];
    foreach ($allKeys as $k) {
        $dbVal = isset($sec[$k]) ? $sec[$k] : null;
        if ($dbVal !== null && $dbVal !== '') {
            $res[$k] = $dbVal;
        } else {
            $res[$k] = $fallback[$k] ?? '';
        }
    }
    return $res;
}

/**
 * Resolve any asset URL to a usable src value.
 *
 * Handles three cases:
 *   1. Absolute URLs (http:// / https://) → returned as-is
 *   2. Root-relative paths (/assets/...)  → returned as-is
 *   3. Relative paths (assets/...)        → returned as-is (browser resolves from page root)
 *   4. Empty string                       → returned as empty (caller should guard with !empty)
 */
if (!function_exists('resolveAssetUrl')) {
    function resolveAssetUrl($url)
    {
        if (is_array($url)) {
            return array_map('resolveAssetUrl', $url);
        }
        $url = trim((string)$url);
        if ($url === '') {
            return '';
        }
        $posAdmin = strpos($url, 'admin/assets/uploads/');
        if ($posAdmin !== false) {
            return substr($url, $posAdmin);
        }
        $posUploads = strpos($url, 'assets/uploads/');
        if ($posUploads !== false) {
            return 'admin/' . substr($url, $posUploads);
        }
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://') || str_starts_with($url, '//')) {
            return $url;
        }
        return $url;
    }
}
?>
<?php include 'includes/head.php'; ?>

<?php
include 'includes/header.php';
$heroData = getHomeSectionData('hero', $homeSections, $fallbackHome);
?>

<!-- Inline data for JS: atelier variants from admin -->
<script>
    window.homepageAtelierData = <?php echo json_encode($atelierVariants, JSON_HEX_TAG | JSON_HEX_AMP) ?>;
</script>

<main>
    <section class="hero">

        <!-- Oversized Editorial Title -->
        <div class="hero-title-container">
            <h1 class="hero-title"><?php echo htmlspecialchars($heroData['section_title'] ?? '') ?></h1>
        </div>

        <!-- 3-Column Content Block -->
        <div class="container hero-body">

            <!-- Vertical Grid Line Dividers (Moved inside hero-body to prevent crossing the title) -->
            <div class="hero-grid-lines">
                <div class="grid-col-line"></div>
                <div class="grid-col-line"></div>
                <div class="grid-col-line"></div>
            </div>

            <!-- Left Column Content -->
            <div class="hero-col hero-col-left">
                <div class="hero-left-text">
                    <?php echo $heroData['left_text'] ?? '' ?>
                </div>
            </div>

            <!-- Center Column Content: Baby Image layers -->
            <div class="hero-col hero-col-center">
                <div class="baby-image-wrapper">
                    <!-- Foreground main baby layers -->
                    <?php for ($i = 1; $i <= 3; $i++): ?>
                        <?php $imgSrc = resolveAssetUrl($heroData['image_url_' . $i] ?? ''); ?>
                        <?php if (! empty($imgSrc)): ?>
                            <img class="baby-main baby-main-layer<?php echo ($i === 1) ? ' active' : '' ?>" data-index="<?php echo $i ?>" src="<?php echo htmlspecialchars($imgSrc) ?>" alt="CloudCush Sitting Baby" loading="<?php echo ($i === 1) ? 'eager' : 'lazy' ?>">
                        <?php else: ?>
                            <img class="baby-main baby-main-layer<?php echo ($i === 1) ? ' active' : '' ?>" data-index="<?php echo $i ?>" src="" alt="" aria-hidden="true" style="display:none;">
                        <?php endif; ?>
                    <?php endfor; ?>

                    <!-- Background double exposure ghost baby layers -->
                    <?php for ($i = 1; $i <= 3; $i++): ?>
                        <?php $imgSrc = resolveAssetUrl($heroData['image_url_' . $i] ?? ''); ?>
                        <?php if (! empty($imgSrc)): ?>
                            <img class="baby-ghost baby-ghost-layer<?php echo ($i === 1) ? ' active' : '' ?>" data-index="<?php echo $i ?>" src="<?php echo htmlspecialchars($imgSrc) ?>" alt="" aria-hidden="true" loading="<?php echo ($i === 1) ? 'eager' : 'lazy' ?>">
                        <?php else: ?>
                            <img class="baby-ghost baby-ghost-layer<?php echo ($i === 1) ? ' active' : '' ?>" data-index="<?php echo $i ?>" src="" alt="" aria-hidden="true" style="display:none;">
                        <?php endif; ?>
                    <?php endfor; ?>
                </div>
            </div>

            <!-- Right Column Content: Info details & CTA -->
            <div class="hero-col hero-col-right">
                <div class="hero-right-text">
                    <?php echo $heroData['right_text'] ?? '' ?>
                </div>
                <?php if (! empty($heroData['btn_text'])): ?>
                    <a href="<?php echo htmlspecialchars($heroData['btn_url'] ?? '') ?>" class="btn-pill"><?php echo htmlspecialchars($heroData['btn_text']) ?></a>
                <?php endif; ?>
            </div>

        </div>
    </section>

    <!-- Showcase Section: Pinned Overlap and 360 Rotating Diaper Showcase -->
    <?php $showcaseData = getHomeSectionData('showcase', $homeSections, $fallbackHome); ?>
    <section class="showcase-section">
        <div class="container showcase-grid">

            <!-- Left Column: Editorial Content -->
            <div class="showcase-col showcase-col-left">
                <!-- section_title may contain HTML (e.g. <br>) — output raw, data is admin-controlled -->
                <h2 class="showcase-title"><?php echo $showcaseData['section_title'] ?? '' ?></h2>

                <?php if (! empty($showcaseData['badge_label'])): ?>
                    <div class="feature-badge">
                        <div class="feature-icon-wrapper">
                            <svg class="cloud-icon" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="50" cy="50" r="44" stroke="currentColor" stroke-width="2.5" fill="none" />
                                <path d="M34,56 A 7,7 0 0,1 34,42 A 10,10 0 0,1 52,34 A 8,8 0 0,1 66,44 A 6,6 0 0,1 64,56 Z"
                                    stroke="currentColor" stroke-width="2.5" stroke-linejoin="round" stroke-linecap="round" fill="none" />
                            </svg>
                        </div>
                        <span class="feature-label"><?php echo htmlspecialchars($showcaseData['badge_label']) ?></span>
                    </div>
                <?php endif; ?>

                <div class="showcase-desc-wrapper">
                    <?php if (! empty($showcaseData['desc_1'])): ?>
                        <div class="showcase-desc"><?php echo $showcaseData['desc_1'] ?></div>
                    <?php endif; ?>
                    <?php if (! empty($showcaseData['desc_2'])): ?>
                        <div class="showcase-desc"><?php echo $showcaseData['desc_2'] ?></div>
                    <?php endif; ?>
                </div>

                <?php if (! empty($showcaseData['btn_text'])): ?>
                    <a href="<?php echo htmlspecialchars($showcaseData['btn_url'] ?? '') ?>" class="btn-pill"><?php echo htmlspecialchars($showcaseData['btn_text']) ?></a>
                <?php endif; ?>
            </div>

            <!-- Right Column: 360 Rotating Diaper Video Showcase -->
            <div class="showcase-col showcase-col-right">
                <div class="diaper-container">
                    <?php $videoSrc = resolveAssetUrl($showcaseData['video_url'] ?? ''); ?>
                    <?php if (! empty($videoSrc)): ?>
                        <video class="diaper-video" src="<?php echo htmlspecialchars($videoSrc) ?>" autoplay loop muted playsinline>
                            Your browser does not support the video tag.
                        </video>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </section>

    <!-- Sizing Atelier Section (Premium Sizing & Sensation Atelier) -->
    <?php $atelierHeader = getHomeSectionData('atelier_header', $homeSections, $fallbackHome); ?>
    <section class="atelier-section" id="diaperSelectorSection">

        <!-- Floating Baby Doodles -->
        <div class="doodle-container" aria-hidden="true">
            <!-- Hand-drawn Cloud -->
            <svg class="doodle doodle-cloud-1" viewBox="0 0 100 60" xmlns="http://www.w3.org/2000/svg">
                <path d="M20,40 Q10,35 15,25 Q20,15 35,20 Q45,10 60,15 Q75,10 80,25 Q90,35 80,45 Q70,55 50,50 Q30,55 20,40" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" />
            </svg>
            <!-- Hand-drawn Star -->
            <svg class="doodle doodle-star-1" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg">
                <path d="M20,5 L24,15 L35,16 L27,23 L29,34 L20,28 L11,34 L13,23 L5,16 L16,15 Z" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linejoin="round" />
            </svg>
            <!-- Hand-drawn Heart -->
            <svg class="doodle doodle-heart-1" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg">
                <path d="M20,12 C18,7 10,7 10,13 C10,21 20,28 20,28 C20,28 30,21 30,13 C30,7 22,7 20,12 Z" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" />
            </svg>
            <!-- Hand-drawn Smile -->
            <svg class="doodle doodle-smile-1" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg">
                <circle cx="20" cy="20" r="15" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" />
                <circle cx="15" cy="17" r="1.5" fill="currentColor" />
                <circle cx="25" cy="17" r="1.5" fill="currentColor" />
                <path d="M13,24 Q20,31 27,24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" />
            </svg>
            <!-- Hand-drawn Spark -->
            <svg class="doodle doodle-spark-1" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg">
                <path d="M20,10 L20,30 M10,20 L30,20 M13,13 L27,27 M13,27 L27,13" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" />
            </svg>
        </div>

        <div class="atelier-container">

            <!-- Asymmetric Header Block -->
            <div class="atelier-header">
                <span class="atelier-meta"><?php echo htmlspecialchars($atelierHeader['section_subtitle'] ?? '') ?></span>
                <!-- section_title may contain HTML tags (<br>, <em>) — output raw, admin-controlled -->
                <h2 class="atelier-title"><?php echo $atelierHeader['section_title'] ?? '' ?></h2>
                <?php if (! empty($atelierHeader['content'])): ?>
                    <div class="atelier-desc">
                        <?php echo $atelierHeader['content'] ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="atelier-canvas">

                <!-- Left: Floating Product Showcase with Glowing Backdrop -->
                <div class="atelier-showcase-col">
                    <div class="atelier-glow-backdrop"></div>
                    <div class="atelier-diaper-viewport">

                        <?php
                        $aIdx = 1;
                        foreach ($atelierVariants as $v):
                            $activeClass = ($aIdx === 1) ? 'active' : '';
                            if (($v['key'] ?? '') === 'newborn') {
                                $tagClassTop    = 'tag-top-left';
                                $tagClassBottom = 'tag-bottom-right';
                            } elseif (($v['key'] ?? '') === 'activefit') {
                                $tagClassTop    = 'tag-top-right';
                                $tagClassBottom = 'tag-bottom-left';
                            } else {
                                $tagClassTop    = 'tag-center-left';
                                $tagClassBottom = 'tag-bottom-right';
                            }
                        ?>
                            <!-- <?php echo htmlspecialchars($v['label'] ?? '') ?> Variant Visuals -->
                            <div class="atelier-variant <?php echo $activeClass ?>" data-variant="<?php echo htmlspecialchars($v['key'] ?? '') ?>">
                                <img src="<?php echo htmlspecialchars(resolveAssetUrl($v['image_url'] ?? '')) ?>" alt="<?php echo htmlspecialchars($v['variant_name'] ?? '') ?>" class="atelier-img">

                                <!-- Floating Editorial Callouts -->
                                <div class="atelier-tag <?php echo $tagClassTop ?>">
                                    <span class="tag-dot"></span>
                                    <div class="tag-content">
                                        <span class="tag-title"><?php echo htmlspecialchars($v['tag_top_title'] ?? '') ?></span>
                                        <span class="tag-desc"><?php echo htmlspecialchars($v['tag_top_desc'] ?? '') ?></span>
                                    </div>
                                </div>
                                <?php if (! empty($v['tag_bottom_title'])): ?>
                                    <div class="atelier-tag <?php echo $tagClassBottom ?>">
                                        <span class="tag-dot"></span>
                                        <div class="tag-content">
                                            <span class="tag-title"><?php echo htmlspecialchars($v['tag_bottom_title']) ?></span>
                                            <span class="tag-desc"><?php echo htmlspecialchars($v['tag_bottom_desc'] ?? '') ?></span>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php
                            $aIdx++;
                        endforeach;
                        ?>

                    </div>
                </div>

                <!-- Right: Sizing Engine Column -->
                <div class="atelier-engine-col">

                    <!-- Watermark background name -->
                    <div class="atelier-watermark-wrap">
                        <span class="atelier-watermark" id="recWatermark">TINYHUG</span>
                    </div>

                    <!-- Stage Selector Tabs (Awwwards Style) -->
                    <div class="atelier-stages">
                        <?php
                        $aIdx = 1;
                        foreach ($atelierVariants as $v):
                            $activeClass = ($aIdx === 1) ? 'active' : '';
                        ?>
                            <button class="atelier-stage-btn <?php echo $activeClass ?>" data-tab="<?php echo htmlspecialchars($v['key'] ?? '') ?>">
                                <span class="btn-num">0<?php echo $aIdx ?></span>
                                <span class="btn-text"><?php echo htmlspecialchars($v['label'] ?? '') ?></span>
                            </button>
                        <?php
                            $aIdx++;
                        endforeach;
                        ?>
                    </div>

                    <!-- Weight Thread (Minimalist line selector) -->
                    <div class="weight-thread-wrapper">
                        <span class="weight-thread-title">Select Baby's Weight</span>
                        <div class="weight-thread-line" id="weightThreadLine">
                            <div class="weight-thread-progress" id="weightThreadProgress"></div>
                            <div class="weight-thread-ring" id="weightThreadRing"></div>
                            <div class="weight-thread-points">
                                <span class="weight-point" data-weight="xs" data-percent="0">
                                    <span class="point-label">&lt;3 kg</span>
                                </span>
                                <span class="weight-point active" data-weight="s" data-percent="25">
                                    <span class="point-label">3-5 kg</span>
                                </span>
                                <span class="weight-point" data-weight="m" data-percent="50">
                                    <span class="point-label">5-8 kg</span>
                                </span>
                                <span class="weight-point" data-weight="l" data-percent="75">
                                    <span class="point-label">8-11 kg</span>
                                </span>
                                <span class="weight-point" data-weight="xl" data-percent="100">
                                    <span class="point-label">11+ kg</span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Recommended Choice Panel -->
                    <div class="atelier-rec-display">
                        <div class="atelier-rec-info">
                            <span class="atelier-rec-eyebrow">Atelier Recommendation</span>
                            <h3 class="atelier-rec-name" id="recDiaperName">CloudCush TinyHug</h3>
                            <span class="atelier-rec-size" id="recDiaperSize">Size S (3-5 kg)</span>
                        </div>
                        <a href="product-details.php" class="atelier-cta" id="recDiaperCta">
                            <span class="cta-label">Explore Variant</span>
                            <svg class="cta-arrow" viewBox="0 0 24 24">
                                <path d="M5 12h14M12 5l7 7-7 7" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </a>
                    </div>

                    <!-- Comfort Sensation Metrics -->
                    <div class="atelier-metrics">
                        <span class="atelier-metrics-title">Sensation Profile</span>

                        <div class="atelier-metric-row">
                            <div class="atelier-metric-meta">
                                <span class="atelier-metric-label">Absorbency Capacity</span>
                                <span class="atelier-metric-value" id="valAbsorbency">80%</span>
                            </div>
                            <div class="atelier-metric-track">
                                <div class="atelier-metric-bar" id="barAbsorbency" style="width: 80%;"></div>
                            </div>
                        </div>

                        <div class="atelier-metric-row">
                            <div class="atelier-metric-meta">
                                <span class="atelier-metric-label">Elasticity &amp; Stretch</span>
                                <span class="atelier-metric-value" id="valStretch">60%</span>
                            </div>
                            <div class="atelier-metric-track">
                                <div class="atelier-metric-bar" id="barStretch" style="width: 60%;"></div>
                            </div>
                        </div>

                        <div class="atelier-metric-row">
                            <div class="atelier-metric-meta">
                                <span class="atelier-metric-label">Topsheet Softness</span>
                                <span class="atelier-metric-value" id="valSoftness">100%</span>
                            </div>
                            <div class="atelier-metric-track">
                                <div class="atelier-metric-bar" id="barSoftness" style="width: 100%;"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Reassurance Badges -->
                    <div class="atelier-reassurance">
                        <div class="reassurance-item">
                            <i class="ri-heart-line reassurance-icon"></i>
                            <span>Pediatrician Approved</span>
                        </div>
                        <div class="reassurance-item">
                            <i class="ri-leaf-line reassurance-icon"></i>
                            <span>Totally Chlorine Free</span>
                        </div>
                        <div class="reassurance-item">
                            <i class="ri-shield-check-line reassurance-icon"></i>
                            <span>Hypoallergenic Certified</span>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </section>

    <!-- ==========================================================================
   Editorial Stacked Scroll Section — BabyCare+ Plan
   ========================================================================== -->
    <?php $carePlanData = getHomeSectionData('care_plan', $homeSections, $fallbackHome); ?>
    <section class="stack-section" id="stackSection">

        <div class="stack-stage" id="stackStage">

            <!-- Cards 1–4: rise from bottom, then cluster shifts right -->
            <div class="stack-panels" id="stackPanels">

                <div class="stack-panel sp-1" id="stackPanel1">
                    <div class="stack-panel-img-wrap">
                        <?php $img = resolveAssetUrl($carePlanData['panel_image_1'] ?? ''); ?>
                        <?php if (! empty($img)): ?>
                            <img class="stack-panel-img" src="<?php echo htmlspecialchars($img) ?>" alt="CloudCush baby comfort" loading="eager">
                        <?php endif; ?>
                    </div>
                </div>

                <div class="stack-panel sp-2" id="stackPanel2">
                    <div class="stack-panel-img-wrap">
                        <?php $img = resolveAssetUrl($carePlanData['panel_image_2'] ?? ''); ?>
                        <?php if (! empty($img)): ?>
                            <img class="stack-panel-img" src="<?php echo htmlspecialchars($img) ?>" alt="CloudCush baby lying down" loading="eager">
                        <?php endif; ?>
                    </div>
                </div>

                <div class="stack-panel sp-3" id="stackPanel3">
                    <div class="stack-panel-img-wrap">
                        <?php $img = resolveAssetUrl($carePlanData['panel_image_3'] ?? ''); ?>
                        <?php if (! empty($img)): ?>
                            <img class="stack-panel-img" src="<?php echo htmlspecialchars($img) ?>" alt="CloudCush baby crawling" loading="eager">
                        <?php endif; ?>
                    </div>
                </div>

                <div class="stack-panel sp-4" id="stackPanel4">
                    <div class="stack-panel-img-wrap">
                        <?php $img = resolveAssetUrl($carePlanData['panel_image_4'] ?? ''); ?>
                        <?php if (! empty($img)): ?>
                            <img class="stack-panel-img" src="<?php echo htmlspecialchars($img) ?>" alt="CloudCush toddler standing" loading="eager">
                        <?php endif; ?>
                    </div>
                </div>

            </div><!-- /.stack-panels -->

            <!-- Panel 5: SIBLING of .stack-panels, NOT inside it. -->
            <div class="stack-panel sp-5" id="stackPanel5">
                <div class="stack-panel-img-wrap">
                    <?php $img = resolveAssetUrl($carePlanData['main_image_url'] ?? ''); ?>
                    <?php if (! empty($img)): ?>
                        <img class="stack-panel-img" src="<?php echo htmlspecialchars($img) ?>" alt="CloudCush Care Plan" loading="eager">
                    <?php endif; ?>
                </div>
            </div>

            <!-- LEFT EDITORIAL CONTENT — SIBLING of .stack-panels -->
            <div class="stack-left-content" id="stackLeftContent">
                <div class="stack-left-inner">

                    <!-- section_title may contain HTML (<br>) — output raw -->
                    <h2 class="stack-main-title">
                        <?php echo $carePlanData['section_title'] ?? '' ?>
                    </h2>

                    <?php if (! empty($carePlanData['content'])): ?>
                        <div class="stack-main-desc">
                            <?php echo $carePlanData['content'] ?>
                        </div>
                    <?php endif; ?>

                    <div class="stack-divider"></div>

                    <?php if (! empty($carePlanPerks)): ?>
                        <div class="stack-perks-grid">
                            <?php
                            $pIdx = 1;
                            foreach ($carePlanPerks as $perk):
                            ?>
                                <div class="stack-perk" id="stackPerk<?php echo $pIdx ?>">
                                    <div class="stack-perk-icon" aria-hidden="true">
                                        <?php echo $perk['icon_svg'] ?? '' ?>
                                    </div>
                                    <span class="stack-perk-label"><?php echo htmlspecialchars($perk['label'] ?? '') ?></span>
                                </div>
                            <?php
                                $pIdx++;
                            endforeach;
                            ?>
                        </div><!-- /.stack-perks-grid -->
                    <?php endif; ?>

                    <?php if (! empty($carePlanData['btn_text'])): ?>
                        <a href="<?php echo htmlspecialchars($carePlanData['btn_url'] ?? '') ?>" class="btn-pill stack-plan-cta" id="stackCta"><?php echo htmlspecialchars($carePlanData['btn_text']) ?></a>
                    <?php endif; ?>

                </div><!-- /.stack-left-inner -->
            </div><!-- /.stack-left-content -->

        </div><!-- /.stack-stage -->

    </section>


    <!-- ==========================================================================
         CATEGORY NAV SECTION — Pinned scroll with 5 tabs
         ========================================================================== -->
    <section class="catnav-section" id="catnavSection">
        <?php $catnavHeader = getHomeSectionData('catnav_header', $homeSections, $fallbackHome); ?>
        <div class="catnav-stage" id="catnavStage">

            <!-- Left: Editorial nav list with progress tracks -->
            <div class="catnav-left" id="catnavLeft">
                <div class="catnav-nav-header">
                    <span class="catnav-section-eyebrow"><?php echo htmlspecialchars($catnavHeader['section_subtitle'] ?? '') ?></span>
                    <h2 class="catnav-section-title"><?php echo htmlspecialchars($catnavHeader['section_title'] ?? '') ?></h2>
                </div>
                <nav class="catnav-list" aria-label="Product categories">
                    <?php
                    $cIdx = 0;
                    foreach ($catnavPanels as $p):
                        $activeClass = ($cIdx === 0) ? 'active' : '';
                    ?>
                        <button class="catnav-item <?php echo $activeClass ?>" data-index="<?php echo $cIdx ?>" aria-label="<?php echo htmlspecialchars($p['label'] ?? '') ?>">
                            <span class="catnav-item-num">0<?php echo ($cIdx + 1) ?></span>
                            <span class="catnav-item-label"><?php echo htmlspecialchars($p['label'] ?? '') ?></span>
                            <div class="catnav-item-progress">
                                <div class="catnav-item-progress-bar"></div>
                            </div>
                        </button>
                    <?php
                        $cIdx++;
                    endforeach;
                    ?>
                </nav>
            </div>

            <!-- Right: Immersive media viewport and details -->
            <div class="catnav-right" id="catnavRight">

                <?php
                $cIdx = 0;
                foreach ($catnavPanels as $p):
                    $activeClass = ($cIdx === 0) ? 'active' : '';
                ?>
                    <div class="catnav-panel <?php echo $activeClass ?>" id="catnavPanel<?php echo $cIdx ?>" data-index="<?php echo $cIdx ?>">
                        <div class="catnav-panel-media">
                            <?php $panelImg = resolveAssetUrl($p['image_url'] ?? ''); ?>
                            <?php if (! empty($panelImg)): ?>
                                <img src="<?php echo htmlspecialchars($panelImg) ?>" alt="<?php echo htmlspecialchars($p['title'] ?? '') ?>" class="catnav-img" loading="<?php echo ($cIdx === 0) ? 'eager' : 'lazy' ?>">
                            <?php endif; ?>
                            <div class="catnav-media-overlay"></div>
                        </div>
                        <div class="catnav-panel-content">
                            <span class="catnav-panel-eyebrow"><?php echo htmlspecialchars($p['eyebrow'] ?? '') ?></span>
                            <h3 class="catnav-panel-title"><?php echo htmlspecialchars($p['title'] ?? '') ?></h3>
                            <div class="catnav-panel-desc"><?php echo $p['description'] ?? '' ?></div>
                            <?php if (! empty($p['btn_text'])): ?>
                                <a href="<?php echo htmlspecialchars($p['btn_url'] ?? '') ?>" class="catnav-panel-cta"><?php echo htmlspecialchars($p['btn_text']) ?></a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php
                    $cIdx++;
                endforeach;
                ?>

            </div>

        </div>
    </section>

    <!-- =========================================================
         CORE COLLECTION — Premium Carousel
         ========================================================= -->
    <section class="core-collection-section" id="coreCollectionSection">
        <div class="container">

            <!-- Section Header Row: title + prev/next buttons -->
            <div class="cc-header">
                <h2 class="core-collection-title">The Core Collection</h2>
                <div class="cc-nav" aria-label="Carousel navigation">
                    <button class="cc-nav-btn cc-prev" id="ccPrev" aria-label="Previous product">
                        <i class="ri-arrow-left-line"></i>
                    </button>
                    <button class="cc-nav-btn cc-next" id="ccNext" aria-label="Next product">
                        <i class="ri-arrow-right-line"></i>
                    </button>
                </div>
            </div>

            <!-- Carousel Viewport -->
            <div class="cc-viewport" id="ccViewport">
                <div class="cc-track" id="ccTrack">

                    <?php
                    $ccIdx = 0;
                    foreach ($activeProducts as $p):
                        $price  = $p['sale_price'] ?: $p['price'];
                        $imgSrc = resolveAssetUrl($p['image_url'] ?? '');

                        // Rating logic matching products.php
                        $ratingCount = (intval($p['id']) * 37 + 107) % 400 + 40;
                        $ratingStars = '★★★★★';
                    ?>
                        <!-- Card <?php echo $ccIdx + 1 ?>: <?php echo htmlspecialchars($p['title']) ?> -->
                        <div class="collection-card" data-cc-index="<?php echo $ccIdx ?>">
                            <a href="product-details.php?slug=<?php echo urlencode($p['slug']) ?>" class="collection-card-link-wrapper">
                                <div class="collection-image-wrap">
                                    <?php if (! empty($imgSrc)): ?>
                                        <img src="<?php echo htmlspecialchars($imgSrc) ?>"
                                            alt="<?php echo htmlspecialchars($p['title']) ?>" class="collection-image" loading="lazy">
                                    <?php endif; ?>
                                </div>
                                <div class="collection-info">

                                    <div class="collection-rating">
                                        <div class="stars"><?php echo $ratingStars ?></div>
                                        <span class="review-count">(<?php echo $ratingCount ?>)</span>
                                    </div>
                                    <h3 class="collection-product-title"><?php echo htmlspecialchars($p['title']) ?></h3>
                                    <p class="collection-desc"><?php echo htmlspecialchars($p['short_description'] ?? '') ?></p>
                                    <p class="collection-price">₹<?php echo number_format($price, 0) ?></p>
                                </div>
                            </a>
                            <div class="collection-cta-wrap">
                                <a href="product-details.php?slug=<?php echo urlencode($p['slug']) ?>" class="collection-cta-btn">View Product</a>
                            </div>
                        </div>
                    <?php
                        $ccIdx++;
                    endforeach;
                    ?>

                </div><!-- /.cc-track -->
            </div><!-- /.cc-viewport -->

            <!-- Dot indicators -->
            <div class="cc-dots" id="ccDots" aria-label="Carousel position indicators">
                <?php for ($i = 0; $i < count($activeProducts); $i++): ?>
                    <button class="cc-dot <?php echo $i === 0 ? 'active' : '' ?>" data-cc-dot="<?php echo $i ?>" aria-label="Go to slide <?php echo $i + 1 ?>"></button>
                <?php endfor; ?>
            </div>

        </div>
    </section>


    <!-- =========================================================
         PHILOSOPHY SECTION — Full-width cinematic storytelling
         Connected to admin: Home → Hero & Showcase → Philosophy
         ========================================================= -->
    <?php $philosophyData = getHomeSectionData('philosophy', $homeSections, $fallbackHome); ?>
    <section class="philosophy-section" id="philosophySection" aria-label="Our Philosophy">

        <!-- Parallax background image layer -->
        <div class="philosophy-bg" id="philosophyBg" aria-hidden="true">
            <?php $philBg = resolveAssetUrl($philosophyData['bg_image_url'] ?? ''); ?>
            <?php if (! empty($philBg)): ?>
                <img
                    src="<?php echo htmlspecialchars($philBg) ?>"
                    alt=""
                    class="philosophy-bg-img"
                    id="philosophyBgImg"
                    loading="lazy"
                    decoding="async">
            <?php endif; ?>
        </div>

        <!-- Dark gradient overlay -->
        <div class="philosophy-overlay" id="philosophyOverlay" aria-hidden="true"></div>

        <!-- Content panel -->
        <div class="philosophy-content" id="philosophyContent">

            <!-- Panel 1: The Philosophy -->
            <div class="philosophy-panel" id="philPanel1">
                <div class="philosophy-eyebrow-wrap" id="philEyebrow">
                    <?php if (! empty($philosophyData['panel1_eyebrow'])): ?>
                        <span class="philosophy-eyebrow"><?php echo htmlspecialchars($philosophyData['panel1_eyebrow']) ?></span>
                    <?php endif; ?>
                    <div class="philosophy-divider" aria-hidden="true"></div>
                </div>

                <div class="philosophy-body" id="philBody">
                    <?php if (! empty($philosophyData['panel1_bold'])): ?>
                        <p class="philosophy-bold"><?php echo htmlspecialchars($philosophyData['panel1_bold']) ?></p>
                    <?php endif; ?>
                    <div class="philosophy-text philosophy-prose">
                        <?php echo $philosophyData['panel1_text'] ?? '' ?>
                    </div>
                </div>

                <?php if (! empty($philosophyData['panel1_btn_text'])): ?>
                    <div class="philosophy-cta-wrap" id="philCta">
                        <a href="<?php echo htmlspecialchars($philosophyData['panel1_btn_url'] ?? '') ?>" class="philosophy-cta" aria-label="<?php echo htmlspecialchars($philosophyData['panel1_btn_text']) ?>">
                            <?php echo htmlspecialchars($philosophyData['panel1_btn_text']) ?>
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Panel 2: The Standards -->
            <div class="philosophy-panel" id="philPanel2">
                <div class="philosophy-eyebrow-wrap">
                    <?php if (! empty($philosophyData['panel2_eyebrow'])): ?>
                        <span class="philosophy-eyebrow"><?php echo htmlspecialchars($philosophyData['panel2_eyebrow']) ?></span>
                    <?php endif; ?>
                    <div class="philosophy-divider" aria-hidden="true"></div>
                </div>

                <div class="philosophy-body">
                    <div class="philosophy-text philosophy-prose">
                        <?php echo $philosophyData['panel2_text'] ?? '' ?>
                    </div>
                </div>

                <?php if (! empty($philosophyData['panel2_btn_text'])): ?>
                    <div class="philosophy-cta-wrap">
                        <a href="<?php echo htmlspecialchars($philosophyData['panel2_btn_url'] ?? '') ?>" class="philosophy-cta" aria-label="<?php echo htmlspecialchars($philosophyData['panel2_btn_text']) ?>">
                            <?php echo htmlspecialchars($philosophyData['panel2_btn_text']) ?>
                        </a>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </section>

    <!-- ==========================================================================
         MOM-APPROVED MOMENTS — Immersive Testimonial Carousel
         ========================================================================== -->
    <section class="mom-moments-section" id="momMomentsSection" aria-label="Mom-Approved Moments">
        <div class="mom-container">

            <!-- Section Header -->
            <div class="mom-header">
                <span class="mom-eyebrow">COMMUNITY TESTIMONIALS</span>
                <h2 class="mom-title">Mom-Approved Moments</h2>
                <p class="mom-subheading">Real stories from our community</p>
            </div>

            <!-- Carousel Viewport -->
            <div class="mom-carousel-viewport" id="momCarouselViewport">
                <div class="mom-carousel-track" id="momCarouselTrack">

                    <?php
                    $idx = 0;
                    foreach ($activeReviews as $rev):
                        $mediaTypeClass = 'mom-card-' . $rev['media_type'];
                    ?>
                        <?php if ($rev['media_type'] === 'video'): ?>
                            <!-- Video Testimonial -->
                            <div class="mom-card <?php echo $mediaTypeClass ?>" data-index="<?php echo $idx ?>">
                                <div class="mom-media-wrap">
                                    <video class="mom-video" autoplay loop muted playsinline loading="lazy">
                                        <source src="<?php echo htmlspecialchars(resolveAssetUrl($rev['media_url'])) ?>" type="video/mp4">
                                    </video>
                                    <div class="mom-media-overlay"></div>
                                </div>
                                <div class="mom-card-content">
                                    <div class="mom-rating">
                                        <span class="mom-stars"><?php echo str_repeat('★', intval($rev['rating'])) ?></span>
                                    </div>
                                    <p class="mom-quote">"<?php echo strip_tags($rev['quote'], '<strong><b><i><em><u><br>') ?>"</p>
                                    <div class="mom-author">
                                        <span class="mom-name"><?php echo htmlspecialchars($rev['name']) ?></span>
                                        <span class="mom-role"><?php echo htmlspecialchars($rev['role'] ?? '') ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php elseif ($rev['media_type'] === 'image'): ?>
                            <!-- Image Testimonial -->
                            <div class="mom-card <?php echo $mediaTypeClass ?>" data-index="<?php echo $idx ?>">
                                <div class="mom-media-wrap">
                                    <img src="<?php echo htmlspecialchars(resolveAssetUrl($rev['media_url'])) ?>" alt="<?php echo htmlspecialchars($rev['name']) ?>" class="mom-img" loading="lazy">
                                    <div class="mom-media-overlay"></div>
                                </div>
                                <div class="mom-card-content">
                                    <div class="mom-rating">
                                        <span class="mom-stars"><?php echo str_repeat('★', intval($rev['rating'])) ?></span>
                                    </div>
                                    <p class="mom-quote">"<?php echo strip_tags($rev['quote'], '<strong><b><i><em><u><br>') ?>"</p>
                                    <div class="mom-author">
                                        <span class="mom-name"><?php echo htmlspecialchars($rev['name']) ?></span>
                                        <span class="mom-role"><?php echo htmlspecialchars($rev['role'] ?? '') ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <!-- Text-Only Testimonial -->
                            <div class="mom-card <?php echo $mediaTypeClass ?>" data-index="<?php echo $idx ?>">
                                <div class="mom-card-content">
                                    <div class="mom-quote-icon">"</div>
                                    <div class="mom-rating">
                                        <span class="mom-stars"><?php echo str_repeat('★', intval($rev['rating'])) ?></span>
                                    </div>
                                    <p class="mom-quote">"<?php echo strip_tags($rev['quote'], '<strong><b><i><em><u><br>') ?>"</p>
                                    <div class="mom-author">
                                        <span class="mom-name"><?php echo htmlspecialchars($rev['name']) ?></span>
                                        <span class="mom-role"><?php echo htmlspecialchars($rev['role'] ?? '') ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php
                        $idx++;
                    endforeach;
                    ?>
                </div>
            </div>
        </div>
    </section>

    <!-- ==========================================================================
         THE JOURNAL — Luxury Editorial Blog Slider
         ========================================================================== -->
    <section class="blog-carousel-section" id="blogCarouselSection" aria-label="The Journal - Editorial Blog">
        <div class="blog-carousel-container">
            <!-- Header row with editorial titles & navigation buttons -->
            <div class="blog-carousel-header">
                <div class="blog-carousel-title-group">
                    <span class="blog-carousel-eyebrow">The Journal</span>
                    <h2 class="blog-carousel-title">Parenting, Science &amp; Softness</h2>
                    <p class="blog-carousel-subtitle">Curated writings on baby care, sleep guides, and pediatrician tips.</p>
                </div>
                <div class="blog-carousel-nav">
                    <button class="blog-nav-btn prev-btn" aria-label="Previous Post">
                        <i class="ri-arrow-left-line"></i>
                    </button>
                    <button class="blog-nav-btn next-btn" aria-label="Next Post">
                        <i class="ri-arrow-right-line"></i>
                    </button>
                </div>
            </div>

            <!-- Carousel Viewport -->
            <div class="blog-carousel-viewport" id="blogCarouselViewport">
                <div class="blog-carousel-track" id="blogCarouselTrack">

                    <?php foreach ($activeBlogs as $b):
                        $blogImg  = resolveAssetUrl($b['thumbnail'] ?? '');
                        $blogDate = date('F d, Y', strtotime($b['created_at']));
                    ?>
                        <!-- Blog Card: <?php echo htmlspecialchars($b['title']) ?> -->
                        <article class="blog-carousel-card">
                            <a href="blog-details.php?slug=<?php echo urlencode($b['slug']) ?>" class="blog-card-anchor">
                                <div class="blog-card-media">
                                    <?php if (! empty($blogImg)): ?>
                                        <img src="<?php echo htmlspecialchars($blogImg) ?>" alt="<?php echo htmlspecialchars($b['title']) ?>" class="blog-card-img" loading="lazy">
                                    <?php endif; ?>
                                    <div class="blog-card-overlay">
                                        <span class="blog-card-read-tag">Read Article <i class="ri-arrow-right-line"></i></span>
                                    </div>
                                </div>
                                <div class="blog-card-details">
                                    <div class="blog-card-meta">
                                        <span class="blog-card-category"><?php echo htmlspecialchars($b['category'] ?? 'General') ?></span>
                                        <span class="blog-card-dot">&bull;</span>
                                        <span class="blog-card-read"><?php echo (int)($b['read_time'] ?? 5) ?> Min Read</span>
                                    </div>
                                    <h3 class="blog-card-title"><?php echo htmlspecialchars($b['title']) ?></h3>
                                    <p class="blog-card-excerpt"><?php echo htmlspecialchars($b['short_description'] ?? '') ?></p>
                                    <div class="blog-card-footer">
                                        <span class="blog-card-date"><?php echo $blogDate ?></span>
                                        <span class="blog-card-cta">Read More <i class="ri-arrow-right-line"></i></span>
                                    </div>
                                </div>
                            </a>
                        </article>
                    <?php endforeach; ?>

                </div>
            </div>

            <!-- Dot indicators -->
            <div class="blog-carousel-dots"></div>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?>