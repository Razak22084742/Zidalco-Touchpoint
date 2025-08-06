<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $message = $_POST['message'] ?? '';

    try {
        $stmt = $pdo->prepare("INSERT INTO feedback (name, email, message) VALUES (?, ?, ?)");
        $stmt->execute([$name, $email, $message]);
        echo "success";
    } catch (PDOException $e) {
        http_response_code(500);
        echo "error: " . $e->getMessage(); // Detailed for debugging
    }
}
?>

