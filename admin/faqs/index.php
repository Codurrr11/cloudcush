<?php
// admin/faqs/index.php — FAQs Listing with Search, Category/Status Filters, Pagination
require_once __DIR__ . '/../includes/auth-check.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/faqs-helper.php';

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
                    $stmt = $db->prepare("UPDATE faqs SET status = :status WHERE id = :id");
                    $stmt->execute([':status' => $status, ':id' => $id]);
                    if ($stmt->rowCount() === 0) {
                        $_SESSION['flash_message'] = 'FAQ not found or status unchanged.';
                        $_SESSION['flash_type']    = 'warning';
                    } else {
                        $labels = ['active' => 'Live', 'draft' => 'set to Draft'];
                        $_SESSION['flash_message'] = 'FAQ status ' . ($labels[$status] ?? $status) . ' successfully.';
                        $_SESSION['flash_type']    = 'success';
                    }
                } catch (\PDOException $e) {
                    error_log('FAQ status toggle error: ' . $e->getMessage());
                    $_SESSION['flash_message'] = 'Database error while updating status.';
                    $_SESSION['flash_type']    = 'error';
                }
            } else {
                $_SESSION['flash_message'] = 'Invalid status.';
                $_SESSION['flash_type']    = 'error';
            }
            header('Location: ' . BASE_URL . 'faqs/');
            exit;
        } else {
            // Delete FAQ!
            try {
                $db = getDBConnection();
                $stmt = $db->prepare("SELECT question FROM faqs WHERE id = :id LIMIT 1");
                $stmt->execute(['id' => $id]);
                $faq = $stmt->fetch();

                if ($faq) {
                    $stmt = $db->prepare("DELETE FROM faqs WHERE id = :id");
                    $stmt->execute(['id' => $id]);
                    $_SESSION['flash_message'] = "FAQ deleted successfully.";
                    $_SESSION['flash_type'] = 'success';
                } else {
                    $_SESSION['flash_message'] = 'FAQ not found.';
                    $_SESSION['flash_type'] = 'error';
                }
            } catch (\PDOException $e) {
                error_log('FAQ delete error: ' . $e->getMessage());
                $_SESSION['flash_message'] = 'Database error while deleting FAQ.';
                $_SESSION['flash_type'] = 'error';
            }
            header('Location: ' . BASE_URL . 'faqs/');
            exit;
        }
    }
}

$page_title  = 'CloudCush Admin — FAQs';
$active_page = 'faqs';

// Gather filter params
$search   = trim($_GET['search']   ?? '');
$category = trim($_GET['category'] ?? '');
$status   = trim($_GET['status']   ?? '');
$curPage  = max(1, (int)($_GET['page'] ?? 1));

$result     = getFaqs(['search' => $search, 'category' => $category, 'status' => $status, 'page' => $curPage, 'per_page' => 15]);
$faqs       = $result['data'];
$totalPages = $result['total_pages'];
$totalCount = $result['total'];

$categories = getFaqCategories();

// Category Icon Mapping
$categoryIcons = [
    'product'   => 'package',
    'materials' => 'shield',
    'orders'    => 'truck',
    'returns'   => 'refresh-cw',
    'parenting' => 'heart'
];

include __DIR__ . '/../includes/header.php';
?>

<!-- Handler URLs for JS -->
<input type="hidden" id="deleteFaqHandlerUrl" value="<?= BASE_URL ?>faqs/index.php">
<input type="hidden" id="statusHandlerUrl" value="<?= BASE_URL ?>faqs/index.php">

