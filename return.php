<?php 
// No database connection needed for static policy page
?>
<?php include 'includes/head.php'; ?>
<?php include 'includes/header.php'; ?>

<main class="policy-page">

  <!-- 1. HERO BANNER SECTION -->
  <section class="policy-hero" style="background-color: #f7f9fa; padding: 60px 0; text-align: center;">
    <div class="container">
      <h1 class="policy-hero-title">Return & Refund Policy</h1>
    </div>
  </section>

  <!-- 2. BREADCRUMB SECTION -->
  <div class="breadcrumb-bar" style="padding: 15px 0; border-bottom: 1px solid #eaeaea;">
    <div class="container">
      <nav class="breadcrumb" aria-label="Breadcrumb">
        <a href="./">Home</a>
        <span class="breadcrumb-separator">/</span>
        <span>Return & Refund Policy</span>
      </nav>
    </div>
  </div>

  <!-- 3. POLICY CONTENT SECTION -->
  <section class="policy-content-section" style="padding: 60px 0;">
    <div class="container" style="max-width: 800px; margin: 0 auto; line-height: 1.8;">

      <!-- Return Policy -->
      <div class="policy-block" style="margin-bottom: 40px;">
        <h2>Return Policy</h2>
        <p>Due to hygiene and safety reasons, baby diapers and other personal care products cannot be returned once delivered and opened.</p>
        
        <p><strong>Returns will only be accepted in the following cases:</strong></p>
        <ul>
          <li>Product received is damaged during transit.</li>
          <li>Wrong product was delivered.</li>
          <li>Product received is defective or unusable.</li>
        </ul>

        <p><strong>To initiate a return request:</strong></p>
        <ol>
          <li>Contact our customer support within 48 hours of delivery.</li>
          <li>Share your order number along with clear photographs of the product and packaging.</li>
          <li>Our team will review the request and provide further instructions.</li>
        </ol>
        
        <p><em>CloudCush reserves the right to reject return requests that do not meet the above conditions.</em></p>
      </div>

      <!-- Refund Policy -->
      <div class="policy-block" style="margin-bottom: 40px;">
        <h2>Refund Policy</h2>
        <p>Refunds will be processed only after the returned product has been received and inspected by our team.</p>
        
        <p><strong>Approved refunds will be issued:</strong></p>
        <ul>
          <li>To the original payment method used during purchase.</li>
          <li>Within 5–7 business days from approval of the refund request.</li>
        </ul>
        
        <p>In case of Cash on Delivery (COD) orders, customers may be asked to provide bank account details for processing the refund.</p>
        <p>Shipping charges, if any, are non-refundable unless the return is due to an error on our part.</p>
      </div>

      <!-- Cancellation Policy -->
      <div class="policy-block" style="margin-bottom: 40px;">
        <h2>Cancellation Policy</h2>
        <ul>
          <li>Orders may be cancelled before dispatch.</li>
          <li>Once an order has been shipped, cancellation requests cannot be accepted.</li>
          <li>If a prepaid order is cancelled before dispatch, the refund will be processed within 5–7 business days.</li>
        </ul>
      </div>

    </div>
  </section>

</main>

<?php include 'includes/footer.php'; ?>