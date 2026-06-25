<?php
/* =============================================================================
   order-handler.php — CloudCush Order Placement Endpoint
   Handles two actions:
     1. cart_checkout  — existing cart-based order (default, no action param)
     2. buy_now        — direct single-product order from product-details page
     3. get_addresses  — returns saved addresses as JSON (for Buy Now modal)
   ============================================================================= */

require_once 'includes/db.php';
require_once 'includes/customers-init.php';

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

$raw  = file_get_contents('php://input');
$body = json_decode($raw, true);

if (json_last_error() !== JSON_ERROR_NONE || !is_array($body)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request format.']);
    exit;
}

// ── Auth check — with Guest Auto-Login ──────────────────────────────────────
if (empty($_SESSION['customer_id'])) {
    // Guest checkout: if email provided, find or create account with password 123456
    $guestEmail = trim($body['guest_email'] ?? '');
    if ($guestEmail && filter_var($guestEmail, FILTER_VALIDATE_EMAIL)) {
        try {
            $pdo = getFrontendDB();
            // Check if customer already exists
            $chk = $pdo->prepare("SELECT id, full_name, email, status FROM customers WHERE email = ? LIMIT 1");
            $chk->execute([$guestEmail]);
            $existing = $chk->fetch();

            if ($existing) {
                // Customer exists — auto-login them
                if (($existing['status'] ?? 'active') === 'inactive') {
                    http_response_code(403);
                    echo json_encode(['success' => false, 'message' => 'This account is deactivated. Please contact support.']);
                    exit;
                }
                $_SESSION['customer_id']    = $existing['id'];
                $_SESSION['customer_name']  = $existing['full_name'];
                $_SESSION['customer_email'] = $existing['email'];
            } else {
                // Create new account with default password 123456
                $guestName = trim($body['guest_name'] ?? '') ?: explode('@', $guestEmail)[0];
                $defaultPw = password_hash('123456', PASSWORD_BCRYPT);
                $ins = $pdo->prepare("INSERT INTO customers (full_name, email, password, created_at) VALUES (?, ?, ?, NOW())");
                $ins->execute([$guestName, $guestEmail, $defaultPw]);
                $newId = (int)$pdo->lastInsertId();
                $_SESSION['customer_id']    = $newId;
                $_SESSION['customer_name']  = $guestName;
                $_SESSION['customer_email'] = $guestEmail;
            }
        } catch (Exception $e) {
            error_log('guest auto-login: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Server error during guest login. Please try again.']);
            exit;
        }
    } else {
        http_response_code(401);
        echo json_encode([
            'success'       => false,
            'need_guest_email' => true,
            'message'       => 'Please enter your email to continue.'
        ]);
        exit;
    }
}

$customerId = (int) $_SESSION['customer_id'];
$action     = trim($body['action'] ?? 'cart_checkout');

// ── Fetch customer ─────────────────────────────────────────────────────────────
try {
    $pdo  = getFrontendDB();
    $stmt = $pdo->prepare("SELECT id, full_name, email FROM customers WHERE id = ? AND status = 'active' LIMIT 1");
    $stmt->execute([$customerId]);
    $customer = $stmt->fetch();

    if (!$customer) {
        http_response_code(403);
        echo json_encode(['success' => false, 'redirect' => 'login.php', 'message' => 'Session expired. Please log in again.']);
        exit;
    }
} catch (Exception $e) {
    error_log('order-handler fetch customer: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error. Please try again.']);
    exit;
}

/* =============================================================================
   ACTION: get_addresses — return saved addresses for Buy Now modal
   ============================================================================= */
if ($action === 'get_addresses') {
    try {
        $stmt = $pdo->prepare("SELECT * FROM customer_addresses WHERE customer_id = ? ORDER BY is_default DESC, created_at DESC");
        $stmt->execute([$customerId]);
        $addresses = $stmt->fetchAll();
        echo json_encode(['success' => true, 'addresses' => $addresses]);
    } catch (Exception $e) {
        echo json_encode(['success' => true, 'addresses' => []]);
    }
    exit;
}

/* =============================================================================
   Helper: resolve and validate a delivery address
   Returns [ 'address_id' => int|null, 'address_snapshot' => string ] or throws.
   ============================================================================= */
