<?php
session_start();
require 'config/db_connect.php';
if(!isset($_SESSION['user_id'])){ header("Location:index.php"); exit; }

// Only allow admins or super_admins to update payment status
if(!in_array($_SESSION['role'], ['admin','super_admin'])){ header("Location:index.php"); exit; }

$payment_id = intval($_GET['id'] ?? 0);
$status = ($_GET['status'] ?? '') === 'paid' ? 'paid' : 'pending';

if($payment_id > 0){
    $stmt = $conn->prepare("UPDATE payments SET status=? WHERE id=?");
    $stmt->bind_param("si", $status, $payment_id);
    $stmt->execute();
    echo "<script>alert('Payment status updated.'); window.location='manage_payments.php';</script>";
    exit;
}
header("Location: manage_payments.php");
?>
