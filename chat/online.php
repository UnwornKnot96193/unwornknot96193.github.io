<?php
date_default_timezone_set('Asia/Shanghai');
$online_file = "online.txt";
$timeout = 30 * 60;
$time_file = "online_time.txt";

$online = array();
if (file_exists($online_file)) {
    $online = file($online_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
}

$user_times = file_exists($time_file) ? json_decode(file_get_contents($time_file), true) : array();
$current_time = time();
$online_clean = array();

foreach ($online as $u) {
    $u = trim($u);
    if (isset($user_times[$u]) && ($current_time - $user_times[$u]) <= $timeout) {
        $online_clean[] = $u;
    }
}

file_put_contents($online_file, implode("\n", $online_clean));

echo json_encode(array(
    'count' => count($online_clean),
    'users' => $online_clean
));
?>