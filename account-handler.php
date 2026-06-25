<?php
// account-handler.php — Handles profile, password, and address CRUD for customer account
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/customers-init.php';

// Must be logged in
if (empty($_SESSION['customer_id'])) {
    header('Location: login.php');
    exit;
}

$pdo         = getFrontendDB();
$customerId  = (int) $_SESSION['customer_id'];
$action      = $_POST['action'] ?? '';

// ──────────────────────────────────────────────────────────────
// UPDATE PROFILE
// ──────────────────────────────────────────────────────────────
if ($action === 'update_profile') {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName  = trim($_POST['last_name']  ?? '');
    $email     = trim($_POST['email']      ?? '');
    $phone     = trim($_POST['phone']      ?? '');
    $gender    = trim($_POST['gender']     ?? '');
    $dob       = trim($_POST['date_of_birth'] ?? '');

    $errors = [];

    if ($firstName === '') $errors[] = 'First name is required.';
    if ($lastName  === '') $errors[] = 'Last name is required.';

    if ($email === '') {
        $errors[] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email address.';
    } else {
        // Check duplicate email
        $chk = $pdo->prepare("SELECT id FROM customers WHERE email = ? AND id != ? LIMIT 1");
        $chk->execute([$email, $customerId]);
        if ($chk->fetch()) {
            $errors[] = 'This email is already in use by another account.';
        }
    }

    if ($phone !== '' && !preg_match('/^\+?[0-9\s\-]{7,15}$/', $phone)) {
        $errors[] = 'Invalid phone number format.';
    }

    $allowedGenders = ['male', 'female', 'other', ''];
    if (!in_array($gender, $allowedGenders)) $gender = '';

    if ($dob !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dob)) {
        $dob = '';
    }

    if (!empty($errors)) {
        $_SESSION['account_flash']      = implode(' ', $errors);
        $_SESSION['account_flash_type'] = 'error';
        header('Location: account.php?tab=account-details');
        exit;
    }

    $fullName = $firstName . ' ' . $lastName;

    $stmt = $pdo->prepare("
        UPDATE customers
        SET full_name = ?, email = ?, phone = ?, gender = ?, date_of_birth = ?
        WHERE id = ?
    ");
    $stmt->execute([
        $fullName,
        $email,
        $phone ?: null,
        $gender ?: null,
        $dob ?: null,
        $customerId
    ]);

    // Update session
    $_SESSION['customer_name']  = $fullName;
    $_SESSION['customer_email'] = $email;

    $_SESSION['account_flash']      = 'Profile updated successfully.';
    $_SESSION['account_flash_type'] = 'success';
    header('Location: account.php?tab=account-details');
    exit;
}

// ──────────────────────────────────────────────────────────────
// CHANGE PASSWORD
// ──────────────────────────────────────────────────────────────
if ($action === 'change_password') {
    $currentPw  = $_POST['current_password'] ?? '';
    $newPw      = $_POST['new_password']     ?? '';
    $confirmPw  = $_POST['confirm_password'] ?? '';

    $errors = [];

    if ($currentPw === '') $errors[] = 'Current password is required.';
    if ($newPw === '') {
        $errors[] = 'New password is required.';
    } elseif (strlen($newPw) < 8) {
        $errors[] = 'New password must be at least 8 characters.';
    }
    if ($newPw !== $confirmPw) $errors[] = 'Passwords do not match.';

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT password FROM customers WHERE id = ? LIMIT 1");
        $stmt->execute([$customerId]);
        $row = $stmt->fetch();

        if (!$row || !password_verify($currentPw, $row['password'])) {
            $errors[] = 'Current password is incorrect.';
        }
    }

    if (!empty($errors)) {
        $_SESSION['account_flash']      = implode(' ', $errors);
        $_SESSION['account_flash_type'] = 'error';
        header('Location: account.php?tab=account-details');
        exit;
    }

    $hashed = password_hash($newPw, PASSWORD_BCRYPT);
    $stmt   = $pdo->prepare("UPDATE customers SET password = ? WHERE id = ?");
    $stmt->execute([$hashed, $customerId]);

    $_SESSION['account_flash']      = 'Password changed successfully.';
    $_SESSION['account_flash_type'] = 'success';
    header('Location: account.php?tab=account-details');
    exit;
}

