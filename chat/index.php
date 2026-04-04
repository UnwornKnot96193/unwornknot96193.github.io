<?php
date_default_timezone_set('Asia/Shanghai');
session_start();
header("Content-Type: text/html; charset=utf-8");

$online_file = "online.txt";
$time_file = "online_time.txt";
$ip_file = "user_ip.txt";

if (!isset($_POST['username'])) {
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>登录聊天室</title>
<style>
body{background:#E0E0E0; font-family:宋体; font-size:12px; text-align:center; margin-top:100px;}
input{border:1px solid #000; padding:2px; width:140px;}
button{border:1px solid #000; background:#C0C0C0; padding:2px 10px;}
</style>
<!--[if lt IE 9]>
<script src="https://cdn.bootcdn.net/ajax/libs/json2/20160511/json2.min.js"></script>
<![endif]-->
</head>
<body>
<h3>请输入昵称</h3>
<form method="post" action="">
<input type="text" name="username" maxlength="10"><br><br>
<button type="submit">进入聊天室</button>
</form>
</body>
</html>
<?php
exit;
}

$user = trim($_POST['username']);
if (empty($user)) {
    echo '<script>alert("请输入用户名");location.href="index.php";</script>';
    exit;
}

$_SESSION['user'] = $user;
$ip = $_SERVER['REMOTE_ADDR'];

if ($ip === '::1') {
    $ip = '[服务器主机]';
}

$userIp = file_exists($ip_file) ? json_decode(file_get_contents($ip_file), true) : array();
$userIp[$user] = $ip;
file_put_contents($ip_file, json_encode($userIp));

$lines = file_exists($online_file) ? file($online_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : array();
if (!in_array($user, $lines)) {
    $lines[] = $user;
    file_put_contents($online_file, implode("\n", $lines));
}

$user_times = file_exists($time_file) ? json_decode(file_get_contents($time_file), true) : array();
$user_times[$user] = time();
file_put_contents($time_file, json_encode($user_times));

$file = "msg.txt";
$time = date("H:i:s");
$join_msg = "<div class='msg' style='color:green'>[$time] 系统：$user 加入聊天室 | IP：$ip</div>";
file_put_contents($file, $join_msg."\n", FILE_APPEND);
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>MNS聊天室</title>
<style>
body{background:#E0E0E0; font-family:宋体; font-size:12px;}
.main{width:900px; margin:0 auto; overflow:hidden;}
.chat-area{float:left; width:700px;}
#chat{width:690px; height:500px; background:#FFF; border:1px solid #000; padding:5px; overflow-y:scroll;}
.msg{margin:5px 0; word-wrap:break-word; word-break:break-all; white-space:normal;}
.nick{color:blue;}
#box{text-align:center; margin-top:10px;}
input{border:1px solid #000; padding:2px; width:400px;}
button{border:1px solid #000; background:#C0C0C0; padding:2px 10px;}

.online-area{
    float:right;
    width:180px;
    height:500px;
    background:#FFF;
    border:1px solid #000;
    padding:5px;
}
.online-title{font-weight:bold; color:blue; margin-bottom:5px;}
.online-count{color:red; font-size:14px; margin:5px 0;}

#userList {
    width:170px;
    height:420px;
    border:1px solid #999;
    background:#fff;
    padding:3px;
    overflow-y:scroll;
    font-size:12px;
    line-height:18px;
}
/* 去掉下划线 */
#userList a {
    color:black;
    text-decoration:none;
    cursor:pointer;
}
#userList a:hover {
    color:blue;
}

.whisper-box {
    margin:8px 0;
    font-size:12px;
    line-height:12px;
    text-align:right;
    padding-right:5px;
}
.whisper-box label {
    display:inline-block;
    vertical-align:middle;
}
.whisper-box input[type="checkbox"] {
    margin:0 0 0 2px;
    padding:0;
    width:auto;
    border:none;
    vertical-align:middle;
}
#whisperSelect {
    margin-left:4px;
    padding:1px;
    border:1px solid #000;
    vertical-align:middle;
    font-size:12px;
}
</style>
<!--[if lt IE 9]>
<script src="https://cdn.bootcdn.net/ajax/libs/json2/20160511/json2.min.js"></script>
<![endif]-->
</head>
<body>
<div style="text-align:center; font-size:14px; font-weight:bold; margin-bottom:5px;">
欢迎：<?php echo $user; ?>（刷新可重新改名）
</div>

<div class="main">
    <div class="chat-area">
        <div id="chat"></div>

        <div class="whisper-box">
            <label>悄悄话  <input type="checkbox" id="whisperCheck"></label>
            <select id="whisperSelect" disabled>
                <option value="">选择接收用户</option>
            </select>
        </div>

        <div id="box">
            <input type="text" id="msg">
            <button id="sendBtn">发送</button>
        </div>
    </div>

    <div class="online-area">
        <div class="online-title">在线状态</div>
        <div class="online-count">当前在线：<span id="onlineNum">0</span> 人</div>
        <div class="online-title">在线用户</div>
        <div id="userList"></div>
    </div>
</div>

<script>
var currentUser = "<?php echo $user; ?>";

function createXHR() {
    if (window.XMLHttpRequest) {
        return new XMLHttpRequest();
    } else if (window.ActiveXObject) {
        try { return new ActiveXObject("Msxml2.XMLHTTP"); } catch (e) {
            try { return new ActiveXObject("Microsoft.XMLHTTP"); } catch (e) {}
        }
    }
    return null;
}

function send() {
    var input = document.getElementById('msg');
    var m = input.value.replace(/(^\s+)|(\s+$)/g, '');
    if (m === '') {
        alert('请输入消息');
        return;
    }

    var isWhisper = document.getElementById('whisperCheck').checked;
    var toUser = document.getElementById('whisperSelect').value;

    if (isWhisper && (!toUser || toUser === currentUser)) {
        alert('请选择正确的接收用户');
        return;
    }

    var xhr = createXHR();
    if (!xhr) return;
    xhr.open('POST', 'chat.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            input.value = '';
        }
    };

    var data = 'msg=' + encodeURIComponent(m);
    if (isWhisper) {
        data += '&whisper=1&to=' + encodeURIComponent(toUser);
    }
    xhr.send(data);
}

document.getElementById('sendBtn').onclick = send;

document.getElementById('msg').onkeydown = function(e) {
    e = e || window.event;
    var key = e.which || e.keyCode;
    if (key === 13) {
        send();
        if (e.preventDefault) {
            e.preventDefault();
        } else {
            e.returnValue = false;
        }
    }
};

document.getElementById('whisperCheck').onchange = function() {
    document.getElementById('whisperSelect').disabled = !this.checked;
};

function loadChat() {
    var xhr = createXHR();
    if (!xhr) return;
    xhr.open('GET', 'chat.php', true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            var box = document.getElementById('chat');
            var oldBottom = box.scrollTop + 500 >= box.scrollHeight - 20;
            box.innerHTML = xhr.responseText;
            if (oldBottom) {
                box.scrollTop = box.scrollHeight;
            }
        }
    };
    xhr.send();
}

function clickToWhisper(username) {
    document.getElementById('whisperCheck').checked = true;
    document.getElementById('whisperSelect').disabled = false;
    
    var sel = document.getElementById('whisperSelect');
    for (var i = 0; i < sel.options.length; i++) {
        if (sel.options[i].value === username) {
            sel.selectedIndex = i;
            break;
        }
    }
}

function loadOnline() {
    var sel = document.getElementById('whisperSelect');
    var selectedVal = sel.value;

    var xhr = createXHR();
    if (!xhr) return;
    xhr.open('GET', 'online.php', true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            try {
                var data = JSON.parse(xhr.responseText);
                document.getElementById('onlineNum').innerText = data.count;

                var ipXhr = createXHR();
                ipXhr.open('GET', 'get_ip.php', true);
                ipXhr.onreadystatechange = function() {
                    if (ipXhr.readyState === 4 && ipXhr.status === 200) {
                        var ipData = JSON.parse(ipXhr.responseText);
                        var userListEl = document.getElementById('userList');
                        userListEl.innerHTML = '';

                        for (var i = 0; i < data.users.length; i++) {
                            var u = data.users[i];
                            var ip = ipData[u] || '未知IP';
                            var item = document.createElement('div');
                            item.style.whiteSpace = 'nowrap';
                            
                            if (u === currentUser) {
                                item.innerHTML = '[<span style="color:blue;font-weight:bold;">' + u + '</span>] ' + ip;
                            } else {
                                item.innerHTML = '<a href="javascript:clickToWhisper(\'' + u + '\')">[' + u + '] ' + ip + '</a>';
                            }
                            
                            userListEl.appendChild(item);
                        }
                    }
                };
                ipXhr.send();

                sel.innerHTML = '<option value="">选择接收用户</option>';
                for (var i = 0; i < data.users.length; i++) {
                    var u = data.users[i];
                    if (u === currentUser) continue;
                    var opt = document.createElement('option');
                    opt.value = u;
                    opt.innerText = u;
                    if (u === selectedVal) {
                        opt.selected = true;
                    }
                    sel.appendChild(opt);
                }
            } catch(e) {}
        }
    };
    xhr.send();
}

setInterval(loadChat, 1000);
setInterval(loadOnline, 3000);
loadChat();
loadOnline();

window.onbeforeunload = function() {
    var xhr = createXHR();
    if (xhr) {
        xhr.open('POST', 'chat.php?act=exit', true);
        xhr.send();
    }
};
</script>
</body>
</html>