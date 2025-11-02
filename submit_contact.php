<?php
session_start();
require 'config/db_connect.php';
if(!isset($_SESSION['user_id'])){ header('Location:index.php'); exit; }

// Ensure messages table exists
$conn->query("CREATE TABLE IF NOT EXISTS messages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  subject VARCHAR(255),
  message TEXT,
  target_roles VARCHAR(255) DEFAULT 'admin,super_admin',
  is_read TINYINT DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');
$user_id = intval($_SESSION['user_id']);
if($subject=='' || $message==''){
  $_SESSION['flash']='Please fill all fields.';
  header('Location: contact.php'); exit;
}
$stmt = $conn->prepare("INSERT INTO messages(user_id,subject,message,target_roles) VALUES(?,?,?,?)");
$roles = 'admin,super_admin';
$stmt->bind_param('isss',$user_id,$subject,$message,$roles);
$stmt->execute();

$_SESSION['flash']='Message sent. Thank you!';
header('Location: contact.php');
