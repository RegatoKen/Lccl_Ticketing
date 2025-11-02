<?php
session_start();
require 'config/db_connect.php';

// Ensure the events table has a `price` column (add it if missing)
// This prevents "Unknown column 'e.price'" errors when older schema lacks price.
$checkCol = $conn->query("
    SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'events'
      AND COLUMN_NAME = 'price'
");
if (!$checkCol || $checkCol->num_rows === 0) {
    // safe alter: add decimal price with default 0.00
    $conn->query("ALTER TABLE `events` ADD COLUMN `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00");
}

// --- existing checkout logic below ---
// Get cart items with price (events.price now guaranteed to exist)
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
$user_id = $_SESSION['user_id'];


$stmt = $conn->prepare("SELECT c.id as cart_id, e.id as event_id, e.title, e.start_date, e.end_date, e.time, e.venue,
       c.quantity, c.ticket_type, e.vip_price, e.regular_price
FROM cart c
JOIN events e ON c.event_id = e.id
WHERE c.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();

// Redirect if cart is empty
if ($res->num_rows === 0) {
  header("Location: cart.php");
  exit();
}

// Build items array and total
$items = [];
$total = 0.0;
while ($r = $res->fetch_assoc()) {
  $qty = isset($r['quantity']) ? intval($r['quantity']) : 1;
  $type = $r['ticket_type'] ?? 'Regular';
  $price = ($type === 'VIP') ? floatval($r['vip_price']) : floatval($r['regular_price']);
  $r['quantity'] = $qty;
  $r['ticket_type'] = $type;
  $r['price'] = $price;
  $items[] = $r;
  $total += $price * $qty;
}

// Handle checkout submission (billing + payment)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout'])) {
    // Basic billing validation
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $state = trim($_POST['state'] ?? '');
    $zip = trim($_POST['zip'] ?? '');
    $country = trim($_POST['country'] ?? '');
    $payment_method = $_POST['payment_method'] ?? 'card';

    if ($full_name === '' || $email === '' || $address === '' || $payment_method === '') {
        $error = "Please fill required billing fields.";
    } else {
        // Simulate/process payment (IMPORTANT: do NOT store raw card data in production)
        $payment_details = [
            'billing' => [
                'name' => $full_name,
                'email' => $email,
                'phone' => $phone,
                'address' => $address,
                'city' => $city,
                'state' => $state,
                'zip' => $zip,
                'country' => $country
            ]
        ];

        if ($payment_method === 'card') {
            $card_number = $_POST['card_number'] ?? '';
            // Only keep last 4 digits (masked)
            $digits = preg_replace('/\D/', '', $card_number);
            $last4 = substr($digits, -4);
            $masked = '**** **** **** ' . $last4;
            $payment_details['card'] = [
                'masked' => $masked,
                'brand' => $_POST['card_brand'] ?? 'card'
            ];
            // In real system: tokenize/send to payment gateway here
            $status = 'paid';
        } elseif ($payment_method === 'paypal') {
            // Simulate PayPal result
            $payment_details['paypal'] = ['txn' => 'PAYPAL_SIM_' . time()];
            $status = 'paid';
        } else {
            // e.g., cash on delivery / bank transfer — mark as pending
            $status = 'pending';
        }

        // ensure payments table exists (safe one-off create)
        $createPayments = "
        CREATE TABLE IF NOT EXISTS payments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            amount DECIMAL(10,2) NOT NULL DEFAULT 0,
            method VARCHAR(50) NOT NULL,
            details JSON NULL,
            status VARCHAR(50) NOT NULL DEFAULT 'pending',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";
        $conn->query($createPayments);

        // Insert payment record
        $amount = number_format($total, 2, '.', '');
        $stmtp = $conn->prepare("INSERT INTO payments (user_id, amount, method, details, status) VALUES (?, ?, ?, ?, ?)");
        $details_json = json_encode($payment_details, JSON_UNESCAPED_UNICODE);
        $stmtp->bind_param("idsss", $user_id, $amount, $payment_method, $details_json, $status);
        $stmtp->execute();
        $payment_id = $stmtp->insert_id;

        // Create tickets for each cart item and remove from cart
    $stmtTicket = $conn->prepare("INSERT INTO tickets (user_id, event_id, quantity, ticket_type, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmtDeductVIP = $conn->prepare("UPDATE events SET vip_seats = vip_seats - ? WHERE id = ?");
    $stmtDeductRegular = $conn->prepare("UPDATE events SET regular_seats = regular_seats - ? WHERE id = ?");
    $stmtDel = $conn->prepare("DELETE FROM cart WHERE id=?");
    foreach ($items as $it) {
  $eid = intval($it['event_id']);
  $cid = intval($it['cart_id']);
  $qty = intval($it['quantity']);
  $type = $it['ticket_type'];

  // Insert ticket
  $stmtTicket->bind_param("iiis", $user_id, $eid, $qty, $type);
  $stmtTicket->execute();

  // Deduct seat
  if ($type === 'VIP') {
    $stmtDeductVIP->bind_param("ii", $qty, $eid);
    $stmtDeductVIP->execute();
  } else {
    $stmtDeductRegular->bind_param("ii", $qty, $eid);
    $stmtDeductRegular->execute();
  }

  // Remove from cart
  $stmtDel->bind_param("i", $cid);
  $stmtDel->execute();
    }

        // store payment id in session for confirmation page
        $_SESSION['last_payment_id'] = $payment_id;

        // redirect to confirmation
        header("Location: confirm_payment.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Checkout - LCCL Ticketing</title>
<link rel="stylesheet" href="assets/style.css">
<style>
body {
  font-family: 'Poppins', Arial, sans-serif;
  background: linear-gradient(120deg, #23243a, #1a1b2f 90%);
  margin: 0;
  min-height: 100vh;
}
main {
  max-width: 600px;
  margin: 60px auto 0 auto;
  padding: 40px 20px;
}
.card {
  background: #23243a;
  border-radius: 18px;
  box-shadow: 0 8px 32px rgba(0,0,0,0.22);
  padding: 44px 32px;
  color: #ffd700;
  text-align: center;
  transition: box-shadow 0.3s;
}
.page-title {
  color: #ffd700;
  font-size: 2rem;
  font-weight: 700;
  margin-bottom: 24px;
  letter-spacing: 1px;
}
ul {
  list-style: none;
  padding: 0;
  margin: 0 0 18px 0;
}
ul li {
  margin-bottom: 12px;
  font-size: 1.08em;
  color: #ffe;
}
form .btn {
  background: linear-gradient(90deg, #ffd700, #ffb347);
  color: #23243a;
  font-weight: 700;
  border-radius: 8px;
  padding: 12px 24px;
  cursor: pointer;
  border: none;
  font-size: 1.08em;
  box-shadow: 0 2px 8px rgba(0,0,0,0.10);
  margin-top: 10px;
  transition: background 0.2s, color 0.2s;
}
form .btn:hover {
  background: linear-gradient(90deg, #ffb347, #ffd700);
  color: #1a1b2f;
}
footer {
  text-align: center;
  color: #ffd700;
  margin-top: 40px;
  font-size: 1.08em;
  padding-bottom: 20px;
}
.checkout-container {
  max-width: 900px;
  margin: 48px auto;
  display: grid;
  grid-template-columns: 1fr 420px;
  gap: 28px;
}
.billing-card, .summary-card {
  background: rgba(34,40,60,0.98);
  border-radius: 14px;
  padding: 20px;
  color: #eaf2ff;
  border: 1px solid #2d3a5a;
}
.form-row { display:flex; gap:12px; }
.form-row .field { flex:1; }
.input, select { width:100%; padding:10px; border-radius:8px; border:1px solid #2b3348; background:#151822; color:#eaf2ff; }
.small-note { font-size:0.9rem; color:#b6c3e6; margin-top:8px; }
.pay-methods { display:flex; gap:8px; margin-top:8px; }
.pay-methods label { background:#1a2132; padding:8px 12px; border-radius:8px; cursor:pointer; border:1px solid transparent; }
.pay-methods input[type="radio"] { margin-right:8px; }
.pay-methods label.active { border-color:#6cb2ff; box-shadow:0 6px 20px #0a0e1a88; }
.btn { /* reuse existing btn styles */ }
@media (max-width:900px){ .checkout-container{ grid-template-columns: 1fr; } }
</style>
</head>
<body>
<?php include('navbar.php'); ?>
<main>
<h1 class="page-title">Checkout</h1>

<?php if(!empty($error)): ?>
  <div style="color:#ffb3b3;background:#2b1a1a;padding:12px;border-radius:8px;margin-bottom:12px;"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="checkout-container">
  <form method="POST" class="billing-card" novalidate>
    <h2 style="color:#ffd700">Billing Information</h2>
    <div class="form-row">
      <div class="field"><label>Full name</label><input class="input" name="full_name" required value="<?= htmlspecialchars($_POST['full_name'] ?? ($_SESSION['username'] ?? '')) ?>"></div>
      <div class="field"><label>Email</label><input class="input" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? ($_SESSION['email'] ?? '')) ?>"></div>
    </div>
    <div class="form-row" style="margin-top:10px;">
      <div class="field"><label>Phone</label><input class="input" name="phone" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"></div>
      <div class="field"><label>Country</label><input class="input" name="country" value="<?= htmlspecialchars($_POST['country'] ?? '') ?>"></div>
    </div>
    <div style="margin-top:10px;"><label>Address</label><input class="input" name="address" required value="<?= htmlspecialchars($_POST['address'] ?? '') ?>"></div>
    <div class="form-row" style="margin-top:10px;">
      <div class="field"><label>City</label><input class="input" name="city" value="<?= htmlspecialchars($_POST['city'] ?? '') ?>"></div>
      <div class="field"><label>State</label><input class="input" name="state" value="<?= htmlspecialchars($_POST['state'] ?? '') ?>"></div>
      <div class="field"><label>ZIP</label><input class="input" name="zip" value="<?= htmlspecialchars($_POST['zip'] ?? '') ?>"></div>
    </div>

    <h3 style="margin-top:18px;color:#6cb2ff">Payment Method</h3>
    <div class="pay-methods" id="pay-methods">
      <label class="<?= (($_POST['payment_method'] ?? '')==='card')?'active':'' ?>"><input type="radio" name="payment_method" value="card" <?= (($_POST['payment_method'] ?? '')==='card')?'checked':'' ?>> Credit / Debit Card</label>
      <label class="<?= (($_POST['payment_method'] ?? '')==='paypal')?'active':'' ?>"><input type="radio" name="payment_method" value="paypal" <?= (($_POST['payment_method'] ?? '')==='paypal')?'checked':'' ?>> PayPal</label>
      <label class="<?= (($_POST['payment_method'] ?? '')==='cash')?'active':'' ?>"><input type="radio" name="payment_method" value="cash" <?= (($_POST['payment_method'] ?? '')==='cash')?'checked':'' ?>> Pay Later / Cash</label>
    </div>

    <div id="card-fields" style="margin-top:12px; <?= (($_POST['payment_method'] ?? 'card')!=='card')?'display:none':'' ?>">
      <div class="form-row">
        <div class="field"><label>Card Number</label><input class="input" name="card_number" placeholder="1234 5678 9012 3456"></div>
        <div class="field"><label>Expiry</label><input class="input" name="card_expiry" placeholder="MM/YY"></div>
      </div>
      <div class="form-row" style="margin-top:8px;">
        <div class="field"><label>CVV</label><input class="input" name="card_cvv" placeholder="123"></div>
        <div class="field"><label>Card Brand (optional)</label><input class="input" name="card_brand" placeholder="Visa/Mastercard"></div>
      </div>
      <div class="small-note">Card details are not stored. This demo masks card numbers and simulates a payment.</div>
    </div>

    <input type="hidden" name="checkout" value="1">
    <button type="submit" class="btn" style="margin-top:16px">Pay & Complete Order</button>
  </form>

  <aside class="summary-card">
    <h2 style="color:#ffd700">Order Summary</h2>
    <div style="margin-top:12px;">
      <?php if(count($items)>0): $grand_total=0; foreach($items as $it): ?>
        <?php $qty = isset($it['quantity']) ? intval($it['quantity']) : 1; $subtotal = $qty * floatval($it['price']); $grand_total += $subtotal; ?>
        <div style="padding:8px 0;border-bottom:1px solid rgba(255,255,255,0.03);display:flex;justify-content:space-between;align-items:center;">
          <div style="text-align:left;">
            <strong><?= htmlspecialchars($it['title']) ?></strong><br>
            <span style="color:#bcd8ff;font-size:.95rem">📅 <?= date('M d, Y', strtotime($it['date'])) ?></span>
          </div>
          <div style="text-align:right;min-width:160px;">
            <span style="color:#ffd700;font-size:1.08em;">Qty: <?= $qty ?></span><br>
            <span style="color:#bcd8ff;font-size:.95em;">₱<?= number_format(floatval($it['price']),2) ?> each</span><br>
            <span style="color:#ffd700;font-size:1.08em;">Subtotal: ₱<?= number_format($subtotal,2) ?></span>
          </div>
        </div>
      <?php endforeach; ?>
        <div style="margin-top:14px;font-weight:700;color:#eaf2ff;font-size:1.15em;text-align:right;">Total: ₱<?= number_format($grand_total,2) ?></div>
      <?php else: ?>
        <p>No items.</p>
      <?php endif; ?>
    </div>
    <div style="margin-top:8px;color:#b6c3e6;font-size:.95rem">You will receive tickets in your account after payment confirmation.</div>
  </aside>
</div>

<script>
// UI niceties: toggle card fields and active labels
document.querySelectorAll('.pay-methods input[type=radio]').forEach(function(r){
  r.addEventListener('change', function(){
    document.querySelectorAll('.pay-methods label').forEach(l=>l.classList.remove('active'));
    this.parentElement.classList.add('active');
    document.getElementById('card-fields').style.display = (this.value==='card') ? 'block' : 'none';
  });
});
</script>

</main>
<footer>
<p>© <?= date('Y') ?> LCCL Ticketing System. All rights reserved.</p>
</footer>
</body>
</html>