function resolveDeliveryAddress(PDO $pdo, int $customerId, array $body): array {
    $addressId = isset($body['address_id']) ? (int)$body['address_id'] : 0;
    $newAddr   = $body['new_address'] ?? null;

    // Option A: use an existing saved address
    if ($addressId > 0) {
        $stmt = $pdo->prepare("SELECT * FROM customer_addresses WHERE id = ? AND customer_id = ? LIMIT 1");
        $stmt->execute([$addressId, $customerId]);
        $addr = $stmt->fetch();
        if (!$addr) throw new RuntimeException('Selected address not found.');

        $snapshot = implode(', ', array_filter([
            $addr['full_name'],
            $addr['address_line_1'],
            $addr['address_line_2'],
            $addr['city'],
            $addr['state'],
            $addr['zip_code'],
            $addr['country'],
        ]));
        return ['address_id' => $addressId, 'address_snapshot' => $snapshot];
    }

    // Option B: new address submitted inline (save it, then use it)
    if (is_array($newAddr)) {
        $fullName = trim($newAddr['full_name']      ?? '');
        $line1    = trim($newAddr['address_line_1'] ?? '');
        $line2    = trim($newAddr['address_line_2'] ?? '');
        $city     = trim($newAddr['city']           ?? '');
        $state    = trim($newAddr['state']          ?? '');
        $zip      = trim($newAddr['zip_code']       ?? '');
        $country  = trim($newAddr['country']        ?? 'India');
        $phone    = trim($newAddr['phone']          ?? '');
        $saveIt   = !empty($newAddr['save']);

        if ($fullName === '' || $line1 === '' || $city === '' || $state === '' || $zip === '') {
            throw new RuntimeException('Address is incomplete. Please fill all required fields.');
        }

        $newId = null;
        if ($saveIt) {
            $ins = $pdo->prepare("
                INSERT INTO customer_addresses
                    (customer_id, full_name, phone, address_line_1, address_line_2, city, state, country, zip_code, is_default)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0)
            ");
            $ins->execute([$customerId, $fullName, $phone ?: null, $line1, $line2 ?: null, $city, $state, $country, $zip]);
            $newId = (int)$pdo->lastInsertId();
        }

        $snapshot = implode(', ', array_filter([$fullName, $line1, $line2, $city, $state, $zip, $country]));
        return ['address_id' => $newId, 'address_snapshot' => $snapshot];
    }

    throw new RuntimeException('Please select or enter a delivery address.');
}

/* =============================================================================
   Helper: save order to DB (shared by both cart_checkout and buy_now)
   ============================================================================= */
function saveOrder(PDO $pdo, array $customer, array $items, float $total, int $itemCount, ?int $addressId, string $addressSnapshot): int {
    $pdo->beginTransaction();
    try {
        $pdo->prepare("
            INSERT INTO orders
                (customer_id, customer_name, customer_email, order_items, item_count, total_amount, delivery_address, address_id, status, created_at)
            VALUES
                (:cid, :cname, :cemail, :items, :count, :total, :addr_snap, :addr_id, 'pending', NOW())
        ")->execute([
            ':cid'       => $customer['id'],
            ':cname'     => $customer['full_name'],
            ':cemail'    => $customer['email'],
            ':items'     => json_encode($items, JSON_UNESCAPED_UNICODE),
            ':count'     => $itemCount,
            ':total'     => $total,
            ':addr_snap' => $addressSnapshot,
            ':addr_id'   => $addressId,
        ]);
        $orderId = (int)$pdo->lastInsertId();
        $pdo->commit();
        return $orderId;
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

// Ensure orders table has all required columns for buy_now and cart_checkout
try {
    $col = $pdo->query("SHOW COLUMNS FROM orders LIKE 'delivery_address'")->fetch();
    if (!$col) {
        $pdo->exec("ALTER TABLE orders ADD COLUMN delivery_address TEXT DEFAULT NULL AFTER total_amount");
    }
    $col2 = $pdo->query("SHOW COLUMNS FROM orders LIKE 'address_id'")->fetch();
    if (!$col2) {
        $pdo->exec("ALTER TABLE orders ADD COLUMN address_id INT(11) DEFAULT NULL AFTER delivery_address");
    }
    $col3 = $pdo->query("SHOW COLUMNS FROM orders LIKE 'order_items'")->fetch();
    if (!$col3) {
        $pdo->exec("ALTER TABLE orders ADD COLUMN order_items LONGTEXT DEFAULT NULL AFTER status");
    }
    $col4 = $pdo->query("SHOW COLUMNS FROM orders LIKE 'item_count'")->fetch();
    if (!$col4) {
        $pdo->exec("ALTER TABLE orders ADD COLUMN item_count INT(11) NOT NULL DEFAULT 1 AFTER order_items");
    }
} catch (Exception $e) {
    // Non-fatal — columns may already exist
}

/* =============================================================================
   ACTION: buy_now — direct single-product order
   ============================================================================= */
if ($action === 'buy_now') {
    $productId    = (int)($body['product_id'] ?? 0);
    $size         = trim($body['size']        ?? '');
    $qty          = max(1, min(99, (int)($body['quantity'] ?? 1)));
    $image        = trim($body['image']       ?? '');

    if ($productId < 1) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid product.']);
        exit;
    }

    // Server-side price resolution — never trust client price
    try {
        // Try to match the variant by variant_value (full label like "Medium (M)")
        // Fall back to product base price if no variant match or no variants
        $pStmt = $pdo->prepare("
            SELECT p.id, p.title, p.price, p.sale_price, p.status, p.stock, p.image_url,
                   COALESCE(pv.price_modifier, 0) AS price_modifier
            FROM products p
            LEFT JOIN product_variants pv
                ON pv.product_id = p.id
                AND (:size = '' OR TRIM(pv.variant_value) = :size2 OR pv.is_default = 1)
            WHERE p.id = :pid AND p.status = 'active'
            ORDER BY
                CASE WHEN :size3 != '' AND TRIM(pv.variant_value) = :size4 THEN 0
                     WHEN pv.is_default = 1 THEN 1
                     ELSE 2 END,
                pv.id ASC
            LIMIT 1
        ");
        $pStmt->execute([
            ':pid'   => $productId,
            ':size'  => $size,
            ':size2' => $size,
            ':size3' => $size,
            ':size4' => $size,
        ]);
        $prod = $pStmt->fetch();

        if (!$prod) {
            // Fallback: fetch product without variant filter
            $pFallback = $pdo->prepare("SELECT *, 0 AS price_modifier FROM products WHERE id = ? AND status = 'active' LIMIT 1");
            $pFallback->execute([$productId]);
            $prod = $pFallback->fetch();
            if (!$prod) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Product not found or unavailable.']);
                exit;
            }
        }
    } catch (Exception $e) {
        error_log('buy_now product fetch: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Server error. Please try again.']);
        exit;
    }

    $basePrice  = (float)($prod['sale_price'] ?: $prod['price']);
    $baseOrig   = $prod['sale_price'] ? (float)$prod['price'] : null;
    $modifier   = (float)($prod['price_modifier'] ?? 0);
    $price      = round($basePrice + $modifier, 2);
    $origPrice  = $baseOrig ? round($baseOrig + $modifier, 2) : null;
    $productName= $prod['title'];
    $prodImage  = $image ?: ($prod['image_url'] ?? '');

    if ($price <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid product price.']);
        exit;
    }

    $lineTotal = round($price * $qty, 2);
    $items = [[
        'id'            => $productId,
        'name'          => $productName,
        'size'          => $size,
        'price'         => $price,
        'original_price'=> $origPrice,
        'quantity'      => $qty,
        'image'         => $prodImage,
        'line_total'    => $lineTotal,
    ]];

    try {
        ['address_id' => $addrId, 'address_snapshot' => $addrSnap] = resolveDeliveryAddress($pdo, $customerId, $body);
        $orderId = saveOrder($pdo, $customer, $items, $lineTotal, $qty, $addrId, $addrSnap);
    } catch (RuntimeException $e) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;
    } catch (Exception $e) {
        error_log('buy_now order save: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Could not save order. Please try again.']);
        exit;
    }

    echo json_encode([
        'success'     => true,
        'order_id'    => $orderId,
        'item_count'  => $qty,
        'order_total' => number_format($lineTotal, 0, '.', ','),
        'message'     => 'Order placed successfully.',
    ]);
    exit;
}

