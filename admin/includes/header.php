<?php
// admin/includes/header.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/constants.php';
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?= isset($page_title) ? $page_title : 'CloudCush Admin Portal'; ?></title>

    <!-- Bootstrap 5.3 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Google Fonts: Merriweather, Familjen Grotesk, Fira Code -->
    <link href="https://fonts.googleapis.com/css2?family=Bellota:ital,wght@0,300;0,400;0,700;1,300;1,400;1,700&family=Bricolage+Grotesque:opsz,wght@12..96,200..800&family=Caveat:wght@400..700&family=Chivo:ital,wght@0,100..900;1,100..900&family=Familjen+Grotesk:ital,wght@0,400..700;1,400..700&family=Fira+Code&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=JetBrains+Mono:ital,wght@0,100..800;1,100..800&family=Karma:wght@300;400;500;600;700&family=Merriweather:ital,opsz,wght@0,18..144,300..900;1,18..144,300..900&display=swap" rel="stylesheet">

    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">

    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

    <!-- Lucide Icons (Premium outline SVG icons) -->
    <script src="https://cdn.jsdelivr.net/npm/lucide@latest/dist/umd/lucide.js"></script>

    <!-- Custom Admin Styling -->
    <link href="<?= BASE_URL ?>assets/css/main.css" rel="stylesheet">
    <?php if (isset($extra_css)): ?>
        <link href="<?= BASE_URL ?>assets/css/<?= htmlspecialchars($extra_css) ?>" rel="stylesheet">
    <?php endif; ?>
</head>

<body class="<?= isset($body_class) ? $body_class : 'bg-light'; ?>">
