<?php
session_start();
require_once "../config/db.php";

if (!isset($_SESSION["admin"])) {
    exit("Unauthorized");
}

$q = isset($_GET['q']) ? '%' . $_GET['q'] . '%' : '%';

$stmt = $conn->prepare("SELECT * FROM client_feedback 
                        WHERE name LIKE :q OR email LIKE :q OR phone LIKE :q OR message LIKE :q 
                        ORDER BY submitted_at DESC");
$stmt->execute([':q' => $q]);
$feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($feedbacks) === 0) {
    echo "<p>No feedback found.</p>";
    exit;
}

echo "<table>
<tr>
    <th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Message</th><th>Date</th><th>Action</th>
</tr>";

foreach ($feedbacks as $fb) {
    echo "<tr>
        <td>{$fb['id']}</td>
        <td>" . htmlspecialchars($fb['name']) . "</td>
        <td>" . htmlspecialchars($fb['email']) . "</td>
        <td>" . htmlspecialchars($fb['phone']) . "</td>
        <td>" . nl2br(htmlspecialchars($fb['message'])) . "</td>
        <td>{$fb['submitted_at']}</td>
        <td><a class='delete' href='?delete={$fb['id']}' onclick='return confirm(\"Delete this feedback?\")'>Delete</a></td>
    </tr>";
}

echo "</table>";
