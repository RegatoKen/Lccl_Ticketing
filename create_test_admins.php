<?php
// One-time helper to create admin and super_admin users.
// USAGE (run in browser once): http://localhost/lccl_ticketing/create_test_admins.php?token=YOUR_SECRET
// After successful creation delete this file for security.


$SECRET = 'make-me-admin-2025'; // CHANGE this to a long random string before running in a public environment

if (!isset($_GET['token']) || $_GET['token'] !== $SECRET) {
    http_response_code(403);
    echo "Forbidden. Provide the correct token as ?token=...\n";
    exit;
}

require 'config/db_connect.php';

// Optional parameters to create custom accounts:
// ?u1=admin&p1=AdminPass123!&r1=admin&u2=superadmin&p2=SuperAdminPass123!&r2=super_admin
$u1 = isset($_GET['u1']) ? trim($_GET['u1']) : 'admin';
$p1 = isset($_GET['p1']) ? $_GET['p1'] : 'AdminPass123!';
$r1 = isset($_GET['r1']) ? $_GET['r1'] : 'admin';

$u2 = isset($_GET['u2']) ? trim($_GET['u2']) : 'superadmin';
$p2 = isset($_GET['p2']) ? $_GET['p2'] : 'SuperAdminPass123!';
$r2 = isset($_GET['r2']) ? $_GET['r2'] : 'super_admin';

$users = [
    ['username' => $u1, 'password' => $p1, 'role' => $r1],
    ['username' => $u2, 'password' => $p2, 'role' => $r2],
];

$out = [];
foreach ($users as $u) {
    $username = $u['username'];
    $role = $u['role'];
    $plain = $u['password'];

    // Check existing
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $res->num_rows > 0) {
        $out[] = "User '{$username}' already exists, skipping.";
        continue;
    }

    $hash = password_hash($plain, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (username, password, role, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param('sss', $username, $hash, $role);
    if ($stmt->execute()) {
        $out[] = "Created user '{$username}' with role '{$role}'. Password: {$plain} (change after first login).";
    } else {
        $out[] = "Failed to create '{$username}': " . $conn->error;
    }
}

header('Content-Type: text/plain');
foreach ($out as $line) echo $line . "\n";

?>