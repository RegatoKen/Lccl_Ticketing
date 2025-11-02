<?php
session_start();
require 'config/db_connect.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['login'])) {
        $username = trim($_POST['username']);
        $password = $_POST['password'];

        $stmt = $conn->prepare("SELECT id, username, password, role, profile_image FROM users WHERE username = ?");
        $stmt->bind_param("s",$username);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res && $res->num_rows === 1) {
            $user = $res->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['profile_image'] = $user['profile_image'] ?? 'assets/avatar.png';
                if ($user['role'] === 'super_admin') {
                    header("Location: super_admin.php");
                } elseif ($user['role'] === 'admin') {
                    header("Location: admin_dashboard.php");
                } else {
                    header("Location: home.php");
                }
                exit;
            } else {
                $message = "Incorrect password.";
            }
        } else {
            $message = "User not found.";
        }
    }

    if (isset($_POST['signup'])) {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (strlen($username) < 3 || strlen($password) < 6) {
            $message = "Username must be 3+ chars and password 6+ chars.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = "Please enter a valid email address.";
        } elseif ($password !== $confirm) {
            $message = "Passwords do not match.";
        } else {
            // Check for existing username or email
            $check = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1");
            $check->bind_param('ss', $username, $email);
            $check->execute();
            $checkRes = $check->get_result();
            if ($checkRes && $checkRes->num_rows > 0) {
                $message = "Username or email already exists.";
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (username, email, password, role, created_at) VALUES (?, ?, ?, 'user', NOW())");
                $stmt->bind_param("sss", $username, $email, $hash);
                if ($stmt->execute()) {
                    $message = "Signup successful! You can now log in.";
                } else {
                    // Log or expose minimal info
                    $message = "Signup failed. Please try again.";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>LCCL Ticketing - Login & Sign Up</title>
<link rel="stylesheet" href="assets/style.css">
<style>
body {
  font-family: 'Poppins', Arial, sans-serif;
  background: linear-gradient(120deg, #23243a, #1a1b2f 90%);
  min-height: 100vh;
  margin: 0;
  display: flex;
  align-items: center;
  justify-content: center;
}
.container {
  background: #23243a;
  border-radius: 18px;
  box-shadow: 0 12px 32px rgba(0,0,0,0.22);
  display: flex;
  width: 900px;
  max-width: 98vw;
  overflow: hidden;
  color: #fff;
  transition: box-shadow 0.3s;
}
.left-panel {
  flex: 1;
  background: linear-gradient(135deg, #23243a, #1a1b2f 100%);
  color: #ffd700;
  padding: 60px 35px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  text-align: center;
}
.left-panel img {
  width: 120px;
  margin-bottom: 25px;
  border-radius: 50%;
  box-shadow: 0 4px 16px rgba(0,0,0,0.08);
}
.left-panel h2 {
  font-size: 2.2rem;
  margin-bottom: 12px;
  letter-spacing: 1px;
}
.left-panel p {
  font-size: 1.08rem;
  line-height: 1.6;
  margin-bottom: 0;
}
.right-panel {
  flex: 1;
  padding: 50px 35px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  background: #282a3a;
}
.tab {
  display: flex;
  justify-content: center;
  margin-bottom: 28px;
  gap: 24px;
}
.tab button {
  background: none;
  border: none;
  font-weight: 700;
  font-size: 1.15rem;
  cursor: pointer;
  padding: 12px 28px;
  border-bottom: 3px solid transparent;
  transition: 0.3s;
  color: #ffd700;
}
.tab button.active {
  border-bottom: 3px solid #ffd700;
  color: #ffd700;
}
form {
  display: flex;
  flex-direction: column;
  gap: 16px;
}
form input {
  padding: 14px;
  border-radius: 9px;
  border: 1px solid #444;
  font-size: 1.08em;
  background: #23243a;
  color: #ffd700;
  transition: border 0.2s;
}
form input:focus {
  border: 1.5px solid #ffd700;
  outline: none;
}
form .btn {
  background: linear-gradient(90deg, #ffd700, #ffb347);
  color: #23243a;
  font-weight: 700;
  border-radius: 9px;
  padding: 14px;
  cursor: pointer;
  transition: background 0.2s, color 0.2s;
  border: none;
  font-size: 1.08em;
  box-shadow: 0 2px 8px rgba(0,0,0,0.10);
}
form .btn:hover {
  background: linear-gradient(90deg, #ffb347, #ffd700);
  color: #1a1b2f;
}
.alert {
  background: #ffeded;
  color: #0016d8ff;
  padding: 12px;
  border-radius: 8px;
  margin-bottom: 14px;
  text-align: center;
  font-weight: 600;
}
@media(max-width:900px){
  .container { flex-direction: column; }
  .left-panel, .right-panel { padding: 30px; text-align: center; }
}
</style>
</head>
<body>
<div class="container">
    <div class="left-panel">
        <img src="assets/logo.png" alt="Logo">
        <h2>LCCL Ticketing</h2>
        <p>Book, explore, and enjoy your favorite events — concerts, festivals, and school events. Safe and quick ticketing at your fingertips.</p>
    </div>
    <div class="right-panel">
        <div class="tab">
            <button class="active" onclick="showTab('login')">Login</button>
            <button onclick="showTab('signup')">Sign Up</button>
        </div>
        <?php if ($message): ?>
            <div class="alert"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <form method="POST" id="login">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="login" class="btn">Log In</button>
        </form>
        <form method="POST" id="signup" style="display:none;">
            <input type="text" name="username" placeholder="Username" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            <button type="submit" name="signup" class="btn">Sign Up</button>
        </form>
    </div>
</div>
<script>
function showTab(tab){
    document.getElementById('login').style.display = (tab==='login')?'flex':'none';
    document.getElementById('signup').style.display = (tab==='signup')?'flex':'none';
    document.querySelectorAll('.tab button').forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');
}
</script>
</body>
</html>
