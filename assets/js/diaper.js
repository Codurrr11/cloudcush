/**
 * diaper.js — CloudCush Products Page
 *
 * Scope: Add-to-Cart interaction + GSAP entrance animations only.
 *
 * NOTE: All filter, sort, accordion, and mobile panel logic
 * lives exclusively in components.js (initProductFilters).
 * This file must NOT re-implement any of that logic.
 */

document.addEventListener('DOMContentLoaded', function () {

  // ── Add to Cart ────────────────────────────────────────────────────────────
  document.querySelectorAll('.btn-add-cart').forEach(function (btn) {
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      e.stopPropagation();

      const card = this.closest('.product-card');
      if (!card) return;

      const productId       = this.getAttribute('data-product') || 'unknown';
      const name            = card.querySelector('.product-name')?.textContent.trim() || 'CloudCush Diaper';
      const size            = card.querySelector('.product-size')?.textContent.trim() || '';
      const priceEl         = card.querySelector('.price');
      const originalPriceEl = card.querySelector('.price-original');
      const priceVal        = priceEl        ? parseFloat(priceEl.textContent.replace(/[^\d.]/g, ''))        : 0;
      const originalPriceVal= originalPriceEl? parseFloat(originalPriceEl.textContent.replace(/[^\d.]/g, '')): null;
      const image           = card.querySelector('.product-main-img')?.src || '';

      if (window.CloudCushCart) {
        window.CloudCushCart.addItem({
          id:            productId,
          name:          name,
          size:          size,
          price:         priceVal,
          originalPrice: originalPriceVal,
          image:         image,
          quantity:      1
        });
      }

      const originalText = this.textContent;
      this.textContent   = '✓ Added';
      this.classList.add('btn-added');
      setTimeout(() => {
        this.textContent = originalText;
        this.classList.remove('btn-added');
      }, 1200);
    });
  });

  // ── GSAP entrance animations ───────────────────────────────────────────────
  if (typeof gsap !== 'undefined' && typeof ScrollTrigger !== 'undefined') {
    gsap.registerPlugin(ScrollTrigger);

    document.querySelectorAll('.product-card').forEach(function (card, index) {
      // Skip cards already hidden by the filter system
      if (card.classList.contains('product-card--hidden')) return;

      gsap.fromTo(card,
        { opacity: 0, y: 30 },
        {
          scrollTrigger: {
            trigger: card,
            start: 'top 92%',
            toggleActions: 'play none none none'
          },
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
