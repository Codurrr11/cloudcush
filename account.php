<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/customers-init.php';

// ── Session guard: must be logged in ──
if (empty($_SESSION['customer_id'])) {
    header('Location: login.php');
    exit;
}

$pdo        = getFrontendDB();
$customerId = (int) $_SESSION['customer_id'];

// ── Fetch customer record ──
$stmt = $pdo->prepare('SELECT * FROM customers WHERE id = ? LIMIT 1');
$stmt->execute([$customerId]);
$customer = $stmt->fetch();

if (!$customer) {
    // Corrupted session — force re-login
    unset($_SESSION['customer_id'], $_SESSION['customer_name'], $_SESSION['customer_email']);
    header('Location: login.php');
    exit;
}

// ── Fetch saved addresses ──
$addrStmt = $pdo->prepare('SELECT * FROM customer_addresses WHERE customer_id = ? ORDER BY is_default DESC, created_at DESC');
$addrStmt->execute([$customerId]);
$addresses = $addrStmt->fetchAll();

// ── Derive display values ──
$fullName   = htmlspecialchars($customer['full_name']);
$nameParts  = explode(' ', $customer['full_name'], 2);
$firstName  = htmlspecialchars($nameParts[0]);
$lastName   = htmlspecialchars($nameParts[1] ?? '');
$email      = htmlspecialchars($customer['email']);
$phone      = htmlspecialchars($customer['phone'] ?? '');
$gender     = $customer['gender'] ?? '';
$dob        = $customer['date_of_birth'] ?? '';
$address    = htmlspecialchars($customer['address'] ?? '');
$createdAt  = date('F j, Y', strtotime($customer['created_at']));
$lastLogin  = $customer['last_login'] ? date('F j, Y \a\t g:i A', strtotime($customer['last_login'])) : 'First visit';

// Avatar initials
$initials = strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[1] ?? '', 0, 1));

// Guest-checkout flag: only show "Set a New Password" if account still has the default password 123456
$hasDefaultPw = !empty($customer['password']) && password_verify('123456', $customer['password']);

// ── Active tab from query param ──
$activeTab = $_GET['tab'] ?? 'profile-overview';
$allowedTabs = ['profile-overview', 'account-details', 'order-history', 'saved-addresses'];
if (!in_array($activeTab, $allowedTabs)) $activeTab = 'profile-overview';

// ── Flash messages ──
$flashMsg  = $_SESSION['account_flash'] ?? '';
$flashType = $_SESSION['account_flash_type'] ?? 'success';
unset($_SESSION['account_flash'], $_SESSION['account_flash_type']);

$use_auth_styles = true;
$page_title = 'My Account — CloudCush';
include 'includes/head.php';
include 'includes/header.php';
?>

