<?php
// admin/blogs/index.php — Blog Listing with Search, Filter, Pagination
require_once __DIR__ . '/../includes/auth-check.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/blogs-helper.php';

// Check if delete or status quick-toggle is being processed
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    if ($id) {
        if (isset($_POST['status'])) {
            // Quick status toggle!
            $status = trim($_POST['status']);
            $allowed = ['active', 'draft'];
            if (in_array($status, $allowed)) {
                try {
                    $db = getDBConnection();
                    $stmt = $db->prepare("UPDATE blogs SET status = :status WHERE id = :id");
                    $stmt->execute([':status' => $status, ':id' => $id]);
                    if ($stmt->rowCount() === 0) {
                        $_SESSION['flash_message'] = 'Article not found or status unchanged.';
                        $_SESSION['flash_type']    = 'warning';
                    } else {
                        $labels = ['active' => 'Published', 'draft' => 'set to Draft'];
                        $_SESSION['flash_message'] = 'Article ' . ($labels[$status] ?? $status) . ' successfully.';
                        $_SESSION['flash_type']    = 'success';
                    }
                } catch (\PDOException $e) {
                    error_log('Status toggle error: ' . $e->getMessage());
                    $_SESSION['flash_message'] = 'Database error while updating status.';
                    $_SESSION['flash_type']    = 'error';
                }
            } else {
                $_SESSION['flash_message'] = 'Invalid status.';
                $_SESSION['flash_type']    = 'error';
            }
            
            $ref = $_SERVER['HTTP_REFERER'] ?? '';
            if (strpos($ref, 'view.php') !== false) {
                header('Location: ' . BASE_URL . "blogs/view.php?id=$id");
            } else {
                header('Location: ' . BASE_URL . 'blogs/');
            }
            exit;
        } else {
            // Delete blog!
            try {
                $db = getDBConnection();
                $stmt = $db->prepare("SELECT title, thumbnail FROM blogs WHERE id = :id LIMIT 1");
                $stmt->execute(['id' => $id]);
                $blog = $stmt->fetch();

                if ($blog) {
                    $stmt = $db->prepare("DELETE FROM blogs WHERE id = :id");
                    $stmt->execute(['id' => $id]);

                    if ($blog['thumbnail']) {
                        $localPath = str_replace(UPLOAD_URL, UPLOAD_DIR, $blog['thumbnail']);
                        if (file_exists($localPath)) @unlink($localPath);
                    }
                    $_SESSION['flash_message'] = "Article \"{$blog['title']}\" deleted successfully.";
                    $_SESSION['flash_type'] = 'success';
                } else {
                    $_SESSION['flash_message'] = 'Article not found.';
                    $_SESSION['flash_type'] = 'error';
                }
            } catch (\PDOException $e) {
                error_log('Blog delete error: ' . $e->getMessage());
                $_SESSION['flash_message'] = 'Database error while deleting article.';
                $_SESSION['flash_type'] = 'error';
            }
            header('Location: ' . BASE_URL . 'blogs/');
            exit;
        }
    }
}

$page_title  = 'CloudCush Admin — Blogs';
$active_page = 'blogs';

// Gather filter params
$search   = trim($_GET['search']   ?? '');
$category = trim($_GET['category'] ?? '');
$status   = trim($_GET['status']   ?? '');
$curPage  = max(1, (int)($_GET['page'] ?? 1));

