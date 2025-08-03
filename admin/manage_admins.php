<?php
session_start();
require_once "../config/db.php";

// ✅ Check if admin is logged in
if (!isset($_SESSION["admin"])) {
    header("Location: admin_login.php");
    exit;
}

// ✅ Fetch logged-in admin role
$stmt = $conn->prepare("SELECT role FROM admin_users WHERE username = :username");
$stmt->execute([':username' => $_SESSION["admin"]]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$admin || $admin['role'] !== 'super') {
    die("❌ Access Denied: Only Super Admins can manage admins.");
}

// ✅ Add new admin
if (isset($_POST['add_admin'])) {
    $username = htmlspecialchars($_POST['username']);
    $password = hash('sha256', $_POST['password']);
    $role = $_POST['role'];

    $stmt = $conn->prepare("INSERT INTO admin_users (username, password, role) VALUES (:username, :password, :role)");
    $stmt->execute([':username' => $username, ':password' => $password, ':role' => $role]);
    header("Location: manage_admins.php?msg=added");
    exit;
}

// ✅ Delete admin (prevent deleting yourself)
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("SELECT username FROM admin_users WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $toDelete = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($toDelete && $toDelete['username'] !== $_SESSION["admin"]) {
        $stmt = $conn->prepare("DELETE FROM admin_users WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }
    header("Location: manage_admins.php?msg=deleted");
    exit;
}

// ✅ Update admin role
if (isset($_POST['update_role'])) {
    $id = intval($_POST['id']);
    $newRole = $_POST['role'];

    $stmt = $conn->prepare("UPDATE admin_users SET role = :role WHERE id = :id");
    $stmt->execute([':role' => $newRole, ':id' => $id]);

    header("Location: manage_admins.php?msg=role_updated");
    exit;
}

// ✅ Fetch all admins
$admins = $conn->query("SELECT * FROM admin_users ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Admins</title>
    <style>
        body { font-family: Arial; background:#f4f4f4; padding:20px; }
        table { width: 100%; border-collapse: collapse; background: #fff; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #333; color: #fff; }
        form { display: inline; }
        select, input { padding: 5px; }
        button { padding: 5px 10px; background: #333; color: #fff; border: none; cursor: pointer; }
        a.btn { background: red; color: white; padding: 5px 10px; text-decoration: none; border-radius: 4px; }
    </style>
</head>
<body>
    <h1>Manage Admins</h1>
    <a href="admin_dashboard.php">⬅ Back to Dashboard</a>

    <h2>Add New Admin</h2>
    <form method="POST">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <select name="role" required>
            <option value="normal">Normal Admin</option>
            <option value="super">Super Admin</option>
        </select>
        <button type="submit" name="add_admin">Add Admin</button>
    </form>

    <h2>Existing Admins</h2>
    <table>
        <tr>
            <th>ID</th><th>Username</th><th>Role</th><th>Actions</th>
        </tr>
        <?php foreach ($admins as $a): ?>
        <tr>
            <td><?= $a['id'] ?></td>
            <td><?= htmlspecialchars($a['username']) ?></td>
            <td>
                <?php if ($a['username'] !== $_SESSION["admin"]): ?>
                    <form method="POST">
                        <input type="hidden" name="id" value="<?= $a['id'] ?>">
                        <select name="role">
                            <option value="normal" <?= $a['role']=='normal'?'selected':'' ?>>Normal</option>
                            <option value="super" <?= $a['role']=='super'?'selected':'' ?>>Super</option>
                        </select>
                        <button type="submit" name="update_role">Update</button>
                    </form>
                <?php else: ?>
                    <?= htmlspecialchars($a['role']) ?> (You)
                <?php endif; ?>
            </td>
            <td>
                <?php if ($a['username'] !== $_SESSION["admin"]): ?>
                    <a class="btn" href="?delete=<?= $a['id'] ?>" onclick="return confirm('Delete this admin?')">Delete</a>
                <?php else: ?>
                    <strong>✅ You</strong>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    <li><a href="view_logs.php">View Activity Logs</a></li>

</body>
</html>
