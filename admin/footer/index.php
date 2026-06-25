<?php
// admin/footer/index.php — Footer Settings Page
require_once __DIR__ . '/../includes/auth-check.php';
require_once __DIR__ . '/../config/footer-helper.php';

$footerData = getFooterData();

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $storyText    = trim($_POST['story_text'] ?? '');
    $typingText1  = trim(strip_tags($_POST['typing_text_1'] ?? ''));
    $typingText2  = trim(strip_tags($_POST['typing_text_2'] ?? ''));
    $copyrightTxt = trim(strip_tags($_POST['copyright_text'] ?? ''));

    // Social links
    $socialLinks = [
        'instagram' => trim(strip_tags($_POST['social_instagram'] ?? '')),
        'youtube'   => trim(strip_tags($_POST['social_youtube']   ?? '')),
        'facebook'  => trim(strip_tags($_POST['social_facebook']  ?? '')),
        'twitter'   => trim(strip_tags($_POST['social_twitter']   ?? '')),
    ];

    // Legal links repeater
    $legalTitles = $_POST['legal_link_title'] ?? [];
    $legalUrls   = $_POST['legal_link_url']   ?? [];
    $legalLinks  = [];
    foreach ($legalTitles as $idx => $title) {
        $title = trim(strip_tags($title));
        $url   = trim(strip_tags($legalUrls[$idx] ?? ''));
        if (empty($title)) continue;
        $legalLinks[] = ['title' => $title, 'url' => $url ?: 'javascript:void(0);'];
    }

    $colTitles = [
        trim(strip_tags($_POST['col_title_1'] ?? '')),
        trim(strip_tags($_POST['col_title_2'] ?? '')),
        trim(strip_tags($_POST['col_title_3'] ?? ''))
    ];

    $columns = [];
    for ($c = 1; $c <= 3; $c++) {
        $linkTitles = $_POST["col_link_title_{$c}"] ?? [];
        $linkUrls   = $_POST["col_link_url_{$c}"]   ?? [];
        $links = [];
        foreach ($linkTitles as $idx => $title) {
            $title = trim(strip_tags($title));
            $url   = trim(strip_tags($linkUrls[$idx] ?? ''));
            if (empty($title) || empty($url)) continue;
            $links[] = ['title' => $title, 'url' => $url];
        }
        $columns[] = [
            'title' => $colTitles[$c - 1] ?: 'Column ' . $c,
            'links' => $links
        ];
    }

    $logoImg = $footerData['logo_img'] ?? 'assets/images/logo.png';
    $bgImage = $footerData['bg_image'] ?? 'assets/images/footer-bg.png';

    // Handle new logo upload
    if (!empty($_FILES['logo_file']['name']) && $_FILES['logo_file']['error'] === UPLOAD_ERR_OK) {
        try {
            $uploadedLogo = handleFooterImageUpload($_FILES['logo_file'], 'footer_logo');
            if ($uploadedLogo) {
                if ($logoImg && str_contains($logoImg, UPLOAD_URL)) {
                    $localPath = str_replace(UPLOAD_URL, UPLOAD_DIR, $logoImg);
                    if (file_exists($localPath)) @unlink($localPath);
                }
                $logoImg = $uploadedLogo;
            }
        } catch (\InvalidArgumentException $e) {
            $errors[] = $e->getMessage();
        } catch (Exception $e) {
            $errors[] = 'Failed to upload logo image: ' . $e->getMessage();
        }
    }

    // Handle background image upload
    if (!empty($_FILES['bg_file']['name']) && $_FILES['bg_file']['error'] === UPLOAD_ERR_OK) {
        try {
            $uploadedBg = handleFooterImageUpload($_FILES['bg_file'], 'footer_bg');
            if ($uploadedBg) {
                if ($bgImage && str_contains($bgImage, UPLOAD_URL)) {
                    $localPath = str_replace(UPLOAD_URL, UPLOAD_DIR, $bgImage);
                    if (file_exists($localPath)) @unlink($localPath);
                }
                $bgImage = $uploadedBg;
            }
        } catch (\InvalidArgumentException $e) {
            $errors[] = $e->getMessage();
        } catch (Exception $e) {
            $errors[] = 'Failed to upload background image: ' . $e->getMessage();
        }
    }

    if (empty($errors)) {
        $payload = [
            'logo_img'       => $logoImg,
            'story_text'     => $storyText,
            'bg_image'       => $bgImage,
            'typing_text_1'  => $typingText1,
            'typing_text_2'  => $typingText2,
            'copyright_text' => $copyrightTxt,
            'social_links'   => $socialLinks,
            'legal_links'    => $legalLinks,
            'columns'        => $columns
        ];
        if (saveFooterData($payload)) {
            $_SESSION['flash_message'] = 'Footer configuration saved successfully.';
            $_SESSION['flash_type'] = 'success';
            header('Location: ' . BASE_URL . 'footer/');
            exit;
        } else {
            $errors[] = 'Failed to save configuration in database.';
        }
    }
}

