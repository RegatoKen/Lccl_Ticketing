<?php 
session_start();
require 'config/db_connect.php';
if(!isset($_SESSION['user_id'])) header("Location:index.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Home - LCCL Ticketing</title>
<link rel="stylesheet" href="assets/style.css">
<style>
body {
  font-family: 'Poppins', Arial, sans-serif;
  background: linear-gradient(120deg, #23243a, #1a1b2f 90%);
  margin: 0;
  min-height: 100vh;
}
main {
  max-width: 1200px;
  margin: 0 auto;
  padding: 40px 20px;
}
.hero-section {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: linear-gradient(90deg, #23243a 60%, #1a1b2f 100%);
  border-radius: 22px;
  box-shadow: 0 12px 48px rgba(0,0,0,0.22);
  padding: 56px 48px;
  margin-bottom: 48px;
  color: #ffd700;
  transition: box-shadow 0.3s;
  position: relative;
  overflow: hidden;
}
.hero-section::before {
  content: "";
  position: absolute;
  top: -60px; left: -60px;
  width: 180px; height: 180px;
  background: radial-gradient(circle, #ffd70044 0%, transparent 70%);
  z-index: 0;
}
.hero-section::after {
  content: "";
  position: absolute;
  bottom: -60px; right: -60px;
  width: 180px; height: 180px;
  background: radial-gradient(circle, #6cb2ff44 0%, transparent 70%);
  z-index: 0;
}
.hero-content {
  flex: 1;
  position: relative;
  z-index: 1;
}
.hero-content h1 {
  font-size: 3.2rem;
  font-weight: 900;
  margin-bottom: 18px;
  letter-spacing: 1px;
  text-shadow: 0 4px 24px #23243a88;
  animation: fadeInDown 1s;
}
@keyframes fadeInDown {
  from { opacity: 0; transform: translateY(-30px);}
  to { opacity: 1; transform: translateY(0);}
}
.hero-content span {
  color: #ffd700;
  text-shadow: 0 2px 8px rgba(0,0,0,0.08);
}
.hero-content p {
  font-size: 1.35rem;
  margin-bottom: 32px;
  color: #ffe;
  animation: fadeIn 1.2s;
}
@keyframes fadeIn {
  from { opacity: 0;}
  to { opacity: 1;}
}
.hero-buttons {
  display: flex;
  gap: 18px;
}
.hero-btn {
  background: linear-gradient(90deg, #ffd700, #ffb347);
  color: #23243a;
  font-weight: 700;
  border-radius: 12px;
  font-size: 1.18em;
  text-decoration: none;
  box-shadow: 0 4px 18px #ffd70033;
  transition: background 0.2s, color 0.2s, transform 0.18s, box-shadow 0.18s;
  border: none;
  outline: none;
  display: inline-block;
  position: relative;
  z-index: 1;
}
.hero-btn:hover, .hero-btn:focus {
  background: linear-gradient(90deg, #ffb347, #ffd700);
  color: #1a1b2f;
  transform: translateY(-3px) scale(1.04);
  box-shadow: 0 8px 32px #ffd70044;
}
.hero-image {
  position: relative;
  z-index: 1;
}
.hero-image img {
  width: 340px;
  border-radius: 22px;
  box-shadow: 0 8px 32px rgba(0,0,0,0.14);
  transition: box-shadow 0.3s, transform 0.2s;
  animation: fadeInRight 1.2s;
}
@keyframes fadeInRight {
  from { opacity: 0; transform: translateX(40px);}
  to { opacity: 1; transform: translateX(0);}
}
.hero-image img:hover {
  box-shadow: 0 16px 48px #ffd70044;
  transform: scale(1.04) rotate(-2deg);
}
.events-section {
  margin-top: 36px;
}
.events-section h2 {
  font-size: 2.2rem;
  color: #ffd700;
  margin-bottom: 28px;
  font-weight: 800;
  text-shadow: 0 2px 12px #23243a44;
  letter-spacing: 1px;
}
.event-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 36px;
}
.event-card {
  background: linear-gradient(120deg,#23243a 80%,#1a1b2f 100%);
  border-radius: 16px;
  box-shadow: 0 4px 24px rgba(0,0,0,0.18);
  overflow: hidden;
  display: flex;
  flex-direction: column;
  transition: transform 0.18s, box-shadow 0.18s;
  color: #ffd700;
  position: relative;
  z-index: 1;
  animation: fadeInUp 1s;
}
@keyframes fadeInUp {
  from { opacity: 0; transform: translateY(30px);}
  to { opacity: 1; transform: translateY(0);}
}
.event-card:hover {
  transform: translateY(-8px) scale(1.04);
  box-shadow: 0 12px 48px #ffd70044;
  border: 1.5px solid #ffd70088;
}
.event-card img {
  width: 100%;
  height: 180px;
  object-fit: cover;
  border-bottom: 1px solid #333;
}
.event-body {
  padding: 22px 18px 16px 18px;
  flex: 1;
  display: flex;
  flex-direction: column;
}
.event-body h3 {
  font-size: 1.22rem;
  color: #ffd700;
  margin-bottom: 10px;
  font-weight: 700;
  text-shadow: 0 2px 8px #23243a44;
}
.event-body p {
  font-size: 1.05rem;
  color: #ffe;
  margin-bottom: 14px;
  flex: 1;
}
.event-meta {
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-size: 1em;
  color: #ffd700;
  margin-top: 8px;
}
.event-meta .btn.small {
  background: linear-gradient(90deg, #ffd700, #ffb347);
  color: #23243a;
  border-radius: 8px;
  padding: 8px 22px;
  font-size: 1em;
  text-decoration: none;
  font-weight: 700;
  border: none;
  cursor: pointer;
  transition: background 0.2s, color 0.2s, transform 0.18s, box-shadow 0.18s;
  box-shadow: 0 2px 8px #ffd70022;
}
.event-meta .btn.small:hover {
  background: linear-gradient(90deg, #ffb347, #ffd700);
  color: #1a1b2f;
  transform: translateY(-2px) scale(1.04);
  box-shadow: 0 4px 16px #ffd70044;
}
footer {
  text-align: center;
  color: #ffd700;
  margin-top: 48px;
  font-size: 1.12em;
  padding-bottom: 24px;
  letter-spacing: 1px;
  text-shadow: 0 2px 8px #23243a44;
}
@media(max-width:900px){
  .hero-section { flex-direction: column; padding: 28px 10px; }
  .hero-image img { width: 100%; margin-top: 18px; }
  main { padding: 10px 2vw; }
  .event-grid { gap: 18px; }
  .events-section h2 { font-size: 1.3rem; }
  .hero-content h1 { font-size: 2rem; }
}
</style>
</head>
<body>
<?php
// Custom navbar with Inbox link
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<header class="navbar">
  <div class="nav-left">
    <img src="assets/logo.png" class="logo" alt="Logo">
    <h2>LCCL Ticketing</h2>
  </div>
  <nav class="nav-links">
    <a href="home.php" class="<?= basename($_SERVER['PHP_SELF']) == 'home.php' ? 'active' : '' ?>">Home</a>
    <a href="events.php" class="<?= basename($_SERVER['PHP_SELF']) == 'events.php' ? 'active' : '' ?>">Events</a>
    <a href="cart.php" class="<?= basename($_SERVER['PHP_SELF']) == 'cart.php' ? 'active' : '' ?>">Cart</a>
    <a href="inbox.php" class="<?= basename($_SERVER['PHP_SELF']) == 'inbox.php' ? 'active' : '' ?>">Inbox</a>
    <a href="contact.php" class="<?= basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'active' : '' ?>">Contact</a>
    <a href="profile.php" class="<?= basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : '' ?>">Profile</a>
    <a href="logout.php" class="logout-btn">Logout</a>
  </nav>
</header>
<main>
<section class="hero-section">
  <div class="hero-content">
    <h1>Welcome to <span>LCCL Ticketing</span></h1>
    <p>Book, explore, and enjoy your favorite events easily.</p>
    <div class="hero-buttons">
      <a href="events.php" class="hero-btn">Browse Events</a>
      <a href="contact.php" class="hero-btn">Contact and Feedbacks</a>
    </div>
  </div>
  <div class="hero-image"><img src="assets/hero-banner.png" alt="Banner"></div>
</section>

<section class="events-section">
  <h2>Upcoming Events</h2>
  <div class="event-grid">
    <?php
    $stmt=$conn->prepare("SELECT id,title,description,date,image FROM events WHERE date>=CURDATE() ORDER BY date ASC LIMIT 6");
    $stmt->execute();
    $res=$stmt->get_result();
    if($res && $res->num_rows>0){
      while($row=$res->fetch_assoc()):
          $img = (!empty($row['image']) ? (strpos($row['image'], 'uploads/') === 0 ? $row['image'] : 'uploads/' . $row['image']) : 'assets/event-placeholder.png');
    ?>
    <div class="event-card">
      <img src="<?= htmlspecialchars($img) ?>" alt="Event">
      <div class="event-body">
        <h3><?= htmlspecialchars($row['title']) ?></h3>
        <p><?= htmlspecialchars(substr($row['description'],0,100))?>...</p>
        <div class="event-meta">
          <span>📅 <?= date('M d, Y',strtotime($row['date'])) ?></span>
          <a href="event_details.php?id=<?= $row['id'] ?>" class="btn small">Details</a>
        </div>
      </div>
    </div>
    <?php endwhile; } else echo "<p>No upcoming events found.</p>"; ?>
  </div>
</section>

<section class="events-section">
  <h2>Recent Events</h2>
  <div class="event-grid">
    <?php
  $res = $conn->query("SELECT id,title,description,date,image FROM events WHERE date < CURDATE() ORDER BY date DESC LIMIT 6");
    if($res && $res->num_rows>0){
      while($row=$res->fetch_assoc()):
          $img = (!empty($row['image']) ? (strpos($row['image'], 'uploads/') === 0 ? $row['image'] : 'uploads/' . $row['image']) : 'assets/event-placeholder.jpg');
    ?>
    <div class="event-card">
      <img src="<?= htmlspecialchars($img) ?>" alt="Event">
      <div class="event-body">
        <h3><?= htmlspecialchars($row['title']) ?></h3>
        <p><?= htmlspecialchars(substr($row['description'],0,100))?>...</p>
        <div class="event-meta">
          <span>📅 <?= date('M d, Y',strtotime($row['date'])) ?></span>
          <a href="event_details.php?id=<?= $row['id'] ?>" class="btn small">Details</a>
        </div>
      </div>
    </div>
    <?php endwhile; } else echo "<p>No recent events found.</p>"; ?>
  </div>
</section>
</main>
<footer>
<p>© <?= date('Y') ?> LCCL Ticketing System</p>
</footer>
</body>
</html>
