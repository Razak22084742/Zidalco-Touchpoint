<?php
session_start();
require_once "../config/db.php";

if (!isset($_SESSION["admin"])) {
    exit("Unauthorized");
}

$q = isset($_GET['q']) ? '%' . $_GET['q'] . '%' : '%';

$stmt = $conn->prepare("SELECT * FROM contact_messages 
                        WHERE name LIKE :q OR email LIKE :q OR phone LIKE :q OR message LIKE :q 
                        ORDER BY created_at DESC");
$stmt->execute([':q' => $q]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($messages) === 0) {
    echo "<p>No messages found.</p>";
    exit;
}

echo "<table>
<tr>
    <th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Message</th><th>Date</th><th>Action</th>
</tr>";

foreach ($messages as $msg) {
    echo "<tr>
        <td>{$msg['id']}</td>
        <td>" . htmlspecialchars($msg['name']) . "</td>
        <td>" . htmlspecialchars($msg['email']) . "</td>
        <td>" . htmlspecialchars($msg['phone']) . "</td>
        <td>" . nl2br(htmlspecialchars($msg['message'])) . "</td>
        <td>{$msg['created_at']}</td>
        <td><a class='delete' href='?delete={$msg['id']}' onclick='return confirm(\"Delete this message?\")'>Delete</a></td>
    </tr>";
}

echo "</table>";
