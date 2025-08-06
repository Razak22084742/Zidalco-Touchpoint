<?php
header('Content-Type: application/json');
require_once 'config/db.php';

try {
    $stmt = $pdo->query("SELECT name, message, created_at FROM feedback ORDER BY created_at DESC");
    $feedback = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($feedback);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch feedback']);
}
?>
