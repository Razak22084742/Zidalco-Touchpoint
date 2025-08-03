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
    $stmt = $conn->prepare("UPDATE client_feedback 
                            SET status = IF(status='unread','read','unread') 
                            WHERE id = :id");
    $stmt->execute([':id' => $id]);
    header("Location: feedback.php");
    exit;
}

// ✅ Delete feedback
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM client_feedback WHERE id = :id");
    $stmt->execute([':id' => $id]);
    header("Location: feedback.php");
    exit;
}

// ✅ Fetch all feedback
$stmt = $conn->query("SELECT * FROM client_feedback ORDER BY submitted_at DESC");
$feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Client Feedback</title>
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
    <h1>Client Feedback</h1>
    <a href="admin_dashboard.php">⬅ Back to Dashboard</a>
    <table>
        <tr>
            <th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Message</th><th>Status</th><th>Email Sent</th><th>Date</th><th>Action</th>
        </tr>
        <?php foreach ($feedbacks as $fb): ?>
        <tr>
            <td><?= $fb['id'] ?></td>
            <td><?= htmlspecialchars($fb['name']) ?></td>
            <td><?= htmlspecialchars($fb['email']) ?></td>
            <td><?= htmlspecialchars($fb['phone']) ?></td>
            <td><?= nl2br(htmlspecialchars($fb['message'])) ?></td>
            <td><?= $fb['status'] ?></td>
            <td><?= ($fb['email_sent'] === 'yes') ? '✅' : '❌'; ?></td>
            <td><?= $fb['submitted_at'] ?></td>
            <td>
                <a class="toggle" href="?toggle=<?= $fb['id'] ?>">
                    Mark as <?= $fb['status']=='unread'?'Read':'Unread' ?>
                </a> | 
                <a class="delete" href="?delete=<?= $fb['id'] ?>" onclick="return confirm('Delete this feedback?')">Delete</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
