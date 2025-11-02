<?php
session_start();
require 'config/db_connect.php';
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_image'])) {
    $file = $_FILES['profile_image'];
    $allowed = ['jpg','jpeg','png','gif'];

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed) || $file['size'] > 2*1024*1024) {
        echo "<script>alert('Invalid file type or size. Max 2MB.'); window.location='profile.php';</script>";
        exit();
    }

    $new_name = uniqid().".".$ext;
    $upload_dir = 'uploads/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

    if (move_uploaded_file($file['tmp_name'], $upload_dir.$new_name)) {
        $stmt = $conn->prepare("UPDATE users SET profile_image=? WHERE id=?");
        $stmt->bind_param("si", $new_name, $user_id);
        $stmt->execute();
        $_SESSION['profile_image'] = $upload_dir.$new_name;
        echo "<script>alert('Profile updated'); window.location='profile.php';</script>";
    } else {
        echo "<script>alert('Upload failed'); window.location='profile.php';</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Profile Image</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-dark text-light">
    <div class="container">
        <h1 class="text-center my-4">Upload Profile Image</h1>
        <form action="profile.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="profile_image">Choose an image</label>
                <input type="file" class="form-control-file" id="profile_image" name="profile_image" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Upload</button>
        </form>
        <div class="text-center my-4">
            <a href="profile.php" class="btn btn-secondary">Back to Profile</a>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
