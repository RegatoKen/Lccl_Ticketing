<?php
session_start();
require 'config/db_connect.php';

if(!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin','super_admin'])) {
    header("Location:index.php");
    exit();
}

// Handle deletion
if(isset($_POST['delete']) && isset($_POST['event_id'])) {
    $id = intval($_POST['event_id']);
    $stmt = $conn->prepare("DELETE FROM events WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

// Fetch all events
$events = [];
$q = "SELECT e.*, u.username as creator,
    (SELECT COUNT(*) FROM tickets t WHERE t.event_id = e.id) as tickets_sold
    FROM events e 
    LEFT JOIN users u ON e.created_by = u.id 
    ORDER BY e.start_date DESC";
$res = $conn->query($q);
if($res) {
    while($row = $res->fetch_assoc()) {
        $events[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Events - LCCL Ticketing</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }
        .event-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 24px;
        }
        .event-card {
            background: #308dffff;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 8px 32px rgba(0,0,0,0.2);
            border: 1px solid rgba(255,215,0,0.1);
        }
        .event-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            background: #1a2234;
        }
        .event-details {
            padding: 20px;
            text-align: center;
        }
        .event-title {
            color: #ffffffff;
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 12px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }
        .event-meta {
            color: #ffffff;
            font-size: 1rem;
            line-height: 2;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
        }
        .event-meta-item {
            background: rgba(26,34,52,0.4);
            padding: 8px 16px;
            border-radius: 8px;
            width: 80%;
            border: 1px solid rgba(255,215,0,0.1);
        }
        .event-meta strong {
            color: #ffffffff;
            margin-right: 8px;
        }
        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            text-align: center;
            cursor: pointer;
            border: none;
            font-size: 1rem;
        }
        .btn-edit {
            background: linear-gradient(90deg,#ffd700,#ffb347);
            color: #1a1b2f;
            box-shadow: 0 4px 12px rgba(255,215,0,0.2);
        }
        .btn-delete {
            background: #d64545;
            color: white;
            box-shadow: 0 4px 12px rgba(214,69,69,0.2);
        }
    </style>
</head>
<body>
    <?php include('includes/navbar.php'); ?>
    
    <div class="container">
        <div class="header">
            <h2 style="color:#ffffff;margin:0">Manage Events</h2>
            <a href="add_event.php" class="btn btn-edit">Add New Event</a>
        </div>

        <div class="event-grid">
            <?php foreach($events as $event): ?>
            <div class="event-card">
                <?php if($event['image']): ?>
                    <?php $img = (strpos($event['image'], 'uploads/') === 0 ? $event['image'] : 'uploads/' . $event['image']); ?>
                    <img src="<?= htmlspecialchars($img) ?>" alt="" class="event-image">
                <?php else: ?>
                <div class="event-image"></div>
                <?php endif; ?>
                
                <div class="event-details">
                    <div class="event-title"><?= htmlspecialchars($event['title']) ?></div>
                    <div class="event-meta">
                        <div class="event-meta-item">
                            <strong>Schedule:</strong> <?= date('M d, Y', strtotime($event['start_date'])) ?> to <?= date('M d, Y', strtotime($event['end_date'])) ?> at <?= htmlspecialchars($event['time']) ?>
                        </div>

                        <div class="event-meta-item">
                            <strong>Venue:</strong> <?= htmlspecialchars($event['venue']) ?>
                        </div>

                        <div class="event-meta-item">
                            <strong>Capacity:</strong> <?= $event['capacity'] ? number_format($event['capacity']) : 'Unlimited' ?>
                        </div>
                        <div class="event-meta-item">
                            <strong>VIP Seats:</strong> <?= number_format($event['vip_seats']) ?> @ ₱<?= number_format($event['vip_price'], 2) ?>
                        </div>

                        <div class="event-meta-item">
                            <strong>Regular Seats:</strong> <?= number_format($event['regular_seats']) ?> @ ₱<?= number_format($event['regular_price'], 2) ?>
                        </div>

                        <div class="event-meta-item">
                            <strong>Tickets sold:</strong> <?= number_format($event['tickets_sold']) ?>
                        </div>
                        <div class="event-meta-item">
                            <strong>Added by:</strong> <?= htmlspecialchars($event['creator'] ?? 'Unknown') ?>
                        </div>
                    </div>
                    
                    <div class="event-actions" style="margin-top:20px;">
                        <a href="edit_event.php?id=<?= $event['id'] ?>" class="btn btn-edit">Edit</a>
                        <form method="POST" style="display:inline" onsubmit="return confirm('Delete this event?')">
                            <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                            <button type="submit" name="delete" class="btn btn-delete">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            
            <?php if(empty($events)): ?>
            <div style="grid-column:1/-1;text-align:center;color:#9fb7e6;padding:40px">
                No events found. Click "Add New Event" to create one.
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>