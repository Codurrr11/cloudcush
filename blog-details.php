<?php
// Establish database connection and load helper
require_once __DIR__ . '/admin/config/database.php';
require_once __DIR__ . '/admin/config/blogs-helper.php';

$idOrSlug = trim($_GET['slug'] ?? $_GET['id'] ?? '');

if (empty($idOrSlug)) {
    header('Location: blog.php');
    exit;
}

$blog = getBlogByIdOrSlug($idOrSlug);

// If blog not found or in draft state (and not in preview mode), redirect
if (!$blog || ($blog['status'] !== 'active' && !isset($_GET['preview']))) {
    header('Location: blog.php');
    exit;
}

// Fetch related blogs in same category (prioritizing category, falling back if needed)
$db = getDBConnection();
$stmt = $db->prepare("
    SELECT b.*, u.name AS author_name, u.role AS author_role
    FROM blogs b
    LEFT JOIN users u ON b.author_id = u.id
    WHERE b.status = 'active' AND b.id != :id
    ORDER BY (b.category = :category) DESC, b.created_at DESC
    LIMIT 2
");
$stmt->execute(['id' => $blog['id'], 'category' => $blog['category']]);
$relatedBlogs = $stmt->fetchAll();

include 'includes/head.php';
?>

<?php include 'includes/header.php'; ?>

<main class="blog-details-page">

  <!-- 1. CINEMATIC ARTICLE HERO -->
  <section class="details-hero">
    <div class="details-hero-bg" <?php if ($blog['thumbnail']): ?>style="background-image: url('<?= htmlspecialchars(resolveAssetUrl($blog['thumbnail'])) ?>');"<?php endif; ?>></div>
    <div class="details-hero-overlay"></div>
    <div class="details-hero-content container">
      <div class="details-hero-meta">
        <a href="blog.php?category=<?= urlencode($blog['category']) ?>" class="details-category-badge"><?= htmlspecialchars($blog['category']) ?></a>
        <span class="details-meta-dot">•</span>
        <span class="details-read-time"><?= (int)$blog['read_time'] ?> Min Read</span>
      </div>
      <h1 class="details-hero-title"><?= htmlspecialchars($blog['title']) ?></h1>
      <?php if ($blog['short_description']): ?>
        <p class="details-hero-subtitle">
          <?= htmlspecialchars($blog['short_description']) ?>
        </p>
      <?php endif; ?>
    </div>
  </section>

  <!-- 2. BREADCRUMB SECTION -->
  <div class="about-breadcrumb-bar">
    <div class="container">
      <nav class="about-breadcrumb" aria-label="Breadcrumb">
        <a href="./">Home</a>
        <span class="breadcrumb-separator">/</span>
        <a href="blog.php">The Journal</a>
        <span class="breadcrumb-separator">/</span>
        <span class="active-crumb"><?= htmlspecialchars($blog['title']) ?></span>
      </nav>
    </div>
  </div>

  <!-- 3. ARTICLE CONTENT CONTAINER -->
  <section class="details-article-section">
    <div class="container details-article-grid">

      <!-- Left Column: Sticky Author & Meta Info -->
      <aside class="details-meta-sidebar">
        <div class="sticky-meta-wrap">

          <div class="author-profile">
            <div class="author-avatar-container">
              <span class="d-flex align-items-center justify-content-center bg-secondary text-white fw-bold rounded-circle mx-auto" style="width: 80px; height: 80px; font-size: 2.2rem; font-family: var(--font-heading);">
                <?= strtoupper(substr($blog['author_name'] ?? 'C', 0, 1)) ?>
              </span>
            </div>
            <h4 class="author-sidebar-name"><?= htmlspecialchars($blog['author_name'] ?? 'CloudCush Editorial') ?></h4>
            <p class="author-sidebar-role"><?= htmlspecialchars($blog['author_role'] ?? 'Contributor') ?></p>
            <p class="author-sidebar-bio">Neonatal skin safety advocate and regular contributor to the CloudCush Journal.</p>
          </div>

          <div class="article-info-list">
            <div class="info-item">
              <span class="info-label">Published</span>
              <span class="info-val"><?= date('M j, Y', strtotime($blog['created_at'])) ?></span>
            </div>
            <div class="info-item">
              <span class="info-label">Reading Time</span>
              <span class="info-val"><?= (int)$blog['read_time'] ?> Minutes</span>
            </div>
          </div>

          <div class="article-share-block">
            <span class="share-title">Share Article</span>
            <div class="share-links">
              <a href="javascript:void(0);" class="share-btn" aria-label="Share on Instagram"><i class="ri-instagram-line"></i></a>
              <a href="javascript:void(0);" class="share-btn" aria-label="Share on Facebook"><i class="ri-facebook-line"></i></a>
              <a href="javascript:void(0);" class="share-btn" aria-label="Share on Pinterest"><i class="ri-pinterest-line"></i></a>
              <a href="javascript:void(0);" class="share-btn" aria-label="Copy Link"><i class="ri-link-m"></i></a>
            </div>
          </div>

        </div>
      </aside>

      <!-- Right Column: Immersive Reading Flow -->
      <article class="details-reading-content">

        <!-- Rich text body output -->
        <div class="article-rich-body">
            <?= $blog['content'] ?>
        </div>

      </article>

    </div>
  </section>

  <!-- 4. RELATED ARTICLES (Asymmetric Magazine Layout) -->
  <?php if (!empty($relatedBlogs)): ?>
    <section class="details-related-section">
      <div class="container">

        <div class="related-header">
          <span class="related-subtitle">Continue Reading</span>
          <h3 class="related-title">More from The Journal</h3>
        </div>

        <div class="related-articles-grid">
          <?php 
          $idx = 1;
          foreach ($relatedBlogs as $rb): 
          ?>
            <!-- Card -->
            <article class="related-card offset-card-<?= $idx ?>">
              <a href="blog-details.php?slug=<?= urlencode($rb['slug']) ?>" class="related-card-link">
                <div class="related-img-wrap">
                  <?php if ($rb['thumbnail']): ?>
                    <img class="related-img" src="<?= htmlspecialchars(resolveAssetUrl($rb['thumbnail'])) ?>" alt="<?= htmlspecialchars($rb['title']) ?>" loading="lazy">
                  <?php else: ?>
                    <div class="d-flex align-items-center justify-content-center bg-light w-100 h-100" style="min-height: 250px; color: #94a3b8;">
                      <i class="ri-image-line" style="font-size: 2rem;"></i>
                    </div>
                  <?php endif; ?>
                  <span class="related-tag"><?= htmlspecialchars($rb['category']) ?></span>
                </div>
                <div class="related-content">
                  <h4 class="related-card-title"><?= htmlspecialchars($rb['title']) ?></h4>
                  <p class="related-card-excerpt"><?= htmlspecialchars($rb['short_description'] ?: substr(strip_tags($rb['content']), 0, 100) . '...') ?></p>
                  <span class="related-read-more">Read Article <i class="ri-arrow-right-line"></i></span>
                </div>
              </a>
            </article>
          <?php 
            $idx++;
          endforeach; 
          ?>
        </div>

      </div>
    </section>
  <?php endif; ?>

  <!-- 5. BOTTOM EMOTIONAL CTA -->
  <section class="details-bottom-cta">
    <div class="cta-bg-layer"></div>
    <div class="cta-overlay-layer"></div>
    <div class="cta-content-wrap container">
      <h2 class="cta-title">Comfort You Can Trust.</h2>
      <p class="cta-desc">
        Experience the softest protection, completely free from chlorine and harsh chemical additives.
      </p>
      <div class="cta-actions">
        <a href="products.php" class="btn-pill btn-pill-primary">Shop Diapers</a>
        <a href="about.php" class="btn-pill">Our Philosophy</a>
      </div>
    </div>
  </section>

</main>

<?php include 'includes/footer.php'; ?>
