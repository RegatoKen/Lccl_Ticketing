<?php
session_start();
require 'config/db_connect.php';
if(!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin','super_admin'])) {
    header("Location:index.php");
    exit();
}
include 'includes/navbar.php';

// Create messages table if it doesn't exist
$createMessages = "
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    status VARCHAR(20) DEFAULT 'new',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";
$conn->query($createMessages);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Admin Dashboard - LCCL Ticketing</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="assets/style.css">
<style>
/* Enhanced Dashboard layout */
.dashboard-wrap {
    display: flex;
    gap: 24px;
    max-width: 1400px;
    margin: 28px auto;
    padding: 20px;
}

.sidebar {
    width: 280px;
    background: linear-gradient(180deg,#1a2234,#1e2738);
    border-radius: 15px;
    padding: 24px;
    color: #ffffff;
    box-shadow: 0 8px 32px rgba(0,0,0,0.3);
}

.sidebar .logo {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 18px;
}

.sidebar .logo img {
    width: 42px;
    height: 42px;
    object-fit: contain;
}

.sidebar nav a {
    display: block;
    padding: 12px 16px;
    border-radius: 10px;
    color: #fff;
    text-decoration: none;
    margin-bottom: 8px;
    transition: all 0.3s;
    font-size: 1.1rem;
    font-weight: 600;
}

.sidebar nav a:hover {
    background: rgba(255,255,255,0.1);
    transform: translateX(6px);
}

.sidebar nav a.active {
    background: linear-gradient(90deg,#ffd700,#ffb347);
    color: #1a1b2f;
    box-shadow: 0 6px 18px rgba(255,215,0,0.2);
}

.content {
    flex: 1;
    min-height: 400px;
    background: #1a2234;
    border-radius: 15px;
    padding: 24px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.2);
}

.header {
    background: linear-gradient(90deg,#1e2738,#1a2234);
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 24px;
}

.header h2 {
    color: #ffffff;
    font-size: 1.8rem;
    margin: 0;
}

.kpis {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 28px;
}

.kpi {
    background: linear-gradient(135deg,#1e2738,#1a2234);
    padding: 20px;
    border-radius: 15px;
    color: #ffffff;
    border: 1px solid rgba(255,215,0,0.1);
    box-shadow: 0 8px 24px rgba(0,0,0,0.2);
}

.section {
    background: #1e2738;
    padding: 24px;
    border-radius: 15px;
    border: 1px solid rgba(255,215,0,0.1);
    margin-bottom: 24px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.2);
}

.table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0 8px;
}

.table th {
    color: #ffd700;
    font-weight: 600;
    padding: 12px;
    font-size: 1.1rem;
}

.table td {
    background: rgba(30,39,56,0.6);
    padding: 16px;
    color: #ffffff;
}

.table tr:hover td {
    background: rgba(30,39,56,0.8);
}

.badge {
    background: #ffd700;
    color: #1a1b2f;
    padding: 8px 16px;
    border-radius: 999px;
    font-weight: 700;
    font-size: 0.9rem;
}

/* Event Management Buttons */
.action-buttons {
    display: flex;
    gap: 12px;
    margin-bottom: 20px;
}

.btn-action {
    background: linear-gradient(90deg,#ffd700,#ffb347);
    color: #1a1b2f;
    border: none;
    padding: 12px 24px;
    border-radius: 10px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-action:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(255,215,0,0.2);
}

.btn-delete {
    background: linear-gradient(90deg,#ff4747,#d63030);
    color: white;
}
</style>
</head>
<body>
<?php // navbar already included above ?>

<main class="dashboard-wrap">
  <!-- LEFT SIDEBAR -->
  <aside class="sidebar" role="navigation" aria-label="Admin menu">
    <div class="logo">
      <img src="assets/logo-small.png" alt="Logo">
      <div style="font-weight:700;font-size:1rem">LCCL Admin</div>
    </div>

    <nav>
      <a href="admin_dashboard.php" class="active">Overview</a>
      <a href="manage_events.php">Events</a>
  <a href="admin_messages.php">Feedback</a>
  <a href="update_ticket_status.php" style="margin-top:8px;background:#ffd700;color:#222;font-weight:700;border-radius:8px;padding:10px 16px;display:block;">Update Ticket Status</a>
    </nav>

    <div style="margin-top:14px;border-top:1px dashed rgba(255,255,255,0.03);padding-top:12px">
      <div class="small-muted">Signed in as</div>
      <div style="font-weight:700"><?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?></div>
      <?php
      // Show latest payment status for this user
      $user_id = $_SESSION['user_id'] ?? 0;
      $pay_status = null;
      $pay_id = null;
      if ($user_id) {
        $stmt = $conn->prepare("SELECT id, status FROM payments WHERE user_id=? ORDER BY created_at DESC LIMIT 1");
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
          $pay_status = $row['status'];
          $pay_id = $row['id'];
        }
      }
      ?>
      <div style="margin-top:10px">
        <?php if ($pay_status): ?>
          <span class="badge" style="background:<?= ($pay_status==='paid')?'#38bdf8':'#ffd700' ?>;color:#222;padding:6px 12px;">
            Payment Status: <?= ucfirst($pay_status) ?>
          </span>
        <?php endif; ?>
        <?php if ($pay_id): ?>
          <form method="post" action="" style="display:inline;margin-left:8px">
            <input type="hidden" name="edit_payment_id" value="<?= $pay_id ?>">
            <select name="new_status" style="padding:4px 8px;border-radius:6px;">
              <option value="paid" <?= $pay_status==='paid'?'selected':'' ?>>Paid</option>
              <option value="pending" <?= $pay_status==='pending'?'selected':'' ?>>Pending</option>
            </select>
            <button type="submit" style="background:#38bdf8;color:#fff;border:none;padding:4px 10px;border-radius:6px;font-weight:600;cursor:pointer;">Update</button>
          </form>
        <?php endif; ?>
        <?php
        // Handle payment status update
        if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['edit_payment_id'], $_POST['new_status'])) {
          $pid = intval($_POST['edit_payment_id']);
          $new_status = ($_POST['new_status']==='paid') ? 'paid' : 'pending';
          $stmt = $conn->prepare("UPDATE payments SET status=? WHERE id=?");
          $stmt->bind_param('si', $new_status, $pid);
          $stmt->execute();
          echo "<script>window.location='admin_dashboard.php';</script>";
        }
        ?>
      </div>
      <div style="margin-top:8px">
        <a href="logout.php" style="color:#ffd770;text-decoration:none;font-weight:700">Sign out</a>
      </div>
    </div>
  </aside>

  <!-- MAIN CONTENT -->
  <section class="content">
    <div class="header">
        <h2>Admin Dashboard</h2>
        <div style="color:#ffd700;margin-top:8px;font-size:1.1rem">Manage Events and Monitor Activity</div>
    </div>

    <!-- Event Management Buttons -->
    <div class="action-buttons">
        <a href="add_event.php" class="btn-action">
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 5v14M5 12h14"></path>
            </svg>
            Add New Event
        </a>
        <a href="manage_events.php" class="btn-action">
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
            Manage Events
        </a>
    </div>

    <?php
    // KPIs
  // total tickets sold
  $totalTickets = 0;
  $res = $conn->query("SELECT SUM(COALESCE(quantity,1)) AS sold FROM tickets");
  if($res){ $r=$res->fetch_assoc(); $totalTickets = intval($r['sold'] ?? 0); }

  // open/upcoming events count
  $upcomingCount = 0;
  $res = $conn->query("SELECT COUNT(*) AS cnt FROM events WHERE date >= CURDATE()");
  if($res){ $r=$res->fetch_assoc(); $upcomingCount = intval($r['cnt'] ?? 0); }

  // unread feedbacks (if messages table exists)
  $unread = 0;
  $checkTable = $conn->query("SHOW TABLES LIKE 'messages'");
  if($checkTable && $checkTable->num_rows > 0) {
    $res = $conn->query("SELECT COUNT(*) AS cnt FROM messages WHERE status='new'");
    if($res){ 
      $r = $res->fetch_assoc(); 
      $unread = intval($r['cnt'] ?? 0); 
    }
  }

  // total revenue (from payments table, paid only)
  $totalRevenue = 0;
  $res = $conn->query("SELECT SUM(amount) AS revenue FROM payments WHERE status='paid'");
  if($res){ $r=$res->fetch_assoc(); $totalRevenue = floatval($r['revenue'] ?? 0); }
    ?>

    <div class="kpis">
      <div class="kpi">
        <div class="small-muted">Total Revenue</div>
        <div style="font-size:1.6rem;font-weight:800;color:#ffd700">₱<?= number_format($totalRevenue,2) ?></div>
      </div>
      <div class="kpi">
        <div class="small-muted">Tickets sold</div>
        <div style="font-size:1.6rem;font-weight:800"><?= number_format($totalTickets) ?></div>
      </div>
      <div class="kpi">
        <div class="small-muted">Upcoming events</div>
        <div style="font-size:1.6rem;font-weight:800"><?= number_format($upcomingCount) ?></div>
      </div>
      <div class="kpi">
        <div class="small-muted">New feedback</div>
        <div style="font-size:1.6rem;font-weight:800"><?= number_format($unread) ?></div>
      </div>
    </div>

    <!-- Tickets Availed (recent purchases) -->
    <div class="section">
      <h3 style="margin:0 0 12px 0;color:#cfe8ff">Recently Purchased Tickets</h3>
      <?php
      $tickets = [];
      $q = "
        SELECT t.id AS ticket_id, t.user_id, COALESCE(t.quantity,1) AS qty, t.created_at,
               e.id AS event_id, e.title
        FROM tickets t
        LEFT JOIN events e ON t.event_id = e.id
        ORDER BY t.created_at DESC
        LIMIT 12
      ";
      if($res = $conn->query($q)){
        while($row = $res->fetch_assoc()) $tickets[] = $row;
      }
      ?>

      <?php if(empty($tickets)): ?>
        <div class="empty">No tickets found.</div>
      <?php else: ?>
        <table class="table" aria-describedby="recent-tickets">
          <thead>
            <tr><th>Event</th><th>Quantity</th><th>User ID</th><th>Purchased</th></tr>
          </thead>
          <tbody>
          <?php foreach($tickets as $t): ?>
            <tr>
              <td><?= htmlspecialchars($t['title'] ?? '—') ?></td>
              <td><?= intval($t['qty']) ?></td>
              <td><?= intval($t['user_id']) ?></td>
              <td class="small-muted"><?= htmlspecialchars($t['created_at']) ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>

    <!-- Open tickets / Upcoming events -->
    <div class="section">
      <h3 style="margin:0 0 12px 0;color:#cfe8ff">Upcoming Events & Availability</h3>
      <?php
      $events = [];
      // Attempt to compute sold per event and capacity if available
      $q = "
        SELECT e.id, e.title, e.date,
               COALESCE(e.capacity, 0) AS capacity,
               COALESCE((
                 SELECT SUM(COALESCE(t.quantity,1)) FROM tickets t WHERE t.event_id = e.id
               ),0) AS sold
        FROM events e
        WHERE e.date >= CURDATE()
        ORDER BY e.date ASC
        LIMIT 12
      ";
      if($res = $conn->query($q)){
        while($row = $res->fetch_assoc()) $events[] = $row;
      }
      ?>

      <?php if(empty($events)): ?>
        <div class="empty">No upcoming events.</div>
      <?php else: ?>
        <table class="table" aria-describedby="upcoming-events">
          <thead>
            <tr><th>Event</th><th>Date</th><th>Capacity</th><th>Sold</th><th>Available</th></tr>
          </thead>
          <tbody>
          <?php foreach($events as $e): 
             $cap = intval($e['capacity']);
             $sold = intval($e['sold']);
             $available = ($cap > 0) ? max(0, $cap - $sold) : null;
          ?>
            <tr>
              <td><?= htmlspecialchars($e['title']) ?></td>
              <td class="small-muted"><?= htmlspecialchars($e['date']) ?></td>
              <td><?= $cap > 0 ? number_format($cap) : '<span class="small-muted">N/A</span>' ?></td>
              <td><?= number_format($sold) ?></td>
              <td>
                <?php if($available === null): ?>
                  <span class="small-muted">Unlimited</span>
                <?php else: ?>
                  <?php if($available <= 0): ?>
                    <span class="badge" style="background:#d64545;color:white">Sold out</span>
                  <?php else: ?>
                    <span class="badge"><?= number_format($available) ?> left</span>
                  <?php endif; ?>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>

    <!-- Tickets Summary Section -->
    <div class="section">
      <h3 style="margin:0 0 12px 0;color:#cfe8ff">Tickets Summary</h3>
      <?php
      // Get detailed ticket summary
      $ticketSummary = [];
      $q = "
        SELECT 
          e.title,
          e.date,
          e.price,
          COUNT(DISTINCT t.user_id) as unique_buyers,
          SUM(COALESCE(t.quantity,1)) as total_tickets,
          SUM(COALESCE(t.quantity,1) * e.price) as total_revenue
        FROM tickets t
        JOIN events e ON t.event_id = e.id
        GROUP BY e.id, e.title, e.date, e.price
        ORDER BY e.date DESC
      ";
      if($res = $conn->query($q)){
        while($row = $res->fetch_assoc()) $ticketSummary[] = $row;
      }
      ?>

      <?php if(empty($ticketSummary)): ?>
        <div style="text-align:center;color:#9fb7e6;padding:20px">No ticket sales recorded yet.</div>
      <?php else: ?>
        <table class="table">
          <thead>
            <tr>
              <th>Event</th>
              <th>Date</th>
              <th>Price</th>
              <th>Unique Buyers</th>
              <th>Total Tickets</th>
              <th>Revenue</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach($ticketSummary as $ts): ?>
            <tr>
              <td style="color:#ffd700;font-weight:600"><?= htmlspecialchars($ts['title']) ?></td>
              <td><?= date('M d, Y', strtotime($ts['date'])) ?></td>
              <td>₱<?= number_format($ts['price'], 2) ?></td>
              <td><?= number_format($ts['unique_buyers']) ?></td>
              <td><?= number_format($ts['total_tickets']) ?></td>
              <td style="color:#ffd700">₱<?= number_format($ts['total_revenue'], 2) ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="3" style="text-align:right;font-weight:600">Total:</td>
              <td><?= number_format(array_sum(array_column($ticketSummary, 'unique_buyers'))) ?></td>
              <td><?= number_format(array_sum(array_column($ticketSummary, 'total_tickets'))) ?></td>
              <td style="color:#ffd700;font-weight:600">₱<?= number_format(array_sum(array_column($ticketSummary, 'total_revenue')), 2) ?></td>
            </tr>
          </tfoot>
        </table>
      <?php endif; ?>
    </div>

  </section>
</main>

<footer style="text-align:center;padding:16px;color:#9fb7e6">© <?= date('Y') ?> LCCL Ticketing</footer>
</body>
</html>
