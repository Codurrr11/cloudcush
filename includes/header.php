<body>

  <!-- Top Announcement Bar -->
  <div class="announcement-bar">
    Subscribe & Save 15% + Free Shipping across India.
  </div>

  <!-- Main Navbar Header -->
  <header class="site-header">
    <div class="container navbar">

      <!-- Mobile Hamburger Menu Button -->
      <div class="menu-toggle" id="mobile-menu-toggle" aria-label="Toggle Menu">
        <span></span>
        <span></span>
        <span></span>
      </div>

      <!-- LEFT: Desktop Links -->
      <nav class="nav-col-left">
        <a href="javascript:void(0);" class="nav-link underline-hover" id="navShopAll">Shop</a>
        <a href="about.php" class="nav-link underline-hover">Why CloudCush</a>
        <a href="diaper-guide.php" class="nav-link underline-hover">Care Guide</a>
      </nav>

      <!-- CENTER: Brand Logo -->
      <div class="nav-col-center">
        <a href="./" class="logo-container">
          <img src="assets/images/logo.png" alt="cloudcush" class="logo-img">
        </a>
      </div>

      <!-- RIGHT: Desktop Links & Actions -->
      <nav class="nav-col-right">
        <a href="blog.php" class="nav-link underline-hover">Journal</a>
        <a href="faq.php" class="nav-link underline-hover">FAQ</a>
        <div class="nav-icons">
          <a href="javascript:void(0);" class="icon-btn" aria-label="Search"><i class="ri-search-line"></i></a>
          <a href="javascript:void(0);" class="icon-btn" aria-label="Account"><i class="ri-user-line"></i></a>
          <a href="cart.php" class="icon-btn" aria-label="Cart">
            <i class="ri-shopping-bag-line"></i>
            <span class="cart-count">0</span>
          </a>
        </div>
      </nav>

      <!-- Mobile Right Icons (Search & Cart only) -->
      <div class="nav-col-right-mobile">
        <a href="javascript:void(0);" class="icon-btn" aria-label="Search"><i class="ri-search-line"></i></a>
        <a href="cart.php" class="icon-btn" aria-label="Cart">
          <i class="ri-shopping-bag-line"></i>
          <span class="cart-count">0</span>
        </a>
      </div>

    </div>

    <!-- Mega Menu Dropdown -->
    <div class="mega-menu" id="megaMenu">
      <div class="container mega-menu-container">

        <!-- LEFT SIDE: 4 columns of diaper categories -->
        <div class="mega-menu-left">

          <!-- Column 1: Growth Stages -->
          <div class="mega-menu-col">
            <h4 class="mega-menu-title">Growth Stages</h4>
            <ul class="mega-menu-links">
              <li><a href="products.php?age=0-3">0–3 Months</a></li>
              <li><a href="products.php?age=3-6">3–6 Months</a></li>
              <li><a href="products.php?age=6-12">6–12 Months</a></li>
              <li><a href="products.php?age=1-2">1–2 Years</a></li>
              <li><a href="products.php?age=2-4">2–4 Years</a></li>
            </ul>
          </div>

          <!-- Column 2: Diaper Types -->
          <div class="mega-menu-col">
            <h4 class="mega-menu-title">Diaper Types</h4>
            <ul class="mega-menu-links">
              <li><a href="products.php?type=overnight">Overnight Diapers</a></li>
              <li><a href="products.php?type=rash-free">Rash-Free Diapers</a></li>
              <li><a href="products.php?type=everyday">Ultra Soft Diapers</a></li>
              <li><a href="products.php?type=active">Active Baby Diapers</a></li>
              <li><a href="products.php?type=sensitive">Sensitive Skin Diapers</a></li>
            </ul>
          </div>

          <!-- Column 3: Collections -->
          <div class="mega-menu-col">
            <h4 class="mega-menu-title">Collections</h4>
            <ul class="mega-menu-links">
              <li><a href="products.php">New Arrivals</a></li>
              <li><a href="products.php">Best Sellers</a></li>
              <li><a href="products.php">Premium Range</a></li>
              <li><a href="products.php?type=everyday">Everyday Comfort</a></li>
              <li><a href="products.php">Summer Care</a></li>
            </ul>
          </div>

          <!-- Column 4: Parent Essentials -->
          <div class="mega-menu-col">
            <h4 class="mega-menu-title">Parent Essentials</h4>
            <ul class="mega-menu-links">
              <li><a href="products.php">Combo Packs</a></li>
              <li><a href="products.php">Monthly Packs</a></li>
              <li><a href="products.php">Single Packs</a></li>
              <li><a href="products.php">Travel Packs</a></li>
            </ul>
          </div>

          <!-- Bottom Link -->
          <div class="mega-menu-bottom-link">
            <a href="products.php" class="mega-explore-link">Explore All Products <i class="ri-arrow-right-line"></i></a>
          </div>

        </div>

        <!-- RIGHT SIDE: Featured cards -->
        <div class="mega-menu-right">

          <!-- Card 1: AirSoft -->
          <a href="javascript:void(0);" class="mega-card">
            <div class="mega-card-img-wrap">
              <img src="https://images.unsplash.com/photo-1544816155-12df9643f363?w=500&auto=format&fit=crop&q=60" alt="CloudCush AirSoft">
            </div>
            <span class="mega-card-title">CloudCush AirSoft</span>
          </a>

          <!-- Card 2: Overnight+ -->
          <a href="javascript:void(0);" class="mega-card">
            <div class="mega-card-img-wrap">
              <img src="https://images.unsplash.com/photo-1697751946618-d04ad612cb78?w=500&auto=format&fit=crop&q=60" alt="CloudCush Overnight+">
            </div>
            <span class="mega-card-title">CloudCush Overnight+</span>
          </a>

          <!-- Card 3: TinyHug -->
          <a href="javascript:void(0);" class="mega-card">
            <div class="mega-card-img-wrap">
              <img src="https://images.unsplash.com/photo-1515488042361-404e9250afef?w=500&auto=format&fit=crop&q=60" alt="CloudCush TinyHug">
            </div>
            <span class="mega-card-title">CloudCush TinyHug</span>
          </a>

        </div>

      </div>
    </div>
  </header>

  <!-- Mobile Side Panel Backdrop -->
  <div class="mobile-nav-backdrop" id="mobile-nav-backdrop"></div>

  <!-- Mobile Fullscreen Overlay Navigation -->
  <div class="mobile-nav-overlay" id="mobile-nav-menu">
    <!-- Close Button -->
    <button class="mobile-close-btn" id="mobile-menu-close" aria-label="Close Menu">
      <i class="ri-close-line"></i>
    </button>
    <nav class="mobile-menu-list">
      <div class="mobile-menu-item has-submenu">
        <button class="mobile-nav-link mobile-submenu-toggle">
          Shop <i class="ri-arrow-down-s-line"></i>
        </button>
        <div class="mobile-submenu">
          <div class="mobile-submenu-inner">

            <!-- Accordion Category 1: Growth Stages -->
            <div class="mobile-submenu-group">
              <button class="mobile-submenu-title">Growth Stages <i class="ri-add-line"></i></button>
              <ul class="mobile-submenu-links">
                <li><a href="products.php?age=0-3">0–3 Months</a></li>
                <li><a href="products.php?age=3-6">3–6 Months</a></li>
                <li><a href="products.php?age=6-12">6–12 Months</a></li>
                <li><a href="products.php?age=1-2">1–2 Years</a></li>
                <li><a href="products.php?age=2-4">2–4 Years</a></li>
              </ul>
            </div>

            <!-- Accordion Category 2: Diaper Types -->
            <div class="mobile-submenu-group">
              <button class="mobile-submenu-title">Diaper Types <i class="ri-add-line"></i></button>
              <ul class="mobile-submenu-links">
                <li><a href="products.php?type=overnight">Overnight Diapers</a></li>
                <li><a href="products.php?type=rash-free">Rash-Free Diapers</a></li>
                <li><a href="products.php?type=everyday">Ultra Soft Diapers</a></li>
                <li><a href="products.php?type=active">Active Baby Diapers</a></li>
                <li><a href="products.php?type=sensitive">Sensitive Skin Diapers</a></li>
              </ul>
            </div>

            <!-- Accordion Category 3: Collections -->
            <div class="mobile-submenu-group">
              <button class="mobile-submenu-title">Collections <i class="ri-add-line"></i></button>
              <ul class="mobile-submenu-links">
                <li><a href="products.php">New Arrivals</a></li>
                <li><a href="products.php">Best Sellers</a></li>
                <li><a href="products.php">Premium Range</a></li>
                <li><a href="products.php?type=everyday">Everyday Comfort</a></li>
                <li><a href="products.php">Summer Care</a></li>
              </ul>
            </div>

            <!-- Accordion Category 4: Parent Essentials -->
            <div class="mobile-submenu-group">
              <button class="mobile-submenu-title">Parent Essentials <i class="ri-add-line"></i></button>
              <ul class="mobile-submenu-links">
                <li><a href="products.php">Combo Packs</a></li>
                <li><a href="products.php">Monthly Packs</a></li>
                <li><a href="products.php">Single Packs</a></li>
                <li><a href="products.php">Travel Packs</a></li>
              </ul>
            </div>

            <div class="mobile-submenu-bottom">
              <a href="products.php" class="mobile-explore-link">Explore All Products <i class="ri-arrow-right-line"></i></a>
            </div>

          </div><!-- /.mobile-submenu-inner -->
        </div>
      </div>

      <div class="mobile-menu-item">
        <a href="about.php" class="mobile-nav-link">Why CloudCush</a>
      </div>

      <div class="mobile-menu-item">
        <a href="diaper-guide.php" class="mobile-nav-link">Care Guide</a>
      </div>

      <div class="mobile-menu-item">
        <a href="blog.php" class="mobile-nav-link">Journal</a>
      </div>

      <div class="mobile-menu-item">
        <a href="faq.php" class="mobile-nav-link">FAQ</a>
      </div>

      <div class="mobile-menu-item">
        <a href="javascript:void(0);" class="mobile-nav-link">Account</a>
      </div>
    </nav>
  </div>
