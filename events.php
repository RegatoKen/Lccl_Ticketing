<?php
session_start();
require 'config/db_connect.php';
if(!isset($_SESSION['user_id'])) header("Location:index.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>All Events - LCCL Ticketing</title>
<link rel="stylesheet" href="assets/style.css">
<style>
body {
    background: linear-gradient(135deg, #181c24 0%, #23243a 100%);
    color: #e0e6f6;
    font-family: 'Segoe UI', 'Montserrat', Arial, sans-serif;
    margin: 0;
    min-height: 100vh;
}
.page-title {
    text-align: center;
    font-size: 2.7rem;
    font-weight: 700;
    margin: 2.5rem 0 1.5rem 0;
    letter-spacing: 2px;
    color: #6cb2ff;
    text-shadow: 0 2px 16px #0a0e1a;
}
        .event-grid {
            display: grid !important;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)) !important;
            gap: 1.2rem !important;
            padding: 1.2rem 2vw 2rem 2vw !important;
}
.event-card {
    background: rgba(34, 40, 60, 0.98);
    border-radius: 18px;
    box-shadow: 0 6px 32px 0 #10131c99;
    overflow: hidden;
    transition: transform 0.25s cubic-bezier(.4,2,.3,1), box-shadow 0.25s;
    display: flex;
    flex-direction: column;
    position: relative;
    border: 1.5px solid #2d3a5a;
         min-width: 0 !important;
         max-width: 100% !important;
}
.event-card:hover {
    transform: translateY(-8px) scale(1.03);
    box-shadow: 0 12px 48px 0 #0a0e1aee, 0 0 40px 10px #0054c2ff;
    border-color: #064de6ff;
    background: radial-gradient(circle at 50% 30%, #6cb2ff33 0%, transparent 70%), rgba(34, 40, 60, 0.98);
}
.event-card img {
    width: 100%;
    height: 220px;
    object-fit: cover;
    border-bottom: 1px solid #23283a;
    background: #23283a;
    transition: filter 0.3s;
}
.event-card:hover img {
    filter: brightness(1.15) saturate(1.2);
}
.event-body {
     padding: 0.7rem 0.7rem 0.6rem 0.7rem !important;
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
.event-body h3 {
    font-size: 1.35rem;
    font-weight: 600;
    color: #6cb2ff;
    margin: 0 0 0.7rem 0;
    letter-spacing: 1px;
}
.event-body p {
    font-size: 1rem;
    color: #e0e6f6cc;
    margin-bottom: 1.1rem;
    min-height: 48px;
}
.event-meta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 0.98rem;
}
.event-meta span {
    color: #b3c7f6;
    font-weight: 500;
    letter-spacing: 0.5px;
}
.btn.small {
    background: linear-gradient(90deg, #6cb2ff 0%, #3a8dde 100%);
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 0.5rem 1.1rem;
    font-size: 0.98rem;
    font-weight: 600;
    cursor: pointer;
    box-shadow: 0 2px 8px #23283a44;
    transition: background 0.2s, box-shadow 0.2s;
    text-decoration: none;
}
.btn.small:hover {
    background: linear-gradient(90deg, #3a8dde 0%, #6cb2ff 100%);
    box-shadow: 0 4px 16px #6cb2ff44;
}
footer {
    background: #181c24;
    color: #b3c7f6;
    text-align: center;
    padding: 1.2rem 0 0.7rem 0;
    font-size: 1rem;
    border-top: 1px solid #23283a;
    margin-top: 2rem;
    box-shadow: 0 -2px 16px #10131c44;
}
@media (max-width: 600px) {
    .event-grid {
        grid-template-columns: 1fr;
        padding: 1rem 2vw 2rem 2vw;
    }
    .event-card img {
        height: 160px;
    }
    .event-body {
        padding: 1rem 0.7rem 0.8rem 0.7rem;
    }
}
</style>
</head>
<body>
<?php include('navbar.php'); ?>

<main>
<h1 class="page-title">All Events</h1>
<div class="event-grid">
<?php
$res = $conn->query("SELECT id,title,description,start_date,end_date,time,venue,image FROM events ORDER BY start_date ASC");
if($res && $res->num_rows>0){
    while($row=$res->fetch_assoc()):
        $img=!empty($row['image'])?'uploads/'.$row['image']:'assets/event-placeholder.png';
?>
<div class="event-card">
<img src="<?= htmlspecialchars($img) ?>" alt="Event">
<div class="event-body">
<h3><?= htmlspecialchars($row['title']) ?></h3>
<p><?= htmlspecialchars(substr($row['description'],0,100)) ?>...</p>
<div class="event-meta">
<span>📅 <?= date('M d, Y',strtotime($row['start_date'])) ?> to <?= date('M d, Y',strtotime($row['end_date'])) ?> at <?= htmlspecialchars($row['time']) ?></span>
<span>📍 <?= htmlspecialchars($row['venue']) ?></span>
<a href="event_details.php?id=<?= $row['id'] ?>" class="btn small">View Details</a>
</div>
</div>
</div>
<?php endwhile; } else { echo "<p style='text-align:center'>No events found.</p>"; } ?>
</div>
</main>

<footer>
<p>© <?= date('Y') ?> LCCL Ticketing System</p>
</footer>
</body>
</html>
