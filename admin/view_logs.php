<?php
session_start();
require_once "../config/db.php";
if (!isset($_SESSION["admin"])) {
    header("Location: admin_login.php");
    exit;
}

$stmt = $conn->query("SELECT * FROM logs ORDER BY created_at DESC");
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Activity Logs</title>
    <style>
        body { font-family: Arial; background:#f4f4f4; padding:20px; }
        table { width: 100%; border-collapse: collapse; background: #fff; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background: #333; color: #fff; }
    </style>
</head>
<body>
    <h1>Activity Logs</h1>
    <a href="admin_dashboard.php">⬅ Back to Dashboard</a>
    <table>
        <tr>
            <th>ID</th><th>Admin</th><th>Action</th><th>IP</th><th>Browser</th><th>Date</th>
        </tr>
        <?php foreach ($logs as $log): ?>
        <tr>
            <td><?= $log['id'] ?></td>
            <td><?= htmlspecialchars($log['admin_username']) ?></td>
            <td><?= htmlspecialchars($log['action']) ?></td>
            <td><?= htmlspecialchars($log['ip_address']) ?></td>
            <td><?= htmlspecialchars($log['user_agent']) ?></td>
            <td><?= $log['created_at'] ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
