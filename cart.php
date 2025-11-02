<?php
session_start();
require 'config/db_connect.php';
if(!isset($_SESSION['user_id'])) header("Location:index.php");
$user_id=$_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Cart - LCCL Ticketing</title>
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
  padding: 44px 32px;
  color: #ffd700;
  text-align: center;
  transition: box-shadow 0.3s;
}
.page-title {
  color: #ffd700;
  font-size: 2rem;
  font-weight: 700;
  margin-bottom: 24px;
  letter-spacing: 1px;
}
ul {
  list-style: none;
  padding: 0;
  margin: 0 0 18px 0;
}
ul li {
  margin-bottom: 14px;
  font-size: 1.08em;
  color: #ffe;
  background: #282a3a;
  border-radius: 8px;
  padding: 12px 18px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.10);
  display: flex;
  align-items: center;
  justify-content: space-between;
  transition: box-shadow 0.2s, background 0.2s;
}
ul li strong {
  color: #ffd700;
}
ul li a {
  color: #f7f1f1ff;
  font-weight: 600;
  margin-left: 18px;
  text-decoration: none;
  border-radius: 6px;
  padding: 6px 14px;
  background: linear-gradient(90deg, #ff5252, #d8000c);
  transition: background 0.2s;
}
ul li a:hover {
  background: linear-gradient(90deg, #d8000c, #ff5252);
}
.btn {
  background: linear-gradient(90deg, #ffd700, #ffb347);
  color: #23243a;
  font-weight: 700;
  border-radius: 8px;
  padding: 12px 24px;
  cursor: pointer;
  border: none;
  font-size: 1.08em;
  box-shadow: 0 2px 8px rgba(0,0,0,0.10);
  margin-top: 10px;
  transition: background 0.2s, color 0.2s;
  text-decoration: none;
  display: inline-block;
}
.btn:hover {
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
<?php include('navbar.php'); ?>
<main>
<h1 class="page-title"><?= htmlspecialchars($_SESSION['username']) ?>'s Cart</h1>
<div class="card">
<?php
$stmt=$conn->prepare("SELECT c.id as cart_id, e.title, e.start_date, e.end_date, e.time, e.venue, e.id as event_id, c.quantity, e.price FROM cart c JOIN events e ON c.event_id=e.id WHERE c.user_id=?");
$stmt->bind_param("i",$user_id);
$stmt->execute();
$res=$stmt->get_result();
if($res->num_rows>0):
  echo '<ul>';
  $total = 0;
  while($row=$res->fetch_assoc()):
    $subtotal = $row['quantity'] * $row['price'];
    $total += $subtotal;
    echo '<li>';
    echo '<div style="flex:1;text-align:left;">';
    echo '<strong>'.htmlspecialchars($row['title']).'</strong><br>';
    echo '<span style="color:#ffd700;font-size:0.98em;">'.date('M d, Y',strtotime($row['start_date'])).' to '.date('M d, Y',strtotime($row['end_date'])).' at '.htmlspecialchars($row['time']).'</span>';
    echo '</div>';
    echo '<div style="min-width:120px;text-align:right;">';
    echo '<span style="color:#ffe;font-size:1.08em;">₱'.number_format($row['price'],2).'</span><br>';
    echo '<span style="color:#ffd700;font-size:1.08em;">Qty: '.$row['quantity'].'</span>';
    echo '<br><span style="color:#ffd700;font-size:0.98em;">Subtotal: ₱'.number_format($subtotal,2).'</span>';
    echo '</div>';
    echo '<a href="remove_from_cart.php?id='.$row['cart_id'].'" style="margin-left:18px;">Remove</a>';
    echo '</li>';
  endwhile;
  echo '</ul>';
  echo '<div style="margin:18px 0;font-size:1.2em;color:#ffd700;font-weight:700;">Total: ₱'.number_format($total,2).'</div>';
  echo '<a href="checkout.php" class="btn">Proceed to Checkout</a>';
else:
  echo '<p>Your cart is empty. <a href="events.php" style="color:#ffd700;text-decoration:underline;">Browse events</a>.</p>';
endif;
?>
</div>
</main>
<footer>
<p>© <?= date('Y') ?> LCCL Ticketing System</p>
</footer>
</body>
</html>
