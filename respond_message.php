<?php
session_start();
require 'config/db_connect.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin','super_admin'])) {
    header('Location: index.php');
    exit();
}

$message_id = intval($_POST['message_id'] ?? 0);
$solution = trim($_POST['solution'] ?? '');

if ($message_id <= 0 || $solution === '') {
    $_SESSION['flash'] = 'Please provide a solution before submitting.';
    header('Location: admin_messages.php');
    exit();
}

$stmt = $conn->prepare("UPDATE messages SET solution = ?, responded_by = ?, responded_at = NOW(), is_read = 1, status = 'answered' WHERE id = ?");
if ($stmt) {
    $responder = intval($_SESSION['user_id']);
    $stmt->bind_param('sii', $solution, $responder, $message_id);
    $stmt->execute();
    $stmt->close();
}

$_SESSION['flash'] = 'Solution saved.';
header('Location: admin_messages.php');
exit();
?>