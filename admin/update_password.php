<?php
require_once "../config/db.php";

$username = "admin"; // Change if needed
$new_password = "Admin@123"; // Set new secure password

$hashed = password_hash($new_password, PASSWORD_DEFAULT);
$stmt = $conn->prepare("UPDATE admin_users SET password_hash = :hash WHERE username = :username");
$stmt->execute([':hash' => $hashed, ':username' => $username]);

echo "✅ Password updated with secure hash!";
