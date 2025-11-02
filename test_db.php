<?php
require 'config/db_connect.php';
header('Content-Type: text/plain');
if ($conn->connect_error) {
    echo 'DB Connection failed: ' . $conn->connect_error . PHP_EOL;
    exit;
}
// Show database name and first few tables
$res = $conn->query("SELECT DATABASE() AS dbname");
$db = $res ? $res->fetch_assoc()['dbname'] : '(unknown)';
echo "Connected to database: $db" . PHP_EOL;
$tables = $conn->query("SHOW TABLES");
if ($tables && $tables->num_rows > 0) {
    echo "Tables in $db:" . PHP_EOL;
    $count = 0;
    while ($r = $tables->fetch_array()) {
        echo " - " . $r[0] . PHP_EOL;
        if (++$count >= 10) break;
    }
} else {
    echo "No tables found or query failed." . PHP_EOL;
}
?>