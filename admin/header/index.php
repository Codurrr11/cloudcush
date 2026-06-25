<?php
// admin/header/index.php — Header Settings Page
require_once __DIR__ . '/../includes/auth-check.php';
require_once __DIR__ . '/../config/header-helper.php';

$headerData = getHeaderData();

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $logoText = trim(strip_tags($_POST['logo_text'] ?? ''));
    $tabTitles = $_POST['tab_title'] ?? [];
    $tabUrls = $_POST['tab_url'] ?? [];
    $tabPositions = $_POST['tab_position'] ?? [];

    $tabs = [];
    foreach ($tabTitles as $i => $title) {
        $title = trim(strip_tags($title));
        $url = trim(strip_tags($tabUrls[$i] ?? ''));
        $pos = trim(strip_tags($tabPositions[$i] ?? 'left'));
        if (empty($title) || empty($url)) continue;
        $tabs[] = [
            'title' => $title,
            'url' => $url,
            'position' => $pos
        ];
    }

    $logoImg = $headerData['logo_img'] ?? 'assets/images/logo.png';

    // Handle new logo upload
    if (!empty($_FILES['logo_file']['name']) && $_FILES['logo_file']['error'] === UPLOAD_ERR_OK) {
        try {
            $uploaded = handleHeaderImageUpload($_FILES['logo_file'], 'header_logo');
            if ($uploaded) {
                // Delete previous upload if it's not the default logo asset
                if ($logoImg && str_contains($logoImg, UPLOAD_URL)) {
                    $localPath = str_replace(UPLOAD_URL, UPLOAD_DIR, $logoImg);
                    if (file_exists($localPath)) @unlink($localPath);
                }
                $logoImg = $uploaded;
            }
        } catch (\InvalidArgumentException $e) {
            $errors[] = $e->getMessage();
        } catch (Exception $e) {
            $errors[] = 'Failed to upload logo image: ' . $e->getMessage();
        }
    }

    if (empty($errors)) {
        $payload = [
            'logo_img' => $logoImg,
            'logo_text' => $logoText,
            'tabs' => $tabs
        ];
        if (saveHeaderData($payload)) {
            $_SESSION['flash_message'] = 'Header configuration saved successfully.';
            $_SESSION['flash_type'] = 'success';
            header('Location: ' . BASE_URL . 'header/');
            exit;
        } else {
            $errors[] = 'Failed to save configuration in database.';
        }
    }
}

$page_title = 'Header Settings — CloudCush Admin';
$active_page = 'header';

include __DIR__ . '/../includes/header.php';
?>

