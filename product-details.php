<?php include 'includes/head.php'; ?>

<?php include 'includes/header.php'; ?>

<main class="product-details">

  <!-- Product Hero Section -->
  <section class="product-hero">
    <div class="container product-hero-grid">

      <!-- Left: Product Gallery -->
      <div class="product-gallery-section">
        <div class="gallery-main">
          <img id="mainImage" class="main-image" src="https://images.unsplash.com/photo-1697751946618-d04ad612cb78?w=600&auto=format&fit=crop&q=80" alt="CloudCush Overnight+ Diaper" loading="eager">
        </div>

        <!-- Thumbnail Gallery -->
        <div class="gallery-thumbnails">
          <button class="thumb" data-src="https://images.unsplash.com/photo-1697751946618-d04ad612cb78?w=600&auto=format&fit=crop&q=80" aria-label="Gallery image 1" data-active="true">
            <img src="https://images.unsplash.com/photo-1697751946618-d04ad612cb78?w=100&auto=format&fit=crop&q=60" alt="">
          </button>
          <button class="thumb" data-src="https://images.unsplash.com/photo-1544816155-12df9643f363?w=600&auto=format&fit=crop&q=80" aria-label="Gallery image 2">
            <img src="https://images.unsplash.com/photo-1544816155-12df9643f363?w=100&auto=format&fit=crop&q=60" alt="">
          </button>
          <button class="thumb" data-src="https://images.unsplash.com/photo-1515488042361-404e9250afef?w=600&auto=format&fit=crop&q=80" aria-label="Gallery image 3">
            <img src="https://images.unsplash.com/photo-1515488042361-404e9250afef?w=100&auto=format&fit=crop&q=60" alt="">
          </button>
          <button class="thumb" data-src="https://images.unsplash.com/photo-1522850959076-3f4770c18413?w=600&auto=format&fit=crop&q=80" aria-label="Gallery image 4">
            <img src="https://images.unsplash.com/photo-1522850959076-3f4770c18413?w=100&auto=format&fit=crop&q=60" alt="">
          </button>
        </div>
      </div>

      <!-- Right: Product Information -->
      <div class="product-info-section">

        <!-- Breadcrumb -->
        <nav class="breadcrumb-product" aria-label="Breadcrumb">
          <a href="./">Home</a>
          <span>/</span>
          <a href="diaper.php">Diapers</a>
          <span>/</span>
          <span>CloudCush Overnight+</span>
        </nav>

        <!-- Title & Rating -->
        <h1 class="product-title">CloudCush Overnight+</h1>
        <p class="product-category">Premium Overnight Protection Diaper</p>

        <div class="product-rating-section">
          <div class="rating-stars"><span class="stars">★★★★★</span> (512 reviews)</div>
          <button class="btn-write-review">Write a Review</button>
        </div>

        <!-- Price & Availability -->
        <div class="price-section">
          <div class="price-display">
            <span class="price-current">₹599</span>
            <span class="price-original">₹799</span>
            <span class="price-discount">-25%</span>
          </div>
          <div class="availability">
            <span class="availability-status in-stock">✓ In Stock</span>
          </div>
        </div>

        <!-- Description -->
        <div class="description-block">
          <p class="description-text">Experience 12-hour dryness with CloudCush Overnight+. Engineered with premium absorbent technology, this diaper provides leak protection and breathable softness for peaceful nights. Perfect for active babies and overnight use.</p>
        </div>

        <!-- Size Selector -->
        <div class="selector-block">
          <label class="selector-label">Select Size</label>
          <div class="size-selector">
            <button class="size-btn" data-size="s">
              <span class="size-label">S</span>
              <span class="size-desc">3-6 kg</span>
            </button>
            <button class="size-btn" data-size="m" data-active="true">
              <span class="size-label">M</span>
              <span class="size-desc">6-11 kg</span>
            </button>
            <button class="size-btn" data-size="l">
              <span class="size-label">L</span>
              <span class="size-desc">9-13 kg</span>
            </button>
            <button class="size-btn" data-size="xl">
              <span class="size-label">XL</span>
              <span class="size-desc">12+ kg</span>
            </button>
          </div>
        </div>

        <!-- Quantity Selector -->
        <div class="selector-block">
          <label class="selector-label">Quantity</label>
          <div class="quantity-selector">
            <button class="qty-btn" id="qtyMinus">−</button>
            <input type="number" id="qtyInput" class="qty-input" value="1" min="1" max="99">
            <button class="qty-btn" id="qtyPlus">+</button>
          </div>
        </div>

        <!-- CTA Buttons -->
        <div class="cta-buttons">
          <button class="btn-add-cart-big" id="addCartBtn">
            <i class="ri-shopping-bag-line"></i> Add to Cart
          </button>
          <button class="btn-buy-now">
            <i class="ri-zap-line"></i> Buy Now
          </button>
        </div>

        <!-- Product Features Pills -->
        <div class="features-pills">
          <div class="feature-pill">
            <i class="ri-shield-check-line"></i> Rash-Free
          </div>
          <div class="feature-pill">
            <i class="ri-windy-line"></i> Breathable
          </div>
          <div class="feature-pill">
            <i class="ri-water-flash-line"></i> 12-Hour Dryness
          </div>
        </div>

      </div>

    </div>
  </section>

  <!-- Product Features & Accordion Section -->
  <section class="product-details-expanded">
    <div class="container">

      <div class="details-grid">

        <!-- Left: Key Features -->
        <div class="details-features">
          <h2 class="details-title">Product Highlights</h2>

          <div class="feature-block">
            <div class="feature-icon">
              <i class="ri-shield-check-line"></i>
            </div>
            <div class="feature-content">
              <h4 class="feature-name">Rash-Free Comfort</h4>
              <p class="feature-desc">Hypoallergenic formula safe for sensitive baby skin</p>
            </div>
          </div>

          <div class="feature-block">
            <div class="feature-icon">
              <i class="ri-windy-line"></i>
            </div>
            <div class="feature-content">
              <h4 class="feature-name">Breathable Softness</h4>
              <p class="feature-desc">Finer than silk material lets skin breathe all night</p>
            </div>
          </div>

          <div class="feature-block">
            <div class="feature-icon">
              <i class="ri-water-flash-line"></i>
            </div>
            <div class="feature-content">
              <h4 class="feature-name">12-Hour Dryness</h4>
              <p class="feature-desc">Premium absorbent technology for overnight protection</p>
            </div>
          </div>

          <div class="feature-block">
            <div class="feature-icon">
              <i class="ri-layout-grid-fill"></i>
            </div>
            <div class="feature-content">
              <h4 class="feature-name">Flexible Fit</h4>
              <p class="feature-desc">Elasticated waistband adapts to growing babies</p>
            </div>
          </div>

        </div>

        <!-- Right: Accordion FAQ & Details -->
        <div class="details-accordion">
          <h2 class="details-title">More Information</h2>

          <div class="faq-accordion-group">

            <div class="faq-item">
              <button class="faq-trigger" aria-expanded="false">
                <span class="faq-question">Product Details</span>
                <span class="faq-icon-box"><i class="ri-add-line"></i></span>
              </button>
              <div class="faq-panel">
                <div class="faq-panel-inner">
                  <p>CloudCush Overnight+ is a premium diaper designed specifically for extended wear and overnight sleep. Engineered with our proprietary SoftCloud™ technology, each diaper combines ultra-soft materials with superior absorbency for worry-free nights.</p>
                  <ul class="details-list">
                    <li>Wetness Indicator (turns blue when wet)</li>
                    <li>Leak Lock sides for extra protection</li>
                    <li>Gentle elastic leg cuffs</li>
                    <li>Hypoallergenic &amp; fragrance-free</li>
                  </ul>
                </div>
              </div>
            </div>

            <div class="faq-item">
              <button class="faq-trigger" aria-expanded="false">
                <span class="faq-question">Materials &amp; Safety</span>
                <span class="faq-icon-box"><i class="ri-add-line"></i></span>
              </button>
              <div class="faq-panel">
                <div class="faq-panel-inner">
                  <p><strong>Premium Materials:</strong></p>
                  <ul class="materials-list">
                    <li>100% TCF (Totally Chlorine-Free) pulp</li>
                    <li>SAP (Super Absorbent Polymer)</li>
                    <li>Soft non-woven fabric layers</li>
                  </ul>
                  <p><strong>Certifications:</strong> Pediatrician-approved, Dermatologist-tested, No harsh chemicals</p>
                </div>
              </div>
            </div>

            <div class="faq-item">
              <button class="faq-trigger" aria-expanded="false">
                <span class="faq-question">Shipping &amp; Returns</span>
                <span class="faq-icon-box"><i class="ri-add-line"></i></span>
              </button>
              <div class="faq-panel">
                <div class="faq-panel-inner">
                  <p><strong>Free Shipping:</strong> On orders above ₹500 across India</p>
                  <p><strong>Delivery:</strong> 3-5 business days to major cities, 5-7 days elsewhere</p>
                  <p><strong>30-Day Returns:</strong> Full refund if unopened. Opened packages available for exchange only.</p>
                </div>
              </div>
            </div>

            <div class="faq-item">
              <button class="faq-trigger" aria-expanded="false">
                <span class="faq-question">Frequently Asked Questions</span>
                <span class="faq-icon-box"><i class="ri-add-line"></i></span>
              </button>
              <div class="faq-panel">
                <div class="faq-panel-inner">
                  <p><strong>Q: What's the difference between AirSoft and Overnight+?</strong></p>
                  <p>A: Overnight+ has enhanced absorbency for 12-hour protection, while AirSoft is ideal for daytime comfort with 8-10 hour protection.</p>
                  <p><strong>Q: Are these diapers suitable for sensitive skin?</strong></p>
                  <p>A: Yes! All CloudCush diapers are hypoallergenic and dermatologist-tested. For extra sensitivity, try our dedicated Sensitive Care line.</p>
                  <p><strong>Q: What size should my baby wear?</strong></p>
                  <p>Refer to the weight guide printed on packaging. Most babies transition every 2-4 months.</p>
                </div>
              </div>
            </div>

          </div>
        </div>

      </div>

    </div>
  </section>

  <!-- Related Products Section -->
  <section class="related-products">
    <div class="container">
      <h2 class="section-title">You Might Also Like</h2>
      <p class="section-subtitle">Similar products from our premium collection</p>

      <div class="related-grid">

        <!-- Related Card 1 -->
        <div class="related-card">
          <div class="related-img-wrap">
            <img src="https://images.unsplash.com/photo-1544816155-12df9643f363?w=400&auto=format&fit=crop&q=60" alt="CloudCush AirSoft">
            <span class="badge">Premium</span>
          </div>
          <h4 class="related-name">CloudCush AirSoft</h4>
          <p class="related-desc">Everyday comfort for active babies</p>
          <div class="related-price">₹449 <span class="original">₹599</span></div>
          <button class="btn-view-product">View Product</button>
        </div>

        <!-- Related Card 2 -->
        <div class="related-card">
          <div class="related-img-wrap">
            <img src="https://images.unsplash.com/photo-1515488042361-404e9250afef?w=400&auto=format&fit=crop&q=60" alt="CloudCush GentleCare">
          </div>
          <h4 class="related-name">CloudCush GentleCare</h4>
          <p class="related-desc">Rash-free care for sensitive skin</p>
          <div class="related-price">₹525 <span class="original">₹699</span></div>
          <button class="btn-view-product">View Product</button>
        </div>

        <!-- Related Card 3 -->
        <div class="related-card">
          <div class="related-img-wrap">
            <img src="https://images.unsplash.com/photo-1622443682456-79d440ba819e?w=400&auto=format&fit=crop&q=60" alt="CloudCush FlexFit">
          </div>
          <h4 class="related-name">CloudCush FlexFit</h4>
          <p class="related-desc">Active baby fit with elastic sides</p>
          <div class="related-price">₹475 <span class="original">₹625</span></div>
          <button class="btn-view-product">View Product</button>
        </div>

        <!-- Related Card 4 -->
        <div class="related-card">
          <div class="related-img-wrap">
            <img src="https://images.unsplash.com/photo-1589637357171-4cf2713d1520?w=400&auto=format&fit=crop&q=60" alt="CloudCush TinyHug">
            <span class="badge">New</span>
          </div>
          <h4 class="related-name">CloudCush TinyHug</h4>
          <p class="related-desc">Ultra-soft newborn protection</p>
          <div class="related-price">₹499 <span class="original">₹699</span></div>
          <button class="btn-view-product">View Product</button>
        </div>

      </div>
    </div>
  </section>

</main>



<?php include 'includes/footer.php'; ?>
