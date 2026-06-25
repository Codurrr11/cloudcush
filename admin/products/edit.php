<?php
// admin/products/edit.php — Edit Product Form
require_once __DIR__ . '/../includes/auth-check.php';
require_once __DIR__ . '/../config/products-helper.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    $_SESSION['flash_message'] = 'Invalid product ID.';
    $_SESSION['flash_type']    = 'error';
    header('Location: ' . BASE_URL . 'products/');
    exit;
}

$db = getDBConnection();
$checkStmt = $db->prepare("SELECT * FROM products WHERE id = :id LIMIT 1");
$checkStmt->execute(['id' => $id]);
$existing = $checkStmt->fetch();

if (!$existing) {
    $_SESSION['flash_message'] = 'Product not found.';
    $_SESSION['flash_type']    = 'error';
    header('Location: ' . BASE_URL . 'products/');
    exit;
}

// Handle POST Request for Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- Collect & sanitize ---
    $title             = trim(strip_tags($_POST['title'] ?? ''));
    $category          = trim(strip_tags($_POST['category'] ?? ''));
    $customCategory    = trim(strip_tags($_POST['custom_category'] ?? ''));
    $shortDescription  = trim(strip_tags($_POST['short_description'] ?? ''));
    $description       = '';
    $price             = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
    $salePrice         = filter_input(INPUT_POST, 'sale_price', FILTER_VALIDATE_FLOAT);
    $costPrice         = filter_input(INPUT_POST, 'cost_price', FILTER_VALIDATE_FLOAT);
    $stock             = filter_input(INPUT_POST, 'stock', FILTER_VALIDATE_INT);
    $lowStockThreshold = filter_input(INPUT_POST, 'low_stock_threshold', FILTER_VALIDATE_INT) ?: 5;
    $status            = in_array($_POST['status'] ?? '', ['active', 'draft', 'out_of_stock', 'archived']) ? $_POST['status'] : 'draft';
    $weight            = filter_input(INPUT_POST, 'weight', FILTER_VALIDATE_FLOAT);
    $dimensions        = trim(strip_tags($_POST['dimensions'] ?? ''));
    $tags              = trim(strip_tags($_POST['tags'] ?? ''));
    $isFeatured        = isset($_POST['is_featured']) ? 1 : 0;
    $metaTitle         = trim(strip_tags($_POST['meta_title'] ?? ''));
    $metaDescription   = trim(strip_tags($_POST['meta_description'] ?? ''));
    $manualSku         = trim(strip_tags($_POST['sku'] ?? ''));

    if ($category === '__custom__' && !empty($customCategory)) {
        $category = $customCategory;
    }
    if (empty($category)) $category = 'Uncategorized';

    // Validation
    $errors = [];
    if (empty($title))                   $errors[] = 'Product title is required.';
    if ($price === false || $price < 0)  $errors[] = 'A valid price is required.';
    if ($stock === false || $stock < 0)  $errors[] = 'Stock must be a non-negative number.';

    if (!empty($errors)) {
        $_SESSION['flash_message'] = implode(' ', $errors);
        $_SESSION['flash_type'] = 'error';
    } else {
        try {
            // Regenerate slug only if title changed
            $slug = ($title !== $existing['title']) ? generateSlug($title, $id) : $existing['slug'];

            // SKU handling
            $sku = !empty($manualSku) ? strtoupper($manualSku) : $existing['sku'];
            if ($sku !== $existing['sku']) {
                $skuCheck = $db->prepare("SELECT id FROM products WHERE sku = :sku AND id != :id LIMIT 1");
                $skuCheck->execute(['sku' => $sku, 'id' => $id]);
                if ($skuCheck->fetch()) {
                    $_SESSION['flash_message'] = 'SKU already exists. Please use a unique SKU.';
                    $_SESSION['flash_type'] = 'error';
                    header('Location: ' . BASE_URL . "products/edit.php?id=$id");
                    exit;
                }
            }

            // Image upload — keep existing if no new upload
            $imageUrl = $existing['image_url'];
            if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $imageUrl = handleImageUpload($_FILES['image'], 'prod');
            }

            // Gallery — handle reordering / deletion of existing and new uploads
            $uploadedGallery = [];
            if (!empty($_FILES['gallery']['name'][0])) {
                foreach ($_FILES['gallery']['name'] as $index => $name) {
                    if (empty($name) || $_FILES['gallery']['error'][$index] !== UPLOAD_ERR_OK) continue;
                    $galleryFile = [
                        'name'     => $_FILES['gallery']['name'][$index],
                        'type'     => $_FILES['gallery']['type'][$index],
                        'tmp_name' => $_FILES['gallery']['tmp_name'][$index],
                        'error'    => $_FILES['gallery']['error'][$index],
                        'size'     => $_FILES['gallery']['size'][$index],
                    ];
                    $uploadedGallery[] = handleImageUpload($galleryFile, 'gal');
                }
            }

            $galleryImages = [];
            $galOrder = $_POST['gallery_images_order'] ?? [];
            foreach ($galOrder as $item) {
                if (str_starts_with($item, 'existing:')) {
                    $galleryImages[] = substr($item, 9);
                } elseif (str_starts_with($item, 'new:')) {
                    $idx = (int)substr($item, 4);
                    if (isset($uploadedGallery[$idx]) && $uploadedGallery[$idx] !== null) {
                        $galleryImages[] = $uploadedGallery[$idx];
                    }
                }
            }
            if (empty($galOrder) && !empty($uploadedGallery)) {
                $galleryImages = $uploadedGallery;
            }

            // Detail description images — handle reordering of existing and new uploads
            $uploadedUrls = [];
            if (!empty($_FILES['detail_images']['name'][0])) {
                foreach ($_FILES['detail_images']['name'] as $index => $name) {
                    if (empty($name) || $_FILES['detail_images']['error'][$index] !== UPLOAD_ERR_OK) continue;
                    $detailFile = [
                        'name'     => $_FILES['detail_images']['name'][$index],
                        'type'     => $_FILES['detail_images']['type'][$index],
                        'tmp_name' => $_FILES['detail_images']['tmp_name'][$index],
                        'error'    => $_FILES['detail_images']['error'][$index],
                        'size'     => $_FILES['detail_images']['size'][$index],
                    ];
                    $uploadedUrls[] = handleImageUpload($detailFile, 'det');
                }
            }

            $detailImages = [];
            $order = $_POST['detail_images_order'] ?? [];
            foreach ($order as $item) {
                if (str_starts_with($item, 'existing:')) {
                    $detailImages[] = substr($item, 9);
                } elseif (str_starts_with($item, 'new:')) {
                    $idx = (int)substr($item, 4);
                    if (isset($uploadedUrls[$idx]) && $uploadedUrls[$idx] !== null) {
                        $detailImages[] = $uploadedUrls[$idx];
                    }
                }
            }
            // Fallback: if order is empty but files uploaded
            if (empty($order) && !empty($uploadedUrls)) {
                $detailImages = $uploadedUrls;
            }

            $stmt = $db->prepare("
                UPDATE products SET
                    title = :title, slug = :slug, sku = :sku, category = :category,
                    description = :description, short_description = :short_description,
                    price = :price, sale_price = :sale_price, cost_price = :cost_price,
                    stock = :stock, low_stock_threshold = :low_stock_threshold,
                    status = :status, image_url = :image_url, gallery_images = :gallery_images,
                    detail_images = :detail_images,
                    tags = :tags, weight = :weight, dimensions = :dimensions,
                    is_featured = :is_featured, meta_title = :meta_title,
                    meta_description = :meta_description
                WHERE id = :id
            ");
            $stmt->execute([
                ':id'                => $id,
                ':title'             => $title,
                ':slug'              => $slug,
                ':sku'               => $sku,
                ':category'          => $category,
                ':description'       => $description,
                ':short_description' => $shortDescription,
                ':price'             => $price,
                ':sale_price'        => $salePrice ?: null,
                ':cost_price'        => $costPrice ?: null,
                ':stock'             => $stock,
                ':low_stock_threshold' => $lowStockThreshold,
                ':status'            => $status,
                ':image_url'         => $imageUrl,
                ':gallery_images'    => !empty($galleryImages) ? json_encode($galleryImages) : null,
                ':detail_images'     => !empty($detailImages) ? json_encode($detailImages) : null,
                ':tags'              => $tags ?: null,
                ':weight'            => $weight ?: null,
                ':dimensions'        => $dimensions ?: null,
                ':is_featured'       => $isFeatured,
                ':meta_title'        => $metaTitle ?: null,
                ':meta_description'  => $metaDescription ?: null,
            ]);

            // Sync variants: delete all then reinsert
            $db->prepare("DELETE FROM product_variants WHERE product_id = :id")->execute(['id' => $id]);

            $variantNames  = $_POST['variant_name']  ?? [];
            $variantValues = $_POST['variant_value'] ?? [];
            $variantPrices = $_POST['variant_price'] ?? [];
            $variantStocks = $_POST['variant_stock'] ?? [];

            if (!empty($variantNames)) {
                $varStmt = $db->prepare("
                    INSERT INTO product_variants (product_id, variant_name, variant_value, price_modifier, stock, is_default)
                    VALUES (:product_id, :variant_name, :variant_value, :price_modifier, :stock, :is_default)
                ");
                foreach ($variantNames as $i => $varName) {
                    $varName  = trim(strip_tags($varName));
                    $varValue = trim(strip_tags($variantValues[$i] ?? ''));
                    if (empty($varName) || empty($varValue)) continue;
                    $varStmt->execute([
                        ':product_id'     => $id,
                        ':variant_name'   => $varName,
                        ':variant_value'  => $varValue,
                        ':price_modifier' => filter_var($variantPrices[$i] ?? 0, FILTER_VALIDATE_FLOAT) ?: 0,
                        ':stock'          => filter_var($variantStocks[$i] ?? 0, FILTER_VALIDATE_INT) ?: 0,
                        ':is_default'     => ($i === 0) ? 1 : 0,
                    ]);
                }
            }

            $_SESSION['flash_message'] = "Product \"$title\" updated successfully.";
            $_SESSION['flash_type'] = 'success';
            header('Location: ' . BASE_URL . 'products/');
            exit;

        } catch (\InvalidArgumentException $e) {
            $_SESSION['flash_message'] = $e->getMessage();
            $_SESSION['flash_type'] = 'error';
        } catch (\PDOException $e) {
            error_log('Product update error: ' . $e->getMessage());
            $_SESSION['flash_message'] = 'Database error while updating product.';
            $_SESSION['flash_type'] = 'error';
        }
    }
}

