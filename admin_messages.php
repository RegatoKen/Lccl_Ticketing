<?php
session_start();
require 'config/db_connect.php';
if(!isset($_SESSION['user_id']) || !in_array($_SESSION['role'],['admin','super_admin'])){ header('Location:index.php'); exit; }

// Fetch messages targeted to admins and include responder username if any
$res = $conn->query("
  SELECT m.id, m.user_id, m.subject, m.message, m.is_read, m.created_at,
         m.solution, m.responded_at, m.responded_by,
         u.username AS user_name,
         r.username AS responder_name
  FROM messages m
  LEFT JOIN users u ON u.id = m.user_id
  LEFT JOIN users r ON r.id = m.responded_by
  WHERE FIND_IN_SET('admin',m.target_roles) OR FIND_IN_SET('super_admin',m.target_roles)
  ORDER BY m.created_at DESC
");
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Admin Messages - LCCL</title>
<link rel="stylesheet" href="assets/style.css">
<style>
main{max-width:1000px;margin:90px auto;padding:20px}
.table{width:100%;border-collapse:collapse}
.table th,.table td{padding:10px;border-bottom:1px solid #030000ff;vertical-align:top}
.badge{background:#ffd700;color:#111;padding:6px 10px;border-radius:6px}
.message-row-unread{background:#fff9e6}
.form-solution textarea{width:100%;min-height:80px;padding:8px;border-radius:6px;border:1px solid #ccc}
.form-solution button{margin-top:6px;padding:8px 12px;border-radius:6px;background:#6cb2ff;color:#fff;border:none;cursor:pointer}
.solution-box{background:#f4f8ff;padding:10px;border-radius:6px;border:1px solid #e1e8ff}
.flash{padding:10px;background:#e6ffea;border:1px solid #b6f0c2;color:#145a2d;border-radius:6px;margin-bottom:12px}
<style>
    body {
        font-family: 'Poppins', sans-serif;
        background: linear-gradient(135deg, #1a1b2f 0%, #23243a 100%);
        color: #e6e6e6;
        margin: 0;
        padding: 0;
    }

    .container {
        max-width: 1000px;
        margin: 60px auto;
        background: rgba(26, 34, 52, 0.85);
        padding: 30px;
        border-radius: 20px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.4);
        border: 1px solid rgba(255, 215, 0, 0.15);
    }

    h2 {
        text-align: center;
        color: #ff0800ea;
        font-size: 2rem;
        text-shadow: 0 2px 6px rgba(0, 0, 0, 0.6);
        margin-bottom: 25px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    th, td {
        padding: 14px 18px;
        border-bottom: 1px solid rgba(255, 215, 0, 0.15);
        text-align: left;
    }

    th {
        background: #1f233a;
        color: #ffd700;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    td {
        color: #0a0000ff;
        font-size: 1rem;
    }

    tr:hover {
        background: rgba(255, 215, 0, 0.05);
        transition: 0.2s ease;
    }

    .btn {
        padding: 8px 16px;
        border-radius: 8px;
        border: none;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .btn-delete {
        background: #d64545;
        color: #fff;
        box-shadow: 0 3px 10px rgba(214, 69, 69, 0.3);
    }

    .btn-delete:hover {
        transform: scale(1.05);
        background: #e35757;
    }

    .no-messages {
        text-align: center;
        color: #ccc;
        padding: 30px 0;
        font-size: 1.2rem;
    }
</style>

</style>
</head>
<body>
<?php include 'includes/navbar.php'; ?>
<main>
<h2>Messages</h2>

<?php if(!empty($_SESSION['flash'])): ?>
  <div class="flash"><?= htmlspecialchars($_SESSION['flash']); unset($_SESSION['flash']); ?></div>
<?php endif; ?>

<table class="table" aria-describedby="messages-table">
<thead>
<tr>
  <th>ID</th><th>User</th><th>Subject</th><th>Message</th><th>When</th><th>Solution / Actions</th>
</tr>
</thead>
<tbody>
<?php while($row = $res->fetch_assoc()): ?>
  <?php $unreadClass = $row['is_read'] ? '' : 'message-row-unread'; ?>
  <tr class="<?= $unreadClass ?>">
    <td><?= intval($row['id']) ?></td>
    <td><?= htmlspecialchars($row['user_name']?:'Guest') ?></td>
    <td><?= htmlspecialchars($row['subject']) ?></td>
    <td><?= nl2br(htmlspecialchars($row['message'])) ?></td>
    <td><?= htmlspecialchars($row['created_at']) ?></td>
    <td style="width:380px">
      <?php if(!empty($row['solution'])): ?>
        <div class="solution-box">
          <strong>Solution (by <?= htmlspecialchars($row['responder_name']?:'Admin') ?><?= $row['responded_at'] ? ' • '.htmlspecialchars($row['responded_at']) : '' ?>):</strong>
          <div style="margin-top:6px;white-space:pre-wrap;"><?= nl2br(htmlspecialchars($row['solution'])) ?></div>
        </div>
        <div style="margin-top:8px">
          <a href="mark_read.php?id=<?= $row['id'] ?>">Mark Read</a> |
          <a href="delete_message.php?id=<?= $row['id'] ?>" onclick="return confirm('Delete?')">Delete</a>
        </div>
      <?php else: ?>
        <form method="POST" action="respond_message.php" class="form-solution" id="reply-form-<?= intval($row['id']) ?>">
          <input type="hidden" name="message_id" value="<?= intval($row['id']) ?>">
          <textarea name="solution" placeholder="Write a solution / response for the customer..." required style="display:none;"></textarea>
          <div style="display:flex;gap:8px;margin-top:6px;">
            <button type="button" onclick="showReplyForm(<?= intval($row['id']) ?>)">Reply</button>
            <button type="submit" style="display:none;" id="submit-btn-<?= intval($row['id']) ?>">Send Reply</button>
            <a href="mark_read.php?id=<?= $row['id'] ?>" style="align-self:center;color:#666;text-decoration:underline;">Mark Read</a>
            <a href="delete_message.php?id=<?= $row['id'] ?>" onclick="return confirm('Delete?')" style="align-self:center;color:#d33;text-decoration:underline;margin-left:auto;">Delete</a>
          </div>
        </form>
        <script>
        function showReplyForm(id) {
          var form = document.getElementById('reply-form-' + id);
          var textarea = form.querySelector('textarea');
          var submitBtn = form.querySelector('button[type="submit"]');
          textarea.style.display = 'block';
          submitBtn.style.display = 'inline-block';
        }
        </script>
      <?php endif; ?>
    </td>
  </tr>
<?php endwhile; ?>
</tbody>
</table>
</main>
<?php include 'includes/footer.php'; ?>
</body>
</html>