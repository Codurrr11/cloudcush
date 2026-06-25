<?php
// admin/config/settings-helper.php
// Centralized helper functions for the Admin Settings page.
// Backing table: `users` (id, name, email, password, role, created_at)
// This is the same table used by the login/auth system (admin/handlers/auth/login-handler.php).

require_once __DIR__ . '/database.php';

/**
 * Fetch a single admin/user row by ID.
 */
function getAdminUser(int $id): ?array {
    try {
        $db = getDBConnection();
        $stmt = $db->prepare("SELECT id, name, email, password, role, created_at FROM users WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch();
        return $user ?: null;
    } catch (\PDOException $e) {
        error_log("getAdminUser error: " . $e->getMessage());
        return null;
    }
}

/**
 * Check whether an email address is already used by a different user account.
 */
function isEmailTakenByOtherUser(string $email, int $excludeId): bool {
    try {
        $db = getDBConnection();
        $stmt = $db->prepare("SELECT id FROM users WHERE email = :email AND id != :id LIMIT 1");
        $stmt->execute(['email' => $email, 'id' => $excludeId]);
        return (bool) $stmt->fetch();
    } catch (\PDOException $e) {
        error_log("isEmailTakenByOtherUser error: " . $e->getMessage());
        // Fail safe: treat DB errors as "taken" so we never silently allow a duplicate.
        return true;
    }
}

/**
 * Update the admin's profile fields (name + email only).
 */
function updateAdminProfile(int $id, string $name, string $email): bool {
    try {
        $db = getDBConnection();
        $stmt = $db->prepare("UPDATE users SET name = :name, email = :email WHERE id = :id");
        return $stmt->execute(['name' => $name, 'email' => $email, 'id' => $id]);
    } catch (\PDOException $e) {
        error_log("updateAdminProfile error: " . $e->getMessage());
        return false;
    }
}

/**
 * Persist a new hashed password for the admin account.
 */
function updateAdminPassword(int $id, string $hashedPassword): bool {
    try {
        $db = getDBConnection();
        $stmt = $db->prepare("UPDATE users SET password = :password WHERE id = :id");
        return $stmt->execute(['password' => $hashedPassword, 'id' => $id]);
    } catch (\PDOException $e) {
        error_log("updateAdminPassword error: " . $e->getMessage());
        return false;
    }
}
