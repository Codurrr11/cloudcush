<?php
// admin/reviews/add.php — Add New Customer Review
require_once __DIR__ . '/../includes/auth-check.php';
require_once __DIR__ . '/../config/reviews-helper.php';

// Check if form is submitted via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name       = trim(strip_tags($_POST['name'] ?? ''));
    $role       = trim(strip_tags($_POST['role'] ?? ''));
    $rating     = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_INT) ?: 5;
    $quote      = trim($_POST['quote'] ?? '');
    $mediaType  = $_POST['media_type'] ?? 'text';
    $status     = in_array($_POST['status'] ?? '', ['active', 'draft']) ? $_POST['status'] : 'draft';

    // Validation
    $errors = [];
    if (empty($name)) $errors[] = 'Customer name is required.';
    if (empty($quote)) $errors[] = 'Review quote is required.';
    if ($rating < 1 || $rating > 5) $errors[] = 'Rating must be between 1 and 5.';
    if (!in_array($mediaType, ['text', 'image', 'video'])) $errors[] = 'Invalid media type selection.';

    if (!empty($errors)) {
        $_SESSION['flash_message'] = implode(' ', $errors);
        $_SESSION['flash_type'] = 'error';
    } else {
        try {
            $db = getDBConnection();

            // Handle media upload if type is image or video
            $mediaUrl = null;
            if (($mediaType === 'image' || $mediaType === 'video') && !empty($_FILES['media_file']['name'])) {
                $mediaUrl = handleReviewMediaUpload($_FILES['media_file'], $mediaType, 'review');
            }

            // Insert review
            $stmt = $db->prepare("
                INSERT INTO reviews (name, role, rating, quote, media_type, media_url, status)
                VALUES (:name, :role, :rating, :quote, :media_type, :media_url, :status)
            ");
            $stmt->execute([
                ':name'       => $name,
                ':role'       => $role ?: null,
                ':rating'     => $rating,
                ':quote'      => $quote,
                ':media_type' => $mediaType,
                ':media_url'  => $mediaUrl,
                ':status'     => $status,
            ]);

            $_SESSION['flash_message'] = "Review by \"$name\" added successfully.";
            $_SESSION['flash_type'] = 'success';
            header('Location: ' . BASE_URL . 'reviews/');
            exit;
        } catch (\InvalidArgumentException $e) {
            $_SESSION['flash_message'] = $e->getMessage();
            $_SESSION['flash_type'] = 'error';
        } catch (\PDOException $e) {
            error_log('Review create error: ' . $e->getMessage());
            $_SESSION['flash_message'] = 'Database error while saving review. Please try again.';
            $_SESSION['flash_type'] = 'error';
        }
    }
}

