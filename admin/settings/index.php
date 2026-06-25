<?php
require_once __DIR__ . '/../includes/auth-check.php';
require_once __DIR__ . '/../config/settings-helper.php';

$adminId = (int) ($_SESSION['user_id'] ?? 0);
$admin   = getAdminUser($adminId);

if (!$admin) {
    $_SESSION['flash_message'] = 'Unable to load your admin account. Please sign in again.';
    $_SESSION['flash_type']    = 'error';
    header('Location: ' . BASE_URL . 'auth/logout.php');
    exit;
}

// POST submission handler — two independent forms on this page (profile / password)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formAction = $_POST['form_action'] ?? '';

    if ($formAction === 'update_profile') {
        $errors = [];

        $name  = trim(strip_tags($_POST['name'] ?? ''));
        $email = trim((string) filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));

        if (empty($name)) {
            $errors[] = 'Full Name is required.';
        } elseif (mb_strlen($name) > 100) {
            $errors[] = 'Full Name must be under 100 characters.';
        }

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'A valid Email Address is required.';
        } elseif (mb_strlen($email) > 150) {
            $errors[] = 'Email Address must be under 150 characters.';
        } elseif (isEmailTakenByOtherUser($email, $adminId)) {
            $errors[] = 'This email address is already in use by another account.';
        }

        if (empty($errors)) {
            if (updateAdminProfile($adminId, $name, $email)) {
                $_SESSION['user_name'] = $name; // keep navbar/session in sync
                $_SESSION['flash_message'] = 'Profile updated successfully.';
                $_SESSION['flash_type']    = 'success';
                header('Location: ' . BASE_URL . 'settings/');
                exit;
            } else {
                $_SESSION['flash_message'] = 'Failed to update profile. Please try again.';
                $_SESSION['flash_type']    = 'error';
            }
        } else {
            $_SESSION['flash_message'] = implode(' ', $errors);
            $_SESSION['flash_type']    = 'error';
        }

        // Keep entered values visible on the form after a validation/db error
        $admin['name']  = $name;
        $admin['email'] = $email;

    } elseif ($formAction === 'update_password') {
        $errors = [];

        $currentPassword = (string) ($_POST['current_password'] ?? '');
        $newPassword      = (string) ($_POST['new_password'] ?? '');
        $confirmPassword  = (string) ($_POST['confirm_password'] ?? '');

        if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
            $errors[] = 'All password fields are required.';
        } elseif (!password_verify($currentPassword, $admin['password'])) {
            $errors[] = 'Current password is incorrect.';
        } elseif (strlen($newPassword) < 8) {
            $errors[] = 'New password must be at least 8 characters long.';
        } elseif ($newPassword !== $confirmPassword) {
            $errors[] = 'New password and confirmation do not match.';
        } elseif (password_verify($newPassword, $admin['password'])) {
            $errors[] = 'New password must be different from your current password.';
        }

        if (empty($errors)) {
            $hashed = password_hash($newPassword, PASSWORD_BCRYPT);
            if (updateAdminPassword($adminId, $hashed)) {
                $_SESSION['flash_message'] = 'Password updated successfully.';
                $_SESSION['flash_type']    = 'success';
                header('Location: ' . BASE_URL . 'settings/');
                exit;
            } else {
                $_SESSION['flash_message'] = 'Failed to update password. Please try again.';
                $_SESSION['flash_type']    = 'error';
            }
        } else {
            $_SESSION['flash_message'] = implode(' ', $errors);
            $_SESSION['flash_type']    = 'error';
        }
    }
}

$page_title  = 'CloudCush Admin - Settings';
$active_page = 'settings';

// Avatar initials (e.g. "Administrator" -> "A", "Sarah Varma" -> "SV")
$nameParts = preg_split('/\s+/', trim($admin['name']));
$initials  = '';
foreach (array_slice($nameParts, 0, 2) as $part) {
    if ($part !== '') $initials .= mb_strtoupper(mb_substr($part, 0, 1));
}
if ($initials === '') $initials = 'A';

$roleBadgeClass = ($admin['role'] === 'admin') ? 'info' : 'secondary';
$memberSince    = !empty($admin['created_at']) ? date('M d, Y', strtotime($admin['created_at'])) : '—';