$page_title = 'Footer Settings — CloudCush Admin';
$active_page = 'footer';

include __DIR__ . '/../includes/header.php';
?>

<div id="wrapper">
    <?php include __DIR__ . '/../includes/sidebar.php'; ?>

    <div id="page-content-wrapper">
        <?php include __DIR__ . '/../includes/navbar.php'; ?>

        <div class="container-fluid px-0 py-2">
            <!-- Page Header -->
            <div class="mb-4">
                <h1 class="h4 fw-bold mb-1 page-heading">Footer Settings</h1>
                <p class="text-secondary mb-0 fs-0-82">
                    Manage your footer description, background, typing animation texts, and navigation link columns.
                </p>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger"><?= implode('<br>', $errors) ?></div>
            <?php endif; ?>

            <form action="" method="POST" enctype="multipart/form-data" novalidate>
                <div class="row g-4">
                    <!-- Left: Description, Typing Animation, Link Columns -->
                    <div class="col-12 col-xl-8">
                        <div class="card-premium mb-3">
                            <span class="form-section-label">Footer Description</span>
                            <div class="mb-3">
                                <label class="form-label-premium" for="story_text">Brand Story Description <span class="hint">(Column 1 text)</span></label>
                                <textarea id="story_text" name="story_text" class="form-control-premium" rows="4" required><?= htmlspecialchars($footerData['story_text'] ?? '') ?></textarea>
                            </div>
                        </div>

                        <div class="card-premium mb-3">
                            <span class="form-section-label">Typing Alternating Texts</span>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label-premium" for="typing_text_1">Typing Text 1 (Brand Name)</label>
                                    <input type="text" id="typing_text_1" name="typing_text_1" class="form-control-premium"
                                           value="<?= htmlspecialchars($footerData['typing_text_1'] ?? '') ?>" placeholder="e.g. Cloudcush">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label-premium" for="typing_text_2">Typing Text 2 (Tagline/Slogan)</label>
                                    <input type="text" id="typing_text_2" name="typing_text_2" class="form-control-premium"
                                           value="<?= htmlspecialchars($footerData['typing_text_2'] ?? '') ?>" placeholder="e.g. comfort designed for tiny humans.">
                                </div>
                            </div>
                        </div>

                        <!-- Footer Columns -->
                        <?php 
                        $colsData = $footerData['columns'] ?? [];
                        for ($c = 1; $c <= 3; $c++):
                            $col = $colsData[$c - 1] ?? ['title' => 'Column ' . $c, 'links' => []];
                        ?>
                            <div class="card-premium mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="form-section-label mb-0">Link Column <?= $c ?></span>
                                    <button type="button" class="btn btn-premium-secondary btn-sm d-flex align-items-center gap-2 add-col-link-btn" data-col="<?= $c ?>">
                                        <i data-lucide="plus" class="icon-sm"></i>
                                        <span>Add Link</span>
                                    </button>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label-premium">Column Title</label>
                                    <input type="text" name="col_title_<?= $c ?>" class="form-control-premium"
                                           value="<?= htmlspecialchars($col['title'] ?? '') ?>" placeholder="Column Title">
                                </div>

                                <div class="variant-header-row mb-2">
                                    <span class="form-label-premium mb-0">Link Title</span>
                                    <span class="form-label-premium mb-0">Link URL <span class="hint">(e.g. products.php)</span></span>
                                    <span>Actions</span>
                                </div>

                                <div id="linksWrapper_<?= $c ?>" class="d-flex flex-column gap-2 wrapper-col-links">
                                    <?php foreach ($col['links'] as $link): ?>
                                        <div class="variant-row align-items-center">
                                            <div>
                                                <input type="text" name="col_link_title_<?= $c ?>[]" class="form-control-premium" required
                                                       value="<?= htmlspecialchars($link['title'] ?? '') ?>" placeholder="e.g. Contact Us">
                                            </div>
                                            <div>
                                                <input type="text" name="col_link_url_<?= $c ?>[]" class="form-control-premium" required
                                                       value="<?= htmlspecialchars($link['url'] ?? '') ?>" placeholder="e.g. faq.php">
                                            </div>
                                            <div class="d-flex gap-1 justify-content-end">
                                                <button type="button" class="btn btn-sm btn-outline-secondary move-up-col-btn" title="Move Up">
                                                    <i data-lucide="arrow-up" style="width:12px;height:12px;"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-secondary move-down-col-btn" title="Move Down">
                                                    <i data-lucide="arrow-down" style="width:12px;height:12px;"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger remove-col-btn" title="Remove">
                                                    <i data-lucide="trash-2" style="width:12px;height:12px;"></i>
                                                </button>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endfor; ?>


                        <!-- Social Media Links -->
                        <?php
                        $savedSocial = $footerData['social_links'] ?? [];
                        ?>
                        <div class="card-premium mb-3">
                            <span class="form-section-label">Social Media Links</span>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label-premium" for="social_instagram">
                                        <i data-lucide="instagram" style="width:14px;height:14px;vertical-align:middle;margin-right:5px;"></i>Instagram URL
                                    </label>
                                    <input type="url" id="social_instagram" name="social_instagram"
                                           class="form-control-premium"
                                           value="<?= htmlspecialchars($savedSocial['instagram'] ?? '') ?>"
                                           placeholder="https://instagram.com/yourpage">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label-premium" for="social_youtube">
                                        <i data-lucide="youtube" style="width:14px;height:14px;vertical-align:middle;margin-right:5px;"></i>YouTube URL
                                    </label>
                                    <input type="url" id="social_youtube" name="social_youtube"
                                           class="form-control-premium"
                                           value="<?= htmlspecialchars($savedSocial['youtube'] ?? '') ?>"
                                           placeholder="https://youtube.com/@yourchannel">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label-premium" for="social_facebook">
                                        <i data-lucide="facebook" style="width:14px;height:14px;vertical-align:middle;margin-right:5px;"></i>Facebook URL
                                    </label>
                                    <input type="url" id="social_facebook" name="social_facebook"
                                           class="form-control-premium"
                                           value="<?= htmlspecialchars($savedSocial['facebook'] ?? '') ?>"
                                           placeholder="https://facebook.com/yourpage">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label-premium" for="social_twitter">
                                        <i data-lucide="twitter" style="width:14px;height:14px;vertical-align:middle;margin-right:5px;"></i>Twitter / X URL <span class="hint">(optional — hidden if empty)</span>
                                    </label>
                                    <input type="url" id="social_twitter" name="social_twitter"
                                           class="form-control-premium"
                                           value="<?= htmlspecialchars($savedSocial['twitter'] ?? '') ?>"
                                           placeholder="https://x.com/yourhandle">
                                </div>
                            </div>
                        </div>

                        <!-- Legal Links -->
                        <?php $savedLegal = $footerData['legal_links'] ?? []; ?>
                        <div class="card-premium mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="form-section-label mb-0">Legal & Policy Links <span class="hint">(bottom bar)</span></span>
                                <button type="button" id="addLegalLinkBtn" class="btn btn-premium-secondary btn-sm d-flex align-items-center gap-2">
                                    <i data-lucide="plus" class="icon-sm"></i>
                                    <span>Add Link</span>
                                </button>
                            </div>

                            <div class="variant-header-row mb-2">
                                <span class="form-label-premium mb-0">Link Title</span>
                                <span class="form-label-premium mb-0">URL <span class="hint">(e.g. faq.php or leave blank)</span></span>
                                <span>Actions</span>
                            </div>

                            <div id="legalLinksWrapper" class="d-flex flex-column gap-2">
                                <?php foreach ($savedLegal as $ll): ?>
                                    <div class="variant-row align-items-center">
                                        <div>
                                            <input type="text" name="legal_link_title[]" class="form-control-premium"
                                                   value="<?= htmlspecialchars($ll['title'] ?? '') ?>" placeholder="e.g. Privacy Policy">
                                        </div>
                                        <div>
                                            <input type="text" name="legal_link_url[]" class="form-control-premium"
                                                   value="<?= htmlspecialchars($ll['url'] ?? '') ?>" placeholder="e.g. privacy.php">
                                        </div>
                                        <div class="d-flex gap-1 justify-content-end">
                                            <button type="button" class="btn btn-sm btn-outline-secondary move-up-legal-btn" title="Move Up">
                                                <i data-lucide="arrow-up" style="width:12px;height:12px;"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary move-down-legal-btn" title="Move Down">
                                                <i data-lucide="arrow-down" style="width:12px;height:12px;"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-legal-btn" title="Remove">
                                                <i data-lucide="trash-2" style="width:12px;height:12px;"></i>
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Copyright Text -->
                        <div class="card-premium mb-3">
                            <span class="form-section-label">Copyright Text <span class="hint">(bottom bar)</span></span>
                            <input type="text" id="copyright_text" name="copyright_text"
                                   class="form-control-premium"
                                   value="<?= htmlspecialchars($footerData['copyright_text'] ?? '© 2026, CloudCush. Crafted for softer beginnings.') ?>"
                                   placeholder="e.g. © 2026, CloudCush. All rights reserved.">
                        </div>

                        <!-- Actions -->
                        <div class="card-premium mt-3 p-3">
                            <div class="d-flex gap-2 max-width-320-px">
                                <button type="submit" class="btn btn-premium-primary flex-grow-1 d-flex align-items-center justify-content-center gap-2 py-2">
                                    <i data-lucide="save" class="icon-lg"></i>
                                    <span>Save Configuration</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Logo Image and Background Image Uploaders -->
                    <div class="col-12 col-xl-4">
                        <div class="form-sidebar-sticky-wrapper d-flex flex-column gap-3">
                            <!-- Footer Logo -->
                            <div class="card-premium p-4">
                                <div class="d-flex align-items-center gap-2 mb-3 border-bottom pb-2">
                                    <i data-lucide="image" class="text-primary" style="width: 16px; height: 16px;"></i>
                                    <span class="form-section-label mb-0" style="font-size:0.82rem;">Footer Logo Image</span>
                                </div>

                                <div class="upload-zone" id="logoUploadZone">
                                    <input type="file" id="logoImageInput" name="logo_file"
                                           accept="image/jpeg,image/png,image/webp"
                                           aria-label="Upload footer logo image">

                                    <?php 
                                    $logoUrl = $footerData['logo_img'] ?? '';
                                    if ($logoUrl): 
                                        $resolvedLogo = resolveAdminAssetUrl($logoUrl);
                                    ?>
                                        <div id="logoUploadZoneBody" class="d-none-init">
                                            <div class="upload-zone-icon">
                                                <i data-lucide="image-plus" class="icon-3d"></i>
                                            </div>
                                            <div class="upload-zone-title">Drop image or click to browse</div>
                                            <div class="upload-zone-sub">JPG, PNG, WebP &bull; Max 5MB</div>
                                        </div>
                                        <div class="upload-zone-preview show shadow-sm" id="logoPreviewWrap" style="background:#f8fafc; padding:15px; border-radius:8px; display:block;">
                                            <img src="<?= htmlspecialchars($resolvedLogo) ?>" id="logoPreviewImg" alt="Logo Preview" style="max-height:80px; object-fit:contain; width:auto; margin:0 auto; display:block;">
                                            <button type="button" class="preview-remove" id="logoPreviewRemoveBtn" title="Remove image">
                                                <i data-lucide="x" class="icon-12"></i>
                                            </button>
                                        </div>
                                    <?php else: ?>
                                        <div id="logoUploadZoneBody">
                                            <div class="upload-zone-icon">
                                                <i data-lucide="image-plus" class="icon-3d"></i>
                                            </div>
                                            <div class="upload-zone-title">Drop image or click to browse</div>
                                            <div class="upload-zone-sub">JPG, PNG, WebP &bull; Max 5MB</div>
                                        </div>
                                        <div class="upload-zone-preview shadow-sm" id="logoPreviewWrap">
                                            <img src="" id="logoPreviewImg" alt="Preview">
                                            <button type="button" class="preview-remove" id="logoPreviewRemoveBtn" title="Remove image">
                                                <i data-lucide="x" class="icon-12"></i>
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Footer Background -->
                            <div class="card-premium p-4">
                                <div class="d-flex align-items-center gap-2 mb-3 border-bottom pb-2">
                                    <i data-lucide="image" class="text-primary" style="width: 16px; height: 16px;"></i>
                                    <span class="form-section-label mb-0" style="font-size:0.82rem;">Footer Background Image</span>
                                </div>

                                <div class="upload-zone" id="bgUploadZone">
                                    <input type="file" id="bgImageInput" name="bg_file"
                                           accept="image/jpeg,image/png,image/webp"
                                           aria-label="Upload footer background image">

                                    <?php 
                                    $bgUrl = $footerData['bg_image'] ?? '';
                                    if ($bgUrl): 
                                        $resolvedBg = resolveAdminAssetUrl($bgUrl);
                                    ?>
                                        <div id="bgUploadZoneBody" class="d-none-init">
                                            <div class="upload-zone-icon">
                                                <i data-lucide="image-plus" class="icon-3d"></i>
                                            </div>
                                            <div class="upload-zone-title">Drop image or click to browse</div>
                                            <div class="upload-zone-sub">JPG, PNG, WebP &bull; Max 5MB</div>
                                        </div>
                                        <div class="upload-zone-preview show shadow-sm" id="bgPreviewWrap" style="background:#f8fafc; padding:15px; border-radius:8px; display:block;">
                                            <img src="<?= htmlspecialchars($resolvedBg) ?>" id="bgPreviewImg" alt="Background Preview" style="max-height:100px; object-fit:contain; width:auto; margin:0 auto; display:block;">
                                            <button type="button" class="preview-remove" id="bgPreviewRemoveBtn" title="Remove image">
                                                <i data-lucide="x" class="icon-12"></i>
                                            </button>
                                        </div>
                                    <?php else: ?>
                                        <div id="bgUploadZoneBody">
                                            <div class="upload-zone-icon">
                                                <i data-lucide="image-plus" class="icon-3d"></i>
                                            </div>
                                            <div class="upload-zone-title">Drop image or click to browse</div>
                                            <div class="upload-zone-sub">JPG, PNG, WebP &bull; Max 5MB</div>
                                        </div>
                                        <div class="upload-zone-preview shadow-sm" id="bgPreviewWrap">
                                            <img src="" id="bgPreviewImg" alt="Preview">
                                            <button type="button" class="preview-remove" id="bgPreviewRemoveBtn" title="Remove image">
                                                <i data-lucide="x" class="icon-12"></i>
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<script>
document.addEventListener("DOMContentLoaded", function () {
    // 1. Logo Preview Handling
    const logoInput = document.getElementById("logoImageInput");
    const logoZone = document.getElementById("logoUploadZone");
    const logoPreviewWrap = document.getElementById("logoPreviewWrap");
    const logoPreviewImg = document.getElementById("logoPreviewImg");
    const logoPreviewRm = document.getElementById("logoPreviewRemoveBtn");
    const logoZoneBody = document.getElementById("logoUploadZoneBody");

    if (logoInput && logoZone) {
        logoInput.addEventListener("change", function () {
            if (this.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    if (logoPreviewImg) logoPreviewImg.src = e.target.result;
                    if (logoPreviewWrap) logoPreviewWrap.style.display = "block";
                    if (logoZoneBody) logoZoneBody.style.display = "none";
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
        if (logoPreviewRm) {
            logoPreviewRm.addEventListener("click", function (e) {
                e.preventDefault();
                logoInput.value = "";
                if (logoPreviewImg) logoPreviewImg.src = "";
                if (logoPreviewWrap) logoPreviewWrap.style.display = "none";
                if (logoZoneBody) logoZoneBody.style.display = "block";
            });
        }
    }

    // 2. Background Preview Handling
    const bgInput = document.getElementById("bgImageInput");
    const bgZone = document.getElementById("bgUploadZone");
    const bgPreviewWrap = document.getElementById("bgPreviewWrap");
    const bgPreviewImg = document.getElementById("bgPreviewImg");
    const bgPreviewRm = document.getElementById("bgPreviewRemoveBtn");
    const bgZoneBody = document.getElementById("bgUploadZoneBody");

    if (bgInput && bgZone) {
        bgInput.addEventListener("change", function () {
            if (this.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    if (bgPreviewImg) bgPreviewImg.src = e.target.result;
                    if (bgPreviewWrap) bgPreviewWrap.style.display = "block";
                    if (bgZoneBody) bgZoneBody.style.display = "none";
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
        if (bgPreviewRm) {
            bgPreviewRm.addEventListener("click", function (e) {
                e.preventDefault();
                bgInput.value = "";
                if (bgPreviewImg) bgPreviewImg.src = "";
                if (bgPreviewWrap) bgPreviewWrap.style.display = "none";
                if (bgZoneBody) bgZoneBody.style.display = "block";
            });
        }
    }

    // 3. Link Columns Repeater Handling
    document.querySelectorAll(".add-col-link-btn").forEach(function (btn) {
        btn.addEventListener("click", function () {
            const colNum = this.dataset.col;
            const wrapper = document.getElementById("linksWrapper_" + colNum);
            if (!wrapper) return;

            const row = document.createElement("div");
            row.className = "variant-row align-items-center";
            row.innerHTML = `
                <div>
                    <input type="text" name="col_link_title_${colNum}[]" class="form-control-premium" required placeholder="e.g. Link Label">
                </div>
                <div>
                    <input type="text" name="col_link_url_${colNum}[]" class="form-control-premium" required placeholder="e.g. products.php">
                </div>
                <div class="d-flex gap-1 justify-content-end">
                    <button type="button" class="btn btn-sm btn-outline-secondary move-up-col-btn" title="Move Up">
                        <i data-lucide="arrow-up" style="width:12px;height:12px;"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary move-down-col-btn" title="Move Down">
                        <i data-lucide="arrow-down" style="width:12px;height:12px;"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-col-btn" title="Remove">
                        <i data-lucide="trash-2" style="width:12px;height:12px;"></i>
                    </button>
                </div>
            `;
            wrapper.appendChild(row);
            if (typeof lucide !== "undefined") {
                lucide.createIcons();
            }
        });
    });

    document.querySelectorAll(".wrapper-col-links").forEach(function (wrapper) {
        wrapper.addEventListener("click", function (e) {
            const btn = e.target.closest("button");
            if (!btn) return;
            const row = btn.closest(".variant-row");
            if (!row) return;

            e.preventDefault();
            e.stopPropagation();

            if (btn.classList.contains("move-up-col-btn")) {
                const prev = row.previousElementSibling;
                if (prev) {
                    wrapper.insertBefore(row, prev);
                }
            } else if (btn.classList.contains("move-down-col-btn")) {
                const next = row.nextElementSibling;
                if (next) {
                    wrapper.insertBefore(next, row);
                }
            } else if (btn.classList.contains("remove-col-btn")) {
                row.remove();
            }
        });
    });
    // ── Legal Links Repeater ──────────────────────────────────────
    const addLegalLinkBtn   = document.getElementById("addLegalLinkBtn");
    const legalLinksWrapper = document.getElementById("legalLinksWrapper");

    if (addLegalLinkBtn && legalLinksWrapper) {
        addLegalLinkBtn.addEventListener("click", function () {
            const row = document.createElement("div");
            row.className = "variant-row align-items-center";
            row.innerHTML = `
                <div>
                    <input type="text" name="legal_link_title[]" class="form-control-premium" placeholder="e.g. Privacy Policy">
                </div>
                <div>
                    <input type="text" name="legal_link_url[]" class="form-control-premium" placeholder="e.g. privacy.php">
                </div>
                <div class="d-flex gap-1 justify-content-end">
                    <button type="button" class="btn btn-sm btn-outline-secondary move-up-legal-btn" title="Move Up">
                        <i data-lucide="arrow-up" style="width:12px;height:12px;"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary move-down-legal-btn" title="Move Down">
                        <i data-lucide="arrow-down" style="width:12px;height:12px;"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-legal-btn" title="Remove">
                        <i data-lucide="trash-2" style="width:12px;height:12px;"></i>
                    </button>
                </div>
            `;
            legalLinksWrapper.appendChild(row);
            if (typeof lucide !== "undefined") lucide.createIcons();
            row.querySelector("input")?.focus();
        });

        legalLinksWrapper.addEventListener("click", function (e) {
            const btn = e.target.closest("button");
            if (!btn) return;
            const row = btn.closest(".variant-row");
            if (!row) return;
            e.preventDefault();
            e.stopPropagation();

            if (btn.classList.contains("move-up-legal-btn")) {
                const prev = row.previousElementSibling;
                if (prev) legalLinksWrapper.insertBefore(row, prev);
            } else if (btn.classList.contains("move-down-legal-btn")) {
                const next = row.nextElementSibling;
                if (next) legalLinksWrapper.insertBefore(next, row);
            } else if (btn.classList.contains("remove-legal-btn")) {
                row.remove();
            }
        });
    }
});
</script>
