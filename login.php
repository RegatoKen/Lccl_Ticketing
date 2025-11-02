<?php
session_start();
require 'config/db_connect.php';
$message = '';

if($_SERVER['REQUEST_METHOD']=='POST'){
    if(isset($_POST['login'])){
        $username=trim($_POST['username'] ?? '');
        $password=trim($_POST['password'] ?? '');

        if ($username === '' || $password === '') {
            $message = "Please provide username and password.";
        } else {
            $stmt=$conn->prepare("SELECT * FROM users WHERE username=? LIMIT 1");
            $stmt->bind_param("s",$username);
            $stmt->execute();
            $res=$stmt->get_result();
            if($res && $res->num_rows==1){
                $user=$res->fetch_assoc();
                if(password_verify($password,$user['password'])){
                    // Set session
                    $_SESSION['user_id']=$user['id'];
                    $_SESSION['username']=$user['username'];
                    $_SESSION['role']=$user['role'];
                    $_SESSION['profile_image']=$user['profile_image']??'assets/logo.png';

                    // Redirect based on role
                    if ($user['role'] === 'super_admin') {
                        header("Location: super_admin.php"); exit;
                    } elseif ($user['role'] === 'admin') {
                        header("Location: admin_dashboard.php"); exit;
                    } else {
                        header("Location: home.php"); exit;
                    }
                } else { $message="Incorrect password"; }
            } else { $message="User not found"; }
        }
    } elseif(isset($_POST['signup'])){
        $username=trim($_POST['username']);
        $email=trim($_POST['email']);
        $password=trim($_POST['password']);
        if(strlen($username)<3||strlen($password)<6){ $message="Username must be 3+ chars and password 6+ chars"; }
        else{
            $hash=password_hash($password,PASSWORD_DEFAULT);
            $stmt=$conn->prepare("INSERT INTO users (username,email,password,role,created_at) VALUES (?,?,?,?,NOW())");
            $role='user';
            $stmt->bind_param("ssss",$username,$email,$hash,$role);
            if($stmt->execute()){ $message="Signup successful! You can login."; }
            else{ $message="Signup failed. Username or Email may exist."; }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>LCCL Ticketing Login</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<main class="login-container">
<div class="card" style="max-width:450px;margin:auto;margin-top:80px;">
<img src="assets/logo.png" style="display:block;margin:auto;width:80px;margin-bottom:20px;">
<h2 style="text-align:center;margin-bottom:15px;">Welcome to LCCL Ticketing</h2>
<p style="text-align:center;color:#555;margin-bottom:20px;">Login or create your account to book tickets for your favorite events.</p>

<?php if($message) echo "<div style='color:red;margin-bottom:15px;text-align:center;'>".htmlspecialchars($message)."</div>"; ?>

<form method="POST">
<input type="text" name="username" placeholder="Username" required>
<input type="email" name="email" placeholder="Email (Signup only)">
<input type="password" name="password" placeholder="Password" required>
<div style="display:flex;gap:10px;justify-content:center;">
<button type="submit" name="login" class="btn">Login</button>
<button type="submit" name="signup" class="btn">Sign Up</button>
</div>
</form>
</div>
</main>
</body>
</html>