$result     = getBlogs(['search' => $search, 'category' => $category, 'status' => $status, 'page' => $curPage, 'per_page' => 15]);
$blogs      = $result['data'];
$totalPages = $result['total_pages'];
$totalCount = $result['total'];
$categories = getBlogCategories();

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
                <div>
                    <h1 class="h4 fw-bold mb-1 page-heading">Blog & Article Management</h1>
                    <p class="text-secondary mb-0 fs-0-82">
                        Publish guidelines, guides, stories and scientific insights &mdash;
                        <span class="tech-data fw-semibold"><?= number_format($totalCount) ?></span>
                        article<?= $totalCount !== 1 ? 's' : '' ?> total
                    </p>
                </div>
                <div class="d-flex gap-2 flex-shrink-0">
                    <a href="<?= BASE_URL ?>blogs/add.php" class="btn btn-premium-primary btn-sm d-flex align-items-center gap-2">
                        <i data-lucide="plus" class="icon-sm"></i>
                        <span>Add Article</span>
                    </a>
                </div>
            </div>

            <!-- Filter Bar -->
            <form method="GET" action="" id="blogsFilterForm">
                <div class="products-filterbar">
                    <!-- Search -->
                    <div class="search-input-wrap">
                        <i data-lucide="search" class="search-icon"></i>
                        <input type="text" name="search" class="form-control"
                               placeholder="Search title, content, category…"
                               value="<?= htmlspecialchars($search) ?>">
                    </div>

                    <!-- Category filter -->
                    <div class="products-filterbar-selects">
                    <select name="category" class="form-select filter-autosubmit">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= htmlspecialchars($cat) ?>" <?= $category === $cat ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <!-- Status filter -->
                    <select name="status" class="form-select filter-autosubmit">
                        <option value="">All Statuses</option>
                        <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Published</option>
                        <option value="draft"  <?= $status === 'draft'  ? 'selected' : '' ?>>Draft</option>
                    </select>
                    </div>

                    <button type="submit" class="btn btn-premium-secondary btn-sm d-flex align-items-center gap-2">
                        <i data-lucide="search" class="icon-xs"></i>
                        <span>Search</span>
                    </button>

                    <?php if ($search || $category || $status): ?>
                        <a href="<?= BASE_URL ?>blogs/" class="btn btn-premium-secondary btn-sm d-flex align-items-center gap-2">
                            <i data-lucide="x" class="icon-xs"></i>
                            <span>Clear</span>
                        </a>
                    <?php endif; ?>
                </div>
            </form>

            <!-- Blogs Table Card -->
            <div class="card-premium p-0 overflow-hidden">

                <?php if (empty($blogs)): ?>
                    <!-- Empty state -->
                    <div class="products-empty-state">
                        <div class="products-empty-icon">
                            <i data-lucide="book-open" class="icon-xl"></i>
                        </div>
                        <h5 class="fw-bold mb-1 fs-0-95">No Articles Found</h5>
                        <p class="text-secondary mb-3 mx-auto max-width-360 fs-0-81 line-height-1-55">
                            <?= ($search || $category || $status)
                                ? 'No articles match your current filters. Try adjusting your search or clearing filters.'
                                : 'Your journal is empty. Add your first article to get started.' ?>
                        </p>
                        <?php if (!$search && !$category && !$status): ?>
                            <a href="<?= BASE_URL ?>blogs/add.php" class="btn btn-premium-primary btn-sm d-flex align-items-center gap-2 mx-auto w-fit-content">
                                <i data-lucide="plus" class="icon-xs"></i>
                                Add Article
                            </a>
                        <?php else: ?>
                            <a href="<?= BASE_URL ?>blogs/" class="btn btn-premium-secondary btn-sm mx-auto w-fit-content">Clear Filters</a>
                        <?php endif; ?>
                    </div>

                <?php else: ?>
                    <div class="table-premium-container">
                        <table class="table table-premium align-middle">
                            <thead>
                                <tr>
                                    <th class="w-80-px">Thumbnail</th>
                                    <th>Title</th>
                                    <th class="d-none d-md-table-cell w-150-px">Category</th>
                                    <th class="d-none d-sm-table-cell w-100-px">Read Time</th>
                                    <th class="d-none d-lg-table-cell w-155-px">Author</th>
                                    <th class="w-130-px">Status</th>
                                    <th class="d-none d-xl-table-cell w-120-px">Published Date</th>
                                    <th class="w-108-px text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($blogs as $blog): ?>
                                <tr>
                                    <!-- Thumbnail -->
                                    <td>
                                        <div class="tbl-thumb">
                                            <?php if ($blog['thumbnail']): ?>
                                                <img src="<?= htmlspecialchars(resolveAdminAssetUrl($blog['thumbnail'])) ?>"
                                                     alt="<?= htmlspecialchars($blog['title']) ?>"
                                                     loading="lazy">
                                            <?php else: ?>
                                                <div class="d-flex align-items-center justify-content-center w-100 h-100 tbl-thumb-placeholder">
                                                    <i data-lucide="image" class="icon-md"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </td>

                                    <!-- Title -->
                                    <td class="max-width-0">
                                        <div class="product-title-cell">
                                            <a href="<?= BASE_URL ?>blogs/view.php?id=<?= $blog['id'] ?>" class="product-title-text fw-semibold text-decoration-none text-dark" title="<?= htmlspecialchars($blog['title']) ?>">
                                                <?= htmlspecialchars($blog['title']) ?>
                                            </a>
                                            <?php if ($blog['short_description']): ?>
                                                <div class="text-secondary fs-0-75 text-truncate" style="max-width: 400px;">
                                                    <?= htmlspecialchars($blog['short_description']) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </td>

                                    <!-- Category -->
                                    <td class="d-none d-md-table-cell">
                                        <span class="table-category-cell" title="<?= htmlspecialchars($blog['category']) ?>">
                                            <?= htmlspecialchars($blog['category']) ?>
                                        </span>
                                    </td>

                                    <!-- Read Time -->
                                    <td class="d-none d-sm-table-cell">
                                        <span class="tech-data">
                                            <?= (int)$blog['read_time'] ?> min
                                        </span>
                                    </td>

                                    <!-- Author -->
                                    <td class="d-none d-lg-table-cell">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="avatar avatar-xxs rounded-circle bg-secondary text-white fw-bold d-flex align-items-center justify-content-center" style="width:20px;height:20px;font-size:0.6rem;">
                                                <?= strtoupper(substr($blog['author_name'] ?? 'A', 0, 1)) ?>
                                            </span>
                                            <span class="fs-0-82 text-dark text-truncate" style="max-width:120px;">
                                                <?= htmlspecialchars($blog['author_name'] ?? 'Admin') ?>
                                            </span>
                                        </div>
                                    </td>

                                    <!-- Status quick toggle select -->
                                    <td>
                                        <select class="form-select form-select-xs status-quick-toggle w-110-px fw-semibold"
                                                data-id="<?= $blog['id'] ?>"
                                                style="padding: 0.15rem 0.4rem; font-size: 0.72rem; border-radius: 4px;">
                                            <option value="draft" <?= $blog['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
                                            <option value="active" <?= $blog['status'] === 'active' ? 'selected' : '' ?>>Published</option>
                                        </select>
                                    </td>

                                    <!-- Created Date -->
                                    <td class="d-none d-xl-table-cell">
                                        <span class="tech-data fs-0-73 text-muted text-nowrap">
                                            <?= date('M j, Y', strtotime($blog['created_at'])) ?>
                                        </span>
                                    </td>

                                    <!-- Actions -->
                                    <td>
                                        <div class="tbl-actions">
                                            <a href="<?= BASE_URL ?>blogs/view.php?id=<?= $blog['id'] ?>"
                                               class="btn-action btn-action-view" title="Preview article">
                                                <i data-lucide="eye" class="icon-sm"></i>
                                            </a>
                                            <a href="<?= BASE_URL ?>blogs/edit.php?id=<?= $blog['id'] ?>"
                                               class="btn-action btn-action-edit" title="Edit article">
                                                <i data-lucide="pencil" class="icon-sm"></i>
                                            </a>
                                            <button type="button"
                                                    class="btn-action btn-action-delete btn-delete-blog"
                                                    data-id="<?= $blog['id'] ?>"
                                                    data-name="<?= htmlspecialchars($blog['title']) ?>"
                                                    title="Delete article">
                                                <i data-lucide="trash-2" class="icon-sm"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <?php
                        $baseUrl = BASE_URL . "blogs/?search=" . urlencode($search)
                                 . "&category=" . urlencode($category)
                                 . "&status=" . urlencode($status);
                        ?>
                        <div class="pagination-footer">
                            <span class="pagination-info">
                                Page <span class="tech-data fw-bold text-dark"><?= $curPage ?></span>
                                of <span class="tech-data fw-bold text-dark"><?= $totalPages ?></span>
                                &middot; <?= number_format($totalCount) ?> total
                            </span>
                            <div class="pagination-premium">
                                <a href="<?= $baseUrl ?>&page=<?= max(1, $curPage - 1) ?>"
                                   class="page-btn <?= $curPage <= 1 ? 'disabled' : '' ?>" aria-label="Previous page">
                                    <i data-lucide="chevron-left" class="icon-sm"></i>
                                </a>

                                <?php
                                $pStart = max(1, $curPage - 2);
                                $pEnd   = min($totalPages, $curPage + 2);
                                if ($pStart > 1): ?>
                                    <a href="<?= $baseUrl ?>&page=1" class="page-btn">1</a>
                                    <?php if ($pStart > 2): ?>
                                        <span class="page-btn page-ellipsis">…</span>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <?php for ($pNum = $pStart; $pNum <= $pEnd; $pNum++): ?>
                                    <a href="<?= $baseUrl ?>&page=<?= $pNum ?>"
                                       class="page-btn <?= $pNum === $curPage ? 'active' : '' ?>">
                                        <?= $pNum ?>
                                    </a>
                                <?php endfor; ?>

                                <?php if ($pEnd < $totalPages): ?>
                                    <?php if ($pEnd < $totalPages - 1): ?>
                                        <span class="page-btn page-ellipsis">…</span>
                                    <?php endif; ?>
                                    <a href="<?= $baseUrl ?>&page=<?= $totalPages ?>" class="page-btn"><?= $totalPages ?></a>
                                <?php endif; ?>

                                <a href="<?= $baseUrl ?>&page=<?= min($totalPages, $curPage + 1) ?>"
                                   class="page-btn <?= $curPage >= $totalPages ? 'disabled' : '' ?>" aria-label="Next page">
                                    <i data-lucide="chevron-right" class="icon-sm"></i>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>

                <?php endif; ?>
            </div><!-- /card-premium -->

        </div><!-- /container-fluid -->
    </div><!-- /page-content-wrapper -->
</div><!-- /wrapper -->

<?php include __DIR__ . '/../includes/footer.php'; ?>
