<?php
// Suppress notices/warnings like index.php does
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

// Start session before any output (needed for loggedIn check in Buy Now block)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/admin/config/database.php';
require_once __DIR__ . '/admin/config/products-helper.php';

// Resolve asset URLs — guard against redeclaration if ever included elsewhere
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

// Fetch product — slug preferred (matches blog-details.php pattern), id as fallback
$product  = null;
$notFound = false;

if (!empty($_GET['slug'])) {
  $product = getProductBySlug(trim($_GET['slug']));
} elseif (!empty($_GET['id']) && is_numeric($_GET['id'])) {
  $product = getProductById((int)$_GET['id']);
}

// Treat inactive / not-found products as 404 but show graceful page
if (!$product || !in_array($product['status'], ['active', 'out_of_stock'])) {
  http_response_code(404);
  $notFound = true;
  $product  = null;
}

// Precompute all display variables & SEO Data
$seoTags = ''; // Variable to hold our SEO tags

if ($product) {
  $price       = (float)($product['sale_price'] ?: $product['price']);
  $origPrice   = $product['sale_price'] ? (float)$product['price'] : null;

  // Adjust base prices for default variant if variants exist
  $variants    = $product['variants'] ?? [];
  if (!empty($variants)) {
    $priceModifier = (float)($variants[0]['price_modifier'] ?? 0);
    $price += $priceModifier;
    if ($origPrice) {
      $origPrice += $priceModifier;
    }
  }

  $discount    = ($origPrice && $origPrice > 0) ? round((($origPrice - $price) / $origPrice) * 100) : 0;
  $mainImg     = resolveAssetUrl($product['image_url'] ?? '');
  // gallery_images already decoded by getProductBySlug/getProductById
  $gallery     = is_array($product['gallery_images']) ? $product['gallery_images'] : [];
  $allImages   = array_values(array_unique(array_filter(
    array_merge([$mainImg], array_map('resolveAssetUrl', $gallery))
  )));
  if (empty($allImages)) $allImages = [''];
  $title       = htmlspecialchars($product['title'] ?? '');
  // Keep HTML in description — only escape for display in non-HTML context later
  $rawDesc     = $product['description'] ?? $product['short_description'] ?? '';
  $rawShort    = $product['short_description'] ?? '';
  $description = $rawDesc;  // raw HTML from DB — used via nl2br or direct output
  $shortDesc   = $rawShort; // raw — used as plain text in description-text paragraph
  $category    = htmlspecialchars($product['category'] ?? 'Premium Diapers');
  // DB column is `stock`, not `stock_qty`
  $inStock     = ($product['status'] === 'active' && (int)($product['stock'] ?? 0) > 0)
    || ($product['status'] === 'active' && !isset($product['stock']));
  $ratingCount = (intval($product['id']) * 37 + 107) % 400 + 40;

  // ==========================================
  // SEO & SOCIAL MEDIA VARIABLES CALCULATION
  // ==========================================
  $seoTitle = $title . " | CloudCush India";

  $cleanDesc = strip_tags($shortDesc ?: $rawDesc);
  $seoDesc = mb_strlen($cleanDesc) > 160 ? mb_substr($cleanDesc, 0, 157) . '...' : $cleanDesc;

  $seoKeywords = strtolower($category) . ", buy " . strtolower($title) . ", baby diapers online";

  // Create absolute URLs for social sharing
  $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
  $domainName = $_SERVER['HTTP_HOST'];

  // Ensure image is a full URL and clean it up
  if (!empty($allImages[0])) {
    $imgUrl = $allImages[0];
    $seoImage = str_starts_with($imgUrl, 'http') ? $imgUrl : $protocol . $domainName . '/' . ltrim($imgUrl, '/');
  } else {
    $seoImage = $protocol . $domainName . '/admin/assets/uploads/default.jpg'; // fallback
  }

  $seoUrl = $protocol . $domainName . $_SERVER['REQUEST_URI'];

  // Generate HTML for SEO tags
  $seoTags = '
    <title>' . htmlspecialchars($seoTitle) . '</title>
    <meta name="title" content="' . htmlspecialchars($seoTitle) . '">
    <meta name="description" content="' . htmlspecialchars($seoDesc) . '">
    <meta name="keywords" content="' . htmlspecialchars($seoKeywords) . '">

    <meta property="og:type" content="product">
    <meta property="og:url" content="' . htmlspecialchars($seoUrl) . '">
    <meta property="og:title" content="' . htmlspecialchars($seoTitle) . '">
    <meta property="og:description" content="' . htmlspecialchars($seoDesc) . '">
    <meta property="og:image" itemprop="image" content="' . htmlspecialchars($seoImage) . '">
    <meta property="og:image:secure_url" content="' . htmlspecialchars($seoImage) . '">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="' . htmlspecialchars($seoUrl) . '">
    <meta name="twitter:title" content="' . htmlspecialchars($seoTitle) . '">
    <meta name="twitter:description" content="' . htmlspecialchars($seoDesc) . '">
    <meta name="twitter:image" content="' . htmlspecialchars($seoImage) . '">
    ';
}

