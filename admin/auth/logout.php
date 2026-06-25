<?php
// admin/auth/logout.php
require_once __DIR__ . '/../config/config.php';
header('Location: ' . BASE_URL . 'handlers/auth/logout-handler.php');
exit;
