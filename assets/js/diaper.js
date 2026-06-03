/**
 * Diaper Listing Page Interactions
 */
document.addEventListener('DOMContentLoaded', function() {
  const filtersPanel = document.getElementById('filtersPanel');
  const filterToggle = document.getElementById('filterToggle');
  const filtersClose = document.getElementById('filtersClose');
  const clearFiltersBtn = document.getElementById('clearFilters');
  const sortSelect = document.getElementById('sortBy');
  const filterLabels = document.querySelectorAll('.filter-label');
  const productsGrid = document.getElementById('productsGrid');
  const resultCountEl = document.getElementById('resultCount');

  // ── All product cards (static NodeList — rebuilt on sort)
  let allCards = Array.from(document.querySelectorAll('.product-card'));

  // ── Mobile filter panel overlay ─────────────────────────────────────────
  let filtersOverlay = document.querySelector('.filters-overlay');
  if (!filtersOverlay) {
    filtersOverlay = document.createElement('div');
    filtersOverlay.className = 'filters-overlay';
    document.body.appendChild(filtersOverlay);
  }

  const openFilters = () => {
    filtersPanel.classList.add('is-active');
    filtersOverlay.classList.add('is-active');
    document.body.style.overflow = 'hidden';
  };

  const closeFilters = () => {
    filtersPanel.classList.remove('is-active');
    filtersOverlay.classList.remove('is-active');
    document.body.style.overflow = '';
  };

  if (filterToggle && filtersPanel) {
    filterToggle.addEventListener('click', () => {
      filtersPanel.classList.contains('is-active') ? closeFilters() : openFilters();
    });
  }
  if (filtersClose) filtersClose.addEventListener('click', closeFilters);
  filtersOverlay.addEventListener('click', closeFilters);

  // ── Filter group accordion — open/close with max-height transition ───────
  filterLabels.forEach(label => {
    label.addEventListener('click', function() {
      const options = this.nextElementSibling;
      if (!options) return;
      const isOpen = this.classList.contains('is-open');
      // Close all groups first
      filterLabels.forEach(l => {
        l.classList.remove('is-open');
        const o = l.nextElementSibling;
        if (o) o.classList.remove('is-open');
      });
      // Re-open the clicked one if it was closed
      if (!isOpen) {
        this.classList.add('is-open');
        options.classList.add('is-open');
      }
    });
  });

  // Open the first filter group by default
  if (filterLabels[0]) {
    filterLabels[0].classList.add('is-open');
    const firstOptions = filterLabels[0].nextElementSibling;
    if (firstOptions) firstOptions.classList.add('is-open');
  }

  // ── Core filter + sort logic ─────────────────────────────────────────────

  /**
   * Read all checked values grouped by filter dimension.
   * Returns { age: Set, size: Set, type: Set, price: Set, features: Set }
   */
  function getActiveFilters() {
    const filters = { age: new Set(), size: new Set(), type: new Set(), price: new Set(), features: new Set() };
    document.querySelectorAll('.filter-checkbox input:checked').forEach(cb => {
      const group = cb.closest('.filter-options');
      if (!group) return;
      const id = group.id; // ageFilter, sizeFilter, typeFilter, priceFilter, featuresFilter
      if (id === 'ageFilter')      filters.age.add(cb.value);
      else if (id === 'sizeFilter')     filters.size.add(cb.value);
      else if (id === 'typeFilter')     filters.type.add(cb.value);
      else if (id === 'priceFilter')    filters.price.add(cb.value);
      else if (id === 'featuresFilter') filters.features.add(cb.value);
    });
    return filters;
  }

  /**
   * Return true if a card passes the price filter.
   * Price ranges encoded as: "0-500", "500-1000", "1000-2000", "2000+"
   */
  function priceMatches(cardPrice, priceSet) {
    if (priceSet.size === 0) return true;
    const p = parseFloat(cardPrice);
    for (const range of priceSet) {
      if (range === '2000+' && p >= 2000) return true;
      const parts = range.split('-');
      if (parts.length === 2) {
        const lo = parseFloat(parts[0]);
        const hi = parseFloat(parts[1]);
        if (p >= lo && p < hi) return true;
      }
    }
    return false;
  }

  /**
   * Apply active filters + sort, update visibility + result count.
   */
  function applyFilters() {
    const f = getActiveFilters();
    const noFilters = f.age.size === 0 && f.size.size === 0 && f.type.size === 0 && f.price.size === 0 && f.features.size === 0;

    let visible = [];

    allCards.forEach(card => {
      let show = false;

      if (noFilters) {
        show = true;
      } else {
        const cardAge   = card.dataset.age   || '';
        const cardSize  = card.dataset.size  || '';
        const cardType  = card.dataset.type  || '';
        const cardPrice = card.dataset.price || '0';
        // data-features is optional (space-separated list)
        const cardFeatures = (card.dataset.features || '').split(' ').filter(Boolean);

        const ageOk      = f.age.size === 0      || f.age.has(cardAge);
        const sizeOk     = f.size.size === 0     || f.size.has(cardSize);
        const typeOk     = f.type.size === 0     || f.type.has(cardType);
        const priceOk    = priceMatches(cardPrice, f.price);
        // Feature filter: card must have ALL selected features
        const featOk     = f.features.size === 0 || [...f.features].every(feat => cardFeatures.includes(feat));

        show = ageOk && sizeOk && typeOk && priceOk && featOk;
      }

      if (show) {
        card.style.display = '';
        visible.push(card);
      } else {
        card.style.display = 'none';
      }
    });

    // ── Sort visible cards ──────────────────────────────────────────────
    const sortVal = sortSelect ? sortSelect.value : 'featured';
    if (sortVal !== 'featured') {
      visible.sort((a, b) => {
        const priceA = parseFloat(a.dataset.price || 0);
        const priceB = parseFloat(b.dataset.price || 0);
        const ratingA = (a.querySelector('.rating-count') || {}).textContent || '(0)';
        const ratingB = (b.querySelector('.rating-count') || {}).textContent || '(0)';
        const countA = parseInt(ratingA.replace(/\D/g, '')) || 0;
        const countB = parseInt(ratingB.replace(/\D/g, '')) || 0;

        if (sortVal === 'price-asc')   return priceA - priceB;
        if (sortVal === 'price-desc')  return priceB - priceA;
        if (sortVal === 'best-selling' || sortVal === 'rating') return countB - countA;
        // newest / featured — leave in DOM order (already sorted)
        return 0;
      });

      // Re-append visible cards in sorted order without touching hidden ones
      visible.forEach(card => productsGrid.appendChild(card));
    }

    // ── Update result count ────────────────────────────────────────────
    if (resultCountEl) resultCountEl.textContent = visible.length;

    // ── Empty state ────────────────────────────────────────────────────
    let emptyMsg = productsGrid.querySelector('.filter-empty-msg');
    if (visible.length === 0) {
      if (!emptyMsg) {
        emptyMsg = document.createElement('p');
        emptyMsg.className = 'filter-empty-msg';
        emptyMsg.style.cssText = 'grid-column:1/-1;text-align:center;padding:60px 0;color:var(--text-light);font-size:15px;';
        emptyMsg.textContent = 'No products match your filters. Try adjusting your selection.';
        productsGrid.appendChild(emptyMsg);
      }
    } else if (emptyMsg) {
      emptyMsg.remove();
    }
  }

  // ── Listen on every checkbox ─────────────────────────────────────────────
  document.querySelectorAll('.filter-checkbox input').forEach(cb => {
    cb.addEventListener('change', applyFilters);
  });

  // ── Sort select ──────────────────────────────────────────────────────────
  if (sortSelect) {
    sortSelect.addEventListener('change', applyFilters);
  }

  // ── Clear filters ────────────────────────────────────────────────────────
  if (clearFiltersBtn) {
    clearFiltersBtn.addEventListener('click', function() {
      document.querySelectorAll('.filter-checkbox input').forEach(cb => { cb.checked = false; });
      if (sortSelect) sortSelect.value = 'featured';
      // Remove any active category highlight from URL-driven state
      document.querySelectorAll('.filter-label.filter-active').forEach(l => l.classList.remove('filter-active'));
      applyFilters();
    });
  }

  // ── URL query-param pre-filtering (header mega-menu deep links) ──────────
  // Supported params: ?type=overnight&age=0-3&size=m  (comma-separated for multi)
  function applyUrlParams() {
    const params = new URLSearchParams(window.location.search);
    let didSet = false;

    const paramMap = {
      type:     'typeFilter',
      age:      'ageFilter',
      size:     'sizeFilter',
      price:    'priceFilter',
      features: 'featuresFilter'
    };

    for (const [param, filterId] of Object.entries(paramMap)) {
      const val = params.get(param);
      if (!val) continue;
      const values = val.split(',').map(v => v.trim()).filter(Boolean);
      values.forEach(v => {
        const cb = document.querySelector(`#${filterId} input[value="${v}"]`);
        if (cb) { cb.checked = true; didSet = true; }
      });
      // Open the matching filter group accordion
      const optionsEl = document.getElementById(filterId);
      if (optionsEl) {
        const labelEl = optionsEl.previousElementSibling;
        // Close all first, then open target
        filterLabels.forEach(l => { l.classList.remove('is-open'); const o = l.nextElementSibling; if (o) o.classList.remove('is-open'); });
        if (labelEl) labelEl.classList.add('is-open');
        optionsEl.classList.add('is-open');
      }
    }

    if (didSet) applyFilters();
  }

  applyUrlParams();

  // ── Add to Cart interaction ──────────────────────────────────────────────
  document.querySelectorAll('.btn-add-cart').forEach(btn => {
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      e.stopPropagation();

      const card = this.closest('.product-card');
      if (!card) return;

      const productId = this.getAttribute('data-product') || 'unknown';
      const name = card.querySelector('.product-name')?.textContent.trim() || 'CloudCush Diaper';
      const size = card.querySelector('.product-size')?.textContent.trim() || '';
      
      const priceEl = card.querySelector('.price');
      const originalPriceEl = card.querySelector('.price-original');
      
      const priceVal = priceEl ? parseFloat(priceEl.textContent.replace(/[^\d.]/g, '')) : 0;
      const originalPriceVal = originalPriceEl ? parseFloat(originalPriceEl.textContent.replace(/[^\d.]/g, '')) : null;
      
      const image = card.querySelector('.product-main-img')?.src || '';

      if (window.CloudCushCart) {
        window.CloudCushCart.addItem({
          id: productId,
          name: name,
          size: size,
          price: priceVal,
          originalPrice: originalPriceVal,
          image: image,
          quantity: 1
        });
      }

      const originalText = this.textContent;
      this.textContent = '✓ Added';
      this.classList.add('btn-added');
      setTimeout(() => {
        this.textContent = originalText;
        this.classList.remove('btn-added');
      }, 1200);
    });
  });

  // ── Product card → details page ──────────────────────────────────────────
  document.querySelectorAll('.product-card').forEach(card => {
    card.style.cursor = 'pointer';
    card.addEventListener('click', function(e) {
      if (e.target.closest('.btn-add-cart')) return;
      window.location.href = 'product-details.php';
    });
  });

  document.querySelectorAll('.product-image-wrap').forEach(wrap => {
    wrap.addEventListener('click', function(e) { e.stopPropagation(); });
  });

  // ── GSAP entrance animation (only on cards visible at load) ─────────────
  if (typeof gsap !== 'undefined' && typeof ScrollTrigger !== 'undefined') {
    gsap.registerPlugin(ScrollTrigger);
    allCards.forEach((card, index) => {
      // Skip cards hidden by URL params pre-filter
      if (card.style.display === 'none') return;
      gsap.fromTo(card,
        { opacity: 0, y: 30 },
        {
          scrollTrigger: { trigger: card, start: 'top 92%', toggleActions: 'play none none none' },
          duration: 0.55,
          y: 0,
          opacity: 1,
          delay: (index % 3) * 0.06,
          ease: 'power3.out'
        }
      );
    });
  }
});

