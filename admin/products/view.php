<?php
// admin/products/view.php — Product Detail View
require_once __DIR__ . '/../includes/auth-check.php';
require_once __DIR__ . '/../config/products-helper.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    $_SESSION['flash_message'] = 'Invalid product ID.';
    $_SESSION['flash_type']    = 'error';
    header('Location: ' . BASE_URL . 'products/');
    exit;
}

$product = getProductById($id);
if (!$product) {
    $_SESSION['flash_message'] = 'Product not found.';
    $_SESSION['flash_type']    = 'error';
    header('Location: ' . BASE_URL . 'products/');
    exit;
}

$page_title  = 'CloudCush Admin — ' . htmlspecialchars($product['title']);
$active_page = 'products';

$badge        = getStatusBadge($product['status']);
$stockClass   = $product['stock'] === 0 ? 'out' : ($product['stock'] <= $product['low_stock_threshold'] ? 'low' : 'ok');
$displayPrice = !empty($product['sale_price']) ? $product['sale_price'] : $product['price'];
$hasDiscount  = !empty($product['sale_price']) && (float)$product['sale_price'] < (float)$product['price'];
$discountPct  = $hasDiscount ? round((1 - (float)$product['sale_price'] / (float)$product['price']) * 100) : 0;
$margin       = (!empty($product['cost_price']) && $product['cost_price'] > 0)
    ? round((($displayPrice - $product['cost_price']) / $displayPrice) * 100, 1)
    : null;

$marginClass = '';
if ($margin !== null) {
    $marginClass = $margin >= 30 ? 'text-success-custom' : ($margin >= 10 ? 'text-warning-custom' : 'text-danger-custom');
}

// Build full gallery array (primary first, then extras)
$galleryAll = [];
if ($product['image_url']) $galleryAll[] = $product['image_url'];
if (!empty($product['gallery_images'])) {
    foreach ($product['gallery_images'] as $g) {
        if ($g && $g !== $product['image_url']) $galleryAll[] = $g;
    }
}

$tags = array_filter(array_map('trim', explode(',', $product['tags'] ?? '')));

include __DIR__ . '/../includes/header.php';
?>

<!-- Handler URLs for JS -->
<input type="hidden" id="deleteHandlerUrl"  value="<?= BASE_URL ?>products/index.php">
<input type="hidden" id="statusHandlerUrl" value="<?= BASE_URL ?>products/index.php">

