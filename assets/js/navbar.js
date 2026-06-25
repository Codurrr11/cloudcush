// Initialize Navbar and Mobile Menu interactions
window.initNavbar = () => {
  const menuToggle = document.getElementById("mobile-menu-toggle");
  const mobileMenu = document.getElementById("mobile-nav-menu");
  const backdrop = document.getElementById("mobile-nav-backdrop");

  // --- MOBILE MENU OPEN / CLOSE HELPERS ---
  const openMobileMenu = () => {
    menuToggle.classList.add("is-active");
    mobileMenu.classList.add("is-active");
    if (backdrop) backdrop.classList.add("is-active");
    if (window.lenis) window.lenis.stop();
    document.body.style.overflow = "hidden";
  };

  const closeMobileMenu = () => {
    menuToggle.classList.remove("is-active");
    mobileMenu.classList.remove("is-active");
    if (backdrop) backdrop.classList.remove("is-active");
    if (window.lenis) window.lenis.start();
    document.body.style.overflow = "";

    // Collapse all open submenus when panel closes
    mobileMenu
      .querySelectorAll(".mobile-submenu.is-active")
      .forEach((s) => s.classList.remove("is-active"));
    mobileMenu
      .querySelectorAll(".mobile-submenu-toggle.is-active")
      .forEach((t) => t.classList.remove("is-active"));
    mobileMenu
      .querySelectorAll(".mobile-submenu-links.is-active")
      .forEach((l) => l.classList.remove("is-active"));
    mobileMenu
      .querySelectorAll(".mobile-submenu-title.is-active")
      .forEach((t) => t.classList.remove("is-active"));
  };

  if (menuToggle && mobileMenu) {
    // Hamburger toggle
    menuToggle.addEventListener("click", () => {
      if (menuToggle.classList.contains("is-active")) {
        closeMobileMenu();
      } else {
        openMobileMenu();
      }
    });

    // Backdrop click closes panel
    if (backdrop) {
      backdrop.addEventListener("click", closeMobileMenu);
    }

    // Dedicated close button
    const closeBtn = document.getElementById("mobile-menu-close");
    if (closeBtn) {
      closeBtn.addEventListener("click", closeMobileMenu);
    }

    // Dismiss panel on plain nav link clicks (not toggles)
    mobileMenu
      .querySelectorAll(
        ".mobile-nav-link:not(.mobile-submenu-toggle), .mobile-submenu-links a, .mobile-discover-links a, .mobile-submenu-bottom a",
      )
      .forEach((link) => {
        link.addEventListener("click", closeMobileMenu);
      });
  }

  // --- MOBILE TOP-LEVEL SUBMENU ACCORDION (one open at a time) ---
  const submenuToggles = document.querySelectorAll(".mobile-submenu-toggle");
  submenuToggles.forEach((toggle) => {
    const submenu = toggle.nextElementSibling;
    if (!submenu) return;

    toggle.addEventListener("click", (e) => {
      e.preventDefault();
      const isOpen = toggle.classList.contains("is-active");

      // Close all other top-level submenus first
      submenuToggles.forEach((otherToggle) => {
        if (otherToggle === toggle) return;
        otherToggle.classList.remove("is-active");
        const otherSubmenu = otherToggle.nextElementSibling;
        if (otherSubmenu) otherSubmenu.classList.remove("is-active");
      });

      // Toggle current
      toggle.classList.toggle("is-active", !isOpen);
      submenu.classList.toggle("is-active", !isOpen);
    });
  });

  // --- MOBILE INNER ACCORDION (sub-group titles, one open at a time per parent) ---
  const accordionGroupTitles = document.querySelectorAll(".mobile-submenu-title");
  accordionGroupTitles.forEach((title) => {
    title.addEventListener("click", (e) => {
      e.preventDefault();
      const isOpen = title.classList.contains("is-active");
      const parentSubmenu = title.closest(".mobile-submenu");

      // Close all sibling group titles in same submenu
      if (parentSubmenu) {
        parentSubmenu
          .querySelectorAll(".mobile-submenu-title.is-active")
          .forEach((t) => {
            t.classList.remove("is-active");
            const l = t.nextElementSibling;
            if (l) l.classList.remove("is-active");
          });
      }

      // Toggle current if it was closed
      if (!isOpen) {
        title.classList.add("is-active");
        const list = title.nextElementSibling;
        if (list) list.classList.add("is-active");
      }
    });
  });

  // Header Scroll compression triggers
  const header = document.querySelector(".site-header");
  window.updateHeaderState = (scrollY) => {
    if (header) {
      if (scrollY > 50) {
        header.classList.add("is-scrolled");
      } else {
        header.classList.remove("is-scrolled");
      }
    }
  };
};
