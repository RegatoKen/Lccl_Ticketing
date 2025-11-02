<?php
session_start();
require 'config/db_connect.php';
if(!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin','super_admin'])){ header('Location:index.php'); exit; }

// Fetch all tickets with payment status
$res = $conn->query("SELECT t.id, t.user_id, u.username, t.event_id, e.title as event_title, t.quantity, t.created_at, p.status as payment_status, p.id as payment_id FROM tickets t JOIN users u ON t.user_id = u.id JOIN events e ON t.event_id = e.id JOIN payments p ON t.payment_id = p.id ORDER BY t.created_at DESC");
$tickets = [];
while($row = $res->fetch_assoc()) $tickets[] = $row;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Tickets - LCCL Ticketing</title>
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
<h1 class="page-title">Recently Purchased Tickets</h1>
<table class="table">
<tr>
<th>Ticket ID</th>
<th>User</th>
<th>Event</th>
<th>Quantity</th>
<th>Created At</th>
<th>Status</th>
<th>Change Status</th>
</tr>
<?php foreach($tickets as $t): ?>
<tr>
<td><?= $t['id'] ?></td>
<td><?= htmlspecialchars($t['username']) ?></td>
<td><?= htmlspecialchars($t['event_title']) ?></td>
<td><?= $t['quantity'] ?></td>
<td><?= htmlspecialchars($t['created_at']) ?></td>
<td>
  <?php if($t['payment_status']==='paid'): ?>
    <span class="status-paid">Paid</span>
  <?php else: ?>
    <span class="status-pending">Pending</span>
  <?php endif; ?>
</td>
<td>
  <?php if($t['payment_status']==='pending'): ?>
    <form method="get" action="update_payment_status.php" style="display:inline;">
      <input type="hidden" name="id" value="<?= $t['payment_id'] ?>">
      <input type="hidden" name="status" value="paid">
      <button type="submit" class="status-btn status-paid">Mark as Paid</button>
    </form>
  <?php else: ?>
    <form method="get" action="update_payment_status.php" style="display:inline;">
      <input type="hidden" name="id" value="<?= $t['payment_id'] ?>">
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
