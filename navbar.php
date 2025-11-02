<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if(!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$current_page = basename($_SERVER['PHP_SELF']);
?>
<style>
.main-navbar {
  background: linear-gradient(90deg,#23243a 70%,#1a1b2f 100%);
  color: #fff;
  padding: 18px 36px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 2px 12px rgba(0,0,0,0.18);
  transition: background 0.3s;
  font-family: 'Poppins', Arial, sans-serif;
}
.main-navbar .brand {
  font-weight: bold;
  font-size: 1.35em;
  letter-spacing: 1.5px;
  color: #ffd700;
  text-shadow: 0 2px 12px #23243a44;
}
.main-navbar .nav-links {
  display: flex;
  gap: 32px;
}
.main-navbar .nav-link {
  color: #fff;
  text-decoration: none;
  font-weight: 600;
  font-size: 1.08em;
  padding-bottom: 2px;
  border-bottom: 2px solid transparent;
  transition: color 0.2s, border-bottom 0.2s;
}
.main-navbar .nav-link.active {
  color: #ffd700;
  border-bottom: 2px solid #ffd700;
}
.main-navbar .nav-link:hover {
  color: #ffd700;
  border-bottom: 2px solid #ffd700;
}
@media(max-width:700px){
  .main-navbar { flex-direction: column; padding: 12px 8px; }
  .main-navbar .nav-links { gap: 18px; }
  .main-navbar .brand { font-size: 1.1em; }
}
</style>
<nav class="main-navbar">
  <div class="brand">LCCL Ticketing</div>
  <div class="nav-links">
    <?php if(in_array($_SESSION['role'], ['admin','super_admin'])): ?>
        <a href="admin_dashboard.php" class="nav-link<?= $current_page=='admin_dashboard.php'?' active':'' ?>">Admin Panel</a>
        <a href="logout.php" class="nav-link" style="color:#ffd700;">Logout</a>
    <?php else: ?>
          <a href="home.php" class="<?= basename($_SERVER['PHP_SELF']) == 'home.php' ? 'active' : '' ?>">Home</a>
          <a href="events.php" class="<?= basename($_SERVER['PHP_SELF']) == 'events.php' ? 'active' : '' ?>">Events</a>
          <a href="cart.php" class="<?= basename($_SERVER['PHP_SELF']) == 'cart.php' ? 'active' : '' ?>">Cart</a>
          <a href="inbox.php" class="<?= basename($_SERVER['PHP_SELF']) == 'inbox.php' ? 'active' : '' ?>">Inbox</a>
          <a href="profile.php" class="<?= basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : '' ?>">Profile</a>
          <a href="logout.php" class="logout-btn">Logout</a>
    <?php endif; ?>
  </div>
</nav>