include __DIR__ . '/../includes/header.php';
?>

<div id="wrapper">
    <?php include __DIR__ . '/../includes/sidebar.php'; ?>

    <div id="page-content-wrapper">
        <?php include __DIR__ . '/../includes/navbar.php'; ?>

        <div class="container-fluid px-0 py-2">

            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
                <div>
                    <h1 class="h4 fw-bold mb-1 page-heading">Account Settings</h1>
                    <p class="text-secondary mb-0 fs-0-82">Manage your admin profile information and login security.</p>
                </div>
            </div>

            <div class="row g-4">

                <!-- Account Information -->
                <div class="col-12 col-xl-7">
                    <div class="card-premium p-4">
                        <div class="d-flex align-items-center gap-2 mb-4 border-bottom pb-2">
                            <i data-lucide="user-cog" class="text-primary" style="width: 18px; height: 18px;"></i>
                            <span class="form-section-label mb-0" style="font-size:0.88rem;">Account Information</span>
                        </div>

                        <div class="d-flex align-items-center gap-3 mb-4">
                            <div class="customer-avatar-lg flex-shrink-0"><?= htmlspecialchars($initials) ?></div>
                            <div>
                                <div class="fw-bold text-dark fs-0-95"><?= htmlspecialchars($admin['name']) ?></div>
                                <div class="d-flex align-items-center gap-2 mt-1">
                                    <span class="badge-status <?= $roleBadgeClass ?>"><?= htmlspecialchars(ucfirst($admin['role'])) ?></span>
                                    <span class="text-secondary fs-0-72">Member since <?= htmlspecialchars($memberSince) ?></span>
                                </div>
                            </div>
                        </div>

                        <form action="" method="POST" novalidate>
                            <input type="hidden" name="form_action" value="update_profile">
                            <div class="d-flex flex-column gap-3">

                                <div class="form-group-wrap">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <i data-lucide="user" class="text-muted" style="width: 14px; height: 14px;"></i>
                                        <label class="form-label-premium mb-0" for="settings_name">
                                            Full Name <span class="req">*</span>
                                        </label>
                                    </div>
                                    <input type="text" id="settings_name" name="name" class="form-control-premium" required
                                           placeholder="e.g. Administrator"
                                           value="<?= htmlspecialchars($admin['name']) ?>">
                                </div>

                                <div class="form-group-wrap">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <i data-lucide="mail" class="text-muted" style="width: 14px; height: 14px;"></i>
                                        <label class="form-label-premium mb-0" for="settings_email">
                                            Email Address <span class="req">*</span>
                                            <span class="hint">— used as your login username</span>
                                        </label>
                                    </div>
                                    <input type="email" id="settings_email" name="email" class="form-control-premium" required
                                           placeholder="admin@cloudcush.com"
                                           value="<?= htmlspecialchars($admin['email']) ?>">
                                </div>

                                <div class="form-group-wrap">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <i data-lucide="shield-check" class="text-muted" style="width: 14px; height: 14px;"></i>
                                        <label class="form-label-premium mb-0">
                                            Role <span class="hint">— read only</span>
                                        </label>
                                    </div>
                                    <input type="text" class="form-control-premium" value="<?= htmlspecialchars(ucfirst($admin['role'])) ?>" disabled>
                                </div>

                            </div>

                            <div class="d-flex gap-2 max-width-320-px mt-4">
                                <button type="submit" class="btn btn-premium-primary flex-grow-1 d-flex align-items-center justify-content-center gap-2 py-2">
                                    <i data-lucide="check" class="icon-lg"></i>
                                    <span>Save Changes</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Security / Password -->
                <div class="col-12 col-xl-5">
                    <div class="card-premium p-4">
                        <div class="d-flex align-items-center gap-2 mb-4 border-bottom pb-2">
                            <i data-lucide="lock-keyhole" class="text-primary" style="width: 18px; height: 18px;"></i>
                            <span class="form-section-label mb-0" style="font-size:0.88rem;">Change Password</span>
                        </div>

                        <form action="" method="POST" novalidate id="passwordChangeForm">
                            <input type="hidden" name="form_action" value="update_password">
                            <div class="d-flex flex-column gap-3">

                                <div class="form-group-wrap">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <i data-lucide="lock" class="text-muted" style="width: 14px; height: 14px;"></i>
                                        <label class="form-label-premium mb-0" for="current_password">
                                            Current Password <span class="req">*</span>
                                        </label>
                                    </div>
                                    <div class="settings-pw-wrap">
                                        <input type="password" id="current_password" name="current_password" class="form-control-premium settings-pw-input" required
                                               placeholder="••••••••" autocomplete="current-password">
                                        <button type="button" class="settings-pw-toggle" data-target="current_password" title="Show/hide password">
                                            <i data-lucide="eye" class="icon-sm"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="form-group-wrap">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <i data-lucide="key-round" class="text-muted" style="width: 14px; height: 14px;"></i>
                                        <label class="form-label-premium mb-0" for="new_password">
                                            New Password <span class="req">*</span>
                                            <span class="hint">— min. 8 characters</span>
                                        </label>
                                    </div>
                                    <div class="settings-pw-wrap">
                                        <input type="password" id="new_password" name="new_password" class="form-control-premium settings-pw-input" required
                                               minlength="8" placeholder="••••••••" autocomplete="new-password">
                                        <button type="button" class="settings-pw-toggle" data-target="new_password" title="Show/hide password">
                                            <i data-lucide="eye" class="icon-sm"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="form-group-wrap">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <i data-lucide="key-round" class="text-muted" style="width: 14px; height: 14px;"></i>
                                        <label class="form-label-premium mb-0" for="confirm_password">
                                            Confirm New Password <span class="req">*</span>
                                        </label>
                                    </div>
                                    <div class="settings-pw-wrap">
                                        <input type="password" id="confirm_password" name="confirm_password" class="form-control-premium settings-pw-input" required
                                               minlength="8" placeholder="••••••••" autocomplete="new-password">
                                        <button type="button" class="settings-pw-toggle" data-target="confirm_password" title="Show/hide password">
                                            <i data-lucide="eye" class="icon-sm"></i>
                                        </button>
                                    </div>
                                    <div class="fs-0-72 text-secondary mt-1" id="pwMatchHint"></div>
                                </div>

                            </div>

                            <div class="d-flex gap-2 max-width-320-px mt-4">
                                <button type="submit" class="btn btn-premium-primary flex-grow-1 d-flex align-items-center justify-content-center gap-2 py-2">
                                    <i data-lucide="shield-check" class="icon-lg"></i>
                                    <span>Update Password</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>

        </div><!-- /container-fluid -->
    </div><!-- /page-content-wrapper -->
