<?php
$ip_file = "user_ip.txt";
$ips = file_exists($ip_file) ? json_decode(file_get_contents($ip_file), true) : array();
echo json_encode($ips);
?>