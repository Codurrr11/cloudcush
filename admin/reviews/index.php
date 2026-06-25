<?php
// admin/reviews/index.php — Reviews Listing with Search, Status Filters, Pagination
require_once __DIR__ . '/../includes/auth-check.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/reviews-helper.php';

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
                    $stmt = $db->prepare("UPDATE reviews SET status = :status WHERE id = :id");
                    $stmt->execute([':status' => $status, ':id' => $id]);
                    if ($stmt->rowCount() === 0) {
                        $_SESSION['flash_message'] = 'Review not found or status unchanged.';
                        $_SESSION['flash_type']    = 'warning';
                    } else {
                        $labels = ['active' => 'Approved', 'draft' => 'set to Pending'];
                        $_SESSION['flash_message'] = 'Review status ' . ($labels[$status] ?? $status) . ' successfully.';
                        $_SESSION['flash_type']    = 'success';
                    }
                } catch (\PDOException $e) {
                    error_log('Review status toggle error: ' . $e->getMessage());
                    $_SESSION['flash_message'] = 'Database error while updating status.';
                    $_SESSION['flash_type']    = 'error';
                }
            } else {
                $_SESSION['flash_message'] = 'Invalid status.';
                $_SESSION['flash_type']    = 'error';
            }
            header('Location: ' . BASE_URL . 'reviews/');
            exit;
        } else {
            // Delete review!
            try {
                $db = getDBConnection();
                $stmt = $db->prepare("SELECT name, media_url FROM reviews WHERE id = :id LIMIT 1");
                $stmt->execute(['id' => $id]);
                $review = $stmt->fetch();

                if ($review) {
                    $stmt = $db->prepare("DELETE FROM reviews WHERE id = :id");
                    $stmt->execute(['id' => $id]);

                    if ($review['media_url']) {
                        $localPath = str_replace(UPLOAD_URL, UPLOAD_DIR, $review['media_url']);
                        if (file_exists($localPath)) @unlink($localPath);
                    }
                    $_SESSION['flash_message'] = "Review by \"{$review['name']}\" deleted successfully.";
                    $_SESSION['flash_type'] = 'success';
                } else {
                    $_SESSION['flash_message'] = 'Review not found.';
                    $_SESSION['flash_type'] = 'error';
                }
            } catch (\PDOException $e) {
                error_log('Review delete error: ' . $e->getMessage());
                $_SESSION['flash_message'] = 'Database error while deleting review.';
                $_SESSION['flash_type'] = 'error';
            }
            header('Location: ' . BASE_URL . 'reviews/');
            exit;
        }
    }
}

$page_title  = 'CloudCush Admin — Reviews';
$active_page = 'reviews';

// Gather filter params
$search   = trim($_GET['search']   ?? '');
$status   = trim($_GET['status']   ?? '');
$curPage  = max(1, (int)($_GET['page'] ?? 1));

$result     = getReviews(['search' => $search, 'status' => $status, 'page' => $curPage, 'per_page' => 15]);
$reviews    = $result['data'];
$totalPages = $result['total_pages'];
$totalCount = $result['total'];

include __DIR__ . '/../includes/header.php';
?>

<!-- Handler URLs for JS -->
<input type="hidden" id="deleteReviewHandlerUrl" value="<?= BASE_URL ?>reviews/index.php">
<input type="hidden" id="statusHandlerUrl" value="<?= BASE_URL ?>reviews/index.php">

