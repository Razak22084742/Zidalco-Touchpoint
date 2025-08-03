<?php
session_start();
// 🔐 Session Timeout Settings
$timeout_duration = 900; // 900 seconds = 15 minutes

if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: admin_login.php?timeout=1");
    exit;
}
$_SESSION['LAST_ACTIVITY'] = time();

if (!isset($_SESSION["admin"])) {
    header("Location: admin_login.php");
    exit;
}

require_once "../config/db.php";

// ✅ Create upload folder if not exists
$uploadDir = __DIR__ . "/../uploads/services/";
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// ✅ Handle Add Service
if (isset($_POST['add_service'])) {
    $title = htmlspecialchars($_POST['title']);
    $description = htmlspecialchars($_POST['description']);

    // ✅ Handle file upload
    $imagePath = "";
    if (!empty($_FILES['image']['name'])) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (in_array($_FILES['image']['type'], $allowedTypes)) {
            $filename = time() . "_" . basename($_FILES['image']['name']);
            $target = $uploadDir . $filename;
            move_uploaded_file($_FILES['image']['tmp_name'], $target);
            $imagePath = "uploads/services/" . $filename;
        }
    }

    $sql = "INSERT INTO services (title, description, image_url) VALUES (:title, :description, :image_url)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':title' => $title, ':description' => $description, ':image_url' => $imagePath]);
    header("Location: service.php?msg=added");
    exit;
}

// ✅ Handle Delete Service
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("SELECT image_url FROM services WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $img = $stmt->fetchColumn();

    if ($img && file_exists(__DIR__ . "/../" . $img)) {
        unlink(__DIR__ . "/../" . $img);
    }

    $sql = "DELETE FROM services WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':id' => $id]);
    header("Location: service.php?msg=deleted");
    exit;
}

// ✅ Handle Update Service
if (isset($_POST['update_service'])) {
    $id = intval($_POST['id']);
    $title = htmlspecialchars($_POST['title']);
    $description = htmlspecialchars($_POST['description']);

    $stmt = $conn->prepare("SELECT image_url FROM services WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $oldImage = $stmt->fetchColumn();

    if (!empty($_FILES['image']['name'])) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (in_array($_FILES['image']['type'], $allowedTypes)) {
            $filename = time() . "_" . basename($_FILES['image']['name']);
            $target = $uploadDir . $filename;
            move_uploaded_file($_FILES['image']['tmp_name'], $target);
            $newImagePath = "uploads/services/" . $filename;

            if ($oldImage && file_exists(__DIR__ . "/../" . $oldImage)) {
                unlink(__DIR__ . "/../" . $oldImage);
            }

            $sql = "UPDATE services SET title = :title, description = :description, image_url = :image_url WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':title' => $title,
                ':description' => $description,
                ':image_url' => $newImagePath,
                ':id' => $id
            ]);
        }
    } else {
        $sql = "UPDATE services SET title = :title, description = :description WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':title' => $title,
            ':description' => $description,
            ':id' => $id
        ]);
    }

    header("Location: service.php?msg=updated");
    exit;
    

}
if (isset($_POST['add_service'])) {
    require_once "../config/csrf.php";
    if (!validateCSRFToken($_POST['csrf_token'])) {
        die("❌ CSRF validation failed.");
    }
    // continue adding service...
}

// ✅ Fetch All Services
$stmt = $conn->query("SELECT * FROM services ORDER BY created_at DESC");
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Services</title>
    <style>
        body { font-family: Arial; background:#f4f4f4; padding:20px; }
        h2 { margin-top: 0; }
        table { width: 100%; border-collapse: collapse; background: #fff; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #333; color: #fff; }
        form { margin-bottom: 20px; background: #fff; padding: 15px; border-radius: 5px; }
        input, textarea { width: 100%; margin: 8px 0; padding: 8px; }
        button { padding: 8px 12px; background: #333; color: #fff; border: none; cursor: pointer; }
        a.btn { background: red; color: white; padding: 5px 10px; text-decoration: none; border-radius: 4px; }
        img { max-width: 100px; border-radius: 4px; }
    </style>
</head>
<body>
    <h1>Manage Services</h1>
    <a href="admin_dashboard.php">⬅ Back to Dashboard</a>

    <h2>Add New Service</h2>
    <form method="POST" enctype="multipart/form-data">
        <input type="text" name="title" placeholder="Service Title" required>
        <input type="file" name="image" accept="image/*">
        <textarea name="description" placeholder="Service Description" rows="3" required></textarea>
        <button type="submit" name="add_service">Add Service</button>
    </form>

    <h2>Existing Services</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Title</th>
            <th>Image</th>
            <th>Description</th>
            <th>Actions</th>
        </tr>
        <?php foreach($services as $service): ?>
            <tr>
                <td><?= $service['id']; ?></td>
                <td><?= htmlspecialchars($service['title']); ?></td>
                <td>
                    <?php if (!empty($service['image_url'])): ?>
                        <img src="../<?= htmlspecialchars($service['image_url']); ?>" alt="Service Image">
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($service['description']); ?></td>
                <td>
                    <form method="POST" enctype="multipart/form-data" style="display:inline;">
                        <input type="hidden" name="id" value="<?= $service['id']; ?>">
                        <input type="text" name="title" value="<?= htmlspecialchars($service['title']); ?>" required>
                        <input type="file" name="image" accept="image/*">
                        <textarea name="description" rows="2" required><?= htmlspecialchars($service['description']); ?></textarea>
                        <button type="submit" name="update_service">Update</button>
                    </form>
                    <a class="btn" href="?delete=<?= $service['id']; ?>" onclick="return confirm('Delete this service?')">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
    <li><a href="view_logs.php">View Activity Logs</a></li>
<?php require_once "../config/csrf.php"; ?>
<form method="POST">
    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken(); ?>">
    <input type="text" name="title" placeholder="Service Title" required>
    <textarea name="description" placeholder="Service Description" rows="3" required></textarea>
    <button type="submit" name="add_service">Add Service</button>
</form>

</body>
</html>
