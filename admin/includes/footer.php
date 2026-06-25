<?php
// admin/includes/footer.php
require_once __DIR__ . '/../config/config.php';
?>
    <!-- Reusable Alerts Include (SweetAlert2 popups listener) -->
    <?php include __DIR__ . '/alerts.php'; ?>

    <!-- Bootstrap 5.3 JS Bundle (includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- TinyMCE CDN -->
    <script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js" referrerpolicy="origin"></script>
    
    <!-- Custom Admin Scripts -->
    <script src="<?= BASE_URL ?>assets/js/main.js"></script>
    <?php if (isset($extra_js)): ?>
        <script src="<?= BASE_URL ?>assets/js/<?= $extra_js ?>"></script>
    <?php endif; ?>
</body>
</html>
