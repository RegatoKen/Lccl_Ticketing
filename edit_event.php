<?php
session_start();
require 'config/db_connect.php';

// Restrict access to admins
if(!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'super_admin'])) {
    header("Location: index.php");
    exit();
}

// Check event ID
if (!isset($_GET['id'])) {
    die("❌ Event ID not provided.");
}

$id = intval($_GET['id']);
$result = $conn->query("SELECT * FROM events WHERE id = $id");

if (!$result || $result->num_rows === 0) {
    die("❌ Event not found.");
}

$event = $result->fetch_assoc();

// Update event on form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $date = $_POST['date'];
    $price = floatval($_POST['price']);

    $stmt = $conn->prepare("UPDATE events SET title=?, description=?, date=?, price=? WHERE id=?");
    $stmt->bind_param("sssdi", $title, $description, $date, $price, $id);
    $stmt->execute();

    header("Location: manage_events.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Event - LCCL Ticketing</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #1a1b2f 0%, #23243a 100%);
            color: #fff;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 700px;
            background: rgba(26, 34, 52, 0.85);
            margin: 80px auto;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            border: 1px solid rgba(255,215,0,0.15);
        }
        h2 {
            text-align: center;
            color: #ffd700;
            text-shadow: 0 2px 6px rgba(0,0,0,0.5);
            margin-bottom: 25px;
        }
        label {
            font-weight: 600;
            color: #ffd700;
            display: block;
            margin-bottom: 6px;
        }
        input, textarea {
            width: 100%;
            padding: 12px;
            border-radius: 10px;
            border: 1px solid rgba(255,215,0,0.1);
            background: #1a1b2f;
            color: white;
            margin-bottom: 18px;
            font-size: 1rem;
        }
        textarea {
            resize: vertical;
            height: 120px;
        }
        input:focus, textarea:focus {
            outline: none;
            border-color: #ffd700;
            box-shadow: 0 0 8px rgba(255,215,0,0.3);
        }
        .btn {
            padding: 12px 24px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 1rem;
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        .btn-save {
            background: linear-gradient(90deg, #ffd700, #ffb347);
            color: #1a1b2f;
            box-shadow: 0 4px 16px rgba(255,215,0,0.3);
        }
        .btn-save:hover {
            transform: scale(1.05);
        }
        .btn-back {
            background: #d64545;
            color: white;
            box-shadow: 0 4px 16px rgba(214,69,69,0.3);
            margin-left: 10px;
        }
        .btn-back:hover {
            transform: scale(1.05);
        }
        .actions {
            text-align: center;
            margin-top: 25px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Edit Event</h2>
        <form method="POST">
            <label>Title:</label>
            <input type="text" name="title" value="<?= htmlspecialchars($event['title']) ?>" required>

            <label>Description:</label>
            <textarea name="description" required><?= htmlspecialchars($event['description']) ?></textarea>

            <label>Date:</label>
            <input type="date" name="date" value="<?= htmlspecialchars($event['date']) ?>" required>

            <label>Price:</label>
            <input type="number" name="price" step="0.01" value="<?= htmlspecialchars($event['price']) ?>" required>

            <div class="actions">
                <button type="submit" class="btn btn-save">💾 Save Changes</button>
                <a href="manage_events.php" class="btn btn-back">↩ Back</a>
            </div>
        </form>
    </div>
</body>
</html>
