<?php
if (!function_exists('getFooterData')) {
  require_once __DIR__ . '/../admin/config/footer-helper.php';
}
$footerData    = getFooterData();
$footerLogo    = !empty($footerData['logo_img'])  ? resolveAssetUrl($footerData['logo_img'])  : 'assets/images/logo.png';
$footerBg      = !empty($footerData['bg_image'])  ? resolveAssetUrl($footerData['bg_image'])  : 'assets/images/footer-bg.png';
$storyText     = !empty($footerData['story_text']) ? $footerData['story_text'] : "CloudCush is a luxury baby-care brand redefining newborn routine comfort. We believe in pure, organic, and dermatologically certified materials designed to protect your baby's delicate skin barrier from day one.";
$typing1       = !empty($footerData['typing_text_1']) ? $footerData['typing_text_1'] : 'Cloudcush';
$typing2       = !empty($footerData['typing_text_2']) ? $footerData['typing_text_2'] : 'comfort designed for tiny humans.';
$footerCols    = $footerData['columns'] ?? [];
$copyrightText = !empty($footerData['copyright_text']) ? $footerData['copyright_text'] : '© 2026, CloudCush. Crafted for softer beginnings.';
$socialLinks   = $footerData['social_links'] ?? [];
$legalLinks    = $footerData['legal_links']  ?? [];

// Social URL helpers
$igUrl  = !empty($socialLinks['instagram']) ? $socialLinks['instagram'] : 'javascript:void(0);';
$ytUrl  = !empty($socialLinks['youtube'])   ? $socialLinks['youtube']   : 'javascript:void(0);';
$fbUrl  = !empty($socialLinks['facebook'])  ? $socialLinks['facebook']  : 'javascript:void(0);';
$twUrl  = !empty($socialLinks['twitter'])   ? $socialLinks['twitter']   : '';
?>
<footer class="site-footer">

  <!-- Decorative background image: product showcase layer -->
  <img
    src="<?= htmlspecialchars($footerBg) ?>"
    alt=""
    class="footer-bg-image"
    aria-hidden="true"
    draggable="false"
    loading="lazy"
    decoding="async">
  <!-- Gradient veil: sits above image, preserves text readability -->
  <div class="footer-bg-veil" aria-hidden="true"></div>

  <div class="container">

    <!-- 1. HERO FOOTER TOP (Giant Editorial Branding) -->
    <div class="footer-hero-top">
      <div class="footer-huge-brand-wrap">
        <div class="footer-logo-brand-container">
          <h2 class="footer-huge-brand"><span class="typing-text"></span><span class="typing-cursor">|</span></h2>
        </div>
      </div>
      <div class="footer-slogan-wrap">
        <p class="footer-slogan"><?= htmlspecialchars($typing2) ?></p>
        <span class="footer-slogan-sub">Crafted for softer beginnings.</span>
      </div>
    </div>

    <!-- 2 & 3. CREATIVE LINK LAYOUT & BRAND STORY BLOCK -->
    <div class="footer-main-grid">

      <!-- Column 1: Brand Story Block -->
      <div class="footer-story-block">
        <img src="<?= htmlspecialchars($footerLogo) ?>" alt="CloudCush Logo" class="footer-big-logo">
        <p class="footer-story-text">
          <?= htmlspecialchars($storyText) ?>
        </p>
        <span class="footer-story-location">Rajasthan, India</span>
      </div>

      <!-- Columns 2, 3, 4: Dynamic Link Columns -->
      <?php foreach ($footerCols as $col): ?>
        <div class="footer-nav-block">
          <h3 class="footer-nav-title"><?= htmlspecialchars($col['title'] ?? '') ?></h3>
          <ul class="footer-nav-list">
            <?php foreach (($col['links'] ?? []) as $link): ?>
              <li><a href="<?= htmlspecialchars($link['url'] ?? '#') ?>" class="footer-nav-link-item"><?= htmlspecialchars($link['title'] ?? '') ?></a></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endforeach; ?>

      <!-- Column 4: Social Links -->
      <div class="footer-nav-block footer-social-column">
        <div class="footer-social-wrap" style="margin-top: 2rem;">
          <a href="<?= htmlspecialchars($igUrl) ?>" class="footer-social-icon" aria-label="Instagram"><i class="ri-instagram-line"></i></a>
          <a href="<?= htmlspecialchars($ytUrl) ?>" class="footer-social-icon" aria-label="YouTube"><i class="ri-youtube-line"></i></a>
          <a href="<?= htmlspecialchars($fbUrl) ?>" class="footer-social-icon" aria-label="Facebook"><i class="ri-facebook-line"></i></a>
          <?php if ($twUrl): ?>
            <a href="<?= htmlspecialchars($twUrl) ?>" class="footer-social-icon" aria-label="Twitter / X"><i class="ri-twitter-x-line"></i></a>
          <?php endif; ?>
        </div>
      </div>

    </div>

    <!-- 5. BOTTOM BAR -->
    <div class="footer-bottom-bar">
      <div class="footer-bottom-info">
        <span class="footer-copyright"><?= htmlspecialchars($copyrightText) ?></span>
        <div class="footer-legal-links-wrap">
          <?php if (!empty($legalLinks)): ?>
            <?php foreach ($legalLinks as $ll): ?>
              <a href="<?= htmlspecialchars($ll['url'] ?? 'javascript:void(0);') ?>" class="footer-policy-link"><?= htmlspecialchars($ll['title'] ?? '') ?></a>
            <?php endforeach; ?>
          <?php else: ?>
            <a href="javascript:void(0);" class="footer-policy-link">Shipping &amp; Delivery</a>
            <a href="javascript:void(0);" class="footer-policy-link">Return &amp; Refund</a>
            <a href="javascript:void(0);" class="footer-policy-link">Warranty</a>
            <a href="javascript:void(0);" class="footer-policy-link">Terms &amp; Conditions</a>
            <a href="javascript:void(0);" class="footer-policy-link">Privacy Policy</a>
          <?php endif; ?>
        </div>
      </div>

      <!-- Payment icon badges -->
      <div class="footer-payment-methods">
        <img class="footer-payment-icon" src="https://cdn-icons-png.flaticon.com/512/349/349221.png" alt="Visa">
        <img class="footer-payment-icon" src="https://cdn-icons-png.flaticon.com/512/349/349228.png" alt="Mastercard">
        <img class="footer-payment-icon" src="https://cdn-icons-png.flaticon.com/512/174/174861.png" alt="Paypal">
        <img class="footer-payment-icon" src="https://cdn-icons-png.flaticon.com/512/349/349230.png" alt="Amex">
        <img class="footer-payment-icon" src="https://cdn-icons-png.flaticon.com/512/196/196565.png" alt="Apple Pay">
        <img class="footer-payment-icon" src="https://cdn-icons-png.flaticon.com/512/6124/6124998.png" alt="Google Pay">
      </div>
    </div>

  </div>
