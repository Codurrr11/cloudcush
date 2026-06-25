<?php
// ─── DB Connection ────────────────────────────────────────────────────────────
require_once __DIR__ . '/includes/db.php';
$pdo = getFrontendDB();

// ─── 1. Fetch all active products ─────────────────────────────────────────────
$productRows = $pdo->query("
    SELECT
        p.id,
        p.title,
        p.slug,
        p.category,
        p.short_description,
        p.price,
        p.sale_price,
        p.stock,
        p.status,
        p.is_featured,
        p.image_url,
        p.gallery_images,
        p.tags,
        p.description,
        p.created_at,
        COALESCE(p.low_stock_threshold, 5) AS low_stock_threshold
    FROM products p
    WHERE p.status = 'active'
    ORDER BY p.is_featured DESC, p.created_at DESC
")->fetchAll();

// ─── 2. Fetch all variants for active products in one query ───────────────────
$productIds = array_column($productRows, 'id');
$variantsByProduct = [];

if (!empty($productIds)) {
    $inClause = implode(',', array_map('intval', $productIds));
    $variantRows = $pdo->query("
        SELECT
            pv.product_id,
            pv.variant_name,
            pv.variant_value,
            pv.price_modifier,
            pv.stock,
            pv.is_default
        FROM product_variants pv
        WHERE pv.product_id IN ($inClause)
        ORDER BY pv.product_id, pv.is_default DESC, pv.id ASC
    ")->fetchAll();

    foreach ($variantRows as $v) {
        $variantsByProduct[$v['product_id']][] = $v;
    }
}

// ─── 3. Build filter data sets from real DB values ────────────────────────────

// Helper: extract size code from a variant_value like "Newborn (NB)" → "NB"
function extractSizeCode(string $label): string {
    if (preg_match('/\(([^)]+)\)/', $label, $m)) {
        return strtoupper(trim($m[1]));
    }
    return strtoupper(trim($label));
}

// Categories — unique, from actual active products only
$categories = [];
foreach ($productRows as $p) {
    $cat = trim($p['category'] ?? '');
    if ($cat !== '' && strtolower($cat) !== 'uncategorized') {
        $categories[$cat] = $cat;
    }
}
ksort($categories);

// Sizes — unique values from product_variants where variant_name = 'Size'
$sizeOrder   = ['NB' => 1, 'S' => 2, 'M' => 3, 'L' => 4, 'XL' => 5, 'XXL' => 6];
$sizeOptions = [];  // [ 'NB' => 'Newborn (NB)', ... ]

foreach ($variantsByProduct as $variants) {
    foreach ($variants as $v) {
        if (strtolower(trim($v['variant_name'])) !== 'size') continue;
        $label = trim($v['variant_value']);
        $code  = extractSizeCode($label);
        if ($code !== '' && !isset($sizeOptions[$code])) {
            $sizeOptions[$code] = $label;
        }
    }
}
uksort($sizeOptions, fn($a, $b) => ($sizeOrder[$a] ?? 99) <=> ($sizeOrder[$b] ?? 99));

// Price range — compute actual min/max across all products (using effective price)
$allPrices = [];
foreach ($productRows as $p) {
    $base = (float)($p['sale_price'] ?: $p['price']);
    $allPrices[] = $base;
    foreach (($variantsByProduct[$p['id']] ?? []) as $v) {
        $ep = $base + (float)$v['price_modifier'];
        if ($ep > 0) $allPrices[] = $ep;
    }
}
$allPrices = array_filter($allPrices, fn($x) => $x > 0);
$priceMin  = $allPrices ? (int)floor(min($allPrices) / 100) * 100 : 0;
$priceMax  = $allPrices ? (int)ceil(max($allPrices)  / 100) * 100 : 2000;

function buildPriceBrackets(int $min, int $max): array
{
    $range  = $max - $min;
    $step   = match (true) {
        $range <= 500  => 100,
        $range <= 1000 => 200,
        $range <= 2000 => 500,
        default        => 1000,
    };
    $brackets = [];
    $cur = (int)(floor($min / $step) * $step);
    while ($cur < $max) {
        $next       = $cur + $step;
        $brackets[] = [
            'min'   => $cur,
            'max'   => $next,
            'label' => '₹' . number_format($cur) . ' – ₹' . number_format($next),
        ];
        $cur = $next;
    }
    return $brackets;
}
$priceBrackets = buildPriceBrackets($priceMin, $priceMax);

// Availability — only show options that actually exist
$hasInStock  = false;
$hasLowStock = false;
foreach ($productRows as $p) {
    $threshold = (int)($p['low_stock_threshold'] ?? 5);
    $stock     = (int)$p['stock'];
    if ($stock > $threshold) $hasInStock  = true;
    if ($stock > 0 && $stock <= $threshold) $hasLowStock = true;
}

// ─── 4. Utility ───────────────────────────────────────────────────────────────
if (!function_exists('resolveAssetUrl')) {
    function resolveAssetUrl($url) {
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

include 'includes/head.php';
include 'includes/header.php';
?>

<main class="diaper-listing">

  <!-- Hero Banner Section -->
  <section class="diaper-hero">
    <div class="container diaper-hero-content">
      <div class="diaper-hero-text">
        <h1 class="diaper-hero-title">Diaper Collection</h1>
        <p class="diaper-hero-subtitle">Premium comfort for every growth stage</p>
      </div>
      <nav class="breadcrumb" aria-label="Breadcrumb">
        <a href="./" class="breadcrumb-link">Home</a>
        <span class="breadcrumb-sep">/</span>
        <span class="breadcrumb-current">Diapers</span>
      </nav>
    </div>
  </section>

  <!-- Main Content Container -->
  <div class="container diaper-layout">

    <!-- ═══ Left Sidebar: Filter Panel ═══════════════════════════════════════ -->
    <aside class="filters-panel" id="filtersPanel" aria-label="Product Filters">

      <div class="filters-header">
        <h3 class="filters-title">Filter</h3>
        <button class="filters-close" id="filtersClose" aria-label="Close Filters">
          <i class="ri-close-line"></i>
        </button>
      </div>

      <div class="filters-content">

        <?php if (!empty($categories)): ?>
        <!-- Filter: Category -->
        <div class="filter-group" id="filterGroup-category">
          <button class="filter-label" data-filter="category" type="button">
            <span class="filter-label-text">Category</span>
            <span class="filter-group-dot" aria-hidden="true"></span>
            <i class="ri-arrow-down-s-line filter-arrow"></i>
          </button>
          <div class="filter-options is-open" id="categoryFilter" role="group" aria-label="Category filters">
            <?php foreach ($categories as $cat): ?>
              <label class="filter-checkbox">
                <input type="checkbox" name="filter-category" value="<?= htmlspecialchars($cat) ?>">
                <span><?= htmlspecialchars($cat) ?></span>
              </label>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($sizeOptions)): ?>
        <!-- Filter: Size -->
        <div class="filter-group" id="filterGroup-size">
          <button class="filter-label" data-filter="size" type="button">
            <span class="filter-label-text">Size</span>
            <span class="filter-group-dot" aria-hidden="true"></span>
            <i class="ri-arrow-down-s-line filter-arrow"></i>
          </button>
          <div class="filter-options is-open" id="sizeFilter" role="group" aria-label="Size filters">
            <?php foreach ($sizeOptions as $code => $label): ?>
              <label class="filter-checkbox">
                <input type="checkbox" name="filter-size" value="<?= htmlspecialchars($code) ?>">
                <span><?= htmlspecialchars($label) ?></span>
              </label>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($priceBrackets)): ?>
        <!-- Filter: Price Range -->
        <div class="filter-group" id="filterGroup-price">
          <button class="filter-label" data-filter="price" type="button">
            <span class="filter-label-text">Price Range</span>
            <span class="filter-group-dot" aria-hidden="true"></span>
            <i class="ri-arrow-down-s-line filter-arrow"></i>
          </button>
          <div class="filter-options is-open" id="priceFilter" role="group" aria-label="Price filters">
            <?php foreach ($priceBrackets as $bracket): ?>
              <label class="filter-checkbox">
                <input type="checkbox" name="filter-price"
                       value="<?= $bracket['min'] . '-' . $bracket['max'] ?>">
                <span><?= htmlspecialchars($bracket['label']) ?></span>
              </label>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>

        <!-- Filter: Availability -->
        <?php if ($hasInStock || $hasLowStock): ?>
        <div class="filter-group" id="filterGroup-availability">
          <button class="filter-label" data-filter="availability" type="button">
            <span class="filter-label-text">Availability</span>
            <span class="filter-group-dot" aria-hidden="true"></span>
            <i class="ri-arrow-down-s-line filter-arrow"></i>
          </button>
          <div class="filter-options is-open" id="availabilityFilter" role="group" aria-label="Availability filters">
            <?php if ($hasInStock): ?>
              <label class="filter-checkbox">
                <input type="checkbox" name="filter-availability" value="in_stock">
                <span>In Stock</span>
              </label>
            <?php endif; ?>
            <?php if ($hasLowStock): ?>
              <label class="filter-checkbox">
                <input type="checkbox" name="filter-availability" value="low_stock">
                <span>Low Stock</span>
              </label>
            <?php endif; ?>
          </div>
        </div>
        <?php endif; ?>

        <!-- Filter: Special Features -->
        <?php
        // Compute which feature tags actually appear in this product set
        $featureMap = [
          'hypoallergenic'    => 'Hypoallergenic',
          'fragrance-free'    => 'Fragrance-Free',
          'wetness-indicator' => 'Wetness Indicator',
          'eco-friendly'      => 'Eco-Friendly',
        ];
        $presentFeatures = [];
        foreach ($productRows as $p) {
            $body = strtolower(($p['description'] ?? '') . ' ' . ($p['tags'] ?? ''));
            if (str_contains($body, 'hypoallergenic') || str_contains($body, 'hypo-allergenic'))
                $presentFeatures['hypoallergenic'] = true;
            if (str_contains($body, 'fragrance-free') || str_contains($body, 'zero fragrances') || str_contains($body, 'unscented'))
                $presentFeatures['fragrance-free'] = true;
            if (str_contains($body, 'wetness indicator') || str_contains($body, 'color-changing strip'))
                $presentFeatures['wetness-indicator'] = true;
            if (str_contains($body, 'eco-friendly') || str_contains($body, 'organic') || str_contains($body, 'nordic') || str_contains($body, 'chlorine-free'))
                $presentFeatures['eco-friendly'] = true;
        }
        $filteredFeatureMap = array_filter($featureMap, fn($k) => isset($presentFeatures[$k]), ARRAY_FILTER_USE_KEY);
        ?>
        <?php if (!empty($filteredFeatureMap)): ?>
        <div class="filter-group" id="filterGroup-features">
          <button class="filter-label" data-filter="features" type="button">
            <span class="filter-label-text">Special Features</span>
            <span class="filter-group-dot" aria-hidden="true"></span>
            <i class="ri-arrow-down-s-line filter-arrow"></i>
          </button>
          <div class="filter-options" id="featuresFilter" role="group" aria-label="Feature filters">
            <?php foreach ($filteredFeatureMap as $key => $label): ?>
              <label class="filter-checkbox">
                <input type="checkbox" name="filter-features" value="<?= htmlspecialchars($key) ?>">
                <span><?= htmlspecialchars($label) ?></span>
              </label>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>

        <!-- Clear All Button -->
        <button class="btn-clear-filters" id="clearFilters" type="button">Clear All</button>

      </div><!-- /.filters-content -->
    </aside>

    <!-- ═══ Right: Products Section ══════════════════════════════════════════ -->
    <section class="products-section">

      <!-- Toolbar -->
      <div class="products-toolbar">
        <div class="toolbar-left">
          <p class="results-count">
            <span id="resultCount"><?= count($productRows) ?></span> Products
          </p>
        </div>
        <div class="toolbar-right">
          <div class="sort-wrapper">
            <label for="sortBy" class="sort-label">Sort By:</label>
            <select id="sortBy" class="sort-select">
              <option value="featured">Featured</option>
              <option value="newest">Newest</option>
              <option value="price-asc">Price: Low to High</option>
              <option value="price-desc">Price: High to Low</option>
            </select>
          </div>
          <button class="filter-toggle" id="filterToggle" aria-label="Toggle Filters" type="button">
            <i class="ri-filter-3-line"></i> Filters
          </button>
        </div>
      </div>

      <!-- Product Grid -->
      <div class="products-grid" id="productsGrid">

        <?php if (empty($productRows)): ?>
          <!-- DB-level empty state (no active products at all) -->
          <div class="blog-empty-state" style="grid-column:1/-1; text-align:center; padding:60px 20px;">
            <div class="blog-empty-icon"><i class="ri-shopping-bag-line"></i></div>
            <h3 class="blog-empty-title">No Products Found</h3>
            <p class="blog-empty-desc">Check back later as we update our inventory.</p>
          </div>

        <?php else: ?>
          <?php foreach ($productRows as $p):

            // ── Variants ─────────────────────────────────────────────────────
            $variants     = $variantsByProduct[$p['id']] ?? [];
            $sizeVariants = array_values(array_filter(
                $variants,
                fn($v) => strtolower(trim($v['variant_name'])) === 'size'
            ));

            // All size codes this product ships in (used for filter matching)
            $productSizeCodes = [];
            foreach ($sizeVariants as $v) {
                $code = extractSizeCode($v['variant_value']);
                if ($code !== '') $productSizeCodes[] = $code;
            }
            $productSizeCodes = array_unique($productSizeCodes);

            // Default variant (is_default = 1, else first)
            $defaultVariant = null;
            foreach ($sizeVariants as $v) {
                if ((int)$v['is_default'] === 1) { $defaultVariant = $v; break; }
            }
            if (!$defaultVariant && !empty($sizeVariants)) {
                $defaultVariant = $sizeVariants[0];
            }
            $displaySizeLabel = $defaultVariant ? trim($defaultVariant['variant_value']) : '';

            // ── Pricing ───────────────────────────────────────────────────────
            $basePrice     = (float)($p['sale_price'] ?: $p['price']);
            $originalPrice = ($p['sale_price'] && (float)$p['sale_price'] > 0) ? (float)$p['price'] : null;

            // Lowest effective price across all size variants (for price bracket filter)
            $effectivePrices = [$basePrice];
            foreach ($sizeVariants as $v) {
                $ep = $basePrice + (float)$v['price_modifier'];
                if ($ep > 0) $effectivePrices[] = $ep;
            }
            $lowestEffectivePrice = min($effectivePrices);

            // Display price uses default variant modifier
            $displayPrice = $defaultVariant
                ? $basePrice + (float)$defaultVariant['price_modifier']
                : $basePrice;

            // ── Images ────────────────────────────────────────────────────────
            $mainImg = resolveAssetUrl($p['image_url'] ?? '');
            $gallery = [];
            if (!empty($p['gallery_images'])) {
                $gallery = is_array($p['gallery_images'])
                    ? $p['gallery_images']
                    : (json_decode($p['gallery_images'], true) ?: []);
            }
            $hoverImg = !empty($gallery[1]) ? resolveAssetUrl($gallery[1]) : $mainImg;

            // ── Badge ─────────────────────────────────────────────────────────
            $badge = (int)$p['is_featured'] ? 'Best Seller' : '';

            // ── Availability ──────────────────────────────────────────────────
            $stock       = (int)$p['stock'];
            $threshold   = (int)($p['low_stock_threshold'] ?? 5);
            $availStatus = ($stock > $threshold)
                ? 'in_stock'
                : (($stock > 0) ? 'low_stock' : 'out_of_stock');

            // ── Feature tags ──────────────────────────────────────────────────
            $body        = strtolower(($p['description'] ?? '') . ' ' . ($p['tags'] ?? ''));
            $featureTags = [];
            if (str_contains($body, 'hypoallergenic') || str_contains($body, 'hypo-allergenic'))
                $featureTags[] = 'hypoallergenic';
            if (str_contains($body, 'fragrance-free') || str_contains($body, 'zero fragrances') || str_contains($body, 'unscented'))
                $featureTags[] = 'fragrance-free';
            if (str_contains($body, 'wetness indicator') || str_contains($body, 'color-changing strip'))
                $featureTags[] = 'wetness-indicator';
            if (str_contains($body, 'eco-friendly') || str_contains($body, 'organic') || str_contains($body, 'nordic') || str_contains($body, 'chlorine-free'))
                $featureTags[] = 'eco-friendly';

            // ── Mock rating (deterministic per product id) ────────────────────
            $ratingCount = ((int)$p['id'] * 37 + 107) % 400 + 40;

            // data-sizes: space-separated uppercase size codes e.g. "NB S M L XL"
            // Empty string means this product has no size variants.
            $dataSizes = implode(' ', $productSizeCodes);
          ?>

            <div class="product-card"
                 data-id="<?= (int)$p['id'] ?>"
                 data-slug="<?= htmlspecialchars($p['slug']) ?>"
                 data-category="<?= htmlspecialchars(trim($p['category'])) ?>"
                 data-sizes="<?= htmlspecialchars($dataSizes) ?>"
                 data-price="<?= htmlspecialchars((string)$lowestEffectivePrice) ?>"
                 data-availability="<?= htmlspecialchars($availStatus) ?>"
                 data-features="<?= htmlspecialchars(implode(' ', $featureTags)) ?>"
                 data-featured="<?= (int)$p['is_featured'] ?>"
                 data-created="<?= strtotime($p['created_at']) ?>"
                 onclick="window.location='product-details.php?slug=<?= urlencode($p['slug']) ?>'"
                 style="cursor:pointer;">

              <div class="product-image-wrap">
                <?php if ($mainImg): ?>
                  <img class="product-image product-main-img"
                       src="<?= htmlspecialchars($mainImg) ?>"
                       alt="<?= htmlspecialchars($p['title']) ?>"
                       loading="lazy">
                <?php endif; ?>

                <?php if ($hoverImg && $hoverImg !== $mainImg): ?>
                  <img class="product-image product-hover-img"
                       src="<?= htmlspecialchars($hoverImg) ?>"
                       alt="<?= htmlspecialchars($p['title']) ?> Detail"
                       loading="lazy">
                <?php endif; ?>

                <?php if ($badge): ?>
                  <span class="product-badge best-seller"><?= htmlspecialchars($badge) ?></span>
                <?php endif; ?>

                <?php if ($availStatus === 'low_stock'): ?>
                  <span class="product-badge low-stock-badge">Low Stock</span>
                <?php endif; ?>
              </div>

              <div class="product-info">
                <h3 class="product-name"><?= htmlspecialchars($p['title']) ?></h3>

                <?php if ($displaySizeLabel): ?>
                  <p class="product-size"><?= htmlspecialchars($displaySizeLabel) ?></p>
                <?php endif; ?>

                <div class="product-rating">
                  <span class="stars">★★★★★</span>
                  <span class="rating-count">(<?= $ratingCount ?>)</span>
                </div>

                <div class="product-price">
                  <span class="price">₹<?= number_format($displayPrice, 0) ?></span>
                  <?php if ($originalPrice && $originalPrice > $displayPrice): ?>
                    <span class="price-original">₹<?= number_format($originalPrice, 0) ?></span>
                  <?php endif; ?>
                </div>

                <a href="javascript:void(0);"
                   class="btn-add-cart"
                   data-product="<?= (int)$p['id'] ?>"
                   onclick="event.stopPropagation();">Add to Cart</a>
              </div>
            </div>

          <?php endforeach; ?>
        <?php endif; ?>

      </div><!-- /#productsGrid -->

      <!-- No-results state (toggled by JS; hidden by default) -->
      <div class="products-no-results" id="noResults" role="status" aria-live="polite">
        <div class="blog-empty-icon"><i class="ri-search-line"></i></div>
        <h3 class="blog-empty-title">No products match your filters</h3>
        <p class="blog-empty-desc">Try adjusting or clearing your selections.</p>
        <button class="btn-clear-filters" id="clearFiltersAlt" type="button" style="margin-top:16px;">
          Clear All Filters
        </button>
      </div>

    </section><!-- /.products-section -->

  </div><!-- /.diaper-layout -->
</main>

<?php include 'includes/footer.php'; ?>
