<?php
session_start();
require 'config/db_connect.php';
if(!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin','super_admin'])){ header('Location:index.php'); exit; }

// Fetch all payments and ticket info
$res = $conn->query("SELECT p.id, p.user_id, u.username, p.amount, p.method, p.status, p.created_at FROM payments p JOIN users u ON p.user_id = u.id ORDER BY p.created_at DESC");
$payments = [];
while($row = $res->fetch_assoc()) $payments[] = $row;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Payments - LCCL Ticketing</title>
<link rel="stylesheet" href="assets/style.css">
<style>
.table{width:100%;border-collapse:collapse;margin-top:30px}
.table th,.table td{padding:10px;border-bottom:1px solid #eee;text-align:left}
.status-btn{padding:6px 14px;border-radius:6px;border:none;cursor:pointer;font-weight:600}
.status-paid{background:#38bdf8;color:#fff}
.status-pending{background:#ffd700;color:#222}
.status-btn:hover{opacity:0.85}
</style>
</head>
<body>
<?php include('includes/navbar.php'); ?>
<main>
<h1 class="page-title">Manage Payments & Ticket Status</h1>
<table class="table">
<tr>
<th>ID</th>
<th>User</th>
<th>Amount</th>
<th>Method</th>
<th>Status</th>
<th>Date</th>
<th>Action</th>
</tr>
<?php foreach($payments as $p): ?>
<tr>
<td><?= $p['id'] ?></td>
<td><?= htmlspecialchars($p['username']) ?></td>
<td><?= number_format($p['amount'],2) ?></td>
<td><?= htmlspecialchars($p['method']) ?></td>
<td>
  <?php if($p['status']==='paid'): ?>
    <span class="status-paid">Paid</span>
  <?php else: ?>
    <span class="status-pending">Pending</span>
  <?php endif; ?>
</td>
<td><?= htmlspecialchars($p['created_at']) ?></td>
<td>
  <?php if($p['status']==='pending'): ?>
    <form method="get" action="update_payment_status.php" style="display:inline;">
      <input type="hidden" name="id" value="<?= $p['id'] ?>">
      <input type="hidden" name="status" value="paid">
      <button type="submit" class="status-btn status-paid">Mark as Paid</button>
    </form>
  <?php else: ?>
    <form method="get" action="update_payment_status.php" style="display:inline;">
      <input type="hidden" name="id" value="<?= $p['id'] ?>">
      <input type="hidden" name="status" value="pending">
      <button type="submit" class="status-btn status-pending">Set Pending</button>
    </form>
  <?php endif; ?>
</td>
</tr>
<?php endforeach; ?>
</table>
</main>
<footer>
<p>© <?= date('Y') ?> LCCL Ticketing System</p>
</footer>
</body>
</html>
