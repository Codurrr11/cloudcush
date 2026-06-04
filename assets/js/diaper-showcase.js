/* =============================================================================
   CloudCush — diaper-showcase.js  v7
   Apple AirPods Style — Alternating left/right minimal text.
   Text enters from the bottom and exits to the top on scroll down.
   Pointer line with dot draws from text heading to product.
   Integrates with Three.js diaper model (diaper-three.js).
   ============================================================================= */

(function () {
  "use strict";

  // ─── Stage Data ─────────────────────────────────────────────────────────────
  const STAGES = [
    {
      num: "01",
      label: "Inner Layer",
      title: "CloudSoft Softness.",
      desc: "Ultra-fine microfiber weave from pharmaceutical-grade cotton pulp. Softer than silk against newborn skin — measurably, scientifically.",
      layout: "left",
    },
    {
      num: "02",
      label: "Protection Core",
      title: "LeakLock Barrier.",
      desc: "Triple-barrier containment channels redirect fluid in milliseconds. 99.8% leak-free across all sleep positions — clinically verified.",
      layout: "right",
    },
    {
      num: "03",
      label: "Air System",
      title: "Breathable Comfort.",
      desc: "Micro-perforated outer shell with 4,000+ air channels per square inch. Core temperature stays stable. Rash rate drops to near zero.",
      layout: "left",
    },
    {
      num: "04",
      label: "Fit System",
      title: "FlexFit Wings.",
      desc: "Dual-stretch elastic waistband and bilateral cuff wings conform to every body shape. No gaps. No restrictions. Every move.",
      layout: "right",
    },
    {
      num: "05",
      label: "Sleep Guard",
      title: "Overnight Protection.",
      desc: "Patented SAP core absorbs 12 hours of fluid, locking it from delicate skin through the deepest sleep — so you both rest.",
      layout: "left",
    },
  ];

  const SECTION_HEIGHT_PER_STAGE = 120; // vh
  let currentStage = -1;
  let scrollTriggerInstance = null;
  let showcaseEl = null;

  // ─── Init ────────────────────────────────────────────────────────────────────
  function init() {
    showcaseEl = document.querySelector(".cc-showcase");
    if (!showcaseEl) return;
    buildDOM();

    // Initialize Three.js 3D canvas
    const canvasEl = showcaseEl.querySelector(".cc-showcase__canvas");
    if (canvasEl && window.CCDiaperThree) {
      window.CCDiaperThree.init(canvasEl);
      window.CCDiaperThree.setVisible(true);

      // Hide loader once Three.js is initialized
      const loader = showcaseEl.querySelector(".cc-showcase__loader");
      if (loader) {
        loader.classList.add("cc-loaded");
      }
    }

    initScrollTrigger();
    entranceAnimation();
  }

  // ─── Build DOM ───────────────────────────────────────────────────────────────
  function buildDOM() {
    const stagesWrap = showcaseEl.querySelector(".cc-showcase__stages");
    if (!stagesWrap) return;

    STAGES.forEach((s, i) => {
      const el = document.createElement("div");
      el.className = "cc-showcase__stage";
      el.dataset.stage = i;
      el.dataset.layout = s.layout; // drives CSS positioning

      // For left layout: title text first, line second.
      // For right layout: line first, title text second.
      const titleContent = s.layout === "left"
        ? `<span class="cc-showcase__stage-title-text">${s.title}</span><span class="cc-showcase__pointer-line"></span>`
        : `<span class="cc-showcase__pointer-line"></span><span class="cc-showcase__stage-title-text">${s.title}</span>`;

      el.innerHTML = `
        <div class="cc-showcase__stage-inner">
          <h3 class="cc-showcase__stage-title">${titleContent}</h3>
          <p class="cc-showcase__stage-desc">${s.desc}</p>
        </div>
      `;
      stagesWrap.appendChild(el);
    });

    // Set track height
    const track = showcaseEl.querySelector(".cc-showcase__track");
    if (track)
      track.style.height = STAGES.length * SECTION_HEIGHT_PER_STAGE + "vh";
  }

  // ─── ScrollTrigger Setup ─────────────────────────────────────────────────────
  function initScrollTrigger() {
    if (typeof gsap === "undefined" || typeof ScrollTrigger === "undefined") {
      setStage(0, 1);
      return;
    }

    gsap.registerPlugin(ScrollTrigger);

    const track = showcaseEl.querySelector(".cc-showcase__track");
    const sticky = showcaseEl.querySelector(".cc-showcase__sticky");
    if (!track || !sticky) return;

    scrollTriggerInstance = ScrollTrigger.create({
      trigger: track,
      start: "top top",
      end: "bottom bottom",
      pin: sticky,
      pinSpacing: false,
      scrub: 1.4,
      onUpdate: (self) => {
        const idx = Math.round(self.progress * (STAGES.length - 1));
        if (idx !== currentStage) {
          setStage(idx, self.direction);
        }
        const fill = showcaseEl.querySelector(".cc-showcase__progress-fill");
        if (fill) fill.style.width = self.progress * 100 + "%";
      },
    });
  }

  // ─── Set Stage ───────────────────────────────────────────────────────────────
  function setStage(index, direction = 1) {
    if (index === currentStage) return;
    const prevIndex = currentStage;
    currentStage = index;
    const stage = STAGES[index];
    if (!stage) return;

    const stages = showcaseEl.querySelectorAll(".cc-showcase__stage");

    // Call Three.js 3D model stage transition
    if (window.CCDiaperThree) {
      window.CCDiaperThree.setStage(index);
    }

    // Y direction offsets for scroll transitions:
    // Scrolling down (direction = 1):
    //   Incoming text slides UP from bottom (y: 60 -> 0)
    //   Outgoing text slides UP to top (y: 0 -> -60)
    // Scrolling up (direction = -1):
    //   Incoming text slides DOWN from top (y: -60 -> 0)
    //   Outgoing text slides DOWN to bottom (y: 0 -> 60)
    const outY = direction === 1 ? -60 : 60;
    const inY = direction === 1 ? 60 : -60;

    // ── Outgoing text ─────────────────────────────────────────────────────────
    if (prevIndex >= 0 && stages[prevIndex]) {
      gsap.to(stages[prevIndex], {
        opacity: 0,
        y: outY,
        filter: "blur(6px)",
        duration: 0.42,
        ease: "power2.in",
        onComplete: () => {
          stages[prevIndex].classList.remove("cc-active");
          gsap.set(stages[prevIndex], { y: 0, filter: "blur(0px)" });
        },
      });
    }

    // ── Incoming text ─────────────────────────────────────────────────────────
    if (stages[index]) {
      stages[index].classList.add("cc-active");

      // Reset start positions
      gsap.set(stages[index], { opacity: 0, y: inY, filter: "blur(6px)" });

      // Animate fade-in + vertical slide
      gsap.to(stages[index], {
        opacity: 1,
        y: 0,
        filter: "blur(0px)",
        duration: 0.65,
        ease: "power2.out",
        delay: 0.08,
      });

      // Animate pointer line drawing effect via clipPath
      const line = stages[index].querySelector(".cc-showcase__pointer-line");
      if (line) {
        const startClip = stage.layout === "left" ? "inset(0 100% 0 0)" : "inset(0 0 0 100%)";
        gsap.fromTo(line,
          { clipPath: startClip },
          { clipPath: "inset(0 0 0 0)", duration: 0.6, ease: "power2.out", delay: 0.28 }
        );
      }
    }

    // ── Glow color per stage ──────────────────────────────────────────────────
    const glowEl = showcaseEl.querySelector(".cc-showcase__canvas-glow");
    const glowColors = [
      "rgba(142,177,217,0.18)",
      "rgba(80,120,180,0.12)",
      "rgba(190,225,250,0.22)",
      "rgba(200,180,130,0.12)",
      "rgba(142,177,217,0.22)",
    ];
    if (glowEl) {
      glowEl.style.background = `radial-gradient(ellipse 55% 55% at 50% 50%, ${glowColors[index] || glowColors[0]} 0%, transparent 70%)`;
    }
  }

  // ─── Entrance Animation ──────────────────────────────────────────────────────
  function entranceAnimation() {
    if (typeof gsap === "undefined" || typeof ScrollTrigger === "undefined")
      return;

    const eyebrow = showcaseEl.querySelector(".cc-showcase__eyebrow");
    const progBar = showcaseEl.querySelector(".cc-showcase__progress-bar");
    const canvasWrap = showcaseEl.querySelector(".cc-showcase__canvas-wrap");
    const divider = showcaseEl.querySelector(".cc-showcase__divider");

    ScrollTrigger.create({
      trigger: showcaseEl,
      start: "top 88%",
      once: true,
      onEnter: () => {
        gsap.to([eyebrow, progBar], {
          opacity: 1,
          y: 0,
          duration: 0.8,
          stagger: 0.13,
          ease: "power3.out",
        });

        if (divider)
          gsap.to(divider, { opacity: 1, duration: 0.8, delay: 0.4 });

        // Canvas wrap floats up elegantly
        if (canvasWrap) {
          gsap.fromTo(
            canvasWrap,
            { opacity: 0, y: 55, scale: 0.94 },
            {
              opacity: 1,
              y: 0,
              scale: 1,
              duration: 1.15,
              ease: "power3.out",
              delay: 0.08,
            },
          );
        }

        setTimeout(() => setStage(0, 1), 260);
      },
    });
  }

  // ─── Boot ────────────────────────────────────────────────────────────────────
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    setTimeout(init, 180);
  }

  window.CCDiaperShowcase = { setStage };
})();