<main class="account-page-wrapper">
  <div class="account-container">

    <!-- ── SIDEBAR NAVIGATION ── -->
    <aside class="account-sidebar">
      <div class="account-profile-header">
        <div class="account-avatar" aria-hidden="true"><?php echo $initials; ?></div>
        <div class="account-user-info">
          <h2 class="account-user-name"><?php echo $fullName; ?></h2>
          <span class="account-user-email"><?php echo $email; ?></span>
        </div>
      </div>

      <nav aria-label="Account sections">
        <ul class="account-nav-list">
          <li>
            <button type="button" class="account-nav-btn<?php echo $activeTab === 'profile-overview' ? ' active' : ''; ?>" data-target="profile-overview">
              <i class="ri-dashboard-line"></i> Profile Overview
            </button>
          </li>
          <li>
            <button type="button" class="account-nav-btn<?php echo $activeTab === 'account-details' ? ' active' : ''; ?>" data-target="account-details">
              <i class="ri-user-line"></i> Account Details
            </button>
          </li>
          <li>
            <button type="button" class="account-nav-btn<?php echo $activeTab === 'order-history' ? ' active' : ''; ?>" data-target="order-history">
              <i class="ri-shopping-bag-line"></i> Order History
            </button>
          </li>
          <li>
            <button type="button" class="account-nav-btn<?php echo $activeTab === 'saved-addresses' ? ' active' : ''; ?>" data-target="saved-addresses">
              <i class="ri-map-pin-line"></i> Saved Addresses
            </button>
          </li>
          <li>
            <a href="logout.php" class="account-nav-btn" style="text-decoration: none;">
              <i class="ri-logout-box-line"></i> Logout
            </a>
          </li>
        </ul>
      </nav>
    </aside>

    <!-- ── MAIN CONTENT CARD ── -->
    <section class="account-main-content">

      <?php if ($flashMsg): ?>
        <div class="account-flash account-flash-<?php echo $flashType; ?>" id="accountFlash">
          <i class="ri-<?php echo $flashType === 'success' ? 'checkbox-circle' : 'error-warning'; ?>-line"></i>
          <span><?php echo htmlspecialchars($flashMsg); ?></span>
          <button type="button" class="account-flash-close" onclick="document.getElementById('accountFlash').remove();">&times;</button>
        </div>
      <?php endif; ?>

      <!-- PANEL 1: PROFILE OVERVIEW -->
      <div class="account-panel<?php echo $activeTab === 'profile-overview' ? ' active' : ''; ?>" id="profile-overview">
        <h1 class="account-panel-title">Welcome back, <?php echo $firstName; ?>.</h1>
        <p class="account-panel-subtitle">Manage your orders, subscriptions and personalized baby stage care plans.</p>

        <!-- Profile Info Grid -->
        <div class="account-summary-grid">
          <div class="account-summary-card">
            <span class="account-summary-label">Full Name</span>
            <span class="account-summary-value"><?php echo $fullName; ?></span>
          </div>
          <div class="account-summary-card">
            <span class="account-summary-label">Email</span>
            <span class="account-summary-value" style="font-size: 14px; word-break: break-all;"><?php echo $email; ?></span>
          </div>
          <div class="account-summary-card">
            <span class="account-summary-label">Phone</span>
            <span class="account-summary-value"><?php echo $phone ?: '—'; ?></span>
          </div>
        </div>

        <div class="account-summary-grid" style="margin-top: 16px;">
          <div class="account-summary-card">
            <span class="account-summary-label">Member Since</span>
            <span class="account-summary-value" style="font-size: 14px;"><?php echo $createdAt; ?></span>
          </div>
          <div class="account-summary-card">
            <span class="account-summary-label">Last Login</span>
            <span class="account-summary-value" style="font-size: 14px;"><?php echo $lastLogin; ?></span>
          </div>
          <div class="account-summary-card">
            <span class="account-summary-label">Saved Addresses</span>
            <span class="account-summary-value"><?php echo count($addresses); ?></span>
          </div>
        </div>

        <?php if ($address): ?>
        <div style="margin-top: 24px;">
          <h3 class="auth-field-label" style="margin-bottom: 8px;">Address on File</h3>
          <p style="font-size: 13px; line-height: 1.6; color: var(--text-light);"><?php echo nl2br($address); ?></p>
        </div>
        <?php endif; ?>
      </div>

      <!-- PANEL 2: ACCOUNT DETAILS -->
      <div class="account-panel<?php echo $activeTab === 'account-details' ? ' active' : ''; ?>" id="account-details">
        <h1 class="account-panel-title">Account Details</h1>
        <p class="account-panel-subtitle">Update your profile settings and personal information.</p>

        <form class="auth-form-fields" action="account-handler.php" method="POST" style="max-width: 100%; gap: 20px;">
          <input type="hidden" name="action" value="update_profile">
          <div class="account-form-grid">
            <div class="auth-field-group">
              <label for="profile-first-name" class="auth-field-label">First Name</label>
              <div class="auth-field-wrap">
                <i class="ri-user-line auth-field-icon"></i>
                <input type="text" id="profile-first-name" name="first_name" class="auth-field-input" value="<?php echo $firstName; ?>" required>
              </div>
            </div>
            <div class="auth-field-group">
              <label for="profile-last-name" class="auth-field-label">Last Name</label>
              <div class="auth-field-wrap">
                <input type="text" id="profile-last-name" name="last_name" class="auth-field-input auth-field-input--no-icon" value="<?php echo $lastName; ?>" required>
              </div>
            </div>
            <div class="auth-field-group account-form-full">
              <label for="profile-email" class="auth-field-label">Email Address</label>
              <div class="auth-field-wrap">
                <i class="ri-mail-line auth-field-icon"></i>
                <input type="email" id="profile-email" name="email" class="auth-field-input" value="<?php echo $email; ?>" required>
              </div>
            </div>
            <div class="auth-field-group">
              <label for="profile-phone" class="auth-field-label">Phone</label>
              <div class="auth-field-wrap">
                <i class="ri-phone-line auth-field-icon"></i>
                <input type="tel" id="profile-phone" name="phone" class="auth-field-input" value="<?php echo $phone; ?>">
              </div>
            </div>
            <div class="auth-field-group">
              <label for="profile-gender" class="auth-field-label">Gender</label>
              <div class="auth-field-wrap">
                <i class="ri-genderless-line auth-field-icon"></i>
                <select id="profile-gender" name="gender" class="auth-field-input">
                  <option value="">Prefer not to say</option>
                  <option value="male" <?php echo $gender === 'male' ? 'selected' : ''; ?>>Male</option>
                  <option value="female" <?php echo $gender === 'female' ? 'selected' : ''; ?>>Female</option>
                  <option value="other" <?php echo $gender === 'other' ? 'selected' : ''; ?>>Other</option>
                </select>
              </div>
            </div>
            <div class="auth-field-group account-form-full">
              <label for="profile-dob" class="auth-field-label">Date of Birth</label>
              <div class="auth-field-wrap">
                <i class="ri-calendar-line auth-field-icon"></i>
                <input type="date" id="profile-dob" name="date_of_birth" class="auth-field-input" value="<?php echo htmlspecialchars($dob); ?>">
              </div>
            </div>
          </div>

          <button type="submit" class="auth-submit-btn" style="max-width: 200px; margin-top: 10px;">
            <span class="auth-submit-text">Save Changes</span>
          </button>
        </form>

        <!-- ── Change Password ── -->
        <div style="margin-top: 40px; padding-top: 30px; border-top: 1px solid var(--border-light, #e8e5e0);">
          <h2 class="account-panel-title" style="font-size: 20px;">Change Password</h2>
          <p class="account-panel-subtitle">Ensure your account stays secure by updating your password regularly.</p>

          <?php if ($hasDefaultPw): ?>
          <!-- Notice for guest-created accounts -->
          <div style="display:flex; align-items:flex-start; gap:12px; padding:14px 16px; margin-bottom:24px; background:#fffbeb; border:1px solid #fde68a; border-radius:12px;">
            <i class="ri-information-line" style="font-size:18px; color:#d97706; flex-shrink:0; margin-top:1px;"></i>
            <p style="font-size:13px; color:#92400e; margin:0; line-height:1.5;">
              Your account was created during guest checkout with a temporary password (<strong>123456</strong>).
              You don't need to enter it — just set your new password below.
            </p>
          </div>

          <form class="auth-form-fields" action="account-handler.php" method="POST" style="max-width: 100%; gap: 20px;">
            <input type="hidden" name="action" value="reset_password">
            <div class="account-form-grid">
              <div class="auth-field-group">
                <label for="new-password" class="auth-field-label">New Password <span style="color:#d97706; font-size:11px;">(min. 6 characters)</span></label>
                <div class="auth-field-wrap">
                  <i class="ri-lock-password-line auth-field-icon"></i>
                  <input type="password" id="new-password" name="new_password" class="auth-field-input" placeholder="Choose a strong password" required minlength="6">
                </div>
              </div>
              <div class="auth-field-group">
                <label for="confirm-password" class="auth-field-label">Confirm New Password</label>
                <div class="auth-field-wrap">
                  <i class="ri-lock-password-line auth-field-icon"></i>
                  <input type="password" id="confirm-password" name="confirm_password" class="auth-field-input" placeholder="Repeat your password" required>
                </div>
              </div>
            </div>
            <button type="submit" class="auth-submit-btn" style="max-width: 220px; margin-top: 10px;">
              <span class="auth-submit-text">Set Password</span>
            </button>
          </form>

          <?php else: ?>

          <form class="auth-form-fields" action="account-handler.php" method="POST" style="max-width: 100%; gap: 20px;">
            <input type="hidden" name="action" value="change_password">
            <div class="account-form-grid">
              <div class="auth-field-group account-form-full">
                <label for="current-password" class="auth-field-label">Current Password</label>
                <div class="auth-field-wrap">
                  <i class="ri-lock-line auth-field-icon"></i>
                  <input type="password" id="current-password" name="current_password" class="auth-field-input" required>
                </div>
              </div>
              <div class="auth-field-group">
                <label for="new-password" class="auth-field-label">New Password</label>
                <div class="auth-field-wrap">
                  <i class="ri-lock-password-line auth-field-icon"></i>
                  <input type="password" id="new-password" name="new_password" class="auth-field-input" placeholder="Min. 8 characters" required>
                </div>
              </div>
              <div class="auth-field-group">
                <label for="confirm-password" class="auth-field-label">Confirm New Password</label>
                <div class="auth-field-wrap">
                  <i class="ri-lock-password-line auth-field-icon"></i>
                  <input type="password" id="confirm-password" name="confirm_password" class="auth-field-input" required>
                </div>
              </div>
            </div>
            <button type="submit" class="auth-submit-btn" style="max-width: 220px; margin-top: 10px;">
              <span class="auth-submit-text">Update Password</span>
            </button>
          </form>

          <?php endif; ?>
        </div>

      </div><!-- /PANEL 2 -->

      <!-- PANEL 3: ORDER HISTORY -->
      <div class="account-panel<?php echo $activeTab === 'order-history' ? ' active' : ''; ?>" id="order-history">
        <h1 class="account-panel-title">Order History</h1>
        <p class="account-panel-subtitle">Track and review your past organic cloud diaper purchases.</p>

        <?php
        // Fetch orders for this customer
        $orders = [];
        try {
            $orderStmt = $pdo->prepare('SELECT * FROM orders WHERE customer_id = ? ORDER BY created_at DESC');
            $orderStmt->execute([$customerId]);
            $orders = $orderStmt->fetchAll();
        } catch (PDOException $e) {
            // orders table may not have customer_id yet — silently ignore
        }
        ?>

        <?php if (empty($orders)): ?>
          <div style="text-align: center; padding: 60px 20px;">
            <i class="ri-shopping-bag-line" style="font-size: 48px; color: var(--text-light); opacity: 0.4;"></i>
            <p style="margin-top: 16px; color: var(--text-light); font-size: 14px;">You haven't placed any orders yet.</p>
            <a href="products.php" class="auth-submit-btn" style="max-width: 200px; margin: 20px auto 0; display: inline-block; text-decoration: none; text-align: center;">Browse Products</a>
          </div>
        <?php else: ?>
          <div class="order-table-wrap">
            <table class="order-table">
              <thead>
                <tr>
                  <th>Order #</th>
                  <th>Date</th>
                  <th>Items</th>
                  <th>Status</th>
                  <th>Total</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($orders as $order): ?>
                <tr>
                  <td><strong>#<?php echo htmlspecialchars($order['id']); ?></strong></td>
                  <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                  <td><?php echo (int)($order['item_count'] ?? 1); ?></td>
                  <td>
                    <span class="order-status <?php echo strtolower($order['status'] ?? 'pending'); ?>">
                      <?php echo htmlspecialchars(ucfirst($order['status'] ?? 'Pending')); ?>
                    </span>
                  </td>
                  <td><strong>₹<?php echo number_format($order['total_amount'] ?? 0, 2); ?></strong></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>

      <!-- PANEL 4: SAVED ADDRESSES -->
      <div class="account-panel<?php echo $activeTab === 'saved-addresses' ? ' active' : ''; ?>" id="saved-addresses">
        <h1 class="account-panel-title">Saved Addresses</h1>
        <p class="account-panel-subtitle">Manage your shipping destinations for hassle-free subscription deliveries.</p>

        <div class="addresses-grid">
          <?php if (empty($addresses)): ?>
            <!-- No addresses yet -->
          <?php else: ?>
            <?php foreach ($addresses as $addr): ?>
            <div class="address-card<?php echo $addr['is_default'] ? ' default' : ''; ?>">
              <?php if ($addr['is_default']): ?>
                <span class="address-badge"><i class="ri-checkbox-circle-fill"></i> Default shipping</span>
              <?php endif; ?>
              <h3 class="address-name"><?php echo htmlspecialchars($addr['label'] ?: $addr['full_name']); ?></h3>
              <p class="address-details">
                <?php echo htmlspecialchars($addr['full_name']); ?><br>
                <?php echo htmlspecialchars($addr['address_line_1']); ?>
                <?php if ($addr['address_line_2']): ?><br><?php echo htmlspecialchars($addr['address_line_2']); ?><?php endif; ?><br>
                <?php echo htmlspecialchars($addr['city'] . ', ' . $addr['state']); ?><br>
                <?php echo htmlspecialchars($addr['country'] . ' — ' . $addr['zip_code']); ?>
                <?php if ($addr['phone']): ?><br><i class="ri-phone-line" style="font-size: 12px;"></i> <?php echo htmlspecialchars($addr['phone']); ?><?php endif; ?>
              </p>
              <div class="address-actions">
                <button class="address-action-btn" onclick="openEditAddressModal(<?php echo htmlspecialchars(json_encode($addr)); ?>)">Edit</button>
                <?php if (!$addr['is_default']): ?>
                  <form action="account-handler.php" method="POST" style="display:inline;">
                    <input type="hidden" name="action" value="set_default_address">
                    <input type="hidden" name="address_id" value="<?php echo $addr['id']; ?>">
                    <button type="submit" class="address-action-btn">Set Default</button>
                  </form>
                <?php endif; ?>
                <form action="account-handler.php" method="POST" style="display:inline;" onsubmit="return confirm('Remove this address?');">
                  <input type="hidden" name="action" value="delete_address">
                  <input type="hidden" name="address_id" value="<?php echo $addr['id']; ?>">
                  <button type="submit" class="address-action-btn delete">Remove</button>
                </form>
              </div>
            </div>
            <?php endforeach; ?>
          <?php endif; ?>

          <!-- Add New Address Button -->
          <button type="button" class="address-card-add" onclick="openAddAddressModal()">
            <i class="ri-add-line"></i>
            <span>Add New Address</span>
          </button>
        </div>
      </div>

    </section>
  </div>
