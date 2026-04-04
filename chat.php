<?php
date_default_timezone_set('Asia/Shanghai');
session_start();
$online_file = "online.txt";
$time_file = "online_time.txt";
$msg_file = "msg.txt";
$last_active_file = "last_active.txt";
$timeout = 30 * 60;

if (!isset($_SESSION['user'])) {
    exit;
}
$user = $_SESSION['user'];
$now = time();

if (isset($_GET['act']) && $_GET['act'] == 'exit') {
    $time = date("H:i:s");
    $exit_msg = "<div class='msg' style='color:red'>[$time] 系统：$user 退出了聊天室</div>";
    file_put_contents($msg_file, $exit_msg."\n", FILE_APPEND);
    
    if(file_exists($online_file)){
        $lines = file($online_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $new_lines = array();
        foreach ($lines as $u) {
            if ($u != $user) {
                $new_lines[] = $u;
            }
        }
        file_put_contents($online_file, implode("\n", $new_lines));
        
        $times = file_exists($time_file) ? json_decode(file_get_contents($time_file), true) : array();
        unset($times[$user]);
        file_put_contents($time_file, json_encode($times));
    }
    exit;
}

if ($_POST) {
    $msg = trim($_POST['msg']);
    $time = date("H:i:s");
    $isWhisper = isset($_POST['whisper']) ? 1 : 0;
    $toUser = isset($_POST['to']) ? trim($_POST['to']) : '';

    if ($isWhisper && $toUser && $toUser != $user) {
        $line = "<div class='msg' style='color:#93C; font-weight:bold;'>[$time] [悄悄话] $user --> $toUser : $msg</div>";
        file_put_contents($msg_file, $line."\n", FILE_APPEND);
    } else {
        $line = "<div class='msg'>[$time] <span style='color:blue;'>$user</span>：$msg</div>";
        file_put_contents($msg_file, $line."\n", FILE_APPEND);
    }

    $lines = file_exists($online_file) ? file($online_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : array();
    if (!in_array($user, $lines)) {
        $lines[] = $user;
        file_put_contents($online_file, implode("\n", $lines));
    }
    $times = file_exists($time_file) ? json_decode(file_get_contents($time_file), true) : array();
    $times[$user] = $now;
    file_put_contents($time_file, json_encode($times));
    file_put_contents($last_active_file, $now);
    exit;
}

$output = '';
if (file_exists($msg_file)) {
    $last_active = file_exists($last_active_file) ? intval(file_get_contents($last_active_file)) : 0;
    if ($now - $last_active > $timeout && $last_active != 0) {
        file_put_contents($msg_file, "");
        file_put_contents($last_active_file, 0);
    }

    $lines = file($msg_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '[悄悄话]') !== false) {
            if (strpos($line, " $user -->") !== false || strpos($line, "--> $user :") !== false) {
                $output .= $line . "\n";
            }
        } else {
            $output .= $line . "\n";
        }
    }
}

echo $output;
?>