<?php
// log_action.php
require_once "../config/db.php";

function log_action($admin_username, $action) {
    global $conn;
    $ip = $_SERVER['REMOTE_ADDR'];
    $agent = $_SERVER['HTTP_USER_AGENT'];

    $stmt = $conn->prepare("INSERT INTO logs (admin_username, action, ip_address, user_agent) 
                            VALUES (:username, :action, :ip, :agent)");
    $stmt->execute([
        ':username' => $admin_username,
        ':action' => $action,
        ':ip' => $ip,
        ':agent' => $agent
    ]);
}
?>
