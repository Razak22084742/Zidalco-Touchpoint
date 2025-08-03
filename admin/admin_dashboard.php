<?php
session_start();
// 🔐 Session Timeout Settings
$timeout_duration = 900; // 15 minutes
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: admin_login.php?timeout=1");
    exit;
}
$_SESSION['LAST_ACTIVITY'] = time();

if (!isset($_SESSION["admin"])) {
    header("Location: admin_login.php");
    exit;
}

require_once "../config/db.php";

// ✅ Count unread messages
$msgCountStmt = $conn->query("SELECT COUNT(*) FROM contact_messages WHERE status='unread'");
$unreadMessages = $msgCountStmt->fetchColumn();

// ✅ Count unread feedback
$fbCountStmt = $conn->query("SELECT COUNT(*) FROM client_feedback WHERE status='unread'");
$unreadFeedback = $fbCountStmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        body { font-family: Arial; background:#f9f9f9; padding:20px; margin:0;}
        header { display:flex; justify-content:space-between; align-items:center; background:#333; color:#fff; padding:10px 15px;}
        .logout { color:#fff; text-decoration:none; background:#e74c3c; padding:6px 12px; border-radius:5px;}
        ul { list-style:none; padding:0; }
        li { margin: 10px 0; }
        a { text-decoration:none; color:#333; background:#eaeaea; padding:8px 12px; border-radius:5px; display:inline-block;}
        a:hover { background:#ccc; }
        .badge {
            background:red; color:white; font-size:12px; padding:3px 7px;
            border-radius:50%; margin-left:5px;
        }
    </style>
</head>
<body>
    <header>
        <h1>Welcome, <?= htmlspecialchars($_SESSION["admin"]); ?>!</h1>
        <a class="logout" href="logout.php">Logout</a>
    </header>

    <h2>Manage Content</h2>
    <ul>
        <li><a href="service.php">Manage Services</a></li>
        <li><a href="portfolio.php">Manage Portfolio</a></li>
        <li><a href="message.php">View Contact Messages 
            <?php if($unreadMessages > 0): ?><span class="badge"><?= $unreadMessages ?></span><?php endif; ?>
        </a></li>
        <li><a href="feedback.php">View Client Feedback
            <?php if($unreadFeedback > 0): ?><span class="badge"><?= $unreadFeedback ?></span><?php endif; ?>
        </a></li>
        <li><a href="change_password.php">Change Password</a></li>
        <li><a href="manage_admins.php">Manage Admins</a></li>
        <li><a href="view_logs.php">View Activity Logs</a></li>
        <li><a href="restore_db.php">Restore Database</a></li> <!-- ✅ Separate page -->
    </ul>
</body>
</html>
