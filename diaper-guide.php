<?php
require_once __DIR__ . '/admin/config/database.php';
require_once __DIR__ . '/admin/config/guide-helper.php';

// Fetch guide sections
$guideSections = getGuideSections();
$timeline = getGuideTimeline();
$metrics = getGuideMetrics();
$layers = getGuideLayers();

// Define fallbacks for sections
$fallbackGuide = [
    'hero' => [
        'section_subtitle' => 'Interactive Experience',
        'section_title' => 'A Journey of Touch, Safety, and Softness.'
    ],
    'quote' => [
        'section_title' => 'Dr. Anjali Sen, MD',
        'section_subtitle' => 'Consultant Pediatric Dermatologist, New Delhi'
    ],
    'cta' => [
        'section_subtitle' => 'Designed for peaceful nights',
        'section_title' => 'Softness that lasts, safety you can trust.'
    ],
    'metrics_header' => [
        'section_subtitle' => 'The Proof in Comfort',
        'section_title' => 'Dermatological Standards. Proven Results.'
    ]
];

// Helper to resolve section data safely
function getGuideSectionData(string $key, array $sections, array $fallbacks): array {
    $sec = $sections[$key] ?? [];
    $fallback = $fallbacks[$key] ?? [];
    
    // Only resolve fallbacks for titles/subtitles. All other fields return empty string if not in DB.
    return [
        'section_title'    => !empty($sec['section_title'])    ? $sec['section_title']    : ($fallback['section_title'] ?? ''),
        'section_subtitle' => !empty($sec['section_subtitle']) ? $sec['section_subtitle'] : ($fallback['section_subtitle'] ?? ''),
        'content'          => !empty($sec['content'])          ? $sec['content']          : '',
        'btn_text_1'       => !empty($sec['btn_text_1'])       ? $sec['btn_text_1']       : '',
        'btn_url_1'        => !empty($sec['btn_url_1'])        ? $sec['btn_url_1']        : '',
        'btn_text_2'       => !empty($sec['btn_text_2'])       ? $sec['btn_text_2']       : '',
        'btn_url_2'        => !empty($sec['btn_url_2'])        ? $sec['btn_url_2']        : ''
    ];
}

if (empty($timeline)) {
    $timeline = [];
}

if (empty($layers)) {
    $layers = [];
}


include 'includes/head.php';
include 'includes/header.php';
?>

