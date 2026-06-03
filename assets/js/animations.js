// =============================================================================
// CloudCush — animations.js
// =============================================================================
gsap.registerPlugin(ScrollTrigger);

window.initAnimations = () => {

  // ---------------------------------------------------------------------------
  // HERO ENTRANCE
  // ---------------------------------------------------------------------------
  const heroTitle = document.querySelector('.hero-title');
  if (heroTitle) {
    const tl = gsap.timeline({ defaults: { ease: 'power4.out' } });

    gsap.set('.announcement-bar',               { yPercent: -100 });
    gsap.set('.site-header',                    { yPercent: -100, opacity: 0 });
    gsap.set('.hero-title',                     { y: 100, opacity: 0 });
    gsap.set('.grid-col-line',                  { scaleY: 0 });
    gsap.set('.hero-left-text',                 { x: -50, opacity: 0 });
    gsap.set('.hero-right-text',                { x: 50,  opacity: 0 });
    gsap.set('.baby-main',                      { xPercent: -50 });
    gsap.set('.baby-ghost',                     { rotation: 6 });
    gsap.set('.baby-main-layer[data-index="1"]',  { scale: 0.8, opacity: 0 });
    gsap.set('.baby-ghost-layer[data-index="1"]', { scale: 0.9, opacity: 0 });
    gsap.set('.hero-col-right .btn-pill',       { y: 30, opacity: 0 });

    tl.to('.announcement-bar', { yPercent: 0, duration: 0.6 })
      .to('.site-header',  { yPercent: 0, opacity: 1, duration: 0.8 }, '-=0.2')
      .to('.hero-title',   { y: 0, opacity: 1, duration: 1.2, ease: 'power3.out' }, '-=0.4')
      .to('.grid-col-line',{ scaleY: 1, transformOrigin: 'top center', duration: 1.2, stagger: 0.1, ease: 'power2.inOut' }, '-=0.9')
      .to('.hero-left-text',  { x: 0, opacity: 1, duration: 0.8 }, '-=0.8')
      .to('.hero-right-text', { x: 0, opacity: 1, duration: 0.8 }, '-=0.8')
      .to('.baby-main-layer[data-index="1"]',  { scale: 1, opacity: 1, duration: 1.2, ease: 'back.out(1.1)' }, '-=0.7')
      .to('.baby-ghost-layer[data-index="1"]', { scale: 1.2, opacity: 0.12, duration: 1.5, ease: 'power2.out' }, '-=1.0')
      .to('.hero-col-right .btn-pill', { y: 0, opacity: 1, duration: 0.8 }, '-=0.9');

    gsap.to('.baby-image-wrapper', { y: -12, duration: 4, repeat: -1, yoyo: true, ease: 'sine.inOut' });
    gsap.to('.baby-ghost-layer',   { x: 10,  duration: 5, repeat: -1, yoyo: true, ease: 'sine.inOut' });

    // Image slider
    let currentActive = 1;
    const totalImages = 3;
    setInterval(() => {
      const nextActive = currentActive === totalImages ? 1 : currentActive + 1;
      const cMain  = document.querySelector(`.baby-main-layer[data-index="${currentActive}"]`);
      const nMain  = document.querySelector(`.baby-main-layer[data-index="${nextActive}"]`);
      const cGhost = document.querySelector(`.baby-ghost-layer[data-index="${currentActive}"]`);
      const nGhost = document.querySelector(`.baby-ghost-layer[data-index="${nextActive}"]`);
      if (nMain)  { nMain.style.display  = 'block'; gsap.fromTo(nMain,  { opacity: 0, scale: 0.95 }, { opacity: 1,    scale: 1,   duration: 1.2, ease: 'power2.out' }); }
      if (nGhost) { nGhost.style.display = 'block'; gsap.fromTo(nGhost, { opacity: 0, scale: 1.1  }, { opacity: 0.12, scale: 1.2, duration: 1.4, ease: 'power2.out' }); }
      if (cMain)  gsap.to(cMain,  { opacity: 0, scale: 0.9,  duration: 1.2, ease: 'power2.out', onComplete: () => { cMain.style.display  = 'none'; } });
      if (cGhost) gsap.to(cGhost, { opacity: 0, scale: 1.05, duration: 1.4, ease: 'power2.out', onComplete: () => { cGhost.style.display = 'none'; } });
      currentActive = nextActive;
    }, 3500);
  } // end heroTitle guard

  const mm = gsap.matchMedia();

  if (document.querySelector('.hero')) {
    mm.add("(min-width: 769px)", () => {
      ScrollTrigger.create({
        trigger: '.hero',
        start: 'top 110px',
        end: 'bottom 110px',
        pin: true,
        pinSpacing: false,
        invalidateOnRefresh: true
      });
    });
  }

  // ---------------------------------------------------------------------------
  // SHOWCASE
  // ---------------------------------------------------------------------------
  if (document.querySelector('.showcase-section')) {
    gsap.set('.showcase-title',             { y: 60, opacity: 0 });
    gsap.set('.feature-badge',              { y: 30, opacity: 0 });
    gsap.set('.showcase-desc',              { y: 30, opacity: 0 });
    gsap.set('.showcase-col-left .btn-pill',{ y: 30, opacity: 0 });

    const showcaseTl = gsap.timeline({
      scrollTrigger: { trigger: '.showcase-section', start: 'top 75%', toggleActions: 'play none none reverse' }
    });
    showcaseTl
      .to('.showcase-title',             { y: 0, opacity: 1, duration: 1.1, ease: 'power3.out' })
      .to('.feature-badge',              { y: 0, opacity: 1, duration: 0.8, ease: 'power3.out' }, '-=0.8')
      .to('.showcase-desc',              { y: 0, opacity: 1, duration: 0.8, stagger: 0.15, ease: 'power3.out' }, '-=0.7')
      .to('.showcase-col-left .btn-pill',{ y: 0, opacity: 1, duration: 0.8, ease: 'power3.out' }, '-=0.6');
  }
  const diaperVideo = document.querySelector('.diaper-video');
  if (diaperVideo) {
    let targetPBR = 0.8, currentPBR = 0.8;
    window.updateDiaperScrollVelocity = (v) => {
      targetPBR = 0.8 + Math.max(-8, Math.min(8, v)) * 0.12;
    };
    (function tick() {
      targetPBR  += (0.8 - targetPBR)  * 0.05;
      currentPBR += (targetPBR - currentPBR) * 0.1;
      diaperVideo.playbackRate = Math.max(0.1, Math.min(3.0, currentPBR));
      requestAnimationFrame(tick);
    })();
  }

  // ---------------------------------------------------------------------------
  // TRIAL
  // ---------------------------------------------------------------------------
  if (document.querySelector('.trial-section')) {
    gsap.set('.trial-title',          { y: 50, opacity: 0 });
    gsap.set('.trial-desc',           { y: 30, opacity: 0 });
    gsap.set('.trial-form-group',     { y: 30, opacity: 0 });
    gsap.set('.trial-submit-wrapper', { y: 30, opacity: 0 });

    const trialTl = gsap.timeline({
      scrollTrigger: { trigger: '.trial-section', start: 'top 80%', toggleActions: 'play none none reverse' }
    });
    trialTl
      .to('.trial-title',          { y: 0, opacity: 1, duration: 1.1, ease: 'power3.out' })
      .to('.trial-desc',           { y: 0, opacity: 1, duration: 0.8, ease: 'power3.out' }, '-=0.8')
      .to('.trial-form-group',     { y: 0, opacity: 1, duration: 0.8, stagger: 0.15, ease: 'power3.out' }, '-=0.7')
      .to('.trial-submit-wrapper', { y: 0, opacity: 1, duration: 0.8, ease: 'power3.out' }, '-=0.6');
  }

  // ===========================================================================
  // STACKED SCROLL SECTION
  // ===========================================================================

  const stackSection     = document.querySelector('#stackSection');
  if (stackSection) {
    const p1               = document.querySelector('#stackPanel1');
    const p2               = document.querySelector('#stackPanel2');
    const p3               = document.querySelector('#stackPanel3');
    const p4               = document.querySelector('#stackPanel4');
    const p5               = document.querySelector('#stackPanel5');
    const stackPanels      = document.querySelector('#stackPanels');
    const stackLeftContent = document.querySelector('#stackLeftContent');
    const stackPerks       = document.querySelectorAll('.stack-perk');
    const stackCta         = document.querySelector('#stackCta');

    if (p1 && p2 && p3 && p4 && p5 && stackPanels && stackLeftContent) {
      mm.add("(min-width: 769px)", () => {

        const VH = window.innerHeight;
        const VW = window.innerWidth;
        const shiftX = VW * 0.52;

        gsap.set([p1, p2, p3, p4], { y: VH, opacity: 1 });
        gsap.set(p5,               { x: -shiftX, opacity: 0 });
        gsap.set(stackPanels,      { x: 0 });
        gsap.set(stackLeftContent, { opacity: 0, x: -50 });
        gsap.set(stackPerks,       { opacity: 0, y: 14 });
        if (stackCta) gsap.set(stackCta, { opacity: 0, y: 14 });

        const entryTl = gsap.timeline({
          scrollTrigger: {
            trigger:       '#stackSection',
            start:         'top 85%',
            toggleActions: 'play none none reverse'
          }
        });
        entryTl
          .to(p1, { y: 0, duration: 0.70, ease: 'power3.out' }, 0.00)
          .to(p2, { y: 0, duration: 0.70, ease: 'power3.out' }, 0.18)
          .to(p3, { y: 0, duration: 0.70, ease: 'power3.out' }, 0.36)
          .to(p4, { y: 0, duration: 0.70, ease: 'power3.out' }, 0.54);

        const scrubTl = gsap.timeline({
          scrollTrigger: {
            trigger:         '#stackSection',
            start:           'top top',
            end:             '+=240%',
            pin:             true,
            pinSpacing:      true,
            scrub:           0.8,
            anticipatePin:   1,
            invalidateOnRefresh: true,
          }
        });

        scrubTl.to({}, { duration: 0.12 }, 0.00);

        scrubTl.to(stackPanels, {
          x:        shiftX,
          duration: 0.30,
          ease:     'power2.inOut'
        }, 0.12);

        scrubTl.to([p1, p2, p3, p4], {
          opacity:  0,
          duration: 0.15,
          stagger:  0.03,
          ease:     'power1.in'
        }, 0.20);

        scrubTl.to(p5, {
          x:        0,
          opacity:  1,
          duration: 0.30,
          ease:     'power2.inOut'
        }, 0.12);

        scrubTl.to({}, { duration: 0.07 }, 0.42);

        scrubTl.to(stackLeftContent, {
          opacity:  1,
          x:        0,
          duration: 0.14,
          ease:     'power3.out',
          onStart:  () => { stackLeftContent.style.pointerEvents = 'all'; }
        }, 0.52);

        scrubTl.to(stackPerks, {
          opacity:  1,
          y:        0,
          duration: 0.10,
          stagger:  0.025,
          ease:     'power2.out'
        }, 0.68);

        if (stackCta) {
          scrubTl.to(stackCta, {
            opacity:  1,
            y:        0,
            duration: 0.08,
            ease:     'power2.out'
          }, 0.92);
        }

        return () => {
          entryTl.kill();
          gsap.set([p1, p2, p3, p4, p5], { clearProps: 'all' });
          gsap.set(stackPanels,           { clearProps: 'all' });
          gsap.set(stackLeftContent,      { clearProps: 'all' });
          gsap.set(stackPerks,            { clearProps: 'all' });
          if (stackCta) gsap.set(stackCta, { clearProps: 'all' });
        };

      });

      // Mobile — static layout (stack section)
      mm.add("(max-width: 768px)", () => {
        const p1 = document.querySelector('#stackPanel1');
        const p2 = document.querySelector('#stackPanel2');
        const p3 = document.querySelector('#stackPanel3');
        const p4 = document.querySelector('#stackPanel4');
        const p5 = document.querySelector('#stackPanel5');
        const stackPanels      = document.querySelector('#stackPanels');
        const stackLeftContent = document.querySelector('#stackLeftContent');
        const stackPerks       = document.querySelectorAll('.stack-perk');
        const stackCta         = document.querySelector('#stackCta');
        gsap.set([p1, p2, p3, p4, p5], { clearProps: 'all' });
        gsap.set(stackPanels,           { clearProps: 'all' });
        gsap.set(stackLeftContent,      { clearProps: 'all' });
        gsap.set(stackPerks,            { clearProps: 'all' });
        if (stackCta) gsap.set(stackCta, { clearProps: 'all' });
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

  const catnavSection  = document.querySelector('#catnavSection');
  if (catnavSection) {
    const catnavItems    = document.querySelectorAll('.catnavItem');
    const catnavPanels   = document.querySelectorAll('.catnavPanel');
    const catnavProgBar  = document.querySelector('#catnavProgressBar');

    if (catnavItems.length && catnavPanels.length) {
      const TOTAL_TABS  = catnavItems.length; // 5
      let activeTabIdx  = 0;
      let isTransitioning = false;

      // ── Cross-fade panel switch ───────────────────────────────────────────────
      const switchTab = (toIndex) => {
        if (toIndex === activeTabIdx) return;

        const fromIndex = activeTabIdx;
        activeTabIdx    = toIndex;

        // Update nav item classes
        catnavItems.forEach((item, i) => {
          item.classList.toggle('active', i === toIndex);
        });

        // Cross-fade panels
        const fromPanel = catnavPanels[fromIndex];
        const toPanel   = catnavPanels[toIndex];

        if (fromPanel && fromPanel !== toPanel) {
          // Fade out old panel
          gsap.to(fromPanel, {
            opacity:  0,
            duration: 0.60,
            ease:     'power2.inOut',
            overwrite: true,
            onComplete: () => {
              fromPanel.classList.remove('active');
              fromPanel.style.pointerEvents = 'none';
            }
          });
        }

        if (toPanel) {
          toPanel.classList.add('active');
          toPanel.style.pointerEvents = 'auto';
          // Fade in new panel — slight y lift for premium feel
          gsap.fromTo(toPanel,
            { opacity: 0 },
            {
              opacity:  1,
              duration: 0.70,
              ease:     'power2.inOut',
              overwrite: true
            }
          );
        }
      };

      // ── Initial state ────────────────────────────────────────────────────────
      catnavPanels.forEach((panel, i) => {
        gsap.set(panel, { opacity: i === 0 ? 1 : 0 });
        panel.style.pointerEvents = i === 0 ? 'auto' : 'none';
        if (i !== 0) panel.classList.remove('active');
      });
      if (catnavProgBar) gsap.set(catnavProgBar, { width: '0%' });

      // ── Desktop: pinned scrub ScrollTrigger ──────────────────────────────────
      mm.add("(min-width: 769px)", () => {

        let catnavST = null;

        catnavST = ScrollTrigger.create({
          trigger:       '#catnavSection',
          start:         'top top',
          end:           '+=500%',       // 5 tabs × 100vh = 500vh pinned scroll
          pin:           true,
          pinSpacing:    true,
          scrub:         1.2,            // smooth, cinematic scrub
          anticipatePin: 1,
          invalidateOnRefresh: true,

          onUpdate: (self) => {
            const progress = self.progress; // 0 → 1

            // Map progress to tab index: 5 equal zones
            // Clamp so last tab holds at progress = 1.0
            let targetIndex = Math.min(
              TOTAL_TABS - 1,
              Math.floor(progress * TOTAL_TABS)
            );

            if (targetIndex !== activeTabIdx) {
              switchTab(targetIndex);
            }

            // Progress bar — direct width update (no GSAP for perf)
            if (catnavProgBar) {
              catnavProgBar.style.width = (progress * 100) + '%';
            }
          }
        });

        // ── Nav item click → smooth scroll to that tab's scroll position ────────
        // Uses Lenis if available (window.lenis), falls back to native scrollTo.
        catnavItems.forEach((item, i) => {
          item.addEventListener('click', () => {
            if (!catnavST) return;

            // Target the middle of that tab's scroll zone for clean activation
            const targetProgress = (i + 0.3) / TOTAL_TABS;
            const rawScroll      = catnavST.start + (catnavST.end - catnavST.start) * targetProgress;

            if (window.lenis) {
              window.lenis.scrollTo(rawScroll, { duration: 1.2, easing: (t) => 1 - Math.pow(1 - t, 4) });
            } else {
              window.scrollTo({ top: rawScroll, behavior: 'smooth' });
            }
          });
        });

        return () => {
          // Reset on breakpoint exit
          catnavItems.forEach((item, i) => {
            item.classList.toggle('active', i === 0);
          });
          catnavPanels.forEach((panel, i) => {
            gsap.set(panel, { clearProps: 'opacity' });
            panel.classList.toggle('active', i === 0);
            panel.style.pointerEvents = i === 0 ? 'auto' : 'none';
          });
          activeTabIdx = 0;
          if (catnavProgBar) catnavProgBar.style.width = '0%';
        };

      }); // end desktop mm for catnav
    }
  }




  // ===========================================================================
  // CORE COLLECTION — Premium Carousel + GSAP Hover Reveal
  // ===========================================================================
  // No external carousel lib needed (no Swiper/Slick in project).
  // Custom carousel built with GSAP + Pointer Events for drag/swipe.
  // ===========================================================================

  const ccSection   = document.querySelector('#coreCollectionSection');
  if (ccSection) {
    const ccTrack     = document.querySelector('#ccTrack');
    const ccViewport  = document.querySelector('#ccViewport');
    const ccPrevBtn   = document.querySelector('#ccPrev');
    const ccNextBtn   = document.querySelector('#ccNext');
    const ccDotBtns   = document.querySelectorAll('[data-cc-dot]');
    const ccCards     = document.querySelectorAll('.collection-card');

    if (ccTrack && ccViewport && ccCards.length > 0) {
      // ── Section scroll-in animation (ScrollTrigger) ────────────────────────────
      const ccTitle = ccSection.querySelector('.core-collection-title');
      const ccNav   = ccSection.querySelector('.cc-nav');
      if (ccTitle) gsap.set(ccTitle, { y: 40, opacity: 0 });
      if (ccNav)   gsap.set(ccNav, { y: 20, opacity: 0 });
      gsap.set(ccCards, { y: 60, opacity: 0 });

      const ccEntryTl = gsap.timeline({
        scrollTrigger: {
          trigger: '#coreCollectionSection',
          start:   'top 72%',
          toggleActions: 'play none none reverse'
        }
      });

      if (ccTitle) ccEntryTl.to(ccTitle, { y: 0, opacity: 1, duration: 0.9, ease: 'power3.out' }, 0);
      if (ccNav)   ccEntryTl.to(ccNav,   { y: 0, opacity: 1, duration: 0.7, ease: 'power3.out' }, 0.15);

      ccEntryTl.to(ccCards, {
        y: 0, opacity: 1,
        duration: 0.7,
        stagger: 0.13,
        ease: 'power3.out'
      }, 0.20);

      // ── Carousel State ──────────────────────────────────────────────────────────
      let ccIndex    = 0;
      let ccPerView  = 3; // updated on resize
      let ccTotal    = ccCards.length;
      let isAnimating = false;

      const getPerView = () => {
        const w = window.innerWidth;
        if (w <= 768)  return 1;
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
          dot.classList.toggle('active', i === idx);
        });
      };

      const updateNavState = (idx) => {
        if (ccPrevBtn) ccPrevBtn.classList.toggle('cc-disabled', idx <= 0);
        if (ccNextBtn) ccNextBtn.classList.toggle('cc-disabled', idx >= maxIndex());
      };

      const goToSlide = (idx, duration = 0.6) => {
        if (isAnimating) return;
        isAnimating = true;

        ccIndex = Math.max(0, Math.min(idx, maxIndex()));
        const gap       = getGap();
        const cardWidth = getCardWidth();
        const offset    = -(ccIndex * (cardWidth + gap));

        gsap.to(ccTrack, {
          x: offset,
          duration,
          ease: 'power3.out',
          onComplete: () => { isAnimating = false; }
        });

        updateDots(ccIndex);
        updateNavState(ccIndex);
      };

      // ── Prev / Next button handlers ─────────────────────────────────────────────
      if (ccPrevBtn) {
        ccPrevBtn.addEventListener('click', () => {
          if (ccIndex > 0) goToSlide(ccIndex - 1);
        });
      }

      if (ccNextBtn) {
        ccNextBtn.addEventListener('click', () => {
          if (ccIndex < maxIndex()) goToSlide(ccIndex + 1);
        });
      }

      // ── Dot button handlers ─────────────────────────────────────────────────────
      ccDotBtns.forEach((dot) => {
        dot.addEventListener('click', () => {
          const targetIdx = parseInt(dot.dataset.ccDot, 10);
          goToSlide(targetIdx);
        });
      });

      // ── Drag / Touch / Pointer support ─────────────────────────────────────────
      let dragStartX     = 0;
      let dragCurrentX   = 0;
      let isDragging     = false;
      let trackBaseX     = 0;
      const DRAG_THRESHOLD = 40; // px to trigger slide change

      const onPointerDown = (e) => {
        isDragging   = true;
        dragStartX   = e.type === 'touchstart' ? e.touches[0].clientX : e.clientX;
        trackBaseX   = gsap.getProperty(ccTrack, 'x');
        gsap.killTweensOf(ccTrack);
        ccViewport.style.cursor = 'grabbing';
      };

      const onPointerMove = (e) => {
        if (!isDragging) return;
        dragCurrentX  = e.type === 'touchmove' ? e.touches[0].clientX : e.clientX;
        const delta   = dragCurrentX - dragStartX;
        gsap.set(ccTrack, { x: trackBaseX + delta });
      };

      const onPointerUp = (e) => {
        if (!isDragging) return;
        isDragging = false;
        ccViewport.style.cursor = 'grab';
        const finalX  = e.type === 'touchend' ? (e.changedTouches[0]?.clientX ?? dragCurrentX) : e.clientX;
        const delta   = finalX - dragStartX;

        if (Math.abs(delta) > DRAG_THRESHOLD) {
          if (delta < 0 && ccIndex < maxIndex()) {
            goToSlide(ccIndex + 1, 0.5);
          } else if (delta > 0 && ccIndex > 0) {
            goToSlide(ccIndex - 1, 0.5);
          } else {
            goToSlide(ccIndex, 0.4); // snap back
          }
        } else {
          goToSlide(ccIndex, 0.4); // snap back
        }
      };

      // Mouse events
      ccViewport.addEventListener('mousedown',  onPointerDown);
      window.addEventListener('mousemove',  onPointerMove);
      window.addEventListener('mouseup',    onPointerUp);

      // Touch events
      ccViewport.addEventListener('touchstart', onPointerDown, { passive: true });
      ccViewport.addEventListener('touchmove',  onPointerMove, { passive: true });
      ccViewport.addEventListener('touchend',   onPointerUp);

      // Prevent link clicks after drag
      ccViewport.addEventListener('click', (e) => {
        if (Math.abs(dragCurrentX - dragStartX) > 8) {
          e.preventDefault();
          e.stopPropagation();
        }
      }, true);

      // ── GSAP Hover Reveal — image expands to fill card, CTA appears ─────────────
      // Architecture:
      //   - .collection-image-wrap  → GSAP height: '58%' → '100%'  (fill card)
      //   - .collection-image       → GSAP scale: 1.06 → 1.0       (de-zoom)
      //   - .collection-info        → GSAP opacity: 1 → 0           (hide text)
      //   - .collection-cta-wrap    → GSAP opacity: 0→1, y: 20→0   (reveal pill)
      //
      // Only on non-touch devices. Touch: CSS forces CTA always visible (mobile).
      const isTouchDevice = () => ('ontouchstart' in window || navigator.maxTouchPoints > 0);

      if (!isTouchDevice()) {
        ccCards.forEach((card) => {
          const imgWrap = card.querySelector('.collection-image-wrap');
          const img     = card.querySelector('.collection-image');
          const info    = card.querySelector('.collection-info');
          const ctaWrap = card.querySelector('.collection-cta-wrap');

          // ── Hover IN ──────────────────────────────────────────────────────────
          const hoverIn = gsap.timeline({ paused: true })
            .to(imgWrap, { height: '100%', duration: 0.60, ease: 'power3.out' }, 0)
            .to(img,     { scale: 1.0, duration: 0.60, ease: 'power3.out' }, 0)
            .to(info,    { opacity: 0, duration: 0.25, ease: 'power2.in' }, 0)
            .to(ctaWrap, { opacity: 1, y: 0, duration: 0.50, ease: 'power3.out' }, 0.18);

          // ── Hover OUT ─────────────────────────────────────────────────────────
          const hoverOut = gsap.timeline({ paused: true })
            .to(ctaWrap, { opacity: 0, y: 20, duration: 0.30, ease: 'power2.in' }, 0)
            .to(info,    { opacity: 1, duration: 0.35, ease: 'power2.out' }, 0.08)
            .to(imgWrap, { height: '58%', duration: 0.55, ease: 'power3.out' }, 0.05)
            .to(img,     { scale: 1.06, duration: 0.55, ease: 'power3.out' }, 0.05);

          card.addEventListener('mouseenter', () => {
            hoverOut.pause();
            hoverIn.restart();
          });

          card.addEventListener('mouseleave', () => {
            hoverIn.pause();
            hoverOut.restart();
          });
        });
      }

      // ── Resize — recalculate layout ─────────────────────────────────────────────
      let resizeTimer;
      window.addEventListener('resize', () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
          const newPerView = getPerView();
          if (newPerView !== ccPerView) {
            ccPerView = newPerView;
            ccIndex   = 0;
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
  const philSection = document.getElementById('philosophySection');
  if (philSection) {
    const philBgImg    = document.getElementById('philosophyBgImg');
    const philOverlay  = document.getElementById('philosophyOverlay');
    
    // Panel containers
    const philPanel1   = document.getElementById('philPanel1');
    const philPanel2   = document.getElementById('philPanel2');

    // Entrance elements (for the first panel)
    const philEyebrow  = document.getElementById('philEyebrow');
    const philBody     = document.getElementById('philBody');
    const philCta      = document.getElementById('philCta');

    // Set initial states
    if (philPanel2) {
      gsap.set(philPanel2.querySelectorAll('.philosophy-eyebrow-wrap, .philosophy-body, .philosophy-cta-wrap'), {
        opacity: 1,
        y: 0
      });
      gsap.set(philPanel2, { opacity: 0, y: 40, pointerEvents: 'none' });
    }

    if (philPanel1) {
      gsap.set(philPanel1, { opacity: 1, y: 0, pointerEvents: 'auto' });
    }

    // ── 1. Section Entrance Animation ────────────────────────────────────────
    const philEntranceTl = gsap.timeline({
      scrollTrigger: {
        trigger: philSection,
        start:   'top 80%',
        end:     'top 30%',
        toggleActions: 'play none none reverse',
      }
    });

    if (philBgImg) {
      philEntranceTl.to(philBgImg, { scale: 1.0, duration: 1.8, ease: 'power2.out' }, 0);
    }

    if (philOverlay) {
      philEntranceTl.to(philOverlay, { opacity: 1, duration: 1.2, ease: 'power2.out' }, 0);
    }

    if (philEyebrow) {
      philEntranceTl.to(philEyebrow, { opacity: 1, y: 0, duration: 1.0, ease: 'power3.out' }, 0.30);
    }

    if (philBody) {
      philEntranceTl.to(philBody, { opacity: 1, y: 0, duration: 1.1, ease: 'power3.out' }, 0.50);
    }

    if (philCta) {
      philEntranceTl.to(philCta, { opacity: 1, y: 0, duration: 0.9, ease: 'power3.out' }, 0.75);
    }

    // ── 2. Scroll Pinned Panel Transition & Parallax ─────────────────────────
    const isMobile = () => window.innerWidth <= 768;

    if (philPanel1 && philPanel2) {
      const philPinTl = gsap.timeline({
        scrollTrigger: {
          trigger: philSection,
          start:   'top top',
          end:     '+=120%',
          pin:     true,
          scrub:   1.0,
          anticipatePin: 1
        }
      });

      philPinTl.to(philPanel1, {
        opacity: 0,
        y: -40,
        duration: 1.0,
        ease: 'power2.inOut'
      }, 0);

      philPinTl.set(philPanel1, { pointerEvents: 'none' }, 0.5);
      philPinTl.set(philPanel2, { pointerEvents: 'auto' }, 0.5);

      philPinTl.to(philPanel2, {
        opacity: 1,
        y: 0,
        duration: 1.0,
        ease: 'power2.inOut'
      }, 0.4);

      if (!isMobile() && philBgImg) {
        philPinTl.to(philBgImg, {
          yPercent: -12,
          ease: 'none',
          duration: 1.4
        }, 0);
      }
    }
  }

  // =============================================================================
  // MOM-APPROVED MOMENTS — Infinite Autoplay + Drag Carousel
  // =============================================================================
  const momSection = document.getElementById('momMomentsSection');
  if (momSection) {
    const momViewport = document.getElementById('momCarouselViewport');
    const momTrack    = document.getElementById('momCarouselTrack');

    if (momViewport && momTrack) {
      // 1. Clone cards to create a seamless infinite loop
      const originalCards = Array.from(momTrack.children);
      originalCards.forEach(card => {
        const clone = card.cloneNode(true);
        momTrack.appendChild(clone);
      });

      // Prevent native image and video dragging browser behaviors
      momTrack.querySelectorAll('img, video').forEach(el => {
        el.addEventListener('dragstart', (e) => e.preventDefault());
      });

      // Ensure all videos (original + cloned) start playing muted
      momTrack.querySelectorAll('video').forEach(video => {
        video.muted = true;
        video.play().catch(() => {});
      });

      // 2. Section Scroll Entrance Animation (Stagger reveal including clones)
      const allMomCards = momTrack.querySelectorAll('.mom-card');
      if (allMomCards.length > 0) {
        gsap.set(allMomCards, { y: 60, opacity: 0 });
        gsap.to(allMomCards, {
          y: 0,
          opacity: 1,
          duration: 0.8,
          stagger: 0.08,
          ease: 'power3.out',
          scrollTrigger: {
            trigger: '#momMomentsSection',
            start: 'top 75%',
            toggleActions: 'play none none reverse'
          }
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
        if (isDragging || isHovered || (momentumTween && momentumTween.isActive())) return;
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

      momViewport.addEventListener('pointerdown', (e) => {
        // Only run for primary clicks/touches
        if (e.button !== 0 && e.pointerType === 'mouse') return;

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

      momViewport.addEventListener('pointermove', (e) => {
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

      momViewport.addEventListener('pointerup', (e) => {
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

            momentumTween = gsap.to({ val: currentX }, {
              val: targetX,
              duration: 1.2,
              ease: 'power2.out',
              onUpdate: function () {
                currentX = this.targets()[0].val;
                wrapX();
                gsap.set(momTrack, { x: currentX });
              }
            });
          }
        }
      });

      momViewport.addEventListener('pointercancel', (e) => {
        isDragging = false;
        try {
          momViewport.releasePointerCapture(e.pointerId);
        } catch (err) {}
      });

      // Pause on hover
      momViewport.addEventListener('mouseenter', () => {
        if (!isDragging) isHovered = true;
      });
      momViewport.addEventListener('mouseleave', () => {
        isHovered = false;
      });

      // Handle window resizing
      window.addEventListener('resize', () => {
        singleLoopWidth = originalCards.length * (getCardWidth() + getGap());
      });
    }
  }

}; // end initAnimations
