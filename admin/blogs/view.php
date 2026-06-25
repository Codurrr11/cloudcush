<?php
// admin/blogs/view.php — Blog Article Detail View
require_once __DIR__ . '/../includes/auth-check.php';
require_once __DIR__ . '/../config/blogs-helper.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    $_SESSION['flash_message'] = 'Invalid article ID.';
    $_SESSION['flash_type']    = 'error';
    header('Location: ' . BASE_URL . 'blogs/');
    exit;
}

$blog = getBlogByIdOrSlug($id);
if (!$blog) {
    $_SESSION['flash_message'] = 'Article not found.';
    $_SESSION['flash_type']    = 'error';
    header('Location: ' . BASE_URL . 'blogs/');
    exit;
}

$page_title  = 'CloudCush Admin — ' . htmlspecialchars($blog['title']);
$active_page = 'blogs';

$badge = getBlogStatusBadge($blog['status']);

include __DIR__ . '/../includes/header.php';
?>

<!-- Handler URLs for JS -->
<input type="hidden" id="deleteBlogHandlerUrl" value="<?= BASE_URL ?>blogs/index.php">
<input type="hidden" id="statusHandlerUrl" value="<?= BASE_URL ?>blogs/index.php">

<div id="wrapper">
    <?php include __DIR__ . '/../includes/sidebar.php'; ?>

    <div id="page-content-wrapper">
        <?php include __DIR__ . '/../includes/navbar.php'; ?>

        <div class="container-fluid px-0 py-2">

            <!-- Page Header -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
                <div class="min-width-0 flex-1">
                    <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                        <a href="<?= BASE_URL ?>blogs/" class="btn-action btn-back-square" title="Back to Articles">
                            <i data-lucide="arrow-left" class="icon-sm"></i>
                        </a>
                        <h1 class="h4 fw-bold mb-0 page-heading text-ellipsis-100">
                            <?= htmlspecialchars($blog['title']) ?>
                        </h1>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap pl-2-25">
                        <span class="badge-status <?= $badge['class'] ?>"><?= $badge['label'] ?></span>
                        <span class="fs-0-73 text-muted-custom">
                            Category: <strong><?= htmlspecialchars($blog['category']) ?></strong>
                        </span>
                        <span class="color-divider">&middot;</span>
                        <span class="fs-0-73 text-muted-custom">
                            Read Time: <strong><?= (int)$blog['read_time'] ?> min</strong>
                        </span>
                        <span class="color-divider">&middot;</span>
                        <span class="fs-0-73 text-muted-custom">
                            Published <?= date('M j, Y', strtotime($blog['created_at'])) ?>
                        </span>
                    </div>
                </div>
                <div class="d-flex gap-2 flex-shrink-0">
                    <a href="<?= BASE_URL ?>blogs/edit.php?id=<?= $id ?>"
                       class="btn btn-premium-primary btn-sm d-flex align-items-center gap-2">
                        <i data-lucide="pencil" class="icon-md"></i>
                        <span>Edit Article</span>
                    </a>
                    <button type="button"
                            class="btn btn-premium-secondary btn-sm d-flex align-items-center gap-2 btn-delete-blog text-danger-custom"
                            data-id="<?= $blog['id'] ?>"
                            data-name="<?= htmlspecialchars($blog['title']) ?>">
                        <i data-lucide="trash-2" class="icon-md"></i>
                        <span class="d-none d-sm-inline">Delete</span>
                    </button>
                </div>
            </div>

            <div class="row g-4">
                <!-- Left Column: Editorial Preview -->
                <div class="col-12 col-xl-8">
                    <!-- Thumbnail preview -->
                    <?php if ($blog['thumbnail']): ?>
                        <div class="card-premium p-0 overflow-hidden mb-3">
                            <div class="blog-view-thumb-container">
                                <img src="<?= htmlspecialchars($blog['thumbnail']) ?>" 
                                     class="blog-view-thumb-img" 
                                     alt="<?= htmlspecialchars($blog['title']) ?>">
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Content card -->
                    <div class="card-premium mb-3">
                        <span class="form-section-label">Article Excerpt & Body</span>
                        
                        <?php if ($blog['short_description']): ?>
                            <p class="fs-1-05 font-ui text-dark fw-semibold mb-4 border-start border-primary border-3 ps-3 py-1 lh-base style-excerpt-font">
                                <?= htmlspecialchars($blog['short_description']) ?>
                            </p>
                        <?php endif; ?>

                        <!-- Standard rendering with premium typography -->
                        <div class="article-body-preview lh-lg text-dark style-body-text">
                            <?= $blog['content'] // Rich HTML from TinyMCE ?>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Settings & Details -->
                <div class="col-12 col-xl-4">
                    <div class="detail-info-sidebar">
                        
                        <!-- Metadata sidebar card -->
                        <div class="card-premium">
                            <span class="form-section-label">Article Metadata</span>
                            
                            <div class="detail-info-grid">
                                <div class="detail-field">
                                    <div class="detail-field-label">Author</div>
                                    <div class="detail-field-value"><?= htmlspecialchars($blog['author_name'] ?? 'Administrator') ?></div>
                                </div>
                                <div class="detail-field">
                                    <div class="detail-field-label">Slug</div>
                                    <div class="detail-field-value mono blog-view-slug-cell" title="<?= htmlspecialchars($blog['slug']) ?>">
                                        <?= htmlspecialchars($blog['slug']) ?>
                                    </div>
                                </div>
                                <div class="detail-field">
                                    <div class="detail-field-label">Category</div>
                                    <div class="detail-field-value"><?= htmlspecialchars($blog['category']) ?></div>
                                </div>
                                <div class="detail-field">
                                    <div class="detail-field-label">Status</div>
                                    <div class="detail-field-value">
                                        <span class="badge-status <?= $badge['class'] ?>"><?= $badge['label'] ?></span>
                                    </div>
                                </div>
                                <div class="detail-field">
                                    <div class="detail-field-label">Read Time</div>
                                    <div class="detail-field-value"><?= (int)$blog['read_time'] ?> Minutes</div>
                                </div>
                                <div class="detail-field">
                                    <div class="detail-field-label">Created Date</div>
                                    <div class="detail-field-value mono fs-0-78"><?= date('M j, Y H:i', strtotime($blog['created_at'])) ?></div>
                                </div>
                                <div class="detail-field">
                                    <div class="detail-field-label">Last Updated</div>
                                    <div class="detail-field-value mono fs-0-78"><?= date('M j, Y H:i', strtotime($blog['updated_at'])) ?></div>
                                </div>
                            </div>
                        </div>

                        <!-- Direct View Site Card -->
                        <?php if ($blog['status'] === 'active'): ?>
                            <div class="card-premium text-center py-4 bg-primary-light border-0">
                                <div class="stat-card-icon bg-primary mx-auto mb-3 d-flex align-items-center justify-content-center rounded-circle text-white">
                                    <i data-lucide="external-link" class="icon-lg"></i>
                                </div>
                                <h5 class="fw-bold text-dark font-ui">Live in the Journal</h5>
                                <p class="text-secondary small mb-3">This article is published and visible to all site visitors.</p>
                                <a href="../../blog-details.php?slug=<?= urlencode($blog['slug']) ?>" 
                                   target="_blank" 
                                   class="btn btn-premium-primary btn-sm w-100">
                                    View Live Article
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="card-premium text-center py-4 bg-light border-0">
                                <div class="stat-card-icon bg-secondary mx-auto mb-3 d-flex align-items-center justify-content-center rounded-circle text-white">
                                    <i data-lucide="eye-off" class="icon-lg"></i>
                                </div>
                                <h5 class="fw-bold text-muted font-ui">Draft Mode</h5>
                                <p class="text-secondary small mb-3">This article is not currently public. Publish it to view it live.</p>
                                <a href="../../blog-details.php?slug=<?= urlencode($blog['slug']) ?>&preview=1" 
                                   target="_blank" 
                                   class="btn btn-premium-secondary btn-sm w-100">
                                    Preview on Site
                                </a>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>
            </div>

        </div><!-- /container-fluid -->
    </div><!-- /page-content-wrapper -->
</div><!-- /wrapper -->

<?php include __DIR__ . '/../includes/footer.php'; ?>
