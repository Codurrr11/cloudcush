/**
 * CloudCush — auth.js
 * Implements password visibility toggles and a lightweight, premium
 * 2D ambient floating particle overlay in the background.
 */

(function () {
  "use strict";

  /* ---------------------------------------------------------------
     Password Visibility Toggle
     --------------------------------------------------------------- */
  document.querySelectorAll(".auth-field-toggle").forEach(function (btn) {
    btn.addEventListener("click", function () {
      var wrap = btn.closest(".auth-field-wrap");
      var input = wrap ? wrap.querySelector(".auth-field-input") : null;
      if (!input) return;

      var isPassword = input.type === "password";
      input.type = isPassword ? "text" : "password";

      var icon = btn.querySelector("i");
      if (icon) {
        icon.className = isPassword ? "ri-eye-line" : "ri-eye-off-line";
      }
      btn.setAttribute("aria-label", isPassword ? "Hide password" : "Show password");
    });
  });

  /* ---------------------------------------------------------------
     2D Ambient Floating Particle System
     --------------------------------------------------------------- */
  var canvas = document.getElementById("authStandCanvas");
  if (canvas) {
    initAmbientParticles(canvas);
  }

  function initAmbientParticles(cvs) {
    var ctx = cvs.getContext("2d");
    var particles = [];
    var particleCount = 25;
    var mouse = { x: 0, y: 0, targetX: 0, targetY: 0 };

    // Resize handler
    function resizeCanvas() {
      cvs.width = window.innerWidth;
      cvs.height = window.innerHeight;
    }
    resizeCanvas();
    window.addEventListener("resize", resizeCanvas);

    // Track mouse coordinates
    window.addEventListener("mousemove", function (e) {
      mouse.targetX = e.clientX;
      mouse.targetY = e.clientY;
    });

    // Create a particle template
    function createParticle(initY) {
      return {
        x: Math.random() * cvs.width,
        y: initY ? Math.random() * cvs.height : cvs.height + 20,
        size: 3 + Math.random() * 8,
        speedY: 0.2 + Math.random() * 0.4,
        speedX: (Math.random() - 0.5) * 0.3,
        opacity: 0.15 + Math.random() * 0.3,
        wobbleSpeed: 0.005 + Math.random() * 0.01,
        wobbleVal: Math.random() * 100
      };
    }

    // Populate particles
    for (var i = 0; i < particleCount; i++) {
      particles.push(createParticle(true));
    }

    // Main animation loop
    function animate() {
      requestAnimationFrame(animate);

      // Clear canvas
      ctx.clearRect(0, 0, cvs.width, cvs.height);

      // Lerp mouse positions
      mouse.x += (mouse.targetX - mouse.x) * 0.05;
      mouse.y += (mouse.targetY - mouse.y) * 0.05;

      // Update and draw particles
      for (var j = 0; j < particles.length; j++) {
        var p = particles[j];

        // Physics
        p.y -= p.speedY;
        p.wobbleVal += p.wobbleSpeed;
        p.x += p.speedX + Math.sin(p.wobbleVal) * 0.15;

        // Subtle mouse influence
        var dx = mouse.x - p.x;
        var dy = mouse.y - p.y;
        var dist = Math.sqrt(dx * dx + dy * dy);
        if (dist < 200) {
          var force = (200 - dist) / 200;
          p.x -= (dx / dist) * force * 0.5;
          p.y -= (dy / dist) * force * 0.5;
        }

        // Draw soft glowing orb
        ctx.beginPath();
        var gradient = ctx.createRadialGradient(p.x, p.y, 0, p.x, p.y, p.size * 2);
        gradient.addColorStop(0, "rgba(255, 255, 255, " + p.opacity + ")");
        gradient.addColorStop(0.5, "rgba(224, 238, 255, " + p.opacity * 0.5 + ")");
        gradient.addColorStop(1, "rgba(224, 238, 255, 0)");
        ctx.fillStyle = gradient;
        ctx.arc(p.x, p.y, p.size * 2, 0, Math.PI * 2);
        ctx.fill();

        // Recycle particle when it goes offscreen
        if (p.y < -20 || p.x < -20 || p.x > cvs.width + 20) {
          particles[j] = createParticle(false);
        }
      }
    }

    animate();
  }

  /* ---------------------------------------------------------------
     Account Dashboard — Smooth tab switching & GSAP reveal
     --------------------------------------------------------------- */
  var accountContainer = document.querySelector(".account-container");
  if (accountContainer) {
    initAccountDashboard();
  }

  function initAccountDashboard() {
    var navBtns = document.querySelectorAll(".account-nav-btn[data-target]");
    var panels = document.querySelectorAll(".account-panel");

    // Page Entrance Animations
    if (typeof gsap !== "undefined") {
      gsap.fromTo(".account-sidebar", 
        { x: -35, opacity: 0 },
        { x: 0, opacity: 1, duration: 0.8, ease: "power3.out" }
      );
      gsap.fromTo(".account-main-content", 
        { y: 35, opacity: 0 },
        { y: 0, opacity: 1, duration: 0.8, ease: "power3.out", delay: 0.1 }
      );
      if (document.querySelector(".account-summary-card")) {
        gsap.fromTo(".account-summary-card",
          { scale: 0.95, opacity: 0, y: 15 },
          { scale: 1, opacity: 1, y: 0, duration: 0.6, stagger: 0.08, ease: "back.out(1.15)", delay: 0.3 }
        );
      }
    }

    // Click handler for tab switching
    navBtns.forEach(function (btn) {
      btn.addEventListener("click", function () {
        var targetId = btn.getAttribute("data-target");
        var activePanel = document.querySelector(".account-panel.active");
        var targetPanel = document.getElementById(targetId);

        if (!targetPanel || btn.classList.contains("active")) return;

        // Toggle active button
        navBtns.forEach(function (b) { b.classList.remove("active"); });
        btn.classList.add("active");

        // Tab Switching Animation (GSAP)
        if (typeof gsap !== "undefined" && activePanel) {
          gsap.to(activePanel, {
            opacity: 0,
            y: -10,
            duration: 0.3,
            ease: "power2.in",
            onComplete: function () {
              activePanel.classList.remove("active");
              targetPanel.classList.add("active");
              gsap.fromTo(targetPanel,
                { opacity: 0, y: 10 },
                { opacity: 1, y: 0, duration: 0.5, ease: "power3.out" }
              );
            }
          });
        } else {
          // Fallback if GSAP is not loaded
          panels.forEach(function (p) { p.classList.remove("active"); });
          targetPanel.classList.add("active");
        }
      });
    });
  }
})();
