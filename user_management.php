<?php
session_start();
require 'config/db_connect.php';
if(!isset($_SESSION['user_id']) || $_SESSION['role']!=='super_admin') {
    header("Location:index.php");
    exit;
}

// Handle add user/admin
$add_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
  $username = trim($_POST['username'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';
  $role = $_POST['role'] ?? 'user';

  // Check for duplicate username
  $check = $conn->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
  $check->bind_param('s', $username);
  $check->execute();
  $checkRes = $check->get_result();
  if ($checkRes && $checkRes->num_rows > 0) {
    $add_msg = "Error: Username already exists.";
  } elseif ($username && $email && $password && in_array($role, ['user','admin','customer'])) {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $email, $hash, $role);
    if ($stmt->execute()) {
      $add_msg = "User added successfully!";
    } else {
      $add_msg = "Error: " . $conn->error;
    }
  } else {
    $add_msg = "All fields are required and role must be customer or admin.";
  }
}

// Handle remove user/admin
if (isset($_POST['remove_user']) && isset($_POST['user_id'])) {
    $uid = intval($_POST['user_id']);
    // Prevent removing self or super_admins
    $stmt = $conn->prepare("SELECT role FROM users WHERE id=?");
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    if ($row && $row['role'] !== 'super_admin' && $uid != $_SESSION['user_id']) {
        $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
        $stmt->bind_param("i", $uid);
        $stmt->execute();
    }
}

// Fetch all users except super_admins
$users = [];
$res = $conn->query("SELECT id, username, email, role, created_at FROM users WHERE role!='super_admin' ORDER BY role DESC, created_at DESC");
while($row = $res->fetch_assoc()) $users[] = $row;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>User Management - LCCL Ticketing</title>
<link rel="stylesheet" href="assets/style.css">
<style>
body { background: #23243a; color: #e0e6f6; font-family: 'Poppins', Arial, sans-serif; }
.main-content { max-width: 900px; margin: 48px auto; background: #1e2738; border-radius: 18px; padding: 38px; box-shadow: 0 8px 32px #0004; }
h1 { color: #ffd700; text-align: center; margin-bottom: 32px; }
.add-form { background: #23243a; border-radius: 12px; padding: 24px; margin-bottom: 32px; box-shadow: 0 2px 12px #ffd70022; }
.add-form label { color: #ffd700; font-weight: 600; }
.add-form input, .add-form select { width: 100%; padding: 10px; margin-bottom: 14px; border-radius: 8px; border: 1px solid #ffd70033; background: #1a2234; color: #fff; }
.add-form button { background: linear-gradient(90deg,#ffd700,#ffb347); color: #23243a; font-weight: 700; border: none; padding: 12px 28px; border-radius: 8px; cursor: pointer; }
.add-form .msg { color: #ffd700; margin-bottom: 12px; }
.user-table { width: 100%; border-collapse: collapse; margin-top: 18px; }
.user-table th, .user-table td { padding: 12px; border-bottom: 1px solid #ffd70022; text-align: center; }
.user-table th { background: #23243a; color: #ffd700; }
.user-table td { background: #1a2234; color: #e0e6f6; }
.user-table tr.admin-row td { color: #ffd700; font-weight: 700; }
.user-table form { display: inline; }
.user-table button { background: #d64545; color: #fff; border: none; padding: 7px 16px; border-radius: 6px; cursor: pointer; font-weight: 600; }
.user-table button:hover { background: #a83232; }
@media(max-width:900px){
  .main-content { padding: 10px; }
  .add-form { padding: 10px; }
  .user-table th, .user-table td { padding: 6px; font-size: 0.98em; }
}
</style>
</head>
<body>
<nav style="margin:24px auto; text-align:center;">
  <a href="super_admin.php" style="background:linear-gradient(90deg,#ffd700,#ffb347);color:#23243a;font-weight:700;padding:12px 28px;border-radius:8px;text-decoration:none;box-shadow:0 2px 12px #ffd70022;margin-right:12px;">Home</a>
  <a href="logout.php" style="background:linear-gradient(90deg,#ffd700,#ffb347);color:#23243a;font-weight:700;padding:12px 28px;border-radius:8px;text-decoration:none;box-shadow:0 2px 12px #ffd70022;">Logout</a>
</nav>
<div class="main-content">
  <h1>User Management</h1>
  <div class="add-form">
    <form method="POST">
      <div class="msg"><?= htmlspecialchars($add_msg) ?></div>
      <label>Username</label>
      <input type="text" name="username" required>
      <label>Email</label>
      <input type="email" name="email" required>
      <label>Password</label>
      <input type="password" name="password" required>
      <label>Role</label>
      <select name="role" required>
        <option value="customer">Customer</option>
        <option value="admin">Admin</option>
      </select>
      <button type="submit" name="add_user">Add User/Admin</button>
    </form>
  </div>
  <table class="user-table">
    <thead>
      <tr>
        <th>ID</th>
        <th>Username</th>
        <th>Email</th>
        <th>Role</th>
        <th>Created At</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($users as $u): ?>
      <tr class="<?= $u['role']=='admin'?'admin-row':'' ?>">
        <td><?= $u['id'] ?></td>
        <td><?= htmlspecialchars($u['username']) ?></td>
        <td><?= htmlspecialchars($u['email']) ?></td>
        <td><?= ucfirst($u['role']) ?></td>
        <td><?= htmlspecialchars($u['created_at']) ?></td>
        <td>
          <?php if($u['role']!='super_admin' && $u['id']!=$_SESSION['user_id']): ?>
          <form method="POST" onsubmit="return confirm('Remove this user?');">
            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
            <button type="submit" name="remove_user">Remove</button>
          </form>
          <?php else: ?>
            <span style="color:#888;">N/A</span>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php include('includes/footer.php'); ?>
</body>
</html>