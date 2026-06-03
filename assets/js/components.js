/* =============================================================================
   CloudCush — components.js
   Centralized reusable shared component logic.
   Loaded on every page via footer.php.
   ============================================================================= */


/* =============================================================================
   ACCORDION — Unified, reusable, single source of truth
   -----------------------------------------------------------------------------
   Works for:
     • about.php         → .faq-item / .faq-trigger / .faq-panel
     • product-details.php → .faq-item / .faq-trigger / .faq-panel (same markup)
     • any future page   → just use .faq-item/.faq-trigger/.faq-panel structure

   Architecture:
     • Event delegation on document — one listener, zero duplicates
     • Finds the closest .faq-item ancestor from the clicked element
     • Toggles .is-open on the item
     • CSS handles max-height transition (no GSAP — no inline style conflicts)
     • Closes other open items in the same .faq-accordion-group (scoped)
     • Updates aria-expanded for accessibility
     • DOM-ready safe (DOMContentLoaded)
   ============================================================================= */

document.addEventListener('DOMContentLoaded', () => {

  // Use event delegation — single listener on document for all current and future accordions
  document.addEventListener('click', (e) => {

    // Walk up from the clicked element to find a .faq-trigger button
    const trigger = e.target.closest('.faq-trigger');
    if (!trigger) return; // click was not on or inside a .faq-trigger — ignore

    // Find the parent .faq-item
    const item = trigger.closest('.faq-item');
    if (!item) return;

    const isOpen = item.classList.contains('is-open');

    // Scope auto-close to items within the same accordion group
    // Falls back to document if no parent .faq-accordion-group exists
    const group = item.closest('.faq-accordion-group') || document;

    // Close all other open items in this group
    group.querySelectorAll('.faq-item.is-open').forEach(openItem => {
      if (openItem === item) return;
      openItem.classList.remove('is-open');
      const openTrigger = openItem.querySelector('.faq-trigger');
      if (openTrigger) openTrigger.setAttribute('aria-expanded', 'false');
    });

    // Toggle current item
    if (isOpen) {
      item.classList.remove('is-open');
      trigger.setAttribute('aria-expanded', 'false');
    } else {
      item.classList.add('is-open');
      trigger.setAttribute('aria-expanded', 'true');
    }
  });

});


/* =============================================================================
   TESTIMONIAL CAROUSEL
   Reserved — no Swiper/Slick in project. Placeholder for future use.
   ============================================================================= */


/* =============================================================================
   MEGA MENU COMPONENT
   Mega menu logic lives in navbar.js (initNavbar). Placeholder only.
   ============================================================================= */


/* =============================================================================
   GLOBAL E-COMMERCE CART SYSTEM
   -----------------------------------------------------------------------------
   Handles client-side cart state using localStorage, dynamic navbar badges,
   and dispatches custom storage and cart-changed events for live syncing.
   ============================================================================= */

window.CloudCushCart = {
  getCart() {
    try {
      const cart = localStorage.getItem('cloudcush_cart');
      return cart ? JSON.parse(cart) : [];
    } catch (e) {
      console.error('Failed to parse cart from localStorage', e);
      return [];
    }
  },

  saveCart(cart) {
    try {
      localStorage.setItem('cloudcush_cart', JSON.stringify(cart));
      this.updateNavbarBadges();
      document.dispatchEvent(new CustomEvent('cartChanged', { detail: cart }));
    } catch (e) {
      console.error('Failed to save cart to localStorage', e);
    }
  },

  addItem(item) {
    const cart = this.getCart();
    const existing = cart.find(i => i.id === item.id);
    if (existing) {
      existing.quantity += item.quantity || 1;
    } else {
      cart.push({
        id: item.id,
        name: item.name,
        size: item.size,
        price: parseFloat(item.price),
        originalPrice: item.originalPrice ? parseFloat(item.originalPrice) : null,
        image: item.image,
        quantity: item.quantity || 1
      });
    }
    this.saveCart(cart);
  },

  removeItem(productId) {
    let cart = this.getCart();
    cart = cart.filter(i => i.id !== productId);
    this.saveCart(cart);
  },

  updateQuantity(productId, quantity) {
    const cart = this.getCart();
    const item = cart.find(i => i.id === productId);
    if (item) {
      item.quantity = Math.max(1, parseInt(quantity) || 1);
      this.saveCart(cart);
    }
  },

  getCartCount() {
    const cart = this.getCart();
    return cart.reduce((total, item) => total + item.quantity, 0);
  },

  getCartSubtotal() {
    const cart = this.getCart();
    return cart.reduce((total, item) => total + (item.price * item.quantity), 0);
  },

  updateNavbarBadges() {
    const count = this.getCartCount();
    document.querySelectorAll('.cart-count').forEach(el => {
      el.textContent = count;
    });
  }
};

