<?php
session_start();
include('config/db_connect.php');
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Events - LCCL</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="assets/home.css">
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
h1 {
  color: #ffd700;
  font-size: 2rem;
  font-weight: 700;
  margin-bottom: 24px;
  letter-spacing: 1px;
}
.event-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 32px;
}
.event-card {
  background: #23243a;
  border-radius: 14px;
  box-shadow: 0 4px 16px rgba(0,0,0,0.18);
  overflow: hidden;
  display: flex;
  flex-direction: column;
  transition: transform 0.18s, box-shadow 0.18s;
  color: #ffd700;
}
.event-card:hover {
  transform: translateY(-6px) scale(1.03);
  box-shadow: 0 8px 32px rgba(0,0,0,0.22);
}
.event-card img {
  width: 100%;
  height: 180px;
  object-fit: cover;
  border-bottom: 1px solid #333;
  transition: box-shadow 0.2s;
}
.event-body {
  padding: 18px 16px 14px 16px;
  flex: 1;
  display: flex;
  flex-direction: column;
}
.event-body h3 {
  font-size: 1.18rem;
  color: #ffd700;
  margin-bottom: 8px;
  font-weight: 700;
}
.event-body p {
  font-size: 1.02rem;
  color: #ffe;
  margin-bottom: 12px;
  flex: 1;
}
.event-meta {
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-size: 0.98em;
  color: #ffd700;
}
.event-meta .btn.small {
  background: linear-gradient(90deg, #ffd700, #ffb347);
  color: #23243a;
  border-radius: 7px;
  padding: 7px 18px;
  font-size: 0.98em;
  text-decoration: none;
  font-weight: 600;
  border: none;
  cursor: pointer;
  transition: background 0.2s, color 0.2s;
}
.event-meta .btn.small:hover {
  background: linear-gradient(90deg, #ffb347, #ffd700);
  color: #1a1b2f;
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
<?php include('navbar_simple.php'); ?>
<main style="padding:30px;">
    <h1>All Events</h1>
    <div class="event-grid">
      <?php
      $res = $conn->query("SELECT id,title,description,date,image FROM events ORDER BY date DESC");
      if ($res && $res->num_rows) {
          while ($e = $res->fetch_assoc()) {
              $img = !empty($e['image']) ? 'uploads/'.$e['image'] : 'assets/event-placeholder.jpg';
              echo '<div class="event-card">';
              echo "<img src='{$img}' alt='ev'>";
              echo '<div class="event-body">';
              echo '<h3>'.htmlspecialchars($e['title']).'</h3>';
              echo '<p>'.htmlspecialchars(substr($e['description'],0,120)).'...</p>';
              echo "<div class='event-meta'><span>".htmlspecialchars($e['date'])."</span> <a class='btn small' href='event_details.php?id={$e['id']}'>Details</a></div>";
              echo '</div></div>';
          }
      } else {
          echo '<p>No events found.</p>';
      }
      ?>
    </div>
</main>
<footer>
<p>© <?= date('Y') ?> LCCL Ticketing System</p>
</footer>
</body>
</html>
