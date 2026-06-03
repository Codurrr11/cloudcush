/**
 * Product Details Page Interactions
 */
document.addEventListener('DOMContentLoaded', function() {
  const mainImage = document.getElementById('mainImage');
  const thumbnails = document.querySelectorAll('.thumb');
  const sizeButtons = document.querySelectorAll('.size-btn');
  const qtyMinus = document.getElementById('qtyMinus');
  const qtyPlus = document.getElementById('qtyPlus');
  const qtyInput = document.getElementById('qtyInput');
  const addCartBtn = document.getElementById('addCartBtn');
  // Note: accordion handled by the unified FAQ listener in about.js

  // Gallery thumbnail switching — smooth fade
  thumbnails.forEach(thumb => {
    thumb.addEventListener('click', function() {
      const newSrc = this.dataset.src;
      if (mainImage && newSrc) {
        mainImage.style.opacity = '0';
        mainImage.style.transform = 'scale(0.98)';
        setTimeout(() => {
          mainImage.src = newSrc;
          mainImage.style.opacity = '1';
          mainImage.style.transform = 'scale(1)';
        }, 180);
      }

      // Update active state
      thumbnails.forEach(t => t.removeAttribute('data-active'));
      this.setAttribute('data-active', 'true');
    });
  });

  // Smooth image crossfade style
  if (mainImage) {
    mainImage.style.transition = 'opacity 0.2s ease, transform 0.2s ease';
  }

  // Size selector
  sizeButtons.forEach(btn => {
    btn.addEventListener('click', function() {
      sizeButtons.forEach(b => b.removeAttribute('data-active'));
      this.setAttribute('data-active', 'true');
    });
  });

  // Quantity controls
  if (qtyMinus && qtyInput) {
    qtyMinus.addEventListener('click', () => {
      const current = parseInt(qtyInput.value);
      if (current > 1) qtyInput.value = current - 1;
    });
  }

  if (qtyPlus && qtyInput) {
    qtyPlus.addEventListener('click', () => {
      const current = parseInt(qtyInput.value);
      if (current < 99) qtyInput.value = current + 1;
    });
  }

  // Add to Cart with feedback
  if (addCartBtn) {
    addCartBtn.addEventListener('click', function() {
      const qty = qtyInput ? parseInt(qtyInput.value) : 1;
      const activeSizeBtn = document.querySelector('.size-btn[data-active="true"]');
      
      let sizeCode = 'm';
      let sizeLabel = 'Medium (M)';
      if (activeSizeBtn) {
        sizeCode = activeSizeBtn.getAttribute('data-size') || 'm';
        const labelSpan = activeSizeBtn.querySelector('.size-label');
        const descSpan = activeSizeBtn.querySelector('.size-desc');
        if (labelSpan && descSpan) {
          sizeLabel = `${labelSpan.textContent.trim()} (${descSpan.textContent.trim()})`;
        } else {
          sizeLabel = activeSizeBtn.textContent.trim().replace(/\s+/g, ' ');
        }
      }

      // Generate product ID based on product type (overnight) and selected size code
      const productId = `overnight-${sizeCode}`;
      const name = document.querySelector('.product-title')?.textContent.trim() || 'CloudCush Overnight+';
      
      const priceEl = document.querySelector('.price-current');
      const originalPriceEl = document.querySelector('.price-original');
      
      const priceVal = priceEl ? parseFloat(priceEl.textContent.replace(/[^\d.]/g, '')) : 599;
      const originalPriceVal = originalPriceEl ? parseFloat(originalPriceEl.textContent.replace(/[^\d.]/g, '')) : null;
      
      const image = mainImage ? mainImage.src : '';

      if (window.CloudCushCart) {
        window.CloudCushCart.addItem({
          id: productId,
          name: name,
          size: sizeLabel,
          price: priceVal,
          originalPrice: originalPriceVal,
          image: image,
          quantity: qty
        });
      }

      const originalHTML = this.innerHTML;
      this.innerHTML = '<i class="ri-checkbox-circle-line"></i> Added to Cart';
      this.classList.add('btn-added');
      this.disabled = true;
      
      setTimeout(() => {
        this.innerHTML = originalHTML;
        this.classList.remove('btn-added');
        this.disabled = false;
      }, 2000);
    });
  }

  // (in case GSAP hasn't loaded or ScrollTrigger hasn't fired)
  const ensureVisible = () => {
    document.querySelectorAll('.feature-block, .related-card').forEach(el => {
      const style = window.getComputedStyle(el);
      if (parseFloat(style.opacity) < 0.1) {
        el.style.opacity = '1';
        el.style.transform = 'none';
        el.style.visibility = 'visible';
      }
    });
  };
  // Run after 2s as a safety fallback if GSAP animations stall
  setTimeout(ensureVisible, 2000);

  // Related product cards redirect
  document.querySelectorAll('.related-card').forEach(card => {
    card.style.cursor = 'pointer';
    card.addEventListener('click', function() {
      window.location.href = 'product-details.php';
    });
  });

  document.querySelectorAll('.btn-view-product').forEach(btn => {
    btn.addEventListener('click', function(e) {
      e.stopPropagation();
      window.location.href = 'product-details.php';
    });
  });

  // GSAP animations on scroll
  if (typeof gsap !== 'undefined' && typeof ScrollTrigger !== 'undefined') {
    gsap.registerPlugin(ScrollTrigger);

    // Animate feature blocks — use fromTo so elements are never stuck at opacity:0
    gsap.utils.toArray('.feature-block').forEach((block, index) => {
      gsap.fromTo(block,
        { opacity: 0, x: -24 },
        {
          scrollTrigger: {
            trigger: block,
            start: 'top 88%',
            toggleActions: 'play none none none'
          },
          duration: 0.5,
          x: 0,
          opacity: 1,
          delay: index * 0.07,
          ease: 'power3.out'
        }
      );
    });

    // Animate related cards — use fromTo so elements never stay invisible
    gsap.utils.toArray('.related-card').forEach((card, index) => {
      gsap.fromTo(card,
        { opacity: 0, y: 20 },
        {
          scrollTrigger: {
            trigger: card,
            start: 'top 92%',
            toggleActions: 'play none none none'
          },
          duration: 0.5,
          y: 0,
          opacity: 1,
          delay: index * 0.06,
          ease: 'power3.out'
        }
      );
    });
  }

  // Prevent text selection on draggable images
  if (mainImage) {
    mainImage.draggable = false;
  }
  thumbnails.forEach(thumb => {
    const img = thumb.querySelector('img');
    if (img) {
      img.draggable = false;
    }
  });
});

