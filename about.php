<?php
require_once __DIR__ . '/admin/config/database.php';
require_once __DIR__ . '/admin/config/reviews-helper.php';
require_once __DIR__ . '/admin/config/about-helper.php';

// Fetch about page sections
$aboutSections = getAboutSections();

// Define fallbacks for each section in case database doesn't hold data
$fallbackAbout = [
    'hero' => [
        'section_title' => 'Thoughtfully Designed<br>for Modern Parenting.',
        'section_subtitle' => 'About CloudCush'
    ],
    'story_1' => [
        'section_title' => 'Crafted in Kota, Rajasthan.',
        'section_subtitle' => '01 / THE ORIGIN'
    ],
    'story_2' => [
        'section_title' => 'TCF Certified Safety.',
        'section_subtitle' => '02 / THE PLEDGE'
    ],
    'story_3' => [
        'section_title' => 'For Modern Indian Families.',
        'section_subtitle' => '03 / THE FUTURE'
    ],
    'philosophy' => [
        'section_title' => '"We believe parenting should feel softer, calmer, and more thoughtful — one comfortable moment at a time."',
        'section_subtitle' => 'Our Philosophy'
    ],
    'cta' => [
        'section_title' => 'Made for Better Baby Days.',
        'section_subtitle' => ''
    ],
    'features_header' => [
        'section_subtitle' => 'Features',
        'section_title' => 'Thoughtful Protection, Reimagined.'
    ],
    'about_faq_header' => [
        'section_subtitle' => 'Got Questions?',
        'section_title' => 'Frequently Asked Questions'
    ]
];

// Helper to resolve section data safely
function getAboutSectionData(string $key, array $sections, array $fallbacks): array {
    $sec = $sections[$key] ?? [];
    $fallback = $fallbacks[$key] ?? [];
    
    // Only resolve fallbacks for titles/subtitles. All other fields return empty string if not in DB.
    return [
        'section_title'    => !empty($sec['section_title'])    ? $sec['section_title']    : ($fallback['section_title'] ?? ''),
        'section_subtitle' => !empty($sec['section_subtitle']) ? $sec['section_subtitle'] : ($fallback['section_subtitle'] ?? ''),
        'content'          => !empty($sec['content'])          ? $sec['content']          : '',
        'accent_text'      => !empty($sec['accent_text'])      ? $sec['accent_text']      : '',
        'image_url'        => !empty($sec['image_url'])        ? $sec['image_url']        : '',
        'btn_text_1'       => !empty($sec['btn_text_1'])       ? $sec['btn_text_1']       : '',
        'btn_url_1'        => !empty($sec['btn_url_1'])        ? $sec['btn_url_1']        : '',
        'btn_text_2'       => !empty($sec['btn_text_2'])       ? $sec['btn_text_2']       : '',
        'btn_url_2'        => !empty($sec['btn_url_2'])        ? $sec['btn_url_2']        : ''
    ];
}

try {
    $db = getDBConnection();
    $stmt = $db->prepare("SELECT * FROM reviews WHERE status = 'active' ORDER BY created_at DESC");
    $stmt->execute();
    $activeReviews = $stmt->fetchAll();
} catch (Exception $e) {
    error_log("Failed to fetch reviews: " . $e->getMessage());
    $activeReviews = [];
}

// Fetch active FAQs for the FAQ block
try {
    $activeFaqs = getAboutFaqs();
} catch (Exception $e) {
    error_log("Failed to fetch FAQs: " . $e->getMessage());
    $activeFaqs = [];
}

// FAQs are loaded strictly from the database


?>
<?php include 'includes/head.php'; ?>

<?php include 'includes/header.php'; ?>


