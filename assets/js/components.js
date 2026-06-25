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

      // Discount calculation
      const discount = Math.round(subtotal * promoDiscountPct);

      // Total is exact cart value — no shipping, no extra charges
      const total = subtotal - discount;

      // Update elements
      const subtotalEl = document.getElementById('cartSubtotal');
      const discountRow = document.getElementById('cartDiscountRow');
      const discountEl = document.getElementById('cartDiscount');
      const totalEl = document.getElementById('cartTotal');

      if (subtotalEl) subtotalEl.textContent = `₹${subtotal.toLocaleString('en-IN')}`;

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

    /* =========================================================================
       CHECKOUT — Guest-aware order placement
       -------------------------------------------------------------------------
       Flow:
         1. User clicks "Proceed to Checkout"
         2. POST cart to order-handler.php
         3a. If logged in → order placed → success dialog
         3b. If NOT logged in (need_guest_email = true) → show SweetAlert email
             prompt → retry POST with guest_email included
         3c. Any other error → show error dialog
       ========================================================================= */
    const checkoutBtn = document.getElementById('checkoutBtn');
    if (checkoutBtn) {

      // Core function: attempt checkout, optionally with a guest email
      function attemptCheckout(guestEmail) {
        const cart = window.CloudCushCart.getCart();

        if (!cart || cart.length === 0) {
          Swal.fire({
            icon: 'warning',
            title: 'Cart is Empty',
            text: 'Please add items to your cart before checking out.',
            customClass: { popup: 'swal2-premium-popup', confirmButton: 'swal2-confirm-primary' },
            buttonsStyling: false,
            confirmButtonText: 'Browse Products'
          }).then(result => {
            if (result.isConfirmed) window.location.href = 'products.php';
          });
          return;
        }

        const originalText = checkoutBtn.textContent;
        checkoutBtn.textContent = 'Placing Order...';
        checkoutBtn.disabled = true;

        const payload = {
          items: cart,
          promoCode: activePromo || ''
        };

        // Attach guest email if provided
        if (guestEmail) {
          payload.guest_email = guestEmail;
        }

        fetch('order-handler.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        })
        .then(res => res.json().then(data => ({ ok: res.ok, data })))
        .then(({ ok, data }) => { data.success = ok && data.success; return data; })
        .then(data => {
          if (data.success) {
            // Clear cart and promo on success
            localStorage.removeItem('cloudcush_cart');
            sessionStorage.removeItem('cloudcush_promo');
            window.CloudCushCart.updateNavbarBadges();

            Swal.fire({
              icon: 'success',
              title: 'Order Placed!',
              html: `<p style="font-size:14px;color:#475569;">
                       Thank you for your order.<br>
                       We've received <strong>${data.item_count} item${data.item_count > 1 ? 's' : ''}</strong>
                       worth <strong>₹${data.order_total}</strong>.<br>
                       We'll start processing it right away.
                     </p>`,
              customClass: { popup: 'swal2-premium-popup', confirmButton: 'swal2-confirm-primary' },
              buttonsStyling: false,
              confirmButtonText: 'View My Orders'
            }).then(() => {
              window.location.href = 'account.php?tab=order-history';
            });

          } else if (data.need_guest_email) {
            // Server says: not logged in — ask for email to proceed as guest
            checkoutBtn.textContent = originalText;
            checkoutBtn.disabled = false;
            promptGuestEmail(cart);

          } else if (data.redirect) {
            // Fallback: explicit redirect (should not normally hit this path now)
            Swal.fire({
              icon: 'info',
              title: 'Login Required',
              text: data.message,
              customClass: { popup: 'swal2-premium-popup', confirmButton: 'swal2-confirm-primary' },
              buttonsStyling: false,
              confirmButtonText: 'Log In'
            }).then(() => {
              window.location.href = data.redirect;
            });

          } else {
            Swal.fire({
              icon: 'error',
              title: 'Order Failed',
              text: data.message || 'Something went wrong. Please try again.',
              customClass: { popup: 'swal2-premium-popup', confirmButton: 'swal2-confirm-primary' },
              buttonsStyling: false,
              confirmButtonText: 'OK'
            });
            checkoutBtn.textContent = originalText;
            checkoutBtn.disabled = false;
          }
        })
        .catch(() => {
          Swal.fire({
            icon: 'error',
            title: 'Connection Error',
            text: 'Could not reach the server. Please check your connection and try again.',
            customClass: { popup: 'swal2-premium-popup', confirmButton: 'swal2-confirm-primary' },
            buttonsStyling: false,
            confirmButtonText: 'OK'
          });
          checkoutBtn.textContent = originalText;
          checkoutBtn.disabled = false;
        });
      }

      // Guest email prompt — shown when server returns need_guest_email:true
      function promptGuestEmail() {
        Swal.fire({
          icon: 'info',
          title: 'Continue as Guest',
          html: `
            <p style="font-size:14px;color:#475569;margin-bottom:16px;">
              Enter your email to place your order. We'll create an account
              for you automatically so you can track your orders.
            </p>
            <div style="text-align:left;margin-bottom:8px;">
              <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:6px;">
                Your Name (optional)
              </label>
              <input id="swal-guest-name" type="text" class="swal2-input"
                placeholder="Enter your name" autocomplete="name"
                style="margin:0;width:100%;box-sizing:border-box;">
            </div>
            <div style="text-align:left;">
              <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:6px;">
                Email Address *
              </label>
              <input id="swal-guest-email" type="email" class="swal2-input"
                placeholder="you@example.com" autocomplete="email"
                style="margin:0;width:100%;box-sizing:border-box;">
            </div>
            <p style="font-size:11px;color:#9ca3af;margin-top:12px;">
              A temporary password <strong>123456</strong> will be set.
              You can change it later from your account.
            </p>
          `,
          customClass: {
            popup: 'swal2-premium-popup',
            confirmButton: 'swal2-confirm-primary',
            cancelButton: 'swal2-cancel-secondary'
          },
          buttonsStyling: false,
          confirmButtonText: 'Place Order',
          cancelButtonText: 'Log In Instead',
          showCancelButton: true,
          focusConfirm: false,
          didOpen: () => {
            // Auto-focus the email field
            const emailInput = document.getElementById('swal-guest-email');
            if (emailInput) setTimeout(() => emailInput.focus(), 100);

            // Allow Enter key to confirm
            const nameInput = document.getElementById('swal-guest-name');
            if (nameInput) {
              nameInput.addEventListener('keydown', e => {
                if (e.key === 'Enter') {
                  e.preventDefault();
                  if (emailInput) emailInput.focus();
                }
              });
            }
            if (emailInput) {
              emailInput.addEventListener('keydown', e => {
                if (e.key === 'Enter') {
                  e.preventDefault();
                  Swal.clickConfirm();
                }
              });
            }
          },
          preConfirm: () => {
            const email = document.getElementById('swal-guest-email')?.value.trim() || '';
            const name  = document.getElementById('swal-guest-name')?.value.trim()  || '';

            if (!email) {
              Swal.showValidationMessage('Please enter your email address.');
              return false;
            }
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
              Swal.showValidationMessage('Please enter a valid email address.');
              return false;
            }
            return { email, name };
          }
        }).then(result => {
          if (result.isConfirmed && result.value) {
            // Retry order with guest credentials
            const payload = {
              items: window.CloudCushCart.getCart(),
              promoCode: activePromo || '',
              guest_email: result.value.email,
              guest_name:  result.value.name || ''
            };

            checkoutBtn.textContent = 'Placing Order...';
            checkoutBtn.disabled = true;

            fetch('order-handler.php', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify(payload)
            })
            .then(res => res.json().then(data => ({ ok: res.ok, data })))
            .then(({ ok, data }) => { data.success = ok && data.success; return data; })
            .then(data => {
              if (data.success) {
                localStorage.removeItem('cloudcush_cart');
                sessionStorage.removeItem('cloudcush_promo');
                window.CloudCushCart.updateNavbarBadges();

                Swal.fire({
                  icon: 'success',
                  title: 'Order Placed!',
                  html: `<p style="font-size:14px;color:#475569;">
                           Thank you for your order!<br>
                           We've received <strong>${data.item_count} item${data.item_count > 1 ? 's' : ''}</strong>
                           worth <strong>₹${data.order_total}</strong>.<br><br>
                           <strong>Your account has been created.</strong><br>
                           Email: <strong>${result.value.email}</strong><br>
                           Temporary password: <strong>123456</strong><br>
                           <span style="font-size:12px;color:#9ca3af;">Please change your password after logging in.</span>
                         </p>`,
                  customClass: { popup: 'swal2-premium-popup', confirmButton: 'swal2-confirm-primary' },
                  buttonsStyling: false,
                  confirmButtonText: 'View My Orders'
                }).then(() => {
                  window.location.href = 'account.php?tab=order-history';
                });

              } else {
                Swal.fire({
                  icon: 'error',
                  title: 'Order Failed',
                  text: data.message || 'Something went wrong. Please try again.',
                  customClass: { popup: 'swal2-premium-popup', confirmButton: 'swal2-confirm-primary' },
                  buttonsStyling: false,
                  confirmButtonText: 'OK'
                });
                checkoutBtn.textContent = 'Proceed to Checkout';
                checkoutBtn.disabled = false;
              }
            })
            .catch(() => {
              Swal.fire({
                icon: 'error',
                title: 'Connection Error',
                text: 'Could not reach the server. Please try again.',
                customClass: { popup: 'swal2-premium-popup', confirmButton: 'swal2-confirm-primary' },
                buttonsStyling: false,
                confirmButtonText: 'OK'
              });
              checkoutBtn.textContent = 'Proceed to Checkout';
              checkoutBtn.disabled = false;
            });

          } else if (result.dismiss === Swal.DismissReason.cancel) {
            // User clicked "Log In Instead"
            window.location.href = 'login.php?redirect=cart.php';
          }
        });
      }

      // Bind checkout button
      checkoutBtn.addEventListener('click', () => {
        attemptCheckout(null);
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

  /* =============================================================================
     FAQ CATEGORY SWITCHER — Smooth Interactive Tab Filtering
     ============================================================================= */
  const faqCategoryItems = document.querySelectorAll('.faq-category-item');
  const faqAccordionGroups = document.querySelectorAll('.faq-accordion-group');

  if (faqCategoryItems.length > 0 && faqAccordionGroups.length > 0) {
    faqCategoryItems.forEach(item => {
      item.addEventListener('click', () => {
        const category = item.getAttribute('data-category');

        // Remove active state from all items and set current as active
        faqCategoryItems.forEach(i => i.classList.remove('active'));
        item.classList.add('active');

        // Close all currently open accordion panels globally first to avoid layout jerks
        document.querySelectorAll('.faq-item.is-open').forEach(openItem => {
          openItem.classList.remove('is-open');
          const trigger = openItem.querySelector('.faq-trigger');
          if (trigger) trigger.setAttribute('aria-expanded', 'false');
        });

        // Toggle visibility of the correct category groups
        faqAccordionGroups.forEach(group => {
          if (group.getAttribute('data-category') === category) {
            // Show current group
            group.style.display = 'flex';
            group.style.flexDirection = 'column';

            // GSAP entrance reveal for the child accordion items
            if (typeof gsap !== 'undefined') {
              gsap.fromTo(group.querySelectorAll('.faq-item'),
                { opacity: 0, y: 15 },
                { opacity: 1, y: 0, duration: 0.5, stagger: 0.08, ease: 'power2.out', clearProps: 'all' }
              );
            }
          } else {
            // Hide other groups
            group.style.display = 'none';
          }
        });

        // Trigger ScrollTrigger refresh in case heights changed
        if (typeof ScrollTrigger !== 'undefined') {
          ScrollTrigger.refresh();
        }
      });
    });
  }
});


