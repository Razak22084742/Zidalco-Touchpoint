<?php
require_once 'config/db.php';

try {
    $stmt = $pdo->query("SELECT name, message, created_at FROM feedback ORDER BY created_at DESC");
    $feedback = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($feedback);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
