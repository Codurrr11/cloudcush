<?php

/**
 * CloudCush — diaper-showcase-section.php  v6
 * Full-viewport Three.js canvas. Minimal Apple-style text overlaid L/R.
 */
?>

<section class="cc-showcase" aria-label="CloudCush Diaper Product Features">

  <div class="cc-showcase__track">

    <div class="cc-showcase__sticky">

      <!-- Eyebrow + progress -->
      <header class="cc-showcase__header" aria-hidden="true">
        <span class="cc-showcase__eyebrow">Inside Every CloudCush</span>
        <div class="cc-showcase__progress-bar" role="progressbar">
          <div class="cc-showcase__progress-fill"></div>
        </div>
      </header>

      <!-- Three.js canvas — full viewport, centered diaper render -->
      <div class="cc-showcase__canvas-wrap">
        <div class="cc-showcase__canvas-glow" aria-hidden="true"></div>
        <canvas
          class="cc-showcase__canvas"
          role="img"
          aria-label="Rotating 3D CloudCush premium diaper"></canvas>
        <!-- Loading ring -->
        <div class="cc-showcase__loader" role="status" aria-live="polite">
          <div class="cc-showcase__loader-ring"></div>
        </div>
      </div>

      <!-- Text overlay — minimal Apple-style floating copy, left or right -->
      <div class="cc-showcase__text-overlay" aria-live="polite">
        <div class="cc-showcase__stages"></div>
      </div>

    </div><!-- /.cc-showcase__sticky -->

  </div><!-- /.cc-showcase__track -->

  <div class="cc-showcase__divider" aria-hidden="true"></div>

</section>
