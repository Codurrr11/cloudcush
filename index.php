<?php include 'includes/head.php'; ?>

<?php include 'includes/header.php'; ?>

<main>
    <section class="hero">

        <!-- Oversized Editorial Title -->
        <div class="hero-title-container">
            <h1 class="hero-title">Softness That Breathes.</h1>
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
                    Soft. Dry. Clean.<br>
                    Pure Comfort.
                </div>
            </div>

            <!-- Center Column Content: Baby Image layers -->
            <div class="hero-col hero-col-center">
                <div class="baby-image-wrapper">
                    <!-- Foreground main baby layers -->
                    <img class="baby-main baby-main-layer active" data-index="1" src="assets/images/baby-hero.png" alt="CloudCush Sitting Baby" loading="eager">
                    <img class="baby-main baby-main-layer" data-index="2" src="assets/images/baby-hero-2.png" alt="CloudCush Sitting Baby" loading="lazy">
                    <img class="baby-main baby-main-layer" data-index="3" src="assets/images/baby-hero-3.png" alt="CloudCush Sitting Baby" loading="lazy">

                    <!-- Background double exposure ghost baby layers -->
                    <img class="baby-ghost baby-ghost-layer active" data-index="1" src="assets/images/baby-hero.png" alt="" aria-hidden="true">
                    <img class="baby-ghost baby-ghost-layer" data-index="2" src="assets/images/baby-hero-2.png" alt="" aria-hidden="true" loading="lazy">
                    <img class="baby-ghost baby-ghost-layer" data-index="3" src="assets/images/baby-hero-3.png" alt="" aria-hidden="true" loading="lazy">
                </div>
            </div>

            <!-- Right Column Content: Info details & CTA -->
            <div class="hero-col hero-col-right">
                <div class="hero-right-text">
                    Pediatrician-approved TCF.<br>
                    Certified safe for newborn skin.
                </div>
                <a href="javascript:void(0);" class="btn-pill">Explore Collection</a>
            </div>

        </div>
    </section>

    <!-- Showcase Section: Pinned Overlap and 360 Rotating Diaper Showcase -->
    <section class="showcase-section">
        <div class="container showcase-grid">

            <!-- Left Column: Editorial Content -->
            <div class="showcase-col showcase-col-left">
                <h2 class="showcase-title">The Softest<br>Diaper Ever.</h2>

                <div class="feature-badge">
                    <div class="feature-icon-wrapper">
                        <svg class="cloud-icon" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="50" cy="50" r="44" stroke="currentColor" stroke-width="2.5" fill="none" />
                            <path d="M34,56 A 7,7 0 0,1 34,42 A 10,10 0 0,1 52,34 A 8,8 0 0,1 66,44 A 6,6 0 0,1 64,56 Z"
                                stroke="currentColor" stroke-width="2.5" stroke-linejoin="round" stroke-linecap="round" fill="none" />
                        </svg>
                    </div>
                    <span class="feature-label">Like Soft Clouds</span>
                </div>

                <div class="showcase-desc-wrapper">
                    <p class="showcase-desc">Not just soft. Finer than silk material engineered to be weightless.</p>
                    <p class="showcase-desc">Softness that breathes, inside and out.</p>
                </div>

                <a href="javascript:void(0);" class="btn-pill">Discover More</a>
            </div>

            <!-- Right Column: 360 Rotating Diaper Video Showcase -->
            <div class="showcase-col showcase-col-right">
                <div class="diaper-container">
                    <video class="diaper-video" autoplay loop muted playsinline>
                        <source src="assets/video/diaper-video.mp4" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                </div>
            </div>

        </div>
    </section>

    <!-- Sizing Atelier Section (Premium Sizing & Sensation Atelier) -->
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
                <span class="atelier-meta">02 / SIZING &amp; SENSATION</span>
                <h2 class="atelier-title">Tailored to their stage, <br><em>crafted for their skin.</em></h2>
                <p class="atelier-desc">
                    Every milestone demands unique care. Map your baby’s stage or weight to discover our custom organic diaper variants, designed to protect skin health from first crawls to deep nights.
                </p>
            </div>

            <div class="atelier-canvas">

                <!-- Left: Floating Product Showcase with Glowing Backdrop -->
                <div class="atelier-showcase-col">
                    <div class="atelier-glow-backdrop"></div>
                    <div class="atelier-diaper-viewport">

                        <!-- Newborn Variant Visuals -->
                        <div class="atelier-variant active" data-variant="newborn">
                            <img src="assets/images/home-swiper-diaper-compress.webp_v=1778306352.png" alt="CloudCush TinyHug Newborn" class="atelier-img">

                            <!-- Floating Editorial Callouts -->
                            <div class="atelier-tag tag-top-left">
                                <span class="tag-dot"></span>
                                <div class="tag-content">
                                    <span class="tag-title">Umbilical Care Cutout</span>
                                    <span class="tag-desc">Contours around the belly button for friction-free newborn healing.</span>
                                </div>
                            </div>
                            <div class="atelier-tag tag-bottom-right">
                                <span class="tag-dot"></span>
                                <div class="tag-content">
                                    <span class="tag-title">Organic Topsheet</span>
                                    <span class="tag-desc">Dermatologically tested plant-based liner, gentle as a mother’s touch.</span>
                                </div>
                            </div>
                        </div>

                        <!-- Active Fit Variant Visuals -->
                        <div class="atelier-variant" data-variant="activefit">
                            <img src="assets/images/home-swiper-nursery-compress.webp_v=1778306352.png" alt="CloudCush FlexFit Active" class="atelier-img">

                            <!-- Floating Editorial Callouts -->
                            <div class="atelier-tag tag-top-right">
                                <span class="tag-dot"></span>
                                <div class="tag-content">
                                    <span class="tag-title">360° Comfort Stretch</span>
                                    <span class="tag-desc">High-elastic waistband that moves with active crawls, leaving zero marks.</span>
                                </div>
                            </div>
                            <div class="atelier-tag tag-bottom-left">
                                <span class="tag-dot"></span>
                                <div class="tag-content">
                                    <span class="tag-title">3D Leak Protection</span>
                                    <span class="tag-desc">Double-layer side hydrophobic barriers to contain all high-motion play.</span>
                                </div>
                            </div>
                        </div>

                        <!-- Overnight Variant Visuals -->
                        <div class="atelier-variant" data-variant="overnight">
                            <img src="assets/images/home-swiper-outing-accessories-compress.webp_v=1778306352.png" alt="CloudCush Overnight Protection" class="atelier-img">

                            <!-- Floating Editorial Callouts -->
                            <div class="atelier-tag tag-center-left">
                                <span class="tag-dot"></span>
                                <div class="tag-content">
                                    <span class="tag-title">12-Hour Dry Lock</span>
                                    <span class="tag-desc">Japanese SAP core that absorbs 10x its weight for uninterrupted sleep.</span>
                                </div>
                            </div>
                            <div class="atelier-tag tag-bottom-right">
                                <span class="tag-dot"></span>
                                <div class="tag-content">
                                    <span class="tag-title">Airflow Micropores</span>
                                    <span class="tag-desc">Perforated outer backsheet that expels heat and humidity instantly.</span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Right: Sizing Engine Column -->
                <div class="atelier-engine-col">

                    <!-- Watermark Watermark background name -->
                    <div class="atelier-watermark-wrap">
                        <span class="atelier-watermark" id="recWatermark">TINYHUG</span>
                    </div>

                    <!-- Stage Selector Tabs (Awwwards Style) -->
                    <div class="atelier-stages">
                        <button class="atelier-stage-btn active" data-tab="newborn">
                            <span class="btn-num">01</span>
                            <span class="btn-text">Newborn Care</span>
                        </button>
                        <button class="atelier-stage-btn" data-tab="activefit">
                            <span class="btn-num">02</span>
                            <span class="btn-text">Active Fit</span>
                        </button>
                        <button class="atelier-stage-btn" data-tab="overnight">
                            <span class="btn-num">03</span>
                            <span class="btn-text">Overnight</span>
                        </button>
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
   GROUND-UP REBUILD

   DOM STRUCTURE (critical):
     .stack-stage
       .stack-panels          ← contains ONLY cards 1–4; shifts RIGHT in Phase 3
         .sp-1 .sp-2 .sp-3 .sp-4
       .sp-5                  ← SIBLING of .stack-panels, enters from LEFT
       .stack-left-content    ← SIBLING of .stack-panels, slides in last
   ========================================================================== -->

    <section class="stack-section" id="stackSection">

        <div class="stack-stage" id="stackStage">

            <!-- Cards 1–4: rise from bottom, then cluster shifts right -->
            <div class="stack-panels" id="stackPanels">

                <div class="stack-panel sp-1" id="stackPanel1">
                    <div class="stack-panel-img-wrap">
                        <img class="stack-panel-img" src="assets/images/home-subscribe-1-compress.jpeg" alt="CloudCush baby comfort" loading="eager">
                    </div>
                </div>

                <div class="stack-panel sp-2" id="stackPanel2">
                    <div class="stack-panel-img-wrap">
                        <img class="stack-panel-img" src="assets/images/home-subscribe-2-compress.jpeg" alt="CloudCush baby lying down" loading="eager">
                    </div>
                </div>

                <div class="stack-panel sp-3" id="stackPanel3">
                    <div class="stack-panel-img-wrap">
                        <img class="stack-panel-img" src="assets/images/home-subscribe-3-compress.jpeg" alt="CloudCush baby crawling" loading="eager">
                    </div>
                </div>

                <div class="stack-panel sp-4" id="stackPanel4">
                    <div class="stack-panel-img-wrap">
                        <img class="stack-panel-img" src="assets/images/home-subscribe-4-compress.jpeg" alt="CloudCush toddler standing" loading="eager">
                    </div>
                </div>

            </div><!-- /.stack-panels -->

            <!-- Panel 5: SIBLING of .stack-panels, NOT inside it.
         right:0 in CSS. GSAP starts at x = -VW, slides to x = 0.
         Must be outside .stack-panels so it moves independently. -->
            <div class="stack-panel sp-5" id="stackPanel5">
                <div class="stack-panel-img-wrap">
                    <img class="stack-panel-img" src="assets/images/home-BabyCare_Plan_for_Diaper.png" alt="CloudCush Care Plan" loading="eager">
                </div>
            </div>

            <!-- LEFT EDITORIAL CONTENT — SIBLING of .stack-panels -->
            <!-- Slides in from left ONLY after Panel 5 locks. -->
            <div class="stack-left-content" id="stackLeftContent">
                <div class="stack-left-inner">

                    <h2 class="stack-main-title">
                        CloudCush Care Plan<br>
                        for Diapers
                    </h2>

                    <p class="stack-main-desc">
                        Starting a CloudCush AirSoft Diapers subscription opens care that
                        integrates into the rhythm of daily life.
                    </p>

                    <div class="stack-divider"></div>

                    <div class="stack-perks-grid">

                        <div class="stack-perk" id="stackPerk1">
                            <div class="stack-perk-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z" />
                                    <line x1="7" y1="7" x2="7.01" y2="7" />
                                </svg>
                            </div>
                            <span class="stack-perk-label">15% Subscription Savings</span>
                        </div>

                        <div class="stack-perk" id="stackPerk2">
                            <div class="stack-perk-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z" />
                                </svg>
                            </div>
                            <span class="stack-perk-label">Anytime WhatsApp Control</span>
                        </div>

                        <div class="stack-perk" id="stackPerk3">
                            <div class="stack-perk-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 12 20 22 4 22 4 12" />
                                    <rect x="2" y="7" width="20" height="5" />
                                    <line x1="12" y1="22" x2="12" y2="7" />
                                    <path d="M12 7H7.5a2.5 2.5 0 010-5C11 2 12 7 12 7z" />
                                    <path d="M12 7h4.5a2.5 2.5 0 000-5C13 2 12 7 12 7z" />
                                </svg>
                            </div>
                            <span class="stack-perk-label">Milestone Gifts</span>
                        </div>

                        <div class="stack-perk" id="stackPerk4">
                            <div class="stack-perk-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="8" r="6" />
                                    <path d="M15.477 12.89L17 22l-5-3-5 3 1.523-9.11" />
                                </svg>
                            </div>
                            <span class="stack-perk-label">Earn Care Points</span>
                        </div>

                    </div><!-- /.stack-perks-grid -->

                    <a href="javascript:void(0);" class="btn-pill stack-plan-cta" id="stackCta">Explore the Plan</a>

                </div><!-- /.stack-left-inner -->
            </div><!-- /.stack-left-content -->

        </div><!-- /.stack-stage -->

    </section>


    <!-- ==========================================================================
         CATEGORY NAV SECTION — Pinned scroll with 5 tabs (Diaper → Play)
         LEFT: vertical category list, one active at a time
         RIGHT: editorial image + content panel per category
         GSAP ScrollTrigger pinned scrub timeline
         ========================================================================== -->
    <section class="catnav-section" id="catnavSection">
        <div class="catnav-stage" id="catnavStage">

            <!-- Left: Editorial nav list with progress tracks -->
            <div class="catnav-left" id="catnavLeft">
                <div class="catnav-nav-header">
                    <span class="catnav-section-eyebrow">Variant Showcase</span>
                    <h2 class="catnav-section-title">The Diaper Experience</h2>
                </div>
                <nav class="catnav-list" aria-label="Product categories">
                    <button class="catnav-item active" data-index="0" aria-label="Newborn Care">
                        <span class="catnav-item-num">01</span>
                        <span class="catnav-item-label">Newborn Care</span>
                        <div class="catnav-item-progress">
                            <div class="catnav-item-progress-bar"></div>
                        </div>
                    </button>
                    <button class="catnav-item" data-index="1" aria-label="Active Fit">
                        <span class="catnav-item-num">02</span>
                        <span class="catnav-item-label">Active Fit</span>
                        <div class="catnav-item-progress">
                            <div class="catnav-item-progress-bar"></div>
                        </div>
                    </button>
                    <button class="catnav-item" data-index="2" aria-label="Overnight Protection">
                        <span class="catnav-item-num">03</span>
                        <span class="catnav-item-label">Overnight Protection</span>
                        <div class="catnav-item-progress">
                            <div class="catnav-item-progress-bar"></div>
                        </div>
                    </button>
                    <button class="catnav-item" data-index="3" aria-label="Sensitive Skin">
                        <span class="catnav-item-num">04</span>
                        <span class="catnav-item-label">Sensitive Skin</span>
                        <div class="catnav-item-progress">
                            <div class="catnav-item-progress-bar"></div>
                        </div>
                    </button>
                    <button class="catnav-item" data-index="4" aria-label="Toddler Comfort">
                        <span class="catnav-item-num">05</span>
                        <span class="catnav-item-label">Toddler Comfort</span>
                        <div class="catnav-item-progress">
                            <div class="catnav-item-progress-bar"></div>
                        </div>
                    </button>
                </nav>
            </div>

            <!-- Right: Immersive media viewport and details -->
            <div class="catnav-right" id="catnavRight">

                <!-- Panel 1: Newborn -->
                <div class="catnav-panel active" id="catnavPanel0" data-index="0">
                    <div class="catnav-panel-media">
                        <img src="assets/images/home-swiper-diaper-compress.webp_v=1778306352.png" alt="CloudCush TinyHug Newborn Diapers" class="catnav-img" loading="eager">
                        <div class="catnav-media-overlay"></div>
                    </div>
                    <div class="catnav-panel-content">
                        <span class="catnav-panel-eyebrow">01 &mdash; First Touch</span>
                        <h3 class="catnav-panel-title">CloudCush TinyHug</h3>
                        <p class="catnav-panel-desc">Feather-soft, ultra-breathable protection custom tailored for your baby's delicate first months. Engineered to prevent rashes from day one.</p>
                        <a href="product-details.php" class="catnav-panel-cta">Shop TinyHug</a>
                    </div>
                </div>

                <!-- Panel 2: Active Fit -->
                <div class="catnav-panel" id="catnavPanel1" data-index="1">
                    <div class="catnav-panel-media">
                        <img src="assets/images/home-swiper-nursery-compress.webp_v=1778306352.png" alt="CloudCush FlexFit Active Baby Diapers" class="catnav-img" loading="lazy">
                        <div class="catnav-media-overlay"></div>
                    </div>
                    <div class="catnav-panel-content">
                        <span class="catnav-panel-eyebrow">02 &mdash; Endless Motion</span>
                        <h3 class="catnav-panel-title">CloudCush FlexFit</h3>
                        <p class="catnav-panel-desc">Stretchy 360-degree waistbands that move with your baby as they roll, crawl, and explore. Leak-proof comfort that stays secure.</p>
                        <a href="product-details.php" class="catnav-panel-cta">Shop FlexFit</a>
                    </div>
                </div>

                <!-- Panel 3: Overnight Protection -->
                <div class="catnav-panel" id="catnavPanel2" data-index="2">
                    <div class="catnav-panel-media">
                        <img src="assets/images/home-swiper-outing-accessories-compress.webp_v=1778306352.png" alt="CloudCush Overnight Protection Diapers" class="catnav-img" loading="lazy">
                        <div class="catnav-media-overlay"></div>
                    </div>
                    <div class="catnav-panel-content">
                        <span class="catnav-panel-eyebrow">03 &mdash; Peaceful Sleep</span>
                        <h3 class="catnav-panel-title">CloudCush Overnight+</h3>
                        <p class="catnav-panel-desc">Advanced absorbent locks for 12-hour dry protection. Keeps wetness away so your baby sleeps through the night completely rash-free.</p>
                        <a href="product-details.php" class="catnav-panel-cta">Shop Overnight+</a>
                    </div>
                </div>

                <!-- Panel 4: Sensitive Skin -->
                <div class="catnav-panel" id="catnavPanel3" data-index="3">
                    <div class="catnav-panel-media">
                        <img src="assets/images/home-swiper-play-compress.webp_v=1778306353.png" alt="CloudCush GentleCare Sensitive Skin Diapers" class="catnav-img" loading="lazy">
                        <div class="catnav-media-overlay"></div>
                    </div>
                    <div class="catnav-panel-content">
                        <span class="catnav-panel-eyebrow">04 &mdash; Skin Integrity</span>
                        <h3 class="catnav-panel-title">CloudCush GentleCare</h3>
                        <p class="catnav-panel-desc">Hypoallergenic organic cotton top sheet designed to soothe. Dermatologist-tested, totally chlorine-free protection for hypersensitive skin.</p>
                        <a href="product-details.php" class="catnav-panel-cta">Shop GentleCare</a>
                    </div>
                </div>

                <!-- Panel 5: Toddler Comfort -->
                <div class="catnav-panel" id="catnavPanel4" data-index="4">
                    <div class="catnav-panel-media">
                        <img src="assets/images/home-swiper-Travel_Gear.png_v=1775026754.png" alt="CloudCush PureComfort Toddler Diapers" class="catnav-img" loading="lazy">
                        <div class="catnav-media-overlay"></div>
                    </div>
                    <div class="catnav-panel-content">
                        <span class="catnav-panel-eyebrow">05 &mdash; Active Growth</span>
                        <h3 class="catnav-panel-title">PureComfort Airflow</h3>
                        <p class="catnav-panel-desc">Maximum airflow comfort designed for active toddlers. Super thin yet ultra-absorbent, making play days worry-free and light.</p>
                        <a href="product-details.php" class="catnav-panel-cta">Shop PureComfort</a>
                    </div>
                </div>

            </div>

        </div>
    </section>

    <!-- =========================================================
         CORE COLLECTION — Premium Carousel
         Layout: fixed-height cards, image expands to fill on hover
         Badge moves to info block (below image, matching reference)
         CTA is absolute overlay revealed by GSAP on hover
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

            <!-- Carousel Viewport (overflow hidden, drag cursor) -->
            <div class="cc-viewport" id="ccViewport">
                <div class="cc-track" id="ccTrack">

                    <!-- Card 1: Diaper ─────────────────────────────────── -->
                    <div class="collection-card" data-cc-index="0">
                        <a href="product-details.php" class="collection-card-link-wrapper">
                            <!-- Image: fills top 58%, expands to 100% on hover via GSAP -->
                            <div class="collection-image-wrap">
                                <img src="assets/images/home-swiper-diaper-compress.webp_v=1778306352.png"
                                    alt="CloudCush AirSoft" class="collection-image" loading="lazy">
                            </div>

                            <!-- Info block: badge (below image) + rating + title + desc + price -->
                            <div class="collection-info">
                                <div class="collection-badge">
                                    <svg class="oeko-badge-svg" viewBox="0 0 52 52" xmlns="http://www.w3.org/2000/svg" aria-label="OEKO-TEX Standard 100">
                                        <circle cx="26" cy="26" r="25" fill="#17a697" />
                                        <text x="26" y="20" text-anchor="middle" font-family="Space Grotesk,sans-serif" font-size="7" font-weight="700" fill="#fff" letter-spacing="0.5">OEKO</text>
                                        <text x="26" y="29" text-anchor="middle" font-family="Space Grotesk,sans-serif" font-size="5.5" font-weight="400" fill="rgba(255,255,255,0.85)" letter-spacing="0.3">TEX&#174;</text>
                                        <line x1="10" y1="32" x2="42" y2="32" stroke="rgba(255,255,255,0.4)" stroke-width="0.8" />
                                        <text x="26" y="39" text-anchor="middle" font-family="Space Grotesk,sans-serif" font-size="4.5" font-weight="500" fill="rgba(255,255,255,0.75)" letter-spacing="0.5">STANDARD</text>
                                        <text x="26" y="45.5" text-anchor="middle" font-family="Space Grotesk,sans-serif" font-size="4.5" font-weight="500" fill="rgba(255,255,255,0.75)" letter-spacing="0.5">100</text>
                                    </svg>
                                </div>
                                <div class="collection-rating">
                                    <div class="stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
                                    <span class="review-count">(42)</span>
                                </div>
                                <h3 class="collection-product-title">CloudCush AirSoft</h3>
                                <p class="collection-desc">Feather-soft protection for newborns and active babies.</p>
                                <p class="collection-price">₹899</p>
                            </div>
                        </a>

                        <!-- CTA: absolute overlay at card bottom, revealed on hover -->
                        <div class="collection-cta-wrap">
                            <a href="product-details.php" class="collection-cta-btn">View Product</a>
                        </div>
                    </div>

                    <!-- Card 2: Wipes ──────────────────────────────────── -->
                    <div class="collection-card" data-cc-index="1">
                        <a href="product-details.php" class="collection-card-link-wrapper">
                            <div class="collection-image-wrap">
                                <img src="assets/images/home-swiper-wipes-compress.webp_v=1778306354.png"
                                    alt="CloudCush GentleCare" class="collection-image" loading="lazy">
                            </div>
                            <div class="collection-info">
                                <div class="collection-badge">
                                    <svg class="oeko-badge-svg" viewBox="0 0 52 52" xmlns="http://www.w3.org/2000/svg" aria-label="OEKO-TEX Standard 100">
                                        <circle cx="26" cy="26" r="25" fill="#17a697" />
                                        <text x="26" y="20" text-anchor="middle" font-family="Space Grotesk,sans-serif" font-size="7" font-weight="700" fill="#fff" letter-spacing="0.5">OEKO</text>
                                        <text x="26" y="29" text-anchor="middle" font-family="Space Grotesk,sans-serif" font-size="5.5" font-weight="400" fill="rgba(255,255,255,0.85)" letter-spacing="0.3">TEX&#174;</text>
                                        <line x1="10" y1="32" x2="42" y2="32" stroke="rgba(255,255,255,0.4)" stroke-width="0.8" />
                                        <text x="26" y="39" text-anchor="middle" font-family="Space Grotesk,sans-serif" font-size="4.5" font-weight="500" fill="rgba(255,255,255,0.75)" letter-spacing="0.5">STANDARD</text>
                                        <text x="26" y="45.5" text-anchor="middle" font-family="Space Grotesk,sans-serif" font-size="4.5" font-weight="500" fill="rgba(255,255,255,0.75)" letter-spacing="0.5">100</text>
                                    </svg>
                                </div>
                                <div class="collection-rating">
                                    <div class="stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
                                    <span class="review-count">(11)</span>
                                </div>
                                <h3 class="collection-product-title">CloudCush GentleCare</h3>
                                <p class="collection-desc">Hypoallergenic organic diaper for rash-free days.</p>
                                <p class="collection-price">₹599</p>
                            </div>
                        </a>
                        <div class="collection-cta-wrap">
                            <a href="product-details.php" class="collection-cta-btn">View Product</a>
                        </div>
                    </div>

                    <!-- Card 3: Carrier ────────────────────────────────── -->
                    <div class="collection-card" data-cc-index="2">
                        <a href="product-details.php" class="collection-card-link-wrapper">
                            <div class="collection-image-wrap">
                                <img src="assets/images/home-swiper-Travel_Gear.png_v=1775026754.png"
                                    alt="CloudCush Overnight+" class="collection-image" loading="lazy">
                            </div>
                            <div class="collection-info">
                                <div class="collection-badge">
                                    <svg class="oeko-badge-svg" viewBox="0 0 52 52" xmlns="http://www.w3.org/2000/svg" aria-label="OEKO-TEX Standard 100">
                                        <circle cx="26" cy="26" r="25" fill="#17a697" />
                                        <text x="26" y="20" text-anchor="middle" font-family="Space Grotesk,sans-serif" font-size="7" font-weight="700" fill="#fff" letter-spacing="0.5">OEKO</text>
                                        <text x="26" y="29" text-anchor="middle" font-family="Space Grotesk,sans-serif" font-size="5.5" font-weight="400" fill="rgba(255,255,255,0.85)" letter-spacing="0.3">TEX&#174;</text>
                                        <line x1="10" y1="32" x2="42" y2="32" stroke="rgba(255,255,255,0.4)" stroke-width="0.8" />
                                        <text x="26" y="39" text-anchor="middle" font-family="Space Grotesk,sans-serif" font-size="4.5" font-weight="500" fill="rgba(255,255,255,0.75)" letter-spacing="0.5">STANDARD</text>
                                        <text x="26" y="45.5" text-anchor="middle" font-family="Space Grotesk,sans-serif" font-size="4.5" font-weight="500" fill="rgba(255,255,255,0.75)" letter-spacing="0.5">100</text>
                                    </svg>
                                </div>
                                <div class="collection-rating">
                                    <div class="stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
                                    <span class="review-count">(76)</span>
                                </div>
                                <h3 class="collection-product-title">CloudCush Overnight+</h3>
                                <p class="collection-desc">12-hour leak-proof dryness for peaceful sleep nights.</p>
                                <p class="collection-price">₹1,299</p>
                            </div>
                        </a>
                        <div class="collection-cta-wrap">
                            <a href="product-details.php" class="collection-cta-btn">View Product</a>
                        </div>
                    </div>

                    <!-- Card 4: Nursery ────────────────────────────────── -->
                    <div class="collection-card" data-cc-index="3">
                        <a href="product-details.php" class="collection-card-link-wrapper">
                            <div class="collection-image-wrap">
                                <img src="assets/images/home-swiper-nursery-compress.webp_v=1778306352.png"
                                    alt="CloudCush DryEase" class="collection-image" loading="lazy">
                            </div>
                            <div class="collection-info">
                                <div class="collection-badge">
                                    <svg class="oeko-badge-svg" viewBox="0 0 52 52" xmlns="http://www.w3.org/2000/svg" aria-label="OEKO-TEX Standard 100">
                                        <circle cx="26" cy="26" r="25" fill="#17a697" />
                                        <text x="26" y="20" text-anchor="middle" font-family="Space Grotesk,sans-serif" font-size="7" font-weight="700" fill="#fff" letter-spacing="0.5">OEKO</text>
                                        <text x="26" y="29" text-anchor="middle" font-family="Space Grotesk,sans-serif" font-size="5.5" font-weight="400" fill="rgba(255,255,255,0.85)" letter-spacing="0.3">TEX&#174;</text>
                                        <line x1="10" y1="32" x2="42" y2="32" stroke="rgba(255,255,255,0.4)" stroke-width="0.8" />
                                        <text x="26" y="39" text-anchor="middle" font-family="Space Grotesk,sans-serif" font-size="4.5" font-weight="500" fill="rgba(255,255,255,0.75)" letter-spacing="0.5">STANDARD</text>
                                        <text x="26" y="45.5" text-anchor="middle" font-family="Space Grotesk,sans-serif" font-size="4.5" font-weight="500" fill="rgba(255,255,255,0.75)" letter-spacing="0.5">100</text>
                                    </svg>
                                </div>
                                <div class="collection-rating">
                                    <div class="stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
                                    <span class="review-count">(29)</span>
                                </div>
                                <h3 class="collection-product-title">CloudCush DryEase</h3>
                                <p class="collection-desc">Super thin airflow comfort tailored for active toddlers.</p>
                                <p class="collection-price">₹399</p>
                            </div>
                        </a>
                        <div class="collection-cta-wrap">
                            <a href="product-details.php" class="collection-cta-btn">View Product</a>
                        </div>
                    </div>

                </div><!-- /.cc-track -->
            </div><!-- /.cc-viewport -->

            <!-- Dot indicators -->
            <div class="cc-dots" id="ccDots" aria-label="Carousel position indicators">
                <button class="cc-dot active" data-cc-dot="0" aria-label="Go to slide 1"></button>
                <button class="cc-dot" data-cc-dot="1" aria-label="Go to slide 2"></button>
                <button class="cc-dot" data-cc-dot="2" aria-label="Go to slide 3"></button>
                <button class="cc-dot" data-cc-dot="3" aria-label="Go to slide 4"></button>
            </div>

        </div>
    </section>


    <!-- =========================================================
         PHILOSOPHY SECTION — Full-width cinematic storytelling
         Background: Unsplash placeholder (replace with final asset)
         Text: left-aligned, white, editorial luxury typography
         Animations: GSAP ScrollTrigger + parallax image via JS
         ========================================================= -->
    <section class="philosophy-section" id="philosophySection" aria-label="Our Philosophy">

        <!-- Parallax background image layer -->
        <div class="philosophy-bg" id="philosophyBg" aria-hidden="true">
            <img
                src="https://images.unsplash.com/photo-1697751946618-d04ad612cb78?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8OHx8YmFieSUyMGNhcmUlMjBkaWFwZXJ8ZW58MHwwfDB8fHwy"
                alt=""
                class="philosophy-bg-img"
                id="philosophyBgImg"
                loading="lazy"
                decoding="async">
        </div>

        <!-- Dark gradient overlay -->
        <div class="philosophy-overlay" id="philosophyOverlay" aria-hidden="true"></div>

        <!-- Content panel -->
        <div class="philosophy-content" id="philosophyContent">

            <!-- Panel 1: The Philosophy -->
            <div class="philosophy-panel" id="philPanel1">
                <!-- Top eyebrow label + divider -->
                <div class="philosophy-eyebrow-wrap" id="philEyebrow">
                    <span class="philosophy-eyebrow">THE PHILOSOPHY</span>
                    <div class="philosophy-divider" aria-hidden="true"></div>
                </div>

                <!-- Main editorial text block -->
                <div class="philosophy-body" id="philBody">
                    <p class="philosophy-text">
                        <strong class="philosophy-bold">Comfort, Made Simple.</strong>
                        Parenthood is made of little moments. At CloudCush, we believe comfort should never be complicated. That&#x2019;s why every diaper is thoughtfully designed for softness, protection, and everyday peace of mind. Crafted in Kota, Rajasthan, we bring premium, skin-friendly protection to modern Indian families.
                    </p>
                </div>

                <!-- CTA link -->
                <div class="philosophy-cta-wrap" id="philCta">
                    <a href="javascript:void(0);" class="philosophy-cta" aria-label="View our philosophy">
                        View Philosophy
                    </a>
                </div>
            </div>

            <!-- Panel 2: The Standards -->
            <div class="philosophy-panel" id="philPanel2">
                <!-- Top eyebrow label + divider -->
                <div class="philosophy-eyebrow-wrap">
                    <span class="philosophy-eyebrow">THE STANDARDS</span>
                    <div class="philosophy-divider" aria-hidden="true"></div>
                </div>

                <!-- Main editorial text block -->
                <div class="philosophy-body">
                    <p class="philosophy-text">
                        We prioritize absolute diaper integrity. Every CloudCush diaper is crafted using certified non-toxic, hypoallergenic, and cotton-soft materials. Our commitment ensures 12-hour dryness and rash-free protection for your baby&#x2019;s delicate skin.
                    </p>
                </div>

                <!-- CTA link -->
                <div class="philosophy-cta-wrap">
                    <a href="javascript:void(0);" class="philosophy-cta" aria-label="View our standards">
                        View Standards
                    </a>
                </div>
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

                    <!-- Card 1: Video Testimonial -->
                    <div class="mom-card mom-card-video" data-index="0">
                        <div class="mom-media-wrap">
                            <video class="mom-video" autoplay loop muted playsinline loading="lazy">
                                <source src="https://assets.mixkit.co/videos/preview/mixkit-mother-holding-her-baby-cuddled-in-a-white-blanket-41487-large.mp4" type="video/mp4">
                            </video>
                            <div class="mom-media-overlay"></div>
                        </div>
                        <div class="mom-card-content">
                            <div class="mom-rating">
                                <span class="mom-stars">★★★★★</span>
                            </div>
                            <p class="mom-quote">"The softest diaper ever! My baby slept 12 hours straight without leaks."</p>
                            <div class="mom-author">
                                <span class="mom-name">Aanya S.</span>
                                <span class="mom-role">Verified Parent • Bengaluru • AirSoft Diaper</span>
                            </div>
                        </div>
                    </div>

                    <!-- Card 2: Image Testimonial -->
                    <div class="mom-card mom-card-image" data-index="1">
                        <div class="mom-media-wrap">
                            <img src="https://images.unsplash.com/photo-1660757731651-4be68ea4c9d1?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8Nzh8fHJldmlldyUyMHBhcmVudHN8ZW58MHwxfDB8fHww" alt="Priya and Baby" class="mom-img" loading="lazy">
                            <div class="mom-media-overlay"></div>
                        </div>
                        <div class="mom-card-content">
                            <div class="mom-rating">
                                <span class="mom-stars">★★★★★</span>
                            </div>
                            <p class="mom-quote">"Unbelievably gentle on sensitive skin. Zero redness or diaper rash since switching."</p>
                            <div class="mom-author">
                                <span class="mom-name">Priya M.</span>
                                <span class="mom-role">Verified Mom • Jaipur • GentleCare Diapers</span>
                            </div>
                        </div>
                    </div>

                    <!-- Card 3: Text-Only Testimonial (Elegant style) -->
                    <div class="mom-card mom-card-text" data-index="2">
                        <div class="mom-card-content">
                            <div class="mom-quote-icon">“</div>
                            <div class="mom-rating">
                                <span class="mom-stars">★★★★★</span>
                            </div>
                            <p class="mom-quote">"I was skeptical about the subscription but it's so convenient. Never having to run to the store at midnight is a lifesaver."</p>
                            <div class="mom-author">
                                <span class="mom-name">Aarav G.</span>
                                <span class="mom-role">Verified Dad • Delhi • AirSoft Diaper Subscription</span>
                            </div>
                        </div>
                    </div>

                    <!-- Card 4: Video Testimonial -->
                    <div class="mom-card mom-card-video" data-index="3">
                        <div class="mom-media-wrap">
                            <video class="mom-video" autoplay loop muted playsinline loading="lazy">
                                <source src="https://assets.mixkit.co/videos/preview/mixkit-close-up-of-a-newborn-baby-yawning-41481-large.mp4" type="video/mp4">
                            </video>
                            <div class="mom-media-overlay"></div>
                        </div>
                        <div class="mom-card-content">
                            <div class="mom-rating">
                                <span class="mom-stars">★★★★★</span>
                            </div>
                            <p class="mom-quote">"The flexible fit is real. Keeps my active baby comfortable all day long."</p>
                            <div class="mom-author">
                                <span class="mom-name">Meera K.</span>
                                <span class="mom-role">Verified Mom • Pune • FlexFit Active</span>
                            </div>
                        </div>
                    </div>

                    <!-- Card 5: Image Testimonial -->
                    <div class="mom-card mom-card-image" data-index="4">
                        <div class="mom-media-wrap">
                            <img src="https://images.unsplash.com/photo-1519689680058-324335c77eba?w=800&q=80" alt="Nursery Room" class="mom-img" loading="lazy">
                            <div class="mom-media-overlay"></div>
                        </div>
                        <div class="mom-card-content">
                            <div class="mom-rating">
                                <span class="mom-stars">★★★★★</span>
                            </div>
                            <p class="mom-quote">"Super soft, dry, and extremely breathable. Complete peace of mind for overnight sleep."</p>
                            <div class="mom-author">
                                <span class="mom-name">Kavya R.</span>
                                <span class="mom-role">Verified Parent • Kota • Overnight+ Diapers</span>
                            </div>
                        </div>
                    </div>

                    <!-- Card 6: Text-Only Testimonial -->
                    <div class="mom-card mom-card-text" data-index="5">
                        <div class="mom-card-content">
                            <div class="mom-quote-icon">“</div>
                            <div class="mom-rating">
                                <span class="mom-stars">★★★★★</span>
                            </div>
                            <p class="mom-quote">"CloudCush's customer support is amazing. They helped me adjust my diaper sizes seamlessly."</p>
                            <div class="mom-author">
                                <span class="mom-name">Neha V.</span>
                                <span class="mom-role">Verified Mom • Ahmedabad • Customer Care</span>
                            </div>
                        </div>
                    </div>
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

                    <!-- Blog Card 1 -->
                    <article class="blog-carousel-card">
                        <a href="blog-details.php" class="blog-card-anchor">
                            <div class="blog-card-media">
                                <img src="https://images.unsplash.com/photo-1519689680058-324335c77eba?w=800&auto=format&fit=crop&q=80" alt="The Science of Touch" class="blog-card-img" loading="lazy">
                                <div class="blog-card-overlay">
                                    <span class="blog-card-read-tag">Read Article <i class="ri-arrow-right-line"></i></span>
                                </div>
                            </div>
                            <div class="blog-card-details">
                                <div class="blog-card-meta">
                                    <span class="blog-card-category">Pediatric Care</span>
                                    <span class="blog-card-dot">&bull;</span>
                                    <span class="blog-card-read">5 Min Read</span>
                                </div>
                                <h3 class="blog-card-title">The Science of Touch: Skin Integrity in the Newborn Phase</h3>
                                <p class="blog-card-excerpt">How physical touch, cotton-like softness, and totally chlorine-free materials interact to shape early developmental pathways and prevent diaper rashes.</p>
                                <div class="blog-card-footer">
                                    <span class="blog-card-date">June 04, 2026</span>
                                    <span class="blog-card-cta">Read More <i class="ri-arrow-right-line"></i></span>
                                </div>
                            </div>
                        </a>
                    </article>

                    <!-- Blog Card 2 -->
                    <article class="blog-carousel-card">
                        <a href="blog-details.php" class="blog-card-anchor">
                            <div class="blog-card-media">
                                <img src="https://images.unsplash.com/photo-1544816155-12df9643f363?w=800&auto=format&fit=crop&q=80" alt="Cozy Sleep Routines" class="blog-card-img" loading="lazy">
                                <div class="blog-card-overlay">
                                    <span class="blog-card-read-tag">Read Story <i class="ri-arrow-right-line"></i></span>
                                </div>
                            </div>
                            <div class="blog-card-details">
                                <div class="blog-card-meta">
                                    <span class="blog-card-category">Sleep Guides</span>
                                    <span class="blog-card-dot">&bull;</span>
                                    <span class="blog-card-read">4 Min Read</span>
                                </div>
                                <h3 class="blog-card-title">Cozy Sleep: Structuring Your Baby's Night Routine</h3>
                                <p class="blog-card-excerpt">From room temperature to calming sensory cues, explore our dermatologically safe evening wind-down rituals.</p>
                                <div class="blog-card-footer">
                                    <span class="blog-card-date">June 02, 2026</span>
                                    <span class="blog-card-cta">Read More <i class="ri-arrow-right-line"></i></span>
                                </div>
                            </div>
                        </a>
                    </article>

                    <!-- Blog Card 3 -->
                    <article class="blog-carousel-card">
                        <a href="blog-details.php" class="blog-card-anchor">
                            <div class="blog-card-media">
                                <img src="https://images.unsplash.com/photo-1502086223501-7ea6ecd79368?w=800&auto=format&fit=crop&q=80" alt="Tackling Diaper Rash" class="blog-card-img" loading="lazy">
                                <div class="blog-card-overlay">
                                    <span class="blog-card-read-tag">Read Article <i class="ri-arrow-right-line"></i></span>
                                </div>
                            </div>
                            <div class="blog-card-details">
                                <div class="blog-card-meta">
                                    <span class="blog-card-category">Care Tips</span>
                                    <span class="blog-card-dot">&bull;</span>
                                    <span class="blog-card-read">5 Min Read</span>
                                </div>
                                <h3 class="blog-card-title">Tackling Diaper Rash: A Modern Parent's Checklist</h3>
                                <p class="blog-card-excerpt">Our simple dermatologist-backed guide to managing skin health during diaper changes and growth milestones.</p>
                                <div class="blog-card-footer">
                                    <span class="blog-card-date">May 28, 2026</span>
                                    <span class="blog-card-cta">Read More <i class="ri-arrow-right-line"></i></span>
                                </div>
                            </div>
                        </a>
                    </article>

                    <!-- Blog Card 4 -->
                    <article class="blog-carousel-card">
                        <a href="blog-details.php" class="blog-card-anchor">
                            <div class="blog-card-media">
                                <img src="https://images.unsplash.com/photo-1484981138541-3d074aa97716?w=600&auto=format&fit=crop&q=80" alt="TCF Wood Pulp" class="blog-card-img" loading="lazy">
                                <div class="blog-card-overlay">
                                    <span class="blog-card-read-tag">Read Article <i class="ri-arrow-right-line"></i></span>
                                </div>
                            </div>
                            <div class="blog-card-details">
                                <div class="blog-card-meta">
                                    <span class="blog-card-category">Product Insights</span>
                                    <span class="blog-card-dot">&bull;</span>
                                    <span class="blog-card-read">6 Min Read</span>
                                </div>
                                <h3 class="blog-card-title">The Totally Chlorine-Free (TCF) Difference</h3>
                                <p class="blog-card-excerpt">Why we rejected conventional bleach methods to protect delicate skin barriers and promote environmental health.</p>
                                <div class="blog-card-footer">
                                    <span class="blog-card-date">May 15, 2026</span>
                                    <span class="blog-card-cta">Read More <i class="ri-arrow-right-line"></i></span>
                                </div>
                            </div>
                        </a>
                    </article>

                </div>
            </div>

            <!-- Dot indicators -->
            <div class="blog-carousel-dots"></div>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?>
