<?php include 'includes/head.php'; ?>

<?php include 'includes/header.php'; ?>

<main class="blog-page">

  <!-- 1. CINEMATIC EDITORIAL HERO -->
  <section class="blog-hero">
    <div class="blog-hero-bg"></div>
    <div class="blog-hero-overlay"></div>
    <div class="blog-hero-content container">
      <span class="blog-hero-label">The CloudCush Journal</span>
      <h1 class="blog-hero-title">Nurturing the First Chapter.</h1>
      <p class="blog-hero-subtext">
        Explore a curated collection of parenting philosophies, pediatric skin health science, and cozy newborn routines designed for modern families.
      </p>
    </div>
  </section>

  <!-- 2. BREADCRUMB SECTION -->
  <div class="about-breadcrumb-bar">
    <div class="container">
      <nav class="about-breadcrumb" aria-label="Breadcrumb">
        <a href="./">Home</a>
        <span class="breadcrumb-separator">/</span>
        <span>The Journal</span>
      </nav>
    </div>
  </div>

  <!-- 3. FEATURED STORY SECTION -->
  <section class="blog-featured-section">
    <div class="container">
      <div class="blog-featured-layout">

        <!-- Large Editorial Image Wrap -->
        <div class="blog-featured-img-wrap">
          <img class="blog-featured-img" src="https://images.unsplash.com/photo-1519689680058-324335c77eba?w=1200&auto=format&fit=crop&q=80" alt="The Science of Touch" loading="lazy">
          <div class="blog-featured-img-overlay"></div>
          <span class="blog-featured-badge">Featured Article</span>
        </div>

        <!-- Overlapping Content Block -->
        <div class="blog-featured-content">
          <div class="blog-featured-meta">
            <span class="blog-post-tag">Pediatric Care</span>
            <span class="blog-post-dot">•</span>
            <span class="blog-post-read">5 Min Read</span>
          </div>
          <h2 class="blog-featured-title">
            <a href="blog-details.php">The Science of Touch: Skin Integrity in the Newborn Phase</a>
          </h2>
          <p class="blog-featured-desc">
            How physical touch, cotton-like softness, and totally chlorine-free materials interact to shape early developmental pathways and prevent diaper rashes.
          </p>
          <div class="blog-featured-author">
            <div class="author-avatar-wrap">
              <img class="author-avatar" src="https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=100&auto=format&fit=crop&q=80" alt="Dr. Sarah Varma">
            </div>
            <div class="author-info">
              <span class="author-name">Dr. Sarah Varma</span>
              <span class="author-role">Pediatric Dermatologist</span>
            </div>
          </div>
          <a href="blog-details.php" class="btn-text-arrow">
            Read Editorial <i class="ri-arrow-right-line"></i>
          </a>
        </div>

      </div>
    </div>
  </section>

  <!-- 4. CREATIVE EDITORIAL GRID -->
  <section class="blog-grid-section">
    <div class="container">

      <div class="blog-grid-header">
        <span class="blog-grid-subtitle">Curated Writings</span>
        <h2 class="blog-grid-title">Insights for Modern Parenting</h2>
      </div>

      <div class="blog-creative-grid">

        <!-- Card 1: Standard Card -->
        <article class="blog-card">
          <a href="blog-details.php" class="blog-card-link-wrapper">
            <div class="blog-card-img-wrap">
              <img class="blog-card-img" src="https://images.unsplash.com/photo-1544816155-12df9643f363?w=800&auto=format&fit=crop&q=80" alt="Cozy Sleep Routines" loading="lazy">
              <div class="blog-card-overlay">
                <span class="blog-card-btn-pill">Read Story</span>
              </div>
            </div>
            <div class="blog-card-content">
              <div class="blog-card-meta">
                <span class="blog-card-tag">Sleep Guides</span>
                <span class="blog-card-dot">•</span>
                <span class="blog-card-read">4 Min Read</span>
              </div>
              <h3 class="blog-card-title">Cozy Sleep: Structuring Your Baby's Night Routine</h3>
              <p class="blog-card-desc">
                From room temperature to calming sensory cues, explore our dermatologically safe evening wind-down rituals.
              </p>
            </div>
          </a>
        </article>

        <!-- Card 2: Typographic Quote Block (No Image) -->
        <article class="blog-card card-typographic">
          <div class="blog-card-content">
            <div class="quote-icon"><i class="ri-double-quotes-l"></i></div>
            <p class="quote-text">
              "Parenthood is not about perfection. It is about presence, softness, and creating safe, rash-free spaces for baby's natural milestones."
            </p>
            <div class="quote-author-wrap">
              <span class="quote-author-name">Anjali Verma</span>
              <span class="quote-author-role">Childhood Development Consultant</span>
            </div>
            <span class="quote-card-tag">Philosophy</span>
          </div>
        </article>

        <!-- Card 3: Standard Card -->
        <article class="blog-card">
          <a href="blog-details.php" class="blog-card-link-wrapper">
            <div class="blog-card-img-wrap">
              <img class="blog-card-img" src="https://images.unsplash.com/photo-1502086223501-7ea6ecd79368?w=800&auto=format&fit=crop&q=80" alt="Tackling Diaper Rash" loading="lazy">
              <div class="blog-card-overlay">
                <span class="blog-card-btn-pill">Read Article</span>
              </div>
            </div>
            <div class="blog-card-content">
              <div class="blog-card-meta">
                <span class="blog-card-tag">Care Tips</span>
                <span class="blog-card-dot">•</span>
                <span class="blog-card-read">5 Min Read</span>
              </div>
              <h3 class="blog-card-title">Tackling Diaper Rash: A Modern Parent's Checklist</h3>
              <p class="blog-card-desc">
                Our simple dermatologist-backed guide to managing skin health during diaper changes and growth milestones.
              </p>
            </div>
          </a>
        </article>

        <!-- Card 4: Horizontal Card (Automatically styled as horizontal via CSS grid nth-child selector) -->
        <article class="blog-card">
          <a href="blog-details.php" class="blog-card-link-wrapper">
            <div class="blog-card-img-wrap">
              <img class="blog-card-img" src="https://images.unsplash.com/photo-1484981138541-3d074aa97716?w=600&auto=format&fit=crop&q=80" alt="TCF Wood Pulp" loading="lazy">
              <div class="blog-card-overlay">
                <span class="blog-card-btn-pill">Read Article</span>
              </div>
            </div>
            <div class="blog-card-content">
              <div class="blog-card-meta">
                <span class="blog-card-tag">Product Insights</span>
                <span class="blog-card-dot">•</span>
                <span class="blog-card-read">6 Min Read</span>
              </div>
              <h3 class="blog-card-title">The Totally Chlorine-Free (TCF) Difference</h3>
              <p class="blog-card-desc">
                Why we rejected conventional bleach methods to protect delicate skin barriers and promote environmental health.
              </p>
            </div>
          </a>
        </article>
      </div>

      <!-- Pagination -->
      <div class="pagination">
        <button class="pagination-btn" disabled aria-label="Previous Page">
          <i class="ri-arrow-left-s-line"></i>
        </button>
        <button class="pagination-btn active">1</button>
        <button class="pagination-btn">2</button>
        <button class="pagination-btn">3</button>
        <span class="pagination-dots">...</span>
        <button class="pagination-btn" aria-label="Next Page">
          <i class="ri-arrow-right-s-line"></i>
        </button>
      </div>

    </div>
  </section>

  <!-- 5. PARENT TIPS / JOURNAL STRIP -->
  <section class="blog-tips-strip-section">
    <div class="blog-tips-strip-header">
      <div class="container">
        <span class="strip-subtitle">Quick Tips</span>
        <h2 class="strip-title">Daily Care Rituals</h2>
      </div>
    </div>
    <div class="blog-tips-marquee">
      <div class="blog-tips-track">

        <!-- Tip Card 1 -->
        <div class="tip-card">
          <div class="tip-icon"><i class="ri-heart-line"></i></div>
          <div class="tip-card-content">
            <h4 class="tip-card-title">Pat Dry Gently</h4>
            <p class="tip-card-desc">Always pat baby skin dry with a soft cloth instead of rubbing to avoid friction rashes.</p>
          </div>
        </div>

        <!-- Tip Card 2 -->
        <div class="tip-card">
          <div class="tip-icon"><i class="ri-time-line"></i></div>
          <div class="tip-card-content">
            <h4 class="tip-card-title">Change Regularly</h4>
            <p class="tip-card-desc">Change newborn diapers every 3-4 hours to keep active wetness off sensitive skin barriers.</p>
          </div>
        </div>

        <!-- Tip Card 3 -->
        <div class="tip-card">
          <div class="tip-icon"><i class="ri-shield-check-line"></i></div>
          <div class="tip-card-content">
            <h4 class="tip-card-title">TCF Certified Only</h4>
            <p class="tip-card-desc">Choose Totally Chlorine-Free diapers to ensure zero toxic bleach residues touch your baby.</p>
          </div>
        </div>

        <!-- Tip Card 4 -->
        <div class="tip-card">
          <div class="tip-icon"><i class="ri-windy-line"></i></div>
          <div class="tip-card-content">
            <h4 class="tip-card-title">Diaper-Free Time</h4>
            <p class="tip-card-desc">Allow 10-15 minutes of air circulation daily to naturally dry skin folds.</p>
          </div>
        </div>

        <!-- Tip Card 5 -->
        <div class="tip-card">
          <div class="tip-icon"><i class="ri-drop-line"></i></div>
          <div class="tip-card-content">
            <h4 class="tip-card-title">Pure Water Cleansing</h4>
            <p class="tip-card-desc">Use pure water and soft wipes rather than fragranced alcohol-based products.</p>
          </div>
        </div>

      </div>
      <!-- Duplicate Track for Seamless Loop -->
      <div class="blog-tips-track" aria-hidden="true">

        <!-- Tip Card 1 -->
        <div class="tip-card">
          <div class="tip-icon"><i class="ri-heart-line"></i></div>
          <div class="tip-card-content">
            <h4 class="tip-card-title">Pat Dry Gently</h4>
            <p class="tip-card-desc">Always pat baby skin dry with a soft cloth instead of rubbing to avoid friction rashes.</p>
          </div>
        </div>

        <!-- Tip Card 2 -->
        <div class="tip-card">
          <div class="tip-icon"><i class="ri-time-line"></i></div>
          <div class="tip-card-content">
            <h4 class="tip-card-title">Change Regularly</h4>
            <p class="tip-card-desc">Change newborn diapers every 3-4 hours to keep active wetness off sensitive skin barriers.</p>
          </div>
        </div>

        <!-- Tip Card 3 -->
        <div class="tip-card">
          <div class="tip-icon"><i class="ri-shield-check-line"></i></div>
          <div class="tip-card-content">
            <h4 class="tip-card-title">TCF Certified Only</h4>
            <p class="tip-card-desc">Choose Totally Chlorine-Free diapers to ensure zero toxic bleach residues touch your baby.</p>
          </div>
        </div>

        <!-- Tip Card 4 -->
        <div class="tip-card">
          <div class="tip-icon"><i class="ri-windy-line"></i></div>
          <div class="tip-card-content">
            <h4 class="tip-card-title">Diaper-Free Time</h4>
            <p class="tip-card-desc">Allow 10-15 minutes of air circulation daily to naturally dry skin folds.</p>
          </div>
        </div>

        <!-- Tip Card 5 -->
        <div class="tip-card">
          <div class="tip-icon"><i class="ri-drop-line"></i></div>
          <div class="tip-card-content">
            <h4 class="tip-card-title">Pure Water Cleansing</h4>
            <p class="tip-card-desc">Use pure water and soft wipes rather than fragranced alcohol-based products.</p>
          </div>
        </div>

      </div>
    </div>
  </section>

  <!-- 6. NEWSLETTER / CTA SECTION -->
  <section class="blog-newsletter-section">
    <div class="blog-newsletter-bg"></div>
    <div class="blog-newsletter-overlay"></div>
    <div class="blog-newsletter-content container">
      <span class="newsletter-eyebrow">CloudCush Circle</span>
      <h2 class="blog-newsletter-title">Insights for Mindful Parents</h2>
      <p class="blog-newsletter-desc">
        Receive our bi-weekly dispatch of pediatrician articles, skin-safety checklists, and subscriber-only collections.
      </p>
      <form class="blog-newsletter-form" action="javascript:void(0);" method="POST">
        <div class="form-input-wrap">
          <input type="email" placeholder="Your Email Address" required class="newsletter-email-input">
          <button type="submit" class="btn-pill-white">Subscribe</button>
        </div>
      </form>
      <p class="newsletter-disclaimer">Zero spam. Unsubscribe anytime.</p>
    </div>
  </section>

</main>

<?php include 'includes/footer.php'; ?>
