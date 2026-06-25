<?php
// Establish database connection and load helper
require_once __DIR__ . '/admin/config/database.php';
require_once __DIR__ . '/admin/config/blogs-helper.php';

$search   = trim($_GET['search']   ?? '');
$category = trim($_GET['category'] ?? '');
$curPage  = max(1, (int)($_GET['page'] ?? 1));
$perPage  = 7; // Max items per page in grid

// We only want published articles for the frontend
$result = getBlogs([
    'search'   => $search,
    'category' => $category,
    'status'   => 'active',
    'page'     => $curPage,
    'per_page' => $perPage
]);

$blogs      = $result['data'];
$totalPages = $result['total_pages'];
$totalCount = $result['total'];

// Extract the first article as featured if we are on the first page and not filtering
$featuredBlog = null;
$gridBlogs    = $blogs;

if ($curPage === 1 && empty($search) && empty($category) && !empty($blogs)) {
    $featuredBlog = array_shift($gridBlogs);
}

// Fetch all available categories for the filtering tabs
$allCategories = getBlogCategories();

include 'includes/head.php';
?>

<?php include 'includes/header.php'; ?>

<main class="blog-page">

  <!-- 1. CINEMATIC EDITORIAL HERO -->
  <section class="blog-hero">
    <div class="blog-hero-bg"></div>
    <div class="blog-hero-overlay"></div>
    <div class="blog-hero-content container">
      <span class="blog-hero-label">The CloudCush Journal</span>
      <h1 class="blog-hero-title">Nurturing the First Chapter.</h1>
      <p class="blog-hero-subtext">
        Explore a curated collection of parenting philosophies, pediatric skin health science, and cozy newborn routines designed for modern families.
      </p>
    </div>
  </section>

  <!-- 2. BREADCRUMB SECTION -->
  <div class="about-breadcrumb-bar">
    <div class="container">
      <nav class="about-breadcrumb" aria-label="Breadcrumb">
        <a href="./">Home</a>
        <span class="breadcrumb-separator">/</span>
        <span>The Journal</span>
      </nav>
    </div>
  </div>

  <!-- Search & Category Filters -->
  <section class="blog-filters-section" style="margin-top: 3.5rem;">
    <div class="container">
      <!-- Search Form -->
      <form action="" method="GET" class="blog-search-bar">
        <i class="ri-search-line blog-search-icon"></i>
        <input type="text" name="search" class="blog-search-input" 
               placeholder="Search articles, topics, keywords..." 
               value="<?= htmlspecialchars($search) ?>">
        <?php if ($category): ?>
          <input type="hidden" name="category" value="<?= htmlspecialchars($category) ?>">
        <?php endif; ?>
      </form>

      <!-- Category Tabs -->
      <div class="blog-categories-tabs">
        <a href="blog.php<?= $search ? '?search=' . urlencode($search) : '' ?>" 
           class="blog-category-btn <?= empty($category) ? 'active' : '' ?>">
           All Stories
        </a>
        <?php foreach ($allCategories as $cat): ?>
          <a href="blog.php?category=<?= urlencode($cat) ?><?= $search ? '&search=' . urlencode($search) : '' ?>" 
             class="blog-category-btn <?= $category === $cat ? 'active' : '' ?>">
             <?= htmlspecialchars($cat) ?>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- 3. FEATURED STORY SECTION -->
  <?php if ($featuredBlog): ?>
    <section class="blog-featured-section">
      <div class="container">
        <div class="blog-featured-layout">

          <!-- Large Editorial Image Wrap -->
          <div class="blog-featured-img-wrap">
            <?php if ($featuredBlog['thumbnail']): ?>
              <img class="blog-featured-img" src="<?= htmlspecialchars(resolveAssetUrl($featuredBlog['thumbnail'])) ?>" alt="<?= htmlspecialchars($featuredBlog['title']) ?>" loading="lazy">
            <?php else: ?>
              <div class="d-flex align-items-center justify-content-center bg-light w-100 h-100" style="min-height: 400px; color: #94a3b8;">
                <i class="ri-image-line" style="font-size: 3rem;"></i>
              </div>
            <?php endif; ?>
            <div class="blog-featured-img-overlay"></div>
            <span class="blog-featured-badge">Featured Article</span>
          </div>

          <!-- Overlapping Content Block -->
          <div class="blog-featured-content">
            <div class="blog-featured-meta">
              <span class="blog-post-tag"><?= htmlspecialchars($featuredBlog['category']) ?></span>
              <span class="blog-post-dot">•</span>
              <span class="blog-post-read"><?= (int)$featuredBlog['read_time'] ?> Min Read</span>
            </div>
            <h2 class="blog-featured-title">
              <a href="blog-details.php?slug=<?= urlencode($featuredBlog['slug']) ?>"><?= htmlspecialchars($featuredBlog['title']) ?></a>
            </h2>
            <p class="blog-featured-desc">
              <?= htmlspecialchars($featuredBlog['short_description'] ?: substr(strip_tags($featuredBlog['content']), 0, 180) . '...') ?>
            </p>
            <div class="blog-featured-author">
              <div class="author-avatar-wrap">
                <span class="d-flex align-items-center justify-content-center bg-secondary text-white fw-bold rounded-circle" style="width: 40px; height: 40px; font-size: 1.1rem; font-family: var(--font-heading);">
                  <?= strtoupper(substr($featuredBlog['author_name'] ?? 'C', 0, 1)) ?>
                </span>
              </div>
              <div class="author-info">
                <span class="author-name"><?= htmlspecialchars($featuredBlog['author_name'] ?? 'CloudCush Editorial') ?></span>
                <span class="author-role"><?= htmlspecialchars($featuredBlog['author_role'] ?? 'Contributor') ?></span>
              </div>
            </div>
            <a href="blog-details.php?slug=<?= urlencode($featuredBlog['slug']) ?>" class="btn-text-arrow">
              Read Editorial <i class="ri-arrow-right-line"></i>
            </a>
          </div>

        </div>
      </div>
    </section>
  <?php endif; ?>

  <!-- 4. CREATIVE EDITORIAL GRID -->
  <section class="blog-grid-section">
    <div class="container">

      <div class="blog-grid-header">
        <span class="blog-grid-subtitle">Curated Writings</span>
        <h2 class="blog-grid-title">Insights for Modern Parenting</h2>
      </div>

      <?php if (empty($gridBlogs) && !$featuredBlog): ?>
        <!-- Empty State -->
        <div class="blog-empty-state">
          <i class="ri-book-open-line blog-empty-icon"></i>
          <h3 class="blog-empty-title">No Articles Found</h3>
          <p class="blog-empty-desc">
            We couldn't find any articles matching your search criteria. Try selecting another category or clear filters.
          </p>
          <a href="blog.php" class="blog-category-btn active" style="margin-top: 15px; display: inline-block;">Clear Filters</a>
        </div>
      <?php else: ?>
        <div class="blog-creative-grid">
          <?php 
          $itemIndex = 0;
          foreach ($gridBlogs as $b): 
              // Inject the stylish typographic quote at the 2nd slot (index 1) to match original layout
              if ($itemIndex === 1):
          ?>
            <article class="blog-card card-typographic">
              <div class="blog-card-content">
                <div class="quote-icon"><i class="ri-double-quotes-l"></i></div>
                <p class="quote-text">
                  "Parenthood is not about perfection. It is about presence, softness, and creating safe, rash-free spaces for baby's natural milestones."
                </p>
                <div class="quote-author-wrap">
                  <span class="quote-author-name">Anjali Verma</span>
                  <span class="quote-author-role">Childhood Development Consultant</span>
                </div>
                <span class="quote-card-tag">Philosophy</span>
              </div>
            </article>
          <?php 
              endif; 
          ?>

            <!-- Blog Card -->
            <article class="blog-card">
              <a href="blog-details.php?slug=<?= urlencode($b['slug']) ?>" class="blog-card-link-wrapper">
                <div class="blog-card-img-wrap">
                  <?php if ($b['thumbnail']): ?>
                    <img class="blog-card-img" src="<?= htmlspecialchars(resolveAssetUrl($b['thumbnail'])) ?>" alt="<?= htmlspecialchars($b['title']) ?>" loading="lazy">
                  <?php else: ?>
                    <div class="d-flex align-items-center justify-content-center bg-light w-100 h-100" style="min-height: 240px; color: #cbd5e1;">
                      <i class="ri-image-line" style="font-size: 2.5rem;"></i>
                    </div>
                  <?php endif; ?>
                  <div class="blog-card-overlay">
                    <span class="blog-card-btn-pill">Read Story</span>
                  </div>
                </div>
                <div class="blog-card-content">
                  <div class="blog-card-meta">
                    <span class="blog-card-tag"><?= htmlspecialchars($b['category']) ?></span>
                    <span class="blog-card-dot">•</span>
                    <span class="blog-card-read"><?= (int)$b['read_time'] ?> Min Read</span>
                  </div>
                  <h3 class="blog-card-title"><?= htmlspecialchars($b['title']) ?></h3>
                  <p class="blog-card-desc">
                    <?= htmlspecialchars($b['short_description'] ?: substr(strip_tags($b['content']), 0, 120) . '...') ?>
                  </p>
                </div>
              </a>
            </article>

          <?php 
              $itemIndex++;
          endforeach;

          // If the grid had only 1 item, append the typographic quote card at the end so it's always visible
          if ($itemIndex === 1):
          ?>
            <article class="blog-card card-typographic">
              <div class="blog-card-content">
                <div class="quote-icon"><i class="ri-double-quotes-l"></i></div>
                <p class="quote-text">
                  "Parenthood is not about perfection. It is about presence, softness, and creating safe, rash-free spaces for baby's natural milestones."
                </p>
                <div class="quote-author-wrap">
                  <span class="quote-author-name">Anjali Verma</span>
                  <span class="quote-author-role">Childhood Development Consultant</span>
                </div>
                <span class="quote-card-tag">Philosophy</span>
              </div>
            </article>
          <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
          <?php
          $baseUrl = "blog.php?search=" . urlencode($search) . "&category=" . urlencode($category);
          ?>
          <div class="pagination">
            <a href="<?= $baseUrl ?>&page=<?= max(1, $curPage - 1) ?>" 
               class="pagination-btn <?= $curPage <= 1 ? 'disabled' : '' ?>" 
               aria-label="Previous Page" style="text-decoration: none; display: inline-flex; align-items: center; justify-content: center;">
              <i class="ri-arrow-left-s-line"></i>
            </a>

            <?php for ($pNum = 1; $pNum <= $totalPages; $pNum++): ?>
              <a href="<?= $baseUrl ?>&page=<?= $pNum ?>" 
                 class="pagination-btn <?= $pNum === $curPage ? 'active' : '' ?>" 
                 style="text-decoration: none; display: inline-flex; align-items: center; justify-content: center;">
                <?= $pNum ?>
              </a>
            <?php endfor; ?>

            <a href="<?= $baseUrl ?>&page=<?= min($totalPages, $curPage + 1) ?>" 
               class="pagination-btn <?= $curPage >= $totalPages ? 'disabled' : '' ?>" 
               aria-label="Next Page" style="text-decoration: none; display: inline-flex; align-items: center; justify-content: center;">
              <i class="ri-arrow-right-s-line"></i>
            </a>
          </div>
        <?php endif; ?>
      <?php endif; ?>

    </div>
  </section>

  <!-- 5. PARENT TIPS / JOURNAL STRIP -->
  <section class="blog-tips-strip-section">
    <div class="blog-tips-strip-header">
      <div class="container">
        <span class="strip-subtitle">Quick Tips</span>
        <h2 class="strip-title">Daily Care Rituals</h2>
      </div>
    </div>
    <div class="blog-tips-marquee">
      <div class="blog-tips-track">

        <!-- Tip Card 1 -->
        <div class="tip-card">
          <div class="tip-icon"><i class="ri-heart-line"></i></div>
          <div class="tip-card-content">
            <h4 class="tip-card-title">Pat Dry Gently</h4>
            <p class="tip-card-desc">Always pat baby skin dry with a soft cloth instead of rubbing to avoid friction rashes.</p>
          </div>
        </div>

        <!-- Tip Card 2 -->
        <div class="tip-card">
          <div class="tip-icon"><i class="ri-time-line"></i></div>
          <div class="tip-card-content">
            <h4 class="tip-card-title">Change Regularly</h4>
            <p class="tip-card-desc">Change newborn diapers every 3-4 hours to keep active wetness off sensitive skin barriers.</p>
          </div>
        </div>

        <!-- Tip Card 3 -->
        <div class="tip-card">
          <div class="tip-icon"><i class="ri-shield-check-line"></i></div>
          <div class="tip-card-content">
            <h4 class="tip-card-title">TCF Certified Only</h4>
            <p class="tip-card-desc">Choose Totally Chlorine-Free diapers to ensure zero toxic bleach residues touch your baby.</p>
          </div>
        </div>

        <!-- Tip Card 4 -->
        <div class="tip-card">
          <div class="tip-icon"><i class="ri-windy-line"></i></div>
          <div class="tip-card-content">
            <h4 class="tip-card-title">Diaper-Free Time</h4>
            <p class="tip-card-desc">Allow 10-15 minutes of air circulation daily to naturally dry skin folds.</p>
          </div>
        </div>

        <!-- Tip Card 5 -->
        <div class="tip-card">
          <div class="tip-icon"><i class="ri-drop-line"></i></div>
          <div class="tip-card-content">
            <h4 class="tip-card-title">Pure Water Cleansing</h4>
            <p class="tip-card-desc">Use pure water and soft wipes rather than fragranced alcohol-based products.</p>
          </div>
        </div>

      </div>
      <!-- Duplicate Track for Seamless Loop -->
      <div class="blog-tips-track" aria-hidden="true">

        <!-- Tip Card 1 -->
        <div class="tip-card">
          <div class="tip-icon"><i class="ri-heart-line"></i></div>
          <div class="tip-card-content">
            <h4 class="tip-card-title">Pat Dry Gently</h4>
            <p class="tip-card-desc">Always pat baby skin dry with a soft cloth instead of rubbing to avoid friction rashes.</p>
          </div>
        </div>

        <!-- Tip Card 2 -->
        <div class="tip-card">
          <div class="tip-icon"><i class="ri-time-line"></i></div>
          <div class="tip-card-content">
            <h4 class="tip-card-title">Change Regularly</h4>
            <p class="tip-card-desc">Change newborn diapers every 3-4 hours to keep active wetness off sensitive skin barriers.</p>
          </div>
        </div>

        <!-- Tip Card 3 -->
        <div class="tip-card">
          <div class="tip-icon"><i class="ri-shield-check-line"></i></div>
          <div class="tip-card-content">
            <h4 class="tip-card-title">TCF Certified Only</h4>
            <p class="tip-card-desc">Choose Totally Chlorine-Free diapers to ensure zero toxic bleach residues touch your baby.</p>
          </div>
        </div>

        <!-- Tip Card 4 -->
        <div class="tip-card">
          <div class="tip-icon"><i class="ri-windy-line"></i></div>
          <div class="tip-card-content">
            <h4 class="tip-card-title">Diaper-Free Time</h4>
            <p class="tip-card-desc">Allow 10-15 minutes of air circulation daily to naturally dry skin folds.</p>
          </div>
        </div>

        <!-- Tip Card 5 -->
        <div class="tip-card">
          <div class="tip-icon"><i class="ri-drop-line"></i></div>
          <div class="tip-card-content">
            <h4 class="tip-card-title">Pure Water Cleansing</h4>
            <p class="tip-card-desc">Use pure water and soft wipes rather than fragranced alcohol-based products.</p>
          </div>
        </div>

      </div>
    </div>
  </section>

  <!-- 6. NEWSLETTER / CTA SECTION -->
  <section class="blog-newsletter-section">
    <div class="blog-newsletter-bg"></div>
    <div class="blog-newsletter-overlay"></div>
    <div class="blog-newsletter-content container">
      <span class="newsletter-eyebrow">CloudCush Circle</span>
      <h2 class="blog-newsletter-title">Insights for Mindful Parents</h2>
      <p class="blog-newsletter-desc">
        Receive our bi-weekly dispatch of pediatrician articles, skin-safety checklists, and subscriber-only collections.
      </p>
      <form class="blog-newsletter-form" action="javascript:void(0);" method="POST">
        <div class="form-input-wrap">
          <input type="email" placeholder="Your Email Address" required class="newsletter-email-input">
          <button type="submit" class="btn-pill-white">Subscribe</button>
        </div>
      </form>
      <p class="newsletter-disclaimer">Zero spam. Unsubscribe anytime.</p>
    </div>
  </section>

</main>

<?php include 'includes/footer.php'; ?>
