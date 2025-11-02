<?php
session_start();
require 'config/db_connect.php';
if(!isset($_SESSION['user_id'])) header("Location:index.php");
$username = $_SESSION['username'];
$email = $_SESSION['email'] ?? '';
$profile_image = $_SESSION['profile_image'] ?? 'assets/avatar.png';

// flash message from update handler
$profile_msg = $_SESSION['profile_msg'] ?? '';
unset($_SESSION['profile_msg']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Profile - LCCL Ticketing</title>
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
  padding: 32px;
  color: #ffd700;
  transition: box-shadow .3s;
}
.page-title {
  color: #ffd700;
  font-size: 2rem;
  font-weight: 700;
  margin-bottom: 24px;
  letter-spacing: 1px;
}
.card img {
  width: 120px;
  height: 120px;
  border-radius: 50%;
  margin-bottom: 18px;
  box-shadow: 0 4px 16px rgba(0,0,0,0.10);
  transition: box-shadow 0.2s;
}
.card h2 {
  font-size: 1.35rem;
  margin-bottom: 10px;
  font-weight: 700;
}
form input[type="file"] {
  margin-top: 18px;
  color: #ffd700;
}
form .btn {
  background: linear-gradient(90deg, #ffd700, #ffb347);
  color: #1a1b2f;
  padding: 10px 18px;
  border-radius: 8px;
  border: none;
  cursor: pointer;
  font-weight: 700;
  font-size: 1.08em;
  box-shadow: 0 2px 8px rgba(0,0,0,0.10);
  margin-top: 10px;
  transition: background 0.2s, color 0.2s;
}
form .btn:hover {
  background: linear-gradient(90deg, #ffb347, #ffd700);
  color: #1a1b2f;
}
.notice {
  background:#12312a;
  color:#d3ffe6;
  padding:10px;
  border-radius:8px;
  margin-bottom:12px;
  border:1px solid rgba(255,255,255,0.03);
}
.error {
  background:#4b1a1a;
  color:#ffd2d2;
  padding:10px;
  border-radius:8px;
  margin-bottom:12px;
  border:1px solid rgba(255,255,255,0.03);
}
.field {
  margin-bottom:12px;
  text-align:left;
}
label {
  display:block;
  color:#bcd8ff;
  margin-bottom:6px;
  font-weight:600;
}
.input {
  width:100%;
  padding:10px;
  border-radius:8px;
  border:1px solid #2b3348;
  background:#151822;
  color:#eaf2ff;
}
.row {
  display:flex;
  gap:12px;
  flex-wrap:wrap;
}
.col {
  flex:1;
  min-width:160px;
}
footer {
  text-align: center;
  color: #ffd700;
  margin-top: 40px;
  font-size: 1.08em;
  padding-bottom: 20px;
}
</style>
</head>
<body>
<?php include('navbar.php'); ?>
<main>
  <h1 class="page-title">My Profile</h1>

  <?php if($profile_msg): ?>
    <div class="notice"><?= htmlspecialchars($profile_msg) ?></div>
  <?php endif; ?>

  <div class="card" style="max-width:800px;margin:0 auto;">
    <div style="display:flex;gap:28px;align-items:flex-start;flex-wrap:wrap">
      <div style="flex:0 0 160px;text-align:center">
        <img src="<?= htmlspecialchars($profile_image) ?>" alt="Avatar" style="width:120px;height:120px;border-radius:50%;box-shadow:0 4px 16px rgba(0,0,0,0.1)">
        <form method="POST" action="upload_profile.php" enctype="multipart/form-data" style="margin-top:12px">
          <input type="file" name="profile_image" accept="image/*" required>
          <div style="margin-top:8px"><button class="btn" type="submit">Upload Photo</button></div>
        </form>
      </div>

      <div style="flex:1;min-width:260px">
        <form method="POST" action="update_profile.php" novalidate>
          <h2 style="color:#ffd700;margin-top:0;margin-bottom:8px">Account Details</h2>

          <div class="field">
            <label for="username">Display name</label>
            <input id="username" name="username" class="input" value="<?= htmlspecialchars($username) ?>" required>
          </div>

          <div class="field">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" class="input" value="<?= htmlspecialchars($email) ?>" required>
          </div>

          <h3 style="color:#b6d8ff;margin-top:12px;margin-bottom:8px">Change password (optional)</h3>

          <div class="field">
            <label for="current_password">Current password (required to change)</label>
            <input id="current_password" name="current_password" type="password" class="input" placeholder="Enter current password to confirm">
          </div>

          <div class="row">
            <div class="col field">
              <label for="new_password">New password</label>
              <input id="new_password" name="new_password" type="password" class="input" placeholder="New password">
            </div>
            <div class="col field">
              <label for="confirm_password">Confirm new password</label>
              <input id="confirm_password" name="confirm_password" type="password" class="input" placeholder="Confirm new password">
            </div>
          </div>

          <div style="margin-top:12px">
            <button class="btn" type="submit">Save changes</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</main>

<footer>
<p>© <?= date('Y') ?> LCCL Ticketing System</p>
</footer>
</body>
</html>
