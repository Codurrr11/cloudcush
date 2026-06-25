<?php
// admin/blogs/add.php — Add New Blog Article
require_once __DIR__ . '/../includes/auth-check.php';
require_once __DIR__ . '/../config/blogs-helper.php';

// Check if form is submitted via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title            = trim(strip_tags($_POST['title'] ?? ''));
    $category         = trim(strip_tags($_POST['category'] ?? ''));
    $customCategory   = trim(strip_tags($_POST['custom_category'] ?? ''));
    $shortDescription = trim(strip_tags($_POST['short_description'] ?? ''));
    $content          = $_POST['content'] ?? ''; // TinyMCE HTML
    $readTime         = filter_input(INPUT_POST, 'read_time', FILTER_VALIDATE_INT) ?: 5;
    
    // Status override via submit action buttons
    $statusOverride   = $_POST['status_override'] ?? '';
    if (in_array($statusOverride, ['active', 'draft'])) {
        $status = $statusOverride;
    } else {
        $status = in_array($_POST['status'] ?? '', ['active', 'draft']) ? $_POST['status'] : 'draft';
    }

    $manualSlug       = trim(strip_tags($_POST['slug'] ?? ''));

    // Resolve category
    if ($category === '__custom__' && !empty($customCategory)) {
        $category = $customCategory;
    }
    if (empty($category)) $category = 'Uncategorized';

    // Validation
    $errors = [];
    if (empty($title)) $errors[] = 'Article title is required.';
    if (empty($content)) $errors[] = 'Article content is required.';

    if (!empty($errors)) {
        $_SESSION['flash_message'] = implode(' ', $errors);
        $_SESSION['flash_type'] = 'error';
    } else {
        try {
            $db = getDBConnection();

            // Generate slug
            $slug = !empty($manualSlug) ? generateBlogSlug($manualSlug) : generateBlogSlug($title);

            // Handle primary image upload
            $thumbnailUrl = null;
            if (!empty($_FILES['thumbnail']['name'])) {
                $thumbnailUrl = handleBlogImageUpload($_FILES['thumbnail'], 'blog');
            }

            // Insert blog
            $stmt = $db->prepare("
                INSERT INTO blogs
                    (title, slug, category, read_time, short_description, content, thumbnail, status, author_id)
                VALUES
                    (:title, :slug, :category, :read_time, :short_description, :content, :thumbnail, :status, :author_id)
            ");
            $stmt->execute([
                ':title'             => $title,
                ':slug'              => $slug,
                ':category'          => $category,
                ':read_time'         => $readTime,
                ':short_description' => $shortDescription ?: null,
                ':content'           => $content,
                ':thumbnail'         => $thumbnailUrl,
                ':status'            => $status,
                ':author_id'         => $_SESSION['user_id'] ?? null,
            ]);

            $_SESSION['flash_message'] = "Article \"$title\" created successfully.";
            $_SESSION['flash_type'] = 'success';
            header('Location: ' . BASE_URL . 'blogs/');
            exit;
        } catch (\InvalidArgumentException $e) {
            $_SESSION['flash_message'] = $e->getMessage();
            $_SESSION['flash_type'] = 'error';
        } catch (\PDOException $e) {
            error_log('Blog create error: ' . $e->getMessage());
            $_SESSION['flash_message'] = 'Database error while creating article. Please try again.';
            $_SESSION['flash_type'] = 'error';
        }
    }
}

$page_title  = 'CloudCush Admin — Add Article';
$active_page = 'blogs';

$categories = getBlogCategories();
$defaultCategories = ['Pediatric Care', 'Sleep Guides', 'Care Tips', 'Product Insights', 'Philosophy', 'Newborn Routine'];

include __DIR__ . '/../includes/header.php';
?>

