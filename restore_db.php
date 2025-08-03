<?php
session_start();
if (!isset($_SESSION["admin"])) {
    header("Location: admin_login.php");
    exit;
}

require_once "../config/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['sql_file'])) {
    $sqlContent = file_get_contents($_FILES['sql_file']['tmp_name']);
    $conn->exec($sqlContent);
    $message = "✅ Database restored successfully!";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Restore Database</title>
</head>
<body>
    <h1>Restore Database</h1>
    <a href="admin_dashboard.php">⬅ Back to Dashboard</a>
    <?php if (!empty($message)) echo "<p>$message</p>"; ?>
    <form method="POST" enctype="multipart/form-data">
        <input type="file" name="sql_file" accept=".sql" required>
        <button type="submit">Restore</button>
    </form>
</body>
</html>