</footer>

<!-- Bottom Right Floating customer chat bubble -->
<a href="javascript:void(0);" class="floating-widget" aria-label="Customer Support">
  <i class="ri-chat-3-line"></i>
</a>

<!-- GSAP Animation Library CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>

<!-- GSAP ScrollTrigger Plugin CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>

<!-- Lenis Kinetic Smooth Scroll Library CDN -->
<script src="https://unpkg.com/@studio-freight/lenis@1.0.34/dist/lenis.min.js"></script>

<!-- SweetAlert2 JS for premium notifications -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Dynamic Alternating Typing Texts Injection -->
<script>
  window.footerTypingTexts = <?= json_encode([$typing1, $typing2]) ?>;
</script>

<!-- Consolidated Javascript Scripts -->
<script src="assets/js/navbar.js"></script>
<script src="assets/js/smooth-scroll.js"></script>
<script src="assets/js/main.js"></script>
<script src="assets/js/about.js"></script>
<script src="assets/js/animations.js"></script>
<script src="assets/js/blog-carousel.js"></script>
<script src="assets/js/components.js"></script>
<script src="assets/js/diaper.js"></script>
<script src="assets/js/product-details.js"></script>

<?php if (!empty($use_auth_styles)): ?>
  <script src="assets/js/auth.js"></script>
<?php endif; ?>

</body>

</html>