$page_title  = 'CloudCush Admin — Add Review';
$active_page = 'reviews';

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
                        <a href="<?= BASE_URL ?>reviews/" class="btn-action btn-back-square" title="Back to Reviews">
                            <i data-lucide="arrow-left" class="icon-sm"></i>
                        </a>
                        <h1 class="h4 fw-bold mb-0 page-heading">Add Customer Review</h1>
                    </div>
                    <p class="text-secondary mb-0 fs-0-82 pl-2-25">
                        Add a new verified testimonial or moment, including custom photos, video clips, and customer quotes.
                    </p>
                </div>
            </div>

            <form action=""
                  method="POST"
                  enctype="multipart/form-data"
                  id="addReviewForm"
                  novalidate>

                <div class="row g-4">

                    <!-- ── LEFT COLUMN: Main Editor ── -->
                    <div class="col-12 col-xl-8">

                        <!-- Basic Information -->
                        <div class="card-premium mb-3">
                            <span class="form-section-label">Reviewer Information</span>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label-premium" for="reviewerName">
                                        Customer Name <span class="req">*</span>
                                    </label>
                                    <input type="text" id="reviewerName" name="name"
                                           class="form-control-premium" required autocomplete="off"
                                           placeholder="e.g. Aanya S."
                                           value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label-premium" for="reviewerRole">
                                        Details / Subheading <span class="hint">(e.g. Verified Parent • Bengaluru)</span>
                                    </label>
                                    <input type="text" id="reviewerRole" name="role"
                                           class="form-control-premium" autocomplete="off"
                                           placeholder="e.g. Verified Mom • Jaipur • GentleCare"
                                           value="<?= htmlspecialchars($_POST['role'] ?? '') ?>">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label-premium" for="reviewerRating">Rating <span class="req">*</span></label>
                                    <select name="rating" id="reviewerRating" class="form-select-premium">
                                        <option value="5" <?= ($_POST['rating'] ?? '5') == 5 ? 'selected' : '' ?>>5 Stars (★★★★★)</option>
                                        <option value="4" <?= ($_POST['rating'] ?? '') == 4 ? 'selected' : '' ?>>4 Stars (★★★★☆)</option>
                                        <option value="3" <?= ($_POST['rating'] ?? '') == 3 ? 'selected' : '' ?>>3 Stars (★★★☆☆)</option>
                                        <option value="2" <?= ($_POST['rating'] ?? '') == 2 ? 'selected' : '' ?>>2 Stars (★★☆☆☆)</option>
                                        <option value="1" <?= ($_POST['rating'] ?? '') == 1 ? 'selected' : '' ?>>1 Star (★☆☆☆☆)</option>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label-premium" for="reviewMediaType">Media Type</label>
                                    <select name="media_type" id="reviewMediaType" class="form-select-premium">
                                        <option value="text"  <?= ($_POST['media_type'] ?? 'text') === 'text' ? 'selected' : '' ?>>Text Only</option>
                                        <option value="image" <?= ($_POST['media_type'] ?? '') === 'image' ? 'selected' : '' ?>>Image Testimonial</option>
                                        <option value="video" <?= ($_POST['media_type'] ?? '') === 'video' ? 'selected' : '' ?>>Video Testimonial</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Review Quote / Content -->
                        <div class="card-premium mb-3">
                            <span class="form-section-label">Testimonial Quote <span class="req">*</span></span>
                            <div class="tinymce-wrap">
                                <textarea id="reviewerQuote" name="quote"
                                          class="tinymce-editor" rows="10" required
                                          placeholder="Enter the customer's comment or quote here…"><?= htmlspecialchars($_POST['quote'] ?? '') ?></textarea>
                            </div>
                        </div>

                    </div><!-- /left column -->

                    <!-- ── RIGHT COLUMN: Sidebar Settings ── -->
                    <div class="col-12 col-xl-4">
                        <div class="form-sidebar-sticky-wrapper">

                            <!-- Status Card -->
                            <div class="card-premium mb-3">
                                <span class="form-section-label">Publish Settings</span>

                                <div class="mb-2">
                                    <label class="form-label-premium">Status</label>
                                    <select name="status" class="form-select-premium">
                                        <option value="draft"  <?= ($_POST['status'] ?? 'draft') === 'draft' ? 'selected' : '' ?>>Pending Approval</option>
                                        <option value="active" <?= ($_POST['status'] ?? '') === 'active' ? 'selected' : '' ?>>Approved & Live</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Media Upload Card (toggled via JS) -->
                            <div class="card-premium mb-3" id="mediaUploadCard" style="display: none;">
                                <span class="form-section-label">Testimonial Media File</span>
                                <div class="upload-zone" id="uploadZone">
                                    <input type="file" id="reviewMediaInput" name="media_file">
                                    <div id="uploadZoneBody">
                                        <div class="upload-zone-icon">
                                            <i data-lucide="upload-cloud" class="icon-3d"></i>
                                        </div>
                                        <div class="upload-zone-title" id="uploadZoneTitle">Drop file or click to browse</div>
                                        <div class="upload-zone-sub" id="uploadZoneSub">Supported file &bull; Max size limit</div>
                                    </div>
                                    <div class="upload-zone-preview" id="uploadPreviewWrap" style="display: none;">
                                        <img src="" id="uploadPreviewImg" alt="Preview" style="display: none; max-width: 100%; border-radius: 8px;">
                                        <video src="" id="uploadPreviewVideo" controls style="display: none; width: 100%; max-height: 200px; border-radius: 8px;"></video>
                                        <button type="button" class="preview-remove" id="previewRemoveBtn" title="Remove file">
                                            <i data-lucide="x" class="icon-12"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="card-premium">
                                <div class="d-flex flex-column gap-2">
                                    <button type="submit"
                                            class="btn btn-premium-primary w-100 d-flex align-items-center justify-content-center gap-2">
                                        <i data-lucide="check" class="icon-lg"></i>
                                        Save Testimonial
                                    </button>
                                    <a href="<?= BASE_URL ?>reviews/"
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

<script>
document.addEventListener("DOMContentLoaded", function() {
    const mediaTypeSelect = document.getElementById("reviewMediaType");
    const mediaUploadCard = document.getElementById("mediaUploadCard");
    const reviewMediaInput = document.getElementById("reviewMediaInput");
    const uploadZoneTitle = document.getElementById("uploadZoneTitle");
    const uploadZoneSub = document.getElementById("uploadZoneSub");

    function updateMediaTypeFields() {
        const type = mediaTypeSelect.value;
        if (type === 'text') {
            mediaUploadCard.style.display = 'none';
            reviewMediaInput.removeAttribute('required');
        } else {
            mediaUploadCard.style.display = 'block';
            if (type === 'image') {
                reviewMediaInput.setAttribute('accept', 'image/jpeg,image/png,image/webp');
                uploadZoneTitle.textContent = 'Drop image or click to browse';
                uploadZoneSub.innerHTML = 'JPG, PNG, WebP &bull; Max 5MB';
            } else if (type === 'video') {
                reviewMediaInput.setAttribute('accept', 'video/mp4,video/webm,video/ogg,video/quicktime');
                uploadZoneTitle.textContent = 'Drop video or click to browse';
                uploadZoneSub.innerHTML = 'MP4, WebM, OGG, MOV &bull; Max 25MB';
            }
        }
    }

    if (mediaTypeSelect) {
        mediaTypeSelect.addEventListener("change", updateMediaTypeFields);
        updateMediaTypeFields();
    }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
