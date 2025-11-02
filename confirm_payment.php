<?php
session_start();
require 'config/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Try to get the payment used for this checkout (set in checkout.php)
$payment_id = $_SESSION['last_payment_id'] ?? null;
$payment = null;

if ($payment_id) {
    $stmtp = $conn->prepare("SELECT * FROM payments WHERE id = ? AND user_id = ? LIMIT 1");
    $stmtp->bind_param("ii", $payment_id, $user_id);
    $stmtp->execute();
    $payment = $stmtp->get_result()->fetch_assoc();
}

// Fallback: fetch the most recent payment for the user
if (!$payment) {
    $pr = $conn->query("SELECT * FROM payments WHERE user_id=" . intval($user_id) . " ORDER BY created_at DESC LIMIT 1");
    $payment = $pr ? $pr->fetch_assoc() : null;
}

// Determine ticket selection window using payment time if available
$tickets = [];
$total_items_amount = 0.00;

if ($payment && !empty($payment['created_at'])) {
    // Use a small window around the payment time to find the tickets created by this checkout
    $created_at = $payment['created_at'];
    // group tickets by event to compute quantities
    $stmt = $conn->prepare("
        SELECT e.id AS event_id, e.title, e.date, COALESCE(e.price, 0) AS price, SUM(COALESCE(t.quantity,1)) AS quantity
        FROM tickets t
        JOIN events e ON t.event_id = e.id
        WHERE t.user_id = ? AND t.created_at >= DATE_SUB(?, INTERVAL 30 SECOND)
        GROUP BY e.id, e.title, e.date, e.price
        ORDER BY SUM(COALESCE(t.quantity,1)) DESC
    ");
    $stmt->bind_param("is", $user_id, $created_at);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $row['subtotal'] = floatval($row['price']) * intval($row['quantity']);
        $total_items_amount += $row['subtotal'];
        $tickets[] = $row;
    }
}

// If no tickets found using payment window, fallback to latest tickets grouped
if (empty($tickets)) {
    $stmt = $conn->prepare("
        SELECT e.id AS event_id, e.title, e.date, COALESCE(e.price, 0) AS price, SUM(COALESCE(t.quantity,1)) AS quantity
        FROM tickets t
        JOIN events e ON t.event_id = e.id
        WHERE t.user_id = ?
        GROUP BY e.id, e.title, e.date, e.price
        ORDER BY MAX(t.created_at) DESC
        LIMIT 20
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $row['subtotal'] = floatval($row['price']) * intval($row['quantity']);
        $total_items_amount += $row['subtotal'];
        $tickets[] = $row;
    }
}

// Prepare display values
$paid_amount = $payment ? floatval($payment['amount']) : $total_items_amount;
$method = $payment ? htmlspecialchars($payment['method']) : 'n/a';
$status = $payment ? htmlspecialchars($payment['status']) : 'n/a';
$payment_time = $payment ? htmlspecialchars($payment['created_at']) : '';

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Payment Confirmation - LCCL Ticketing</title>
<link rel="stylesheet" href="assets/style.css">
<style>
body {
    background: linear-gradient(135deg, #181c24 0%, #23283a 100%);
    color: #e0e6f6;
    font-family: 'Segoe UI', 'Montserrat', Arial, sans-serif;
    margin: 0;
    min-height: 100vh;
}
.page-title {
    text-align: center;
    font-size: 2.1rem;
    font-weight: 700;
    margin: 2rem 0 1rem 0;
    color: #6cb2ff;
}
.confirm-card {
    background: rgba(34, 40, 60, 0.98);
    border-radius: 14px;
    box-shadow: 0 6px 32px 0 #10131c88;
    max-width: 820px;
    margin: 0 auto 2.5rem auto;
    padding: 1.8rem;
    border: 1px solid #2d3a5a;
}
.header-row{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap}
.meta { color:#b8d8ff; font-size:0.95rem; }
.items { margin-top:1rem; }
.item-row { display:flex; justify-content:space-between; align-items:center; padding:10px; background:#23283a; border-radius:8px; margin-bottom:10px; }
.item-left { display:flex; flex-direction:column; }
.item-title { color:#eaf2ff; font-weight:600; }
.item-meta { color:#b6c3e6; font-size:0.92rem; }
.item-right { text-align:right; min-width:160px; }
.summary { margin-top:14px; display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; }
.summary .total { font-size:1.2rem; font-weight:700; color:#ffd770; }
.btn {
    background: linear-gradient(90deg, #6cb2ff 0%, #3a8dde 100%);
    color: #fff;
    border: none;
    border-radius: 10px;
    padding: 0.6rem 1.6rem;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    display:inline-block;
}
@media (max-width:700px){ .item-right{min-width:120px;text-align:right} .header-row{flex-direction:column;align-items:flex-start} }
</style>
</head>
<body>
<?php include('navbar.php'); ?>

<main>
    <h1 class="page-title">✅ Payment Confirmed</h1>

    <div class="confirm-card">
        <div class="header-row">
            <div>
                <div style="font-size:1.05rem;font-weight:700;color:#eaf2ff">Thank you — your order is complete</div>
                <div class="meta">Payment ID: <?php echo $payment ? htmlspecialchars($payment['id']) : '—'; ?> • Status: <?php echo $status; ?> • Method: <?php echo $method; ?></div>
                <?php if($payment_time): ?><div class="meta">Date: <?php echo $payment_time; ?></div><?php endif; ?>
            </div>
            <div style="text-align:right">
                <div style="font-size:0.9rem;color:#b6c3e6">Amount paid</div>
                <div class="summary-amount" style="font-size:1.4rem;font-weight:800;color:#ffd770">₱<?php echo number_format($paid_amount,2); ?></div>
            </div>
        </div>

        <div class="items">
            <?php if(empty($tickets)): ?>
                <p class="meta">No ticket items found for this order.</p>
            <?php else: foreach($tickets as $t): ?>
                <div class="item-row">
                    <div class="item-left">
                        <div class="item-title"><?php echo htmlspecialchars($t['title']); ?></div>
                        <div class="item-meta">Date: <?php echo date('M d, Y', strtotime($t['date'])); ?> • Price: ₱<?php echo number_format(floatval($t['price']),2); ?></div>
                    </div>
                    <div class="item-right">
                        <div class="item-meta">Qty: <?php echo intval($t['quantity']); ?></div>
                        <div style="font-weight:700;color:#dfefff">Subtotal</div>
                        <div style="font-size:1.1rem;color:#ffd770">₱<?php echo number_format($t['subtotal'],2); ?></div>
                    </div>
                </div>
            <?php endforeach; endif; ?>
        </div>

        <div class="summary">
            <div class="meta">Items total: ₱<?php echo number_format($total_items_amount,2); ?></div>
            <div>
                <div class="total">Paid: ₱<?php echo number_format($paid_amount,2); ?></div>
                <?php if($payment): ?>
                    <a href="generate_receipt.php?payment_id=<?php echo $payment['id']; ?>" class="btn" style="margin-left:12px">Download Receipt</a>
                <?php endif; ?>
                <a href="home.php" class="btn" style="margin-left:12px">Back to Home</a>
            </div>
        </div>
    </div>
</main>

<footer>
<p style="text-align:center;color:#9fb7e6;padding:14px 0;margin:0">© <?php echo date('Y'); ?> LCCL Ticketing System. All rights reserved.</p>
</footer>
</body>
</html>