// Sync cart across active tabs/windows
window.addEventListener('storage', (e) => {
  if (e.key === 'cloudcush_cart') {
    window.CloudCushCart.updateNavbarBadges();
    document.dispatchEvent(new CustomEvent('cartChanged', { detail: window.CloudCushCart.getCart() }));
  }
});

// Initialize on DOMContentLoaded
document.addEventListener('DOMContentLoaded', () => {
  window.CloudCushCart.updateNavbarBadges();

  // Cart Page Controller
  const cartItemsList = document.getElementById('cartItemsList');
  if (cartItemsList) {
    let activePromo = sessionStorage.getItem('cloudcush_promo') || '';
    let promoDiscountPct = activePromo === 'WELCOME10' ? 0.1 : (activePromo === 'CUSH15' ? 0.15 : 0);

    const renderCart = () => {
      const cart = window.CloudCushCart.getCart();
      const emptyState = document.getElementById('cartEmptyState');
      const activeLayout = document.getElementById('cartActiveLayout');

      if (cart.length === 0) {
        if (activeLayout) activeLayout.style.display = 'none';
        if (emptyState) emptyState.style.display = 'block';
        return;
      }

      if (activeLayout) activeLayout.style.display = 'grid';
      if (emptyState) emptyState.style.display = 'none';

      // Build HTML
      let html = '';
      cart.forEach(item => {
        const itemTotal = item.price * item.quantity;
        const itemOriginalTotal = item.originalPrice ? item.originalPrice * item.quantity : null;
        
        html += `
          <div class="cart-item-row" data-id="${item.id}">
            <div class="cart-item-img-wrap">
              <img src="${item.image}" alt="${item.name}" class="cart-item-image">
            </div>
            <div class="cart-item-details">
              <h4 class="cart-item-title">${item.name}</h4>
              <p class="cart-item-size">Size: ${item.size}</p>
              <button class="btn-item-remove" data-id="${item.id}">
                <i class="ri-delete-bin-line"></i> Remove
              </button>
            </div>
            <div class="cart-item-quantity-wrapper">
              <div class="quantity-selector">
                <button class="qty-btn btn-qty-minus" data-id="${item.id}">−</button>
                <input type="number" class="qty-input item-qty-input" data-id="${item.id}" value="${item.quantity}" min="1" max="99" readonly>
                <button class="qty-btn btn-qty-plus" data-id="${item.id}">+</button>
              </div>
            </div>
            <div class="cart-item-price-wrapper">
              <span class="cart-item-price">₹${itemTotal.toLocaleString('en-IN')}</span>
              ${itemOriginalTotal ? `<span class="cart-item-price-original">₹${itemOriginalTotal.toLocaleString('en-IN')}</span>` : ''}
            </div>
          </div>
        `;
      });
      cartItemsList.innerHTML = html;
      updateTotals();
    };

    const updateTotals = () => {
      const subtotal = window.CloudCushCart.getCartSubtotal();
      
      // Shipping rule: Free for orders >= ₹1,000, else ₹99
      const shipping = subtotal >= 1000 ? 0 : 99;
      
      // Discount calculation
      const discount = Math.round(subtotal * promoDiscountPct);
      
      // Final Total
      const total = subtotal + shipping - discount;

      // Update elements
      const subtotalEl = document.getElementById('cartSubtotal');
      const shippingEl = document.getElementById('cartShipping');
      const discountRow = document.getElementById('cartDiscountRow');
      const discountEl = document.getElementById('cartDiscount');
      const totalEl = document.getElementById('cartTotal');

      if (subtotalEl) subtotalEl.textContent = `₹${subtotal.toLocaleString('en-IN')}`;
      if (shippingEl) shippingEl.textContent = shipping === 0 ? 'Free' : `₹${shipping}`;
      
      if (discount > 0) {
        if (discountRow) discountRow.style.display = 'flex';
        if (discountEl) discountEl.textContent = `-₹${discount.toLocaleString('en-IN')}`;
      } else {
        if (discountRow) discountRow.style.display = 'none';
      }
      
      if (totalEl) totalEl.textContent = `₹${total.toLocaleString('en-IN')}`;
    };

    // Render on load
    renderCart();

    // Re-render when cart state changes (e.g. from other tabs)
    document.addEventListener('cartChanged', renderCart);

    // Event delegation for item modifications inside cartItemsList
    cartItemsList.addEventListener('click', (e) => {
      const target = e.target;
      
      // Quantity Decrement
      if (target.closest('.btn-qty-minus')) {
        const btn = target.closest('.btn-qty-minus');
        const id = btn.getAttribute('data-id');
        const cart = window.CloudCushCart.getCart();
        const item = cart.find(i => i.id === id);
        if (item && item.quantity > 1) {
          window.CloudCushCart.updateQuantity(id, item.quantity - 1);
        }
      }

      // Quantity Increment
      if (target.closest('.btn-qty-plus')) {
        const btn = target.closest('.btn-qty-plus');
        const id = btn.getAttribute('data-id');
        const cart = window.CloudCushCart.getCart();
        const item = cart.find(i => i.id === id);
        if (item && item.quantity < 99) {
          window.CloudCushCart.updateQuantity(id, item.quantity + 1);
        }
      }

      // Item Removal
      if (target.closest('.btn-item-remove')) {
        const btn = target.closest('.btn-item-remove');
        const id = btn.getAttribute('data-id');
        const row = cartItemsList.querySelector(`.cart-item-row[data-id="${id}"]`);
        
        if (row) {
          // Add transition class
          row.classList.add('is-removing');
          
          // Wait for CSS animation/transition to end
          setTimeout(() => {
            window.CloudCushCart.removeItem(id);
          }, 350); // matches CSS transition duration
        }
      }
    });

    // Promo Code Field
    const couponInput = document.getElementById('couponInput');
    const couponBtn = document.getElementById('couponBtn');
    const couponFeedback = document.getElementById('couponFeedback');

    if (activePromo) {
      if (couponInput) couponInput.value = activePromo;
      if (couponFeedback) {
        couponFeedback.textContent = `Code ${activePromo} active (${promoDiscountPct * 100}% off)`;
        couponFeedback.className = 'coupon-feedback success';
      }
    }

    if (couponBtn && couponInput) {
      couponBtn.addEventListener('click', () => {
        const code = couponInput.value.trim().toUpperCase();
        if (code === 'WELCOME10') {
          activePromo = 'WELCOME10';
          promoDiscountPct = 0.1;
          sessionStorage.setItem('cloudcush_promo', activePromo);
          if (couponFeedback) {
            couponFeedback.textContent = 'Code WELCOME10 applied! 10% discount';
            couponFeedback.className = 'coupon-feedback success';
          }
          updateTotals();
        } else if (code === 'CUSH15') {
          activePromo = 'CUSH15';
          promoDiscountPct = 0.15;
          sessionStorage.setItem('cloudcush_promo', activePromo);
          if (couponFeedback) {
            couponFeedback.textContent = 'Code CUSH15 applied! 15% discount';
            couponFeedback.className = 'coupon-feedback success';
          }
          updateTotals();
        } else if (code === '') {
          activePromo = '';
          promoDiscountPct = 0;
          sessionStorage.removeItem('cloudcush_promo');
          if (couponFeedback) {
            couponFeedback.textContent = '';
            couponFeedback.className = 'coupon-feedback';
          }
          updateTotals();
        } else {
          if (couponFeedback) {
            couponFeedback.textContent = 'Invalid coupon code';
            couponFeedback.className = 'coupon-feedback error';
          }
        }
      });
    }

    // Checkout button placeholder functionality
    const checkoutBtn = document.getElementById('checkoutBtn');
    if (checkoutBtn) {
      checkoutBtn.addEventListener('click', () => {
        const originalText = checkoutBtn.textContent;
        checkoutBtn.textContent = 'Processing...';
        checkoutBtn.disabled = true;
        
        setTimeout(() => {
          alert('Thank you for your order! (Checkout integration placeholder)');
          localStorage.removeItem('cloudcush_cart');
          sessionStorage.removeItem('cloudcush_promo');
          window.location.reload();
        }, 1500);
      });
    }

    // GSAP Entrance Animations
    if (typeof gsap !== 'undefined') {
      const tl = gsap.timeline({ defaults: { ease: 'power3.out', duration: 0.8 } });
      
      // Set initial states
      gsap.set('.cart-hero-title', { y: 40, opacity: 0 });
      gsap.set('.cart-hero-subtitle', { y: 20, opacity: 0 });
      gsap.set('.cart-page .breadcrumb', { y: 15, opacity: 0 });
      
      const cart = window.CloudCushCart.getCart();
      if (cart.length > 0) {
        gsap.set('.cart-main-content', { y: 30, opacity: 0 });
        gsap.set('.cart-sidebar', { y: 30, opacity: 0 });
        gsap.set('.cart-item-row', { y: 20, opacity: 0 });

        tl.to('.cart-hero-title', { y: 0, opacity: 1 })
          .to('.cart-hero-subtitle', { y: 0, opacity: 1 }, '-=0.6')
          .to('.cart-page .breadcrumb', { y: 0, opacity: 1 }, '-=0.6')
          .to('.cart-main-content', { y: 0, opacity: 1 }, '-=0.5')
          .to('.cart-sidebar', { y: 0, opacity: 1 }, '-=0.6')
          .to('.cart-item-row', { y: 0, opacity: 1, stagger: 0.08, duration: 0.6, ease: 'power2.out' }, '-=0.4');
      } else {
        gsap.set('.cart-empty-state', { y: 40, opacity: 0 });

        tl.to('.cart-hero-title', { y: 0, opacity: 1 })
          .to('.cart-hero-subtitle', { y: 0, opacity: 1 }, '-=0.6')
          .to('.cart-page .breadcrumb', { y: 0, opacity: 1 }, '-=0.6')
          .to('.cart-empty-state', { y: 0, opacity: 1, ease: 'back.out(1.15)', duration: 1.0 }, '-=0.5');
      }
    }

    // GSAP Exit Transitions for Links
    document.querySelectorAll('.btn-continue-shopping, .breadcrumb-link').forEach(link => {
      link.addEventListener('click', function(e) {
        const href = this.getAttribute('href');
        if (href && href !== 'javascript:void(0);' && href !== '#') {
          e.preventDefault();
          if (typeof gsap !== 'undefined') {
            gsap.to('.cart-page', {
              opacity: 0,
              y: -30,
              duration: 0.5,
              ease: 'power2.in',
              onComplete: () => {
                window.location.href = href;
              }
            });
          } else {
            window.location.href = href;
          }
        }
      });
    });
  }
});