</main>

<!-- ── ADD ADDRESS MODAL ── -->
<div class="account-modal-overlay" id="addAddressModal">
  <div class="account-modal">
    <div class="account-modal-header">
      <h2>Add New Address</h2>
      <button type="button" class="account-modal-close" onclick="closeModal('addAddressModal')">&times;</button>
    </div>
    <form action="account-handler.php" method="POST">
      <input type="hidden" name="action" value="add_address">
      <div class="account-form-grid" style="padding: 20px;">
        <div class="auth-field-group">
          <label class="auth-field-label">Label (optional)</label>
          <div class="auth-field-wrap">
            <input type="text" name="label" class="auth-field-input auth-field-input--no-icon" placeholder="e.g. Home, Office">
          </div>
        </div>
        <div class="auth-field-group">
          <label class="auth-field-label">Full Name *</label>
          <div class="auth-field-wrap">
            <input type="text" name="addr_full_name" class="auth-field-input auth-field-input--no-icon" value="<?php echo $fullName; ?>" required>
          </div>
        </div>
        <div class="auth-field-group account-form-full">
          <label class="auth-field-label">Phone</label>
          <div class="auth-field-wrap">
            <input type="tel" name="addr_phone" class="auth-field-input auth-field-input--no-icon" value="<?php echo $phone; ?>">
          </div>
        </div>
        <div class="auth-field-group account-form-full">
          <label class="auth-field-label">Address Line 1 *</label>
          <div class="auth-field-wrap">
            <input type="text" name="address_line_1" class="auth-field-input auth-field-input--no-icon" placeholder="House no., Street" required>
          </div>
        </div>
        <div class="auth-field-group account-form-full">
          <label class="auth-field-label">Address Line 2</label>
          <div class="auth-field-wrap">
            <input type="text" name="address_line_2" class="auth-field-input auth-field-input--no-icon" placeholder="Landmark, Area">
          </div>
        </div>
        <div class="auth-field-group">
          <label class="auth-field-label">City *</label>
          <div class="auth-field-wrap">
            <input type="text" name="city" class="auth-field-input auth-field-input--no-icon" required>
          </div>
        </div>
        <div class="auth-field-group">
          <label class="auth-field-label">State *</label>
          <div class="auth-field-wrap">
            <input type="text" name="state" class="auth-field-input auth-field-input--no-icon" required>
          </div>
        </div>
        <div class="auth-field-group">
          <label class="auth-field-label">ZIP / Postal Code *</label>
          <div class="auth-field-wrap">
            <input type="text" name="zip_code" class="auth-field-input auth-field-input--no-icon" required>
          </div>
        </div>
        <div class="auth-field-group">
          <label class="auth-field-label">Country</label>
          <div class="auth-field-wrap">
            <input type="text" name="country" class="auth-field-input auth-field-input--no-icon" value="India">
          </div>
        </div>
        <div class="auth-field-group account-form-full" style="flex-direction:row; align-items:center; gap:8px;">
          <input type="checkbox" name="is_default" id="add-addr-default" value="1">
          <label for="add-addr-default" class="auth-field-label" style="margin:0;">Set as default shipping address</label>
        </div>
      </div>
      <div class="account-modal-footer">
        <button type="button" class="address-action-btn" onclick="closeModal('addAddressModal')">Cancel</button>
        <button type="submit" class="auth-submit-btn" style="width: auto; min-width: 130px; max-width: 160px; flex-shrink: 0;">Save Address</button>
      </div>
    </form>
  </div>
