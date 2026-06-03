// Initialize Lenis Smooth Scroll Engine
window.initSmoothScroll = () => {
  const lenis = new Lenis({
    duration: 1.2,
    easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)),
    direction: 'vertical',
    gestureDirection: 'vertical',
    smooth: true,
    mouseMultiplier: 1,
    smoothTouch: false,
    touchMultiplier: 2,
    infinite: false,
  });

  // ── CRITICAL: Bridge Lenis scroll position to GSAP ScrollTrigger ──────────
  // Without this, ScrollTrigger reads native scroll position (0) instead of
  // Lenis's smoothed position, causing scrub animations to stutter or freeze.
  lenis.on('scroll', ScrollTrigger.update);

  // Tick Lenis inside GSAP's RAF loop for perfect sync
  gsap.ticker.add((time) => {
    lenis.raf(time * 1000);
  });

  // Disable GSAP's own lag smoothing — Lenis handles this
  gsap.ticker.lagSmoothing(0);

  // Expose Lenis globally
  window.lenis = lenis;
};
