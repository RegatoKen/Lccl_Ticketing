<?php
session_start();
require 'config/db_connect.php';
if(!isset($_SESSION['user_id'])) header('Location: index.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Contact / Feedback - LCCL Ticketing</title>
<link rel="stylesheet" href="assets/style.css">
<style>
main { max-width:720px; margin:90px auto; padding:20px; }
.card { background:#fff; padding:20px; border-radius:10px; box-shadow:0 4px 12px rgba(0,0,0,0.06); }
label{display:block;margin:10px 0 6px}
input[type=text], textarea{width:100%;padding:10px;border:1px solid #ddd;border-radius:6px}
.btn-primary{background:#6cb2ff;color:#fff;padding:10px 16px;border-radius:8px;border:none;cursor:pointer}
.btn-primary:hover{background:#3a8dde}
.notification { 
    padding: 15px 20px;
    margin-bottom: 20px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    animation: slideIn 0.5s ease;
}
.notification.success {
    background-color: #d4edda;
    border: 1px solid #c3e6cb;
    color: #155724;
}
@keyframes slideIn {
    from { transform: translateY(-20px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}
</style>
</head>
<body>
<?php include 'includes/navbar.php'; ?>
<main>
<?php if(isset($_SESSION['flash'])): ?>
    <div class="notification success">
        <?= htmlspecialchars($_SESSION['flash']) ?>
    </div>
    <?php unset($_SESSION['flash']); ?>
<?php endif; ?>
<div class="card">
<h2>Contact / Feedback</h2>
<p>Send us your feedback or questions and our admins will review them.</p>
<form method="POST" action="submit_contact.php">
<label for="subject">Subject</label>
<input type="text" name="subject" id="subject" required>
<label for="message">Message</label>
<textarea name="message" id="message" rows="6" required></textarea>
<input type="hidden" name="user_id" value="<?= $_SESSION['user_id'] ?>">
<button type="submit" class="btn-primary">Send Message</button>
</form>
</div>
</main>
<?php include 'includes/footer.php'; ?>
</body>
</html>