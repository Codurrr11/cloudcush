document.addEventListener("DOMContentLoaded", () => {
  // 1. Initialize Smooth Scroll (Lenis)
  if (typeof window.initSmoothScroll === "function") {
    window.initSmoothScroll();
  }

  // 2. Initialize Navbar & Menus
  if (typeof window.initNavbar === "function") {
    window.initNavbar();
  }

  // 3. Initialize GSAP Entrance reveals & loops
  if (typeof window.initAnimations === "function") {
    window.initAnimations();
  }

  // 4. Hook Parallax calculations into Lenis Scroll event listener
  //
  // SCROLL JITTER FIX:
  // ─────────────────────────────────────────────────────────────────────────
  // Previous issue: gsap.to() was called on EVERY scroll tick with
  // duration:0.4. This spawned hundreds of overlapping short tweens per
  // second, causing competing animations to fight each other — resulting
  // in the jittery/stuttering scroll behavior.
  //
  // Fix: use gsap.quickTo() — creates a single persistent tween per
  // property that can be retargeted without spawning new tweens. This is
  // the GSAP-recommended approach for scroll/mouse-driven animations.
  // ─────────────────────────────────────────────────────────────────────────

  if (window.lenis) {

    // ── Hero parallax: only initialise quickTo functions if hero exists ──
    const heroHasElements =
      document.querySelector(".baby-main") &&
      document.querySelector(".baby-ghost") &&
      document.querySelector(".hero-left-text") &&
      document.querySelector(".hero-col-right");

    let qBabyMainY, qBabyMainScale, qBabyGhostY,
        qHeroLeftY, qHeroLeftOpacity, qHeroRightY, qHeroRightOpacity;

    if (heroHasElements) {
      // Create one reusable quickTo setter per property per target.
      // duration here is the smoothing lag (seconds) — identical to the
      // old duration:0.4 but without spawning new tweens on each tick.
      qBabyMainY       = gsap.quickTo(".baby-main",     "y",       { duration: 0.4, overwrite: "auto" });
      qBabyMainScale   = gsap.quickTo(".baby-main",     "scale",   { duration: 0.4, overwrite: "auto" });
      qBabyGhostY      = gsap.quickTo(".baby-ghost",    "y",       { duration: 0.4, overwrite: "auto" });
      qHeroLeftY       = gsap.quickTo(".hero-left-text","y",       { duration: 0.4, overwrite: "auto" });
      qHeroLeftOpacity = gsap.quickTo(".hero-left-text","opacity", { duration: 0.4, overwrite: "auto" });
      qHeroRightY      = gsap.quickTo(".hero-col-right","y",       { duration: 0.4, overwrite: "auto" });
      qHeroRightOpacity= gsap.quickTo(".hero-col-right","opacity", { duration: 0.4, overwrite: "auto" });
    }

    window.lenis.on("scroll", (e) => {
      const scrollY = e.scroll;

      // Update sticky navbar state
      if (typeof window.updateHeaderState === "function") {
        window.updateHeaderState(scrollY);
      }

      // Update diaper rotation velocity reaction
      if (typeof window.updateDiaperScrollVelocity === "function") {
        window.updateDiaperScrollVelocity(e.velocity);
      }

      // Parallax — homepage hero elements only
      if (heroHasElements) {
        qBabyMainY(scrollY * 0.12 - 6);
        qBabyMainScale(Math.max(0.9, 1 - scrollY * 0.0002));
        qBabyGhostY(scrollY * 0.2 + 4);
        qHeroLeftY(scrollY * 0.06);
        qHeroLeftOpacity(Math.max(0, 1 - scrollY * 0.0025));
        qHeroRightY(scrollY * 0.06);
        qHeroRightOpacity(Math.max(0, 1 - scrollY * 0.0025));
      }
    });
  }

  // 5. Handle Newsletter Form Submissions via AJAX
  const newsletterForm = document.querySelector(".blog-newsletter-form");
  if (newsletterForm) {
    newsletterForm.addEventListener("submit", (e) => {
      e.preventDefault();
      
      const emailInput = newsletterForm.querySelector(".newsletter-email-input");
      const submitBtn = newsletterForm.querySelector("button[type='submit']");
      const emailVal = emailInput ? emailInput.value.trim() : "";
      
      if (!emailVal) return;
      
      // Disable inputs and show loading state
      if (emailInput) emailInput.disabled = true;
      if (submitBtn) {
        submitBtn.disabled = true;
        var originalBtnText = submitBtn.textContent;
        submitBtn.textContent = "Saving...";
      }
      
      fetch("newsletter-subscribe.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json"
        },
        body: JSON.stringify({ email: emailVal })
      })
      .then(response => {
        if (!response.ok) {
          return response.json().then(errData => {
            throw new Error(errData.message || "Something went wrong.");
          }).catch(() => {
            throw new Error("Server error. Please try again.");
          });
        }
        return response.json();
      })
      .then(data => {
        Swal.fire({
          title: data.status === "success" ? "Thank You!" : "Note",
          text: data.message,
          icon: data.status === "success" ? "success" : "info",
          customClass: {
            popup: "swal2-premium-popup",
            confirmButton: "swal2-confirm-primary"
          },
          buttonsStyling: false,
          confirmButtonText: "Close"
        });
        
        if (data.status === "success" && newsletterForm) {
          newsletterForm.reset();
        }
      })
      .catch(error => {
        Swal.fire({
          title: "Oops...",
          text: error.message || "Failed to submit. Please try again later.",
          icon: "error",
          customClass: {
            popup: "swal2-premium-popup",
            confirmButton: "swal2-confirm-primary"
          },
          buttonsStyling: false,
          confirmButtonText: "Ok"
        });
      })
      .finally(() => {
        // Re-enable form inputs
        if (emailInput) emailInput.disabled = false;
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.textContent = originalBtnText;
        }
      });
    });
  }
});