// Fetch product for rendering
$product = getProductById($id);
if (!$product) {
    $_SESSION['flash_message'] = 'Product not found.';
    $_SESSION['flash_type']    = 'error';
    header('Location: ' . BASE_URL . 'products/');
    exit;
}

$page_title  = 'Edit: ' . htmlspecialchars($product['title']) . ' — CloudCush Admin';
$active_page = 'products';

$categories        = getProductCategories();
$defaultCategories = ['Everyday Comfort', 'Overnight Protection', 'Rash-Free Care', 'Active Baby Fit', 'Sensitive Skin', 'Newborn'];
$allCategories     = array_unique(array_merge($defaultCategories, $categories));
sort($allCategories);

include __DIR__ . '/../includes/header.php';
?>

<input type="hidden" id="deleteHandlerUrl" value="<?= BASE_URL ?>products/index.php">

<div id="wrapper">
    <?php include __DIR__ . '/../includes/sidebar.php'; ?>

    <div id="page-content-wrapper">
        <?php include __DIR__ . '/../includes/navbar.php'; ?>

        <div class="container-fluid px-0 py-2">

            <!-- Page Header -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <a href="<?= BASE_URL ?>products/" class="btn-action btn-back-square" title="Back to Products">
                            <i data-lucide="arrow-left" class="icon-sm"></i>
                        </a>
                        <h1 class="h4 fw-bold mb-0 page-heading">Edit Product</h1>
                    </div>
                    <p class="text-secondary mb-0 fs-0-82 pl-2-25">
                        Editing: <strong class="text-main"><?= htmlspecialchars($product['title']) ?></strong>
                        &mdash; <span class="tech-data fs-0-79 text-muted-custom">SKU: <?= htmlspecialchars($product['sku']) ?></span>
                    </p>
                </div>
                <div class="d-flex gap-2 flex-shrink-0">
                    <a href="<?= BASE_URL ?>products/view.php?id=<?= $id ?>"
                       class="btn btn-premium-secondary btn-sm d-flex align-items-center gap-2">
                        <i data-lucide="eye" class="icon-md"></i>
                        <span>View</span>
                    </a>
                    <button type="button"
                            class="btn btn-premium-secondary btn-sm d-flex align-items-center gap-2 btn-delete-product text-danger-custom"
                            data-id="<?= $product['id'] ?>"
                            data-name="<?= htmlspecialchars($product['title']) ?>">
                        <i data-lucide="trash-2" class="icon-md"></i>
                        <span>Delete</span>
                    </button>
                </div>
            </div>

            <form action=""
                  method="POST"
                  enctype="multipart/form-data"
                  id="editProductForm"
                  novalidate>

                <input type="hidden" name="id" value="<?= $id ?>">

                <div class="row g-4">

                    <!-- ── LEFT COLUMN ── -->
                    <div class="col-12 col-xl-8">

                        <!-- Basic Info -->
                        <div class="card-premium mb-3">
                            <span class="form-section-label">Basic Information</span>

                            <div class="mb-3">
                                <label class="form-label-premium" for="productTitle">
                                    Product Title <span class="req">*</span>
                                </label>
                                <input type="text" id="productTitle" name="title"
                                       class="form-control-premium" required autocomplete="off"
                                       value="<?= htmlspecialchars($product['title']) ?>">
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label-premium" for="productSlug">
                                        URL Slug <span class="hint">(auto-updated on title change)</span>
                                    </label>
                                    <div class="slug-input-wrap">
                                        <input type="text" id="productSlug" name="slug"
                                               class="form-control-premium slug-field" readonly
                                               value="<?= htmlspecialchars($product['slug']) ?>"
                                               data-user-edited="true">
                                        <button type="button" class="slug-edit-btn" id="slugEditBtn" title="Edit slug manually">
                                            <i data-lucide="pencil" class="icon-xs"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label-premium" for="productSku">SKU</label>
                                    <input type="text" id="productSku" name="sku"
                                           class="form-control-premium" autocomplete="off"
                                           value="<?= htmlspecialchars($product['sku']) ?>">
                                </div>
                            </div>

                            <div class="mt-3">
                                <label class="form-label-premium">
                                    Short Description <span class="hint">(max 500 chars)</span>
                                </label>
                                <textarea name="short_description" class="form-control-premium"
                                          rows="2" maxlength="500"><?= htmlspecialchars($product['short_description'] ?? '') ?></textarea>
                            </div>
                        </div>

                        <!-- Description Images -->
                        <div class="card-premium mb-3">
                            <span class="form-section-label">Description Images (Centered Column)</span>
                            
                            <div id="detailImagesContainer" class="d-flex flex-wrap gap-3 mb-3">
                                <?php 
                                $detImages = $product['detail_images'] ?? [];
                                foreach ($detImages as $index => $imgUrl): 
                                    $resolvedUrl = resolveAdminAssetUrl($imgUrl);
                                ?>
                                    <div class="detail-image-item card p-2 bg-light" style="width: 140px; border: 1px solid var(--border); border-radius: 12px;" data-url="<?= htmlspecialchars($imgUrl) ?>">
                                        <img src="<?= htmlspecialchars($resolvedUrl) ?>" alt="Detail Image" class="img-thumbnail mb-2" style="height: 100px; width: 100%; object-fit: cover; border-radius: 8px;">
                                        <input type="hidden" name="detail_images_order[]" value="existing:<?= htmlspecialchars($imgUrl) ?>">
                                        <div class="d-flex justify-content-between align-items-center gap-1">
                                            <button type="button" class="btn btn-sm btn-outline-secondary move-prev-btn px-2 py-1" title="Move Left">
                                                <i data-lucide="arrow-left" style="width:12px;height:12px;"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-det-img-btn px-2 py-1" title="Remove">
                                                <i data-lucide="trash-2" style="width:12px;height:12px;"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary move-next-btn px-2 py-1" title="Move Right">
                                                <i data-lucide="arrow-right" style="width:12px;height:12px;"></i>
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="upload-zone upload-zone-compact">
                                <input type="file" id="detailImagesInput" name="detail_images[]"
                                       accept="image/jpeg,image/png,image/webp"
                                       multiple aria-label="Upload detail images">
                                <div class="upload-zone-icon upload-zone-icon-xxl">
                                    <i data-lucide="image" class="icon-xxl"></i>
                                </div>
                                <div class="upload-zone-title fs-0-8">Add new description images</div>
                                <div class="upload-zone-sub">Listed centered top-to-bottom on details page</div>
                            </div>
                            <div id="detailImagesPreviewStrip" class="gallery-preview-strip mt-2"></div>
                        </div>

                        <!-- Pricing -->
                        <div class="card-premium mb-3">
                            <span class="form-section-label">Pricing</span>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label-premium">Regular Price <span class="req">*</span></label>
                                    <div class="input-group-premium">
                                        <span class="input-prefix">₹</span>
                                        <input type="number" name="price" class="form-control-premium"
                                               required min="0" step="0.01"
                                               value="<?= htmlspecialchars($product['price']) ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label-premium">Sale Price <span class="hint">(optional)</span></label>
                                    <div class="input-group-premium">
                                        <span class="input-prefix">₹</span>
                                        <input type="number" name="sale_price" class="form-control-premium"
                                               min="0" step="0.01"
                                               value="<?= htmlspecialchars($product['sale_price'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label-premium">Cost Price <span class="hint">(internal)</span></label>
                                    <div class="input-group-premium">
                                        <span class="input-prefix">₹</span>
                                        <input type="number" name="cost_price" class="form-control-premium"
                                               min="0" step="0.01"
                                               value="<?= htmlspecialchars($product['cost_price'] ?? '') ?>">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Inventory -->
                        <div class="card-premium mb-3">
                            <span class="form-section-label">Inventory</span>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label-premium">Stock Quantity <span class="req">*</span></label>
                                    <input type="number" name="stock" class="form-control-premium"
                                           required min="0"
                                           value="<?= htmlspecialchars($product['stock']) ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label-premium">Low Stock Alert Threshold</label>
                                    <input type="number" name="low_stock_threshold" class="form-control-premium"
                                           min="0"
                                           value="<?= htmlspecialchars($product['low_stock_threshold'] ?? 5) ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label-premium">Weight <span class="hint">(grams)</span></label>
                                    <input type="number" name="weight" class="form-control-premium"
                                           min="0" step="0.01"
                                           value="<?= htmlspecialchars($product['weight'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label-premium">Dimensions <span class="hint">(L×W×H cm)</span></label>
                                    <input type="text" name="dimensions" class="form-control-premium"
                                           placeholder="e.g. 30×20×10"
                                           value="<?= htmlspecialchars($product['dimensions'] ?? '') ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Variants -->
                        <div class="card-premium mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="form-section-label mb-0">Variants <span class="hint hint-reset">(optional)</span></span>
                                <button type="button" id="addVariantBtn"
                                        class="btn btn-premium-secondary btn-sm d-flex align-items-center gap-2">
                                    <i data-lucide="plus" class="icon-sm"></i>
                                    <span>Add Variant</span>
                                </button>
                            </div>

                            <div class="variant-header-row">
                                <span class="form-label-premium mb-0">Name</span>
                                <span class="form-label-premium mb-0">Value</span>
                                <span class="form-label-premium mb-0">±Price</span>
                                <span class="form-label-premium mb-0">Stock</span>
                                <span></span>
                            </div>

                            <div id="variantsWrapper">
                                <?php if (!empty($product['variants'])): ?>
                                    <?php foreach ($product['variants'] as $variant): ?>
                                        <div class="variant-row">
                                            <div>
                                                <input type="text" name="variant_name[]" class="form-control-premium"
                                                       value="<?= htmlspecialchars($variant['variant_name']) ?>"
                                                       placeholder="e.g. Size">
                                            </div>
                                            <div>
                                                <input type="text" name="variant_value[]" class="form-control-premium"
                                                       value="<?= htmlspecialchars($variant['variant_value']) ?>"
                                                       placeholder="e.g. Medium (M)">
                                            </div>
                                            <div>
                                                <input type="number" name="variant_price[]" class="form-control-premium"
                                                       value="<?= htmlspecialchars($variant['price_modifier']) ?>"
                                                       placeholder="±₹0" step="0.01">
                                            </div>
                                            <div>
                                                <input type="number" name="variant_stock[]" class="form-control-premium"
                                                       value="<?= htmlspecialchars($variant['stock']) ?>"
                                                       placeholder="0" min="0">
                                            </div>
                                            <button type="button" class="btn-remove-variant" title="Remove variant">
                                                <i data-lucide="x" class="icon-md"></i>
                                            </button>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="variant-row">
                                        <div><input type="text" name="variant_name[]" class="form-control-premium" placeholder="e.g. Size"></div>
                                        <div><input type="text" name="variant_value[]" class="form-control-premium" placeholder="e.g. Medium (M)"></div>
                                        <div><input type="number" name="variant_price[]" class="form-control-premium" placeholder="±₹0" step="0.01"></div>
                                        <div><input type="number" name="variant_stock[]" class="form-control-premium" placeholder="0" min="0"></div>
                                        <button type="button" class="btn-remove-variant" title="Remove variant">
                                            <i data-lucide="x" class="icon-md"></i>
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <p class="mb-0 mt-2 fs-0-74 text-muted-custom">
                                Leave blank rows — they are ignored on save.
                            </p>
                        </div>

                        <!-- SEO -->
                        <div class="card-premium mb-3">
                            <span class="form-section-label">SEO &amp; Metadata</span>
                            <div class="mb-3">
                                <label class="form-label-premium">Meta Title <span class="hint">(defaults to product title)</span></label>
                                <input type="text" name="meta_title" class="form-control-premium"
                                       placeholder="Custom SEO title…"
                                       value="<?= htmlspecialchars($product['meta_title'] ?? '') ?>">
                            </div>
                            <div>
                                <label class="form-label-premium">Meta Description <span class="hint">(max 500 chars)</span></label>
                                <textarea name="meta_description" class="form-control-premium"
                                          rows="2" maxlength="500"><?= htmlspecialchars($product['meta_description'] ?? '') ?></textarea>
                            </div>
                        </div>

                    </div><!-- /left col -->

                    <!-- ── RIGHT COLUMN ── -->
                    <div class="col-12 col-xl-4">
                        <div class="form-sidebar-sticky-wrapper">

                            <!-- Publish -->
                            <div class="card-premium mb-3">
                                <span class="form-section-label">Publish Settings</span>

                                <div class="mb-3">
                                    <label class="form-label-premium">Status</label>
                                    <select name="status" class="form-select-premium">
                                        <option value="active"       <?= $product['status'] === 'active'       ? 'selected' : '' ?>>Active</option>
                                        <option value="draft"        <?= $product['status'] === 'draft'        ? 'selected' : '' ?>>Draft</option>
                                        <option value="out_of_stock" <?= $product['status'] === 'out_of_stock' ? 'selected' : '' ?>>Out of Stock</option>
                                        <option value="archived"     <?= $product['status'] === 'archived'     ? 'selected' : '' ?>>Archived</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label-premium" for="productCategory">Category</label>
                                    <select name="category" id="productCategory" class="form-select-premium">
                                        <?php foreach ($allCategories as $cat): ?>
                                            <option value="<?= htmlspecialchars($cat) ?>"
                                                <?= ($product['category'] ?? '') === $cat ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($cat) ?>
                                            </option>
                                        <?php endforeach; ?>
                                        <option value="__custom__">+ Add New Category…</option>
                                    </select>
                                </div>

                                <div id="customCategoryWrap" class="mb-3 d-none-init">
                                    <label class="form-label-premium">New Category Name</label>
                                    <input type="text" name="custom_category" class="form-control-premium"
                                           placeholder="Enter category name">
                                </div>

                                <div class="d-flex align-items-center gap-3 mb-3 featured-toggle-row">
                                    <label class="form-label-premium mb-0">Featured Product</label>
                                    <div class="form-check form-switch ms-auto mb-0">
                                        <input class="form-check-input" type="checkbox" name="is_featured"
                                               id="isFeatured" <?= $product['is_featured'] ? 'checked' : '' ?>>
                                    </div>
                                </div>

                                <div>
                                    <label class="form-label-premium" for="productTags">
                                        Tags <span class="hint">(comma-separated)</span>
                                    </label>
                                    <input type="text" id="productTags" name="tags"
                                           class="form-control-premium"
                                           placeholder="rash-free, overnight, newborn"
                                           value="<?= htmlspecialchars($product['tags'] ?? '') ?>">
                                    <div id="tagPillsDisplay" class="tag-pills-display"></div>
                                </div>
                            </div>

                            <!-- Primary Image -->
                            <div class="card-premium mb-3">
                                <span class="form-section-label">Primary Image</span>

                                <?php if (!empty($product['image_url'])): ?>
                                    <div class="current-image-preview mb-2">
                                        <img src="<?= htmlspecialchars(resolveAdminAssetUrl($product['image_url'])) ?>"
                                             alt="Current product image">
                                        <div class="current-image-label">Current image</div>
                                    </div>
                                <?php endif; ?>

                                <div class="upload-zone upload-zone-compact" id="uploadZone">
                                    <input type="file" id="productImageInput" name="image"
                                           accept="image/jpeg,image/png,image/webp"
                                           aria-label="Upload product image">
                                    <div id="uploadZoneBody">
                                        <div class="upload-zone-icon upload-zone-icon-xxl">
                                            <i data-lucide="image-plus" class="icon-xxl"></i>
                                        </div>
                                        <div class="upload-zone-title fs-0-8">
                                            <?= !empty($product['image_url']) ? 'Drop or click to replace' : 'Drop or click to browse' ?>
                                        </div>
                                        <div class="upload-zone-sub">JPG, PNG, WebP &bull; Max 5MB</div>
                                    </div>
                                    <div class="upload-zone-preview" id="uploadPreviewWrap">
                                        <img src="" id="uploadPreviewImg" alt="New image preview">
                                        <button type="button" class="preview-remove" id="previewRemoveBtn" title="Remove">
                                            <i data-lucide="x" class="icon-12"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Gallery -->
                            <div class="card-premium mb-3">
                                <span class="form-section-label">Gallery Images</span>

                                <div id="galleryContainer" class="d-flex flex-wrap gap-3 mb-3">
                                    <?php if (!empty($product['gallery_images'])): ?>
                                        <?php foreach ($product['gallery_images'] as $gImg): 
                                            $resolvedGImg = resolveAdminAssetUrl($gImg);
                                        ?>
                                            <div class="gallery-image-item card p-2 bg-light" style="width: 140px; border: 1px solid var(--border); border-radius: 12px;">
                                                <img src="<?= htmlspecialchars($resolvedGImg) ?>" alt="Gallery Image" class="img-thumbnail mb-2" style="height: 100px; width: 100%; object-fit: cover; border-radius: 8px;">
                                                <input type="hidden" name="gallery_images_order[]" value="existing:<?= htmlspecialchars($gImg) ?>">
                                                <div class="d-flex justify-content-between align-items-center gap-1">
                                                    <button type="button" class="btn btn-sm btn-outline-secondary move-prev-gal px-2 py-1" title="Move Left">
                                                        <i data-lucide="arrow-left" style="width:12px;height:12px;"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-danger remove-gal-img-btn px-2 py-1" title="Remove">
                                                        <i data-lucide="trash-2" style="width:12px;height:12px;"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-secondary move-next-gal px-2 py-1" title="Move Right">
                                                        <i data-lucide="arrow-right" style="width:12px;height:12px;"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>

                                <div class="upload-zone upload-zone-compact">
                                    <input type="file" id="galleryInput" name="gallery[]"
                                           accept="image/jpeg,image/png,image/webp"
                                           multiple aria-label="Upload gallery images">
                                    <div class="upload-zone-icon upload-zone-icon-xxl">
                                        <i data-lucide="images" class="icon-xxl"></i>
                                    </div>
                                    <div class="upload-zone-title fs-0-8">Add gallery images</div>
                                    <div class="upload-zone-sub">Multiple files supported</div>
                                </div>
                            </div>



                            <!-- Actions -->
                            <div class="card-premium">
                                <div class="d-flex flex-column gap-2">
                                    <button type="submit"
                                            class="btn btn-premium-primary w-100 d-flex align-items-center justify-content-center gap-2">
                                        <i data-lucide="save" class="icon-lg"></i>
                                        Save Changes
                                    </button>
                                    <a href="<?= BASE_URL ?>products/view.php?id=<?= $id ?>"
                                       class="btn btn-premium-secondary w-100 d-flex align-items-center justify-content-center gap-2">
                                        <i data-lucide="eye" class="icon-lg"></i>
                                        View Product
                                    </a>
                                    <a href="<?= BASE_URL ?>products/"
                                       class="btn btn-premium-secondary w-100 d-flex align-items-center justify-content-center gap-2">
                                        <i data-lucide="arrow-left" class="icon-lg"></i>
                                        Back to Catalog
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div><!-- /right col -->
                </div><!-- /row -->
            </form>

        </div><!-- /container-fluid -->
    </div><!-- /page-content-wrapper -->
</div><!-- /wrapper -->

<?php include __DIR__ . '/../includes/footer.php'; ?>