</div>

<!-- ── EDIT ADDRESS MODAL ── -->
<div class="account-modal-overlay" id="editAddressModal">
  <div class="account-modal">
    <div class="account-modal-header">
      <h2>Edit Address</h2>
      <button type="button" class="account-modal-close" onclick="closeModal('editAddressModal')">&times;</button>
    </div>
    <form action="account-handler.php" method="POST" id="editAddressForm">
      <input type="hidden" name="action" value="update_address">
      <input type="hidden" name="address_id" id="edit-addr-id">
      <div class="account-form-grid" style="padding: 20px;">
        <div class="auth-field-group">
          <label class="auth-field-label">Label</label>
          <div class="auth-field-wrap">
            <input type="text" name="label" id="edit-addr-label" class="auth-field-input auth-field-input--no-icon">
          </div>
        </div>
        <div class="auth-field-group">
          <label class="auth-field-label">Full Name *</label>
          <div class="auth-field-wrap">
            <input type="text" name="addr_full_name" id="edit-addr-fullname" class="auth-field-input auth-field-input--no-icon" required>
          </div>
        </div>
        <div class="auth-field-group account-form-full">
          <label class="auth-field-label">Phone</label>
          <div class="auth-field-wrap">
            <input type="tel" name="addr_phone" id="edit-addr-phone" class="auth-field-input auth-field-input--no-icon">
          </div>
        </div>
        <div class="auth-field-group account-form-full">
          <label class="auth-field-label">Address Line 1 *</label>
          <div class="auth-field-wrap">
            <input type="text" name="address_line_1" id="edit-addr-line1" class="auth-field-input auth-field-input--no-icon" required>
          </div>
        </div>
        <div class="auth-field-group account-form-full">
          <label class="auth-field-label">Address Line 2</label>
          <div class="auth-field-wrap">
            <input type="text" name="address_line_2" id="edit-addr-line2" class="auth-field-input auth-field-input--no-icon">
          </div>
        </div>
        <div class="auth-field-group">
          <label class="auth-field-label">City *</label>
          <div class="auth-field-wrap">
            <input type="text" name="city" id="edit-addr-city" class="auth-field-input auth-field-input--no-icon" required>
          </div>
        </div>
        <div class="auth-field-group">
          <label class="auth-field-label">State *</label>
          <div class="auth-field-wrap">
            <input type="text" name="state" id="edit-addr-state" class="auth-field-input auth-field-input--no-icon" required>
          </div>
        </div>
        <div class="auth-field-group">
          <label class="auth-field-label">ZIP / Postal Code *</label>
          <div class="auth-field-wrap">
            <input type="text" name="zip_code" id="edit-addr-zip" class="auth-field-input auth-field-input--no-icon" required>
          </div>
        </div>
        <div class="auth-field-group">
          <label class="auth-field-label">Country</label>
          <div class="auth-field-wrap">
            <input type="text" name="country" id="edit-addr-country" class="auth-field-input auth-field-input--no-icon">
          </div>
        </div>
        <div class="auth-field-group account-form-full" style="flex-direction:row; align-items:center; gap:8px;">
          <input type="checkbox" name="is_default" id="edit-addr-default" value="1">
          <label for="edit-addr-default" class="auth-field-label" style="margin:0;">Set as default shipping address</label>
        </div>
      </div>
      <div class="account-modal-footer">
        <button type="button" class="address-action-btn" onclick="closeModal('editAddressModal')">Cancel</button>
        <button type="submit" class="auth-submit-btn" style="width: auto; min-width: 140px; max-width: 160px; flex-shrink: 0;">Update Address</button>
      </div>
    </form>
  </div>