// ==========================================
// MAGIC INJECTION: Put tags INSIDE <head>
// ==========================================
ob_start();
include 'includes/head.php';
$headContent = ob_get_clean();

// Find the closing </head> tag and insert our SEO tags right before it
if (!empty($seoTags) && stripos($headContent, '</head>') !== false) {
    $headContent = str_ireplace('</head>', $seoTags . "\n</head>", $headContent);
} else {
    // If for some reason </head> isn't found, just append it
    $headContent .= $seoTags;
}
echo $headContent;
?>

<?php include 'includes/header.php'; ?>

<main class="product-details">

  <?php if (!empty($notFound)): ?>
    <!-- Product Not Found -->
    <section class="product-hero">
      <div class="container" style="padding:100px 0;text-align:center;">
        <h2 style="font-family:var(--font-heading);color:var(--primary);font-size:2rem;margin-bottom:16px;">Product Not Found</h2>
        <p style="color:var(--text-light);margin-bottom:32px;">The product you're looking for doesn't exist or is no longer available.</p>
        <a href="products.php" class="btn-add-cart-big" style="display:inline-flex;text-decoration:none;"><i class="ri-arrow-left-line"></i>&nbsp; Back to Products</a>
      </div>
    </section>
  <?php else: ?>

    <!-- Product Hero Section -->
    <section class="product-hero">
      <div class="container product-hero-grid">

        <!-- Left: Product Gallery -->
        <div class="product-gallery-section">
          <div class="gallery-main">
            <img id="mainImage" class="main-image"
              src="<?= htmlspecialchars($allImages[0] ?? '') ?>"
              alt="<?= $title ?>" loading="eager">
          </div>

          <!-- Thumbnail Gallery -->
          <div class="gallery-thumbnails">
            <?php
            // Show actual gallery images; if only 1 image, repeat it 4 times for visual consistency
            $thumbImages = count($allImages) > 1 ? $allImages : array_fill(0, 4, $allImages[0] ?? '');
            foreach ($thumbImages as $tIdx => $tUrl):
            ?>
              <button class="thumb" data-src="<?= htmlspecialchars($tUrl) ?>"
                aria-label="Gallery image <?= $tIdx + 1 ?>"
                <?= ($tIdx === 0) ? 'data-active="true"' : '' ?>>
                <img src="<?= htmlspecialchars($tUrl) ?>" alt="">
              </button>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Right: Product Information -->
        <div class="product-info-section">

          <!-- Breadcrumb -->
          <nav class="breadcrumb-product" aria-label="Breadcrumb">
            <a href="./">Home</a>
            <span>/</span>
            <a href="products.php">Diapers</a>
            <span>/</span>
            <span><?= $title ?></span>
          </nav>

          <!-- Title & Rating -->
          <h1 class="product-title"><?= $title ?></h1>
          <p class="product-category"><?= $category ?></p>

          <div class="product-rating-section">
            <div class="rating-stars">★★★★★ (<?= $ratingCount ?> reviews)</div>
          </div>

          <!-- Price & Availability -->
          <div class="price-section">
            <div class="price-display">
              <span class="price-current">₹<?= number_format((float)$price, 0) ?></span>
              <?php if ($origPrice): ?>
                <span class="price-original">₹<?= number_format((float)$origPrice, 0) ?></span>
                <span class="price-discount">-<?= $discount ?>%</span>
              <?php endif; ?>
            </div>
            <div class="availability">
              <?php if ($inStock): ?>
                <span class="availability-status in-stock">✓ In Stock</span>
              <?php else: ?>
                <span class="availability-status" style="background:#fce4e4;color:#b71c1c;">✕ Out of Stock</span>
              <?php endif; ?>
            </div>
          </div>

          <!-- Description -->
          <div class="description-block">
            <p class="description-text"><?= htmlspecialchars(strip_tags($shortDesc ?: $rawDesc)) ?></p>
          </div>

          <?php
          $variants = $product['variants'] ?? [];
          if (!empty($variants)):
          ?>
            <!-- Size Selector -->
            <div class="selector-block">
              <label class="selector-label">Select Size</label>
              <div class="size-selector">
                <?php
                $basePrice = (float)($product['sale_price'] ?: $product['price']);
                $baseOrigPrice = $product['sale_price'] ? (float)$product['price'] : null;

                foreach ($variants as $vIdx => $variant):
                  $isDefault = !empty($variant['is_default']) || $vIdx === 0;

                  // Extract size abbreviation from variant_value
                  $val = $variant['variant_value'];
                  $sizeStr = $val;
                  if (preg_match('/\(([^)]+)\)/', $val, $matches)) {
                    $sizeStr = $matches[1];
                  }
                  $sizeStr = strtoupper($sizeStr);

                  $vPrice = $basePrice + (float)($variant['price_modifier'] ?? 0);
                  $vOrigPrice = $baseOrigPrice ? ($baseOrigPrice + (float)($variant['price_modifier'] ?? 0)) : null;
                ?>
                  <button class="size-btn" data-size="<?= htmlspecialchars($sizeStr) ?>"
                    data-price="<?= htmlspecialchars($vPrice) ?>"
                    <?= $vOrigPrice ? 'data-original-price="' . htmlspecialchars($vOrigPrice) . '"' : '' ?>
                    <?= $isDefault ? 'data-active="true"' : '' ?>>
                    <span class="size-label"><?= htmlspecialchars($sizeStr) ?></span>
                    <span class="size-desc"><?= htmlspecialchars($val) ?></span>
                  </button>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endif; ?>

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
            <button class="btn-add-cart-big" id="addCartBtn" data-product-id="<?= htmlspecialchars($product['slug'] ?? $product['id']) ?>">
              <i class="ri-shopping-bag-line"></i> Add to Cart
            </button>
            <button class="btn-buy-now">
              <i class="ri-zap-line"></i> Buy Now
            </button>
          </div>

          <!-- Product Features Pills -->
          <div class="features-pills">
            <div class="feature-pill"><i class="ri-shield-check-line"></i> Rash-Free</div>
            <div class="feature-pill"><i class="ri-windy-line"></i> Breathable</div>
            <div class="feature-pill"><i class="ri-water-flash-line"></i> 12-Hour Dryness</div>
          </div>

        </div>

      </div>
    </section>

    <!-- Centered Product Detail Images Column -->
    <?php if (!empty($product['detail_images'])): ?>
      <section class="product-details-images-section">
        <div class="container text-center">
          <div class="product-detail-images-column">
            <?php foreach ($product['detail_images'] as $detImg): ?>
              <div class="detail-image-wrapper">
                <img src="<?= htmlspecialchars(resolveAssetUrl($detImg)) ?>" alt="<?= $title ?> Detail Highlight" class="detail-big-image" loading="lazy">
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </section>
    <?php endif; ?>

    <!-- Related Products Section -->
    <section class="related-products">
      <div class="container">
        <h2 class="section-title">You Might Also Like</h2>
        <p class="section-subtitle">Similar products from our premium collection</p>

        <div class="related-grid">

          <?php
          // Fetch up to 4 other active products (exclude current)
          try {
            $relRes = getProducts(['status' => 'active', 'per_page' => 8]);
            $relAll = $relRes['data'] ?? [];
            $related = array_values(array_filter($relAll, fn($r) => $r['id'] !== $product['id']));
            $related = array_slice($related, 0, 4);
          } catch (Exception $e) {
            $related = [];
          }
          foreach ($related as $r):
            $rImg   = resolveAssetUrl($r['image_url'] ?? '');
            $rPrice = $r['sale_price'] ?: $r['price'];
            $rOrig  = $r['sale_price'] ? $r['price'] : null;
            $rBadge = !empty($r['is_featured']) ? 'Best Seller' : '';
          ?>
            <div class="related-card">
              <a href="product-details.php?slug=<?= urlencode($r['slug']) ?>" style="text-decoration:none;color:inherit;display:block;">
                <div class="related-img-wrap">
                  <?php if ($rImg): ?><img src="<?= htmlspecialchars($rImg) ?>" alt="<?= htmlspecialchars($r['title']) ?>"><?php endif; ?>
                  <?php if ($rBadge): ?><span class="badge"><?= htmlspecialchars($rBadge) ?></span><?php endif; ?>
                </div>
                <div style="padding:16px;">
                  <h4 class="related-name"><?= htmlspecialchars($r['title']) ?></h4>
                  <p class="related-desc"><?= htmlspecialchars($r['short_description'] ?? '') ?></p>
                  <div class="related-price">
                    ₹<?= number_format((float)$rPrice, 0) ?>
                    <?php if ($rOrig): ?><span class="original">₹<?= number_format((float)$rOrig, 0) ?></span><?php endif; ?>
                  </div>
                </div>
              </a>
              <div style="padding:0 16px 16px;">
                <a href="product-details.php?slug=<?= urlencode($r['slug']) ?>" class="btn-view-product">View Product</a>
              </div>
            </div>
          <?php endforeach; ?>

        </div>
      </div>
    </section>

  <?php endif; ?>

