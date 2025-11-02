<?php
include('config/db_connect.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';

  // Basic validation
  if (strlen($username) < 3 || strlen($password) < 6 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "<script>alert('Please provide a valid username (3+ chars), password (6+ chars), and email address.'); window.location='signup.php';</script>";
    exit;
  }

  // Check if username already exists
  $check = $conn->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
  $check->bind_param('s', $username);
  $check->execute();
  $checkRes = $check->get_result();
  if ($checkRes && $checkRes->num_rows > 0) {
    echo "<script>alert('Username already taken.'); window.location='signup.php';</script>";
    exit;
  }

  // Hash password
  $hash = password_hash($password, PASSWORD_DEFAULT);

  $sql = "INSERT INTO users (username, email, password, role, created_at) VALUES (?, ?, ?, 'customer', NOW())";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("sss", $username, $email, $hash);
  
  if ($stmt->execute()) {
    // Verify the role was set correctly
    $new_user_id = $stmt->insert_id;
    $check_role = $conn->prepare("SELECT role FROM users WHERE id = ?");
    $check_role->bind_param("i", $new_user_id);
    $check_role->execute();
    $role_result = $check_role->get_result()->fetch_assoc();
    
    if ($role_result && $role_result['role'] === 'customer') {
      echo "<script>alert('Account created successfully! You are registered as a customer.'); window.location='index.php';</script>";
    } else {
      echo "<script>alert('Account created but role may not be set properly.'); window.location='index.php';</script>";
    }
  } else {

  if ($stmt->execute()) {
    echo "<script>alert('Signup successful! You can now login.'); window.location='index.php';</script>";
  } else {
    // Generic error message to avoid leaking DB info
    echo "<script>alert('Signup failed. Please try again.'); window.location='signup.php';</script>";
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign Up - LCCL Ticketing</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
  <div class="signup-container">
    <form method="POST">
      <h2>CREATE ACCOUNT</h2>
      <input type="text" name="username" placeholder="Enter Username" required>
      <input type="email" name="email" placeholder="Enter Email Address" required>
      <input type="password" name="password" placeholder="Enter Password" required>
      <button type="submit">SIGN UP</button>
      <p>Already have an account? <a href="index.php">Login here</a></p>
    </form>
  </div>
</body>
</html>
