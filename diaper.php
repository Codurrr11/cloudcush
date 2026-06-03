<?php include 'includes/head.php'; ?>

<?php include 'includes/header.php'; ?>

<main class="diaper-listing">

  <!-- Hero Banner Section -->
  <section class="diaper-hero">
    <div class="container diaper-hero-content">
      <div class="diaper-hero-text">
        <h1 class="diaper-hero-title">Diaper Collection</h1>
        <p class="diaper-hero-subtitle">Premium comfort for every growth stage</p>
      </div>

      <!-- Breadcrumb Navigation -->
      <nav class="breadcrumb" aria-label="Breadcrumb">
        <a href="./" class="breadcrumb-link">Home</a>
        <span class="breadcrumb-sep">/</span>
        <span class="breadcrumb-current">Diapers</span>
      </nav>
    </div>

    <!-- Hero Background Image -->
    <img src="https://images.unsplash.com/photo-1544816155-12df9643f363?w=1200&auto=format&fit=crop&q=60" alt="CloudCush Diapers" class="diaper-hero-image" loading="eager">
  </section>

  <!-- Main Content Container -->
  <div class="container diaper-layout">

    <!-- Left Sidebar: Filter Panel -->
    <aside class="filters-panel" id="filtersPanel">
      <div class="filters-header">
        <h3 class="filters-title">Filter</h3>
        <button class="filters-close" id="filtersClose" aria-label="Close Filters">
          <i class="ri-close-line"></i>
        </button>
      </div>

      <div class="filters-content">

        <!-- Filter: Age Group -->
        <div class="filter-group">
          <button class="filter-label" data-filter="age">
            Age Group <i class="ri-arrow-down-s-line"></i>
          </button>
          <div class="filter-options" id="ageFilter">
            <label class="filter-checkbox">
              <input type="checkbox" value="0-3"> 0–3 Months
            </label>
            <label class="filter-checkbox">
              <input type="checkbox" value="3-6"> 3–6 Months
            </label>
            <label class="filter-checkbox">
              <input type="checkbox" value="6-12"> 6–12 Months
            </label>
            <label class="filter-checkbox">
              <input type="checkbox" value="1-2"> 1–2 Years
            </label>
            <label class="filter-checkbox">
              <input type="checkbox" value="2-4"> 2–4 Years
            </label>
          </div>
        </div>

        <!-- Filter: Size -->
        <div class="filter-group">
          <button class="filter-label" data-filter="size">
            Size <i class="ri-arrow-down-s-line"></i>
          </button>
          <div class="filter-options" id="sizeFilter">
            <label class="filter-checkbox">
              <input type="checkbox" value="nb"> NB (Newborn)
            </label>
            <label class="filter-checkbox">
              <input type="checkbox" value="s"> S (3-6 kg)
            </label>
            <label class="filter-checkbox">
              <input type="checkbox" value="m"> M (6-11 kg)
            </label>
            <label class="filter-checkbox">
              <input type="checkbox" value="l"> L (9-13 kg)
            </label>
            <label class="filter-checkbox">
              <input type="checkbox" value="xl"> XL (12+ kg)
            </label>
          </div>
        </div>

        <!-- Filter: Type -->
        <div class="filter-group">
          <button class="filter-label" data-filter="type">
            Diaper Type <i class="ri-arrow-down-s-line"></i>
          </button>
          <div class="filter-options" id="typeFilter">
            <label class="filter-checkbox">
              <input type="checkbox" value="everyday"> Everyday Comfort
            </label>
            <label class="filter-checkbox">
              <input type="checkbox" value="overnight"> Overnight Protection
            </label>
            <label class="filter-checkbox">
              <input type="checkbox" value="rash-free"> Rash-Free Care
            </label>
            <label class="filter-checkbox">
              <input type="checkbox" value="active"> Active Baby Fit
            </label>
            <label class="filter-checkbox">
              <input type="checkbox" value="sensitive"> Sensitive Skin
            </label>
          </div>
        </div>

        <!-- Filter: Price Range -->
        <div class="filter-group">
          <button class="filter-label" data-filter="price">
            Price Range <i class="ri-arrow-down-s-line"></i>
          </button>
          <div class="filter-options" id="priceFilter">
            <label class="filter-checkbox">
              <input type="checkbox" value="0-500"> ₹0 – ₹500
            </label>
            <label class="filter-checkbox">
              <input type="checkbox" value="500-1000"> ₹500 – ₹1,000
            </label>
            <label class="filter-checkbox">
              <input type="checkbox" value="1000-2000"> ₹1,000 – ₹2,000
            </label>
            <label class="filter-checkbox">
              <input type="checkbox" value="2000+"> ₹2,000+
            </label>
          </div>
        </div>

        <!-- Filter: Special Features -->
        <div class="filter-group">
          <button class="filter-label" data-filter="features">
            Special Features <i class="ri-arrow-down-s-line"></i>
          </button>
          <div class="filter-options" id="featuresFilter">
            <label class="filter-checkbox">
              <input type="checkbox" value="hypoallergenic"> Hypoallergenic
            </label>
            <label class="filter-checkbox">
              <input type="checkbox" value="eco-friendly"> Eco-Friendly
            </label>
            <label class="filter-checkbox">
              <input type="checkbox" value="fragrance-free"> Fragrance-Free
            </label>
            <label class="filter-checkbox">
              <input type="checkbox" value="wetness-indicator"> Wetness Indicator
            </label>
          </div>
        </div>

        <!-- Clear Filters Button -->
        <button class="btn-clear-filters" id="clearFilters">Clear All</button>
      </div>
    </aside>

    <!-- Right Content: Products Grid -->
    <section class="products-section">

      <!-- Toolbar: Sort & View Toggle -->
      <div class="products-toolbar">
        <div class="toolbar-left">
          <p class="results-count"><span id="resultCount">12</span> Products</p>
        </div>
        <div class="toolbar-right">
          <div class="sort-wrapper">
            <label for="sortBy" class="sort-label">Sort By:</label>
            <select id="sortBy" class="sort-select">
              <option value="featured">Featured</option>
              <option value="newest">Newest</option>
              <option value="price-asc">Price: Low to High</option>
              <option value="price-desc">Price: High to Low</option>
              <option value="best-selling">Best Selling</option>
              <option value="rating">Top Rated</option>
            </select>
          </div>
          <button class="filter-toggle" id="filterToggle" aria-label="Toggle Filters">
            <i class="ri-filter-3-line"></i> Filters
          </button>
        </div>
      </div>

      <!-- Product Grid -->
      <div class="products-grid" id="productsGrid">

        <!-- Product Card Template (Repeating) -->
        <!-- Card 1: AirSoft Newborn -->
        <div class="product-card" data-age="0-3" data-size="nb" data-type="everyday" data-price="450">
          <div class="product-image-wrap">
            <img class="product-image product-main-img" src="https://images.unsplash.com/photo-1544816155-12df9643f363?w=400&auto=format&fit=crop&q=60" alt="CloudCush AirSoft Newborn" loading="lazy">
            <img class="product-image product-hover-img" src="https://images.unsplash.com/photo-1522850959076-3f4770c18413?w=400&auto=format&fit=crop&q=60" alt="CloudCush AirSoft Newborn Back" loading="lazy">
            <span class="product-badge">New</span>
          </div>
          <div class="product-info">
            <h3 class="product-name">CloudCush AirSoft</h3>
            <p class="product-size">Newborn (NB)</p>
            <div class="product-rating">
              <span class="stars">★★★★★</span>
              <span class="rating-count">(248)</span>
            </div>
            <div class="product-price">
              <span class="price">₹449</span>
              <span class="price-original">₹599</span>
            </div>
            <button class="btn-add-cart" data-product="airsoft-nb">Add to Cart</button>
          </div>
        </div>

        <!-- Card 2: Overnight+ -->
        <div class="product-card" data-age="1-2" data-size="m" data-type="overnight" data-price="599">
          <div class="product-image-wrap">
            <img class="product-image product-main-img" src="https://images.unsplash.com/photo-1697751946618-d04ad612cb78?w=400&auto=format&fit=crop&q=60" alt="CloudCush Overnight+" loading="lazy">
            <img class="product-image product-hover-img" src="https://images.unsplash.com/photo-1515488042361-404e9250afef?w=400&auto=format&fit=crop&q=60" alt="CloudCush Overnight+ Detail" loading="lazy">
            <span class="product-badge best-seller">Best Seller</span>
          </div>
          <div class="product-info">
            <h3 class="product-name">CloudCush Overnight+</h3>
            <p class="product-size">Medium (M)</p>
            <div class="product-rating">
              <span class="stars">★★★★★</span>
              <span class="rating-count">(512)</span>
            </div>
            <div class="product-price">
              <span class="price">₹599</span>
              <span class="price-original">₹799</span>
            </div>
            <button class="btn-add-cart" data-product="overnight-m">Add to Cart</button>
          </div>
        </div>

        <!-- Card 3: GentleCare -->
        <div class="product-card" data-age="3-6" data-size="s" data-type="rash-free" data-price="525">
          <div class="product-image-wrap">
            <img class="product-image product-main-img" src="https://images.unsplash.com/photo-1589637357171-4cf2713d1520?w=400&auto=format&fit=crop&q=60" alt="CloudCush GentleCare" loading="lazy">
            <img class="product-image product-hover-img" src="https://images.unsplash.com/photo-1597423244036-20d845b3cfec?w=400&auto=format&fit=crop&q=60" alt="CloudCush GentleCare Back" loading="lazy">
          </div>
          <div class="product-info">
            <h3 class="product-name">CloudCush GentleCare</h3>
            <p class="product-size">Small (S)</p>
            <div class="product-rating">
              <span class="stars">★★★★☆</span>
              <span class="rating-count">(189)</span>
            </div>
            <div class="product-price">
              <span class="price">₹525</span>
              <span class="price-original">₹699</span>
            </div>
            <button class="btn-add-cart" data-product="gentlecare-s">Add to Cart</button>
          </div>
        </div>

        <!-- Card 4: FlexFit -->
        <div class="product-card" data-age="6-12" data-size="m" data-type="active" data-price="475">
          <div class="product-image-wrap">
            <img class="product-image product-main-img" src="https://images.unsplash.com/photo-1622443682456-79d440ba819e?w=400&auto=format&fit=crop&q=60" alt="CloudCush FlexFit" loading="lazy">
            <img class="product-image product-hover-img" src="https://images.unsplash.com/photo-1555685812-4b943f1cb0eb?w=400&auto=format&fit=crop&q=60" alt="CloudCush FlexFit Detail" loading="lazy">
          </div>
          <div class="product-info">
            <h3 class="product-name">CloudCush FlexFit</h3>
            <p class="product-size">Medium (M)</p>
            <div class="product-rating">
              <span class="stars">★★★★★</span>
              <span class="rating-count">(326)</span>
            </div>
            <div class="product-price">
              <span class="price">₹475</span>
              <span class="price-original">₹625</span>
            </div>
            <button class="btn-add-cart" data-product="flexfit-m">Add to Cart</button>
          </div>
        </div>

        <!-- Card 5: TinyHug -->
        <div class="product-card" data-age="0-3" data-size="nb" data-type="everyday" data-price="499">
          <div class="product-image-wrap">
            <img class="product-image product-main-img" src="https://images.unsplash.com/photo-1515488042361-404e9250afef?w=400&auto=format&fit=crop&q=60" alt="CloudCush TinyHug" loading="lazy">
            <img class="product-image product-hover-img" src="https://images.unsplash.com/photo-1544816155-12df9643f363?w=400&auto=format&fit=crop&q=60" alt="CloudCush TinyHug Back" loading="lazy">
            <span class="product-badge">New</span>
          </div>
          <div class="product-info">
            <h3 class="product-name">CloudCush TinyHug</h3>
            <p class="product-size">Newborn (NB)</p>
            <div class="product-rating">
              <span class="stars">★★★★★</span>
              <span class="rating-count">(407)</span>
            </div>
            <div class="product-price">
              <span class="price">₹499</span>
              <span class="price-original">₹699</span>
            </div>
            <button class="btn-add-cart" data-product="tinyhug-nb">Add to Cart</button>
          </div>
        </div>

        <!-- Card 6: AirSoft Large -->
        <div class="product-card" data-age="2-4" data-size="l" data-type="everyday" data-price="520">
          <div class="product-image-wrap">
            <img class="product-image product-main-img" src="https://images.unsplash.com/photo-1522850959076-3f4770c18413?w=400&auto=format&fit=crop&q=60" alt="CloudCush AirSoft Large" loading="lazy">
            <img class="product-image product-hover-img" src="https://images.unsplash.com/photo-1697751946618-d04ad612cb78?w=400&auto=format&fit=crop&q=60" alt="CloudCush AirSoft Large Back" loading="lazy">
          </div>
          <div class="product-info">
            <h3 class="product-name">CloudCush AirSoft</h3>
            <p class="product-size">Large (L)</p>
            <div class="product-rating">
              <span class="stars">★★★★★</span>
              <span class="rating-count">(198)</span>
            </div>
            <div class="product-price">
              <span class="price">₹520</span>
              <span class="price-original">₹699</span>
            </div>
            <button class="btn-add-cart" data-product="airsoft-l">Add to Cart</button>
          </div>
        </div>

        <!-- Card 7: Overnight+ Small -->
        <div class="product-card" data-age="1-2" data-size="s" data-type="overnight" data-price="575">
          <div class="product-image-wrap">
            <img class="product-image product-main-img" src="https://images.unsplash.com/photo-1515488042361-404e9250afef?w=400&auto=format&fit=crop&q=60" alt="CloudCush Overnight+ Small" loading="lazy">
            <img class="product-image product-hover-img" src="https://images.unsplash.com/photo-1544816155-12df9643f363?w=400&auto=format&fit=crop&q=60" alt="CloudCush Overnight+ Small Back" loading="lazy">
          </div>
          <div class="product-info">
            <h3 class="product-name">CloudCush Overnight+</h3>
            <p class="product-size">Small (S)</p>
            <div class="product-rating">
              <span class="stars">★★★★★</span>
              <span class="rating-count">(215)</span>
            </div>
            <div class="product-price">
              <span class="price">₹575</span>
              <span class="price-original">₹749</span>
            </div>
            <button class="btn-add-cart" data-product="overnight-s">Add to Cart</button>
          </div>
        </div>

        <!-- Card 8: GentleCare Medium -->
        <div class="product-card" data-age="6-12" data-size="m" data-type="rash-free" data-price="540">
          <div class="product-image-wrap">
            <img class="product-image product-main-img" src="https://images.unsplash.com/photo-1597423244036-20d845b3cfec?w=400&auto=format&fit=crop&q=60" alt="CloudCush GentleCare Medium" loading="lazy">
            <img class="product-image product-hover-img" src="https://images.unsplash.com/photo-1589637357171-4cf2713d1520?w=400&auto=format&fit=crop&q=60" alt="CloudCush GentleCare Medium Back" loading="lazy">
          </div>
          <div class="product-info">
            <h3 class="product-name">CloudCush GentleCare</h3>
            <p class="product-size">Medium (M)</p>
            <div class="product-rating">
              <span class="stars">★★★★☆</span>
              <span class="rating-count">(142)</span>
            </div>
            <div class="product-price">
              <span class="price">₹540</span>
              <span class="price-original">₹699</span>
            </div>
            <button class="btn-add-cart" data-product="gentlecare-m">Add to Cart</button>
          </div>
        </div>

        <!-- Card 9: FlexFit Large -->
        <div class="product-card" data-age="2-4" data-size="l" data-type="active" data-price="495">
          <div class="product-image-wrap">
            <img class="product-image product-main-img" src="https://images.unsplash.com/photo-1555685812-4b943f1cb0eb?w=400&auto=format&fit=crop&q=60" alt="CloudCush FlexFit Large" loading="lazy">
            <img class="product-image product-hover-img" src="https://images.unsplash.com/photo-1622443682456-79d440ba819e?w=400&auto=format&fit=crop&q=60" alt="CloudCush FlexFit Large Back" loading="lazy">
          </div>
          <div class="product-info">
            <h3 class="product-name">CloudCush FlexFit</h3>
            <p class="product-size">Large (L)</p>
            <div class="product-rating">
              <span class="stars">★★★★★</span>
              <span class="rating-count">(287)</span>
            </div>
            <div class="product-price">
              <span class="price">₹495</span>
              <span class="price-original">₹649</span>
            </div>
            <button class="btn-add-cart" data-product="flexfit-l">Add to Cart</button>
          </div>
        </div>

        <!-- Card 10: TinyHug S -->
        <div class="product-card" data-age="3-6" data-size="s" data-type="everyday" data-price="515">
          <div class="product-image-wrap">
            <img class="product-image product-main-img" src="https://images.unsplash.com/photo-1544816155-12df9643f363?w=400&auto=format&fit=crop&q=60" alt="CloudCush TinyHug Small" loading="lazy">
            <img class="product-image product-hover-img" src="https://images.unsplash.com/photo-1515488042361-404e9250afef?w=400&auto=format&fit=crop&q=60" alt="CloudCush TinyHug Small Back" loading="lazy">
          </div>
          <div class="product-info">
            <h3 class="product-name">CloudCush TinyHug</h3>
            <p class="product-size">Small (S)</p>
            <div class="product-rating">
              <span class="stars">★★★★★</span>
              <span class="rating-count">(312)</span>
            </div>
            <div class="product-price">
              <span class="price">₹515</span>
              <span class="price-original">₹699</span>
            </div>
            <button class="btn-add-cart" data-product="tinyhug-s">Add to Cart</button>
          </div>
        </div>

        <!-- Card 11: AirSoft Medium -->
        <div class="product-card" data-age="6-12" data-size="m" data-type="everyday" data-price="475">
          <div class="product-image-wrap">
            <img class="product-image product-main-img" src="https://images.unsplash.com/photo-1522850959076-3f4770c18413?w=400&auto=format&fit=crop&q=60" alt="CloudCush AirSoft Medium" loading="lazy">
            <img class="product-image product-hover-img" src="https://images.unsplash.com/photo-1697751946618-d04ad612cb78?w=400&auto=format&fit=crop&q=60" alt="CloudCush AirSoft Medium Back" loading="lazy">
          </div>
          <div class="product-info">
            <h3 class="product-name">CloudCush AirSoft</h3>
            <p class="product-size">Medium (M)</p>
            <div class="product-rating">
              <span class="stars">★★★★★</span>
              <span class="rating-count">(456)</span>
            </div>
            <div class="product-price">
              <span class="price">₹475</span>
              <span class="price-original">₹599</span>
            </div>
            <button class="btn-add-cart" data-product="airsoft-m">Add to Cart</button>
          </div>
        </div>

        <!-- Card 12: Overnight+ XL -->
        <div class="product-card" data-age="2-4" data-size="xl" data-type="overnight" data-price="649">
          <div class="product-image-wrap">
            <img class="product-image product-main-img" src="https://images.unsplash.com/photo-1697751946618-d04ad612cb78?w=400&auto=format&fit=crop&q=60" alt="CloudCush Overnight+ XL" loading="lazy">
            <img class="product-image product-hover-img" src="https://images.unsplash.com/photo-1515488042361-404e9250afef?w=400&auto=format&fit=crop&q=60" alt="CloudCush Overnight+ XL Back" loading="lazy">
          </div>
          <div class="product-info">
            <h3 class="product-name">CloudCush Overnight+</h3>
            <p class="product-size">Extra Large (XL)</p>
            <div class="product-rating">
              <span class="stars">★★★★★</span>
              <span class="rating-count">(178)</span>
            </div>
            <div class="product-price">
              <span class="price">₹649</span>
              <span class="price-original">₹849</span>
            </div>
            <button class="btn-add-cart" data-product="overnight-xl">Add to Cart</button>
          </div>
        </div>

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
        <button class="pagination-btn">8</button>
        <button class="pagination-btn" aria-label="Next Page">
          <i class="ri-arrow-right-s-line"></i>
        </button>
      </div>

    </section>
  </div>
</main>



<?php include 'includes/footer.php'; ?>
