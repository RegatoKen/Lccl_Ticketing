<?php
session_start();
require 'config/db_connect.php';
if(!isset($_SESSION['user_id'])) header("Location:index.php");
if($_SESSION['role']!=='admin' && $_SESSION['role']!=='super_admin') { header("Location: index.php"); exit; }
?>
<main>
<h1 class="page-title">Admin Dashboard</h1>
<div class="card">
<p>Welcome, <?= htmlspecialchars($_SESSION['username']) ?>. Use the links below to manage the system.</p>
<ul>
<li><a href="manage_events.php">Manage Events</a></li>
<li><a href="users.php">Manage Users</a></li>
<li><a href="manage_payments.php">Manage Payments & Ticket Status</a></li>
<li><a href="manage_tickets.php">Manage Tickets</a></li>
<li><a href="logout.php">Logout</a></li>
</ul>
</div>

<?php
// ...existing code...
</html>
<li><a href="users.php">Manage Users</a></li>
<li><a href="manage_payments.php">Manage Payments & Ticket Status</a></li>
<li><a href="manage_tickets.php">Manage Tickets</a></li>
<li><a href="logout.php">Logout</a></li>
</ul>
</div>

<?php
$res = $conn->query("SELECT t.id, t.user_id, u.username, t.event_id, e.title as event_title, t.quantity, t.created_at, p.status as payment_status, p.id as payment_id FROM tickets t JOIN users u ON t.user_id = u.id JOIN events e ON t.event_id = e.id JOIN payments p ON t.payment_id = p.id ORDER BY t.created_at DESC LIMIT 10");
$tickets = [];
while($row = $res && $res->fetch_assoc()) $tickets[] = $row;
?>
<h2 style="margin-top:40px;">Recently Purchased Tickets</h2>
<table class="table" style="margin-top:20px;">
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
</html>