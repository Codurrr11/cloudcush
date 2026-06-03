// About Page Animations and Interactions
document.addEventListener('DOMContentLoaded', () => {
  // Ensure GSAP and ScrollTrigger are loaded before initializing
  if (typeof gsap === 'undefined' || typeof ScrollTrigger === 'undefined') {
    console.warn('GSAP or ScrollTrigger is not loaded.');
    return;
  }

  // Register ScrollTrigger plugin
  gsap.registerPlugin(ScrollTrigger);

  // 1. HERO INTRO REVEAL ANIMATIONS
  const heroTl = gsap.timeline();
  
  // Scale down the hero background slightly for an opening cinematic feel
  heroTl.fromTo('.about-hero-bg', 
    { scale: 1.18 },
    { scale: 1.05, duration: 2.0, ease: 'power2.out' }
  );

  // Stagger reveal of hero text/elements
  heroTl.fromTo('.about-hero-content > *',
    { opacity: 0, y: 30 },
    { opacity: 1, y: 0, duration: 0.9, stagger: 0.18, ease: 'power3.out' },
    '-=1.5' // overlap with background scaling
  );

  // Fade in scroll indicator
  heroTl.fromTo('.about-hero-scroll',
    { opacity: 0 },
    { opacity: 0.8, duration: 0.6, ease: 'power1.out' },
    '-=0.4'
  );

  // Smooth scroll to next section on CTA scroll click
  const ctaPhilosophy = document.querySelector('.cta-scroll-philosophy');
  if (ctaPhilosophy) {
    ctaPhilosophy.addEventListener('click', (e) => {
      e.preventDefault();
      const targetSec = document.querySelector('.horizontal-story-section');
      if (targetSec && window.lenis) {
        window.lenis.scrollTo(targetSec, { duration: 1.2 });
      } else if (targetSec) {
        targetSec.scrollIntoView({ behavior: 'smooth' });
      }
    });
  }

  // 2. PINNED HORIZONTAL STORYTELLING STRIP (Desktop Only)
  const storySection = document.querySelector('.horizontal-story-section');
  const storyTrack = document.querySelector('.horizontal-story-track');
  const storyContainer = document.querySelector('.horizontal-story-container');
  
  if (storySection && storyTrack && storyContainer) {
    // Media Query match for desktop sizes (above 1024px)
    ScrollTrigger.matchMedia({
      "(min-width: 1025px)": function() {
        const getScrollAmount = () => {
          let trackWidth = storyTrack.scrollWidth;
          return -(trackWidth - window.innerWidth + window.innerWidth * 0.15); // account for container padding
        };

        gsap.to(storyContainer, {
          x: getScrollAmount,
          ease: 'none',
          scrollTrigger: {
            trigger: storySection,
            pin: true,
            scrub: 1,
            start: 'top top',
            end: () => '+=' + Math.abs(getScrollAmount()),
            invalidateOnRefresh: true
          }
        });

        // Stagger story cards scaling/movement slightly as they scroll horizontally
        const cards = storyTrack.querySelectorAll('.story-card');
        cards.forEach((card, index) => {
          gsap.fromTo(card,
            { scale: 0.95, opacity: 0.85 },
            {
              scale: 1,
              opacity: 1,
              ease: 'power1.out',
              scrollTrigger: {
                trigger: card,
                containerAnimation: gsap.getTweensOf(storyContainer)[0], // bind to horizontal animation
                start: 'left center+=200',
                end: 'right center',
                scrub: true
              }
            }
          );
        });
      }
    });
  }

  // 3. WHY CHOOSE CARDS STAGGER
  gsap.fromTo('.why-card',
    { opacity: 0, y: 40 },
    {
      opacity: 1,
      y: 0,
      duration: 0.8,
      stagger: 0.08,
      ease: 'power3.out',
      scrollTrigger: {
        trigger: '.why-choose-grid',
        start: 'top 80%',
        once: true
      }
    }
  );

  // 4. LAYERED PHILOSOPHY SECTION PARALLAX
  gsap.to('.philosophy-right-visual img', {
    yPercent: -15,
    ease: 'none',
    scrollTrigger: {
      trigger: '.philosophy-layered-section',
      start: 'top bottom',
      end: 'bottom top',
      scrub: true
    }
  });

  gsap.fromTo('.philosophy-left > *',
    { opacity: 0, y: 30 },
    {
      opacity: 1,
      y: 0,
      duration: 1.0,
      stagger: 0.2,
      ease: 'power3.out',
      scrollTrigger: {
        trigger: '.philosophy-layered-section',
        start: 'top 65%',
        once: true
      }
    }
  );

  // 5. FAQ STAGGER REVEAL (cosmetic only — GSAP handles entrance animation)
  gsap.fromTo('.faq-item',
    { opacity: 0, y: 20 },
    {
      opacity: 1,
      y: 0,
      duration: 0.6,
      stagger: 0.08,
      ease: 'power2.out',
      scrollTrigger: {
        trigger: '.faq-accordion-group',
        start: 'top 85%',
        once: true
      }
    }
  );

  // 6. FINAL CTA PARALLAX
  gsap.to('.about-cta-bg', {
    yPercent: 12,
    ease: 'none',
    scrollTrigger: {
      trigger: '.about-cta-section',
      start: 'top bottom',
      end: 'bottom top',
      scrub: true
    }
  });

  gsap.fromTo('.about-cta-content > *',
    { opacity: 0, y: 24 },
    {
      opacity: 1,
      y: 0,
      duration: 0.8,
      stagger: 0.15,
      ease: 'power2.out',
      scrollTrigger: {
        trigger: '.about-cta-section',
        start: 'top 75%',
        once: true
      }
    }
  );
});

