<?php
// admin/products/index.php — Product Listing with Search, Filter, Pagination
require_once __DIR__ . '/../includes/auth-check.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/products-helper.php';

// Check if delete or status quick-toggle is being processed
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    if ($id) {
        if (isset($_POST['status'])) {
            // Quick status toggle!
            $status = trim($_POST['status']);
            $allowed = ['active', 'draft', 'out_of_stock', 'archived'];
            if (in_array($status, $allowed)) {
                try {
                    $db = getDBConnection();
                    $stmt = $db->prepare("UPDATE products SET status = :status WHERE id = :id");
                    $stmt->execute([':status' => $status, ':id' => $id]);
                    if ($stmt->rowCount() === 0) {
                        $_SESSION['flash_message'] = 'Product not found or status unchanged.';
                        $_SESSION['flash_type']    = 'warning';
                    } else {
                        $labels = ['active' => 'Published', 'draft' => 'set to Draft', 'out_of_stock' => 'marked Out of Stock', 'archived' => 'Archived'];
                        $_SESSION['flash_message'] = 'Product ' . ($labels[$status] ?? $status) . ' successfully.';
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
            // Redirect back to either view page or catalog list based on HTTP_REFERER
            $ref = $_SERVER['HTTP_REFERER'] ?? '';
            if (strpos($ref, 'view.php') !== false) {
                header('Location: ' . BASE_URL . "products/view.php?id=$id");
            } else {
                header('Location: ' . BASE_URL . 'products/');
            }
            exit;
        } else {
            // Delete product!
            try {
                $db = getDBConnection();
                $stmt = $db->prepare("SELECT title, image_url, gallery_images FROM products WHERE id = :id LIMIT 1");
                $stmt->execute(['id' => $id]);
                $product = $stmt->fetch();

                if ($product) {
                    $db->prepare("DELETE FROM product_variants WHERE product_id = :id")->execute(['id' => $id]);
                    $db->prepare("DELETE FROM products WHERE id = :id")->execute(['id' => $id]);

                    if ($product['image_url']) {
                        $localPath = str_replace(UPLOAD_URL, UPLOAD_DIR, $product['image_url']);
                        if (file_exists($localPath)) @unlink($localPath);
                    }
                    if ($product['gallery_images']) {
                        $gallery = json_decode($product['gallery_images'], true);
                        foreach ((array)$gallery as $gUrl) {
                            $gPath = str_replace(UPLOAD_URL, UPLOAD_DIR, $gUrl);
                            if (file_exists($gPath)) @unlink($gPath);
                        }
                    }
                    $_SESSION['flash_message'] = "Product \"{$product['title']}\" deleted successfully.";
                    $_SESSION['flash_type'] = 'success';
                } else {
                    $_SESSION['flash_message'] = 'Product not found.';
                    $_SESSION['flash_type'] = 'error';
                }
            } catch (\PDOException $e) {
                error_log('Product delete error: ' . $e->getMessage());
                $_SESSION['flash_message'] = 'Database error while deleting product.';
                $_SESSION['flash_type'] = 'error';
            }
            header('Location: ' . BASE_URL . 'products/');
            exit;
        }
    }
}

$page_title  = 'CloudCush Admin — Products';
$active_page = 'products';

// Gather filter params
$search   = trim($_GET['search']   ?? '');
$category = trim($_GET['category'] ?? '');
$status   = trim($_GET['status']   ?? '');
$curPage  = max(1, (int)($_GET['page'] ?? 1));

$result     = getProducts(['search' => $search, 'category' => $category, 'status' => $status, 'page' => $curPage, 'per_page' => 15]);
$products   = $result['data'];
$totalPages = $result['total_pages'];
$totalCount = $result['total'];
$categories = getProductCategories();

include __DIR__ . '/../includes/header.php';
?>

<!-- Handler URLs for JS -->
<input type="hidden" id="deleteHandlerUrl" value="<?= BASE_URL ?>products/index.php">
<input type="hidden" id="statusHandlerUrl" value="<?= BASE_URL ?>products/index.php">

<div id="wrapper">
    <?php include __DIR__ . '/../includes/sidebar.php'; ?>

    <div id="page-content-wrapper">
        <?php include __DIR__ . '/../includes/navbar.php'; ?>

        <div class="container-fluid px-0 py-2">

            <!-- Page Header -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
                <div>
                    <h1 class="h4 fw-bold mb-1 page-heading">Product Catalog</h1>
                    <p class="text-secondary mb-0 fs-0-82">
                        Manage inventory, pricing, variants and availability &mdash;
                        <span class="tech-data fw-semibold"><?= number_format($totalCount) ?></span>
                        product<?= $totalCount !== 1 ? 's' : '' ?> total
                    </p>
                </div>
                <div class="d-flex gap-2 flex-shrink-0">
                    <a href="<?= BASE_URL ?>products/add.php" class="btn btn-premium-primary btn-sm d-flex align-items-center gap-2">
                        <i data-lucide="plus" class="icon-sm"></i>
                        <span>Add Product</span>
                    </a>
                </div>
            </div>

            <!-- Filter Bar -->
            <form method="GET" action="" id="productsFilterForm">
                <div class="products-filterbar">
                    <!-- Search -->
                    <div class="search-input-wrap">
                        <i data-lucide="search" class="search-icon"></i>
                        <input type="text" name="search" class="form-control"
                               placeholder="Search title, SKU, category…"
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
                        <option value="active"       <?= $status === 'active'       ? 'selected' : '' ?>>Active</option>
                        <option value="draft"        <?= $status === 'draft'        ? 'selected' : '' ?>>Draft</option>
                        <option value="out_of_stock" <?= $status === 'out_of_stock' ? 'selected' : '' ?>>Out of Stock</option>
                        <option value="archived"     <?= $status === 'archived'     ? 'selected' : '' ?>>Archived</option>
                    </select>
                    </div>

                    <button type="submit" class="btn btn-premium-secondary btn-sm d-flex align-items-center gap-2">
                        <i data-lucide="search" class="icon-xs"></i>
                        <span>Search</span>
                    </button>

                    <?php if ($search || $category || $status): ?>
                        <a href="<?= BASE_URL ?>products/" class="btn btn-premium-secondary btn-sm d-flex align-items-center gap-2">
                            <i data-lucide="x" class="icon-xs"></i>
                            <span>Clear</span>
                        </a>
                    <?php endif; ?>
                </div>
            </form>

            <!-- Products Table Card -->
            <div class="card-premium p-0 overflow-hidden">

                <?php if (empty($products)): ?>
                    <!-- Empty state -->
                    <div class="products-empty-state">
                        <div class="products-empty-icon">
                            <i data-lucide="package" class="icon-xl"></i>
                        </div>
                        <h5 class="fw-bold mb-1 fs-0-95">No Products Found</h5>
                        <p class="text-secondary mb-3 mx-auto max-width-360 fs-0-81 line-height-1-55">
                            <?= ($search || $category || $status)
                                ? 'No products match your current filters. Try adjusting your search or clearing filters.'
                                : 'Your catalog is empty. Add your first product to get started.' ?>
                        </p>
                        <?php if (!$search && !$category && !$status): ?>
                            <a href="<?= BASE_URL ?>products/add.php" class="btn btn-premium-primary btn-sm d-flex align-items-center gap-2 mx-auto w-fit-content">
                                <i data-lucide="plus" class="icon-xs"></i>
                                Add Product
                            </a>
                        <?php else: ?>
                            <a href="<?= BASE_URL ?>products/" class="btn btn-premium-secondary btn-sm mx-auto w-fit-content">Clear Filters</a>
                        <?php endif; ?>
                    </div>

                <?php else: ?>
                    <div class="table-premium-container">
                        <table class="table table-premium align-middle">
                            <thead>
                                <tr>
                                    <th class="w-52-px">Image</th>
                                    <th>Product</th>
                                    <th class="d-none d-lg-table-cell w-120-px">SKU</th>
                                    <th class="d-none d-md-table-cell w-130-px">Category</th>
                                    <th class="w-110-px">Price</th>
                                    <th class="d-none d-sm-table-cell w-90-px">Stock</th>
                                    <th class="w-110-px">Status</th>
                                    <th class="d-none d-xl-table-cell w-100-px">Created</th>
                                    <th class="w-108-px text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $prod):
                                    $badge      = getStatusBadge($prod['status']);
                                    $stockClass = $prod['stock'] === 0 ? 'out' : ($prod['stock'] <= $prod['low_stock_threshold'] ? 'low' : 'ok');
                                    $displayPrice = !empty($prod['sale_price']) ? $prod['sale_price'] : $prod['price'];
                                ?>
                                <tr>
                                    <!-- Thumb -->
                                    <td>
                                        <div class="tbl-thumb">
                                            <?php if ($prod['image_url']): ?>
                                                <img src="<?= htmlspecialchars(resolveAdminAssetUrl($prod['image_url'])) ?>"
                                                     alt="<?= htmlspecialchars($prod['title']) ?>"
                                                     loading="lazy">
                                            <?php else: ?>
                                                <div class="d-flex align-items-center justify-content-center w-100 h-100 tbl-thumb-placeholder">
                                                    <i data-lucide="image" class="icon-md"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </td>

                                    <!-- Title + featured badge -->
                                    <td class="max-width-0">
                                        <div class="product-title-cell">
                                            <span class="product-title-text" title="<?= htmlspecialchars($prod['title']) ?>">
                                                <?= htmlspecialchars($prod['title']) ?>
                                            </span>
                                            <?php if ($prod['is_featured']): ?>
                                                <span class="badge-status success product-featured-badge">
                                                    <i data-lucide="star" class="icon-xxs"></i> Featured
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </td>

                                    <!-- SKU -->
                                    <td class="d-none d-lg-table-cell">
                                        <span class="tech-data table-sku-cell" title="<?= htmlspecialchars($prod['sku']) ?>">
                                            <?= htmlspecialchars($prod['sku']) ?>
                                        </span>
                                    </td>

                                    <!-- Category -->
                                    <td class="d-none d-md-table-cell">
                                        <span class="table-category-cell" title="<?= htmlspecialchars($prod['category']) ?>">
                                            <?= htmlspecialchars($prod['category']) ?>
                                        </span>
                                    </td>

                                    <!-- Price -->
                                    <td>
                                        <span class="tech-data fw-bold table-price-cell">
                                            ₹<?= number_format((float)$displayPrice, 2) ?>
                                        </span>
                                        <?php if (!empty($prod['sale_price']) && (float)$prod['sale_price'] < (float)$prod['price']): ?>
                                            <span class="tech-data text-decoration-line-through fs-0-69 text-muted">
                                                ₹<?= number_format((float)$prod['price'], 2) ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Stock -->
                                    <td class="d-none d-sm-table-cell">
                                        <span class="stock-indicator <?= $stockClass ?>">
                                            <?php if ($stockClass === 'out'): ?>
                                                <i data-lucide="alert-circle" class="icon-xs"></i>
                                            <?php elseif ($stockClass === 'low'): ?>
                                                <i data-lucide="alert-triangle" class="icon-xs"></i>
                                            <?php else: ?>
                                                <i data-lucide="check-circle" class="icon-xs"></i>
                                            <?php endif; ?>
                                            <?= number_format($prod['stock']) ?>
                                        </span>
                                    </td>

                                    <!-- Status badge -->
                                    <td>
                                        <span class="badge-status <?= $badge['class'] ?>">
                                            <?= htmlspecialchars($badge['label']) ?>
                                        </span>
                                    </td>

                                    <!-- Created -->
                                    <td class="d-none d-xl-table-cell">
                                        <span class="tech-data fs-0-73 text-muted text-nowrap">
                                            <?= date('M j, Y', strtotime($prod['created_at'])) ?>
                                        </span>
                                    </td>

                                    <!-- Actions -->
                                    <td>
                                        <div class="tbl-actions">
                                            <a href="<?= BASE_URL ?>products/view.php?id=<?= $prod['id'] ?>"
                                               class="btn-action btn-action-view" title="View product">
                                                <i data-lucide="eye" class="icon-sm"></i>
                                            </a>
                                            <a href="<?= BASE_URL ?>products/edit.php?id=<?= $prod['id'] ?>"
                                               class="btn-action btn-action-edit" title="Edit product">
                                                <i data-lucide="pencil" class="icon-sm"></i>
                                            </a>
                                            <button type="button"
                                                    class="btn-action btn-action-delete btn-delete-product"
                                                    data-id="<?= $prod['id'] ?>"
                                                    data-name="<?= htmlspecialchars($prod['title']) ?>"
                                                    title="Delete product">
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
                        $baseUrl = BASE_URL . "products/?search=" . urlencode($search)
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