<div id="wrapper">
    <?php include __DIR__ . '/../includes/sidebar.php'; ?>

    <div id="page-content-wrapper">
        <?php include __DIR__ . '/../includes/navbar.php'; ?>

        <div class="container-fluid px-0 py-2">

            <!-- Page Header -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
                <div>
                    <h1 class="h4 fw-bold mb-1 page-heading">Customer Reviews & Testimonials</h1>
                    <p class="text-secondary mb-0 fs-0-82">
                        Manage feedback shown on the Homepage and About pages &mdash;
                        <span class="tech-data fw-semibold"><?= number_format($totalCount) ?></span>
                        review<?= $totalCount !== 1 ? 's' : '' ?> total
                    </p>
                </div>
                <div class="d-flex gap-2 flex-shrink-0">
                    <a href="<?= BASE_URL ?>reviews/add.php" class="btn btn-premium-primary btn-sm d-flex align-items-center gap-2">
                        <i data-lucide="plus" class="icon-sm"></i>
                        <span>Add Review</span>
                    </a>
                </div>
            </div>

            <!-- Filter Bar -->
            <form method="GET" action="" id="reviewsFilterForm">
                <div class="products-filterbar">
                    <!-- Search -->
                    <div class="search-input-wrap">
                        <i data-lucide="search" class="search-icon"></i>
                        <input type="text" name="search" class="form-control"
                               placeholder="Search reviewer name, role, quote…"
                               value="<?= htmlspecialchars($search) ?>">
                    </div>

                    <!-- Status filter -->
                    <div class="products-filterbar-selects">
                    <select name="status" class="form-select filter-autosubmit">
                        <option value="">All Statuses</option>
                        <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Approved</option>
                        <option value="draft"  <?= $status === 'draft'  ? 'selected' : '' ?>>Pending</option>
                    </select>
                    </div>

                    <button type="submit" class="btn btn-premium-secondary btn-sm d-flex align-items-center gap-2">
                        <i data-lucide="search" class="icon-xs"></i>
                        <span>Search</span>
                    </button>

                    <?php if ($search || $status): ?>
                        <a href="<?= BASE_URL ?>reviews/" class="btn btn-premium-secondary btn-sm d-flex align-items-center gap-2">
                            <i data-lucide="x" class="icon-xs"></i>
                            <span>Clear</span>
                        </a>
                    <?php endif; ?>
                </div>
            </form>

            <!-- Reviews Table Card -->
            <div class="card-premium p-0 overflow-hidden">

                <?php if (empty($reviews)): ?>
                    <!-- Empty state -->
                    <div class="products-empty-state">
                        <div class="products-empty-icon">
                            <i data-lucide="star" class="icon-xl"></i>
                        </div>
                        <h5 class="fw-bold mb-1 fs-0-95">No Reviews Found</h5>
                        <p class="text-secondary mb-3 mx-auto max-width-360 fs-0-81 line-height-1-55">
                            <?= ($search || $status)
                                ? 'No customer reviews match your current filters. Try adjusting your search or clearing filters.'
                                : 'No customer reviews have been added yet. Add your first review to show it on the site.' ?>
                        </p>
                        <?php if (!$search && !$status): ?>
                            <a href="<?= BASE_URL ?>reviews/add.php" class="btn btn-premium-primary btn-sm d-flex align-items-center gap-2 mx-auto w-fit-content">
                                <i data-lucide="plus" class="icon-xs"></i>
                                Add Review
                            </a>
                        <?php else: ?>
                            <a href="<?= BASE_URL ?>reviews/" class="btn btn-premium-secondary btn-sm mx-auto w-fit-content">Clear Filters</a>
                        <?php endif; ?>
                    </div>

                <?php else: ?>
                    <div class="table-premium-container">
                        <table class="table table-premium align-middle">
                            <thead>
                                <tr>
                                    <th class="w-90-px">Media</th>
                                    <th>Customer</th>
                                    <th class="d-none d-md-table-cell">Details / Subheading</th>
                                    <th class="w-110-px text-center">Rating</th>
                                    <th>Quote</th>
                                    <th class="w-130-px">Status</th>
                                    <th class="d-none d-xl-table-cell w-120-px">Added Date</th>
                                    <th class="w-108-px text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reviews as $rev): ?>
                                <tr>
                                    <!-- Media representation -->
                                    <td>
                                        <?php if ($rev['media_type'] === 'image' && $rev['media_url']): ?>
                                            <div class="tbl-thumb">
                                                <img src="<?= htmlspecialchars(resolveAdminAssetUrl($rev['media_url'])) ?>" alt="Review image" loading="lazy">
                                            </div>
                                        <?php elseif ($rev['media_type'] === 'video' && $rev['media_url']): ?>
                                            <div class="tbl-thumb d-flex align-items-center justify-content-center bg-dark text-white rounded position-relative" style="width: 52px; height: 38px;">
                                                <i data-lucide="play" class="icon-xs text-white opacity-75"></i>
                                                <span class="fs-0-6 position-absolute bottom-0 start-50 translate-middle-x pb-1" style="font-size: 8px !important;">VIDEO</span>
                                            </div>
                                        <?php else: ?>
                                            <div class="d-flex align-items-center justify-content-center tbl-thumb-placeholder bg-light rounded text-muted-custom" style="width: 52px; height: 38px;">
                                                <i data-lucide="file-text" class="icon-sm"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <!-- Name -->
                                    <td>
                                        <span class="text-main fw-semibold fs-0-88"><?= htmlspecialchars($rev['name']) ?></span>
                                    </td>

                                    <!-- Details / Role -->
                                    <td class="d-none d-md-table-cell">
                                        <span class="fs-0-82 text-muted-custom"><?= htmlspecialchars($rev['role'] ?? '—') ?></span>
                                    </td>

                                    <!-- Rating -->
                                    <td class="text-center">
                                        <span class="text-warning-custom fw-bold fs-0-83"><?= str_repeat('★', $rev['rating']) . str_repeat('☆', 5 - $rev['rating']) ?></span>
                                    </td>

                                    <!-- Quote -->
                                    <td>
                                        <?php 
                                        $plain_quote = strip_tags($rev['quote']);
                                        $words = explode(' ', trim($plain_quote));
                                        $quote_preview = (count($words) > 4) ? implode(' ', array_slice($words, 0, 4)) . '...' : $plain_quote;
                                        ?>
                                        <div class="fs-0-82 text-muted-custom" title="<?= htmlspecialchars($plain_quote) ?>">
                                            <?= htmlspecialchars($quote_preview) ?>
                                        </div>
                                    </td>

                                    <!-- Status quick toggle -->
                                    <td>
                                        <select class="form-select form-select-sm status-quick-toggle"
                                                data-id="<?= $rev['id'] ?>"
                                                style="font-size: 0.73rem; padding: 0.15rem 1.4rem 0.15rem 0.4rem; height: auto;">
                                            <option value="draft"  <?= $rev['status'] === 'draft'  ? 'selected' : '' ?>>Pending</option>
                                            <option value="active" <?= $rev['status'] === 'active' ? 'selected' : '' ?>>Approved</option>
                                        </select>
                                    </td>

                                    <!-- Added Date -->
                                    <td class="d-none d-xl-table-cell tech-data fs-0-78 text-muted-custom">
                                        <?= date('M j, Y', strtotime($rev['created_at'])) ?>
                                    </td>

                                    <!-- Actions -->
                                    <td>
                                        <div class="tbl-actions">
                                            <a href="<?= BASE_URL ?>reviews/edit.php?id=<?= $rev['id'] ?>"
                                               class="btn-action btn-action-edit" title="Edit Review">
                                                <i data-lucide="pencil" class="icon-sm"></i>
                                            </a>
                                            <button type="button"
                                                    class="btn-action btn-action-delete btn-delete-review"
                                                    data-id="<?= $rev['id'] ?>"
                                                    data-name="Review by <?= htmlspecialchars($rev['name']) ?>"
                                                    title="Delete Review">
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
                        <div class="pagination-footer">
                            <span class="fs-0-75 text-secondary">
                                Showing Page <strong><?= $curPage ?></strong> of <strong><?= $totalPages ?></strong>
                            </span>
                            <nav aria-label="Page navigation">
                                <ul class="pagination pagination-sm mb-0">
                                    <!-- Prev -->
                                    <li class="page-item <?= $curPage <= 1 ? 'disabled' : '' ?>">
                                        <a class="page-link" href="?search=<?= urlencode($search) ?>&status=<?= urlencode($status) ?>&page=<?= $curPage - 1 ?>">Previous</a>
                                    </li>
                                    <!-- Pages -->
                                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                        <li class="page-item <?= $curPage === $i ? 'active' : '' ?>">
                                            <a class="page-link" href="?search=<?= urlencode($search) ?>&status=<?= urlencode($status) ?>&page=<?= $i ?>"><?= $i ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    <!-- Next -->
                                    <li class="page-item <?= $curPage >= $totalPages ? 'disabled' : '' ?>">
                                        <a class="page-link" href="?search=<?= urlencode($search) ?>&status=<?= urlencode($status) ?>&page=<?= $curPage + 1 ?>">Next</a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    <?php endif; ?>

                <?php endif; ?>

            </div><!-- /card-premium -->

        </div><!-- /container-fluid -->
    </div><!-- /page-content-wrapper -->
</div><!-- /wrapper -->

<?php include __DIR__ . '/../includes/footer.php'; ?>