/* =============================================================================
   ACTION: cart_checkout — existing multi-item cart order
   ============================================================================= */
$items = $body['items'] ?? [];
if (empty($items) || !is_array($items)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Your cart is empty.']);
    exit;
}

$cleanItems  = [];
$totalAmount = 0.0;
$totalQty    = 0;

foreach ($items as $item) {
    $name  = trim($item['name']     ?? '');
    $size  = trim($item['size']     ?? '');
    $price = isset($item['price'])    ? (float)$item['price']    : 0.0;
    $qty   = isset($item['quantity']) ? (int)$item['quantity']   : 1;
    $image = trim($item['image']    ?? '');
    $id    = trim($item['id']       ?? '');

    if ($name === '' || $price <= 0 || $qty < 1) continue;
    $qty = min($qty, 99);

    $lineTotal    = round($price * $qty, 2);
    $cleanItems[] = ['id' => $id, 'name' => $name, 'size' => $size, 'price' => $price, 'quantity' => $qty, 'image' => $image, 'line_total' => $lineTotal];
    $totalAmount += $lineTotal;
    $totalQty    += $qty;
}

if (empty($cleanItems)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No valid items found in cart.']);
    exit;
}

$totalAmount = round($totalAmount, 2);

// Cart checkout doesn't require address (existing behaviour preserved)
$addrSnap = '';
$addrId   = null;
if (!empty($body['address_id'])) {
    try {
        ['address_id' => $addrId, 'address_snapshot' => $addrSnap] = resolveDeliveryAddress($pdo, $customerId, $body);
    } catch (Exception $e) { /* optional for cart */ }
}

try {
    $orderId = saveOrder($pdo, $customer, $cleanItems, $totalAmount, $totalQty, $addrId, $addrSnap);
} catch (Exception $e) {
    error_log('cart_checkout order save: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Could not save order. Please try again.']);
    exit;
}

echo json_encode([
    'success'     => true,
    'order_id'    => $orderId,
    'item_count'  => $totalQty,
    'order_total' => number_format($totalAmount, 0, '.', ','),
    'message'     => 'Order placed successfully.',
]);
exit;
