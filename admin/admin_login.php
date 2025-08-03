<?php
session_start();
require_once "../config/db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];

    $sql = "SELECT * FROM admin_users WHERE username = :username";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && hash('sha256', $password) === $user['password_hash']) {
        $_SESSION["admin"] = $user["username"];
        header("Location: admin_dashboard.php");
        exit;
    } else {
        $error = "Invalid username or password";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Login</title>
    <style>
        body { font-family: Arial; background: #f4f4f4; display:flex; justify-content:center; align-items:center; height:100vh;}
        form { background:#fff; padding:20px; border-radius:8px; box-shadow:0 2px 6px rgba(0,0,0,0.2);}
        input { display:block; width:100%; margin:10px 0; padding:10px;}
        button { background:#333; color:#fff; border:none; padding:10px; width:100%; cursor:pointer;}
        .error { color:red; }
    </style>
</head>
<body>
    <form method="POST">
        <h2>Admin Login</h2>
        <?php if(!empty($error)): ?><p class="error"><?= $error ?></p><?php endif; ?>
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>
    <?php if (isset($_GET['timeout'])): ?>
    <p style="color:red;">⚠️ You were logged out due to inactivity. Please login again.</p>
<?php endif; ?>

</body>
</html>
