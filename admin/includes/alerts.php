<?php
// admin/includes/alerts.php
// Flash message display via SweetAlert2 — styled to CloudCush design system

if (isset($_SESSION['flash_message'])) {
    $flashMsg  = $_SESSION['flash_message'];
    $flashType = $_SESSION['flash_type'] ?? 'info'; // success | error | warning | info
    unset($_SESSION['flash_message'], $_SESSION['flash_type']);

    $title = match($flashType) {
        'success' => 'Done!',
        'error'   => 'Error',
        'warning' => 'Heads up',
        default   => 'Notice',
    };

    // Map type → icon (SweetAlert2 icon names)
    $icon = in_array($flashType, ['success', 'error', 'warning', 'info']) ? $flashType : 'info';
    ?>
    <script>
    document.addEventListener("DOMContentLoaded", function () {
        Swal.fire({
            icon:  '<?= $icon ?>',
            title: '<?= addslashes($title) ?>',
            text:  '<?= addslashes($flashMsg) ?>',
            confirmButtonText: 'OK',
            confirmButtonColor: '#4f46e5',
            customClass: {
                popup:         'swal2-premium-popup',
                confirmButton: 'swal2-confirm-primary',
            },
            buttonsStyling: true,
        });
    });
    </script>
    <?php
}
