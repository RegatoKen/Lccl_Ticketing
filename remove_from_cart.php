<?php
session_start();
require 'config/db_connect.php';
if(!isset($_SESSION['user_id'])){ header("Location:index.php"); exit; }

if(isset($_GET['id'])){
    $id=intval($_GET['id']);
    $stmt=$conn->prepare("DELETE FROM cart WHERE id=?");
    $stmt->bind_param("i",$id);
    $stmt->execute();
}
header("Location: cart.php");
?>
