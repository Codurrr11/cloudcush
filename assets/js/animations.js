// =============================================================================
// CloudCush — animations.js
// =============================================================================
gsap.registerPlugin(ScrollTrigger);

window.initAnimations = () => {
  // ---------------------------------------------------------------------------
  // HERO ENTRANCE
  // ---------------------------------------------------------------------------
  const heroTitle = document.querySelector(".hero-title");
  if (heroTitle) {
    const tl = gsap.timeline({ defaults: { ease: "power4.out" } });

    gsap.set(".announcement-bar", { yPercent: -100 });
    gsap.set(".site-header", { yPercent: -100, opacity: 0 });
    gsap.set(".hero-title", { y: 100, opacity: 0 });
    gsap.set(".grid-col-line", { scaleY: 0 });
    gsap.set(".hero-left-text", { x: -50, opacity: 0 });
    gsap.set(".hero-right-text", { x: 50, opacity: 0 });
    gsap.set(".baby-main", { xPercent: -50 });
    gsap.set(".baby-ghost", { rotation: 6 });
    gsap.set('.baby-main-layer[data-index="1"]', { scale: 0.8, opacity: 0 });
    gsap.set('.baby-ghost-layer[data-index="1"]', { scale: 0.9, opacity: 0 });
    gsap.set(".hero-col-right .btn-pill", { y: 30, opacity: 0 });

    tl.to(".announcement-bar", { yPercent: 0, duration: 0.6 })
      .to(".site-header", { yPercent: 0, opacity: 1, duration: 0.8 }, "-=0.2")
      .to(
        ".hero-title",
        { y: 0, opacity: 1, duration: 1.2, ease: "power3.out" },
        "-=0.4",
      )
      .to(
        ".grid-col-line",
        {
          scaleY: 1,
          transformOrigin: "top center",
          duration: 1.2,
          stagger: 0.1,
          ease: "power2.inOut",
        },
        "-=0.9",
      )
      .to(".hero-left-text", { x: 0, opacity: 1, duration: 0.8 }, "-=0.8")
      .to(".hero-right-text", { x: 0, opacity: 1, duration: 0.8 }, "-=0.8")
      .to(
        '.baby-main-layer[data-index="1"]',
        { scale: 1, opacity: 1, duration: 1.2, ease: "back.out(1.1)" },
        "-=0.7",
      )
      .to(
        '.baby-ghost-layer[data-index="1"]',
        { scale: 1.2, opacity: 0.12, duration: 1.5, ease: "power2.out" },
        "-=1.0",
      )
      .to(
        ".hero-col-right .btn-pill",
        { y: 0, opacity: 1, duration: 0.8 },
        "-=0.9",
      );

    gsap.to(".baby-image-wrapper", {
      y: -12,
      duration: 4,
      repeat: -1,
      yoyo: true,
      ease: "sine.inOut",
    });
    gsap.to(".baby-ghost-layer", {
      x: 10,
      duration: 5,
      repeat: -1,
      yoyo: true,
      ease: "sine.inOut",
    });

    // Image slider
    let currentActive = 1;
    const totalImages = 3;
    setInterval(() => {
      const nextActive = currentActive === totalImages ? 1 : currentActive + 1;
      const cMain = document.querySelector(
        `.baby-main-layer[data-index="${currentActive}"]`,
      );
      const nMain = document.querySelector(
        `.baby-main-layer[data-index="${nextActive}"]`,
      );
      const cGhost = document.querySelector(
        `.baby-ghost-layer[data-index="${currentActive}"]`,
      );
      const nGhost = document.querySelector(
        `.baby-ghost-layer[data-index="${nextActive}"]`,
      );
      if (nMain) {
        nMain.style.display = "block";
        gsap.fromTo(
          nMain,
          { opacity: 0, scale: 0.95 },
          { opacity: 1, scale: 1, duration: 1.2, ease: "power2.out" },
        );
      }
      if (nGhost) {
        nGhost.style.display = "block";
        gsap.fromTo(
          nGhost,
          { opacity: 0, scale: 1.1 },
          { opacity: 0.12, scale: 1.2, duration: 1.4, ease: "power2.out" },
        );
      }
      if (cMain)
        gsap.to(cMain, {
          opacity: 0,
          scale: 0.9,
          duration: 1.2,
          ease: "power2.out",
          onComplete: () => {
            cMain.style.display = "none";
          },
        });
      if (cGhost)
        gsap.to(cGhost, {
          opacity: 0,
          scale: 1.05,
          duration: 1.4,
          ease: "power2.out",
          onComplete: () => {
            cGhost.style.display = "none";
          },
        });
      currentActive = nextActive;
    }, 3500);
  } // end heroTitle guard

  const mm = gsap.matchMedia();

  if (document.querySelector(".hero")) {
    mm.add("(min-width: 769px)", () => {
      // Pin hero section while the user scrolls past it.
      // anticipatePin avoids the brief jump that can occur when Lenis
      // hands off scroll position to GSAP's scrub.
      ScrollTrigger.create({
        trigger: ".hero",
        start: "top 110px",
        end: "bottom 110px",
        pin: true,
        pinSpacing: false,
        anticipatePin: 1,
        invalidateOnRefresh: true,
      });
    });
  }

  // ---------------------------------------------------------------------------
  // SHOWCASE
  // ---------------------------------------------------------------------------
  if (document.querySelector(".showcase-section")) {
    gsap.set(".showcase-title", { y: 60, opacity: 0 });
    gsap.set(".feature-badge", { y: 30, opacity: 0 });
    gsap.set(".showcase-desc", { y: 30, opacity: 0 });
    gsap.set(".showcase-col-left .btn-pill", { y: 30, opacity: 0 });

    const showcaseTl = gsap.timeline({
      scrollTrigger: {
        trigger: ".showcase-section",
        start: "top 75%",
        toggleActions: "play none none reverse",
      },
    });
    showcaseTl
      .to(".showcase-title", {
        y: 0,
        opacity: 1,
        duration: 1.1,
        ease: "power3.out",
      })
      .to(
        ".feature-badge",
        { y: 0, opacity: 1, duration: 0.8, ease: "power3.out" },
        "-=0.8",
      )
      .to(
        ".showcase-desc",
        { y: 0, opacity: 1, duration: 0.8, stagger: 0.15, ease: "power3.out" },
        "-=0.7",
      )
      .to(
        ".showcase-col-left .btn-pill",
        { y: 0, opacity: 1, duration: 0.8, ease: "power3.out" },
        "-=0.6",
      );
  }
  const diaperVideo = document.querySelector(".diaper-video");
  if (diaperVideo) {
    let targetPBR = 0.8,
      currentPBR = 0.8;
    window.updateDiaperScrollVelocity = (v) => {
      targetPBR = 0.8 + Math.max(-8, Math.min(8, v)) * 0.12;
    };
    (function tick() {
      targetPBR += (0.8 - targetPBR) * 0.05;
      currentPBR += (targetPBR - currentPBR) * 0.1;
      diaperVideo.playbackRate = Math.max(0.1, Math.min(3.0, currentPBR));
      requestAnimationFrame(tick);
    })();
  }

  // ---------------------------------------------------------------------------
  // TRIAL
  // ---------------------------------------------------------------------------
  // ---------------------------------------------------------------------------
  // INTERACTIVE DIAPER SELECTOR & FIT EXPLORER
  // --------------------------------------------------------------------------  // ---------------------------------------------------------------------------
  // PREMIUM SIZING & SENSATION ATELIER
  // ---------------------------------------------------------------------------
  if (document.querySelector("#diaperSelectorSection")) {
    let variantData = {
      newborn: {
        name: "CloudCush TinyHug",
        absorbency: 80,
        stretch: 60,
        softness: 100,
        defaultWeight: "s",
        watermark: "TINYHUG"
      },
      activefit: {
        name: "CloudCush FlexFit",
        absorbency: 80,
        stretch: 100,
        softness: 80,
        defaultWeight: "m",
        watermark: "FLEXFIT"
      },
      overnight: {
        name: "CloudCush Overnight+",
        absorbency: 100,
        stretch: 80,
        softness: 80,
        defaultWeight: "xl",
        watermark: "OVERNIGHT+"
      }
    };

    if (window.homepageAtelierData && Array.isArray(window.homepageAtelierData)) {
      window.homepageAtelierData.forEach(v => {
        if (variantData[v.key]) {
          variantData[v.key].name = v.variant_name;
          variantData[v.key].absorbency = parseInt(v.val_absorbency) || 0;
          variantData[v.key].stretch = parseInt(v.val_stretch) || 0;
          variantData[v.key].softness = parseInt(v.val_softness) || 0;
          variantData[v.key].watermark = (v.key === 'newborn' ? 'TINYHUG' : (v.key === 'activefit' ? 'FLEXFIT' : 'OVERNIGHT+'));
        }
      });
    }

    const weightMappings = {
      xs: { variant: "newborn", size: "Size XS (<3 kg)", name: variantData.newborn.name, percent: 0 },
      s:  { variant: "newborn", size: "Size S (3-5 kg)", name: variantData.newborn.name, percent: 25 },
      m:  { variant: "activefit", size: "Size M (5-8 kg)", name: variantData.activefit.name, percent: 50 },
      l:  { variant: "activefit", size: "Size L (8-11 kg)", name: variantData.activefit.name, percent: 75 },
      xl: { variant: "overnight", size: "Size XL (11+ kg)", name: variantData.overnight.name, percent: 100 }
    };

    let activeVariantId = "newborn";
    let activeWeightId = "s";

    // Text Counter Animation Helper
    const animateNumber = (id, targetVal) => {
      const el = document.querySelector(id);
      if (!el) return;
      const currentVal = parseInt(el.innerText) || 0;
      const obj = { value: currentVal };
      gsap.to(obj, {
        value: targetVal,
        duration: 0.8,
        ease: "power2.out",
        onUpdate: () => {
          el.innerText = Math.round(obj.value) + "%";
        }
      });
    };

    // Dynamic Sensation Metrics Animator
    const updateMetrics = (absorbency, stretch, softness) => {
      // 1. Animate Bars
      gsap.to("#barAbsorbency", { width: absorbency + "%", duration: 0.8, ease: "power3.out" });
      gsap.to("#barStretch", { width: stretch + "%", duration: 0.8, ease: "power3.out" });
      gsap.to("#barSoftness", { width: softness + "%", duration: 0.8, ease: "power3.out" });

      // 2. Animate Numbers
      animateNumber("#valAbsorbency", absorbency);
      animateNumber("#valStretch", stretch);
      animateNumber("#valSoftness", softness);
    };

    // Weight Selection Slider Handler
    const selectWeight = (weightId, triggerTabSwitch = true) => {
      activeWeightId = weightId;
      const weightInfo = weightMappings[weightId];
      if (!weightInfo) return;

      // Update active state class on points
      document.querySelectorAll(".weight-point").forEach(point => {
        point.classList.toggle("active", point.getAttribute("data-weight") === weightId);
      });

      // Animate Slider Ring & Progress Bar
      gsap.to("#weightThreadRing", { left: weightInfo.percent + "%", duration: 0.6, ease: "power3.out" });
      gsap.to("#weightThreadProgress", { width: weightInfo.percent + "%", duration: 0.6, ease: "power3.out" });

      // Update Rec Details
      document.querySelector("#recDiaperName").innerText = weightInfo.name;
      document.querySelector("#recDiaperSize").innerText = weightInfo.size;

      // Swap variant stage tabs if needed
      if (triggerTabSwitch) {
        switchVariant(weightInfo.variant, false);
      }
    };

    // Variant Stage Tabs Switcher
    const switchVariant = (variantId, fromTabClick = false) => {
      activeVariantId = variantId;
      const data = variantData[variantId];
      if (!data) return;

      // 1. Update Active button state
      document.querySelectorAll(".atelier-stage-btn").forEach(btn => {
        btn.classList.toggle("active", btn.getAttribute("data-tab") === variantId);
      });

      // 2. Crossfade Active Visuals with scale-in reveal
      document.querySelectorAll(".atelier-variant").forEach(variant => {
        const isCurrent = variant.getAttribute("data-variant") === variantId;
        if (isCurrent) {
          variant.classList.add("active");
          gsap.fromTo(variant, 
            { opacity: 0, scale: 0.92, y: 10 }, 
            { opacity: 1, scale: 1, y: 0, duration: 0.6, ease: "power3.out", overwrite: "auto" }
          );
        } else {
          variant.classList.remove("active");
          gsap.to(variant, { opacity: 0, duration: 0.3, overwrite: "auto" });
        }
      });

      // 3. Editorial Watermark Transition
      const watermark = document.querySelector("#recWatermark");
      if (watermark) {
        gsap.to(watermark, { opacity: 0, y: 15, duration: 0.25, ease: "power2.in", onComplete: () => {
          watermark.innerText = data.watermark;
          gsap.fromTo(watermark, { y: -15, opacity: 0 }, { y: 0, opacity: 1, duration: 0.5, ease: "power3.out" });
        }});
      }

      // 4. Update Metrics
      updateMetrics(data.absorbency, data.stretch, data.softness);

      // 5. Select default weight for active stage if tab clicked directly
      if (fromTabClick) {
        selectWeight(data.defaultWeight, false);
      }
    };

    // Bind Stage Button Click Handlers
    document.querySelectorAll(".atelier-stage-btn").forEach(btn => {
      btn.addEventListener("click", () => {
        const tabId = btn.getAttribute("data-tab");
        switchVariant(tabId, true);
      });
    });

    // Bind Weight Thread Point Click Handlers
    document.querySelectorAll(".weight-point").forEach(point => {
      point.addEventListener("click", () => {
        const weightId = point.getAttribute("data-weight");
        selectWeight(weightId, true);
      });
    });
  }

  // ===========================================================================
  // STACKED SCROLL SECTION
  // ===========================================================================

  const stackSection = document.querySelector("#stackSection");
  if (stackSection) {
    const p1 = document.querySelector("#stackPanel1");
    const p2 = document.querySelector("#stackPanel2");
    const p3 = document.querySelector("#stackPanel3");
    const p4 = document.querySelector("#stackPanel4");
    const p5 = document.querySelector("#stackPanel5");
    const stackPanels = document.querySelector("#stackPanels");
    const stackLeftContent = document.querySelector("#stackLeftContent");
    const stackPerks = document.querySelectorAll(".stack-perk");
    const stackCta = document.querySelector("#stackCta");

    if (p1 && p2 && p3 && p4 && p5 && stackPanels && stackLeftContent) {
      mm.add("(min-width: 769px)", () => {
        const VH = window.innerHeight;
        const VW = window.innerWidth;
        const shiftX = VW * 0.52;

        gsap.set([p1, p2, p3, p4], { y: VH, opacity: 1 });
        gsap.set(p5, { x: -shiftX, opacity: 0 });
        gsap.set(stackPanels, { x: 0 });
        gsap.set(stackLeftContent, { opacity: 0, x: -50 });
        gsap.set(stackPerks, { opacity: 0, y: 14 });
        if (stackCta) gsap.set(stackCta, { opacity: 0, y: 14 });

        const entryTl = gsap.timeline({
          scrollTrigger: {
            trigger: "#stackSection",
            start: "top 85%",
            toggleActions: "play none none reverse",
          },
        });
        entryTl
          .to(p1, { y: 0, duration: 0.7, ease: "power3.out" }, 0.0)
          .to(p2, { y: 0, duration: 0.7, ease: "power3.out" }, 0.18)
          .to(p3, { y: 0, duration: 0.7, ease: "power3.out" }, 0.36)
          .to(p4, { y: 0, duration: 0.7, ease: "power3.out" }, 0.54);

        const scrubTl = gsap.timeline({
          scrollTrigger: {
            trigger: "#stackSection",
            start: "top top",
            end: "+=240%",
            pin: true,
            pinSpacing: true,
            scrub: 0.8,
            anticipatePin: 1,
            invalidateOnRefresh: true,
          },
        });

        scrubTl.to({}, { duration: 0.12 }, 0.0);

        scrubTl.to(
          stackPanels,
          {
            x: shiftX,
            duration: 0.3,
            ease: "power2.inOut",
          },
          0.12,
        );

        scrubTl.to(
          [p1, p2, p3, p4],
          {
            opacity: 0,
            duration: 0.15,
            stagger: 0.03,
            ease: "power1.in",
          },
          0.2,
        );

        scrubTl.to(
          p5,
          {
            x: 0,
            opacity: 1,
            duration: 0.3,
            ease: "power2.inOut",
          },
          0.12,
        );

        scrubTl.to({}, { duration: 0.07 }, 0.42);

        scrubTl.to(
          stackLeftContent,
          {
            opacity: 1,
            x: 0,
            duration: 0.14,
            ease: "power3.out",
            onStart: () => {
              stackLeftContent.style.pointerEvents = "all";
            },
          },
          0.52,
        );

        scrubTl.to(
          stackPerks,
          {
            opacity: 1,
            y: 0,
            duration: 0.1,
            stagger: 0.025,
            ease: "power2.out",
          },
          0.68,
        );

        if (stackCta) {
          scrubTl.to(
            stackCta,
            {
              opacity: 1,
              y: 0,
              duration: 0.08,
              ease: "power2.out",
            },
            0.92,
          );
        }

        return () => {
          entryTl.kill();
          gsap.set([p1, p2, p3, p4, p5], { clearProps: "all" });
          gsap.set(stackPanels, { clearProps: "all" });
          gsap.set(stackLeftContent, { clearProps: "all" });
          gsap.set(stackPerks, { clearProps: "all" });
          if (stackCta) gsap.set(stackCta, { clearProps: "all" });
        };
      });

      // Mobile — static layout (stack section)
      mm.add("(max-width: 768px)", () => {
        const p1 = document.querySelector("#stackPanel1");
        const p2 = document.querySelector("#stackPanel2");
        const p3 = document.querySelector("#stackPanel3");
        const p4 = document.querySelector("#stackPanel4");
        const p5 = document.querySelector("#stackPanel5");
        const stackPanels = document.querySelector("#stackPanels");
        const stackLeftContent = document.querySelector("#stackLeftContent");
        const stackPerks = document.querySelectorAll(".stack-perk");
        const stackCta = document.querySelector("#stackCta");
        gsap.set([p1, p2, p3, p4, p5], { clearProps: "all" });
        gsap.set(stackPanels, { clearProps: "all" });
        gsap.set(stackLeftContent, { clearProps: "all" });
        gsap.set(stackPerks, { clearProps: "all" });
        if (stackCta) gsap.set(stackCta, { clearProps: "all" });
        return () => {};
      });
    }
  }

  // ===========================================================================
  // CATEGORY NAV PINNED SCROLL SECTION
  //
  // ARCHITECTURE:
  // ─────────────────────────────────────────────────────────────────────────
  // Pinned for 500vh (5 tabs × 100vh each).
  // ScrollTrigger onUpdate tracks progress 0→1, maps to tab index 0→4.
  // Tab switches use GSAP crossfade on panels + CSS opacity on nav items.
  //
  // Nav click: uses Lenis scrollTo (available via window.lenis) or native
  // window.scrollTo fallback — NO ScrollToPlugin needed.
  //
  // After last tab finishes (progress reaches 1.0), pin releases naturally.
  // ─────────────────────────────────────────────────────────────────────────

  const catnavSection = document.querySelector("#catnavSection");
  if (catnavSection) {
    const catnavItems = document.querySelectorAll(".catnav-item");
    const catnavPanels = document.querySelectorAll(".catnav-panel");

    if (catnavItems.length && catnavPanels.length) {
      const TOTAL_TABS = catnavItems.length; // 5
      let activeTabIdx = 0;

      // ── Cross-fade panel switch (Desktop & Mobile) ─────────────────────────────
      const switchTab = (toIndex) => {
        if (toIndex === activeTabIdx) return;

        const fromIndex = activeTabIdx;
        activeTabIdx = toIndex;

        // Update nav item classes
        catnavItems.forEach((item, i) => {
          item.classList.toggle("active", i === toIndex);
        });

        // Cross-fade panels with premium easing & staggered elements
        const fromPanel = catnavPanels[fromIndex];
        const toPanel = catnavPanels[toIndex];

        if (fromPanel && fromPanel !== toPanel) {
          // Fade out old panel
          gsap.to(fromPanel, {
            opacity: 0,
            duration: 0.6,
            ease: "power2.inOut",
            overwrite: true,
            onComplete: () => {
              fromPanel.classList.remove("active");
              fromPanel.style.pointerEvents = "none";
            },
          });
        }

        if (toPanel) {
          toPanel.classList.add("active");
          toPanel.style.pointerEvents = "auto";

          // Zoom image slightly on enter for premium parallax feel
          const toImg = toPanel.querySelector(".catnav-img");
          if (toImg) {
            gsap.fromTo(
              toImg,
              { scale: 1.15 },
              { scale: 1, duration: 1.6, ease: "power3.out", overwrite: true }
            );
          }

          // Fade in new panel
          gsap.fromTo(
            toPanel,
            { opacity: 0 },
            {
              opacity: 1,
              duration: 0.7,
              ease: "power2.out",
              overwrite: true,
            }
          );

          // Slide in floating content card elements staggered
          const toContent = toPanel.querySelector(".catnav-panel-content");
          if (toContent) {
            gsap.fromTo(
              toContent,
              { y: 30, opacity: 0 },
              {
                y: 0,
                opacity: 1,
                duration: 0.8,
                ease: "power3.out",
                overwrite: true,
              }
            );
          }
        }
      };

      // ── Initial State ────────────────────────────────────────────────────────
      catnavPanels.forEach((panel, i) => {
        gsap.set(panel, { opacity: i === 0 ? 1 : 0 });
        panel.style.pointerEvents = i === 0 ? "auto" : "none";
        if (i === 0) {
          panel.classList.add("active");
          // Ensure first image is scale(1)
          const img = panel.querySelector(".catnav-img");
          if (img) gsap.set(img, { scale: 1 });
          const contentCard = panel.querySelector(".catnav-panel-content");
          if (contentCard) gsap.set(contentCard, { y: 0, opacity: 1 });
        } else {
          panel.classList.remove("active");
        }
      });

      // Clear all progress bars
      catnavItems.forEach((item, i) => {
        const bar = item.querySelector(".catnav-item-progress-bar");
        if (bar) {
          gsap.set(bar, { width: i === 0 ? "100%" : "0%" });
        }
      });

      // ── Desktop: Pinned scrub ScrollTrigger ──────────────────────────────────
      mm.add("(min-width: 769px)", () => {
        let catnavST = null;

        catnavST = ScrollTrigger.create({
          trigger: "#catnavSection",
          start: "top top",
          end: "+=500%", // 5 tabs × 100vh = 500vh pinned scroll
          pin: true,
          pinSpacing: true,
          scrub: 1.2,
          anticipatePin: 1,
          invalidateOnRefresh: true,

          onUpdate: (self) => {
            const progress = self.progress; // 0 → 1
            const sectionProgress = progress * TOTAL_TABS; // 0 → 5

            // Map progress to tab index: 5 equal zones
            let targetIndex = Math.min(
              TOTAL_TABS - 1,
              Math.floor(progress * TOTAL_TABS)
            );

            if (targetIndex !== activeTabIdx) {
              switchTab(targetIndex);
            }

            // Fill progress bars dynamically based on section progress
            catnavItems.forEach((item, idx) => {
              const bar = item.querySelector(".catnav-item-progress-bar");
              if (bar) {
                if (idx < targetIndex) {
                  bar.style.width = "100%";
                } else if (idx > targetIndex) {
                  bar.style.width = "0%";
                } else {
                  // Active item progress bar scrubs from 0% to 100%
                  const currentSectionProg = (sectionProgress - idx) * 100;
                  bar.style.width = Math.max(0, Math.min(100, currentSectionProg)) + "%";
                }
              }
            });
          },
        });

        // Nav item click → smooth scroll to scroll position
        catnavItems.forEach((item, i) => {
          item.addEventListener("click", () => {
            if (!catnavST) return;

            const targetProgress = (i + 0.3) / TOTAL_TABS;
            const rawScroll =
              catnavST.start + (catnavST.end - catnavST.start) * targetProgress;

            if (window.lenis) {
              window.lenis.scrollTo(rawScroll, {
                duration: 1.2,
                easing: (t) => 1 - Math.pow(1 - t, 4),
              });
            } else {
              window.scrollTo({ top: rawScroll, behavior: "smooth" });
            }
          });
        });

        return () => {
          // Reset desktop scroll animations on resize
          if (catnavST) {
            catnavST.kill();
            catnavST = null;
          }
          catnavItems.forEach((item, i) => {
            item.classList.toggle("active", i === 0);
            const bar = item.querySelector(".catnav-item-progress-bar");
            if (bar) bar.style.width = i === 0 ? "100%" : "0%";
          });
          catnavPanels.forEach((panel, i) => {
            gsap.set(panel, { clearProps: "opacity" });
            panel.classList.toggle("active", i === 0);
            panel.style.pointerEvents = i === 0 ? "auto" : "none";
          });
          activeTabIdx = 0;
        };
      }); // end desktop mm

      // ── Mobile/Tablet: Unpinned click + touch swipe transitions ─────────────
      mm.add("(max-width: 768px)", () => {
        // Tab click centering + panel switch
        catnavItems.forEach((item, i) => {
          item.addEventListener("click", () => {
            switchTab(i);

            // Center active item horizontally in the list
            const container = document.querySelector(".catnav-list");
            if (container) {
              const offsetLeft = item.offsetLeft;
              const width = item.offsetWidth;
              const containerWidth = container.offsetWidth;
              container.scrollTo({
                left: offsetLeft - containerWidth / 2 + width / 2,
                behavior: "smooth",
              });
            }
          });
        });

        // Swipe detector on the right panel
        const catnavRight = document.querySelector("#catnavRight");
        if (catnavRight) {
          let touchStartX = 0;
          let touchEndX = 0;

          const handleSwipe = () => {
            const swipeThreshold = 50;
            const diff = touchStartX - touchEndX;

            if (Math.abs(diff) > swipeThreshold) {
              if (diff > 0) {
                // Swipe Left -> Next tab
                if (activeTabIdx < TOTAL_TABS - 1) {
                  const targetIdx = activeTabIdx + 1;
                  const nextItem = catnavItems[targetIdx];
                  if (nextItem) nextItem.click();
                }
              } else {
                // Swipe Right -> Prev tab
                if (activeTabIdx > 0) {
                  const targetIdx = activeTabIdx - 1;
                  const prevItem = catnavItems[targetIdx];
                  if (prevItem) prevItem.click();
                }
              }
            }
          };

          catnavRight.addEventListener(
            "touchstart",
            (e) => {
              touchStartX = e.changedTouches[0].screenX;
            },
            { passive: true }
          );

          catnavRight.addEventListener(
            "touchend",
            (e) => {
              touchEndX = e.changedTouches[0].screenX;
              handleSwipe();
            },
            { passive: true }
          );
        }

        return () => {
          // Reset mobile handlers
          catnavItems.forEach((item, i) => {
            item.classList.toggle("active", i === 0);
          });
          catnavPanels.forEach((panel, i) => {
            panel.classList.toggle("active", i === 0);
          });
          activeTabIdx = 0;
        };
      }); // end mobile mm
    }
  }

  // ===========================================================================
  // CORE COLLECTION — Premium Carousel + GSAP Hover Reveal
  // ===========================================================================
  // No external carousel lib needed (no Swiper/Slick in project).
  // Custom carousel built with GSAP + Pointer Events for drag/swipe.
  // ===========================================================================

  const ccSection = document.querySelector("#coreCollectionSection");
  if (ccSection) {
    const ccTrack = document.querySelector("#ccTrack");
    const ccViewport = document.querySelector("#ccViewport");
    const ccPrevBtn = document.querySelector("#ccPrev");
    const ccNextBtn = document.querySelector("#ccNext");
    const ccDotBtns = document.querySelectorAll("[data-cc-dot]");
    const ccCards = document.querySelectorAll(".collection-card");

    if (ccTrack && ccViewport && ccCards.length > 0) {
      // ── Section scroll-in animation (ScrollTrigger) ────────────────────────────
      const ccTitle = ccSection.querySelector(".core-collection-title");
      const ccNav = ccSection.querySelector(".cc-nav");
      if (ccTitle) gsap.set(ccTitle, { y: 40, opacity: 0 });
      if (ccNav) gsap.set(ccNav, { y: 20, opacity: 0 });
      gsap.set(ccCards, { y: 60, opacity: 0 });

      const ccEntryTl = gsap.timeline({
        scrollTrigger: {
          trigger: "#coreCollectionSection",
          start: "top 72%",
          toggleActions: "play none none reverse",
        },
      });

      if (ccTitle)
        ccEntryTl.to(
          ccTitle,
          { y: 0, opacity: 1, duration: 0.9, ease: "power3.out" },
          0,
        );
      if (ccNav)
        ccEntryTl.to(
          ccNav,
          { y: 0, opacity: 1, duration: 0.7, ease: "power3.out" },
          0.15,
        );

      ccEntryTl.to(
        ccCards,
        {
          y: 0,
          opacity: 1,
          duration: 0.7,
          stagger: 0.13,
          ease: "power3.out",
        },
        0.2,
      );

      // ── Carousel State ──────────────────────────────────────────────────────────
      let ccIndex = 0;
      let ccPerView = 3; // updated on resize
      let ccTotal = ccCards.length;
      let isAnimating = false;

      const getPerView = () => {
        const w = window.innerWidth;
        if (w <= 768) return 1;
        if (w <= 1100) return 2;
        return 3;
      };

      const getGap = () => {
        const style = window.getComputedStyle(ccTrack);
        return parseFloat(style.gap) || 28;
      };

      const getCardWidth = () => {
        if (!ccCards[0]) return 0;
        return ccCards[0].getBoundingClientRect().width;
      };

      const maxIndex = () => Math.max(0, ccTotal - ccPerView);

      const updateDots = (idx) => {
        ccDotBtns.forEach((dot, i) => {
          dot.classList.toggle("active", i === idx);
        });
      };

      const updateNavState = (idx) => {
        if (ccPrevBtn) ccPrevBtn.classList.toggle("cc-disabled", idx <= 0);
        if (ccNextBtn)
          ccNextBtn.classList.toggle("cc-disabled", idx >= maxIndex());
      };

      const goToSlide = (idx, duration = 0.6) => {
        if (isAnimating) return;
        isAnimating = true;

        ccIndex = Math.max(0, Math.min(idx, maxIndex()));
        const gap = getGap();
        const cardWidth = getCardWidth();
        const offset = -(ccIndex * (cardWidth + gap));

        gsap.to(ccTrack, {
          x: offset,
          duration,
          ease: "power3.out",
          onComplete: () => {
            isAnimating = false;
          },
        });

        updateDots(ccIndex);
        updateNavState(ccIndex);
      };

      // ── Prev / Next button handlers ─────────────────────────────────────────────
      if (ccPrevBtn) {
        ccPrevBtn.addEventListener("click", () => {
          if (ccIndex > 0) goToSlide(ccIndex - 1);
        });
      }

      if (ccNextBtn) {
        ccNextBtn.addEventListener("click", () => {
          if (ccIndex < maxIndex()) goToSlide(ccIndex + 1);
        });
      }

      // ── Dot button handlers ─────────────────────────────────────────────────────
      ccDotBtns.forEach((dot) => {
        dot.addEventListener("click", () => {
          const targetIdx = parseInt(dot.dataset.ccDot, 10);
          goToSlide(targetIdx);
        });
      });

      // ── Drag / Touch / Pointer support ─────────────────────────────────────────
      let dragStartX = 0;
      let dragCurrentX = 0;
      let isDragging = false;
      let trackBaseX = 0;
      const DRAG_THRESHOLD = 40; // px to trigger slide change

      const onPointerDown = (e) => {
        isDragging = true;
        dragStartX = e.type === "touchstart" ? e.touches[0].clientX : e.clientX;
        // ── FIX: sync dragCurrentX to dragStartX so that on a clean tap
        // (no pointermove fired), the delta stays 0 and the ghost-click
        // guard below never incorrectly fires preventDefault on anchor clicks.
        dragCurrentX = dragStartX;
        trackBaseX = gsap.getProperty(ccTrack, "x");
        gsap.killTweensOf(ccTrack);
        ccViewport.style.cursor = "grabbing";
      };

      const onPointerMove = (e) => {
        if (!isDragging) return;
        dragCurrentX =
          e.type === "touchmove" ? e.touches[0].clientX : e.clientX;
        const delta = dragCurrentX - dragStartX;
        // Only mark as a real drag when movement exceeds 5px threshold
        if (Math.abs(delta) > 5) {
          hasDragged = true;
        }
        gsap.set(ccTrack, { x: trackBaseX + delta });
      };

      const onPointerUp = (e) => {
        if (!isDragging) return;
        isDragging = false;
        ccViewport.style.cursor = "grab";
        const finalX =
          e.type === "touchend"
            ? (e.changedTouches[0]?.clientX ?? dragCurrentX)
            : e.clientX;
        const delta = finalX - dragStartX;

        if (Math.abs(delta) > DRAG_THRESHOLD) {
          // hasDragged already set in pointermove; slide to target
          if (delta < 0 && ccIndex < maxIndex()) {
            goToSlide(ccIndex + 1, 0.5);
          } else if (delta > 0 && ccIndex > 0) {
            goToSlide(ccIndex - 1, 0.5);
          } else {
            goToSlide(ccIndex, 0.4); // snap back
            hasDragged = false; // didn't slide — allow click
          }
        } else {
          goToSlide(ccIndex, 0.4); // snap back
          hasDragged = false; // micro-movement — allow click
        }
      };

      // Mouse events
      ccViewport.addEventListener("mousedown", onPointerDown);
      window.addEventListener("mousemove", onPointerMove);
      window.addEventListener("mouseup", onPointerUp);

      // Touch events
      ccViewport.addEventListener("touchstart", onPointerDown, {
        passive: true,
      });
      ccViewport.addEventListener("touchmove", onPointerMove, {
        passive: true,
      });
      ccViewport.addEventListener("touchend", onPointerUp);

      // Block anchor navigation ONLY when the pointer genuinely dragged.
      // hasDragged is set true only when pointermove fires with meaningful
      // movement, and is reset to false after each pointerup.
      // This ensures a clean tap ALWAYS fires the anchor click immediately.
      let hasDragged = false;

      ccViewport.addEventListener(
        "click",
        (e) => {
          if (hasDragged) {
            e.preventDefault();
            e.stopPropagation();
            hasDragged = false; // reset after suppressing
          }
        },
        true,
      );

      // ── GSAP Hover Reveal — image expands to fill card, CTA appears ─────────────
      // Architecture:
      //   - .collection-image-wrap  → height: '58%' → '100%'    (fills card)
      //   - .collection-image       → scale: 1.06 → 1.0         (de-zoom)
      //   - .collection-info        → opacity: 1 → 0            (hide text)
      //   - .collection-cta-wrap    → opacity: 0→1, y: 20→0     (reveal pill)
      //   - .collection-card        → scale: 1.0 → 1.015        (premium lift)
      //
      // pointer-events on ctaWrap is managed BOTH by CSS :hover AND by GSAP
      // onStart/onComplete callbacks — dual approach ensures correctness at any
      // screen width (1080p, 1440p, 1920p, 2560px ultra-wide).
      //
      // Only on non-touch devices. Touch: CSS forces CTA always visible.
      const isTouchDevice = () =>
        "ontouchstart" in window || navigator.maxTouchPoints > 0;

      if (!isTouchDevice()) {
        ccCards.forEach((card) => {
          const imgWrap = card.querySelector(".collection-image-wrap");
          const img = card.querySelector(".collection-image");
          const info = card.querySelector(".collection-info");
          const ctaWrap = card.querySelector(".collection-cta-wrap");

          if (!imgWrap || !img || !info || !ctaWrap) return;

          // ── FIX: Ensure the CTA anchor is always pointer-interactive.
          // CSS default is pointer-events:none until :hover fires, but on a fast
          // first click the :hover state may not have propagated yet. Forcing
          // auto here means the anchor inside ctaWrap is always clickable.
          ctaWrap.style.pointerEvents = "auto";

          // ── Premium Reversible Hover Timeline ──────────────────────────────────
          const hoverTl = gsap.timeline({
            paused: true,
            defaults: { duration: 0.55, ease: "power3.out" }
          });

          hoverTl
            .to(imgWrap, { height: "100%", ease: "power3.inOut" }, 0)
            .to(img, { scale: 1.0, ease: "power3.inOut" }, 0)
            .to(card, { scale: 1.015, ease: "power3.out" }, 0)
            .to(info, { opacity: 0, y: -12, duration: 0.28, ease: "power2.in" }, 0)
            .to(ctaWrap, { opacity: 1, y: 0, duration: 0.45, ease: "power3.out" }, 0.12);

          card.addEventListener("mouseenter", () => {
            hoverTl.play();
          });

          card.addEventListener("mouseleave", () => {
            hoverTl.reverse();
          });
        });
      }

      // ── Resize — recalculate layout ─────────────────────────────────────────────
      let resizeTimer;
      window.addEventListener("resize", () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
          const newPerView = getPerView();
          if (newPerView !== ccPerView) {
            ccPerView = newPerView;
            ccIndex = 0;
            gsap.set(ccTrack, { x: 0 });
            updateDots(0);
            updateNavState(0);
          } else {
            goToSlide(ccIndex, 0.3);
          }
        }, 120);
      });

      // ── Initial state ────────────────────────────────────────────────────────────
      ccPerView = getPerView();
      gsap.set(ccTrack, { x: 0 });
      updateDots(0);
      updateNavState(0);
    }
  }

  // =============================================================================
  // PHILOSOPHY SECTION — GSAP ScrollTrigger + Pinned Panel Transition
  // =============================================================================
  const philSection = document.getElementById("philosophySection");
  if (philSection) {
    const philBgImg = document.getElementById("philosophyBgImg");
    const philOverlay = document.getElementById("philosophyOverlay");

    // Panel containers
    const philPanel1 = document.getElementById("philPanel1");
    const philPanel2 = document.getElementById("philPanel2");

    // Entrance elements (for the first panel)
    const philEyebrow = document.getElementById("philEyebrow");
    const philBody = document.getElementById("philBody");
    const philCta = document.getElementById("philCta");

    // Set initial states
    if (philPanel2) {
      gsap.set(
        philPanel2.querySelectorAll(
          ".philosophy-eyebrow-wrap, .philosophy-body, .philosophy-cta-wrap",
        ),
        {
          opacity: 1,
          y: 0,
        },
      );
      gsap.set(philPanel2, { opacity: 0, y: 40, pointerEvents: "none" });
    }

    if (philPanel1) {
      gsap.set(philPanel1, { opacity: 1, y: 0, pointerEvents: "auto" });
    }

    // ── 1. Section Entrance Animation ────────────────────────────────────────
    // Set initial hidden states for entrance elements before any animation
    if (philEyebrow) gsap.set(philEyebrow, { opacity: 0, y: 30 });
    if (philBody)    gsap.set(philBody,    { opacity: 0, y: 40 });
    if (philCta)     gsap.set(philCta,     { opacity: 0, y: 20 });
    if (philBgImg)   gsap.set(philBgImg,   { scale: 1.08 });
    if (philOverlay) gsap.set(philOverlay, { opacity: 0 });

    const philEntranceTl = gsap.timeline({
      scrollTrigger: {
        trigger: philSection,
        start: "top 80%",
        end: "top 30%",
        toggleActions: "play none none reverse",
      },
    });

    if (philBgImg) {
      philEntranceTl.to(
        philBgImg,
        { scale: 1.0, duration: 1.8, ease: "power2.out" },
        0,
      );
    }

    if (philOverlay) {
      philEntranceTl.to(
        philOverlay,
        { opacity: 1, duration: 1.2, ease: "power2.out" },
        0,
      );
    }

    if (philEyebrow) {
      philEntranceTl.to(
        philEyebrow,
        { opacity: 1, y: 0, duration: 1.0, ease: "power3.out" },
        0.3,
      );
    }

    if (philBody) {
      philEntranceTl.to(
        philBody,
        { opacity: 1, y: 0, duration: 1.1, ease: "power3.out" },
        0.5,
      );
    }

    if (philCta) {
      philEntranceTl.to(
        philCta,
        { opacity: 1, y: 0, duration: 0.9, ease: "power3.out" },
        0.75,
      );
    }

    // ── 2. Scroll Pinned Panel Transition & Parallax ─────────────────────────
    const isMobile = () => window.innerWidth <= 768;

    if (philPanel1 && philPanel2) {
      const philPinTl = gsap.timeline({
        scrollTrigger: {
          trigger: philSection,
          start: "top top",
          end: "+=120%",
          pin: true,
          scrub: 1.0,
          anticipatePin: 1,
          invalidateOnRefresh: true,
        },
      });

      philPinTl.to(
        philPanel1,
        {
          opacity: 0,
          y: -40,
          duration: 1.0,
          ease: "power2.inOut",
        },
        0,
      );

      philPinTl.set(philPanel1, { pointerEvents: "none" }, 0.5);
      philPinTl.set(philPanel2, { pointerEvents: "auto" }, 0.5);

      philPinTl.to(
        philPanel2,
        {
          opacity: 1,
          y: 0,
          duration: 1.0,
          ease: "power2.inOut",
        },
        0.4,
      );

      if (!isMobile() && philBgImg) {
        philPinTl.to(
          philBgImg,
          {
            yPercent: -12,
            ease: "none",
            duration: 1.4,
          },
          0,
        );
      }
    }
  }

  // =============================================================================
  // MOM-APPROVED MOMENTS — Infinite Autoplay + Drag Carousel
  // =============================================================================
  const momSection = document.getElementById("momMomentsSection");
  if (momSection) {
    const momViewport = document.getElementById("momCarouselViewport");
    const momTrack = document.getElementById("momCarouselTrack");

    if (momViewport && momTrack) {
      // 1. Clone cards to create a seamless infinite loop
      const originalCards = Array.from(momTrack.children);
      originalCards.forEach((card) => {
        const clone = card.cloneNode(true);
        momTrack.appendChild(clone);
      });

      // Prevent native image and video dragging browser behaviors
      momTrack.querySelectorAll("img, video").forEach((el) => {
        el.addEventListener("dragstart", (e) => e.preventDefault());
      });

      // Ensure all videos (original + cloned) start playing muted
      momTrack.querySelectorAll("video").forEach((video) => {
        video.muted = true;
        video.play().catch(() => {});
      });

      // 2. Section Scroll Entrance Animation (Stagger reveal including clones)
      const allMomCards = momTrack.querySelectorAll(".mom-card");
      if (allMomCards.length > 0) {
        gsap.set(allMomCards, { y: 60, opacity: 0 });
        gsap.to(allMomCards, {
          y: 0,
          opacity: 1,
          duration: 0.8,
          stagger: 0.08,
          ease: "power3.out",
          scrollTrigger: {
            trigger: "#momMomentsSection",
            start: "top 75%",
            toggleActions: "play none none reverse",
          },
        });
      }

      // 3. Carousel Loop Logic
      let currentX = 0;
      let isDragging = false;
      let isHovered = false;
      let autoplaySpeed = 0.5; // pixels per frame
      let momentumTween = null;
      let dragPositions = [];

      const getCardWidth = () => {
        if (!originalCards[0]) return 0;
        return originalCards[0].getBoundingClientRect().width;
      };

      const getGap = () => {
        const style = window.getComputedStyle(momTrack);
        return parseFloat(style.gap) || 24;
      };

      let singleLoopWidth = originalCards.length * (getCardWidth() + getGap());

      const wrapX = () => {
        if (currentX <= -singleLoopWidth) {
          currentX += singleLoopWidth;
        } else if (currentX > 0) {
          currentX -= singleLoopWidth;
        }
      };

      // GSAP Ticker for smooth autoplay scroll
      const tick = () => {
        // Return if dragging, hovering, or active momentum drift is running
        if (
          isDragging ||
          isHovered ||
          (momentumTween && momentumTween.isActive())
        )
          return;
        currentX -= autoplaySpeed;
        wrapX();
        gsap.set(momTrack, { x: currentX });
      };

      gsap.ticker.add(tick);

      // 4. Pointer Events for Drag / Swipe Support
      let startX = 0;
      let startY = 0;
      let startScrollX = 0;
      let isSwipeDirectionSelected = false;
      let isVerticalScroll = false;

      momViewport.addEventListener("pointerdown", (e) => {
        // Only run for primary clicks/touches
        if (e.button !== 0 && e.pointerType === "mouse") return;

        isDragging = true;
        isSwipeDirectionSelected = false;
        isVerticalScroll = false;
        isHovered = false; // Drag overrides hover pauses

        if (momentumTween) momentumTween.kill();

        startX = e.clientX;
        startY = e.clientY;
        startScrollX = currentX;
        dragPositions = [{ x: currentX, t: Date.now() }];

        momViewport.setPointerCapture(e.pointerId);
      });

      momViewport.addEventListener("pointermove", (e) => {
        if (!isDragging) return;

        const dx = e.clientX - startX;
        const dy = e.clientY - startY;

        // Choose between horizontal carousel drag or native vertical page scroll
        if (!isSwipeDirectionSelected) {
          const absX = Math.abs(dx);
          const absY = Math.abs(dy);
          if (absX > 6 || absY > 6) {
            isSwipeDirectionSelected = true;
            if (absY > absX) {
              isVerticalScroll = true;
              isDragging = false; // Abort drag, release pointer for native page scroll
              try {
                momViewport.releasePointerCapture(e.pointerId);
              } catch (err) {}
              return;
            }
          } else {
            return;
          }
        }

        if (isVerticalScroll) return;

        // Prevent browser selection / actions on horizontal swipe
        e.preventDefault();

        currentX = startScrollX + dx;
        wrapX();
        gsap.set(momTrack, { x: currentX });

        // Keep last 5 positions for speed calculations
        dragPositions.push({ x: currentX, t: Date.now() });
        if (dragPositions.length > 5) dragPositions.shift();
      });

      momViewport.addEventListener("pointerup", (e) => {
        if (!isDragging) {
          isDragging = false;
          return;
        }
        isDragging = false;

        try {
          momViewport.releasePointerCapture(e.pointerId);
        } catch (err) {}

        // Apply momentum drift if speed is high enough
        if (dragPositions.length > 1) {
          const first = dragPositions[0];
          const last = dragPositions[dragPositions.length - 1];
          const dt = last.t - first.t;
          const dx = last.x - first.x;
          const velocity = dt > 0 ? dx / dt : 0; // px/ms

          if (Math.abs(velocity) > 0.15) {
            const targetDistance = velocity * 220; // Drift weight
            const targetX = currentX + targetDistance;

            momentumTween = gsap.to(
              { val: currentX },
              {
                val: targetX,
                duration: 1.2,
                ease: "power2.out",
                onUpdate: function () {
                  currentX = this.targets()[0].val;
                  wrapX();
                  gsap.set(momTrack, { x: currentX });
                },
              },
            );
          }
        }
      });

      momViewport.addEventListener("pointercancel", (e) => {
        isDragging = false;
        try {
          momViewport.releasePointerCapture(e.pointerId);
        } catch (err) {}
      });

      // Pause on hover
      momViewport.addEventListener("mouseenter", () => {
        if (!isDragging) isHovered = true;
      });
      momViewport.addEventListener("mouseleave", () => {
        isHovered = false;
      });

      // Handle window resizing
      window.addEventListener("resize", () => {
        singleLoopWidth = originalCards.length * (getCardWidth() + getGap());
      });
    }
  }

  // ---------------------------------------------------------------------------
  // BLOG PAGE ANIMATIONS
  // ---------------------------------------------------------------------------
  if (document.querySelector(".blog-page")) {
    // 1. Cinematic Hero Entrance
    const blogHeroTl = gsap.timeline({ defaults: { ease: "power3.out" } });

    gsap.set(".blog-hero-bg", { scale: 1.15 });
    gsap.set(".blog-hero-label", { y: 20, opacity: 0 });
    gsap.set(".blog-hero-title", { y: 40, opacity: 0 });
    gsap.set(".blog-hero-subtext", { y: 30, opacity: 0 });
    gsap.set(".blog-hero-scroll", { y: 15, opacity: 0 });

    blogHeroTl
      .to(".blog-hero-bg", { scale: 1.0, duration: 2.2, ease: "power2.out" })
      .to(".blog-hero-label", { y: 0, opacity: 1, duration: 0.8 }, "-=1.7")
      .to(".blog-hero-title", { y: 0, opacity: 1, duration: 1.0 }, "-=1.4")
      .to(".blog-hero-subtext", { y: 0, opacity: 0.95, duration: 0.8 }, "-=1.1")
      .to(".blog-hero-scroll", { y: 0, opacity: 0.8, duration: 0.6 }, "-=0.8");

    // 2. Featured Story Scroll Entrance
    if (document.querySelector(".blog-featured-section")) {
      const featTl = gsap.timeline({
        scrollTrigger: {
          trigger: ".blog-featured-section",
          start: "top 75%",
          toggleActions: "play none none reverse",
        },
      });

      gsap.set(".blog-featured-img-wrap", { y: 40, opacity: 0 });
      gsap.set(".blog-featured-content", { y: 60, opacity: 0 });

      featTl
        .to(".blog-featured-img-wrap", {
          y: 0,
          opacity: 1,
          duration: 1.0,
          ease: "power3.out",
        })
        .to(
          ".blog-featured-content",
          { y: 0, opacity: 1, duration: 1.0, ease: "power3.out" },
          "-=0.7",
        );

      // Featured image slow parallax
      gsap.to(".blog-featured-img", {
        yPercent: -8,
        ease: "none",
        scrollTrigger: {
          trigger: ".blog-featured-section",
          start: "top bottom",
          end: "bottom top",
          scrub: true,
        },
      });
    }

    // 3. Grid Cards Stagger Entrance
    if (document.querySelector(".blog-creative-grid")) {
      gsap.fromTo(
        ".blog-card",
        { y: 60, opacity: 0 },
        {
          y: 0,
          opacity: 1,
          duration: 0.9,
          stagger: 0.15,
          ease: "power3.out",
          scrollTrigger: {
            trigger: ".blog-creative-grid",
            start: "top 75%",
            once: true,
          },
        },
      );
    }
  }

  // ---------------------------------------------------------------------------
  // BLOG DETAILS PAGE ANIMATIONS
  // ---------------------------------------------------------------------------
  if (document.querySelector(".blog-details-page")) {
    // 1. Details Hero Entrance
    const detailsHeroTl = gsap.timeline({ defaults: { ease: "power3.out" } });

    gsap.set(".details-hero-bg", { scale: 1.12 });
    gsap.set(".details-category-badge", { y: 15, opacity: 0 });
    gsap.set(".details-read-time", { y: 15, opacity: 0 });
    gsap.set(".details-hero-title", { y: 35, opacity: 0 });
    gsap.set(".details-hero-subtitle", { y: 25, opacity: 0 });

    detailsHeroTl
      .to(".details-hero-bg", { scale: 1.0, duration: 2.0, ease: "power2.out" })
      .to(
        ".details-category-badge",
        { y: 0, opacity: 1, duration: 0.7 },
        "-=1.5",
      )
      .to(".details-read-time", { y: 0, opacity: 0.9, duration: 0.7 }, "-=1.4")
      .to(".details-hero-title", { y: 0, opacity: 1, duration: 0.9 }, "-=1.2")
      .to(
        ".details-hero-subtitle",
        { y: 0, opacity: 0.95, duration: 0.8 },
        "-=0.9",
      );

    // Hero background slow parallax
    gsap.to(".details-hero-bg", {
      yPercent: 12,
      ease: "none",
      scrollTrigger: {
        trigger: ".details-hero",
        start: "top top",
        end: "bottom top",
        scrub: true,
      },
    });

    // 2. Sidebar & Reading Content Scroll Trigger Entrance
    if (document.querySelector(".details-article-grid")) {
      gsap.fromTo(
        ".sticky-meta-wrap",
        { opacity: 0, x: -30 },
        {
          opacity: 1,
          x: 0,
          duration: 1.0,
          ease: "power3.out",
          scrollTrigger: {
            trigger: ".details-article-grid",
            start: "top 70%",
            once: true,
          },
        },
      );

      // Progressive reading flow reveals
      const childParagraphs = document.querySelectorAll(
        ".details-reading-content > *",
      );
      childParagraphs.forEach((el) => {
        gsap.fromTo(
          el,
          { y: 30, opacity: 0 },
          {
            y: 0,
            opacity: 1,
            duration: 0.8,
            ease: "power2.out",
            scrollTrigger: {
              trigger: el,
              start: "top 85%",
              once: true,
            },
          },
        );
      });
    }

    // 3. Related Articles Stagger Reveal
    if (document.querySelector(".related-articles-grid")) {
      gsap.fromTo(
        ".related-card",
        { y: 50, opacity: 0 },
        {
          y: 0,
          opacity: 1,
          duration: 0.9,
          stagger: 0.2,
          ease: "power3.out",
          scrollTrigger: {
            trigger: ".details-related-section",
            start: "top 75%",
            once: true,
          },
        },
      );
    }

    // 4. Bottom CTA Parallax
    if (document.querySelector(".details-bottom-cta")) {
      gsap.to(".cta-bg-layer", {
        yPercent: 10,
        ease: "none",
        scrollTrigger: {
          trigger: ".details-bottom-cta",
          start: "top bottom",
          end: "bottom top",
          scrub: true,
        },
      });

      gsap.fromTo(
        ".cta-content-wrap > *",
        { y: 24, opacity: 0 },
        {
          y: 0,
          opacity: 1,
          duration: 0.8,
          stagger: 0.15,
          ease: "power2.out",
          scrollTrigger: {
            trigger: ".details-bottom-cta",
            start: "top 75%",
            once: true,
          },
        },
      );
    }
  }

  // ---------------------------------------------------------------------------
  // FAQ PAGE ANIMATIONS
  // ---------------------------------------------------------------------------
  if (document.querySelector(".faq-page")) {
    // 1. Cinematic Hero Entrance
    const faqHeroTl = gsap.timeline({ defaults: { ease: "power3.out" } });

    gsap.set(".faq-hero-bg", { scale: 1.15 });
    gsap.set(".faq-hero-label", { y: 20, opacity: 0 });
    gsap.set(".faq-hero-title", { y: 40, opacity: 0 });
    gsap.set(".faq-hero-subtext", { y: 30, opacity: 0 });

    faqHeroTl
      .to(".faq-hero-bg", { scale: 1.0, duration: 2.2, ease: "power2.out" })
      .to(".faq-hero-label", { y: 0, opacity: 1, duration: 0.8 }, "-=1.7")
      .to(".faq-hero-title", { y: 0, opacity: 1, duration: 1.0 }, "-=1.4")
      .to(".faq-hero-subtext", { y: 0, opacity: 0.95, duration: 0.8 }, "-=1.1");

    // 2. Interactive Section Scroll Entrance
    if (document.querySelector(".faq-interactive-section")) {
      const interactiveTl = gsap.timeline({
        scrollTrigger: {
          trigger: ".faq-interactive-section",
          start: "top 75%",
          toggleActions: "play none none reverse",
        },
      });

      gsap.set(".faq-sidebar", { y: 40, opacity: 0 });
      gsap.set(".faq-content", { y: 50, opacity: 0 });

      interactiveTl
        .to(".faq-sidebar", {
          y: 0,
          opacity: 1,
          duration: 0.8,
          ease: "power3.out",
        })
        .to(
          ".faq-content",
          { y: 0, opacity: 1, duration: 0.9, ease: "power3.out" },
          "-=0.6",
        );
    }

    // 3. Comfort Promise Section
    if (document.querySelector(".faq-promise-section")) {
      const promiseTl = gsap.timeline({
        scrollTrigger: {
          trigger: ".faq-promise-section",
          start: "top 75%",
          toggleActions: "play none none reverse",
        },
      });

      gsap.set(".promise-info > :not(.promise-features-list)", {
        y: 30,
        opacity: 0,
      });
      gsap.set(".promise-feature-item", { y: 20, opacity: 0 });
      gsap.set(".promise-img-wrap", { y: 40, opacity: 0 });
      gsap.set(".promise-floating-card", { x: 30, opacity: 0 });

      promiseTl
        .to(".promise-info > :not(.promise-features-list)", {
          y: 0,
          opacity: 1,
          duration: 0.8,
          stagger: 0.15,
          ease: "power3.out",
        })
        .to(
          ".promise-feature-item",
          { y: 0, opacity: 1, duration: 0.7, stagger: 0.1, ease: "power2.out" },
          "-=0.5",
        )
        .to(
          ".promise-img-wrap",
          { y: 0, opacity: 1, duration: 0.9, ease: "power3.out" },
          "-=0.9",
        )
        .to(
          ".promise-floating-card",
          { x: 0, opacity: 1, duration: 0.8, ease: "back.out(1.15)" },
          "-=0.6",
        );
    }

    // 4. Support CTA
    if (document.querySelector(".faq-support-cta")) {
      gsap.fromTo(
        ".support-cta-box > *",
        { y: 24, opacity: 0 },
        {
          y: 0,
          opacity: 1,
          duration: 0.8,
          stagger: 0.15,
          ease: "power2.out",
          scrollTrigger: {
            trigger: ".faq-support-cta",
            start: "top 80%",
            once: true,
          },
        },
      );
    }
  }

  // ---------------------------------------------------------------------------
  // DIAPER GUIDE PAGE ANIMATIONS
  // ---------------------------------------------------------------------------
  if (document.querySelector(".guide-page")) {
    // 1. Cinematic Hero Entrance
    const guideHeroTl = gsap.timeline({ defaults: { ease: "power3.out" } });

    gsap.set(".guide-hero-bg", { scale: 1.15 });
    gsap.set(".guide-hero-label", { y: 20, opacity: 0 });
    gsap.set(".guide-hero-title", { y: 40, opacity: 0 });
    gsap.set(".guide-hero-subtext", { y: 30, opacity: 0 });
    gsap.set(".guide-hero-scroll", { y: 15, opacity: 0 });

    guideHeroTl
      .to(".guide-hero-bg", { scale: 1.0, duration: 2.2, ease: "power2.out" })
      .to(".guide-hero-label", { y: 0, opacity: 1, duration: 0.8 }, "-=1.7")
      .to(".guide-hero-title", { y: 0, opacity: 1, duration: 1.0 }, "-=1.4")
      .to(
        ".guide-hero-subtext",
        { y: 0, opacity: 0.95, duration: 0.8 },
        "-=1.1",
      )
      .to(".guide-hero-scroll", { y: 0, opacity: 0.8, duration: 0.6 }, "-=0.8");

    // 2. Scroll Pinned Timeline Experience (Desktop Only)
    const timelineSec = document.querySelector("#guideTimelineSection");
    if (timelineSec) {
      const panels = gsap.utils.toArray(".timeline-panel");
      const steps = gsap.utils.toArray(".timeline-step");

      ScrollTrigger.matchMedia({
        "(min-width: 1025px)": function () {
          const scrollHeight = window.innerHeight * panels.length;

          ScrollTrigger.create({
            trigger: timelineSec,
            pin: true,
            start: "top top",
            end: () => "+=" + scrollHeight,
            scrub: true,
            invalidateOnRefresh: true,
            onUpdate: (self) => {
              const activeIdx = Math.min(
                panels.length - 1,
                Math.floor(self.progress * panels.length),
              );

              steps.forEach((step, idx) => {
                if (idx === activeIdx) {
                  step.classList.add("active");
                } else {
                  step.classList.remove("active");
                }
              });

              panels.forEach((panel, idx) => {
                if (idx === activeIdx) {
                  panel.classList.add("active");
                } else {
                  panel.classList.remove("active");
                }
              });

              const progressBar = document.getElementById(
                "timelineProgressBar",
              );
              if (progressBar) {
                progressBar.style.height = self.progress * 100 + "%";
              }
            },
          });
        },
      });
    }

    // 3. Comfort Metrics Counters
    const metricValues = document.querySelectorAll(".metric-number");
    if (metricValues.length > 0) {
      metricValues.forEach((el) => {
        const target = parseFloat(el.getAttribute("data-target"));
        const isPercent = el.getAttribute("data-percent") === "true";
        const isHours = el.getAttribute("data-hours") === "true";
        const isPlus = el.getAttribute("data-plus") === "true";
        const isStar = el.getAttribute("data-star") === "true";
        const decimals = el.getAttribute("data-decimals") === "1" ? 1 : 0;

        const obj = { value: 0 };

        gsap.fromTo(
          obj,
          { value: 0 },
          {
            value: target,
            duration: 1.8,
            ease: "power2.out",
            scrollTrigger: {
              trigger: el,
              start: "top 85%",
              once: true,
            },
            onUpdate: () => {
              let val = obj.value.toFixed(decimals);
              if (target >= 1000) {
                val = Math.round(obj.value).toLocaleString("en-IN");
              }
              if (isPercent) val = val + "%";
              if (isHours) val = val + "h";
              if (isPlus) val = val + "+";
              if (isStar) val = "★ " + val;
              el.textContent = val;
            },
          },
        );
      });
    }

    // 4. Apple-Style Pinned Visual Story
    const storySection = document.querySelector("#visualStorySection");
    if (storySection) {
      const storyBlocks = gsap.utils.toArray(".visual-story-block");
      const storyImages = gsap.utils.toArray(".visual-story-image");

      if (storyBlocks.length > 0 && storyImages.length > 0) {
        storyBlocks.forEach((block, index) => {
          ScrollTrigger.create({
            trigger: block,
            start: "top center",
            end: "bottom center",
            onToggle: (self) => {
              if (self.isActive) {
                storyImages.forEach((img, imgIdx) => {
                  if (imgIdx === index) {
                    img.classList.add("active");
                  } else {
                    img.classList.remove("active");
                  }
                });
              }
            },
          });
        });
      }
    }

    // 5. Pediatrician Quote Stagger
    if (document.querySelector(".guide-quote-section")) {
      gsap.fromTo(
        ".quote-box > *",
        { y: 30, opacity: 0 },
        {
          y: 0,
          opacity: 1,
          duration: 0.9,
          stagger: 0.15,
          ease: "power3.out",
          scrollTrigger: {
            trigger: ".guide-quote-section",
            start: "top 75%",
            once: true,
          },
        },
      );
    }

    // 6. Final CTA Reveal
    if (document.querySelector(".guide-cta-section")) {
      gsap.fromTo(
        ".guide-cta-box > *",
        { y: 24, opacity: 0 },
        {
          y: 0,
          opacity: 1,
          duration: 0.8,
          stagger: 0.15,
          ease: "power2.out",
          scrollTrigger: {
            trigger: ".guide-cta-section",
            start: "top 80%",
            once: true,
          },
        },
      );
    }
  } // end guide-page guard

  // ---------------------------------------------------------------------------
  // FOOTER ANIMATION & TYPING EFFECT — runs on ALL pages
  // ---------------------------------------------------------------------------
  if (document.querySelector(".site-footer")) {
    // 1. Setup Typing Effect (Pure JS loop for absolute robustness)
    const typingTextEl = document.querySelector(".typing-text");
    if (typingTextEl) {
      // Defaults used when admin hasn't set a value or value is empty
      const defaults = ["Cloudcush", "comfort designed for tiny humans."];
      const raw = window.footerTypingTexts || [];
      // For each slot: use the admin value if non-empty, otherwise use the default
      const texts = defaults.map((def, i) => (raw[i] && raw[i].trim()) ? raw[i].trim() : def);

      let phraseIndex = 0;
      let charIndex = 0;
      let isDeleting = false;

      // Parent h2 — toggle compact class for the longer tagline phrase
      const brandHeading = typingTextEl.closest(".footer-huge-brand");

      const applyPhraseStyle = (idx) => {
        if (!brandHeading) return;
        if (idx === 0) {
          brandHeading.classList.remove("footer-huge-brand--tagline");
        } else {
          brandHeading.classList.add("footer-huge-brand--tagline");
        }
      };

      applyPhraseStyle(phraseIndex);

      const typeLoop = () => {
        const currentText = texts[phraseIndex];
        const display = currentText.substring(0, charIndex);
        typingTextEl.textContent = display;

        let speed = 220; // Typing speed

        if (isDeleting) {
          speed = 80; // Deleting speed
          charIndex--;
        } else {
          charIndex++;
        }

        if (!isDeleting && charIndex > currentText.length) {
          isDeleting = true;
          speed = 2000; // Pause when fully typed
        } else if (isDeleting && charIndex < 0) {
          isDeleting = false;
          charIndex = 0;
          phraseIndex = (phraseIndex + 1) % texts.length;
          speed = 600; // Pause when fully erased
          // Switch font size right as the new phrase begins
          applyPhraseStyle(phraseIndex);
        }

        setTimeout(typeLoop, speed);
      };

      typeLoop();
    }

    // 2. Scroll Trigger Entrance Animations
    gsap.set(".footer-logo-brand-container", { y: 40, opacity: 0 });
    gsap.set(".footer-slogan-wrap", { y: 40, opacity: 0 });
    gsap.set(".footer-story-block > *", { y: 30, opacity: 0 });
    gsap.set(".footer-nav-block", { y: 30, opacity: 0 });
    gsap.set(".footer-bottom-bar", { y: 20, opacity: 0 });

    const footerTl = gsap.timeline({
      scrollTrigger: {
        trigger: ".site-footer",
        start: "top 85%",
        once: true,
      },
    });

    footerTl
      .to(".footer-logo-brand-container", {
        y: 0,
        opacity: 1,
        duration: 1.0,
        ease: "power3.out",
      })
      .to(
        ".footer-slogan-wrap",
        { y: 0, opacity: 1, duration: 1.0, ease: "power3.out" },
        "-=0.8",
      )
      .to(
        ".footer-story-block > *",
        { y: 0, opacity: 1, duration: 0.8, stagger: 0.12, ease: "power3.out" },
        "-=0.6",
      )
      .to(
        ".footer-nav-block",
        { y: 0, opacity: 1, duration: 0.8, stagger: 0.1, ease: "power3.out" },
        "-=0.6",
      )
      .to(
        ".footer-bottom-bar",
        { y: 0, opacity: 1, duration: 0.8, ease: "power2.out" },
        "-=0.4",
      );

    // 3. Background image — slow vertical parallax (desktop/tablet only)
    // Lightweight scrub keeps it locked to Lenis scroll velocity.
    const footerBgImg = document.querySelector(".footer-bg-image");
    if (footerBgImg && window.innerWidth > 768) {
      gsap.to(footerBgImg, {
        yPercent: 0,
        ease: "none",
        scrollTrigger: {
          trigger: ".site-footer",
          start: "top bottom",
          end: "bottom ",
          scrub: 1.4,
        },
      });
    }
  }
}; // end initAnimations
