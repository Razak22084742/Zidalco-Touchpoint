<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once "config/db.php";

    $name = $_POST["name"];
    $email = $_POST["email"];
    $message = $_POST["message"];

    $stmt = $conn->prepare("INSERT INTO feedback (name, email, message) VALUES (?, ?, ?)");
    $stmt->execute([$name, $email, $message]);

    echo "Thank you for your feedback!";
}
?>
