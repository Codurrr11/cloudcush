<?php
// admin/faqs/add.php — Add New FAQ
require_once __DIR__ . '/../includes/auth-check.php';
require_once __DIR__ . '/../config/faqs-helper.php';

// Check if form is submitted via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $question  = trim(strip_tags($_POST['question'] ?? ''));
    $answer    = trim($_POST['answer'] ?? '');
    $category  = $_POST['category'] ?? 'product';
    $sortOrder = filter_input(INPUT_POST, 'sort_order', FILTER_VALIDATE_INT) ?? 0;
    $status    = in_array($_POST['status'] ?? '', ['active', 'draft']) ? $_POST['status'] : 'draft';

    // Validation
    $errors = [];
    if (empty($question)) $errors[] = 'Question is required.';
    if (empty($answer)) $errors[] = 'Answer content is required.';
    
    $validCategories = array_keys(getFaqCategories());
    if (!in_array($category, $validCategories)) $errors[] = 'Invalid category selection.';

    if (!empty($errors)) {
        $_SESSION['flash_message'] = implode(' ', $errors);
        $_SESSION['flash_type'] = 'error';
    } else {
        try {
            $db = getDBConnection();

            // Insert FAQ
            $stmt = $db->prepare("
                INSERT INTO faqs (question, answer, category, sort_order, status)
                VALUES (:question, :answer, :category, :sort_order, :status)
            ");
            $stmt->execute([
                ':question'   => $question,
                ':answer'     => $answer,
                ':category'   => $category,
                ':sort_order' => $sortOrder,
                ':status'     => $status,
            ]);

            $_SESSION['flash_message'] = "FAQ added successfully.";
            $_SESSION['flash_type'] = 'success';
            header('Location: ' . BASE_URL . 'faqs/');
            exit;
        } catch (\PDOException $e) {
            error_log('FAQ create error: ' . $e->getMessage());
            $_SESSION['flash_message'] = 'Database error while saving FAQ. Please try again.';
            $_SESSION['flash_type'] = 'error';
        }
    }
}

$page_title  = 'CloudCush Admin — Add FAQ';
$active_page = 'faqs';

$categories = getFaqCategories();

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
                        <a href="<?= BASE_URL ?>faqs/" class="btn-action btn-back-square" title="Back to FAQs">
                            <i data-lucide="arrow-left" class="icon-sm"></i>
                        </a>
                        <h1 class="h4 fw-bold mb-0 page-heading">Add FAQ</h1>
                    </div>
                    <p class="text-secondary mb-0 fs-0-82 pl-2-25">
                        Add a new frequently asked question and answer to help center resources.
                    </p>
                </div>
            </div>

            <form action=""
                  method="POST"
                  id="addFaqForm"
                  novalidate>

                <div class="row g-4">

                    <!-- ── LEFT COLUMN: Main Editor ── -->
                    <div class="col-12 col-xl-8">

                        <!-- Question Input -->
                        <div class="card-premium mb-3">
                            <span class="form-section-label">FAQ Information</span>

                            <div class="mb-3">
                                <label class="form-label-premium" for="faqQuestion">
                                    Question <span class="req">*</span>
                                </label>
                                <input type="text" id="faqQuestion" name="question"
                                       class="form-control-premium" required autocomplete="off"
                                       placeholder="e.g. How do I clean my diaper cover?"
                                       value="<?= htmlspecialchars($_POST['question'] ?? '') ?>">
                            </div>
                        </div>

                        <!-- FAQ Answer / Rich Content -->
                        <div class="card-premium mb-3">
                            <span class="form-section-label">FAQ Answer <span class="req">*</span></span>
                            <div class="tinymce-wrap">
                                <textarea id="faqAnswer" name="answer"
                                          class="tinymce-editor" rows="12" required
                                          placeholder="Enter the detailed answer to this question…"><?= htmlspecialchars($_POST['answer'] ?? '') ?></textarea>
                            </div>
                        </div>

                    </div><!-- /left column -->

                    <!-- ── RIGHT COLUMN: Sidebar Settings ── -->
                    <div class="col-12 col-xl-4">
                        <div class="form-sidebar-sticky-wrapper">

                            <!-- Category & Sorting Card -->
                            <div class="card-premium mb-3">
                                <span class="form-section-label">Categorization</span>

                                <div class="mb-3">
                                    <label class="form-label-premium" for="faqCategory">Category <span class="req">*</span></label>
                                    <select name="category" id="faqCategory" class="form-select-premium">
                                        <?php foreach ($categories as $key => $lbl): ?>
                                            <option value="<?= htmlspecialchars($key) ?>" <?= ($_POST['category'] ?? 'product') === $key ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($lbl) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="mb-2">
                                    <label class="form-label-premium" for="faqSortOrder">Sort Order</label>
                                    <input type="number" id="faqSortOrder" name="sort_order"
                                           class="form-control-premium" min="0" step="1"
                                           placeholder="e.g. 10 (lowest values first)"
                                           value="<?= htmlspecialchars($_POST['sort_order'] ?? '0') ?>">
                                    <div class="form-text fs-0-7 text-secondary mt-1">Controls the display order on the FAQs page.</div>
                                </div>
                            </div>

                            <!-- Status Card -->
                            <div class="card-premium mb-3">
                                <span class="form-section-label">Publish Settings</span>

                                <div class="mb-2">
                                    <label class="form-label-premium">Status</label>
                                    <select name="status" class="form-select-premium">
                                        <option value="draft"  <?= ($_POST['status'] ?? 'draft') === 'draft' ? 'selected' : '' ?>>Draft (Hidden)</option>
                                        <option value="active" <?= ($_POST['status'] ?? '') === 'active' ? 'selected' : '' ?>>Live (Visible)</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="card-premium">
                                <div class="d-flex flex-column gap-2">
                                    <button type="submit"
                                            class="btn btn-premium-primary w-100 d-flex align-items-center justify-content-center gap-2">
                                        <i data-lucide="check" class="icon-lg"></i>
                                        Save FAQ
                                    </button>
                                    <a href="<?= BASE_URL ?>faqs/"
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
