<?php
$password = "Admin123!"; // choose your admin password
$hash = password_hash($password, PASSWORD_DEFAULT);
echo $hash;
?>