</main>

<!-- ═══════════════════════════════════════════════════════════════════════════
     BUY NOW MODAL
     Injected here so it’s available when product-details.js initialises.
     PHP embeds product data into window.ccBuyNow so JS never touches the DOM
     for product info — it just reads the JS object.
     ═══════════════════════════════════════════════════════════════════════════ -->
<?php if ($product): ?>
  <script>
    window.ccBuyNow = {
      loggedIn: <?= empty($_SESSION['customer_id']) ? 'false' : 'true' ?>,
      loginUrl: 'login.php?redirect=<?= urlencode('product-details.php?slug=' . urlencode($product['slug'] ?? '')) ?>',
      productId: <?= (int)($product['id'] ?? 0) ?>,
      productSlug: <?= json_encode($product['slug'] ?? '') ?>,
      productName: <?= json_encode($product['title'] ?? '') ?>,
      image: <?= json_encode($allImages[0] ?? '') ?>,
    };
  </script>

  <div class="bn-overlay" id="buyNowOverlay" aria-modal="true" role="dialog" aria-labelledby="bnTitle">
    <div class="bn-modal">

      <div class="bn-header">
        <h2 class="bn-title" id="bnTitle"><i class="ri-zap-line"></i> Confirm Your Order</h2>
        <button class="bn-close" id="bnClose" aria-label="Close">&times;</button>
      </div>

      <!-- Order summary strip -->
      <div class="bn-product-row">
        <img class="bn-product-img" id="bnProductImg" src="" alt="">
        <div class="bn-product-info">
          <p class="bn-product-name" id="bnProductName"></p>
          <p class="bn-product-meta" id="bnProductMeta"></p>
          <p class="bn-product-price" id="bnProductPrice"></p>
        </div>
      </div>

      <!-- Step 1: Address selection -->
      <div class="bn-section" id="bnAddressSection">
        <h3 class="bn-section-title">Delivery Address</h3>

        <!-- Saved addresses list (populated by JS) -->
        <div class="bn-addresses" id="bnAddressList"></div>

        <!-- New address form (hidden by default) -->
        <div class="bn-new-addr" id="bnNewAddrForm" style="display:none">
          <div class="bn-form-grid">
            <div class="bn-field bn-field-full">
              <label>Full Name *</label>
              <input type="text" id="bnAddrName" placeholder="Receiver’s full name" autocomplete="name">
            </div>
            <div class="bn-field bn-field-full">
              <label>Phone</label>
              <input type="tel" id="bnAddrPhone" placeholder="+91 XXXXX XXXXX" autocomplete="tel">
            </div>
            <div class="bn-field bn-field-full">
              <label>Address Line 1 *</label>
              <input type="text" id="bnAddrLine1" placeholder="House no., Street" autocomplete="address-line1">
            </div>
            <div class="bn-field bn-field-full">
              <label>Address Line 2</label>
              <input type="text" id="bnAddrLine2" placeholder="Landmark, Area" autocomplete="address-line2">
            </div>
            <div class="bn-field">
              <label>City *</label>
              <input type="text" id="bnAddrCity" placeholder="City" autocomplete="address-level2">
            </div>
            <div class="bn-field">
              <label>State *</label>
              <input type="text" id="bnAddrState" placeholder="State" autocomplete="address-level1">
            </div>
            <div class="bn-field">
              <label>ZIP / Postal Code *</label>
              <input type="text" id="bnAddrZip" placeholder="400001" autocomplete="postal-code">
            </div>
            <div class="bn-field">
              <label>Country</label>
              <input type="text" id="bnAddrCountry" value="India" autocomplete="country-name">
            </div>
          </div>
          <label class="bn-save-label">
            <input type="checkbox" id="bnSaveAddr"> Save this address to my account
          </label>
        </div>

        <button class="bn-toggle-addr" id="bnToggleAddr" type="button">+ Add a new address</button>
      </div>

      <div class="bn-footer">
        <button class="bn-btn-cancel" id="bnCancel" type="button">Cancel</button>
        <button class="bn-btn-confirm" id="bnConfirm" type="button">
          <i class="ri-check-line"></i> Place Order
        </button>
      </div>

    </div>
  </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
