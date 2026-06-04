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
  if (window.lenis) {
    window.lenis.on("scroll", (e) => {
      const scrollY = e.scroll;

      // Update sticky navbar compressions
      if (typeof window.updateHeaderState === "function") {
        window.updateHeaderState(scrollY);
      }

      // Update diaper rotation velocity reaction
      if (typeof window.updateDiaperScrollVelocity === "function") {
        window.updateDiaperScrollVelocity(e.velocity);
      }

      // Parallax — homepage hero elements only
      if (document.querySelector(".baby-main")) {
        gsap.to(".baby-main", {
          y: scrollY * 0.12 - 6,
          scale: Math.max(0.9, 1 - scrollY * 0.0002),
          duration: 0.4,
          overwrite: "auto",
        });

        gsap.to(".baby-ghost", {
          y: scrollY * 0.2 + 4,
          duration: 0.4,
          overwrite: "auto",
        });

        gsap.to(".hero-left-text", {
          y: scrollY * 0.06,
          opacity: Math.max(0, 1 - scrollY * 0.0025),
          duration: 0.4,
          overwrite: "auto",
        });

        gsap.to(".hero-col-right", {
          y: scrollY * 0.06,
          opacity: Math.max(0, 1 - scrollY * 0.0025),
          duration: 0.4,
          overwrite: "auto",
        });
      }
    });
  }
});
