<?php
session_start();
require 'config/db_connect.php';
if(!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin','super_admin'])) {
    header('Location:index.php'); exit();
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['payment_id'], $_POST['new_status'])) {
    $pid = intval($_POST['payment_id']);
    $new_status = ($_POST['new_status'] === 'paid') ? 'paid' : 'pending';
    $stmt = $conn->prepare('UPDATE payments SET status=? WHERE id=?');
    $stmt->bind_param('si', $new_status, $pid);
    $stmt->execute();
    $msg = 'Payment status updated.';
}

// Fetch all payments and tickets for users
$q = "SELECT p.id AS payment_id, p.user_id, u.username, p.amount, p.status, p.method, p.created_at,
            GROUP_CONCAT(CONCAT(e.title, ' (x', t.quantity, ')')) AS tickets
      FROM payments p
      LEFT JOIN tickets t ON t.payment_id = p.id
      LEFT JOIN events e ON t.event_id = e.id
      LEFT JOIN users u ON p.user_id = u.id
      GROUP BY p.id
      ORDER BY p.created_at DESC";
$q = "SELECT p.id AS payment_id, p.user_id, u.username, p.amount, p.status, p.method, p.created_at
    FROM payments p
    LEFT JOIN users u ON p.user_id = u.id
    ORDER BY p.created_at DESC";
 $res = $conn->query($q);
 $payments = [];
 if($res) while($row = $res->fetch_assoc()) $payments[] = $row;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Update Ticket Status - LCCL Ticketing</title>
<link rel="stylesheet" href="assets/style.css">
<style>
.table { width:100%; border-collapse:collapse; margin-top:30px; }
.table th, .table td { padding:12px; border-bottom:1px solid #eee; }
.badge { padding:6px 14px; border-radius:8px; font-weight:600; }
.badge.paid { background:#38bdf8; color:#fff; }
.badge.pending { background:#ffd700; color:#222; }
form.inline { display:inline; }
</style>
</head>
<body>
<?php include('includes/navbar.php'); ?>
<main style="max-width:1100px;margin:60px auto;">
<h2>Update User Ticket Payment Status</h2>
<?php if(!empty($msg)) echo '<div style="color:green;font-weight:600;margin-bottom:18px;">'.htmlspecialchars($msg).'</div>'; ?>
<table class="table">
<thead>
<tr>
<th>User</th><th>Tickets</th><th>Amount</th><th>Method</th><th>Status</th><th>Purchased</th><th>Action</th>
</tr>
</thead>
<tbody>
<?php foreach($payments as $p): ?>
<tr>
<td><?= htmlspecialchars($p['username']) ?></td>
<td><?= htmlspecialchars($p['tickets'] ?? '-') ?></td>
<td>
    <?php
        // Fetch tickets for this payment by user and payment time
        $tickets = [];
        $pt = $conn->prepare("SELECT t.quantity, e.title FROM tickets t JOIN events e ON t.event_id = e.id WHERE t.user_id=? AND t.created_at >= ? ORDER BY t.created_at ASC");
        $pt->bind_param('is', $p['user_id'], $p['created_at']);
        $pt->execute();
        $tr = $pt->get_result();
        while($tk = $tr->fetch_assoc()) $tickets[] = $tk['title'] . ' (x' . $tk['quantity'] . ')';
        echo $tickets ? htmlspecialchars(implode(', ', $tickets)) : '-';
    ?>
</td>
<td>₱<?= number_format($p['amount'],2) ?></td>
<td><?= htmlspecialchars($p['method']) ?></td>
<td><span class="badge <?= $p['status'] ?>"><?= ucfirst($p['status']) ?></span></td>
<td><?= htmlspecialchars($p['created_at']) ?></td>
<td>
<form method="post" class="inline">
<input type="hidden" name="payment_id" value="<?= $p['payment_id'] ?>">
<select name="new_status">
<option value="paid" <?= $p['status']==='paid'?'selected':'' ?>>Paid</option>
<option value="pending" <?= $p['status']==='pending'?'selected':'' ?>>Pending</option>
</select>
<button type="submit" style="background:#38bdf8;color:#fff;border:none;padding:4px 10px;border-radius:6px;font-weight:600;cursor:pointer;">Update</button>
</form>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</main>
</body>
</html>
