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
if (isset($_POST['add_service'])) {
    require_once "../config/csrf.php";
    if (!validateCSRFToken($_POST['csrf_token'])) {
        die("❌ CSRF validation failed.");
    }
    // continue adding service...
}

require_once "../config/db.php";

// ✅ Create upload folder if not exists
$uploadDir = __DIR__ . "/../uploads/portfolio/";
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// ✅ Handle Add Portfolio Item
if (isset($_POST['add_portfolio'])) {
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
            $imagePath = "uploads/portfolio/" . $filename;
        }
    }

    $sql = "INSERT INTO portfolio (title, image_url, description) VALUES (:title, :image_url, :description)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':title' => $title, ':image_url' => $imagePath, ':description' => $description]);
    header("Location: portfolio.php?msg=added");
    exit;
}

// ✅ Handle Delete Portfolio Item
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("SELECT image_url FROM portfolio WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $img = $stmt->fetchColumn();

    if ($img && file_exists(__DIR__ . "/../" . $img)) {
        unlink(__DIR__ . "/../" . $img);
    }

    $sql = "DELETE FROM portfolio WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':id' => $id]);
    header("Location: portfolio.php?msg=deleted");
    exit;
}

// ✅ Handle Update Portfolio Item
if (isset($_POST['update_portfolio'])) {
    $id = intval($_POST['id']);
    $title = htmlspecialchars($_POST['title']);
    $description = htmlspecialchars($_POST['description']);

    // ✅ Check if a new image is uploaded
    $stmt = $conn->prepare("SELECT image_url FROM portfolio WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $oldImage = $stmt->fetchColumn();

    if (!empty($_FILES['image']['name'])) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (in_array($_FILES['image']['type'], $allowedTypes)) {
            $filename = time() . "_" . basename($_FILES['image']['name']);
            $target = $uploadDir . $filename;
            move_uploaded_file($_FILES['image']['tmp_name'], $target);
            $newImagePath = "uploads/portfolio/" . $filename;

            if ($oldImage && file_exists(__DIR__ . "/../" . $oldImage)) {
                unlink(__DIR__ . "/../" . $oldImage);
            }

            $sql = "UPDATE portfolio SET title = :title, image_url = :image_url, description = :description WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':title' => $title,
                ':image_url' => $newImagePath,
                ':description' => $description,
                ':id' => $id
            ]);
        }
    } else {
        $sql = "UPDATE portfolio SET title = :title, description = :description WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':title' => $title,
            ':description' => $description,
            ':id' => $id
        ]);
    }

    header("Location: portfolio.php?msg=updated");
    exit;
}

// ✅ Fetch All Portfolio Items
$stmt = $conn->query("SELECT * FROM portfolio ORDER BY created_at DESC");
$portfolio_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Portfolio</title>
    <style>
        body { font-family: Arial; background:#f4f4f4; padding:20px; }
        h2 { margin-top: 0; }
        table { width: 100%; border-collapse: collapse; background: #fff; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; vertical-align: top; }
        th { background: #333; color: #fff; }
        form { margin-bottom: 20px; background: #fff; padding: 15px; border-radius: 5px; }
        input, textarea { width: 100%; margin: 8px 0; padding: 8px; }
        button { padding: 8px 12px; background: #333; color: #fff; border: none; cursor: pointer; }
        a.btn { background: red; color: white; padding: 5px 10px; text-decoration: none; border-radius: 4px; }
        img { max-width: 120px; border-radius: 4px; }
    </style>
</head>
<body>
    <h1>Manage Portfolio</h1>
    <a href="admin_dashboard.php">⬅ Back to Dashboard</a>

    <h2>Add New Portfolio Item</h2>
    <form method="POST" enctype="multipart/form-data">
        <input type="text" name="title" placeholder="Portfolio Title" required>
        <input type="file" name="image" accept="image/*" required>
        <textarea name="description" placeholder="Portfolio Description" rows="3" required></textarea>
        <button type="submit" name="add_portfolio">Add Portfolio</button>
    </form>

    <h2>Existing Portfolio Items</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Title</th>
            <th>Image</th>
            <th>Description</th>
            <th>Actions</th>
        </tr>
        <?php foreach($portfolio_items as $item): ?>
            <tr>
                <td><?= $item['id']; ?></td>
                <td><?= htmlspecialchars($item['title']); ?></td>
                <td>
                    <?php if ($item['image_url']): ?>
                        <img src="../<?= htmlspecialchars($item['image_url']); ?>" alt="Portfolio Image">
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($item['description']); ?></td>
                <td>
                    <!-- Edit Form -->
                    <form method="POST" enctype="multipart/form-data" style="display:inline;">
                        <input type="hidden" name="id" value="<?= $item['id']; ?>">
                        <input type="text" name="title" value="<?= htmlspecialchars($item['title']); ?>" required>
                        <input type="file" name="image" accept="image/*">
                        <textarea name="description" rows="2" required><?= htmlspecialchars($item['description']); ?></textarea>
                        <button type="submit" name="update_portfolio">Update</button>
                    </form>
                    <a class="btn" href="?delete=<?= $item['id']; ?>" onclick="return confirm('Delete this portfolio item?')">Delete</a>
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