<main class="guide-page">

  <?php $hero = getGuideSectionData('hero', $guideSections, $fallbackGuide); ?>
  <!-- 1. IMMERSIVE HERO SECTION -->
  <section class="guide-hero">
    <div class="guide-hero-bg"></div>
    <div class="guide-hero-overlay"></div>
    <div class="guide-hero-content container">
      <span class="guide-hero-label"><?= htmlspecialchars($hero['section_subtitle']) ?></span>
      <h1 class="guide-hero-title"><?= htmlspecialchars($hero['section_title']) ?></h1>
      <div class="guide-hero-subtext">
        <?= strip_tags($hero['content'], '<strong><b><i><em><u><br><p><ul><li><a>') ?>
      </div>
      <div class="guide-hero-scroll">
        <span>Scroll Journey</span>
        <div class="scroll-mouse">
          <div class="scroll-wheel"></div>
        </div>
      </div>
    </div>
  </section>

  <!-- 2. SCROLL PINNED TIMELINE EXPERIENCE -->
  <section class="guide-timeline-section" id="guideTimelineSection">
    <div class="container guide-timeline-container">

      <!-- Left: Navigation Timeline (Sticky on Desktop) -->
      <aside class="guide-timeline-nav">
        <div class="timeline-line">
          <div class="timeline-progress-bar" id="timelineProgressBar"></div>
        </div>
        <ul class="timeline-steps">
          <?php 
          $stepIndex = 0;
          foreach ($timeline as $t): 
          ?>
            <li class="timeline-step <?= $stepIndex === 0 ? 'active' : '' ?>" data-step="<?= $stepIndex ?>">
              <span class="step-dot"></span>
              <span class="step-title"><?= htmlspecialchars(sprintf('%02d', $stepIndex + 1)) ?>. <?= htmlspecialchars($t['title']) ?></span>
            </li>
          <?php 
              $stepIndex++;
          endforeach; 
          ?>
        </ul>
      </aside>

      <!-- Right: Changing Media/Info Panels -->
      <div class="guide-timeline-panels">

        <?php 
        $stepIndex = 0;
        foreach ($timeline as $t): 
        ?>
          <!-- Stage <?= htmlspecialchars($t['subtitle']) ?> -->
          <div class="timeline-panel <?= $stepIndex === 0 ? 'active' : '' ?>" data-step="<?= $stepIndex ?>">
            <?php if (!empty($t['image_url'])): ?>
              <div class="panel-media">
                <div class="panel-img-wrap">
                  <img src="<?= htmlspecialchars(resolveAssetUrl($t['image_url'])) ?>" alt="<?= htmlspecialchars($t['title']) ?>" loading="<?= $stepIndex === 0 ? 'eager' : 'lazy' ?>">
                  <div class="media-overlay"></div>
                </div>
              </div>
            <?php endif; ?>
            <div class="panel-info">
              <span class="panel-eyebrow"><?= htmlspecialchars($t['subtitle']) ?></span>
              <h3 class="panel-title"><?= htmlspecialchars($t['title_heading']) ?></h3>
              <div class="panel-desc">
                <?= strip_tags($t['description'], '<strong><b><i><em><u><br><p><ul><li><a>') ?>
              </div>
            </div>
          </div>
        <?php 
            $stepIndex++;
        endforeach; 
        ?>

      </div>

    </div>
  </section>

  <!-- 3. COMFORT METRICS SECTION (INFOGRAPHIC) -->
  <section class="guide-metrics-section">
    <div class="container">
      <?php $metricsHeader = getGuideSectionData('metrics_header', $guideSections, $fallbackGuide); ?>
      <div class="metrics-header">
        <span class="metrics-eyebrow"><?= htmlspecialchars($metricsHeader['section_subtitle']) ?></span>
        <h2 class="metrics-title"><?= htmlspecialchars($metricsHeader['section_title']) ?></h2>
      </div>
      
      <?php 
      if (empty($metrics)) {
          $metrics = [];
      }

      ?>
      <div class="metrics-grid">
        <?php foreach ($metrics as $m): 
            $hoursOpt = ($m['suffix_type'] === 'hours') ? 'true' : 'false';
            $percentOpt = ($m['suffix_type'] === 'percent') ? 'true' : 'false';
            $plusOpt = ($m['suffix_type'] === 'plus') ? 'true' : 'false';
            $starOpt = ($m['suffix_type'] === 'star') ? 'true' : 'false';
            $decVal = intval($m['decimals'] ?? 0);
            
            $startText = '0';
            if ($m['suffix_type'] === 'hours') $startText = '0h';
            elseif ($m['suffix_type'] === 'percent') $startText = '0%';
            elseif ($m['suffix_type'] === 'plus') $startText = '0+';
            elseif ($m['suffix_type'] === 'star') $startText = '0.0';
        ?>
          <!-- Metric Card -->
          <div class="metric-card">
            <div class="metric-icon"><i class="<?= htmlspecialchars($m['icon_class']) ?>"></i></div>
            <span class="metric-number" 
                  data-target="<?= htmlspecialchars($m['target_value']) ?>" 
                  <?= $hoursOpt === 'true' ? 'data-hours="true"' : '' ?>
                  <?= $percentOpt === 'true' ? 'data-percent="true"' : '' ?>
                  <?= $plusOpt === 'true' ? 'data-plus="true"' : '' ?>
                  <?= $starOpt === 'true' ? 'data-star="true"' : '' ?>
                  <?= $decVal > 0 ? 'data-decimals="' . $decVal . '"' : '' ?>
            ><?= $startText ?></span>
            <h4 class="metric-label"><?= htmlspecialchars($m['label']) ?></h4>
            <div class="metric-desc"><?= strip_tags($m['description'], '<strong><b><i><em><u><br><p><ul><li><a>') ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- 4. PINNED VISUAL STORY SECTION (APPLE-STYLE LAYER STORY) -->
  <section class="visual-story-section" id="visualStorySection">
    <div class="container visual-story-grid">

      <!-- Left: Sticky Diaper Image Layering -->
      <div class="visual-story-sticky">
        <div class="sticky-media-inner">
          
          <?php 
          $layerIndex = 0;
          foreach ($layers as $l): 
          ?>
            <?php if (!empty($l['image_url'])): ?>
              <div class="visual-story-image <?= $layerIndex === 0 ? 'active' : '' ?>" data-story="<?= $layerIndex + 1 ?>">
                <img src="<?= htmlspecialchars(resolveAssetUrl($l['image_url'])) ?>" alt="<?= htmlspecialchars($l['title']) ?>">
                <span class="media-caption"><?= htmlspecialchars($l['caption']) ?></span>
              </div>
            <?php endif; ?>
          <?php 
              $layerIndex++;
          endforeach; 
          ?>

        </div>
      </div>

      <!-- Right: Scrolling Explanatory Blocks -->
      <div class="visual-story-scrollable">

        <?php 
        $layerIndex = 0;
        foreach ($layers as $l): 
        ?>
          <!-- Block <?= $layerIndex + 1 ?> -->
          <div class="visual-story-block" data-story="<?= $layerIndex + 1 ?>">
            <span class="story-badge"><?= htmlspecialchars($l['badge']) ?></span>
            <h3 class="story-title"><?= htmlspecialchars($l['title']) ?></h3>
            <div class="story-desc">
              <?= strip_tags($l['description'], '<strong><b><i><em><u><br><p><ul><li><a>') ?>
            </div>
            
            <?php 
            if (!empty($l['specs'])): 
                $specsArr = array_map('trim', explode(',', $l['specs']));
            ?>
              <div class="story-specs">
                <?php foreach ($specsArr as $spec): ?>
                  <span><?= htmlspecialchars($spec) ?></span>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        <?php 
            $layerIndex++;
        endforeach; 
        ?>

      </div>

    </div>
  </section>

  <?php $quote = getGuideSectionData('quote', $guideSections, $fallbackGuide); ?>
  <!-- 5. EDITORIAL PEDIATRICIAN QUOTE BLOCK -->
  <section class="guide-quote-section">
    <div class="container">
      <div class="quote-box">
        <div class="quote-icon"><i class="ri-double-quotes-l"></i></div>
        <blockquote class="pediatrician-quote">
          "<?= strip_tags($quote['content'], '<strong><b><i><em><u><br><p><ul><li><a>') ?>"
        </blockquote>
        <div class="quote-author">
          <span class="author-name"><?= htmlspecialchars($quote['section_title']) ?></span>
          <span class="author-title"><?= htmlspecialchars($quote['section_subtitle']) ?></span>
        </div>
      </div>
    </div>
  </section>

  <?php $cta = getGuideSectionData('cta', $guideSections, $fallbackGuide); ?>
  <!-- 6. EMOTIONAL FINAL CTA SECTION -->
  <section class="guide-cta-section">
    <div class="container">
      <div class="guide-cta-box">
        <span class="cta-eyebrow"><?= htmlspecialchars($cta['section_subtitle']) ?></span>
        <h2 class="cta-title"><?= htmlspecialchars($cta['section_title']) ?></h2>
        <div class="cta-desc">
          <?= strip_tags($cta['content'], '<strong><b><i><em><u><br><p><ul><li><a>') ?>
        </div>
        <div class="cta-actions">
          <?php if (!empty($cta['btn_text_1'])): ?>
            <a href="<?= htmlspecialchars($cta['btn_url_1']) ?>" class="btn-pill-white"><?= htmlspecialchars($cta['btn_text_1']) ?></a>
          <?php endif; ?>
          <?php if (!empty($cta['btn_text_2'])): ?>
            <a href="<?= htmlspecialchars($cta['btn_url_2']) ?>" class="btn-pill-outline-white"><?= htmlspecialchars($cta['btn_text_2']) ?></a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </section>

</main>

<?php include 'includes/footer.php'; ?>
