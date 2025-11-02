<?php
session_start();
require 'config/db_connect.php';
if(!isset($_SESSION['user_id']) || !in_array($_SESSION['role'],['admin','super_admin'])){ header('Location:index.php'); exit; }
$id=intval($_GET['id']??0);
if($id){
  $stmt=$conn->prepare("DELETE FROM messages WHERE id=?");
  $stmt->bind_param('i',$id);
  $stmt->execute();
}
header('Location: admin_messages.php');
