<?php
require_once "config/db.php";

$stmt = $conn->query("SELECT * FROM portfolio ORDER BY created_at DESC");
$portfolio = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($portfolio);