<div id="wrapper">
    <?php include __DIR__ . '/../includes/sidebar.php'; ?>

    <div id="page-content-wrapper">
        <?php include __DIR__ . '/../includes/navbar.php'; ?>

        <div class="container-fluid px-0 py-2">
            <!-- Page Header -->
            <div class="mb-4">
                <h1 class="h4 fw-bold mb-1 page-heading">Header Settings</h1>
                <p class="text-secondary mb-0 fs-0-82">
                    Manage your store logo and desktop navigation links.
                </p>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger"><?= implode('<br>', $errors) ?></div>
            <?php endif; ?>

            <form action="" method="POST" enctype="multipart/form-data" novalidate>
                <div class="row g-4">
                    <!-- Left: Navigation Links and Text Settings -->
                    <div class="col-12 col-xl-8">
                        <div class="card-premium mb-3">
                            <span class="form-section-label">General Branding</span>
                            <div class="mb-3">
                                <label class="form-label-premium" for="logo_text">Logo Brand Text <span class="hint">(fallback if no image logo is uploaded)</span></label>
                                <input type="text" id="logo_text" name="logo_text" class="form-control-premium"
                                       value="<?= htmlspecialchars($headerData['logo_text'] ?? '') ?>" placeholder="e.g. CloudCush">
                            </div>
                        </div>

                        <div class="card-premium">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="form-section-label mb-0">Navigation Tabs</span>
                                <button type="button" id="addTabBtn" class="btn btn-premium-secondary btn-sm d-flex align-items-center gap-2">
                                    <i data-lucide="plus" class="icon-sm"></i>
                                    <span>Add Link</span>
                                </button>
                            </div>

                            <div class="variant-header-row mb-2">
                                <span class="form-label-premium mb-0">Tab Title</span>
                                <span class="form-label-premium mb-0">Link URL <span class="hint">(e.g. products.php)</span></span>
                                <span class="form-label-premium mb-0">Position</span>
                                <span>Actions</span>
                            </div>

                            <div id="tabsWrapper" class="d-flex flex-column gap-2 mb-3">
                                <?php 
                                $tabsList = $headerData['tabs'] ?? [];
                                foreach ($tabsList as $tab): 
                                ?>
                                    <div class="variant-row align-items-center">
                                        <div>
                                            <input type="text" name="tab_title[]" class="form-control-premium" required
                                                   value="<?= htmlspecialchars($tab['title'] ?? '') ?>" placeholder="e.g. Shop">
                                        </div>
                                        <div>
                                            <input type="text" name="tab_url[]" class="form-control-premium" required
                                                   value="<?= htmlspecialchars($tab['url'] ?? '') ?>" placeholder="e.g. products.php">
                                        </div>
                                        <div>
                                            <select name="tab_position[]" class="form-select-premium">
                                                <option value="left" <?= ($tab['position'] ?? 'left') === 'left' ? 'selected' : '' ?>>Left of Logo</option>
                                                <option value="right" <?= ($tab['position'] ?? 'left') === 'right' ? 'selected' : '' ?>>Right of Logo</option>
                                            </select>
                                        </div>
                                        <div class="d-flex gap-1 justify-content-end">
                                            <button type="button" class="btn btn-sm btn-outline-secondary move-up-tab-btn" title="Move Up">
                                                <i data-lucide="arrow-up" style="width:12px;height:12px;"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary move-down-tab-btn" title="Move Down">
                                                <i data-lucide="arrow-down" style="width:12px;height:12px;"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-tab-btn" title="Remove">
                                                <i data-lucide="trash-2" style="width:12px;height:12px;"></i>
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
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

                    <!-- Right: Brand Logo Image Uploader -->
                    <div class="col-12 col-xl-4">
                        <div class="form-sidebar-sticky-wrapper d-flex flex-column gap-3">
                            <div class="card-premium p-4">
                                <div class="d-flex align-items-center gap-2 mb-3 border-bottom pb-2">
                                    <i data-lucide="image" class="text-primary" style="width: 16px; height: 16px;"></i>
                                    <span class="form-section-label mb-0" style="font-size:0.82rem;">Logo Image</span>
                                </div>

                                <div class="upload-zone" id="uploadZone">
                                    <input type="file" id="logoImageInput" name="logo_file"
                                           accept="image/jpeg,image/png,image/webp"
                                           aria-label="Upload logo image">

                                    <?php 
                                    $logoUrl = $headerData['logo_img'] ?? '';
                                    if ($logoUrl): 
                                        $resolvedLogo = resolveAdminAssetUrl($logoUrl);
                                    ?>
                                        <div id="uploadZoneBody" class="d-none-init">
                                            <div class="upload-zone-icon">
                                                <i data-lucide="image-plus" class="icon-3d"></i>
                                            </div>
                                            <div class="upload-zone-title">Drop image or click to browse</div>
                                            <div class="upload-zone-sub">JPG, PNG, WebP &bull; Max 5MB</div>
                                        </div>
                                        <div class="upload-zone-preview show shadow-sm" id="uploadPreviewWrap" style="background:#f8fafc; padding:15px; border-radius:8px; display:block;">
                                            <img src="<?= htmlspecialchars($resolvedLogo) ?>" id="uploadPreviewImg" alt="Logo Preview" style="max-height:80px; object-fit:contain; width:auto; margin:0 auto; display:block;">
                                            <button type="button" class="preview-remove" id="previewRemoveBtn" title="Remove image">
                                                <i data-lucide="x" class="icon-12"></i>
                                            </button>
                                        </div>
                                    <?php else: ?>
                                        <div id="uploadZoneBody">
                                            <div class="upload-zone-icon">
                                                <i data-lucide="image-plus" class="icon-3d"></i>
                                            </div>
                                            <div class="upload-zone-title">Drop image or click to browse</div>
                                            <div class="upload-zone-sub">JPG, PNG, WebP &bull; Max 5MB</div>
                                        </div>
                                        <div class="upload-zone-preview shadow-sm" id="uploadPreviewWrap">
                                            <img src="" id="uploadPreviewImg" alt="Preview">
                                            <button type="button" class="preview-remove" id="previewRemoveBtn" title="Remove image">
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
    const tabsWrapper = document.getElementById("tabsWrapper");
    const addTabBtn = document.getElementById("addTabBtn");
    const logoInput = document.getElementById("logoImageInput");
    const uploadZone = document.getElementById("uploadZone");
    const previewWrap = document.getElementById("uploadPreviewWrap");
    const previewImg = document.getElementById("uploadPreviewImg");
    const previewRmBtn = document.getElementById("previewRemoveBtn");
    const uploadBody = document.getElementById("uploadZoneBody");

    // Dynamic Image Preview
    if (logoInput && uploadZone) {
        logoInput.addEventListener("change", function () {
            if (this.files[0]) {
                const file = this.files[0];
                const reader = new FileReader();
                reader.onload = function (e) {
                    if (previewImg) {
                        previewImg.src = e.target.result;
                        previewImg.style.display = "block";
                    }
                    if (previewWrap) {
                        previewWrap.style.display = "block";
                        previewWrap.classList.add("show");
                    }
                    if (uploadBody) uploadBody.style.display = "none";
                };
                reader.readAsDataURL(file);
            }
        });

        if (previewRmBtn) {
            previewRmBtn.addEventListener("click", function (e) {
                e.preventDefault();
                e.stopPropagation();
                logoInput.value = "";
                if (previewImg) previewImg.src = "";
                if (previewWrap) {
                    previewWrap.style.display = "none";
                    previewWrap.classList.remove("show");
                }
                if (uploadBody) uploadBody.style.display = "block";
            });
        }
    }

    if (addTabBtn && tabsWrapper) {
        addTabBtn.addEventListener("click", function () {
            const row = document.createElement("div");
            row.className = "variant-row align-items-center";
            row.innerHTML = `
                <div>
                    <input type="text" name="tab_title[]" class="form-control-premium" required placeholder="e.g. Shop">
                </div>
                <div>
                    <input type="text" name="tab_url[]" class="form-control-premium" required placeholder="e.g. products.php">
                </div>
                <div>
                    <select name="tab_position[]" class="form-select-premium">
                        <option value="left">Left of Logo</option>
                        <option value="right">Right of Logo</option>
                    </select>
                </div>
                <div class="d-flex gap-1 justify-content-end">
                    <button type="button" class="btn btn-sm btn-outline-secondary move-up-tab-btn" title="Move Up">
                        <i data-lucide="arrow-up" style="width:12px;height:12px;"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary move-down-tab-btn" title="Move Down">
                        <i data-lucide="arrow-down" style="width:12px;height:12px;"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-tab-btn" title="Remove">
                        <i data-lucide="trash-2" style="width:12px;height:12px;"></i>
                    </button>
                </div>
            `;
            tabsWrapper.appendChild(row);
            if (typeof lucide !== "undefined") {
                lucide.createIcons();
            }
        });

        tabsWrapper.addEventListener("click", function (e) {
            const btn = e.target.closest("button");
            if (!btn) return;
            const row = btn.closest(".variant-row");
            if (!row) return;

            e.preventDefault();
            e.stopPropagation();

            if (btn.classList.contains("move-up-tab-btn")) {
                const prev = row.previousElementSibling;
                if (prev) {
                    tabsWrapper.insertBefore(row, prev);
                }
            } else if (btn.classList.contains("move-down-tab-btn")) {
                const next = row.nextElementSibling;
                if (next) {
                    tabsWrapper.insertBefore(next, row);
                }
            } else if (btn.classList.contains("remove-tab-btn")) {
                row.remove();
            }
        });
    }
});
</script>
