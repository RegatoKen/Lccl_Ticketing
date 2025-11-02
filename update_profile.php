<?php
session_start();
require 'config/db_connect.php';
if(!isset($_SESSION['user_id'])) header('Location:index.php');
$user_id = $_SESSION['user_id'];

$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$current_password = $_POST['current_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';
$msg = '';

// Update username/email
if($username && $email) {
    $stmt = $conn->prepare("UPDATE users SET username=?, email=? WHERE id=?");
    $stmt->bind_param('ssi', $username, $email, $user_id);
    $stmt->execute();
    $_SESSION['username'] = $username;
    $_SESSION['email'] = $email;
    $msg = 'Profile updated.';
}

// Change password if requested
if($new_password && $confirm_password) {
    if($new_password !== $confirm_password) {
        $msg = 'New passwords do not match.';
    } elseif(strlen($new_password) < 6) {
        $msg = 'New password must be at least 6 characters.';
    } else {
        // Verify current password
        $stmt = $conn->prepare("SELECT password FROM users WHERE id=?");
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        if($row && password_verify($current_password, $row['password'])) {
            $hash = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
            $stmt->bind_param('si', $hash, $user_id);
            $stmt->execute();
            $msg = 'Password changed successfully.';
        } else {
            $msg = 'Current password is incorrect.';
        }
    }
}

$_SESSION['profile_msg'] = $msg;
header('Location: profile.php');
exit();