/* =============================================================================
   PRODUCTS PAGE — Dynamic Filter & Sort System
   -----------------------------------------------------------------------------
   Architecture:
     • All filtering is client-side (DOM already rendered by PHP with all products).
     • Each .product-card has data-attributes for all filterable properties.
     • Filter state is read from checkboxes; applyFilters() runs on every change.
     • Accordion open/close is handled here exclusively — NOT via CSS class on button.
     • The .is-open class is placed on the .filter-options div only (for CSS transition).
     • Arrow rotation is driven by a separate .filter-label--open class on the button.

   Filter Logic (all groups use AND, within each group uses OR):
     • Category    : data-category     — exact string match
     • Size        : data-sizes        — space-separated size codes; card matches
                                         if ANY selected code is present in the list
     • Price       : data-price        — numeric (lowest effective price);
                                         compared against bracket min/max
     • Availability: data-availability — exact value: "in_stock" | "low_stock"
     • Features    : data-features     — space-separated tags; card matches only
                                         if ALL selected features are present (AND)

   Sort runs after filtering. Hidden cards maintain their DOM position but are
   display:none via the .product-card--hidden class so layout isn't disrupted.
   ============================================================================= */

(function () {
  'use strict';

  // ─── Inject critical filter CSS once ──────────────────────────────────────
  function injectFilterStyles() {
    if (document.getElementById('cc-filter-style')) return;
    const style = document.createElement('style');
    style.id = 'cc-filter-style';
    style.textContent = `
      /* Hidden product cards */
      .product-card--hidden {
        display: none !important;
      }

      /* Filter accordion — options container */
      .filter-options {
        max-height: 0 !important;
        overflow: hidden !important;
        margin-top: 0 !important;
        opacity: 0 !important;
        transition:
          max-height 0.35s cubic-bezier(0.25, 1, 0.5, 1),
          margin-top 0.2s ease,
          opacity 0.25s ease !important;
      }
      .filter-options.is-open {
        max-height: 350px !important;
        overflow-y: auto !important;
        margin-top: 14px !important;
        opacity: 1 !important;
      }

      /* Arrow icon on filter label buttons */
      .filter-label i.filter-arrow {
        transition: transform 0.3s cubic-bezier(0.25, 1, 0.5, 1);
        flex-shrink: 0;
        font-size: 16px;
      }
      .filter-label--open i.filter-arrow {
        transform: rotate(180deg);
      }

      /* Active filter group dot indicator */
      .filter-group--active > .filter-label > .filter-group-dot {
        display: inline-block !important;
      }
      .filter-group-dot {
        display: none !important;
        width: 7px;
        height: 7px;
        background: var(--accent-blue);
        border-radius: 50%;
        margin-left: 6px;
        vertical-align: middle;
        flex-shrink: 0;
      }

      /* Active filter label color */
      .filter-group--active > .filter-label {
        color: var(--primary) !important;
      }

      /* No-results panel */
      .products-no-results {
        display: none;
        flex-direction: column;
        align-items: center;
        text-align: center;
        padding: 60px 20px;
        width: 100%;
      }
      .products-no-results.is-visible {
        display: flex !important;
      }

      /* Low-stock badge */
      .low-stock-badge {
        background: #fff3cd !important;
        color: #856404 !important;
        border: 1px solid #ffc107 !important;
      }

      /* Mobile filter panel overlay */
      @media (max-width: 960px) {
        .filters-panel {
          position: fixed !important;
          top: 0 !important;
          left: -100% !important;
          width: min(320px, 88vw) !important;
          height: 100vh !important;
          background: var(--bg) !important;
          border-right: 1px solid var(--border) !important;
          z-index: 9999 !important;
          overflow-y: auto !important;
          padding: 24px 20px 40px !important;
          transition: left 0.4s cubic-bezier(0.25, 1, 0.5, 1) !important;
          box-shadow: 4px 0 30px rgba(45,62,82,0.12) !important;
        }
        .filters-panel.is-active {
          left: 0 !important;
        }
        .filters-close {
          display: flex !important;
        }
        .filter-toggle {
          display: inline-flex !important;
        }
        .filters-panel-backdrop {
          display: block;
          position: fixed;
          inset: 0;
          background: rgba(28,45,66,0.4);
          z-index: 9998;
          opacity: 0;
          pointer-events: none;
          transition: opacity 0.3s ease;
        }
        .filters-panel-backdrop.is-active {
          opacity: 1;
          pointer-events: all;
        }
      }
    `;
    document.head.appendChild(style);
  }

  // ─── Main init ─────────────────────────────────────────────────────────────
  function initProductFilters() {
    const grid = document.getElementById('productsGrid');
    if (!grid) return; // not on the products page

    injectFilterStyles();

    const countEl      = document.getElementById('resultCount');
    const noResults    = document.getElementById('noResults');
    const sortSelect   = document.getElementById('sortBy');
    const clearBtn     = document.getElementById('clearFilters');
    const clearBtnAlt  = document.getElementById('clearFiltersAlt');
    const filterToggle = document.getElementById('filterToggle');
    const filtersPanel = document.getElementById('filtersPanel');
    const filtersClose = document.getElementById('filtersClose');

    // ── Inject mobile backdrop if needed ────────────────────────────────────
    let backdrop = document.querySelector('.filters-panel-backdrop');
    if (!backdrop) {
      backdrop = document.createElement('div');
      backdrop.className = 'filters-panel-backdrop';
      document.body.appendChild(backdrop);
    }

    // ── Accordion: filter group open / close ─────────────────────────────────
    document.querySelectorAll('.filter-label[data-filter]').forEach(btn => {
      const optionsDiv = btn.nextElementSibling;
      if (!optionsDiv || !optionsDiv.classList.contains('filter-options')) return;

      // Ensure arrow icon has the correct class for our CSS to target
      const icon = btn.querySelector('i');
      if (icon) {
        icon.className = ''; // clear all existing classes
        icon.classList.add('ri-arrow-down-s-line', 'filter-arrow');
      }

      // Sync label open-state to match PHP-rendered .is-open on the options div
      if (optionsDiv.classList.contains('is-open')) {
        btn.classList.add('filter-label--open');
      } else {
        btn.classList.remove('filter-label--open');
      }

      btn.addEventListener('click', (e) => {
        e.stopPropagation();
        const isOpen = optionsDiv.classList.contains('is-open');

        if (isOpen) {
          optionsDiv.classList.remove('is-open');
          btn.classList.remove('filter-label--open');
        } else {
          optionsDiv.classList.add('is-open');
          btn.classList.add('filter-label--open');
        }
      });
    });

    // ── Mobile panel open / close ───────────────────────────────────────────
    function openPanel() {
      if (filtersPanel) filtersPanel.classList.add('is-active');
      if (backdrop) backdrop.classList.add('is-active');
      document.body.style.overflow = 'hidden';
    }

    function closePanel() {
      if (filtersPanel) filtersPanel.classList.remove('is-active');
      if (backdrop) backdrop.classList.remove('is-active');
      document.body.style.overflow = '';
    }

    if (filterToggle) filterToggle.addEventListener('click', openPanel);
    if (filtersClose) filtersClose.addEventListener('click', closePanel);
    if (backdrop) backdrop.addEventListener('click', closePanel);

    // ── Helpers ─────────────────────────────────────────────────────────────
    const getAllCards = () => Array.from(grid.querySelectorAll('.product-card'));

    const checkedValues = (name) =>
      Array.from(document.querySelectorAll(`input[name="${name}"]:checked`))
           .map(el => el.value.trim())
           .filter(Boolean);

    // ── Core filter engine ───────────────────────────────────────────────────
    function applyFilters() {
      const selCategories    = checkedValues('filter-category');
      const selSizes         = checkedValues('filter-size');
      const selPriceBrackets = checkedValues('filter-price');
      const selAvailability  = checkedValues('filter-availability');
      const selFeatures      = checkedValues('filter-features');

      let visible = 0;
      const cards = getAllCards();

      cards.forEach(card => {
        let match = true;

        if (selCategories.length > 0) {
          const cardCat = (card.dataset.category || '').trim();
          match = match && selCategories.some(cat =>
            cat.toLowerCase() === cardCat.toLowerCase()
          );
        }

        if (match && selSizes.length > 0) {
          const cardSizes = (card.dataset.sizes || '').trim();
          if (cardSizes === '') {
            match = false;
          } else {
            const sizeList = cardSizes.split(' ');
            match = selSizes.some(s => sizeList.includes(s));
          }
        }

        if (match && selPriceBrackets.length > 0) {
          const cardPrice = parseFloat(card.dataset.price) || 0;
          match = match && selPriceBrackets.some(bracket => {
            const parts = bracket.split('-');
            if (parts.length < 2) return false;
            const bMin = parseFloat(parts[0]);
            const bMax = parseFloat(parts[1]);
            return cardPrice >= bMin && cardPrice <= bMax;
          });
        }

        if (match && selAvailability.length > 0) {
          const cardAvail = (card.dataset.availability || '').trim();
          match = match && selAvailability.includes(cardAvail);
        }

        if (match && selFeatures.length > 0) {
          const cardFeatures = (card.dataset.features || '')
            .trim().split(/\s+/).filter(Boolean);
          match = match && selFeatures.every(f => cardFeatures.includes(f));
        }

        if (match) {
          card.classList.remove('product-card--hidden');
          visible++;
        } else {
          card.classList.add('product-card--hidden');
        }
      });

      if (countEl) countEl.textContent = visible;

      if (noResults) {
        if (visible === 0) {
          noResults.classList.add('is-visible');
          noResults.style.display = 'flex';
        } else {
          noResults.classList.remove('is-visible');
          noResults.style.display = 'none';
        }
      }

      applySort(false);
    }

    // ── Sort engine ──────────────────────────────────────────────────────────
    function applySort(recount = true) {
      if (!sortSelect) return;
      const order = sortSelect.value;
      const cards = getAllCards();

      cards.sort((a, b) => {
        switch (order) {
          case 'price-asc':
            return parseFloat(a.dataset.price || 0) - parseFloat(b.dataset.price || 0);
          case 'price-desc':
            return parseFloat(b.dataset.price || 0) - parseFloat(a.dataset.price || 0);
          case 'newest':
            return parseInt(b.dataset.created || 0) - parseInt(a.dataset.created || 0);
          case 'featured':
          default: {
            const fa = parseInt(a.dataset.featured || 0);
            const fb = parseInt(b.dataset.featured || 0);
            if (fb !== fa) return fb - fa;
            return parseInt(b.dataset.created || 0) - parseInt(a.dataset.created || 0);
          }
        }
      });

      cards.forEach(card => grid.appendChild(card));

      if (recount) {
        const visible = cards.filter(c => !c.classList.contains('product-card--hidden')).length;
        if (countEl) countEl.textContent = visible;
      }
    }

    // ── Active filter group dot indicator ────────────────────────────────────
    function updateFilterGroupState() {
      document.querySelectorAll('.filter-group').forEach(group => {
        const hasActive = group.querySelectorAll('input[type="checkbox"]:checked').length > 0;
        group.classList.toggle('filter-group--active', hasActive);
      });
    }

    // ── Bind all checkbox changes ─────────────────────────────────────────────
    document.querySelectorAll('input[name^="filter-"]').forEach(cb => {
      cb.addEventListener('change', () => {
        applyFilters();
        updateFilterGroupState();
      });
    });

    if (sortSelect) {
      sortSelect.addEventListener('change', () => applySort(true));
    }

    // ── Clear all filters ─────────────────────────────────────────────────────
    function clearAll() {
      document.querySelectorAll('input[name^="filter-"]:checked').forEach(cb => {
        cb.checked = false;
      });
      applyFilters();
      updateFilterGroupState();
    }

    if (clearBtn)    clearBtn.addEventListener('click', clearAll);
    if (clearBtnAlt) clearBtnAlt.addEventListener('click', clearAll);

    // ── Handle URL query params ───────────────────────────────────────────────
    function applyUrlParams() {
      const params = new URLSearchParams(window.location.search);

      const typeParam = params.get('type');
      if (typeParam) {
        const typeToCategory = {
          'overnight': 'Overnight Protection',
          'everyday' : 'Everyday Comfort',
          'rash-free': 'Rash-Free Care',
        };
        const cat = typeToCategory[typeParam.toLowerCase()];
        if (cat) {
          const cb = document.querySelector(`input[name="filter-category"][value="${cat}"]`);
          if (cb) cb.checked = true;
        }
      }

      const catParam = params.get('category');
      if (catParam) {
        const cbs = document.querySelectorAll('input[name="filter-category"]');
        cbs.forEach(cb => {
          if (cb.value.toLowerCase() === catParam.toLowerCase()) cb.checked = true;
        });
      }

      const sizeParam = params.get('size');
      if (sizeParam) {
        const cb = document.querySelector(
          `input[name="filter-size"][value="${sizeParam.toUpperCase()}"]`
        );
        if (cb) cb.checked = true;
      }
    }

    // ── Init sequence ─────────────────────────────────────────────────────────
    applyUrlParams();
    updateFilterGroupState();
    applyFilters();
    applySort(false);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initProductFilters);
  } else {
    initProductFilters();
  }

}());
