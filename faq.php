<?php
require_once __DIR__ . '/admin/config/database.php';
require_once __DIR__ . '/admin/config/faqs-helper.php';

try {
    $db = getDBConnection();
    $stmt = $db->prepare("SELECT * FROM faqs WHERE status = 'active' ORDER BY sort_order ASC, created_at DESC");
    $stmt->execute();
    $allFaqs = $stmt->fetchAll();
} catch (Exception $e) {
    error_log("Failed to fetch FAQs: " . $e->getMessage());
    $allFaqs = [];
}

// No fallback FAQ data is allowed. All FAQs are sourced strictly from the database.


$faqsByCategory = [];
foreach ($allFaqs as $faq) {
    $faqsByCategory[$faq['category']][] = $faq;
}

include 'includes/head.php';
?>

<?php include 'includes/header.php'; ?>

<main class="faq-page">

  <!-- 1. EDITORIAL FAQ HERO -->
  <section class="faq-hero">
    <div class="faq-hero-bg"></div>
    <div class="faq-hero-overlay"></div>
    <div class="faq-hero-content container">
      <span class="faq-hero-label">Parent Support</span>
      <h1 class="faq-hero-title">Your Questions, Answered with Care.</h1>
      <p class="faq-hero-subtext">
        From material safety standards and sizing guides to shipping updates and routine baby-care tips—find everything you need to navigate your comfort journey.
      </p>
    </div>
  </section>

  <!-- 2. BREADCRUMB BAR -->
  <div class="about-breadcrumb-bar">
    <div class="container">
      <nav class="about-breadcrumb" aria-label="Breadcrumb">
        <a href="./">Home</a>
        <span class="breadcrumb-separator">/</span>
        <span>FAQs</span>
      </nav>
    </div>
  </div>

  <!-- 3. SMART FAQ INTERACTIVE EXPERIENCE -->
  <section class="faq-interactive-section">
    <div class="container">
      <div class="faq-interactive-layout">
        
        <!-- Left Sidebar Navigation -->
        <aside class="faq-sidebar">
          <ul class="faq-categories-list">
            <?php
            $categories = getFaqCategories();
            $catIndex = 1;
            foreach ($categories as $catKey => $catLabel):
                $numStr = str_pad($catIndex, 2, '0', STR_PAD_LEFT);
                $isCatActive = ($catIndex === 1);
                $activeClass = $isCatActive ? ' active' : '';
            ?>
              <li class="faq-category-item<?= $activeClass ?>" data-category="<?= htmlspecialchars($catKey) ?>">
                <span class="faq-category-num"><?= $numStr ?></span>
                <span class="faq-category-name"><?= htmlspecialchars($catLabel) ?></span>
              </li>
            <?php 
              $catIndex++;
            endforeach; 
            ?>
          </ul>
        </aside>

        <!-- Right Accordion Containers -->
        <div class="faq-content">
          <?php
          $catIndex = 1;
          foreach ($categories as $catKey => $catLabel):
              $isCatActive = ($catIndex === 1);
              $activeClass = $isCatActive ? ' active' : '';
              $displayStyle = $isCatActive ? '' : ' style="display: none;"';
              $categoryFaqs = $faqsByCategory[$catKey] ?? [];
          ?>
            <div class="faq-accordion-group<?= $activeClass ?>" data-category="<?= htmlspecialchars($catKey) ?>"<?= $displayStyle ?>>
              <?php if (empty($categoryFaqs)): ?>
                <div class="faq-empty-state text-center py-4">
                  <p class="text-muted mb-0">No questions in this category yet.</p>
                </div>
              <?php else: ?>
                <?php foreach ($categoryFaqs as $faq): ?>
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
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
          <?php 
            $catIndex++;
          endforeach; 
          ?>
        </div>

      </div>
    </div>
  </section>

  <!-- 4. ASYMMETRICAL COMFORT PROMISE SECTION -->
  <section class="faq-promise-section">
    <div class="container promise-container">
      <div class="promise-grid">
        
        <!-- Left: Text Editorial -->
        <div class="promise-info">
          <span class="promise-eyebrow">Our Comfort Promise</span>
          <h2 class="promise-title">Designed for Sleep. Crafted for Safety.</h2>
          <p class="promise-desc">
            We believe parenting is filled with choices, but safety shouldn't be one of them. Every design choice behind CloudCush is made to protect the delicate skin barrier of your little one, ensuring calm nights and playful days.
          </p>

          <div class="promise-features-list">
            
            <div class="promise-feature-item">
              <div class="feature-icon-circle">
                <i class="ri-shield-user-line"></i>
              </div>
              <div class="feature-text">
                <h4 class="feature-title">Hypoallergenic Certified</h4>
                <p class="feature-desc">Independently tested and verified safe for newborns and eczema-prone skin.</p>
              </div>
            </div>

            <div class="promise-feature-item">
              <div class="feature-icon-circle">
                <i class="ri-leaf-line"></i>
              </div>
              <div class="feature-text">
                <h4 class="feature-title">100% Chlorine-Free</h4>
                <p class="feature-desc">Processed without any toxic chlorine or optical brighteners for pure safety.</p>
              </div>
            </div>

            <div class="promise-feature-item">
              <div class="feature-icon-circle">
                <i class="ri-cloud-line"></i>
              </div>
              <div class="feature-text">
                <h4 class="feature-title">Cotton-Soft Feel</h4>
                <p class="feature-desc">Fitted with plant-based fibers that mimic the touch of soft hand-woven cotton.</p>
              </div>
            </div>

          </div>
        </div>

        <!-- Right: Magazine Image Block -->
        <div class="promise-showcase">
          <div class="promise-img-wrap">
            <div class="promise-img-overlay"></div>
          </div>
          
          <!-- Overlapping Card -->
          <div class="promise-floating-card">
            <span class="floating-card-tag">D2C Trust Stamp</span>
            <h3 class="floating-card-title">Loved by 10,000+ Indian Parents</h3>
            <p class="floating-card-text">
              "We switched to CloudCush for our newborn after struggling with rashes. The difference was night and day—zero redness and no middle-of-the-night leaks."
            </p>
            <span class="floating-card-author">— Meera & Kabir S., Bengaluru</span>
          </div>
        </div>

      </div>
    </div>
  </section>

  <!-- 5. SUPPORT CTA SECTION -->
  <section class="faq-support-cta">
    <div class="container">
      <div class="support-cta-box">
        <span class="support-eyebrow">Still have questions?</span>
        <h2 class="support-title">Our Comfort Advocates are here to help.</h2>
        <p class="support-desc">
          Can't find the answers you're looking for? Reach out directly to our support team. We're here to make your parenting journey as soft and cozy as possible.
        </p>
        <div class="support-cta-buttons">
          <a href="mailto:support@cloudcush.com" class="btn-pill-white">
            <i class="ri-mail-line"></i> Email Advocates
          </a>
          <a href="tel:+91180028742273" class="btn-pill-outline-white">
            <i class="ri-phone-line"></i> +91-1800-CUSH-CARE
          </a>
        </div>
      </div>
    </div>
  </section>

</main>

<?php include 'includes/footer.php'; ?>
