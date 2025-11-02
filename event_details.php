<?php
session_start();
require 'config/db_connect.php';
if(!isset($_SESSION['user_id'])){ header("Location: index.php"); exit(); }

if(!isset($_GET['id'])){ header("Location: events.php"); exit(); }
$id=intval($_GET['id']);
$stmt=$conn->prepare("SELECT * FROM events WHERE id=?");
$stmt->bind_param("i",$id);
$stmt->execute();
$res=$stmt->get_result();
$event=$res->fetch_assoc();
if(!$event){ echo "<script>alert('Event not found'); window.location='events.php';</script>"; exit(); }
include 'includes/navbar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($event['title']) ?> - Event Details</title>
    <link rel="stylesheet" href="assets/style.css">
<style>
body {
    background: linear-gradient(135deg, #181c24 0%, #23283a 100%);
    color: #e0e6f6;
    font-family: 'Segoe UI', 'Montserrat', Arial, sans-serif;
    margin: 0;
    min-height: 100vh;
}
.event-details-card {
    background: rgba(34, 40, 60, 0.98);
    border-radius: 22px;
    box-shadow: 0 8px 40px 0 #10131c99;
    max-width: 700px;
    margin: 56px auto 32px auto;
    padding: 32px;
    text-align: center;
}
.price-tag {
    font-size: 1.5rem;
    color: #4CAF50;
    font-weight: bold;
    margin: 20px 0;
}
.quantity-controls {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 15px;
    margin: 20px 0;
}
.quantity-controls button {
    background: #2c3347;
    border: none;
    color: white;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    cursor: pointer;
    font-size: 18px;
    transition: background 0.3s;
}
.quantity-controls button:hover {
    background: #3c445c;
}
.quantity-controls input {
    width: 60px;
    text-align: center;
    font-size: 18px;
    padding: 8px;
    border: 2px solid #2c3347;
    border-radius: 8px;
    background: rgba(255,255,255,0.1);
    color: white;
}
.add-to-cart-btn {
    background: #4CAF50;
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 8px;
    font-size: 16px;
    cursor: pointer;
    transition: background 0.3s;
    margin-top: 20px;
}
.add-to-cart-btn:hover {
    background: #45a049;
}
	padding: 2.2rem 2.2rem 2rem 2.2rem;
	display: flex;
	flex-direction: column;
	align-items: center;
	border: 1.5px solid #2d3a5a;
	transition: box-shadow 0.25s, border-color 0.25s;
}
.event-details-card:hover {
	box-shadow: 0 16px 64px 0 #0a0e1aee;
	border-color: #6cb2ff;
}
.event-details-card img {
	width: 100%;
	max-height: 320px;
	object-fit: cover;
	border-radius: 16px;
	margin-bottom: 1.7rem;
	background: #23283a;
	box-shadow: 0 2px 16px #23283a44;
	transition: filter 0.3s;
}
.event-details-card:hover img {
	filter: brightness(1.12) saturate(1.15);
}
.event-details-card h2 {
	font-size: 2.1rem;
	font-weight: 700;
	color: #6cb2ff;
	margin-bottom: 1.1rem;
	text-align: center;
	letter-spacing: 1.5px;
	text-shadow: 0 2px 16px #0a0e1a;
}
.event-details-card p {
	font-size: 1.08rem;
	color: #e0e6f6cc;
	margin-bottom: 1.1rem;
	text-align: center;
}
.event-details-card p strong {
	color: #6cb2ff;
	font-weight: 600;
}
.event-details-card form {
	margin-top: 1.5rem;
	width: 100%;
	display: flex;
	justify-content: center;
}
.btn {
	background: linear-gradient(90deg, #6cb2ff 0%, #3a8dde 100%);
	color: #fff;
	border: none;
	border-radius: 10px;
	padding: 0.7rem 2.2rem;
	font-size: 1.08rem;
	font-weight: 600;
	cursor: pointer;
	box-shadow: 0 2px 12px #23283a44;
	transition: background 0.2s, box-shadow 0.2s;
	text-decoration: none;
}
.btn:hover {
	background: linear-gradient(90deg, #3a8dde 0%, #6cb2ff 100%);
	box-shadow: 0 4px 24px #6cb2ff44;
}
@media (max-width: 700px) {
	.event-details-card {
		padding: 1.2rem 0.7rem 1rem 0.7rem;
	}
	.event-details-card img {
		max-height: 180px;
	}
	.event-details-card h2 {
		font-size: 1.3rem;
	}
}
</style>

<main class="event-details-card">
	<img src="<?= !empty($event['image'])?'uploads/'.htmlspecialchars($event['image']):'assets/event-placeholder.png' ?>" alt="Event Image">
	<h2><?= htmlspecialchars($event['title']) ?></h2>
	<?php
$formatted_time = date('g:i A', strtotime($event['time']));
?>
	<p><strong>Schedule:</strong> <?= date('F d, Y',strtotime($event['start_date'])) ?> to <?= date('F d, Y',strtotime($event['end_date'])) ?> at <?= $formatted_time ?></p>
	<p><strong>Venue:</strong> <?= htmlspecialchars($event['venue']) ?></p>
	<p><?= nl2br(htmlspecialchars($event['description'])) ?></p>
	<div class="price-tag">
    <p><strong>VIP Price:</strong> ₱<?= number_format($event['vip_price'], 2) ?></p>
    <p><strong>Regular Price:</strong> ₱<?= number_format($event['regular_price'], 2) ?></p>
	<p><strong>VIP Seats:</strong> <?= number_format($event['vip_seats']) ?></p>
	<p><strong>Regular Seats:</strong> <?= number_format($event['regular_seats']) ?></p>
</div>
	<form method="POST" action="add_to_cart.php" style="flex-direction: column; align-items: center;">
		<input type="hidden" name="event_id" value="<?= $event['id'] ?>">
		<select name="ticket_type" required style="margin-bottom: 12px; padding: 8px; border-radius: 6px;">
  <option value="Regular">Regular - ₱<?= number_format($event['regular_price'], 2) ?></option>
  <option value="VIP">VIP - ₱<?= number_format($event['vip_price'], 2) ?></option>
</select>

		<div class="quantity-controls">
			<button type="button" onclick="updateQuantity(-1)">-</button>
			<input type="number" name="quantity" id="quantity" value="1" min="1" max="10">
			<button type="button" onclick="updateQuantity(1)">+</button>
		</div>
		<button type="submit" class="btn">Add to Cart</button>
	</form>
	<script>
	function updateQuantity(change) {
		const input = document.getElementById('quantity');
		let value = parseInt(input.value) + change;
		value = Math.max(1, Math.min(10, value));
		input.value = value;
	}
	</script>
</main>
<?php include 'includes/footer.php'; ?>