</div>

<script>
// ── Tab switching ──
document.querySelectorAll('.account-nav-btn[data-target]').forEach(function(btn) {
  btn.addEventListener('click', function() {
    var target = this.getAttribute('data-target');

    document.querySelectorAll('.account-nav-btn').forEach(function(b) { b.classList.remove('active'); });
    document.querySelectorAll('.account-panel').forEach(function(p) { p.classList.remove('active'); });

    this.classList.add('active');
    var panel = document.getElementById(target);
    if (panel) panel.classList.add('active');

    // Update URL without reload
    var url = new URL(window.location);
    url.searchParams.set('tab', target);
    window.history.replaceState({}, '', url);
  });
});

// ── Address Modals ──
function openAddAddressModal() {
  document.getElementById('addAddressModal').classList.add('open');
  document.body.style.overflow = 'hidden';
  if (window.lenis) window.lenis.stop();
}

function openEditAddressModal(addr) {
  document.getElementById('edit-addr-id').value = addr.id;
  document.getElementById('edit-addr-label').value = addr.label || '';
  document.getElementById('edit-addr-fullname').value = addr.full_name;
  document.getElementById('edit-addr-phone').value = addr.phone || '';
  document.getElementById('edit-addr-line1').value = addr.address_line_1;
  document.getElementById('edit-addr-line2').value = addr.address_line_2 || '';
  document.getElementById('edit-addr-city').value = addr.city;
  document.getElementById('edit-addr-state').value = addr.state;
  document.getElementById('edit-addr-zip').value = addr.zip_code;
  document.getElementById('edit-addr-country').value = addr.country;
  document.getElementById('edit-addr-default').checked = addr.is_default == 1;

  document.getElementById('editAddressModal').classList.add('open');
  document.body.style.overflow = 'hidden';
  if (window.lenis) window.lenis.stop();
}

function closeModal(id) {
  document.getElementById(id).classList.remove('open');
  document.body.style.overflow = '';
  if (window.lenis) window.lenis.start();
}

// Close modal on overlay click
document.querySelectorAll('.account-modal-overlay').forEach(function(overlay) {
  overlay.addEventListener('click', function(e) {
    if (e.target === this) {
      this.classList.remove('open');
      document.body.style.overflow = '';
      if (window.lenis) window.lenis.start();
    }
  });
});

// Fix: Prevent Lenis from intercepting wheel events inside the modal
document.querySelectorAll('.account-modal').forEach(function(modal) {
  modal.addEventListener('wheel', function(e) {
    e.stopPropagation();
  }, { passive: true });
});

// Auto-hide flash after 5s
var flash = document.getElementById('accountFlash');
if (flash) {
  setTimeout(function() {
    flash.style.opacity = '0';
    setTimeout(function() { flash.remove(); }, 300);
  }, 5000);
}
</script>

<?php
include 'includes/footer.php';
?>
