<?php
// Run this in your browser: http://localhost/lccl_ticketing/gen_hash.php
// It will print password hashes for admin and superadmin accounts.
$admin_pass = 'AdminPass2025!';
$super_pass = 'SuperAdminPass2025!';
echo "Admin hash: ".password_hash($admin_pass, PASSWORD_DEFAULT)."\n";
echo "SuperAdmin hash: ".password_hash($super_pass, PASSWORD_DEFAULT)."\n";
?>