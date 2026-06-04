<?php include 'includes/head.php'; ?>

<?php include 'includes/header.php'; ?>

<main class="guide-page">

  <!-- 1. IMMERSIVE HERO SECTION -->
  <section class="guide-hero">
    <div class="guide-hero-bg"></div>
    <div class="guide-hero-overlay"></div>
    <div class="guide-hero-content container">
      <span class="guide-hero-label">Interactive Experience</span>
      <h1 class="guide-hero-title">A Journey of Touch, Safety, and Softness.</h1>
      <p class="guide-hero-subtext">
        Scroll to explore our baby-centric technology and see how we redefine newborn routine comfort, layer by layer, from newborn crawls to peaceful nights.
      </p>
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
          <li class="timeline-step active" data-step="0">
            <span class="step-dot"></span>
            <span class="step-title">01. Gentle Materials</span>
          </li>
          <li class="timeline-step" data-step="1">
            <span class="step-dot"></span>
            <span class="step-title">02. Breathable Layers</span>
          </li>
          <li class="timeline-step" data-step="2">
            <span class="step-dot"></span>
            <span class="step-title">03. Leak Protection</span>
          </li>
          <li class="timeline-step" data-step="3">
            <span class="step-dot"></span>
            <span class="step-title">04. Night Comfort</span>
          </li>
          <li class="timeline-step" data-step="4">
            <span class="step-dot"></span>
            <span class="step-title">05. Skin Safety</span>
          </li>
          <li class="timeline-step" data-step="5">
            <span class="step-dot"></span>
            <span class="step-title">06. All-Day Movement</span>
          </li>
        </ul>
      </aside>

      <!-- Right: Changing Media/Info Panels -->
      <div class="guide-timeline-panels">

        <!-- Stage 01: Gentle Materials -->
        <div class="timeline-panel active" data-step="0">
          <div class="panel-media">
            <div class="panel-img-wrap">
              <img src="https://images.unsplash.com/photo-1544816155-12df9643f363?w=800&auto=format&fit=crop&q=80" alt="Gentle Materials" loading="eager">
              <div class="media-overlay"></div>
            </div>
          </div>
          <div class="panel-info">
            <span class="panel-eyebrow">Milestone 01</span>
            <h3 class="panel-title">Fiber-Level Pureness</h3>
            <p class="panel-desc">
              We source Totally Chlorine-Free (TCF) wood pulp from responsibly managed Nordic forests. Unbleached by chlorine or optical brighteners, this organic core is pure, soft, and completely safe for your baby's delicate skin barrier.
            </p>
          </div>
        </div>

        <!-- Stage 02: Breathable Layers -->
        <div class="timeline-panel" data-step="1">
          <div class="panel-media">
            <div class="panel-img-wrap">
              <img src="https://images.unsplash.com/photo-1502086223501-7ea6ecd79368?w=800&auto=format&fit=crop&q=80" alt="Breathable Layers" loading="lazy">
              <div class="media-overlay"></div>
            </div>
          </div>
          <div class="panel-info">
            <span class="panel-eyebrow">Milestone 02</span>
            <h3 class="panel-title">Microscopic Aeration</h3>
            <p class="panel-desc">
              Trapped humidity triggers diaper rash. Our plant-based backsheet features millions of microscopic pores that allow air to circulate freely while keeping wetness locked inside. Keep baby's skin cool, dry, and fresh.
            </p>
          </div>
        </div>

        <!-- Stage 03: Leak Protection -->
        <div class="timeline-panel" data-step="2">
          <div class="panel-media">
            <div class="panel-img-wrap">
              <img src="https://images.unsplash.com/photo-1519689680058-324335c77eba?w=800&auto=format&fit=crop&q=80" alt="Leak Protection" loading="lazy">
              <div class="media-overlay"></div>
            </div>
          </div>
          <div class="panel-info">
            <span class="panel-eyebrow">Milestone 03</span>
            <h3 class="panel-title">Double Barrier Lock</h3>
            <p class="panel-desc">
              Equipped with a Japanese Superabsorbent Polymer (SAP) core and double-layered hydrophobic leg cuffs. Our diaper dynamically wraps your baby's shape, catching wetness instantly to block side leaks and blowouts.
            </p>
          </div>
        </div>

        <!-- Stage 04: Night Comfort -->
        <div class="timeline-panel" data-step="3">
          <div class="panel-media">
            <div class="panel-img-wrap">
              <img src="https://images.unsplash.com/photo-1522850959074-3d074aa97716?w=800&auto=format&fit=crop&q=80" alt="Night Comfort" loading="lazy">
              <div class="media-overlay"></div>
            </div>
          </div>
          <div class="panel-info">
            <span class="panel-eyebrow">Milestone 04</span>
            <h3 class="panel-title">12-Hour Peaceful Sleep</h3>
            <p class="panel-desc">
              Sleep is crucial for newborn development. Our high-capacity lock layer holds up to 10x its weight, keeping skin completely dry for up to 12 hours so your baby (and you) can sleep through the night undisturbed.
            </p>
          </div>
        </div>

        <!-- Stage 05: Skin Safety -->
        <div class="timeline-panel" data-step="4">
          <div class="panel-media">
            <div class="panel-img-wrap">
              <img src="https://images.unsplash.com/photo-1544022613-e87ca75a784a?w=800&auto=format&fit=crop&q=80" alt="Skin Safety" loading="lazy">
              <div class="media-overlay"></div>
            </div>
          </div>
          <div class="panel-info">
            <span class="panel-eyebrow">Milestone 05</span>
            <h3 class="panel-title">Certified Hypoallergenic</h3>
            <p class="panel-desc">
              Approved by dermatologists and certified chemical-free. We formulate with zero fragrances, zero parabens, zero lotions, and zero allergens. It's the ultimate skin protection for eczema-prone or reactive newborn skin.
            </p>
          </div>
        </div>

        <!-- Stage 06: All-Day Movement -->
        <div class="timeline-panel" data-step="5">
          <div class="panel-media">
            <div class="panel-img-wrap">
              <img src="https://images.unsplash.com/photo-1484981138541-3d074aa97716?w=800&auto=format&fit=crop&q=80" alt="All-Day Movement" loading="lazy">
              <div class="media-overlay"></div>
            </div>
          </div>
          <div class="panel-info">
            <span class="panel-eyebrow">Milestone 06</span>
            <h3 class="panel-title">Active 3D Fit</h3>
            <p class="panel-desc">
              As your baby rolls, crawls, and takes their first steps, our ultra-flexible 3D stretch waistband adapts. It gently expands with breathing while preventing sag, keeping the diaper perfectly snug through every wiggle.
            </p>
          </div>
        </div>

      </div>

    </div>
  </section>

  <!-- 3. COMFORT METRICS SECTION (INFOGRAPHIC) -->
  <section class="guide-metrics-section">
    <div class="container">
      <div class="metrics-header">
        <span class="metrics-eyebrow">The Proof in Comfort</span>
        <h2 class="metrics-title">Dermatological Standards. Proven Results.</h2>
      </div>
      <div class="metrics-grid">

        <!-- Metric 1 -->
        <div class="metric-card">
          <div class="metric-icon"><i class="ri-time-line"></i></div>
          <span class="metric-number" data-target="12" data-hours="true">0h</span>
          <h4 class="metric-label">All-Night Dryness</h4>
          <p class="metric-desc">Continuous locks to prevent middle-of-the-night moisture disruptions.</p>
        </div>

        <!-- Metric 2 -->
        <div class="metric-card">
          <div class="metric-icon"><i class="ri-shield-check-line"></i></div>
          <span class="metric-number" data-target="0" data-percent="true">100%</span>
          <h4 class="metric-label">Toxic Residue</h4>
          <p class="metric-desc">Absolutely zero chlorine bleach, fragrances, phthalates, or parabens.</p>
        </div>

        <!-- Metric 3 -->
        <div class="metric-card">
          <div class="metric-icon"><i class="ri-heart-line"></i></div>
          <span class="metric-number" data-target="10000" data-plus="true">0+</span>
          <h4 class="metric-label">Happy Families</h4>
          <p class="metric-desc">Trusted by Indian parents for eczema-prone baby skin health.</p>
        </div>

        <!-- Metric 4 -->
        <div class="metric-card">
          <div class="metric-icon"><i class="ri-star-line"></i></div>
          <span class="metric-number" data-target="4.9" data-star="true" data-decimals="1">0.0</span>
          <h4 class="metric-label">Dermatological Rating</h4>
          <p class="metric-desc">Tested by independent labs and certified clean-care standard.</p>
        </div>

      </div>
    </div>
  </section>

  <!-- 4. PINNED VISUAL STORY SECTION (APPLE-STYLE LAYER STORY) -->
  <section class="visual-story-section" id="visualStorySection">
    <div class="container visual-story-grid">

      <!-- Left: Sticky Diaper Image Layering -->
      <div class="visual-story-sticky">
        <div class="sticky-media-inner">
          <div class="visual-story-image active" data-story="1">
            <img src="https://images.unsplash.com/photo-1544816155-12df9643f363?w=800&auto=format&fit=crop&q=80" alt="Topsheet Layer">
            <span class="media-caption">01 / Cottony Cloud Topsheet</span>
          </div>
          <div class="visual-story-image" data-story="2">
            <img src="https://images.unsplash.com/photo-1502086223501-7ea6ecd79368?w=800&auto=format&fit=crop&q=80" alt="Absorbent Core">
            <span class="media-caption">02 / TCF wood pulp + SAP core</span>
          </div>
          <div class="visual-story-image" data-story="3">
            <img src="https://images.unsplash.com/photo-1484981138541-3d074aa97716?w=800&auto=format&fit=crop&q=80" alt="Backsheet and Waistband">
            <span class="media-caption">03 / 3D waist band & breathable shell</span>
          </div>
        </div>
      </div>

      <!-- Right: Scrolling Explanatory Blocks -->
      <div class="visual-story-scrollable">

        <!-- Block 1 -->
        <div class="visual-story-block" data-story="1">
          <span class="story-badge">Layer One</span>
          <h3 class="story-title">Topsheet Comfort</h3>
          <p class="story-desc">
            Directly contacting baby's skin, our topsheet is crafted from plant-based PLA and organic cotton fibers. This ultra-porous layer draws liquid instantly away while maintaining dry softness next to baby's body.
          </p>
          <div class="story-specs">
            <span>Hypoallergenic</span>
            <span>Zero fragrances</span>
            <span>Anti-friction</span>
          </div>
        </div>

        <!-- Block 2 -->
        <div class="visual-story-block" data-story="2">
          <span class="story-badge">Layer Two</span>
          <h3 class="story-title">Advanced Core Tech</h3>
          <p class="story-desc">
            The heart of CloudCush. Standard chlorine bleach creates toxic dioxin traces. We use Totally Chlorine-Free wood pulp layered with premium Japanese SAP particles to bind moisture into gels, locking wetness down.
          </p>
          <div class="story-specs">
            <span>FSC Wood Pulp</span>
            <span>Oxygen Bleached</span>
            <span>12h Absorption</span>
          </div>
        </div>

        <!-- Block 3 -->
        <div class="visual-story-block" data-story="3">
          <span class="story-badge">Layer Three</span>
          <h3 class="story-title">Snug Waist & Air Shell</h3>
          <p class="story-desc">
            The outer wrapper is a micro-perforated breathing membrane that allows heat and sweat to exit. Combined with a 3D stretch elastic waistband and double-stitched leg cuffs, it creates a custom fit that moves with baby.
          </p>
          <div class="story-specs">
            <span>3D Elastic fit</span>
            <span>Micro-pores shell</span>
            <span>Leakproof cuff</span>
          </div>
        </div>

      </div>

    </div>
  </section>

  <!-- 5. EDITORIAL PEDIATRICIAN QUOTE BLOCK -->
  <section class="guide-quote-section">
    <div class="container">
      <div class="quote-box">
        <div class="quote-icon"><i class="ri-double-quotes-l"></i></div>
        <blockquote class="pediatrician-quote">
          "Newborn skin is 30% thinner than adult skin and lacks a fully developed barrier. Selecting Totally Chlorine-Free (TCF) materials and letting baby skin breathe is not a luxury option—it's the primary way to prevent friction-based diaper rashes."
        </blockquote>
        <div class="quote-author">
          <span class="author-name">Dr. Anjali Sen, MD</span>
          <span class="author-title">Consultant Pediatric Dermatologist, New Delhi</span>
        </div>
      </div>
    </div>
  </section>

  <!-- 6. EMOTIONAL FINAL CTA SECTION -->
  <section class="guide-cta-section">
    <div class="container">
      <div class="guide-cta-box">
        <span class="cta-eyebrow">Designed for peaceful nights</span>
        <h2 class="cta-title">Softness that lasts, safety you can trust.</h2>
        <p class="cta-desc">
          Begin your baby's comfort journey with our diaper options. Experience the premium CloudCush difference today.
        </p>
        <div class="cta-actions">
          <a href="products.php" class="btn-pill-white">Explore Diapers</a>
          <a href="about.php" class="btn-pill-outline-white">Why CloudCush</a>
        </div>
      </div>
    </div>
  </section>

</main>

<?php include 'includes/footer.php'; ?>