<div id="wrapper">
    <?php include __DIR__ . '/../includes/sidebar.php'; ?>

    <div id="page-content-wrapper">
        <?php include __DIR__ . '/../includes/navbar.php'; ?>

        <div class="container-fluid px-0 py-2">

            <!-- Page Header -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <a href="<?= BASE_URL ?>blogs/" class="btn-action btn-back-square" title="Back to Articles">
                            <i data-lucide="arrow-left" class="icon-sm"></i>
                        </a>
                        <h1 class="h4 fw-bold mb-0 page-heading">Add New Article</h1>
                    </div>
                    <p class="text-secondary mb-0 fs-0-82 pl-2-25">
                        Write a new post, customize URLs, upload thumbnails and publish to the CloudCush Journal.
                    </p>
                </div>
            </div>

            <form action=""
                  method="POST"
                  enctype="multipart/form-data"
                  id="addBlogForm"
                  novalidate>

                <div class="row g-4">

                    <!-- ── LEFT COLUMN: Main Content ── -->
                    <div class="col-12 col-xl-8">

                        <!-- Basic Information -->
                        <div class="card-premium mb-3">
                            <span class="form-section-label">Article Details</span>

                            <div class="mb-3">
                                <label class="form-label-premium" for="blogTitle">
                                    Article Title <span class="req">*</span>
                                </label>
                                <input type="text" id="blogTitle" name="title"
                                       class="form-control-premium" required autocomplete="off"
                                       placeholder="e.g. Cozy Sleep: Structuring Your Baby's Night Routine"
                                       value="<?= htmlspecialchars($_POST['title'] ?? '') ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label-premium" for="blogSlug">
                                    URL Slug <span class="hint">(auto-generated from title)</span>
                                </label>
                                <div class="slug-input-wrap">
                                    <input type="text" id="blogSlug" name="slug"
                                           class="form-control-premium slug-field" readonly
                                           placeholder="article-url-slug"
                                           value="<?= htmlspecialchars($_POST['slug'] ?? '') ?>">
                                    <button type="button" class="slug-edit-btn" id="blogSlugEditBtn" title="Edit slug manually">
                                        <i data-lucide="pencil" class="icon-xs"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label-premium" for="shortDescription">
                                    Short Description / Excerpt <span class="hint">(max 500 chars)</span>
                                </label>
                                <textarea id="shortDescription" name="short_description"
                                          class="form-control-premium" rows="3" maxlength="500"
                                          placeholder="A brief summary shown on the blog feed page…"><?= htmlspecialchars($_POST['short_description'] ?? '') ?></textarea>
                            </div>
                        </div>

                        <!-- Rich Editor Content -->
                        <div class="card-premium mb-3">
                            <span class="form-section-label">Article Body Content <span class="req">*</span></span>
                            <div class="tinymce-wrap">
                                <textarea id="blogContent" name="content"
                                          class="tinymce-editor"
                                          rows="14"><?= htmlspecialchars($_POST['content'] ?? '') ?></textarea>
                            </div>
                        </div>

                    </div><!-- /left column -->

                    <!-- ── RIGHT COLUMN: Sidebar Fields ── -->
                    <div class="col-12 col-xl-4">
                        <div class="form-sidebar-sticky-wrapper">

                            <!-- Publish Card -->
                            <div class="card-premium mb-3">
                                <span class="form-section-label">Publish Settings</span>

                                <div class="mb-3">
                                    <label class="form-label-premium">Status</label>
                                    <select name="status" class="form-select-premium">
                                        <option value="draft"  <?= ($_POST['status'] ?? 'draft') === 'draft' ? 'selected' : '' ?>>Draft</option>
                                        <option value="active" <?= ($_POST['status'] ?? '') === 'active' ? 'selected' : '' ?>>Published</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label-premium" for="blogCategory">Category</label>
                                    <select name="category" id="blogCategory" class="form-select-premium">
                                        <?php
                                        $allCategories = array_unique(array_merge($defaultCategories, $categories));
                                        sort($allCategories);
                                        foreach ($allCategories as $cat):
                                        ?>
                                            <option value="<?= htmlspecialchars($cat) ?>"
                                                <?= ($_POST['category'] ?? '') === $cat ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($cat) ?>
                                            </option>
                                        <?php endforeach; ?>
                                        <option value="__custom__">+ Add New Category…</option>
                                    </select>
                                </div>

                                <div id="blogCustomCategoryWrap" class="mb-3 d-none-init">
                                    <label class="form-label-premium">New Category Name</label>
                                    <input type="text" name="custom_category" class="form-control-premium"
                                           placeholder="Enter category name">
                                </div>

                                <div>
                                    <label class="form-label-premium" for="readTime">
                                        Read Time <span class="hint">(minutes)</span>
                                    </label>
                                    <input type="number" id="readTime" name="read_time"
                                           class="form-control-premium" min="1" max="60"
                                           placeholder="e.g. 5"
                                           value="<?= htmlspecialchars($_POST['read_time'] ?? '5') ?>">
                                </div>
                            </div>

                            <!-- Thumbnail Image -->
                            <div class="card-premium mb-3">
                                <span class="form-section-label">Thumbnail Image</span>
                                <div class="upload-zone" id="uploadZone">
                                    <input type="file" id="blogImageInput" name="thumbnail"
                                           accept="image/jpeg,image/png,image/webp"
                                           aria-label="Upload article thumbnail image">
                                    <div id="uploadZoneBody">
                                        <div class="upload-zone-icon">
                                            <i data-lucide="image-plus" class="icon-3d"></i>
                                        </div>
                                        <div class="upload-zone-title">Drop thumbnail or click to browse</div>
                                        <div class="upload-zone-sub">JPG, PNG, WebP &bull; Max 5MB</div>
                                    </div>
                                    <div class="upload-zone-preview" id="uploadPreviewWrap">
                                        <img src="" id="uploadPreviewImg" alt="Preview">
                                        <button type="button" class="preview-remove" id="previewRemoveBtn" title="Remove image">
                                            <i data-lucide="x" class="icon-12"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Actions -->
                            <div class="card-premium">
                                <div class="d-flex flex-column gap-2">
                                    <button type="submit" name="status_override" value="active"
                                            class="btn btn-premium-primary w-100 d-flex align-items-center justify-content-center gap-2">
                                        <i data-lucide="globe" class="icon-lg"></i>
                                        Save &amp; Publish
                                    </button>
                                    <button type="submit" name="status_override" value="draft"
                                            class="btn btn-premium-secondary w-100 d-flex align-items-center justify-content-center gap-2">
                                        <i data-lucide="file" class="icon-lg"></i>
                                        Save as Draft
                                    </button>
                                    <a href="<?= BASE_URL ?>blogs/"
                                       class="btn btn-premium-secondary w-100 d-flex align-items-center justify-content-center gap-2">
                                        <i data-lucide="x" class="icon-lg"></i>
                                        Cancel
                                    </a>
                                </div>
                            </div>

                        </div>
                    </div><!-- /right column -->
                </div><!-- /row -->
            </form>

        </div><!-- /container-fluid -->
    </div><!-- /page-content-wrapper -->
</div><!-- /wrapper -->

<?php include __DIR__ . '/../includes/footer.php'; ?>
