<?php
session_start();
require 'config/db_connect.php';
if(!isset($_SESSION['user_id'])) header("Location:index.php");
if($_SESSION['role']!=='super_admin') { header("Location: index.php"); exit; }

// KPIs for dashboard
$totalRevenue = 0;
$res = $conn->query("SELECT SUM(amount) AS revenue FROM payments WHERE status='paid'");
if($res){ $r=$res->fetch_assoc(); $totalRevenue = floatval($r['revenue'] ?? 0); }

$totalUsers = 0;
$res = $conn->query("SELECT COUNT(*) AS cnt FROM users");
if($res){ $r=$res->fetch_assoc(); $totalUsers = intval($r['cnt'] ?? 0); }

$totalEvents = 0;
$res = $conn->query("SELECT COUNT(*) AS cnt FROM events");
if($res){ $r=$res->fetch_assoc(); $totalEvents = intval($r['cnt'] ?? 0); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Super Admin Dashboard - LCCL Ticketing</title>
<link rel="stylesheet" href="assets/style.css">
<style>
body {
  font-family: 'Poppins', Arial, sans-serif;
  background: linear-gradient(120deg, #23243a, #1a1b2f 90%);
  margin: 0;
  min-height: 100vh;
}
.dashboard-wrap {
  display: flex;
  gap: 0;
  max-width: 1400px;
  margin: 0 auto;
  padding: 0;
}
.sidebar {
  width: 270px;
  background: linear-gradient(180deg,#1a2234,#1e2738);
  border-radius: 0 22px 22px 0;
  padding: 38px 18px 18px 18px;
  color: #fff;
  box-shadow: 0 8px 32px rgba(0,0,0,0.22);
  min-height: 100vh;
  position: relative;
}
.sidebar .logo {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 28px;
}
.sidebar .logo img {
  width: 42px;
  height: 42px;
  object-fit: contain;
}
.sidebar nav {
  margin-bottom: 32px;
}
.sidebar nav a {
  display: block;
  padding: 14px 18px;
  border-radius: 10px;
  color: #ffd700;
  text-decoration: none;
  margin-bottom: 10px;
  font-size: 1.13rem;
  font-weight: 700;
  background: none;
  transition: background 0.2s, color 0.2s;
}
.sidebar nav a.active, .sidebar nav a:hover {
  background: linear-gradient(90deg,#ffd700,#ffb347);
  color: #23243a;
}
.main-content {
  flex: 1;
  padding: 54px 38px 38px 38px;
  background: #23243a;
  border-radius: 0 0 22px 0;
  min-height: 100vh;
  color: #e0e6f6;
}
h1.page-title {
  color: #ffd700;
  margin-bottom: 36px;
  font-size: 2.5rem;
  font-weight: 900;
  letter-spacing: 2px;
  text-align: center;
  text-shadow: 0 2px 16px #0a0e1a;
}
.kpis {
  display: flex;
  gap: 32px;
  margin-bottom: 38px;
  justify-content: center;
}
.kpi {
  background: linear-gradient(135deg,#1e2738,#1a2234);
  padding: 28px 38px;
  border-radius: 15px;
  color: #ffd700;
  border: 1px solid rgba(255,215,0,0.18);
  box-shadow: 0 8px 24px rgba(0,0,0,0.18);
  text-align: center;
  min-width: 180px;
}
.kpi-title {
  font-size: 1.08em;
  color: #b6c3e6;
  margin-bottom: 8px;
  font-weight: 600;
}
.kpi-value {
  font-size: 2.1em;
  font-weight: 900;
  color: #ffd700;
}
.card {
  background: rgba(34, 40, 60, 0.98);
  border-radius: 16px;
  padding: 36px 28px 28px 28px;
  margin-bottom: 32px;
  box-shadow: 0 4px 24px rgba(0,0,0,0.14);
  max-width: 700px;
  margin: 0 auto;
  border: 1.5px solid #ffd700;
}
.card h2 {
  color: #ffd700;
  margin-bottom: 18px;
  font-size: 1.3em;
  font-weight: 700;
  text-align: center;
}
.card ul {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 22px;
}
.card ul li a {
  background: linear-gradient(90deg, #ffd700, #ffb347);
  color: #23243a;
  font-weight: 700;
  border-radius: 10px;
  padding: 15px 38px;
  text-decoration: none;
  font-size: 1.12em;
  box-shadow: 0 2px 12px rgba(0,0,0,0.10);
  transition: background 0.2s, color 0.2s, box-shadow 0.2s;
  display: inline-block;
  letter-spacing: 1px;
  border: 2px solid #ffd700;
}
.card ul li a:hover {
  background: linear-gradient(90deg, #ffb347, #ffd700);
  color: #1a1b2f;
  box-shadow: 0 4px 24px #ffd70044;
  border-color: #ffb347;
}
footer {
  background: #23243a;
  padding: 1.2rem 0 0.7rem 0;
  margin-top: 2rem;
  box-shadow: 0 -2px 16px #10131c44;
}
footer p {
  text-align: center;
  color: #ffd700;
  font-size: 1.08em;
  margin: 0;
}
@media(max-width:900px){
  .dashboard-wrap { flex-direction: column; }
  .sidebar { width: 100%; min-height: auto; border-radius: 0; padding: 18px 8px; }
  .main-content { padding: 18px 8px; border-radius: 0; }
  .kpis { flex-direction: column; gap: 18px; }
  .card { padding: 15px 5px; }
  h1.page-title { font-size: 1.5rem; }
  .card ul li a { padding: 12px 18px; font-size: 1em; }
}
</style>
</head>
<body>
<div class="dashboard-wrap">
  <aside class="sidebar">
    <div class="logo">
      <img src="assets/logo.png" alt="LCCL">
      <span style="font-weight:700;font-size:1.1em;">Super Admin</span>
    </div>
    <nav>
      <a href="super_admin.php" class="active">Dashboard</a>
      <a href="user_management.php">User Management</a>
      <a href="manage_events.php">Event Management</a>
      <a href="admin_messages.php">Feedback & Messages</a>
      <a href="system_settings.php">System Settings</a>
    </nav>
    <div class="sidebar-footer" style="position:absolute;bottom:18px;left:18px;right:18px;text-align:center;">
      <a href="logout.php" style="background:linear-gradient(90deg,#ffd700,#ffb347);color:#23243a;font-weight:700;padding:10px 32px;border-radius:8px;text-decoration:none;box-shadow:0 2px 12px #ffd70022;display:inline-block;">Logout</a>
    </div>
  </aside>
  <main class="main-content">
    <h1 class="page-title">Super Admin Dashboard</h1>
    <div class="kpis">
      <div class="kpi">
        <div class="kpi-title">Total Revenue</div>
        <div class="kpi-value">₱<?= number_format($totalRevenue,2) ?></div>
      </div>
      <div class="kpi">
        <div class="kpi-title">Registered Users</div>
        <div class="kpi-value"><?= number_format($totalUsers) ?></div>
      </div>
      <div class="kpi">
        <div class="kpi-title">Total Events</div>
        <div class="kpi-value"><?= number_format($totalEvents) ?></div>
      </div>
    </div>
  </main>
</div>
<footer>
  <p>© <?= date('Y') ?> LCCL Ticketing System</p>
</footer>
</body>
</html>