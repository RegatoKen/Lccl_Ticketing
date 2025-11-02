<?php
session_start();
require 'config/db_connect.php';
if(!isset($_SESSION['user_id'])){ header("Location:index.php"); exit; }

if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['event_id'])){
    $event_id=intval($_POST['event_id']);
    $user_id=$_SESSION['user_id'];

    // check if already in cart
    $stmt=$conn->prepare("SELECT id FROM cart WHERE user_id=? AND event_id=?");
    $stmt->bind_param("ii",$user_id,$event_id);
    $stmt->execute();
    $res=$stmt->get_result();
    if($res->num_rows>0){
        echo "<script>alert('Event already in cart'); window.history.back();</script>";
    } else {
        $quantity = min(10, max(1, intval($_POST['quantity'] ?? 1))); // Ensure quantity is between 1 and 10
        $ticket_type = $_POST['ticket_type'] ?? 'Regular';
        $stmt=$conn->prepare("INSERT INTO cart(user_id,event_id,quantity,ticket_type,added_at) VALUES(?,?,?,?,NOW())");
        $stmt->bind_param("iiis",$user_id,$event_id,$quantity,$ticket_type);
        $stmt->execute();
        echo "<script>alert('Added to cart'); window.location='cart.php';</script>";
    }
}
?>
