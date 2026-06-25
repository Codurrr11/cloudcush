<?php 
// No database connection needed for static policy page
?>
<?php include 'includes/head.php'; ?>
<?php include 'includes/header.php'; ?>

<main class="policy-page">

  <!-- 1. HERO BANNER SECTION -->
  <section class="policy-hero" style="background-color: #f7f9fa; padding: 60px 0; text-align: center;">
    <div class="container">
      <h1 class="policy-hero-title">Shipping & Delivery Policy</h1>
    </div>
  </section>

  <!-- 2. BREADCRUMB SECTION -->
  <div class="breadcrumb-bar" style="padding: 15px 0; border-bottom: 1px solid #eaeaea;">
    <div class="container">
      <nav class="breadcrumb" aria-label="Breadcrumb">
        <a href="./">Home</a>
        <span class="breadcrumb-separator">/</span>
        <span>Shipping & Delivery Policy</span>
      </nav>
    </div>
  </div>

  <!-- 3. POLICY CONTENT SECTION -->
  <section class="policy-content-section" style="padding: 60px 0;">
    <div class="container" style="max-width: 800px; margin: 0 auto; line-height: 1.8;">

      <!-- Shipping Policy -->
      <div class="policy-block" style="margin-bottom: 40px;">
        <h2>Shipping Policy</h2>
        <p>At CloudCush, we strive to ensure that your order reaches you safely and on time.</p>
        
        <ul>
          <li>Orders are processed within 1–2 business days after successful payment confirmation.</li>
          <li>Orders placed on Sundays or public holidays will be processed on the next working day.</li>
          <li>We currently ship across India through trusted courier partners.</li>
          <li>Shipping charges, if applicable, will be displayed during checkout before payment.</li>
          <li>Once your order is dispatched, you will receive a tracking link via email, SMS, or WhatsApp.</li>
        </ul>
      </div>

      <!-- Delivery Policy -->
      <div class="policy-block" style="margin-bottom: 40px;">
        <h2>Delivery Policy</h2>
        
        <ul>
          <li>Estimated delivery time is 3–7 business days for most locations across India.</li>
          <li>Deliveries to remote or rural areas may take additional time.</li>
          <li>Delivery timelines are estimates and may vary due to unforeseen circumstances such as weather conditions, transportation delays, government restrictions, or courier partner issues.</li>
          <li>Customers are requested to provide accurate shipping information to avoid delivery delays.</li>
        </ul>
      </div>

    </div>
  </section>

</main>

<?php include 'includes/footer.php'; ?>