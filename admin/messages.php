<?php
session_start();
require_once "../config/db.php";

if (!isset($_SESSION["admin"])) {
    header("Location: admin_login.php");
    exit;
}

// ✅ Mark as Read/Unread
if (isset($_GET['toggle'])) {
    $id = intval($_GET['toggle']);
    $stmt = $conn->prepare("UPDATE contact_messages 
                            SET status = IF(status='unread','read','unread') 
                            WHERE id = :id");
    $stmt->execute([':id' => $id]);
    header("Location: message.php");
    exit;
}

// ✅ Delete message
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM contact_messages WHERE id = :id");
    $stmt->execute([':id' => $id]);
    header("Location: message.php");
    exit;
}
if (isset($_POST['add_service'])) {
    require_once "../config/csrf.php";
    if (!validateCSRFToken($_POST['csrf_token'])) {
        die("❌ CSRF validation failed.");
    }
    // continue adding service...
}


// ✅ Fetch all messages
$stmt = $conn->query("SELECT * FROM contact_messages ORDER BY created_at DESC");
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Contact Messages</title>
    <style>
        body { font-family: Arial; padding: 20px; background:#f9f9f9; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #ccc; text-align: left; }
        th { background: #333; color: white; }
        a.delete { color: red; text-decoration: none; }
        a.toggle { color: blue; text-decoration: none; }
    </style>
</head>
<body>
    <h1>Contact Messages</h1>
    <a href="admin_dashboard.php">⬅ Back to Dashboard</a>
    <table>
        <tr>
            <th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Message</th><th>Status</th><th>Date</th><th>Action</th>
        </tr>
        <?php foreach ($messages as $msg): ?>
        <tr>
            <td><?= $msg['id'] ?></td>
            <td><?= htmlspecialchars($msg['name']) ?></td>
            <td><?= htmlspecialchars($msg['email']) ?></td>
            <td><?= htmlspecialchars($msg['phone']) ?></td>
            <td><?= nl2br(htmlspecialchars($msg['message'])) ?></td>
            <td><?= $msg['status'] ?></td>
            <td><?= $msg['created_at'] ?></td>
            <td>
                <a class="toggle" href="?toggle=<?= $msg['id'] ?>">
                    Mark as <?= $msg['status']=='unread'?'Read':'Unread' ?>
                </a> | 
                <a class="delete" href="?delete=<?= $msg['id'] ?>" onclick="return confirm('Delete this message?')">Delete</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    <li><a href="view_logs.php">View Activity Logs</a></li>
<?php require_once "../config/csrf.php"; ?>
<form method="POST">
    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken(); ?>">
    <input type="text" name="title" placeholder="Service Title" required>
    <textarea name="description" placeholder="Service Description" rows="3" required></textarea>
    <button type="submit" name="add_service">Add Service</button>
</form>
<div class="table-container">
    <table>
        <tr>
            <th>ID</th><th>Name</th><th>Email</th><th>Phone</th>
            <th>Message</th><th>Status</th><th>Date</th><th>Action</th>
        </tr>
        <?php foreach ($messages as $msg): ?>
        <tr>
            <td><?= $msg['id'] ?></td>
            <td><?= htmlspecialchars($msg['name']) ?></td>
            <td><?= htmlspecialchars($msg['email']) ?></td>
            <td><?= htmlspecialchars($msg['phone']) ?></td>
            <td><?= nl2br(htmlspecialchars($msg['message'])) ?></td>
            <td><?= $msg['status'] ?></td>
            <td><?= $msg['created_at'] ?></td>
            <td><a class="delete" href="?delete=<?= $msg['id'] ?>" onclick="return confirm('Delete this message?')">Delete</a></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

</body>
</html>

