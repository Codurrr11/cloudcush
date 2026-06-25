<?php
if (!function_exists('getHeaderData')) {
    require_once __DIR__ . '/../admin/config/header-helper.php';
}
$headerData = getHeaderData();
$logoImg = !empty($headerData['logo_img']) ? resolveAssetUrl($headerData['logo_img']) : '';
$logoText = !empty($headerData['logo_text']) ? $headerData['logo_text'] : 'CloudCush';
$tabs = $headerData['tabs'] ?? [];

$leftTabs = [];
$rightTabs = [];
foreach ($tabs as $tab) {
    if (($tab['position'] ?? 'left') === 'right') {
        $rightTabs[] = $tab;
    } else {
        $leftTabs[] = $tab;
    }
}
?>
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
        <?php foreach ($leftTabs as $tab): ?>
          <a href="<?= htmlspecialchars($tab['url']) ?>" class="nav-link underline-hover"><?= htmlspecialchars($tab['title']) ?></a>
        <?php endforeach; ?>
      </nav>

      <!-- CENTER: Brand Logo -->
      <div class="nav-col-center">
        <a href="./" class="logo-container">
          <?php if ($logoImg): ?>
            <img src="<?= htmlspecialchars($logoImg) ?>" alt="<?= htmlspecialchars($logoText) ?>" class="logo-img">
          <?php else: ?>
            <span class="fs-4 fw-bold text-dark logo-text" style="font-family: 'Lora', serif; letter-spacing: 1px;"><?= htmlspecialchars($logoText) ?></span>
          <?php endif; ?>
        </a>
      </div>

      <!-- RIGHT: Desktop Links & Actions -->
      <nav class="nav-col-right">
        <?php foreach ($rightTabs as $tab): ?>
          <a href="<?= htmlspecialchars($tab['url']) ?>" class="nav-link underline-hover"><?= htmlspecialchars($tab['title']) ?></a>
        <?php endforeach; ?>
        <div class="nav-icons">
          <a href="javascript:void(0);" class="icon-btn" aria-label="Search"><i class="ri-search-line"></i></a>
          <a href="account.php" class="icon-btn" aria-label="Account"><i class="ri-user-line"></i></a>
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
      <?php foreach ($tabs as $tab): 
          $isShop = (strcasecmp($tab['title'], 'Shop') === 0 || str_contains(strtolower($tab['url']), 'products.php'));
          if ($isShop):
      ?>
          <div class="mobile-menu-item has-submenu">
            <button class="mobile-nav-link mobile-submenu-toggle">
              <?= htmlspecialchars($tab['title']) ?> <i class="ri-arrow-down-s-line"></i>
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
                  <a href="<?= htmlspecialchars($tab['url']) ?>" class="mobile-explore-link">Explore All Products <i class="ri-arrow-right-line"></i></a>
                </div>

              </div><!-- /.mobile-submenu-inner -->
            </div>
          </div>
      <?php else: ?>
          <div class="mobile-menu-item">
            <a href="<?= htmlspecialchars($tab['url']) ?>" class="mobile-nav-link"><?= htmlspecialchars($tab['title']) ?></a>
          </div>
      <?php endif; ?>
      <?php endforeach; ?>

      <div class="mobile-menu-item">
        <a href="account.php" class="mobile-nav-link">Account</a>
      </div>
    </nav>
  </div>
