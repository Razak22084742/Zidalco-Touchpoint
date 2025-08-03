<?php
session_start();
require_once "../config/db.php";

if (!isset($_SESSION["admin"])) {
    header("Location: admin_login.php");
    exit;
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $current = hash("sha256", $_POST['current_password']);
    $new = hash("sha256", $_POST['new_password']);
    $confirm = hash("sha256", $_POST['confirm_password']);
    $username = $_SESSION["admin"];

    if ($new !== $confirm) {
        $message = "❌ New passwords do not match.";
    } else {
        // ✅ Verify current password
        $stmt = $conn->prepare("SELECT password FROM admin_users WHERE username = :username");
        $stmt->execute([':username' => $username]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row && $row['password'] === $current) {
            // ✅ Update password
            $update = $conn->prepare("UPDATE admin_users SET password = :password WHERE username = :username");
            $update->execute([':password' => $new, ':username' => $username]);
            $message = "✅ Password updated successfully.";
        } else {
            $message = "❌ Current password is incorrect.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Change Password</title>
    <style>
        body { font-family: Arial; padding: 20px; background:#f4f4f4; }
        form { background:#fff; padding:20px; border-radius:5px; max-width:400px; margin:auto; }
        input { width:100%; padding:8px; margin:8px 0; }
        button { padding:10px; background:#333; color:#fff; border:none; cursor:pointer; width:100%; }
        .msg { margin-bottom:10px; color:red; font-weight:bold; }
        a { display:inline-block; margin-top:10px; color:#333; text-decoration:none; }
    </style>
</head>
<body>
    <h1>Change Password</h1>
    <?php if ($message): ?>
        <p class="msg"><?= $message ?></p>
    <?php endif; ?>
    <form method="POST">
        <input type="password" name="current_password" placeholder="Current Password" required>
        <input type="password" name="new_password" placeholder="New Password" required>
        <input type="password" name="confirm_password" placeholder="Confirm New Password" required>
        <button type="submit">Update Password</button>
    </form>
    <a href="admin_dashboard.php">⬅ Back to Dashboard</a>
</body>
</html>