// ──────────────────────────────────────────────────────────────
// ADD ADDRESS
// ──────────────────────────────────────────────────────────────
if ($action === 'add_address') {
    $label    = trim($_POST['label']          ?? '');
    $fullName = trim($_POST['addr_full_name'] ?? '');
    $phone    = trim($_POST['addr_phone']     ?? '');
    $line1    = trim($_POST['address_line_1'] ?? '');
    $line2    = trim($_POST['address_line_2'] ?? '');
    $city     = trim($_POST['city']           ?? '');
    $state    = trim($_POST['state']          ?? '');
    $country  = trim($_POST['country']        ?? 'India');
    $zip      = trim($_POST['zip_code']       ?? '');
    $type     = trim($_POST['address_type']   ?? 'shipping');
    $default  = !empty($_POST['is_default']) ? 1 : 0;

    $errors = [];
    if ($fullName === '') $errors[] = 'Full name is required.';
    if ($line1    === '') $errors[] = 'Address line 1 is required.';
    if ($city     === '') $errors[] = 'City is required.';
    if ($state    === '') $errors[] = 'State is required.';
    if ($zip      === '') $errors[] = 'ZIP/Postal code is required.';

    if (!empty($errors)) {
        $_SESSION['account_flash']      = implode(' ', $errors);
        $_SESSION['account_flash_type'] = 'error';
        header('Location: account.php?tab=saved-addresses');
        exit;
    }

    // If setting as default, clear other defaults
    if ($default) {
        $pdo->prepare("UPDATE customer_addresses SET is_default = 0 WHERE customer_id = ?")->execute([$customerId]);
    }

    $stmt = $pdo->prepare("
        INSERT INTO customer_addresses
        (customer_id, label, full_name, phone, address_line_1, address_line_2, city, state, country, zip_code, address_type, is_default)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $customerId, $label ?: null, $fullName, $phone ?: null,
        $line1, $line2 ?: null, $city, $state, $country, $zip, $type, $default
    ]);

    $_SESSION['account_flash']      = 'Address added successfully.';
    $_SESSION['account_flash_type'] = 'success';
    header('Location: account.php?tab=saved-addresses');
    exit;
}

