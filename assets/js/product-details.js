/**
 * Product Details Page Interactions
 */
document.addEventListener("DOMContentLoaded", function () {
  const mainImage = document.getElementById("mainImage");
  const thumbnails = document.querySelectorAll(".thumb");
  const sizeButtons = document.querySelectorAll(".size-btn");
  const qtyMinus = document.getElementById("qtyMinus");
  const qtyPlus = document.getElementById("qtyPlus");
  const qtyInput = document.getElementById("qtyInput");
  const addCartBtn = document.getElementById("addCartBtn");
  // Product Details Accordion Toggle
  const accordionTriggers = document.querySelectorAll(".accordion-trigger");
  accordionTriggers.forEach((trigger) => {
    trigger.addEventListener("click", function () {
      const targetId = this.getAttribute("data-accordion");
      const content = document.getElementById(targetId);
      if (!content) return;

      const isActive = this.classList.contains("is-active");

      // Auto-close other accordion items within the details-accordion
      const parentAccordion = this.closest(".details-accordion");
      if (parentAccordion) {
        const otherTriggers = parentAccordion.querySelectorAll(".accordion-trigger");
        const otherContents = parentAccordion.querySelectorAll(".accordion-content");
        
        otherTriggers.forEach((t) => {
          if (t !== this) t.classList.remove("is-active");
        });
        otherContents.forEach((c) => {
          if (c !== content) c.classList.remove("is-active");
        });
      }

      // Toggle current item
      if (isActive) {
        this.classList.remove("is-active");
        content.classList.remove("is-active");
      } else {
        this.classList.add("is-active");
        content.classList.add("is-active");
      }
    });
  });

  // Gallery thumbnail switching — smooth fade
  thumbnails.forEach((thumb) => {
    thumb.addEventListener("click", function () {
      const newSrc = this.dataset.src;
      if (mainImage && newSrc) {
        mainImage.style.opacity = "0";
        mainImage.style.transform = "scale(0.98)";
        setTimeout(() => {
          mainImage.src = newSrc;
          mainImage.style.opacity = "1";
          mainImage.style.transform = "scale(1)";
        }, 180);
      }

      // Update active state
      thumbnails.forEach((t) => t.removeAttribute("data-active"));
      this.setAttribute("data-active", "true");
    });
  });

  // Smooth image crossfade style
  if (mainImage) {
    mainImage.style.transition = "opacity 0.2s ease, transform 0.2s ease";
  }

  // Size selector
  sizeButtons.forEach((btn) => {
    btn.addEventListener("click", function () {
      sizeButtons.forEach((b) => b.removeAttribute("data-active"));
      this.setAttribute("data-active", "true");

      // Update displayed price based on selected size variant
      const newPrice = this.getAttribute("data-price");
      if (newPrice) {
        const priceCurrentEl = document.querySelector(".price-current");
        if (priceCurrentEl) {
          priceCurrentEl.textContent =
            "₹" +
            parseFloat(newPrice).toLocaleString("en-IN", {
              minimumFractionDigits: 0,
              maximumFractionDigits: 0,
            });
        }

        const origPrice = this.getAttribute("data-original-price");
        const priceOriginalEl = document.querySelector(".price-original");
        const priceDiscountEl = document.querySelector(".price-discount");
        if (origPrice && priceOriginalEl) {
          priceOriginalEl.style.display = "";
          priceOriginalEl.textContent =
            "₹" +
            parseFloat(origPrice).toLocaleString("en-IN", {
              minimumFractionDigits: 0,
              maximumFractionDigits: 0,
            });

          if (priceDiscountEl) {
            const disc = Math.round(
              ((parseFloat(origPrice) - parseFloat(newPrice)) /
                parseFloat(origPrice)) *
              100,
            );
            priceDiscountEl.textContent = `-${disc}%`;
            priceDiscountEl.style.display = "";
          }
        } else {
          if (priceOriginalEl) priceOriginalEl.style.display = "none";
          if (priceDiscountEl) priceDiscountEl.style.display = "none";
        }
      }
    });
  });

  // Quantity controls
  if (qtyMinus && qtyInput) {
    qtyMinus.addEventListener("click", () => {
      const current = parseInt(qtyInput.value);
      if (current > 1) qtyInput.value = current - 1;
    });
  }

  if (qtyPlus && qtyInput) {
    qtyPlus.addEventListener("click", () => {
      const current = parseInt(qtyInput.value);
      if (current < 99) qtyInput.value = current + 1;
    });
  }

  // Add to Cart with feedback
  if (addCartBtn) {
    addCartBtn.addEventListener("click", function () {
      const qty = qtyInput ? parseInt(qtyInput.value) || 1 : 1;
      const activeSizeBtn = document.querySelector(
        '.size-btn[data-active="true"]',
      );

      // sizeCode always uppercase to match data-size attribute from PHP (strtoupper)
      const sizeCode = activeSizeBtn
        ? (activeSizeBtn.getAttribute("data-size") || "M").toUpperCase()
        : "M";
      // sizeLabel is the full description e.g. "Medium (M)" — taken from data-size's desc span only
      const sizeLabel = activeSizeBtn
        ? activeSizeBtn.querySelector(".size-desc")?.textContent.trim() ||
        sizeCode
        : sizeCode;

      const baseId = this.getAttribute("data-product-id") || "product";
      // ID uses lowercase for consistency e.g. slug-m, slug-nb, slug-s
      const productId = `${baseId}-${sizeCode.toLowerCase()}`;

      const name =
        document.querySelector(".product-title")?.textContent.trim() || "";

      const priceEl = document.querySelector(".price-current");
      const originalPriceEl = document.querySelector(".price-original");

      const priceVal = priceEl
        ? parseFloat(priceEl.textContent.replace(/[^\d.]/g, ""))
        : 0;
      const originalPriceVal =
        originalPriceEl && originalPriceEl.style.display !== "none"
          ? parseFloat(originalPriceEl.textContent.replace(/[^\d.]/g, ""))
          : null;

      const image = mainImage ? mainImage.src : "";

      if (window.CloudCushCart) {
        window.CloudCushCart.addItem({
          id: productId,
          name: name,
          size: sizeLabel,
          price: priceVal,
          originalPrice: originalPriceVal,
          image: image,
          quantity: qty,
        });
      }

      const originalHTML = this.innerHTML;
      this.innerHTML = '<i class="ri-checkbox-circle-line"></i> Added to Cart';
      this.classList.add("btn-added");
      this.disabled = true;

      setTimeout(() => {
        this.innerHTML = originalHTML;
        this.classList.remove("btn-added");
        this.disabled = false;
      }, 2000);
    });
  }

  // (in case GSAP hasn't loaded or ScrollTrigger hasn't fired)
  const ensureVisible = () => {
    document.querySelectorAll(".feature-block, .related-card").forEach((el) => {
      const style = window.getComputedStyle(el);
      if (parseFloat(style.opacity) < 0.1) {
        el.style.opacity = "1";
        el.style.transform = "none";
        el.style.visibility = "visible";
      }
    });
  };
  // Run after 2s as a safety fallback if GSAP animations stall
  setTimeout(ensureVisible, 2000);

  // Related product cards redirect
  document.querySelectorAll(".related-card").forEach((card) => {
    card.style.cursor = "pointer";
    card.addEventListener("click", function () {
      const link = this.querySelector("a");
      if (link && link.getAttribute("href")) {
        window.location.href = link.getAttribute("href");
      } else {
        window.location.href = "product-details.php";
      }
    });
  });

  document.querySelectorAll(".btn-view-product").forEach((btn) => {
    btn.addEventListener("click", function (e) {
      e.stopPropagation();
      const card = this.closest(".related-card");
      const link = card ? card.querySelector("a") : null;
      if (link && link.getAttribute("href")) {
        window.location.href = link.getAttribute("href");
      } else {
        window.location.href = "product-details.php";
      }
    });
  });

  // GSAP animations on scroll
  if (typeof gsap !== "undefined" && typeof ScrollTrigger !== "undefined") {
    gsap.registerPlugin(ScrollTrigger);

    // Animate feature blocks — use fromTo so elements are never stuck at opacity:0
    gsap.utils.toArray(".feature-block").forEach((block, index) => {
      gsap.fromTo(
        block,
        { opacity: 0, x: -24 },
        {
          scrollTrigger: {
            trigger: block,
            start: "top 88%",
            toggleActions: "play none none none",
          },
          duration: 0.5,
          x: 0,
          opacity: 1,
          delay: index * 0.07,
          ease: "power3.out",
        },
      );
    });

    // Animate related cards — use fromTo so elements never stay invisible
    gsap.utils.toArray(".related-card").forEach((card, index) => {
      gsap.fromTo(
        card,
        { opacity: 0, y: 20 },
        {
          scrollTrigger: {
            trigger: card,
            start: "top 92%",
            toggleActions: "play none none none",
          },
          duration: 0.5,
          y: 0,
          opacity: 1,
          delay: index * 0.06,
          ease: "power3.out",
        },
      );
    });
  }

  // Prevent text selection on draggable images
  if (mainImage) {
    mainImage.draggable = false;
  }
  thumbnails.forEach((thumb) => {
    const img = thumb.querySelector("img");
    if (img) {
      img.draggable = false;
    }
  });

  /* ===========================================================================
     BUY NOW — full flow (with guest checkout support)
     ---------------------------------------------------------------------------
     Flow for logged-in users:
       1. Click "Buy Now" → modal opens with saved addresses
       2. Pick/add address → Place Order

     Flow for guests (not logged in):
       1. Click "Buy Now" → guest email prompt (SweetAlert)
       2. Enter email (+ optional name) → modal opens
       3. Add address → Place Order with guest_email in payload
       4. Server auto-creates account (pw: 123456) → order placed
       5. Success dialog shows account creation info
     =========================================================================== */
  const cfg = window.ccBuyNow;
  const buyNowBtn = document.querySelector('.btn-buy-now');
  const overlay = document.getElementById('buyNowOverlay');
  if (!cfg || !buyNowBtn || !overlay) return; // not on product-details, bail

  const bnClose = document.getElementById('bnClose');
  const bnCancel = document.getElementById('bnCancel');
  const bnConfirm = document.getElementById('bnConfirm');
  const bnToggleAddr = document.getElementById('bnToggleAddr');
  const bnNewForm = document.getElementById('bnNewAddrForm');
  const bnAddrList = document.getElementById('bnAddressList');

  // State
  let currentSelectedAddressId = null;
  let newFormVisible = false;
  let submitting = false;
  let guestEmail = '';  // set when guest provides email
  let guestName  = '';

  // ── Open / close helpers ──────────────────────────────────────────────────
  function openModal() {
    // Populate summary strip from current DOM state
    const activeSize = document.querySelector('.size-btn[data-active="true"]');
    const sizeLabel = activeSize ? (activeSize.querySelector('.size-desc')?.textContent.trim() || activeSize.dataset.size) : '';
    const qty = parseInt(document.getElementById('qtyInput')?.value) || 1;
    const price = parseFloat(document.querySelector('.price-current')?.textContent.replace(/[^\d.]/g, '')) || 0;

    document.getElementById('bnProductImg').src = cfg.image;
    document.getElementById('bnProductName').textContent = cfg.productName;
    document.getElementById('bnProductMeta').textContent = [
      sizeLabel ? 'Size: ' + sizeLabel : '',
      'Qty: ' + qty,
    ].filter(Boolean).join(' · ');
    document.getElementById('bnProductPrice').textContent =
      '\u20b9' + (price * qty).toLocaleString('en-IN', { maximumFractionDigits: 0 });

    // Load saved addresses (only if logged in; guest shows new-address form directly)
    if (cfg.loggedIn) {
      loadAddresses();
    } else {
      // Guest: skip address list, go directly to new-address form
      bnAddrList.innerHTML = '';
      showNewForm();
    }

    overlay.classList.add('is-open');
    document.body.style.overflow = 'hidden';
    if (window.lenis) window.lenis.stop();
  }

  function closeModal() {
    overlay.classList.remove('is-open');
    document.body.style.overflow = '';
    if (window.lenis) window.lenis.start();
    submitting = false;
    bnConfirm.disabled = false;
    bnConfirm.innerHTML = '<i class="ri-check-line"></i> Place Order';
  }

  // ── Load saved addresses via fetch ──────────────────────────────────────────
  function loadAddresses() {
    bnAddrList.innerHTML = '<p class="bn-loading">Loading addresses\u2026</p>';
    currentSelectedAddressId = null;
    hideNewForm();

    fetch('order-handler.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'get_addresses' }),
    })
      .then(r => r.json())
      .then(data => {
        renderAddresses(data.addresses || []);
      })
      .catch(() => {
        renderAddresses([]);
      });
  }

  function renderAddresses(addresses) {
    bnAddrList.innerHTML = '';

    if (addresses.length === 0) {
      bnAddrList.innerHTML = '<p class="bn-no-addr">No saved addresses. Add one below.</p>';
      showNewForm();
      return;
    }

    addresses.forEach((addr, i) => {
      const isDefault = addr.is_default == 1;
      const addrLine = [
        addr.address_line_1,
        addr.address_line_2,
        addr.city + ', ' + addr.state,
        addr.zip_code,
      ].filter(Boolean).join(', ');

      const card = document.createElement('div');
      card.className = 'bn-addr-card' + (isDefault || i === 0 ? ' is-selected' : '');
      card.dataset.id = addr.id;
      card.innerHTML = `
        <div class="bn-addr-radio"></div>
        <div class="bn-addr-body">
          ${isDefault ? '<span class="bn-addr-default">\u2605 Default</span>' : ''}
          <p class="bn-addr-name">${escHtml(addr.full_name)}${addr.phone ? ' \u00b7 ' + escHtml(addr.phone) : ''}</p>
          <p class="bn-addr-line">${escHtml(addrLine)}</p>
        </div>
      `;
      card.addEventListener('click', () => selectAddress(card, addr.id));
      bnAddrList.appendChild(card);
    });

    // Auto-select default (or first)
    const first = bnAddrList.querySelector('.bn-addr-card.is-selected');
    if (first) currentSelectedAddressId = parseInt(first.dataset.id);
  }

  function selectAddress(card, id) {
    bnAddrList.querySelectorAll('.bn-addr-card').forEach(c => c.classList.remove('is-selected'));
    card.classList.add('is-selected');
    currentSelectedAddressId = parseInt(id);
    hideNewForm();
  }

  function showNewForm() {
    bnNewForm.style.display = 'block';
    if (cfg.loggedIn) {
      bnToggleAddr.textContent = '\u2212 Cancel new address';
    } else {
      // Guest: hide toggle button (they always use the new form)
      bnToggleAddr.style.display = 'none';
    }
    newFormVisible = true;
    currentSelectedAddressId = null;
    bnAddrList.querySelectorAll('.bn-addr-card').forEach(c => c.classList.remove('is-selected'));
  }

  function hideNewForm() {
    bnNewForm.style.display = 'none';
    bnToggleAddr.textContent = '+ Add a new address';
    bnToggleAddr.style.display = '';
    newFormVisible = false;
  }

  bnToggleAddr.addEventListener('click', () => {
    if (newFormVisible) {
      hideNewForm();
      // Re-select first saved address if any
      const first = bnAddrList.querySelector('.bn-addr-card');
      if (first) selectAddress(first, parseInt(first.dataset.id));
    } else {
      showNewForm();
    }
  });

  // ── Place Order ────────────────────────────────────────────────────────────
  bnConfirm.addEventListener('click', () => {
    if (submitting) return;

    const activeSize = document.querySelector('.size-btn[data-active="true"]');
    const sizeLabel = activeSize ? (activeSize.querySelector('.size-desc')?.textContent.trim() || activeSize.dataset.size) : '';
    const qty = parseInt(document.getElementById('qtyInput')?.value) || 1;
    const price = parseFloat(document.querySelector('.price-current')?.textContent.replace(/[^\d.]/g, '')) || 0;
    const origPriceEl = document.querySelector('.price-original');
    const origPrice = origPriceEl && origPriceEl.style.display !== 'none'
      ? parseFloat(origPriceEl.textContent.replace(/[^\d.]/g, '')) : null;

    const payload = {
      action: 'buy_now',
      product_id: cfg.productId,
      product_name: cfg.productName,
      size: sizeLabel,
      price: price,
      original_price: origPrice,
      image: cfg.image,
      quantity: qty,
    };

    // Attach guest credentials if this is a guest checkout
    if (!cfg.loggedIn && guestEmail) {
      payload.guest_email = guestEmail;
      if (guestName) payload.guest_name = guestName;
    }

    if (newFormVisible || currentSelectedAddressId === null) {
      // Validate and send inline form
      const name  = document.getElementById('bnAddrName').value.trim();
      const line1 = document.getElementById('bnAddrLine1').value.trim();
      const city  = document.getElementById('bnAddrCity').value.trim();
      const state = document.getElementById('bnAddrState').value.trim();
      const zip   = document.getElementById('bnAddrZip').value.trim();

      if (!name || !line1 || !city || !state || !zip) {
        Swal.fire({
          icon: 'warning', title: 'Address incomplete',
          text: 'Please fill in all required address fields.',
          customClass: { popup: 'swal2-premium-popup', confirmButton: 'swal2-confirm-primary' },
          buttonsStyling: false, confirmButtonText: 'OK'
        });
        return;
      }

      payload.new_address = {
        full_name:       name,
        phone:           document.getElementById('bnAddrPhone').value.trim(),
        address_line_1:  line1,
        address_line_2:  document.getElementById('bnAddrLine2').value.trim(),
        city, state,
        zip_code:        zip,
        country:         document.getElementById('bnAddrCountry').value.trim() || 'India',
        save:            cfg.loggedIn && document.getElementById('bnSaveAddr').checked,
      };
    } else {
      payload.address_id = currentSelectedAddressId;
    }

    submitting = true;
    bnConfirm.disabled = true;
    bnConfirm.innerHTML = '<i class="ri-loader-4-line"></i> Placing order\u2026';

    fetch('order-handler.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload),
    })
      .then(r => r.json().then(d => ({ ok: r.ok, data: d })))
      .then(({ ok, data }) => {
        if (ok && data.success) {
          closeModal();

          const successHtml = cfg.loggedIn
            ? `<p style="font-size:14px;color:#475569;">
                 Thank you! We've received your order for<br>
                 <strong>${escHtml(cfg.productName)}</strong><br>
                 Total: <strong>\u20b9${data.order_total}</strong>
               </p>`
            : `<p style="font-size:14px;color:#475569;">
                 Thank you! We've received your order for<br>
                 <strong>${escHtml(cfg.productName)}</strong><br>
                 Total: <strong>\u20b9${data.order_total}</strong><br><br>
                 <strong>Your account has been created.</strong><br>
                 Email: <strong>${escHtml(guestEmail)}</strong><br>
                 Temporary password: <strong>123456</strong><br>
                 <span style="font-size:12px;color:#9ca3af;">Please change your password after logging in.</span>
               </p>`;

          Swal.fire({
            icon: 'success', title: 'Order Placed!',
            html: successHtml,
            customClass: { popup: 'swal2-premium-popup', confirmButton: 'swal2-confirm-primary' },
            buttonsStyling: false, confirmButtonText: 'View My Orders'
          }).then(() => { window.location.href = 'account.php?tab=order-history'; });

        } else if (data.need_guest_email) {
          // Server says we need guest email (should not normally hit this in Buy Now
          // since we ask email before opening modal, but handle gracefully)
          closeModal();
          guestEmail = '';
          guestName  = '';
          buyNowBtn.click(); // restart the flow

        } else if (data.redirect) {
          Swal.fire({
            icon: 'info', title: 'Login Required', text: data.message,
            customClass: { popup: 'swal2-premium-popup', confirmButton: 'swal2-confirm-primary' },
            buttonsStyling: false, confirmButtonText: 'Log In'
          }).then(() => { window.location.href = data.redirect; });

        } else {
          Swal.fire({
            icon: 'error', title: 'Order Failed',
            text: data.message || 'Something went wrong. Please try again.',
            customClass: { popup: 'swal2-premium-popup', confirmButton: 'swal2-confirm-primary' },
            buttonsStyling: false, confirmButtonText: 'OK'
          });
          submitting = false;
          bnConfirm.disabled = false;
          bnConfirm.innerHTML = '<i class="ri-check-line"></i> Place Order';
        }
      })
      .catch(() => {
        Swal.fire({
          icon: 'error', title: 'Connection Error',
          text: 'Could not reach the server. Please try again.',
          customClass: { popup: 'swal2-premium-popup', confirmButton: 'swal2-confirm-primary' },
          buttonsStyling: false, confirmButtonText: 'OK'
        });
        submitting = false;
        bnConfirm.disabled = false;
        bnConfirm.innerHTML = '<i class="ri-check-line"></i> Place Order';
      });
  });

  // ── Buy Now button click ──────────────────────────────────────────────────────
  buyNowBtn.addEventListener('click', () => {
    if (cfg.loggedIn) {
      // Logged-in: open modal directly
      openModal();
    } else {
      // Guest: ask for email first, then open modal
      Swal.fire({
        icon: 'info',
        title: 'Continue as Guest',
        html: `
          <p style="font-size:14px;color:#475569;margin-bottom:16px;">
            Enter your email to place your order. We'll create an account
            automatically so you can track your orders later.
          </p>
          <div style="text-align:left;margin-bottom:10px;">
            <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:6px;">
              Your Name (optional)
            </label>
            <input id="swal-bn-name" type="text" class="swal2-input"
              placeholder="Your name" autocomplete="name"
              style="margin:0;width:100%;box-sizing:border-box;">
          </div>
          <div style="text-align:left;">
            <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:6px;">
              Email Address *
            </label>
            <input id="swal-bn-email" type="email" class="swal2-input"
              placeholder="you@example.com" autocomplete="email"
              style="margin:0;width:100%;box-sizing:border-box;">
          </div>
          <p style="font-size:11px;color:#9ca3af;margin-top:12px;">
            A temporary password <strong>123456</strong> will be set.
            You can change it from your account after checkout.
          </p>
        `,
        customClass: {
          popup: 'swal2-premium-popup',
          confirmButton: 'swal2-confirm-primary',
          cancelButton: 'swal2-cancel-secondary'
        },
        buttonsStyling: false,
        confirmButtonText: 'Continue to Order',
        cancelButtonText: 'Log In Instead',
        showCancelButton: true,
        focusConfirm: false,
        didOpen: () => {
          const emailInput = document.getElementById('swal-bn-email');
          const nameInput  = document.getElementById('swal-bn-name');
          if (emailInput) setTimeout(() => emailInput.focus(), 100);

          if (nameInput) {
            nameInput.addEventListener('keydown', e => {
              if (e.key === 'Enter') { e.preventDefault(); if (emailInput) emailInput.focus(); }
            });
          }
          if (emailInput) {
            emailInput.addEventListener('keydown', e => {
              if (e.key === 'Enter') { e.preventDefault(); Swal.clickConfirm(); }
            });
          }
        },
        preConfirm: () => {
          const email = document.getElementById('swal-bn-email')?.value.trim() || '';
          const name  = document.getElementById('swal-bn-name')?.value.trim()  || '';
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
          guestEmail = result.value.email;
          guestName  = result.value.name || '';
          openModal();
        } else if (result.dismiss === Swal.DismissReason.cancel) {
          // Redirect to login, come back to this product after
          const returnUrl = 'product-details.php' + (cfg.productSlug ? '?slug=' + encodeURIComponent(cfg.productSlug) : '?id=' + cfg.productId);
          window.location.href = 'login.php?redirect=' + encodeURIComponent(returnUrl);
        }
      });
    }
  });

  // ── Close triggers ──────────────────────────────────────────────────────────
  bnClose.addEventListener('click', closeModal);
  bnCancel.addEventListener('click', closeModal);
  overlay.addEventListener('click', e => { if (e.target === overlay) closeModal(); });
  document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });

  // Fix: prevent Lenis from intercepting wheel events inside the modal
  document.querySelector('.bn-modal').addEventListener('wheel', function (e) {
    e.stopPropagation();
  }, { passive: true });

  // ── Utility ─────────────────────────────────────────────────────────────────
  function escHtml(str) {
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

});
