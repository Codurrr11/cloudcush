<?php
// includes/customers-init.php — Creates/upgrades customers + customer_addresses tables

require_once __DIR__ . '/db.php';

function ensureCustomersTable() {
    $pdo = getFrontendDB();

    // Base table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `customers` (
            `id`         INT(11)      NOT NULL AUTO_INCREMENT,
            `full_name`  VARCHAR(150) NOT NULL,
            `email`      VARCHAR(150) NOT NULL,
            `phone`      VARCHAR(20)  DEFAULT NULL,
            `password`   VARCHAR(255) NOT NULL,
            `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `email` (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // Add status column if missing
    $col = $pdo->query("SHOW COLUMNS FROM customers LIKE 'status'")->fetch();
    if (!$col) {
        $pdo->exec("ALTER TABLE `customers` ADD COLUMN `status` ENUM('active','inactive') NOT NULL DEFAULT 'active' AFTER `password`");
    }

    // Add gender column if missing
    $col = $pdo->query("SHOW COLUMNS FROM customers LIKE 'gender'")->fetch();
    if (!$col) {
        $pdo->exec("ALTER TABLE `customers` ADD COLUMN `gender` ENUM('male','female','other') DEFAULT NULL AFTER `phone`");
    }

    // Add date_of_birth column if missing
    $col = $pdo->query("SHOW COLUMNS FROM customers LIKE 'date_of_birth'")->fetch();
    if (!$col) {
        $pdo->exec("ALTER TABLE `customers` ADD COLUMN `date_of_birth` DATE DEFAULT NULL AFTER `gender`");
    }

    // Add profile_photo column if missing
    $col = $pdo->query("SHOW COLUMNS FROM customers LIKE 'profile_photo'")->fetch();
    if (!$col) {
        $pdo->exec("ALTER TABLE `customers` ADD COLUMN `profile_photo` VARCHAR(255) DEFAULT NULL AFTER `date_of_birth`");
    }

    // Add address (legacy text) column if missing
    $col = $pdo->query("SHOW COLUMNS FROM customers LIKE 'address'")->fetch();
    if (!$col) {
        $pdo->exec("ALTER TABLE `customers` ADD COLUMN `address` TEXT DEFAULT NULL AFTER `status`");
    }

    // Add last_login column if missing
    $col = $pdo->query("SHOW COLUMNS FROM customers LIKE 'last_login'")->fetch();
    if (!$col) {
        $pdo->exec("ALTER TABLE `customers` ADD COLUMN `last_login` TIMESTAMP NULL DEFAULT NULL AFTER `created_at`");
    }

    // ── Customer Addresses Table ──
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `customer_addresses` (
            `id`             INT(11)      NOT NULL AUTO_INCREMENT,
            `customer_id`    INT(11)      NOT NULL,
            `label`          VARCHAR(100) DEFAULT NULL,
            `full_name`      VARCHAR(150) NOT NULL,
            `phone`          VARCHAR(20)  DEFAULT NULL,
            `address_line_1` VARCHAR(255) NOT NULL,
            `address_line_2` VARCHAR(255) DEFAULT NULL,
            `city`           VARCHAR(100) NOT NULL,
            `state`          VARCHAR(100) NOT NULL,
            `country`        VARCHAR(100) NOT NULL DEFAULT 'India',
            `zip_code`       VARCHAR(20)  NOT NULL,
            `address_type`   ENUM('billing','shipping') NOT NULL DEFAULT 'shipping',
            `is_default`     TINYINT(1)   NOT NULL DEFAULT 0,
            `created_at`     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at`     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_ca_customer` (`customer_id`),
            CONSTRAINT `fk_ca_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // ── Add customer_id FK to orders table if missing ──
    $col = $pdo->query("SHOW COLUMNS FROM orders LIKE 'customer_id'")->fetch();
    if (!$col) {
        $pdo->exec("ALTER TABLE `orders` ADD COLUMN `customer_id` INT(11) DEFAULT NULL AFTER `id`");
        $pdo->exec("ALTER TABLE `orders` ADD KEY `idx_order_customer` (`customer_id`)");
    }
}

ensureCustomersTable();
