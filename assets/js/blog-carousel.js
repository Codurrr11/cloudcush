/**
 * CloudCush Journal Blog Slider/Carousel
 * Standalone module handling premium dragging, arrow snap, touch support, autoplay and dot pagination.
 */
window.initBlogCarousel = () => {
  const section = document.getElementById("blogCarouselSection");
  if (!section) return;

  const viewport = document.getElementById("blogCarouselViewport");
  const track = document.getElementById("blogCarouselTrack");
  const prevBtn = section.querySelector(".prev-btn");
  const nextBtn = section.querySelector(".next-btn");
  const dotsContainer = section.querySelector(".blog-carousel-dots");
  const cards = Array.from(track.querySelectorAll(".blog-carousel-card"));

  if (!viewport || !track || !cards.length) return;

  // Prevent default image dragging in browser
  cards.forEach(card => {
    card.querySelectorAll("img").forEach(img => {
      img.addEventListener("dragstart", e => e.preventDefault());
    });
  });

  let currentTranslateX = 0;
  let targetTranslateX = 0;
  let currentIndex = 0;
  let slideWidth = 0;
  let maxTranslateX = 0;
  let isDragging = false;
  let dragStartX = 0;
  let dragStartTranslate = 0;
  let dragOffset = 0;
  let hasDragged = false;
  let autoplayTimer = null;
  let isHovered = false;

  // Calculate slide dimensions and layouts
  const updateLayout = () => {
    const viewportWidth = viewport.getBoundingClientRect().width;
    const firstCard = cards[0].getBoundingClientRect();
    const cardWidth = firstCard.width;
    const style = window.getComputedStyle(track);
    const gap = parseFloat(style.gap) || 32;

    slideWidth = cardWidth + gap;

    // Track total width
    const totalTrackWidth = (cards.length * cardWidth) + ((cards.length - 1) * gap);
    
    // We shouldn't translate beyond the edge of the viewport
    maxTranslateX = Math.min(0, -(totalTrackWidth - viewportWidth));

    // Keep active slide in bounds
    goToSlide(currentIndex, false);
    buildDots();
  };

  // Build dots pagination dynamically
  const buildDots = () => {
    if (!dotsContainer) return;
    dotsContainer.innerHTML = "";

    const viewportWidth = viewport.getBoundingClientRect().width;
    const firstCard = cards[0].getBoundingClientRect();
    const cardWidth = firstCard.width;
    const style = window.getComputedStyle(track);
    const gap = parseFloat(style.gap) || 32;
    const totalTrackWidth = (cards.length * cardWidth) + ((cards.length - 1) * gap);

    if (totalTrackWidth <= viewportWidth) return;

    // Generate dot items based on snap possibilities
    cards.forEach((_, idx) => {
      const slidePos = -idx * slideWidth;
      const targetPos = Math.max(maxTranslateX, slidePos);

      // Avoid creating redundant dots at the end
      if (idx > 0) {
        const prevSlidePos = -(idx - 1) * slideWidth;
        const prevTargetPos = Math.max(maxTranslateX, prevSlidePos);
        if (Math.abs(targetPos - prevTargetPos) < 5 && idx < cards.length - 1) {
          return;
        }
      }

      const dot = document.createElement("button");
      dot.className = `blog-dot${idx === currentIndex ? " active" : ""}`;
      dot.setAttribute("aria-label", `Go to slide ${idx + 1}`);
      dot.addEventListener("click", () => {
        pauseAutoplay();
        goToSlide(idx);
        startAutoplay();
      });
      dotsContainer.appendChild(dot);
    });
    
    updateDotsActiveState();
  };

  const updateDotsActiveState = () => {
    if (!dotsContainer) return;
    const dots = Array.from(dotsContainer.querySelectorAll(".blog-dot"));
    if (!dots.length) return;

    let closestIndex = 0;
    let minDiff = Infinity;

    dots.forEach((dot, idx) => {
      const dotTargetIdx = Math.min(idx, cards.length - 1);
      const dotPos = Math.max(maxTranslateX, -dotTargetIdx * slideWidth);
      const diff = Math.abs(currentTranslateX - dotPos);
      if (diff < minDiff) {
        minDiff = diff;
        closestIndex = idx;
      }
    });

    dots.forEach((dot, idx) => {
      if (idx === closestIndex) {
        dot.classList.add("active");
      } else {
        dot.classList.remove("active");
      }
    });
  };

  // Go to a specific slide index with optional animation
  const goToSlide = (index, animate = true) => {
    currentIndex = Math.max(0, Math.min(index, cards.length - 1));

    let tx = -currentIndex * slideWidth;
    tx = Math.max(maxTranslateX, Math.min(0, tx));
    targetTranslateX = tx;
    currentTranslateX = tx;

    updateNavButtons();

    if (animate) {
      gsap.to(track, {
        x: targetTranslateX,
        duration: 0.6,
        ease: "power3.out",
        onUpdate: updateDotsActiveState
      });
    } else {
      gsap.set(track, { x: targetTranslateX });
      updateDotsActiveState();
    }
  };

  const updateNavButtons = () => {
    if (prevBtn) {
      if (currentTranslateX >= 0) {
        prevBtn.style.opacity = "0.4";
        prevBtn.style.pointerEvents = "none";
      } else {
        prevBtn.style.opacity = "1";
        prevBtn.style.pointerEvents = "auto";
      }
    }

    if (nextBtn) {
      if (currentTranslateX <= maxTranslateX + 5) {
        nextBtn.style.opacity = "0.4";
        nextBtn.style.pointerEvents = "none";
      } else {
        nextBtn.style.opacity = "1";
        nextBtn.style.pointerEvents = "auto";
      }
    }
  };

  // Navigation handlers
  if (prevBtn) {
    prevBtn.addEventListener("click", () => {
      pauseAutoplay();
      if (currentTranslateX >= 0) {
        const maxIndex = Math.ceil(Math.abs(maxTranslateX) / slideWidth);
        goToSlide(maxIndex);
      } else {
        goToSlide(currentIndex - 1);
      }
      startAutoplay();
    });
  }

  if (nextBtn) {
    nextBtn.addEventListener("click", () => {
      pauseAutoplay();
      if (currentTranslateX <= maxTranslateX + 5) {
        goToSlide(0);
      } else {
        goToSlide(currentIndex + 1);
      }
      startAutoplay();
    });
  }

  // Pointer dragging events
  viewport.addEventListener("pointerdown", (e) => {
    if (e.button !== 0 && e.pointerType === "mouse") return;

    isDragging = true;
    hasDragged = false;
    dragStartX = e.clientX;
    dragStartTranslate = currentTranslateX;
    pauseAutoplay();

    if (track.style.transition) track.style.transition = "none";
  });

  window.addEventListener("pointermove", (e) => {
    if (!isDragging) return;

    const currentX = e.clientX;
    dragOffset = currentX - dragStartX;
    
    let tx = dragStartTranslate + dragOffset;
    if (tx > 0) {
      tx = tx * 0.3; // rubber band effect
    } else if (tx < maxTranslateX) {
      tx = maxTranslateX + (tx - maxTranslateX) * 0.3;
    }

    targetTranslateX = tx;
    gsap.set(track, { x: targetTranslateX });

    if (Math.abs(dragOffset) > 10) {
      hasDragged = true;
      viewport.classList.add("dragging");
    }
  });

  const handleDragEnd = () => {
    if (!isDragging) return;
    isDragging = false;
    viewport.classList.remove("dragging");

    currentTranslateX = targetTranslateX;

    let targetIndex = Math.round(-currentTranslateX / slideWidth);
    targetIndex = Math.max(0, Math.min(targetIndex, cards.length - 1));

    goToSlide(targetIndex);
    startAutoplay();
  };

  window.addEventListener("pointerup", handleDragEnd);
  window.addEventListener("pointercancel", handleDragEnd);

  // Prevent link click when dragging
  track.addEventListener("click", (e) => {
    if (hasDragged) {
      e.preventDefault();
      e.stopPropagation();
    }
  }, { capture: true });

  // Autoplay functionality
  const startAutoplay = () => {
    if (isHovered) return;
    pauseAutoplay();
    autoplayTimer = setInterval(() => {
      if (currentTranslateX <= maxTranslateX + 5) {
        goToSlide(0);
      } else {
        goToSlide(currentIndex + 1);
      }
    }, 4500);
  };

  const pauseAutoplay = () => {
    if (autoplayTimer) {
      clearInterval(autoplayTimer);
      autoplayTimer = null;
    }
  };

  // Hover pauses autoplay
  section.addEventListener("mouseenter", () => {
    isHovered = true;
    pauseAutoplay();
  });

  section.addEventListener("mouseleave", () => {
    isHovered = false;
    startAutoplay();
  });

  // ── GSAP Hover Reveal for Blog Cards ──────────────────────────────────────
  const isTouchDevice = () =>
    "ontouchstart" in window || navigator.maxTouchPoints > 0;

  if (!isTouchDevice()) {
    cards.forEach((card) => {
      const imgWrap = card.querySelector(".blog-card-media");
      const img = card.querySelector(".blog-card-img");
      const details = card.querySelector(".blog-card-details");
      const overlay = card.querySelector(".blog-card-overlay");

      if (!imgWrap || !img || !details || !overlay) return;

      // ── Premium Reversible Hover Timeline ──────────────────────────────────
      const hoverTl = gsap.timeline({
        paused: true,
        defaults: { duration: 0.55, ease: "power3.out" }
      });

      hoverTl
        .to(imgWrap, { height: "100%", ease: "power3.inOut" }, 0)
        .to(img, { scale: 1.0, ease: "power3.inOut" }, 0)
        .to(card, { scale: 1.015, ease: "power3.out" }, 0)
        .to(details, { opacity: 0, y: -12, duration: 0.28, ease: "power2.in" }, 0)
        .to(overlay, { opacity: 1, y: 0, duration: 0.45, ease: "power3.out" }, 0.12);

      card.addEventListener("mouseenter", () => {
        hoverTl.play();
      });

      card.addEventListener("mouseleave", () => {
        hoverTl.reverse();
      });
    });
  }

  // Resize handler
  window.addEventListener("resize", updateLayout);

  // Initial call
  updateLayout();
  startAutoplay();
};

document.addEventListener("DOMContentLoaded", () => {
  if (typeof window.initBlogCarousel === "function") {
    window.initBlogCarousel();
  }
});
