<?php
session_start();
require 'config/db_connect.php';
if(!isset($_SESSION['user_id']) || $_SESSION['role']!=='super_admin') {
    header("Location:index.php");
    exit;
}

// Example: Change site name (add more settings as needed)
$settings_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['site_name'])) {
    $site_name = trim($_POST['site_name']);
    // Save to database or config file (example: settings table)
    $stmt = $conn->prepare("UPDATE settings SET value=? WHERE name='site_name'");
    $stmt->bind_param("s", $site_name);
    if ($stmt->execute()) {
        $settings_msg = "Site name updated!";
    } else {
        $settings_msg = "Error updating site name.";
    }
}

// Get current site name
$site_name = 'LCCL Ticketing System';
$res = $conn->query("SELECT value FROM settings WHERE name='site_name'");
if($row = $res->fetch_assoc()) $site_name = $row['value'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>System Settings - LCCL Ticketing</title>
<link rel="stylesheet" href="assets/style.css">
<style>
body { background: #23243a; color: #e0e6f6; font-family: 'Poppins', Arial, sans-serif; }
.settings-content { max-width: 700px; margin: 48px auto; background: #1e2738; border-radius: 18px; padding: 38px; box-shadow: 0 8px 32px #0004; }
h1 { color: #ffd700; text-align: center; margin-bottom: 32px; }
.settings-form { background: #23243a; border-radius: 12px; padding: 24px; margin-bottom: 32px; box-shadow: 0 2px 12px #ffd70022; }
.settings-form label { color: #ffd700; font-weight: 600; }
.settings-form input { width: 100%; padding: 10px; margin-bottom: 14px; border-radius: 8px; border: 1px solid #ffd70033; background: #1a2234; color: #fff; }
.settings-form button { background: linear-gradient(90deg,#ffd700,#ffb347); color: #23243a; font-weight: 700; border: none; padding: 12px 28px; border-radius: 8px; cursor: pointer; }
.settings-form .msg { color: #ffd700; margin-bottom: 12px; }
@media(max-width:900px){
  .settings-content { padding: 10px; }
  .settings-form { padding: 10px; }
}
</style>
</head>
<body>
<div class="settings-content">
  <h1>System Settings</h1>
  <form method="POST" class="settings-form">
    <div class="msg"><?= htmlspecialchars($settings_msg) ?></div>
    <label>Site Name</label>
    <input type="text" name="site_name" value="<?= htmlspecialchars($site_name) ?>" required>
    <button type="submit">Save Changes</button>
  </form>
  <!-- Add more settings forms here as needed -->
</div>
<footer>
  <p>© <?= date('Y') ?> LCCL Ticketing System</p>
</footer>
</body>
</html>