<?php
session_start();
require 'config/db_connect.php';

if(!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin','super_admin'])) {
    header("Location:index.php");
    exit();
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $date = $_POST['date'] ?? '';
    $price = floatval($_POST['price'] ?? 0);
    $capacity = intval($_POST['capacity'] ?? 0);
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';
    $time = $_POST['time'] ?? '';
    $venue = trim($_POST['venue'] ?? '');
    $vip_seats = intval($_POST['vip_seats'] ?? 0);
    $regular_seats = intval($_POST['regular_seats'] ?? 0);
    $vip_price = floatval($_POST['vip_price'] ?? 0);
    $regular_price = floatval($_POST['regular_price'] ?? 0);

    $image = null;
    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif'];
        if (in_array($ext, $allowed)) {
            $new_name = uniqid().".".$ext;
            $upload_dir = 'uploads/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir.$new_name);
            $image = $new_name;
        }
    }
    if ($title && $start_date && $end_date && $time && $venue) {
    $total_seats = $vip_seats + $regular_seats;

    if ($capacity > 0 && $total_seats > $capacity) {
        $message = "Total seats (VIP + Regular) exceed the event capacity.";
    } else {
        $stmt = $conn->prepare("INSERT INTO events (title, description, start_date, end_date, time, venue, price, capacity, vip_seats, regular_seats, vip_price, regular_price, created_by, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssiiiidds", $title, $description, $start_date, $end_date, $time, $venue, $price, $capacity, $vip_seats, $regular_seats, $vip_price, $regular_price, $_SESSION['user_id'], $image);
        if ($stmt->execute()) {
            header("Location: manage_events.php?success=1");
            exit();
        } else {
            $message = "Error adding event.";
        }
    }
} else {
    $message = "Please fill in all required fields: Title, Start Date, End Date, Time, and Venue.";
}

}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New Event - LCCL Ticketing</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .form-container {
            max-width: 800px;
            margin: 40px auto;
            background: #4a9bfdff;
            padding: 32px;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.2);
        }
        .form-group { margin-bottom: 20px; }
        label {
            display: block;
            color: #ffffffff;
            margin-bottom: 8px;
            font-weight: 600;
        }
        input, textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid rgba(255,215,0,0.1);
            border-radius: 8px;
            background: #ffffffcb;
            color: #000000ff;
        }
        .btn-submit {
            background: linear-gradient(90deg,#ffd700,#ffb347);
            color: #1a1b2f;
            border: none;
            padding: 14px 28px;
            border-radius: 10px;
            font-weight: 700;
            cursor: pointer;
            font-size: 1.1rem;
        }
    </style>
</head>
<body>
    <?php include('includes/navbar.php'); ?>
    
    <div class="form-container">
        <h2 style="color:#ffffff;margin-bottom:24px;font-size:1.8rem">Add New Event</h2>
        
        <?php if($message): ?>
        <div style="padding:12px;background:rgba(255,215,0,0.1);color:#ffd700;border-radius:8px;margin-bottom:16px">
            <?php echo htmlspecialchars($message); ?>
        </div>
        <?php endif; ?>
        
    <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Event Title*</label>
                <input type="text" name="title" required>
            </div>
            
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="4"></textarea>
            </div>
            
            <div class="form-group">
                <label>Start Date*</label>
                <input type="date" name="start_date" required>
            </div>

            <div class="form-group">
                <label>End Date*</label>
                <input type="date" name="end_date" required>
            </div>

            <div class="form-group">
                <label>Event Time*</label>
                <input type="time" name="time" required>
            </div>

            <div class="form-group">
                <label>Venue*</label>
                <input type="text" name="venue" required>
            </div>

            <div class="form-group">
                <label>Capacity (Tickets)</label>
                <input type="number" name="capacity" min="0" value="0">
            </div>
            
            <div class="form-group">
                <label>VIP Seats</label>
                <input type="number" name="vip_seats" min="0" value="0">
            </div>

            <div class="form-group">
                <label>Regular Seats</label>
                <input type="number" name="regular_seats" min="0" value="0">
            </div>

            <div class="form-group">
                <label>VIP Price (₱)</label>
                <input type="number" name="vip_price" step="0.01" min="0" value="0">
            </div>

            <div class="form-group">
                <label>Regular Price (₱)</label>
                <input type="number" name="regular_price" step="0.01" min="0" value="0">
            </div>


            <div class="form-group">
                <label>Event Image</label>
                <input type="file" name="image" accept="image/*">
            </div>
            <button type="submit" class="btn-submit">Create Event</button>
        </form>
    </div>
</body>
</html>