<div id="wrapper">
    <?php include __DIR__ . '/../includes/sidebar.php'; ?>

    <div id="page-content-wrapper">
        <?php include __DIR__ . '/../includes/navbar.php'; ?>

        <div class="container-fluid px-0 py-2">

            <!-- Page Header -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
                <div>
                    <h1 class="h4 fw-bold mb-1 page-heading">Frequently Asked Questions (FAQs)</h1>
                    <p class="text-secondary mb-0 fs-0-82">
                        Manage help center questions and categories on the public FAQ page &mdash;
                        <span class="tech-data fw-semibold"><?= number_format($totalCount) ?></span>
                        FAQ<?= $totalCount !== 1 ? 's' : '' ?> total
                    </p>
                </div>
                <div class="d-flex gap-2 flex-shrink-0">
                    <a href="<?= BASE_URL ?>faqs/add.php" class="btn btn-premium-primary btn-sm d-flex align-items-center gap-2">
                        <i data-lucide="plus" class="icon-sm"></i>
                        <span>Add FAQ</span>
                    </a>
                </div>
            </div>

            <!-- Filter Bar -->
            <form method="GET" action="" id="faqsFilterForm">
                <div class="products-filterbar">
                    <!-- Search -->
                    <div class="search-input-wrap">
                        <i data-lucide="search" class="search-icon"></i>
                        <input type="text" name="search" class="form-control"
                               placeholder="Search questions or answers…"
                               value="<?= htmlspecialchars($search) ?>">
                    </div>

                    <!-- Category filter -->
                    <div class="products-filterbar-selects">
                    <select name="category" class="form-select filter-autosubmit">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $key => $lbl): ?>
                            <option value="<?= htmlspecialchars($key) ?>" <?= $category === $key ? 'selected' : '' ?>>
                                <?= htmlspecialchars($lbl) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <!-- Status filter -->
                    <select name="status" class="form-select filter-autosubmit">
                        <option value="">All Statuses</option>
                        <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Live</option>
                        <option value="draft"  <?= $status === 'draft'  ? 'selected' : '' ?>>Draft</option>
                    </select>
                    </div>

                    <button type="submit" class="btn btn-premium-secondary btn-sm d-flex align-items-center gap-2">
                        <i data-lucide="search" class="icon-xs"></i>
                        <span>Search</span>
                    </button>

                    <?php if ($search || $category || $status): ?>
                        <a href="<?= BASE_URL ?>faqs/" class="btn btn-premium-secondary btn-sm d-flex align-items-center gap-2">
                            <i data-lucide="x" class="icon-xs"></i>
                            <span>Clear</span>
                        </a>
                    <?php endif; ?>
                </div>
            </form>

            <!-- FAQs Table Card -->
            <div class="card-premium p-0 overflow-hidden">

                <?php if (empty($faqs)): ?>
                    <!-- Empty state -->
                    <div class="products-empty-state">
                        <div class="products-empty-icon">
                            <i data-lucide="help-circle" class="icon-xl"></i>
                        </div>
                        <h5 class="fw-bold mb-1 fs-0-95">No FAQs Found</h5>
                        <p class="text-secondary mb-3 mx-auto max-width-360 fs-0-81 line-height-1-55">
                            <?= ($search || $category || $status)
                                ? 'No FAQs match your current filters. Try adjusting your search or clearing filters.'
                                : 'No FAQs have been added yet. Add your first FAQ to publish it on the site.' ?>
                        </p>
                        <?php if (!$search && !$category && !$status): ?>
                            <a href="<?= BASE_URL ?>faqs/add.php" class="btn btn-premium-primary btn-sm d-flex align-items-center gap-2 mx-auto w-fit-content">
                                <i data-lucide="plus" class="icon-xs"></i>
                                Add FAQ
                            </a>
                        <?php else: ?>
                            <a href="<?= BASE_URL ?>faqs/" class="btn btn-premium-secondary btn-sm mx-auto w-fit-content">Clear Filters</a>
                        <?php endif; ?>
                    </div>

                <?php else: ?>
                    <div class="table-premium-container">
                        <table class="table table-premium align-middle">
                            <thead>
                                <tr>
                                    <th class="w-130-px">Category</th>
                                    <th>Question</th>
                                    <th>Answer Preview</th>
                                    <th class="w-100-px text-center">Sort Order</th>
                                    <th class="w-130-px">Status</th>
                                    <th class="d-none d-xl-table-cell w-120-px">Added Date</th>
                                    <th class="w-108-px text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($faqs as $faq): 
                                    $catIcon = $categoryIcons[$faq['category']] ?? 'help-circle';
                                    $catLabel = $categories[$faq['category']] ?? $faq['category'];
                                ?>
                                <tr>
                                    <!-- Category -->
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="bg-primary-light text-primary rounded d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                                                <i data-lucide="<?= $catIcon ?>" class="icon-xs"></i>
                                            </div>
                                            <span class="fs-0-8 fw-semibold text-dark"><?= htmlspecialchars($catLabel) ?></span>
                                        </div>
                                    </td>

                                    <!-- Question -->
                                    <td class="fw-semibold text-dark fs-0-82 max-width-280-px text-truncate" title="<?= htmlspecialchars($faq['question']) ?>">
                                        <?= htmlspecialchars($faq['question']) ?>
                                    </td>

                                    <!-- Answer Preview -->
                                    <td>
                                        <?php 
                                        $plain_answer = strip_tags($faq['answer']);
                                        $words = explode(' ', trim($plain_answer));
                                        $answer_preview = (count($words) > 6) ? implode(' ', array_slice($words, 0, 6)) . '...' : $plain_answer;
                                        ?>
                                        <div class="fs-0-8 text-muted-custom max-width-320-px text-truncate" title="<?= htmlspecialchars($plain_answer) ?>">
                                            <?= htmlspecialchars($answer_preview) ?>
                                        </div>
                                    </td>

                                    <!-- Sort Order -->
                                    <td class="text-center tech-data fs-0-82 text-dark">
                                        <?= (int)$faq['sort_order'] ?>
                                    </td>

                                    <!-- Status quick toggle -->
                                    <td>
                                        <select class="form-select form-select-sm status-quick-toggle"
                                                data-id="<?= $faq['id'] ?>"
                                                style="font-size: 0.73rem; padding: 0.15rem 1.4rem 0.15rem 0.4rem; height: auto;">
                                            <option value="draft"  <?= $faq['status'] === 'draft'  ? 'selected' : '' ?>>Draft</option>
                                            <option value="active" <?= $faq['status'] === 'active' ? 'selected' : '' ?>>Live</option>
                                        </select>
                                    </td>

                                    <!-- Added Date -->
                                    <td class="d-none d-xl-table-cell tech-data fs-0-78 text-muted-custom">
                                        <?= date('M j, Y', strtotime($faq['created_at'])) ?>
                                    </td>

                                    <!-- Actions -->
                                    <td>
                                        <div class="tbl-actions">
                                            <a href="<?= BASE_URL ?>faqs/edit.php?id=<?= $faq['id'] ?>"
                                               class="btn-action btn-action-edit" title="Edit FAQ">
                                                <i data-lucide="pencil" class="icon-sm"></i>
                                            </a>
                                            <button type="button"
                                                    class="btn-action btn-action-delete btn-delete-faq"
                                                    data-id="<?= $faq['id'] ?>"
                                                    data-name="<?= htmlspecialchars($faq['question']) ?>"
                                                    title="Delete FAQ">
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
                                        <a class="page-link" href="?search=<?= urlencode($search) ?>&category=<?= urlencode($category) ?>&status=<?= urlencode($status) ?>&page=<?= $curPage - 1 ?>">Previous</a>
                                    </li>
                                    <!-- Pages -->
                                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                        <li class="page-item <?= $curPage === $i ? 'active' : '' ?>">
                                            <a class="page-link" href="?search=<?= urlencode($search) ?>&category=<?= urlencode($category) ?>&status=<?= urlencode($status) ?>&page=<?= $i ?>"><?= $i ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    <!-- Next -->
                                    <li class="page-item <?= $curPage >= $totalPages ? 'disabled' : '' ?>">
                                        <a class="page-link" href="?search=<?= urlencode($search) ?>&category=<?= urlencode($category) ?>&status=<?= urlencode($status) ?>&page=<?= $curPage + 1 ?>">Next</a>
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