// ──────────────────────────────────────────────────────────────
// UPDATE ADDRESS
// ──────────────────────────────────────────────────────────────
if ($action === 'update_address') {
    $addrId   = (int) ($_POST['address_id']     ?? 0);
    $label    = trim($_POST['label']             ?? '');
    $fullName = trim($_POST['addr_full_name']    ?? '');
    $phone    = trim($_POST['addr_phone']        ?? '');
    $line1    = trim($_POST['address_line_1']    ?? '');
    $line2    = trim($_POST['address_line_2']    ?? '');
    $city     = trim($_POST['city']              ?? '');
    $state    = trim($_POST['state']             ?? '');
    $country  = trim($_POST['country']           ?? 'India');
    $zip      = trim($_POST['zip_code']          ?? '');
    $type     = trim($_POST['address_type']      ?? 'shipping');
    $default  = !empty($_POST['is_default']) ? 1 : 0;

    $errors = [];
    if (!$addrId)        $errors[] = 'Invalid address.';
    if ($fullName === '') $errors[] = 'Full name is required.';
    if ($line1    === '') $errors[] = 'Address line 1 is required.';
    if ($city     === '') $errors[] = 'City is required.';
    if ($state    === '') $errors[] = 'State is required.';
    if ($zip      === '') $errors[] = 'ZIP/Postal code is required.';

    if (!empty($errors)) {
        $_SESSION['account_flash']      = implode(' ', $errors);
        $_SESSION['account_flash_type'] = 'error';
        header('Location: account.php?tab=saved-addresses');
        exit;
    }

    // Verify ownership
    $chk = $pdo->prepare("SELECT id FROM customer_addresses WHERE id = ? AND customer_id = ?");
    $chk->execute([$addrId, $customerId]);
    if (!$chk->fetch()) {
        $_SESSION['account_flash']      = 'Address not found.';
        $_SESSION['account_flash_type'] = 'error';
        header('Location: account.php?tab=saved-addresses');
        exit;
    }

    if ($default) {
        $pdo->prepare("UPDATE customer_addresses SET is_default = 0 WHERE customer_id = ?")->execute([$customerId]);
    }

    $stmt = $pdo->prepare("
        UPDATE customer_addresses
        SET label = ?, full_name = ?, phone = ?, address_line_1 = ?, address_line_2 = ?,
            city = ?, state = ?, country = ?, zip_code = ?, address_type = ?, is_default = ?
        WHERE id = ? AND customer_id = ?
    ");
    $stmt->execute([
        $label ?: null, $fullName, $phone ?: null,
        $line1, $line2 ?: null, $city, $state, $country, $zip, $type, $default,
        $addrId, $customerId
    ]);

    $_SESSION['account_flash']      = 'Address updated successfully.';
    $_SESSION['account_flash_type'] = 'success';
    header('Location: account.php?tab=saved-addresses');
    exit;
}

// ──────────────────────────────────────────────────────────────
// DELETE ADDRESS
// ──────────────────────────────────────────────────────────────
if ($action === 'delete_address') {
    $addrId = (int) ($_POST['address_id'] ?? 0);

    if ($addrId) {
        $stmt = $pdo->prepare("DELETE FROM customer_addresses WHERE id = ? AND customer_id = ?");
        $stmt->execute([$addrId, $customerId]);

        $_SESSION['account_flash']      = 'Address removed.';
        $_SESSION['account_flash_type'] = 'success';
    }

    header('Location: account.php?tab=saved-addresses');
    exit;
}

// ──────────────────────────────────────────────────────────────
// SET DEFAULT ADDRESS
// ──────────────────────────────────────────────────────────────
if ($action === 'set_default_address') {
    $addrId = (int) ($_POST['address_id'] ?? 0);

    if ($addrId) {
        $pdo->prepare("UPDATE customer_addresses SET is_default = 0 WHERE customer_id = ?")->execute([$customerId]);
        $pdo->prepare("UPDATE customer_addresses SET is_default = 1 WHERE id = ? AND customer_id = ?")->execute([$addrId, $customerId]);

        $_SESSION['account_flash']      = 'Default address updated.';
        $_SESSION['account_flash_type'] = 'success';
    }

    header('Location: account.php?tab=saved-addresses');
    exit;
}

// ──────────────────────────────────────────────────────────────
if ($action === 'reset_password') {
    $newPw     = $_POST['new_password']     ?? '';
    $confirmPw = $_POST['confirm_password'] ?? '';

    $errors = [];

    if ($newPw === '') {
        $errors[] = 'New password is required.';
    } elseif (strlen($newPw) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }
    if ($newPw !== $confirmPw) {
        $errors[] = 'Passwords do not match.';
    }

    if (!empty($errors)) {
        $_SESSION['account_flash']      = implode(' ', $errors);
        $_SESSION['account_flash_type'] = 'error';
        header('Location: account.php?tab=account-details');
        exit;
    }

    $hashed = password_hash($newPw, PASSWORD_BCRYPT);
    $stmt   = $pdo->prepare("UPDATE customers SET password = ? WHERE id = ?");
    $stmt->execute([$hashed, $customerId]);

    $_SESSION['account_flash']      = 'Password set successfully. You can now use your new password to log in.';
    $_SESSION['account_flash_type'] = 'success';
    header('Location: account.php?tab=account-details');
    exit;
}

// Unknown action — redirect back
header('Location: account.php');
exit;