</div><!-- /wrapper -->

<style>
.settings-pw-wrap { position: relative; display: flex; align-items: center; }
.settings-pw-wrap .settings-pw-input { padding-right: 2.4rem; }
.settings-pw-toggle {
    position: absolute;
    right: 8px;
    width: 26px;
    height: 26px;
    border-radius: 6px;
    border: none;
    background: transparent;
    color: var(--text-secondary);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.15s ease;
    flex-shrink: 0;
}
.settings-pw-toggle:hover { background: rgba(79, 70, 229, 0.08); color: #4f46e5; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Password visibility toggles (scoped to this page only)
    document.querySelectorAll('.settings-pw-toggle').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const input = document.getElementById(this.dataset.target);
            if (!input) return;
            const showing = input.type === 'text';
            input.type = showing ? 'password' : 'text';
            this.innerHTML = showing
                ? '<i data-lucide="eye" class="icon-sm"></i>'
                : '<i data-lucide="eye-off" class="icon-sm"></i>';
            if (typeof lucide !== 'undefined') lucide.createIcons();
        });
    });

    // Live "passwords match" hint (cosmetic only — real check happens server-side)
    const newPw = document.getElementById('new_password');
    const confirmPw = document.getElementById('confirm_password');
    const hint = document.getElementById('pwMatchHint');
    function checkMatch() {
        if (!hint) return;
        if (!confirmPw.value) { hint.textContent = ''; return; }
        hint.textContent = (newPw.value === confirmPw.value) ? 'Passwords match.' : 'Passwords do not match.';
        hint.style.color = (newPw.value === confirmPw.value) ? '#059669' : '#dc2626';
    }
    if (newPw && confirmPw) {
        newPw.addEventListener('input', checkMatch);
        confirmPw.addEventListener('input', checkMatch);
    }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