<main class="about-page">

  <?php $hero = getAboutSectionData('hero', $aboutSections, $fallbackAbout); ?>
  <!-- 1. HERO BANNER SECTION -->
  <section class="about-hero">
    <div class="about-hero-bg"></div>
    <div class="about-hero-overlay"></div>
    <div class="about-hero-content">
      <span class="about-hero-label"><?= htmlspecialchars($hero['section_subtitle']) ?></span>
      <h1 class="about-hero-title"><?= strip_tags($hero['section_title'], '<br>') ?></h1>
      <div class="about-hero-subtext">
        <?= strip_tags($hero['content'], '<strong><b><i><em><u><br><p><ul><li><a>') ?>
      </div>
      <div class="about-hero-actions">
        <a href="products.php" class="btn-pill btn-pill-primary">Explore Collection</a>
        <a href="javascript:void(0);" class="btn-pill cta-scroll-philosophy">Our Philosophy</a>
      </div>
    </div>
    <div class="about-hero-scroll">
      <span>Scroll</span>
      <div class="scroll-mouse">
        <div class="scroll-wheel"></div>
      </div>
    </div>
  </section>

  <!-- 2. BREADCRUMB SECTION -->
  <div class="about-breadcrumb-bar">
    <div class="container">
      <nav class="about-breadcrumb" aria-label="Breadcrumb">
        <a href="./">Home</a>
        <span class="breadcrumb-separator">/</span>
        <span>About Us</span>
      </nav>
    </div>
  </div>

  <!-- New About Section: Brand Intro/Vision -->
  <section class="about-vision-section">
    <div class="about-vision-container">
      <div class="about-vision-grid">
        <!-- Left Column: Copy -->
        <div class="about-vision-content">
          <span class="vision-eyebrow">ABOUT US</span>
          <h2 class="vision-title">Every day is a new beginning. We make it softer.</h2>
          <div class="vision-text-wrap">
            <p class="vision-lead">
              We believe that early childhood is a sacred phase. Our mission is to create a nurturing, safe environment for your baby with products that represent the absolute pinnacle of material science and skin comfort.
            </p>
            <p class="vision-body">
              By rejecting harsh chemical bleaching, fragrance additives, and plastic-heavy fillers, CloudCush offers a cleaner alternative that respects your child's delicate skin barrier. Every fabric selection, seam design, and fit adjustment is refined iteratively alongside pediatric dermatologists.
            </p>
          </div>
        </div>
        <!-- Right Column: Visual Showcase -->
        <div class="about-vision-visual">
          <div class="vision-image-wrapper">
            <img src="https://images.unsplash.com/photo-1522845015757-58b2acd497c7?auto=format&fit=crop&w=800&q=80" alt="CloudCush Baby Comfort" class="vision-main-img" loading="lazy">
            <div class="vision-badge-floating">
              <span class="badge-number">100%</span>
              <span class="badge-label">Hypoallergenic</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- 3. PINNED HORIZONTAL STORYTELLING STRIP -->
  <section class="horizontal-story-section">
    <div class="horizontal-story-container">
      <div class="horizontal-story-track">
        
        <?php 
        for ($i = 1; $i <= 3; $i++): 
            $story = getAboutSectionData('story_' . $i, $aboutSections, $fallbackAbout);
        ?>
          <!-- Story Card <?= $i ?> -->
          <div class="story-card">
            <?php if (!empty($story['image_url'])): ?>
              <div class="story-card-img-wrap">
                <img class="story-card-img" src="<?= htmlspecialchars(resolveAssetUrl($story['image_url'])) ?>" alt="<?= htmlspecialchars(strip_tags($story['section_title'])) ?>" loading="lazy">
              </div>
            <?php endif; ?>
            <div class="story-card-content">
              <span class="story-card-label"><?= htmlspecialchars($story['section_subtitle']) ?></span>
              <h3 class="story-card-title"><?= htmlspecialchars($story['section_title']) ?></h3>
              <div class="story-card-desc">
                <?= strip_tags($story['content'], '<strong><b><i><em><u><br><p><ul><li><a>') ?>
              </div>
              <button type="button" class="story-read-more-btn" aria-expanded="false">Read More</button>
              <?php if (!empty($story['accent_text'])): ?>
                <div class="story-card-accent">
                  <?= htmlspecialchars($story['accent_text']) ?>
                </div>
              <?php endif; ?>
            </div>
          </div>
        <?php endfor; ?>

      </div>
    </div>
  </section>

  <!-- 4. WHY CHOOSE CLOUDCUSH SECTION -->
  <section class="why-choose-section">
    <div class="container">
      
      <?php $featuresHeader = getAboutSectionData('features_header', $aboutSections, $fallbackAbout); ?>
      <div class="why-choose-header">
        <span class="why-choose-label"><?= htmlspecialchars($featuresHeader['section_subtitle']) ?></span>
        <h2 class="why-choose-title"><?= htmlspecialchars($featuresHeader['section_title']) ?></h2>
      </div>

      <?php 
      $dynamicFeatures = getAboutFeatures();
      if (empty($dynamicFeatures)) {
          $dynamicFeatures = [];
      }

      ?>

      <div class="why-choose-grid">
        <?php foreach ($dynamicFeatures as $feat): ?>
          <div class="why-card">
            <div class="why-icon-wrap">
              <i class="<?= htmlspecialchars($feat['icon_class'] ?? 'ri-checkbox-circle-line') ?>"></i>
            </div>
            <h3 class="why-card-title"><?= htmlspecialchars($feat['title']) ?></h3>
            <div class="why-card-desc">
              <?= strip_tags($feat['description'], '<strong><b><i><em><u><br><p><ul><li><a>') ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

    </div>
  </section>

  <!-- 5. INFINITE TEXT MARQUEE -->
  <section class="marquee-section">
    <div class="marquee-track">
      <div class="marquee-content">
        <span>SOFTNESS</span>
        <span class="marquee-dot">•</span>
        <span>BREATHABILITY</span>
        <span class="marquee-dot">•</span>
        <span>HYPOALLERGENIC</span>
        <span class="marquee-dot">•</span>
        <span>12-HOUR DRYNESS</span>
        <span class="marquee-dot">•</span>
        <span>CERTIFIED SAFE</span>
        <span class="marquee-dot">•</span>
        <span>PURE COMFORT</span>
        <span class="marquee-dot">•</span>
        <span>THOUGHTFULLY DESIGNED</span>
        <span class="marquee-dot">•</span>
      </div>
      <!-- Duplicate for loop -->
      <div class="marquee-content" aria-hidden="true">
        <span>SOFTNESS</span>
        <span class="marquee-dot">•</span>
        <span>BREATHABILITY</span>
        <span class="marquee-dot">•</span>
        <span>HYPOALLERGENIC</span>
        <span class="marquee-dot">•</span>
        <span>12-HOUR DRYNESS</span>
        <span class="marquee-dot">•</span>
        <span>CERTIFIED SAFE</span>
        <span class="marquee-dot">•</span>
        <span>PURE COMFORT</span>
        <span class="marquee-dot">•</span>
        <span>THOUGHTFULLY DESIGNED</span>
        <span class="marquee-dot">•</span>
      </div>
    </div>
  </section>

  <?php $phil = getAboutSectionData('philosophy', $aboutSections, $fallbackAbout); ?>
  <!-- 6. LAYERED PHILOSOPHY SECTION -->
  <section class="philosophy-layered-section">
    <div class="philosophy-grid">
      
      <!-- Left Column: Storytelling quote -->
      <div class="philosophy-left">
        <span class="philosophy-quote-label"><?= htmlspecialchars($phil['section_subtitle']) ?></span>
        <h2 class="philosophy-huge-text">
          <?= strip_tags($phil['section_title'], '<strong><b><i><em><u><br><p><ul><li><a>') ?>
        </h2>
      </div>

      <!-- Right Column: Parallax Image visual -->
      <?php if (!empty($phil['image_url'])): ?>
        <div class="philosophy-right-visual">
          <img src="<?= htmlspecialchars(resolveAssetUrl($phil['image_url'])) ?>" alt="CloudCush Baby and Parent Lifestyle" loading="lazy">
        </div>
      <?php endif; ?>

    </div>
  </section>

  <!-- 7. REUSED TESTIMONIAL CAROUSEL (MOM-APPROVED MOMENTS) -->
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
                  <div class="mom-card <?= $mediaTypeClass ?>" data-index="<?= $idx ?>">
                      <div class="mom-media-wrap">
                          <video class="mom-video" autoplay loop muted playsinline loading="lazy">
                              <source src="<?= htmlspecialchars(resolveAssetUrl($rev['media_url'])) ?>" type="video/mp4">
                          </video>
                          <div class="mom-media-overlay"></div>
                      </div>
                      <div class="mom-card-content">
                          <div class="mom-rating">
                              <span class="mom-stars"><?= str_repeat('★', $rev['rating']) ?></span>
                          </div>
                          <p class="mom-quote">"<?= strip_tags($rev['quote'], '<strong><b><i><em><u><br>') ?>"</p>
                          <div class="mom-author">
                              <span class="mom-name"><?= htmlspecialchars($rev['name']) ?></span>
                              <span class="mom-role"><?= htmlspecialchars($rev['role'] ?? '') ?></span>
                          </div>
                      </div>
                  </div>
              <?php elseif ($rev['media_type'] === 'image'): ?>
                  <!-- Image Testimonial -->
                  <div class="mom-card <?= $mediaTypeClass ?>" data-index="<?= $idx ?>">
                      <div class="mom-media-wrap">
                          <img src="<?= htmlspecialchars(resolveAssetUrl($rev['media_url'])) ?>" alt="<?= htmlspecialchars($rev['name']) ?>" class="mom-img" loading="lazy">
                          <div class="mom-media-overlay"></div>
                      </div>
                      <div class="mom-card-content">
                          <div class="mom-rating">
                              <span class="mom-stars"><?= str_repeat('★', $rev['rating']) ?></span>
                          </div>
                          <p class="mom-quote">"<?= strip_tags($rev['quote'], '<strong><b><i><em><u><br>') ?>"</p>
                          <div class="mom-author">
                              <span class="mom-name"><?= htmlspecialchars($rev['name']) ?></span>
                              <span class="mom-role"><?= htmlspecialchars($rev['role'] ?? '') ?></span>
                          </div>
                      </div>
                  </div>
              <?php else: ?>
                  <!-- Text-Only Testimonial -->
                  <div class="mom-card <?= $mediaTypeClass ?>" data-index="<?= $idx ?>">
                      <div class="mom-card-content">
                          <div class="mom-quote-icon">“</div>
                          <div class="mom-rating">
                              <span class="mom-stars"><?= str_repeat('★', $rev['rating']) ?></span>
                          </div>
                          <p class="mom-quote">"<?= strip_tags($rev['quote'], '<strong><b><i><em><u><br>') ?>"</p>
                          <div class="mom-author">
                              <span class="mom-name"><?= htmlspecialchars($rev['name']) ?></span>
                              <span class="mom-role"><?= htmlspecialchars($rev['role'] ?? '') ?></span>
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


  <!-- 8. FAQ SECTION -->
  <section class="faq-section">
    <div class="container">
      
      <?php $faqHeader = getAboutSectionData('about_faq_header', $aboutSections, $fallbackAbout); ?>
      <div class="faq-header">
        <span class="faq-label"><?= htmlspecialchars($faqHeader['section_subtitle']) ?></span>
        <h2 class="faq-title"><?= htmlspecialchars($faqHeader['section_title']) ?></h2>
      </div>

      <div class="faq-container">
        <div class="faq-accordion-group">
          
          <?php 
          $faqIndex = 1;
          foreach ($activeFaqs as $faq): 
          ?>
            <!-- FAQ <?= $faqIndex ?> -->
            <div class="faq-item">
              <button class="faq-trigger" aria-expanded="false">
                <span class="faq-question"><?= htmlspecialchars($faq['question']) ?></span>
                <span class="faq-icon-box"><i class="ri-add-line"></i></span>
              </button>
              <div class="faq-panel">
                <div class="faq-panel-inner">
                  <?= strip_tags($faq['answer'], '<strong><b><i><em><u><br><p><ul><li><a>') ?>
                </div>
              </div>
            </div>
          <?php 
              $faqIndex++;
          endforeach; 
          ?>

        </div>
      </div>

    </div>
  </section>

  <?php $cta = getAboutSectionData('cta', $aboutSections, $fallbackAbout); ?>
  <!-- 9. FINAL CTA SECTION -->
  <section class="about-cta-section">
    <div class="about-cta-bg"></div>
    <div class="about-cta-overlay"></div>
    <div class="about-cta-content">
      <h2 class="about-cta-title"><?= htmlspecialchars($cta['section_title']) ?></h2>
      <div class="about-cta-desc">
        <?= strip_tags($cta['content'], '<strong><b><i><em><u><br><p><ul><li><a>') ?>
      </div>
      <div class="about-cta-actions">
        <?php if (!empty($cta['btn_text_1'])): ?>
          <a href="<?= htmlspecialchars($cta['btn_url_1']) ?>" class="btn-pill btn-pill-primary"><?= htmlspecialchars($cta['btn_text_1']) ?></a>
        <?php endif; ?>
        <?php if (!empty($cta['btn_text_2'])): ?>
          <a href="<?= htmlspecialchars($cta['btn_url_2']) ?>" class="btn-pill"><?= htmlspecialchars($cta['btn_text_2']) ?></a>
        <?php endif; ?>
      </div>
    </div>
  </section>

</main>

<?php include 'includes/footer.php'; ?>