<div id="wrapper">
    <?php include __DIR__ . '/../includes/sidebar.php'; ?>

    <div id="page-content-wrapper">
        <?php include __DIR__ . '/../includes/navbar.php'; ?>

        <div class="container-fluid px-0 py-2">

            <!-- Page Header -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
                <div class="min-width-0 flex-1">
                    <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                        <a href="<?= BASE_URL ?>products/" class="btn-action btn-back-square" title="Back to Products">
                            <i data-lucide="arrow-left" class="icon-sm"></i>
                        </a>
                        <h1 class="h4 fw-bold mb-0 page-heading text-ellipsis-100">
                            <?= htmlspecialchars($product['title']) ?>
                        </h1>
                        <?php if ($product['is_featured']): ?>
                            <span class="badge-status success featured-badge flex-shrink-0">
                                <i data-lucide="star" class="icon-xxs"></i>&nbsp;Featured
                            </span>
                        <?php endif; ?>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap pl-2-25">
                        <span class="badge-status <?= $badge['class'] ?>"><?= $badge['label'] ?></span>
                        <span class="tech-data fs-0-73 text-muted-custom">
                            SKU: <?= htmlspecialchars($product['sku']) ?>
                        </span>
                        <span class="color-divider">&middot;</span>
                        <span class="fs-0-73 text-muted-custom">
                            Added <?= date('M j, Y', strtotime($product['created_at'])) ?>
                        </span>
                        <?php if (!empty($product['category'])): ?>
                            <span class="color-divider">&middot;</span>
                            <span class="fs-0-73 text-muted-custom"><?= htmlspecialchars($product['category']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="d-flex gap-2 flex-shrink-0">
                    <a href="<?= BASE_URL ?>products/edit.php?id=<?= $id ?>"
                       class="btn btn-premium-primary btn-sm d-flex align-items-center gap-2">
                        <i data-lucide="pencil" class="icon-md"></i>
                        <span>Edit Product</span>
                    </a>
                    <button type="button"
                            class="btn btn-premium-secondary btn-sm d-flex align-items-center gap-2 btn-delete-product text-danger-custom"
                            data-id="<?= $product['id'] ?>"
                            data-name="<?= htmlspecialchars($product['title']) ?>">
                        <i data-lucide="trash-2" class="icon-md"></i>
                        <span class="d-none d-sm-inline">Delete</span>
                    </button>
                </div>
            </div>            <!-- Unified Layout: Gallery & Descriptions (Left), Metadata & Actions (Right) -->
            <div class="row g-4">

                <!-- ── LEFT COLUMN: Gallery & Main Content ── -->
                <div class="col-12 col-xl-8">

                    <!-- Gallery Card -->
                    <div class="card-premium p-0-9 mb-3">
                        <span class="form-section-label">Product Gallery</span>
                        <?php if (!empty($galleryAll)): ?>
                            <div class="detail-gallery-main">
                                <img src="<?= htmlspecialchars(resolveAdminAssetUrl($galleryAll[0])) ?>"
                                     alt="<?= htmlspecialchars($product['title']) ?>"
                                     id="detailMainImg" loading="eager">
                            </div>
                            <?php if (count($galleryAll) > 1): ?>
                                <div class="detail-gallery-thumbs">
                                    <?php foreach ($galleryAll as $i => $img): ?>
                                         <div class="detail-gallery-thumb <?= $i === 0 ? 'active' : '' ?>"
                                              data-src="<?= htmlspecialchars(resolveAdminAssetUrl($img)) ?>">
                                             <img src="<?= htmlspecialchars(resolveAdminAssetUrl($img)) ?>"
                                                  alt="Gallery image <?= $i + 1 ?>"
                                                  loading="lazy">
                                         </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="detail-gallery-main d-flex align-items-center justify-content-center empty-state-upload">
                                <div class="text-center p-2-rem">
                                    <i data-lucide="image" class="detail-no-image-icon"></i>
                                    <span class="fs-0-8 text-muted-custom font-ui">No image uploaded</span>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Short Description -->
                    <?php if (!empty($product['short_description'])): ?>
                        <div class="card-premium mb-3">
                            <span class="form-section-label">Short Description</span>
                            <p class="mb-0 fs-0-88 color-desc lh-1-7">
                                <?= htmlspecialchars($product['short_description']) ?>
                            </p>
                        </div>
                    <?php endif; ?>

                    <!-- Full Description -->
                    <div class="card-premium mb-3">
                        <div class="d-flex align-items-center justify-content-between mb-3 border-bottom-divider-pb-0-55">
                            <span class="form-section-label mb-0">Full Description</span>
                            <a href="<?= BASE_URL ?>products/edit.php?id=<?= $id ?>" class="edit-link-custom">
                                Edit &rarr;
                            </a>
                        </div>
                        <?php if (!empty($product['description'])): ?>
                            <div class="product-description-body">
                                <?= $product['description'] ?>
                            </div>
                        <?php else: ?>
                            <div class="d-flex align-items-center gap-2 py-3 text-muted-custom fs-0-82 fst-italic">
                                <i data-lucide="file-text" class="icon-xl opacity-35 flex-shrink-0"></i>
                                No description provided yet.
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Detail Description Images -->
                    <?php if (!empty($product['detail_images'])): ?>
                        <div class="card-premium mb-3">
                            <span class="form-section-label">Detail Description Images</span>
                            <div class="d-flex flex-wrap gap-2 mt-2">
                                <?php foreach ($product['detail_images'] as $det): ?>
                                    <div class="gallery-thumb-item" style="width:100px;height:auto;border-radius:6px;overflow:hidden;border:1px solid #e2e8f0;padding:4px;">
                                         <img src="<?= htmlspecialchars(resolveAdminAssetUrl($det)) ?>" alt="Detail image" style="width:100%;height:auto;display:block;">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Variants -->
                    <?php if (!empty($product['variants'])): ?>
                        <div class="card-premium mb-3">
                            <span class="form-section-label">
                                Variants
                                <span class="badge-variant-count"><?= count($product['variants']) ?></span>
                            </span>
                            <div class="variants-list">
                                <?php foreach ($product['variants'] as $v): ?>
                                    <div class="variant-pill">
                                        <div class="vp-label">
                                            <span class="variant-pill-label-prefix">
                                                <?= htmlspecialchars($v['variant_name']) ?>:
                                            </span>
                                            <?= htmlspecialchars($v['variant_value']) ?>
                                            <?php if ($v['is_default']): ?>
                                                <span class="badge-default-variant">Default</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="vp-meta">
                                            <?php if ($v['price_modifier'] != 0): ?>
                                                <span class="vp-price <?= $v['price_modifier'] > 0 ? 'text-success-custom' : 'text-danger-custom' ?>">
                                                    <?= $v['price_modifier'] > 0 ? '+' : '' ?>₹<?= number_format((float)$v['price_modifier'], 2) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="vp-price text-muted-custom">base price</span>
                                            <?php endif; ?>
                                            <span class="vp-stock">
                                                <i data-lucide="package" class="icon-xs"></i>
                                                <?= number_format((int)$v['stock']) ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                </div><!-- /left column -->

                <!-- ── RIGHT COLUMN: Metadata & Actions ── -->
                <div class="col-12 col-xl-4">
                    <div class="detail-info-sidebar">

                        <!-- Pricing card -->
                        <div class="card-premium">
                            <span class="form-section-label">Pricing</span>
                            <div class="d-flex align-items-end gap-3 mb-3 flex-wrap">
                                <div>
                                    <div class="detail-field-label mb-0-3-rem">
                                        <?= $hasDiscount ? 'Sale Price' : 'Price' ?>
                                    </div>
                                    <div class="detail-price-main-custom">
                                        ₹<?= number_format((float)$displayPrice, 2) ?>
                                    </div>
                                </div>
                                <?php if ($hasDiscount): ?>
                                    <div class="pb-0-2-rem">
                                        <span class="detail-price-old">
                                            ₹<?= number_format((float)$product['price'], 2) ?>
                                        </span>
                                    </div>
                                    <span class="badge-status danger mb-0-15-rem">&minus;<?= $discountPct ?>%</span>
                                <?php endif; ?>
                            </div>

                            <div class="detail-info-grid">
                                <div class="detail-field">
                                    <div class="detail-field-label">Regular Price</div>
                                    <div class="detail-field-value mono">₹<?= number_format((float)$product['price'], 2) ?></div>
                                </div>
                                <?php if (!empty($product['sale_price'])): ?>
                                    <div class="detail-field">
                                        <div class="detail-field-label">Sale Price</div>
                                        <div class="detail-field-value mono text-success-custom">₹<?= number_format((float)$product['sale_price'], 2) ?></div>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($product['cost_price'])): ?>
                                    <div class="detail-field">
                                        <div class="detail-field-label">Cost Price</div>
                                        <div class="detail-field-value mono">₹<?= number_format((float)$product['cost_price'], 2) ?></div>
                                    </div>
                                <?php endif; ?>
                                <?php if ($margin !== null): ?>
                                    <div class="detail-field">
                                        <div class="detail-field-label">Gross Margin</div>
                                        <div class="detail-field-value mono <?= $marginClass ?>">
                                            <?= $margin ?>%
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Inventory card -->
                        <div class="card-premium">
                            <span class="form-section-label">Inventory</span>
                            <div class="detail-info-grid">
                                <div class="detail-field">
                                    <div class="detail-field-label">Stock</div>
                                    <div class="detail-field-value">
                                        <span class="stock-indicator <?= $stockClass ?> fs-0-88">
                                            <?php if ($stockClass === 'out'): ?>
                                                <i data-lucide="alert-circle" class="icon-sm"></i>
                                            <?php elseif ($stockClass === 'low'): ?>
                                                <i data-lucide="alert-triangle" class="icon-sm"></i>
                                            <?php else: ?>
                                                <i data-lucide="check-circle" class="icon-sm"></i>
                                            <?php endif; ?>
                                            <?= number_format($product['stock']) ?> units
                                        </span>
                                    </div>
                                </div>
                                <div class="detail-field">
                                    <div class="detail-field-label">Low Stock Alert</div>
                                    <div class="detail-field-value mono"><?= (int)$product['low_stock_threshold'] ?> units</div>
                                </div>
                                <?php if (!empty($product['weight'])): ?>
                                    <div class="detail-field">
                                        <div class="detail-field-label">Weight</div>
                                        <div class="detail-field-value mono"><?= number_format((float)$product['weight'], 2) ?>g</div>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($product['dimensions'])): ?>
                                    <div class="detail-field">
                                        <div class="detail-field-label">Dimensions</div>
                                        <div class="detail-field-value mono"><?= htmlspecialchars($product['dimensions']) ?> cm</div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Classification card -->
                        <div class="card-premium">
                            <span class="form-section-label">Classification</span>
                            <div class="detail-info-grid">
                                <div class="detail-field">
                                    <div class="detail-field-label">Category</div>
                                    <div class="detail-field-value"><?= htmlspecialchars($product['category'] ?? '—') ?></div>
                                </div>
                                <div class="detail-field">
                                    <div class="detail-field-label">Status</div>
                                    <div class="detail-field-value">
                                        <span class="badge-status <?= $badge['class'] ?>"><?= $badge['label'] ?></span>
                                    </div>
                                </div>
                                <div class="detail-field">
                                    <div class="detail-field-label">SKU</div>
                                    <div class="detail-field-value mono fs-0-78" title="<?= htmlspecialchars($product['sku']) ?>">
                                        <?= htmlspecialchars($product['sku']) ?>
                                    </div>
                                </div>
                                <div class="detail-field">
                                    <div class="detail-field-label">URL Slug</div>
                                    <div class="detail-field-value mono fs-0-73" title="/<?= htmlspecialchars($product['slug']) ?>">
                                        /<?= htmlspecialchars($product['slug']) ?>
                                    </div>
                                </div>
                            </div>
                            <?php if (!empty($tags)): ?>
                                <div class="mt-2 pt-2 border-top-divider">
                                    <div class="detail-field-label mb-1">Tags</div>
                                    <div class="tag-pills-display">
                                        <?php foreach ($tags as $tag): ?>
                                            <span class="tag-pill-item"><?= htmlspecialchars($tag) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- SEO Preview -->
                        <?php if (!empty($product['meta_title']) || !empty($product['meta_description'])): ?>
                            <div class="card-premium">
                                <span class="form-section-label">SEO &amp; Metadata</span>

                                <?php if (!empty($product['meta_title'])): ?>
                                    <div class="detail-field">
                                        <div class="detail-field-label">Meta Title</div>
                                        <div class="detail-field-value fs-0-82 white-space-normal">
                                            <?= htmlspecialchars($product['meta_title']) ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($product['meta_description'])): ?>
                                    <div class="detail-field">
                                        <div class="detail-field-label">Meta Description</div>
                                        <div class="fs-0-78 text-muted-custom lh-1-55 white-space-normal">
                                            <?= htmlspecialchars($product['meta_description']) ?>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <!-- Google snippet preview -->
                                <div class="mt-3 p-3 search-preview-wrap">
                                    <div class="detail-field-label mb-2">Search Preview</div>
                                    <div class="search-preview-title">
                                        <?= htmlspecialchars($product['meta_title'] ?: $product['title']) ?>
                                    </div>
                                    <div class="search-preview-url">
                                        cloudcush.com &rsaquo; products &rsaquo; <?= htmlspecialchars($product['slug']) ?>
                                    </div>
                                    <div class="search-preview-desc">
                                        <?= htmlspecialchars($product['meta_description'] ?: ($product['short_description'] ?: 'No meta description set.')) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Activity Card -->
                        <div class="card-premium">
                            <span class="form-section-label">Activity</span>

                            <div class="activity-row">
                                <div class="activity-icon activity-icon-bg-1">
                                    <i data-lucide="plus-circle" class="icon-md color-primary-custom"></i>
                                </div>
                                <div>
                                    <div class="activity-title">Created</div>
                                    <div class="activity-time"><?= date('M j, Y · g:i A', strtotime($product['created_at'])) ?></div>
                                </div>
                            </div>

                            <?php if (!empty($product['updated_at']) && $product['updated_at'] !== $product['created_at']): ?>
                                <div class="activity-row">
                                    <div class="activity-icon activity-icon-bg-2">
                                        <i data-lucide="edit-3" class="icon-md color-cyan-custom"></i>
                                    </div>
                                    <div>
                                        <div class="activity-title">Last Updated</div>
                                        <div class="activity-time"><?= date('M j, Y · g:i A', strtotime($product['updated_at'])) ?></div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($product['creator_name'])): ?>
                                <div class="activity-row">
                                    <div class="activity-icon activity-icon-bg-3">
                                        <i data-lucide="user" class="icon-md text-success-custom"></i>
                                    </div>
                                    <div>
                                        <div class="activity-title">Created By</div>
                                        <div class="activity-time font-heading fs-0-72">
                                            <?= htmlspecialchars($product['creator_name']) ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Quick Actions Card -->
                        <div class="card-premium">
                            <span class="form-section-label">Quick Actions</span>
                            <div class="d-flex flex-column gap-2">

                                <a href="<?= BASE_URL ?>products/edit.php?id=<?= $id ?>"
                                   class="btn btn-premium-primary w-100 d-flex align-items-center justify-content-center gap-2">
                                    <i data-lucide="pencil" class="icon-md"></i>
                                    Edit Product
                                </a>

                                <!-- Primary status toggle -->
                                <form action="<?= BASE_URL ?>products/index.php"
                                      method="POST" class="w-100">
                                    <input type="hidden" name="id" value="<?= $id ?>">
                                    <?php if ($product['status'] === 'active'): ?>
                                        <input type="hidden" name="status" value="draft">
                                        <button type="submit" class="btn btn-premium-secondary w-100 d-flex align-items-center justify-content-center gap-2">
                                            <i data-lucide="eye-off" class="icon-md"></i>
                                            Set to Draft
                                        </button>
                                    <?php elseif ($product['status'] === 'draft'): ?>
                                        <input type="hidden" name="status" value="active">
                                        <button type="submit" class="btn btn-premium-secondary w-100 d-flex align-items-center justify-content-center gap-2">
                                            <i data-lucide="globe" class="icon-md"></i>
                                            Publish Now
                                        </button>
                                    <?php elseif ($product['status'] === 'archived'): ?>
                                        <input type="hidden" name="status" value="active">
                                        <button type="submit" class="btn btn-premium-secondary w-100 d-flex align-items-center justify-content-center gap-2">
                                            <i data-lucide="refresh-cw" class="icon-md"></i>
                                            Restore Active
                                        </button>
                                    <?php elseif ($product['status'] === 'out_of_stock'): ?>
                                        <input type="hidden" name="status" value="active">
                                        <button type="submit" class="btn btn-premium-secondary w-100 d-flex align-items-center justify-content-center gap-2">
                                            <i data-lucide="package-check" class="icon-md"></i>
                                            Mark as Active
                                        </button>
                                    <?php endif; ?>
                                </form>

                                <!-- Archive toggle (if not already archived) -->
                                <?php if ($product['status'] !== 'archived'): ?>
                                    <form action="<?= BASE_URL ?>products/index.php"
                                          method="POST" class="w-100">
                                        <input type="hidden" name="id" value="<?= $id ?>">
                                        <input type="hidden" name="status" value="archived">
                                        <button type="submit"
                                                class="btn btn-premium-secondary w-100 d-flex align-items-center justify-content-center gap-2 text-muted-custom">
                                            <i data-lucide="archive" class="icon-md"></i>
                                            Archive Product
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <button type="button"
                                        class="btn btn-premium-secondary w-100 d-flex align-items-center justify-content-center gap-2 btn-delete-product btn-delete-product-custom"
                                        data-id="<?= $product['id'] ?>"
                                        data-name="<?= htmlspecialchars($product['title']) ?>">
                                    <i data-lucide="trash-2" class="icon-md"></i>
                                    Delete Product
                                </button>

                                <a href="<?= BASE_URL ?>products/"
                                   class="btn btn-premium-secondary w-100 d-flex align-items-center justify-content-center gap-2">
                                    <i data-lucide="arrow-left" class="icon-md"></i>
                                    Back to Catalog
                                </a>

                            </div>
                        </div>

                    </div>
                </div><!-- /right column -->
            </div><!-- /row -->

        </div><!-- /container-fluid -->
    </div><!-- /page-content-wrapper -->
</div><!-- /wrapper -->

<?php include __DIR__ . '/../includes/footer.php'; ?>
