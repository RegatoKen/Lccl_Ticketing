<?php
session_start();
require 'config/db_connect.php';
if(!isset($_SESSION['user_id'])) header('Location:index.php');
$user_id = $_SESSION['user_id'];

// Fetch messages sent to this user (feedback replies from admin/super_admin)
$q = "SELECT m.id, m.subject, m.message, m.solution, m.status, m.created_at, u.username as responder
      FROM messages m
      LEFT JOIN users u ON m.responded_by = u.id
      WHERE m.user_id = ? AND m.solution IS NOT NULL
      ORDER BY m.created_at DESC";
$stmt = $conn->prepare($q);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$res = $stmt->get_result();
$messages = [];
while($row = $res->fetch_assoc()) $messages[] = $row;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Inbox - LCCL Ticketing</title>
<link rel="stylesheet" href="assets/style.css">
<style>
.inbox-container { max-width: 900px; margin: 60px auto; background: #1e2738; border-radius: 18px; padding: 38px; box-shadow: 0 8px 32px #0004; }
.inbox-title { color: #ffd700; font-size: 2rem; margin-bottom: 24px; text-align: center; }
.inbox-table { width: 100%; border-collapse: collapse; margin-top: 18px; }
.inbox-table th, .inbox-table td { padding: 14px; border-bottom: 1px solid #ffd70022; text-align: left; }
.inbox-table th { background: #23243a; color: #ffd700; }
.inbox-table td { background: #1a2234; color: #e0e6f6; }
.inbox-table .solution { color: #38bdf8; font-weight: 600; }
.inbox-table .status { font-weight: 700; }
</style>
</head>
<body>
<?php include('navbar.php'); ?>
<div class="inbox-container">
  <div class="inbox-title">Inbox: Admin/Super Admin Replies</div>
  <?php if(empty($messages)): ?>
    <div style="color:#ffd700;text-align:center;padding:30px;">No replies from admin or super admin yet.</div>
  <?php else: ?>
    <table class="inbox-table">
      <thead>
        <tr>
          <th>Subject</th>
          <th>Your Message</th>
          <th>Reply</th>
          <th>Responder</th>
          <th>Status</th>
          <th>Date</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($messages as $m): ?>
        <tr>
          <td><?= htmlspecialchars($m['subject']) ?></td>
          <td><?= htmlspecialchars($m['message']) ?></td>
          <td class="solution"><?= htmlspecialchars($m['solution']) ?></td>
          <td><?= htmlspecialchars($m['responder'] ?? 'Admin') ?></td>
          <td class="status"><?= ucfirst($m['status']) ?></td>
          <td><?= htmlspecialchars($m['created_at']) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>
</body>
</html>
